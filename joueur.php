<?php
declare(strict_types=1);
require __DIR__ . '/includes/db.php';

/* ───────────────────────────────────────────────────────────
   1) Joueur
   ─────────────────────────────────────────────────────────── */
$joueurId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($joueurId <= 0) { header('Location: equipe.php'); exit; }

$st = $pdo->prepare("
  SELECT id, nom, prenom, photo,
         classement_simple, classement_double, classement_mixte
  FROM joueurs
  WHERE id = ? LIMIT 1
");
$st->execute([$joueurId]);
$joueur = $st->fetch(PDO::FETCH_ASSOC);
if (!$joueur) { header('Location: equipe.php'); exit; }

$fullName = trim(($joueur['prenom'] ?? '') . ' ' . ($joueur['nom'] ?? ''));

/* ───────────────────────────────────────────────────────────
   2) Helpers SQL (sans COLLATE, comparaisons via LOWER(TRIM()))
   ─────────────────────────────────────────────────────────── */

/** Pastilles depuis la vue v_match_details_for_stats (doit contenir: id, joueur_id, resultat, date_match) */
function getFormeJoueur(PDO $pdo, int $joueurId, int $n = 10): array {
  $sql = "
    SELECT v.id AS detail_id, v.resultat, v.date_match
    FROM v_match_details_for_stats v
    WHERE v.joueur_id = :id
      AND v.date_match <= NOW()
    ORDER BY v.date_match DESC
    LIMIT :n
  ";
  try {
    $st = $pdo->prepare($sql);
    $st->bindValue(':id', $joueurId, PDO::PARAM_INT);
    $st->bindValue(':n',  $n,        PDO::PARAM_INT);
    $st->execute();
    $out = [];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
      $res  = mb_strtolower((string)$r['resultat'], 'UTF-8');
      $code = ($res === 'victoire') ? 'w' : (($res === 'défaite' || $res === 'defaite') ? 'l' : 'n');
      $out[] = ['code'=>$code, 'detail_id'=>(int)$r['detail_id']];
    }
    while (count($out) < $n) { $out[] = ['code'=>'empty','detail_id'=>null]; }
    return $out;
  } catch (Throwable $e) {
    return array_fill(0, $n, ['code'=>'empty','detail_id'=>null]);
  }
}

/** Sous-requête UNION (joueur + quand il est binôme) pour match_details (rich fields) */
function unionMatchDetailsSQL(): string {
  return "
    SELECT id, joueur_id, type_match, resultat, date_match, score, lieu, nom_adversaire, binome
    FROM match_details
    WHERE joueur_id = :id

    UNION ALL
    SELECT id, :id AS joueur_id, type_match, resultat, date_match, score, lieu, nom_adversaire, binome
    FROM match_details
    WHERE binome IS NOT NULL AND binome <> ''
      AND LOWER(TRIM(binome)) = LOWER(TRIM(:bn))
  ";
}

/** Tous les matchs “riches” */
function fetchAllMatchesFull(PDO $pdo, int $id, string $bn): array {
  $sql = unionMatchDetailsSQL() . " ORDER BY date_match DESC";
  $st = $pdo->prepare($sql);
  $st->execute([':id'=>$id, ':bn'=>$bn]);
  return $st->fetchAll(PDO::FETCH_ASSOC);
}

/** N derniers “riches” */
function fetchLastNMatches(PDO $pdo, int $id, string $bn, int $n = 5): array {
  $sql = unionMatchDetailsSQL() . " ORDER BY date_match DESC LIMIT :n";
  $st = $pdo->prepare($sql);
  $st->bindValue(':id', $id, PDO::PARAM_INT);
  $st->bindValue(':bn', $bn, PDO::PARAM_STR);
  $st->bindValue(':n',  $n,  PDO::PARAM_INT);
  $st->execute();
  return $st->fetchAll(PDO::FETCH_ASSOC);
}

/** Dernier & Prochain */
function fetchLastMatch(PDO $pdo, int $id, string $bn): ?array {
  $sql = unionMatchDetailsSQL() . " AND date_match < NOW() ORDER BY date_match DESC LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute([':id'=>$id, ':bn'=>$bn]);
  $r = $st->fetch(PDO::FETCH_ASSOC);
  return $r ?: null;
}
function fetchNextMatch(PDO $pdo, int $id, string $bn): ?array {
  $sql = unionMatchDetailsSQL() . " AND date_match >= NOW() ORDER BY date_match ASC LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute([':id'=>$id, ':bn'=>$bn]);
  $r = $st->fetch(PDO::FETCH_ASSOC);
  return $r ?: null;
}

/** Partenaires préférés (jamais soi-même) */
function fetchTopPartners(PDO $pdo, int $id, string $bn, int $n=3): array {
  $sql = "
    SELECT partenaire, SUM(resultat='victoire') AS v, SUM(resultat='défaite') AS d, COUNT(*) total
    FROM (
      SELECT TRIM(md.binome) AS partenaire, md.resultat
      FROM match_details md
      WHERE md.joueur_id = :id
        AND md.binome IS NOT NULL AND md.binome <> ''
        AND md.type_match IN ('double','mixte')
      UNION ALL
      SELECT CONCAT(j.prenom,' ',j.nom) AS partenaire, md.resultat
      FROM match_details md
      JOIN joueurs j ON j.id = md.joueur_id
      WHERE md.binome IS NOT NULL AND md.binome <> ''
        AND LOWER(TRIM(md.binome)) = LOWER(TRIM(:bn))
        AND md.type_match IN ('double','mixte')
    ) t
    WHERE t.partenaire IS NOT NULL AND t.partenaire <> '' AND LOWER(t.partenaire) <> LOWER(:me)
    GROUP BY partenaire
    HAVING COUNT(*) > 0
    ORDER BY v DESC, total DESC
    LIMIT :n
  ";
  $st = $pdo->prepare($sql);
  $st->bindValue(':id', $id, PDO::PARAM_INT);
  $st->bindValue(':bn', $bn, PDO::PARAM_STR);
  $st->bindValue(':me', $bn, PDO::PARAM_STR);
  $st->bindValue(':n',  $n,  PDO::PARAM_INT);
  $st->execute();
  return $st->fetchAll(PDO::FETCH_ASSOC);
}

/* ───────────────────────────────────────────────────────────
   3) Données pour la page
   ─────────────────────────────────────────────────────────── */
$formeData = getFormeJoueur($pdo, $joueurId, 10);
$formeTooltip = implode('', array_map(fn($it)=>$it['code']==='w'?'V':($it['code']==='l'?'D':'-'), $formeData));

$all       = fetchAllMatchesFull($pdo, $joueurId, $fullName);
$last5     = fetchLastNMatches($pdo, $joueurId, $fullName, 5);
$partners  = fetchTopPartners($pdo, $joueurId, $fullName, 3);
$lastMatch = fetchLastMatch($pdo, $joueurId, $fullName);
// Prochain match (futur uniquement)
$nextMatch = $pdo->prepare("
    SELECT *, STR_TO_DATE(date_match, '%d/%m/%Y %H:%i') AS dt_norm
    FROM match_details
    WHERE (joueur_id = :id OR LOWER(TRIM(binome)) = LOWER(TRIM(:fullname)))
      AND STR_TO_DATE(date_match, '%d/%m/%Y %H:%i') >= NOW()
    ORDER BY dt_norm ASC
    LIMIT 1
");
$nextMatch->execute([':id'=>$joueurId, ':fullname'=>$fullName]);
$nextMatch = $nextMatch->fetch();
/* Faits marquants */
$totalJoues = count($all);
$wins  = array_sum(array_map(fn($m)=>mb_strtolower($m['resultat'],'UTF-8')==='victoire', $all));
$loss  = array_sum(array_map(fn($m)=>in_array(mb_strtolower($m['resultat'],'UTF-8'), ['défaite','defaite'], true), $all));
$winrate = $totalJoues ? round($wins*100/$totalJoues, 1) : 0.0;

$bestStreak = 0; $worstStreak = 0; $cur=0; $curType=0;
foreach ($all as $m) {
  $t = (mb_strtolower($m['resultat'],'UTF-8')==='victoire') ? 1 : -1;
  if ($t === $curType) { $cur++; } else { $curType = $t; $cur = 1; }
  if ($curType === 1)  $bestStreak  = max($bestStreak,  $cur);
  if ($curType === -1) $worstStreak = max($worstStreak, $cur);
}
$bestScore = '—';
foreach ($all as $m) { if (!empty($m['score']) && mb_strtolower($m['resultat'],'UTF-8')==='victoire') { $bestScore = $m['score']; break; } }

/* Stats globales et par discipline via la vue (agrégats) */
$st = $pdo->prepare("
  SELECT COUNT(*) AS total,
         SUM(resultat='victoire') AS victoires,
         SUM(resultat='défaite')  AS defaites
  FROM v_match_details_for_stats
  WHERE joueur_id = :id AND date_match <= NOW()
");
$st->execute([':id'=>$joueurId]);
$g = $st->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'victoires'=>0,'defaites'=>0];
$total     = (int)$g['total'];
$victoires = (int)$g['victoires'];
$defaites  = (int)$g['defaites'];
$winrateGlobal = $total ? round($victoires/$total*100,1) : 0;

$disciplines = ['simple'=>'Simple','double'=>'Double','mixte'=>'Mixte'];
$statsDisc = [];
$sth = $pdo->prepare("
  SELECT SUM(resultat='victoire') AS v, SUM(resultat='défaite') AS d
  FROM v_match_details_for_stats
  WHERE joueur_id = :id AND type_match = :t AND date_match <= NOW()
");
foreach ($disciplines as $type=>$label) {
  $sth->execute([':id'=>$joueurId, ':t'=>$type]);
  $r = $sth->fetch(PDO::FETCH_ASSOC) ?: ['v'=>0,'d'=>0];
  $statsDisc[$type] = ['label'=>$label,'victoires'=>(int)$r['v'],'defaites'=>(int)$r['d']];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Profil de <?= htmlspecialchars($joueur['prenom'].' '.$joueur['nom']) ?></title>
  <link rel="stylesheet" href="css/style.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
</head>
<body>
<?php include __DIR__.'/includes/header.php'; ?>

<main class="container">
  <div class="player-profile">
    <img src="img/joueurs/<?= htmlspecialchars($joueur['photo']) ?>" alt="Photo de <?= htmlspecialchars($joueur['prenom']) ?>" class="photo-profil">
    <h1><?= htmlspecialchars(strtoupper($joueur['nom'])) ?> <?= htmlspecialchars($joueur['prenom']) ?></h1>

    <!-- Pastilles cliquables vers matches.php -->
    <div class="player-forme" title="<?= htmlspecialchars($formeTooltip) ?>">
      <span class="label">Forme&nbsp;:</span>
      <div class="streak">
        <?php foreach ($formeData as $it): ?>
          <?php if (!empty($it['detail_id'])): ?>
            <a class="pastille-link" href="matches.php?match_id=<?= (int)$it['detail_id'] ?>#row-m<?= (int)$it['detail_id'] ?>">
              <span class="<?= htmlspecialchars($it['code']) ?>"></span>
            </a>
          <?php else: ?>
            <span class="<?= htmlspecialchars($it['code']) ?>"></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Classements -->
    <ul class="classements">
      <li><strong>Simple :</strong> <?= htmlspecialchars($joueur['classement_simple']) ?></li>
      <li><strong>Double :</strong> <?= htmlspecialchars($joueur['classement_double']) ?></li>
      <li><strong>Mixte :</strong> <?= htmlspecialchars($joueur['classement_mixte']) ?></li>
    </ul>
  </div>

  <!-- Faits marquants -->
  <div class="highlights">
    <div class="chip"><span>W%</span><strong><?= $winrate ?>%</strong></div>
    <div class="chip"><span>Série+</span><strong><?= $bestStreak ?> V</strong></div>
    <div class="chip"><span>Série−</span><strong><?= $worstStreak ?> D</strong></div>
    <div class="chip"><span>Set marquant</span><strong><?= htmlspecialchars($bestScore) ?></strong></div>
  </div>

  <!-- Statistiques globales -->
  <div class="player-stats" style="max-width:250px;margin:1rem auto;">
    <h3>Statistiques globales</h3>
    <ul>
      <li>Total de matches : <?= $total ?></li>
      <li>Victoires : <?= $victoires ?></li>
      <li>Défaites : <?= $defaites ?></li>
      <li>Taux de réussite : <?= $winrateGlobal ?> %</li>
    </ul>
  </div>

  <!-- Camemberts -->
  <h3 class="section-title">ratio victoires / défaites</h3>
  <div class="stat-charts">
    <?php foreach ($statsDisc as $type => $sd): ?>
      <div>
        <canvas id="<?= $type ?>Chart" width="200" height="200"></canvas>
        <p><strong><?= $sd['label'] ?></strong></p>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- 5 derniers matchs -->
  <h3 class="section-title">5 derniers matchs</h3>
  <table class="last-matches">
    <thead>
      <tr><th>Date</th><th>Adversaire</th><th>Format</th><th>Score</th><th>Résultat</th></tr>
    </thead>
    <tbody>
      <?php foreach ($last5 as $m): ?>
        <?php $isWin = (mb_strtolower($m['resultat'],'UTF-8')==='victoire'); ?>
        <tr class="<?= $isWin ? 'row-win' : 'row-loss' ?>">
          <td><?= date('d/m/Y H:i', strtotime($m['date_match'])) ?></td>
          <td><?= htmlspecialchars($m['nom_adversaire'] ?: '—') ?></td>
          <td><?= ucfirst($m['type_match']) ?></td>
          <td><?= htmlspecialchars($m['score'] ?: '—') ?></td>
          <td><span class="<?= $isWin ? 'badge-win' : 'badge-loss' ?>"><?= $isWin ? 'V' : 'D' ?></span></td>
        </tr>
      <?php endforeach; if (empty($last5)): ?>
        <tr><td colspan="5" style="text-align:center;">Aucun match</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Partenaires -->
  <h3 class="section-title">Partenaires préférés</h3>
  <table class="partners-table">
    <thead><tr><th>Partenaire</th><th>Bilan</th><th>%</th></tr></thead>
    <tbody>
      <?php foreach ($partners as $p):
        $pct = $p['total'] ? round($p['v']*100/$p['total']) : 0; ?>
        <tr>
          <td><?= htmlspecialchars($p['partenaire'] ?: '—') ?></td>
          <td><?= (int)$p['v'] ?>-<?= (int)$p['d'] ?></td>
          <td><?= $pct ?>%</td>
        </tr>
      <?php endforeach; if (empty($partners)): ?>
        <tr><td colspan="3" style="text-align:center;">—</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Dernier / Prochain -->
  <div class="match-blocks">
    <div class="match-card">
      <h3>Dernier match</h3>
      <?php if ($lastMatch): ?>
        <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($lastMatch['date_match'])) ?></p>
        <p><strong>Adversaire :</strong> <?= htmlspecialchars($lastMatch['nom_adversaire'] ?: '—') ?></p>
        <p><strong>Type :</strong> <?= htmlspecialchars($lastMatch['type_match']) ?></p>
        <p><strong>Score :</strong> <?= htmlspecialchars($lastMatch['score'] ?: '—') ?></p>
        <p><strong>Résultat :</strong>
          <span class="<?= (mb_strtolower($lastMatch['resultat'],'UTF-8')==='victoire')?'victoire':'defaite' ?>">
            <?= ucfirst($lastMatch['resultat']) ?>
          </span>
        </p>
        <p><strong>Lieu :</strong> <?= htmlspecialchars($lastMatch['lieu'] ?: '—') ?></p>
      <?php else: ?>
        <p>Aucun match passé trouvé.</p>
      <?php endif; ?>
    </div>
    <div class="match-card">
      <h3>Prochain match</h3>
      <?php if ($nextMatch): ?>
        <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($nextMatch['date_match'])) ?></p>
        <p><strong>Adversaire :</strong> <?= htmlspecialchars($nextMatch['nom_adversaire'] ?: '—') ?></p>
        <p><strong>Type :</strong> <?= htmlspecialchars($nextMatch['type_match']) ?></p>
        <p><strong>Lieu :</strong> <?= htmlspecialchars($nextMatch['lieu'] ?: '—') ?></p>
      <?php else: ?>
        <p>Aucun match programmé.</p>
      <?php endif; ?>
    </div>
  </div>

  <p style="text-align:center;margin-top:1rem;"><a href="equipe.php">← Retour à l’équipe</a></p>
</main>
<!-- Bouton retour en haut -->
<button id="backToTop" title="Retour en haut">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M4 15l8-8 8 8H4z"/>
    </svg>
</button>

<script>
// Affiche / masque le bouton
window.onscroll = function() {
    let btn = document.getElementById("backToTop");
    if (document.documentElement.scrollTop > 200) {
        btn.style.display = "flex";
    } else {
        btn.style.display = "none";
    }
};

// Scroll vers le haut
document.getElementById("backToTop").onclick = function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>
<?php include __DIR__.'/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  Chart.register(ChartDataLabels);
  const colors = ['#5cb85c','#d9534f'];

  <?php foreach ($statsDisc as $type => $sd): ?>
  new Chart(document.getElementById('<?= $type ?>Chart'), {
    type: 'pie',
    data: { labels: ['Victoires','Défaites'],
      datasets: [{ data: [<?= $sd['victoires'] ?>, <?= $sd['defaites'] ?>], backgroundColor: colors }] },
    options: {
      plugins: { datalabels: {
        color:'#fff', font:{size:14,weight:'bold'},
        formatter:(v,ctx)=>{ const s=ctx.dataset.data.reduce((a,b)=>a+b,0)||1; return Math.round(v/s*100)+'%'; }
      }, legend:{display:false}},
      layout:{padding:6}
    },
    plugins:[ChartDataLabels]
  });
  <?php endforeach; ?>
});
</script>
</body>
</html>
