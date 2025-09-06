<?php
declare(strict_types=1);
require __DIR__ . '/includes/db.php';

/* 1) Joueur */
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

/* 2) Helpers SQL (inchangés) */
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

function fetchAllMatchesFull(PDO $pdo, int $id, string $bn): array {
  $sql = unionMatchDetailsSQL() . " ORDER BY date_match DESC";
  $st = $pdo->prepare($sql);
  $st->execute([':id'=>$id, ':bn'=>$bn]);
  return $st->fetchAll(PDO::FETCH_ASSOC);
}

function fetchLastNMatches(PDO $pdo, int $id, string $bn, int $n = 5): array {
  $sql = unionMatchDetailsSQL() . " ORDER BY date_match DESC LIMIT :n";
  $st = $pdo->prepare($sql);
  $st->bindValue(':id', $id, PDO::PARAM_INT);
  $st->bindValue(':bn', $bn, PDO::PARAM_STR);
  $st->bindValue(':n',  $n,  PDO::PARAM_INT);
  $st->execute();
  return $st->fetchAll(PDO::FETCH_ASSOC);
}

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

/* 3) Données pour la page */
$formeData    = getFormeJoueur($pdo, $joueurId, 10);
$formeTooltip = implode('', array_map(fn($it)=>$it['code']==='w'?'V':($it['code']==='l'?'D':'-'), $formeData));

$all       = fetchAllMatchesFull($pdo, $joueurId, $fullName);
$last5     = fetchLastNMatches($pdo, $joueurId, $fullName, 5);
$partners  = fetchTopPartners($pdo, $joueurId, $fullName, 3);
$lastMatch = fetchLastMatch($pdo, $joueurId, $fullName);

/* Prochain match (format jj/mm/aaaa HH:ii) */
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
$winrate = $totalJoues ? round($wins*100/$totalJoues, 0) : 0;

$bestStreak = 0; $worstStreak = 0; $cur=0; $curType=0;
foreach ($all as $m) {
  $t = (mb_strtolower($m['resultat'],'UTF-8')==='victoire') ? 1 : -1;
  if ($t === $curType) { $cur++; } else { $curType = $t; $cur = 1; }
  if ($curType === 1)  $bestStreak  = max($bestStreak,  $cur);
  if ($curType === -1) $worstStreak = max($worstStreak, $cur);
}
$bestScore = '—';
foreach ($all as $m) {
  if (!empty($m['score']) && mb_strtolower($m['resultat'],'UTF-8')==='victoire') {
    $bestScore = $m['score']; break;
  }
}

/* Stats via la vue */
$st = $pdo->prepare("
  SELECT COUNT(*) AS total,
         SUM(resultat='victoire') AS victoires,
         SUM(resultat='défaite')  AS defaites
  FROM v_match_details_for_stats
  WHERE joueur_id = :id AND date_match <= NOW()
");
$st->execute([':id'=>$joueurId]);
$g = $st->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'victoires'=>0,'defaites'=>0];
$total          = (int)$g['total'];
$victoires      = (int)$g['victoires'];
$defaites       = (int)$g['defaites'];
$winrateGlobal  = $total ? round($victoires/$total*100,0) : 0;

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

/* Sous-titre section “5 derniers matchs” : date la plus ancienne */
$subtitleOldest = '';
if (!empty($last5)) {
  $ds = array_map(fn($m)=>strtotime($m['date_match']), $last5);
  $min = min($ds);
  $subtitleOldest = date('d/m/Y', $min);
}

/* Compteurs par format pour les filtres */
$cntAll = count($last5);
$cntSimple = $cntDouble = $cntMixte = 0;
foreach ($last5 as $m) {
  $t = mb_strtolower($m['type_match'],'UTF-8');
  if ($t==='simple') $cntSimple++;
  elseif ($t==='double') $cntDouble++;
  elseif ($t==='mixte')  $cntMixte++;
}

/* Filtre sélectionné via URL (?t=simple|double|mixte|all) */
$filterFromUrl = isset($_GET['t']) ? mb_strtolower(trim($_GET['t']), 'UTF-8') : 'all';
if (!in_array($filterFromUrl, ['all','simple','double','mixte'], true)) $filterFromUrl = 'all';

/* helper */
$e = fn($s)=>htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Profil de <?= $e(($joueur['prenom'] ?? '').' '.($joueur['nom'] ?? '')) ?></title>
  <link rel="stylesheet" href="style.css" />
  <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
</head>
<body>
<?php include __DIR__.'/includes/header.php'; ?>

<!-- HERO joueur -->
<section class="player-hero">
  <img class="photo-profil" src="img/joueurs/<?= $e($joueur['photo']) ?>"
       alt="Photo de <?= $e($joueur['prenom'].' '.$joueur['nom']) ?>" loading="lazy">
  <div class="player-hero-text">
    <h1><?= $e(strtoupper($joueur['nom']).' '.$joueur['prenom']) ?></h1>

    <div class="player-forme" title="<?= $e($formeTooltip) ?>">
      <span class="label">Forme&nbsp;:</span>
      <div class="streak">
        <?php foreach ($formeData as $it): ?>
          <?php if (!empty($it['detail_id'])): ?>
            <a class="pastille-link" href="matches.php?match_id=<?= (int)$it['detail_id'] ?>#row-m<?= (int)$it['detail_id'] ?>">
              <span class="<?= $e($it['code']) ?>"></span>
            </a>
          <?php else: ?>
            <span class="<?= $e($it['code']) ?>"></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>

    <p class="hero-tags">
      <?php if (!empty($joueur['classement_simple'])): ?>
        <span>Simple <?= $e($joueur['classement_simple']) ?></span>
      <?php endif; ?>
      <?php if (!empty($joueur['classement_double'])): ?>
        <span>Double <?= $e($joueur['classement_double']) ?></span>
      <?php endif; ?>
      <?php if (!empty($joueur['classement_mixte'])): ?>
        <span>Mixte <?= $e($joueur['classement_mixte']) ?></span>
      <?php endif; ?>
    </p>
  </div>
</section>

<!-- LAYOUT 2 colonnes -->
<section class="player-layout">

  <!-- Sidebar sticky -->
  <aside class="player-aside">
    <div class="player-stats card" aria-labelledby="stats-globales-title">
      <h3 id="stats-globales-title">Statistiques globales</h3>
      <ul>
        <li>Total de matches : <?= $total ?></li>
        <li>Victoires : <?= $victoires ?></li>
        <li>Défaites : <?= $defaites ?></li>
      </ul>

      <!-- jauge winrate -->
      <div class="winrate-wrap" aria-label="Taux de réussite global">
        <div class="winrate-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $winrateGlobal ?>">
          <div class="winrate-fill" style="--p: <?= (int)$winrateGlobal ?>%"></div>
        </div>
        <div class="winrate-label"><strong><?= $winrateGlobal ?>%</strong> de victoires</div>
      </div>

      <div class="chip"><span>Plus longue série de victoire</span><strong><?= $bestStreak ?> V</strong></div>
      <div class="chip"><span>Plus longue série de défaites</span><strong><?= $worstStreak ?> D</strong></div>
      <div class="chip"><span>Set marquant</span><strong><?= $e($bestScore) ?></strong></div>
    </div>

    <div class="match-card card">
      <h3>Dernier match</h3>
      <?php if ($lastMatch): $isW = (mb_strtolower($lastMatch['resultat'],'UTF-8')==='victoire'); ?>
        <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($lastMatch['date_match'])) ?></p>
        <p><strong>Adversaire :</strong> <?= $e($lastMatch['nom_adversaire'] ?: '—') ?></p>
        <p><strong>Type :</strong> <?= $e($lastMatch['type_match']) ?></p>
        <p><strong>Score :</strong> <?= $e($lastMatch['score'] ?: '—') ?></p>
        <p><strong>Résultat :</strong>
          <span class="<?= $isW ? 'badge-win' : 'badge-loss' ?>"><?= $isW ? 'V' : 'D' ?></span>
        </p>
        <p><strong>Lieu :</strong> <?= $e($lastMatch['lieu'] ?: '—') ?></p>
      <?php else: ?>
        <p>Pas encore de match joué.</p>
      <?php endif; ?>
    </div>

    <div class="match-card card">
      <h3>Prochain match</h3>
      <?php if ($nextMatch): ?>
        <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($nextMatch['date_match'])) ?></p>
        <p><strong>Adversaire :</strong> <?= $e($nextMatch['nom_adversaire'] ?: '—') ?></p>
        <p><strong>Type :</strong> <?= $e($nextMatch['type_match']) ?></p>
        <p><strong>Lieu :</strong> <?= $e($nextMatch['lieu'] ?: '—') ?></p>
      <?php else: ?>
        <p>Aucun match programmé.</p>
      <?php endif; ?>
    </div>
  </aside>

  <!-- Colonne principale -->
  <div class="player-main">
    <h3 class="section-title">📊 Ratio victoires / défaites</h3>
    <div class="stat-charts">
      <?php foreach ($statsDisc as $type => $sd): ?>
        <div class="card chart-card">
          <div class="chart-wrap">
            <canvas id="<?= $type ?>Chart" width="220" height="220" aria-label="Camembert <?= $e($sd['label']) ?>"></canvas>
          </div>
          <p><strong><?= $e($sd['label']) ?></strong></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="chart-legend" aria-hidden="true">
      <span class="dot dot-win"></span> Victoires
      <span class="dot dot-space"></span>
      <span class="dot dot-loss"></span> Défaites
    </div>

    <h3 class="section-title">🗓️ 5 derniers matchs</h3>
    

    <!-- Filtres -->
    <div class="table-tools">
      <div class="filters" role="tablist" aria-label="Filtrer les matchs">
        <a class="filter-btn<?= $filterFromUrl==='all'?' is-active':'' ?>"    href="?id=<?= (int)$joueurId ?>&t=all"    data-filter="all">Tous (<?= $cntAll ?>)</a>
        <a class="filter-btn<?= $filterFromUrl==='simple'?' is-active':'' ?>" href="?id=<?= (int)$joueurId ?>&t=simple" data-filter="simple">Simple (<?= $cntSimple ?>)</a>
        <a class="filter-btn<?= $filterFromUrl==='double'?' is-active':'' ?>" href="?id=<?= (int)$joueurId ?>&t=double" data-filter="double">Double (<?= $cntDouble ?>)</a>
        <a class="filter-btn<?= $filterFromUrl==='mixte'?' is-active':'' ?>"  href="?id=<?= (int)$joueurId ?>&t=mixte"  data-filter="mixte">Mixte (<?= $cntMixte ?>)</a>
      </div>
    </div>

    <div class="card">
      <table class="last-matches">
        <thead>
          <tr>
            <th>Date</th><th>Adversaire</th><th>Format</th><th>Score</th><th>Lieu</th><th>Résultat</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($last5)): foreach ($last5 as $m):
            $isWin = (mb_strtolower($m['resultat'],'UTF-8')==='victoire');
            $rowType = mb_strtolower($m['type_match'],'UTF-8'); ?>
            <tr class="<?= $isWin ? 'row-win' : 'row-loss' ?>" data-type="<?= $e($rowType) ?>">
              <td class="date-cell">
                <?php if (!empty($m['id'])): ?>
                  <a class="row-link" href="matches.php?match_id=<?= (int)$m['id'] ?>#row-m<?= (int)$m['id'] ?>">
                    <?= date('d/m/Y H:i', strtotime($m['date_match'])) ?>
                  </a>
                <?php else: ?>
                  <?= date('d/m/Y H:i', strtotime($m['date_match'])) ?>
                <?php endif; ?>
              </td>
              <td><?= $e($m['nom_adversaire'] ?: '—') ?></td>
              <td><?= ucfirst($m['type_match']) ?></td>
              <td><?= $e($m['score'] ?: '—') ?></td>
              <td><?= $e($m['lieu'] ?: '—') ?></td>
              <td><span class="<?= $isWin ? 'badge-win' : 'badge-loss' ?>" title="<?= $isWin?'Victoire':'Défaite' ?>"><?= $isWin ? 'V' : 'D' ?></span></td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" class="empty-state">Aucun match pour l’instant. Planifie un match, il s’affichera ici 👇</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Version cartes (mobile) -->
      <div class="matches-cards">
        <?php if (!empty($last5)): foreach ($last5 as $m):
          $isWin = (mb_strtolower($m['resultat'],'UTF-8')==='victoire');
          $rowType = mb_strtolower($m['type_match'],'UTF-8'); ?>
          <article class="match-card-item" data-type="<?= $e($rowType) ?>">
            <header>
              <span class="badge <?= $isWin ? 'badge-win' : 'badge-loss' ?>"><?= $isWin ? 'V' : 'D' ?></span>
              <time><?= date('d/m/Y H:i', strtotime($m['date_match'])) ?></time>
            </header>
            <p><strong>Adversaire :</strong> <?= $e($m['nom_adversaire'] ?: '—') ?></p>
            <p><strong>Format :</strong> <?= ucfirst($m['type_match']) ?></p>
            <p><strong>Score :</strong> <?= $e($m['score'] ?: '—') ?></p>
            <p><strong>Lieu :</strong> <?= $e($m['lieu'] ?: '—') ?></p>
            <?php if (!empty($m['id'])): ?>
              <a class="row-link" href="matches.php?match_id=<?= (int)$m['id'] ?>#row-m<?= (int)$m['id'] ?>">Voir la ligne du match</a>
            <?php endif; ?>
          </article>
        <?php endforeach; else: ?>
          <div class="empty-state">Aucun match pour l’instant. Planifie un match, il s’affichera ici 👇</div>
        <?php endif; ?>
      </div>
    </div>

    <h3 class="section-title">🤝 Partenaires préférés</h3>
    <div class="card">
      <table class="partners-table">
        <thead><tr><th>Partenaire</th><th>Bilan</th><th>%</th></tr></thead>
        <tbody>
          <?php if (!empty($partners)): foreach ($partners as $p):
            $pct = $p['total'] ? round($p['v']*100/$p['total']) : 0; ?>
            <tr>
              <td><?= $e($p['partenaire'] ?: '—') ?></td>
              <td><?= (int)$p['v'] ?>-<?= (int)$p['d'] ?></td>
              <td><?= $pct ?>%</td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="3" class="empty-state">Pas encore de partenaire favori.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<!-- Bouton retour en haut -->
<button id="backToTop" title="Retour en haut" aria-label="Retour en haut">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 15l8-8 8 8H4z"/></svg>
</button>

<?php include __DIR__.'/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Bouton Top
  const btn = document.getElementById('backToTop');
  const onScroll = () => (document.documentElement.scrollTop > 200)
    ? btn.style.display = 'flex'
    : btn.style.display = 'none';
  window.addEventListener('scroll', onScroll);
  btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  onScroll();

  // Graphs
  if (window.Chart && window.ChartDataLabels) {
    Chart.register(ChartDataLabels);
    const colors = ['#5cb85c','#d9534f'];

    <?php foreach ($statsDisc as $type => $sd): ?>
    new Chart(document.getElementById('<?= $type ?>Chart'), {
      type: 'pie',
      data: {
        labels: ['Victoires','Défaites'],
        datasets: [{ data: [<?= $sd['victoires'] ?>, <?= $sd['defaites'] ?>], backgroundColor: colors }]
      },
      options: {
        plugins: {
          datalabels: {
            color:'#fff',
            font:{size:16, weight:'bold'},
            // n’affiche la valeur qu’aux arcs les plus grands (lisibilité)
            formatter: (v, ctx) => {
              const data = ctx.dataset.data;
              const sum = data.reduce((a,b)=>a+b,0) || 1;
              const max = Math.max.apply(null, data);
              return (v === max) ? Math.round(v/sum*100)+'%' : '';
            }
          },
          legend:{display:false}
        },
        layout:{padding:6}
      },
      plugins:[ChartDataLabels]
    });
    <?php endforeach; ?>
  }

  // Filtres tableau (garde aussi l’état via ?t=)
  const rows = document.querySelectorAll('.last-matches tbody tr, .matches-cards .match-card-item');
  function apply(filter){
    rows.forEach(tr=>{
      const t = (tr.getAttribute('data-type')||'').toLowerCase();
      tr.style.display = (filter==='all' || t===filter) ? '' : 'none';
    });
  }
  const initFilter = (new URLSearchParams(location.search)).get('t') || 'all';
  apply(initFilter);
});
</script>
</body>
</html>
