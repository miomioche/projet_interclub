<?php
declare(strict_types=1);

/* ===== Debug (à désactiver en prod) ===== */
ini_set('display_errors', '1');
error_reporting(E_ALL);

/* ===== Connexion PDO ===== */
require_once __DIR__ . '/includes/db.php';
if (!isset($pdo) || !($pdo instanceof PDO)) {
  http_response_code(500);
  exit('Connexion DB non initialisée');
}

/* ===== Helpers ===== */
$e = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

/* ===== Paramètre ===== */
$joueurId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$joueurId || $joueurId <= 0) { header('Location: equipe.php'); exit; }

/* ===== 1) Joueur ===== */
$sqlJoueur = "
  SELECT id, nom, prenom, photo,
         classement_simple, classement_double, classement_mixte
  FROM joueurs
  WHERE id = :id
  LIMIT 1
";
$st = $pdo->prepare($sqlJoueur);
$st->execute([':id' => $joueurId]);
$joueur = $st->fetch(PDO::FETCH_ASSOC);
if (!$joueur) { header('Location: equipe.php'); exit; }

$fullName = trim(($joueur['prenom'] ?? '') . ' ' . ($joueur['nom'] ?? ''));

/* Photo avec fallback */
$photoFile = trim((string)($joueur['photo'] ?? ''));
$photoSrc  = $photoFile !== '' ? "img/joueurs/{$photoFile}" : "img/joueurs/default.jpg";
$absPhoto  = __DIR__ . '/' . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $photoSrc);
if (!is_file($absPhoto)) { $photoSrc = "img/joueurs/default.jpg"; }

/* ===== 2) SQL helpers matchs ===== */
function unionMatchDetailsSQL(): string {
  // Placeholders DISTINCTS (évite HY093)
  return "
    SELECT id, joueur_id, type_match, resultat, date_match, score, lieu, nom_adversaire, binome
    FROM match_details
    WHERE joueur_id = :id1

    UNION ALL

    SELECT id, :id2 AS joueur_id, type_match, resultat, date_match, score, lieu, nom_adversaire, binome
    FROM match_details
    WHERE binome IS NOT NULL AND binome <> ''
      AND LOWER(TRIM(binome)) = LOWER(TRIM(:bn))
  ";
}

/* Normalisation de date (multi formats) à réutiliser dans les requêtes */
function dtExpr(string $alias = 'u'): string {
  return "COALESCE(
            STR_TO_DATE(TRIM($alias.date_match), '%d/%m/%Y %H:%i'),
            STR_TO_DATE(TRIM($alias.date_match), '%Y-%m-%d %H:%i:%s'),
            STR_TO_DATE(TRIM($alias.date_match), '%d/%m/%Y'),
            STR_TO_DATE(TRIM($alias.date_match), '%Y-%m-%d')
          )";
}

/** Derniers N matchs (table + binôme) — LIMIT interpolé */
function fetchLastMatches(PDO $pdo, int $id, string $bn, int $limit = 5): array {
  $limit = max(1, (int)$limit);
  $sql = "SELECT * FROM (".unionMatchDetailsSQL().") u
          ORDER BY ".dtExpr('u')." DESC, u.id DESC
          LIMIT {$limit}";
  $st  = $pdo->prepare($sql);
  $st->execute([':id1'=>$id, ':id2'=>$id, ':bn'=>$bn]);
  return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/** Tous les matchs (table + binôme) */
function fetchAllMatches(PDO $pdo, int $id, string $bn): array {
  $sql = "SELECT * FROM (".unionMatchDetailsSQL().") u
          ORDER BY ".dtExpr('u')." DESC, u.id DESC";
  $st  = $pdo->prepare($sql);
  $st->execute([':id1'=>$id, ':id2'=>$id, ':bn'=>$bn]);
  return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/** Dernier match joué */
function fetchLastMatch(PDO $pdo, int $id, string $bn): ?array {
  $dt = dtExpr('u');
  $sql = "SELECT u.*, {$dt} AS dt
          FROM (".unionMatchDetailsSQL().") u
          WHERE {$dt} IS NOT NULL AND {$dt} <= NOW()
          ORDER BY dt DESC, u.id DESC
          LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute([':id1'=>$id, ':id2'=>$id, ':bn'=>$bn]);
  $r = $st->fetch(PDO::FETCH_ASSOC);
  return $r ?: null;
}

/** Prochain match (>= maintenant) */
function fetchNextMatch(PDO $pdo, int $id, string $bn): ?array {
  $dt = dtExpr('u');
  $sql = "SELECT u.*, {$dt} AS dt
          FROM (".unionMatchDetailsSQL().") u
          WHERE {$dt} IS NOT NULL AND {$dt} >= NOW()
          ORDER BY dt ASC, u.id ASC
          LIMIT 1";
  $st = $pdo->prepare($sql);
  $st->execute([':id1'=>$id, ':id2'=>$id, ':bn'=>$bn]);
  $r = $st->fetch(PDO::FETCH_ASSOC);
  return $r ?: null;
}

/** Forme N matchs (sans vue) */
function getFormeJoueur(PDO $pdo, int $id, string $bn, int $limit = 10): array {
  $limit = max(1, (int)$limit);
  $dt = dtExpr('u');
  $sql = "SELECT u.id AS detail_id, u.resultat, {$dt} AS dt
          FROM (".unionMatchDetailsSQL().") u
          WHERE {$dt} IS NOT NULL AND {$dt} <= NOW()
          ORDER BY dt DESC, u.id DESC
          LIMIT {$limit}";
  try {
    $st = $pdo->prepare($sql);
    $st->execute([':id1'=>$id, ':id2'=>$id, ':bn'=>$bn]);
    $out = [];
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
      $res  = mb_strtolower((string)$r['resultat'], 'UTF-8');
      $code = ($res === 'victoire') ? 'w' : (in_array($res, ['défaite','defaite'], true) ? 'l' : 'n');
      $out[] = ['code'=>$code, 'detail_id'=>(int)$r['detail_id']];
    }
    while (count($out) < $limit) { $out[] = ['code'=>'empty','detail_id'=>null]; }
    return $out;
  } catch (Throwable $t) {
    return array_fill(0, $limit, ['code'=>'empty','detail_id'=>null]);
  }
}

/** Partenaires favoris (double/mixte) — LIMIT interpolé */
function fetchTopPartners(PDO $pdo, int $id, string $bn, int $limit = 3): array {
  $limit = max(1, (int)$limit);
  $sql = "
    SELECT partenaire, SUM(res='victoire') AS v,
           SUM(res IN ('défaite','defaite')) AS d, COUNT(*) total
    FROM (
      SELECT TRIM(md.binome) AS partenaire, LOWER(md.resultat) AS res
      FROM match_details md
      WHERE md.joueur_id = :id
        AND md.binome IS NOT NULL AND md.binome <> ''
        AND md.type_match IN ('double','mixte')

      UNION ALL

      SELECT CONCAT(j.prenom,' ',j.nom) AS partenaire, LOWER(md.resultat) AS res
      FROM match_details md
      JOIN joueurs j ON j.id = md.joueur_id
      WHERE md.binome IS NOT NULL AND md.binome <> ''
        AND LOWER(TRIM(md.binome)) = LOWER(TRIM(:bn))
        AND md.type_match IN ('double','mixte')
    ) t
    WHERE partenaire IS NOT NULL AND partenaire <> '' AND LOWER(partenaire) <> LOWER(:me)
    GROUP BY partenaire
    HAVING COUNT(*) > 0
    ORDER BY v DESC, total DESC
    LIMIT {$limit}
  ";
  $st = $pdo->prepare($sql);
  $st->execute([':id'=>$id, ':bn'=>$bn, ':me'=>$bn]);
  return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

/* ===== 3) Données ===== */
$binomeName = $fullName;

$last5      = fetchLastMatches($pdo, $joueurId, $binomeName, 5);
$all        = fetchAllMatches($pdo, $joueurId, $binomeName);
$lastMatch  = fetchLastMatch($pdo, $joueurId, $binomeName);
$nextMatch  = fetchNextMatch($pdo, $joueurId, $binomeName);
$partners   = fetchTopPartners($pdo, $joueurId, $binomeName, 3);
$formeData  = getFormeJoueur($pdo, $joueurId, $binomeName, 10);
$formeTooltip = implode('', array_map(fn($it)=>$it['code']==='w'?'V':($it['code']==='l'?'D':'-'), $formeData));

/* Stats globales (sans vue) */
$dt = dtExpr('u');
$sqlTotals = "SELECT
                COUNT(*) AS total,
                SUM(LOWER(u.resultat)='victoire') AS victoires,
                SUM(LOWER(u.resultat) IN ('défaite','defaite')) AS defaites
              FROM (".unionMatchDetailsSQL().") u
              WHERE {$dt} IS NOT NULL AND {$dt} <= NOW()";
$st = $pdo->prepare($sqlTotals);
$st->execute([':id1'=>$joueurId, ':id2'=>$joueurId, ':bn'=>$binomeName]);
$g = $st->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'victoires'=>0,'defaites'=>0];
$total         = (int)$g['total'];
$victoires     = (int)$g['victoires'];
$defaites      = (int)$g['defaites'];
$winrateGlobal = $total ? round($victoires / $total * 100) : 0;

/* Par discipline */
$disciplines = ['simple'=>'Simple','double'=>'Double','mixte'=>'Mixte'];
$statsDisc = [];
$sqlDisc = "SELECT
              SUM(LOWER(u.resultat)='victoire') AS v,
              SUM(LOWER(u.resultat) IN ('défaite','defaite')) AS d
            FROM (".unionMatchDetailsSQL().") u
            WHERE LOWER(u.type_match) = LOWER(:t)
              AND {$dt} IS NOT NULL AND {$dt} <= NOW()";
$sth = $pdo->prepare($sqlDisc);
foreach ($disciplines as $type=>$label) {
  $sth->execute([':id1'=>$joueurId, ':id2'=>$joueurId, ':bn'=>$binomeName, ':t'=>$type]);
  $r = $sth->fetch(PDO::FETCH_ASSOC) ?: ['v'=>0,'d'=>0];
  $statsDisc[$type] = ['label'=>$label,'victoires'=>(int)$r['v'],'defaites'=>(int)$r['d']];
}

/* Compteurs filtres (sur les 5 derniers affichés) */
$cntAll = count($last5); $cntSimple = $cntDouble = $cntMixte = 0;
foreach ($last5 as $m) {
  $t = mb_strtolower((string)$m['type_match'], 'UTF-8');
  if ($t==='simple') $cntSimple++; elseif ($t==='double') $cntDouble++; elseif ($t==='mixte') $cntMixte++;
}
$filterFromUrl = isset($_GET['t']) ? strtolower((string)$_GET['t']) : 'all';
if (!in_array($filterFromUrl, ['all','simple','double','mixte'], true)) $filterFromUrl = 'all';

/* Séries + set marquant (sur l'ensemble) */
$bestStreak = 0; $worstStreak = 0; $cur=0; $curType=0;
foreach ($all as $m) {
  $t = (mb_strtolower((string)$m['resultat'],'UTF-8')==='victoire') ? 1 : -1;
  if ($t === $curType) { $cur++; } else { $curType = $t; $cur = 1; }
  if ($curType === 1)  $bestStreak  = max($bestStreak,  $cur);
  if ($curType === -1) $worstStreak = max($worstStreak, $cur);
}
$bestScore = '—';
foreach ($all as $m) {
  if (!empty($m['score']) && mb_strtolower((string)$m['resultat'],'UTF-8')==='victoire') { $bestScore = (string)$m['score']; break; }
}
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
  <img class="photo-profil" src="<?= $e($photoSrc) ?>"
       alt="Photo de <?= $e($joueur['prenom'].' '.$joueur['nom']) ?>" loading="lazy">
  <div class="player-hero-text">
    <h1><?= $e(strtoupper((string)$joueur['nom']).' '.($joueur['prenom'] ?? '')) ?></h1>

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
  <!-- Sidebar -->
  <aside class="player-aside">
    <div class="player-stats card" aria-labelledby="stats-globales-title">
      <h3 id="stats-globales-title">Statistiques globales</h3>
      <ul>
        <li>Total de matches : <?= $total ?></li>
        <li>Victoires : <?= $victoires ?></li>
        <li>Défaites : <?= $defaites ?></li>
      </ul>

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
      <?php if ($lastMatch): $isW = (mb_strtolower((string)$lastMatch['resultat'],'UTF-8')==='victoire'); ?>
        <p><strong>Date :</strong> <?= $e(date('d/m/Y H:i', strtotime((string)$lastMatch['date_match']))) ?></p>
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
        <p><strong>Date :</strong> <?= $e(date('d/m/Y H:i', strtotime((string)$nextMatch['date_match']))) ?></p>
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
          <tr><th>Date</th><th>Adversaire</th><th>Format</th><th>Score</th><th>Lieu</th><th>Résultat</th></tr>
        </thead>
        <tbody>
          <?php if (!empty($last5)): foreach ($last5 as $m):
            $isWin = (mb_strtolower((string)$m['resultat'],'UTF-8')==='victoire');
            $rowType = mb_strtolower((string)$m['type_match'],'UTF-8'); ?>
            <tr class="<?= $isWin ? 'row-win' : 'row-loss' ?>" data-type="<?= $e($rowType) ?>">
              <td class="date-cell">
                <?php if (!empty($m['id'])): ?>
                  <a class="row-link" href="matches.php?match_id=<?= (int)$m['id'] ?>#row-m<?= (int)$m['id'] ?>">
                    <?= $e(date('d/m/Y H:i', strtotime((string)$m['date_match']))) ?>
                  </a>
                <?php else: ?>
                  <?= $e(date('d/m/Y H:i', strtotime((string)$m['date_match']))) ?>
                <?php endif; ?>
              </td>
              <td><?= $e($m['nom_adversaire'] ?: '—') ?></td>
              <td><?= $e(ucfirst((string)$m['type_match'])) ?></td>
              <td><?= $e($m['score'] ?: '—') ?></td>
              <td><?= $e($m['lieu'] ?: '—') ?></td>
              <td><span class="<?= $isWin ? 'badge-win' : 'badge-loss' ?>" title="<?= $isWin?'Victoire':'Défaite' ?>"><?= $isWin ? 'V' : 'D' ?></span></td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="6" class="empty-state">Aucun match pour l’instant.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Cartes mobile -->
      <div class="matches-cards">
        <?php if (!empty($last5)): foreach ($last5 as $m):
          $isWin = (mb_strtolower((string)$m['resultat'],'UTF-8')==='victoire');
          $rowType = mb_strtolower((string)$m['type_match'],'UTF-8'); ?>
          <article class="match-card-item" data-type="<?= $e($rowType) ?>">
            <header>
              <span class="badge <?= $isWin ? 'badge-win' : 'badge-loss' ?>"><?= $isWin ? 'V' : 'D' ?></span>
              <time><?= $e(date('d/m/Y H:i', strtotime((string)$m['date_match']))) ?></time>
            </header>
            <p><strong>Adversaire :</strong> <?= $e($m['nom_adversaire'] ?: '—') ?></p>
            <p><strong>Format :</strong> <?= $e(ucfirst((string)$m['type_match'])) ?></p>
            <p><strong>Score :</strong> <?= $e($m['score'] ?: '—') ?></p>
            <p><strong>Lieu :</strong> <?= $e($m['lieu'] ?: '—') ?></p>
            <?php if (!empty($m['id'])): ?>
              <a class="row-link" href="matches.php?match_id=<?= (int)$m['id'] ?>#row-m<?= (int)$m['id'] ?>">Voir la ligne du match</a>
            <?php endif; ?>
          </article>
        <?php endforeach; else: ?>
          <div class="empty-state">Aucun match pour l’instant.</div>
        <?php endif; ?>
      </div>
    </div>

    <h3 class="section-title">🤝 Partenaires préférés</h3>
    <div class="card">
      <table class="partners-table">
        <thead><tr><th>Partenaire</th><th>Bilan</th><th>%</th></tr></thead>
        <tbody>
          <?php if (!empty($partners)): foreach ($partners as $p):
            $pct = $p['total'] ? round(((int)$p['v']) * 100 / (int)$p['total']) : 0; ?>
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

  // Charts (conserve ton visuel)
  if (window.Chart && window.ChartDataLabels) {
    Chart.register(ChartDataLabels);
    const colors = ['#5cb85c','#d9534f'];
    <?php foreach ($statsDisc as $type => $sd): ?>
    new Chart(document.getElementById('<?= $type ?>Chart'), {
      type: 'pie',
      data: { labels: ['Victoires','Défaites'], datasets: [{ data: [<?= $sd['victoires'] ?>, <?= $sd['defaites'] ?>], backgroundColor: colors }] },
      options: {
        plugins: {
          datalabels: {
            color:'#fff', font:{size:16, weight:'bold'},
            formatter: (v, ctx) => {
              const data = ctx.dataset.data, sum = data.reduce((a,b)=>a+b,0) || 1, max = Math.max.apply(null, data);
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

  // Filtre visuel (garde l’état via ?t=)
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
