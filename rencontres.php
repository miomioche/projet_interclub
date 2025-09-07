<?php
// rencontres.php — Calendrier (À venir + Derniers résultats) + Classement

date_default_timezone_set('Europe/Paris');
require __DIR__ . '/includes/db.php';
require __DIR__ . '/includes/header.php';

/* ========= Réglages ========= */
$SAISON   = $SAISON   ?? '2024-2025';
$DIVISION = $DIVISION ?? 'D6';        // pour le classement
$POULE    = $POULE    ?? 'Poule 7';   // pour le classement (masquée côté UI)
$CLUB_ID  = 1;                        // BCA (Arras)

// Si tes fixtures n'ont pas exactement "D6 Poule 7" en competition, laisse vide.
$COMPET_FIXTURES = ''; // ex: $DIVISION.' '.$POULE;

// Affichage de notre équipe : 'short' => BCA 6 ; 'long' => Badminton Club Arras 6
$OUR_LABEL_MODE = 'short';

// Limites d’affichage (3 lignes par défaut, ?full_upcoming=1 ou ?full_results=1 pour tout)
$VISIBLE_COUNT   = 3;
$SHOW_FULL_UPC   = isset($_GET['full_upcoming']);
$SHOW_FULL_RES   = isset($_GET['full_results']);

/* ============ 1) Équipes du BCA (saison) ============ */
$sqlTeams = "SELECT id, short_name, name FROM teams WHERE club_id = :club AND season = :season";
$stmtTeams = $pdo->prepare($sqlTeams);
$stmtTeams->execute([':club' => $CLUB_ID, ':season' => $SAISON]);
$teams = $stmtTeams->fetchAll(PDO::FETCH_ASSOC);
$teamIds = array_map('intval', array_column($teams, 'id'));
if (!$teamIds) { $teamIds = [-1]; } // évite IN() vide
$inHome = implode(',', array_fill(0, count($teamIds), '?'));
$inAway = implode(',', array_fill(0, count($teamIds), '?'));

/* ============ 2) Derniers résultats & À venir ============ */
$sqlResults = "
SELECT f.*,
       th.short_name AS home_short, th.name AS home_name,
       ta.short_name AS away_short, ta.name AS away_name
FROM fixtures f
JOIN teams th ON th.id = f.home_team_id
JOIN teams ta ON ta.id = f.away_team_id
WHERE f.season = ?
  AND f.status = 'played'
  AND (f.home_team_id IN ($inHome) OR f.away_team_id IN ($inAway))
  AND (? = '' OR f.competition = ?)
ORDER BY f.date_time DESC
LIMIT 100";
$paramsResults = array_merge([$SAISON], $teamIds, $teamIds, [$COMPET_FIXTURES, $COMPET_FIXTURES]);
$stmtResults = $pdo->prepare($sqlResults);
$stmtResults->execute($paramsResults);
$lastResults = $stmtResults->fetchAll(PDO::FETCH_ASSOC);

$sqlUpcoming = "
SELECT f.*,
       th.short_name AS home_short, th.name AS home_name,
       ta.short_name AS away_short, ta.name AS away_name
FROM fixtures f
JOIN teams th ON th.id = f.home_team_id
JOIN teams ta ON ta.id = f.away_team_id
WHERE f.season = ?
  AND f.status = 'scheduled'
  AND f.date_time >= NOW()
  AND (f.home_team_id IN ($inHome) OR f.away_team_id IN ($inAway))
  AND (? = '' OR f.competition = ?)
ORDER BY f.date_time ASC
LIMIT 100";
$paramsUpcoming = array_merge([$SAISON], $teamIds, $teamIds, [$COMPET_FIXTURES, $COMPET_FIXTURES]);
$stmtUpcoming = $pdo->prepare($sqlUpcoming);
$stmtUpcoming->execute($paramsUpcoming);
$upcoming = $stmtUpcoming->fetchAll(PDO::FETCH_ASSOC);

/* ============ 3) Classement (division + poule masquée en UI) ============ */
$sqlStandings = "
  SELECT rang, equipe, jouees, gagnees, nulles, perdues, forfaits,
         bonus, penalites, points, matchs_diff, sets_diff, pts_diff
  FROM classement_equipes
  WHERE TRIM(saison)   = TRIM(:saison)
    AND TRIM(division) = TRIM(:division)
    AND TRIM(poule)    = TRIM(:poule)
  ORDER BY rang ASC";
$stmtSt = $pdo->prepare($sqlStandings);
$stmtSt->execute([':saison'=>$SAISON, ':division'=>$DIVISION, ':poule'=>$POULE]);
$standings = $stmtSt->fetchAll(PDO::FETCH_ASSOC);

/* ============ 4) Rendu d'une rencontre (ligne) ============ */
function render_fixture_line(array $f, array $teamIds): string {
  $dt = new DateTime($f['date_time']);
  $date  = $dt->format('d/m');
  $heure = $dt->format('H\\hi');
  $lieu = trim(($f['venue_name'] ?? '') . (isset($f['venue_city']) && $f['venue_city'] ? ', ' . $f['venue_city'] : ''));

  $homeIsClub = in_array((int)$f['home_team_id'], $teamIds, true);
  $awayIsClub = in_array((int)$f['away_team_id'], $teamIds, true);

  $mode = $GLOBALS['OUR_LABEL_MODE'] ?? 'short';
  $formatTeam = function(string $name, string $short, bool $isOurClub, string $mode) {
    if (!$isOurClub) return htmlspecialchars($name);
    if ($mode === 'short') {
      // convertit "Arras 6" -> "BCA 6" si besoin
      if (preg_match('/^\\s*Arras\\s*(\\d+)\\s*$/i', $short, $m)) return 'BCA ' . $m[1];
      return htmlspecialchars($short);
    }
    return htmlspecialchars($name);
  };

  $homeLabel = $formatTeam($f['home_name'], $f['home_short'], $homeIsClub, $mode);
  $awayLabel = $formatTeam($f['away_name'], $f['away_short'], $awayIsClub, $mode);
  $teamsLine = $homeLabel . " <span class='text-muted'>vs</span> " . $awayLabel;

  // Score / statut
  $scoreHtml = "<span class='score-chip score-sched'>à venir</span>";
  if ($f['status'] === 'played' && $f['score_home'] !== null && $f['score_away'] !== null) {
    $home = (int)$f['score_home']; $away = (int)$f['score_away'];
    $clubWon  = ($homeIsClub && $home > $away) || ($awayIsClub && $away > $home);
    $clubDraw = ($home === $away);
    $cls = $clubDraw ? 'score-draw' : ($clubWon ? 'score-win' : 'score-loss');
    $scoreHtml = "<span class='score-chip {$cls}'>{$home}&nbsp;–&nbsp;{$away}</span>";
  }

  // Badge compo / journée — retire visuellement "Poule X"
  $badge = '';
  if (!empty($f['competition']) || !empty($f['matchday'])) {
    $comp = $f['competition'] ?? '';
    $comp = preg_replace('/\\s*Poule\\s+[^\\s]+/i', '', $comp); // "D6 Poule 7" → "D6"
    $comp = trim($comp);
    $parts = [];
    if ($comp !== '') $parts[] = htmlspecialchars($comp);
    if (!empty($f['matchday'])) $parts[] = 'J' . (int)$f['matchday'];
    if (!empty($parts)) $badge = "<span class='badge badge-soft'>" . implode(' · ', $parts) . "</span>";
  }

  return "
    <li class='match-item'>
      <div class='mi-date'>{$date}<small>{$heure}</small></div>
      <div class='mi-body'>
        <div class='mi-teams'>{$teamsLine}</div>
        <div class='mi-lieu'>" . htmlspecialchars($lieu) . "</div>
      </div>
      <div class='mi-meta'>
        {$scoreHtml}
        {$badge}
      </div>
    </li>
  ";
}
?>

<link rel="stylesheet" href="style.css">
<main class="container py-4">
  <h2 class="mb-4">Calendrier des Rencontres</h2>

  <div class="cards-grid">
    <!-- À venir -->
    <div class="card shadow-sm" id="upcoming">
  <div class="card-body">
    <h5 class="card-title mb-3">À venir</h5>

    <?php if (empty($upcoming)): ?>
      <div class="text-muted">Aucune rencontre programmée.</div>
    <?php else: ?>
      <ul class="match-list">
        <?php foreach ($list_upcoming as $f) echo render_fixture_line($f, $teamIds); ?>
      </ul>

      <div class="mt-2">
        <?php if (!$show_full_upcoming && count($upcoming) > $VISIBLE_COUNT): ?>
          <a href="?full_upcoming=1#upcoming" class="btn btn-sm btn-success rounded-pill">
            🏸 Historique complet
          </a>
        <?php elseif ($show_full_upcoming): ?>
          <a href="rencontres.php#upcoming" class="btn btn-sm btn-success rounded-pill">
            🏸⬆️ Réduire
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

    <!-- Derniers résultats -->
    <div class="card shadow-sm" id="results">
      <div class="card-body">
        <h5 class="card-title mb-3">Derniers résultats</h5>
        <?php if (empty($lastResults)): ?>
          <div class="text-muted">Aucun résultat pour le moment.</div>
        <?php else: ?>
          <?php $list = $SHOW_FULL_RES ? $lastResults : array_slice($lastResults, 0, $VISIBLE_COUNT); ?>
          <ul class="match-list">
            <?php foreach ($list as $f) echo render_fixture_line($f, $teamIds); ?>
          </ul>
          <div class="mt-2">
            <?php if (!$SHOW_FULL_RES && count($lastResults) > $VISIBLE_COUNT): ?>
              <a href="?full_results=1#results" class="btn btn-sm btn-success rounded-pill">🏸 Historique complet</a>
            <?php elseif ($SHOW_FULL_RES): ?>
              <a href="rencontres.php#results" class="btn btn-sm btn-success rounded-pill">🏸⬆️ Réduire</a>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
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
  <!-- Classement (visuel amélioré) -->
  <div class="card shadow-sm mt-4 card-standings">
    <div class="card-body">
      <div class="caption-bar">
        <h5 class="card-title mb-0">
          Classement <?= htmlspecialchars($DIVISION) ?> <small class="text-muted">• <?= htmlspecialchars($SAISON) ?></small>
        </h5>
        <span class="badge-soft">Données officielles</span>
      </div>

      <?php if (empty($standings)): ?>
        <div class="text-muted">Aucun classement disponible.</div>
      <?php else: ?>
        <?php
          $PROMO_SLOTS = 1;               // rangs en vert
          $RELEG_SLOTS = 2;               // derniers rangs en rouge
          $totalTeams  = count($standings);
          $chip = function($v){
            $v = (int)$v;
            $cls = $v > 0 ? 'chip chip-pos' : ($v < 0 ? 'chip chip-neg' : 'chip chip-0');
            return "<span class=\"$cls\">$v</span>";
          };
        ?>
        <div class="table-responsive table-standings-wrap">
          <table class="table-standings">
            <thead>
              <tr>
                <th class="td-rank">#</th>
                <th>Équipe</th>
                <th class="num">J</th>
                <th class="num">G</th>
                <th class="num">N</th>
                <th class="num">P</th>
                <th class="num">Forf.</th>
                <th class="num">Bonus</th>
                <th class="num">Pénal.</th>
                <th class="num">Pts</th>
                <th class="num">Matchs +/-</th>
                <th class="num">Sets +/-</th>
                <th class="num">Pts +/-</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($standings as $row):
                $rang = (int)$row['rang'];
                $isBCA = stripos($row['equipe'], 'Arras') !== false || stripos($row['equipe'], 'BCA') !== false;

                $rankClass = 'rank-pill';
                if ($rang <= $PROMO_SLOTS) $rankClass .= ' rank-top';
                if ($rang > $totalTeams - $RELEG_SLOTS) $rankClass .= ' rank-down';
              ?>
                <tr class="<?= $isBCA ? 'highlight-bca' : '' ?>">
                  <td class="td-rank"><span class="<?= $rankClass ?>"><?= $rang ?></span></td>
                  <td class="td-team"><?= htmlspecialchars($row['equipe']) ?></td>
                  <td class="num"><?= (int)$row['jouees'] ?></td>
                  <td class="num"><?= (int)$row['gagnees'] ?></td>
                  <td class="num"><?= (int)$row['nulles'] ?></td>
                  <td class="num"><?= (int)$row['perdues'] ?></td>
                  <td class="num"><?= (int)$row['forfaits'] ?></td>
                  <td class="num"><?= (int)$row['bonus'] ?></td>
                  <td class="num"><?= (int)$row['penalites'] ?></td>
                  <td class="num td-points"><?= (int)$row['points'] ?></td>
                  <td class="<?= ((int)$row['matchs_diff'] > 0) ? 'diff-pos' : (((int)$row['matchs_diff'] < 0) ? 'diff-neg' : 'diff-zero') ?>">
  <?= (int)$row['matchs_diff'] ?>
</td>
<td class="<?= ((int)$row['sets_diff'] > 0) ? 'diff-pos' : (((int)$row['sets_diff'] < 0) ? 'diff-neg' : 'diff-zero') ?>">
  <?= (int)$row['sets_diff'] ?>
</td>
<td class="<?= ((int)$row['pts_diff'] > 0) ? 'diff-pos' : (((int)$row['pts_diff'] < 0) ? 'diff-neg' : 'diff-zero') ?>">
  <?= (int)$row['pts_diff'] ?>
</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
