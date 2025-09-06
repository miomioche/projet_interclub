<?php



// 1) Charger la connexion PDO une seule fois
require_once __DIR__ . '/includes/db.php';

// 2) Sécurité : vérifier que $pdo existe bien
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die('Connexion DB non initialisée');
}
// === Réglages ===
$TEAM_ID = 1; // id de BCA 6 dans ta table teams (à ajuster si besoin)

// === Requêtes ===
// === Prochaine rencontre ===
$sql_next = "
SELECT f.*, th.short_name AS home_short, ta.short_name AS away_short,
       th.name AS home_name, ta.name AS away_name
FROM fixtures f
JOIN teams th ON th.id = f.home_team_id
JOIN teams ta ON ta.id = f.away_team_id
WHERE (f.home_team_id = :tid1 OR f.away_team_id = :tid2)
  AND f.status = 'scheduled'
ORDER BY f.date_time ASC
LIMIT 1
";
$st = $pdo->prepare($sql_next);
$st->execute(['tid1' => $TEAM_ID, 'tid2' => $TEAM_ID]);
$next_match = $st->fetch(PDO::FETCH_ASSOC);

// === Dernier résultat ===
$sql_last = "
SELECT f.*, th.short_name AS home_short, ta.short_name AS away_short,
       th.name AS home_name, ta.name AS away_name
FROM fixtures f
JOIN teams th ON th.id = f.home_team_id
JOIN teams ta ON ta.id = f.away_team_id
WHERE (f.home_team_id = :tid1 OR f.away_team_id = :tid2)
  AND f.status = 'played'
ORDER BY f.date_time DESC
LIMIT 1
";
$st = $pdo->prepare($sql_last);
$st->execute(['tid1' => $TEAM_ID, 'tid2' => $TEAM_ID]);
$last_result = $st->fetch(PDO::FETCH_ASSOC);
// Choix du bloc principal à afficher
$primary     = $next_match ?: $last_result;
$isUpcoming  = !empty($next_match);
$sectionTitl = $isUpcoming ? 'Prochaine Rencontre' : 'Dernier Résultat';

// Helpers
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function dt_fr($ts){ return date('d/m/Y \à H\hi', strtotime($ts)); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Accueil — Club InterClubs Badminton d’Arras</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<?php include __DIR__ . '/includes/header.php'; ?>


<!-- ===== HERO ===== -->
<section class="hero">
  <img src="img/teambanner.jpeg" alt="Équipe InterClubs" class="hero-img">
  <div class="hero-overlay">
    <div class="hero-content">
      <h1>Équipe InterClubs<br>Badminton Arras</h1>
      <p>Passion — Esprit d’équipe — After</p>
      <a href="rencontres.php" class="btn btn-primary btn-lg">Voir la prochaine rencontre</a>
    </div>
  </div>
</section>

<main class="container">

<div class="card result-card">
  <div class="card-body">

    <div class="result-head">
      <div class="icon">🏆</div>
      <div>
        <h2>Dernier Résultat</h2>
        <?php if (!empty($last_result['date_time'])): ?>
          <div class="sub"><?= h(dt_fr($last_result['date_time'])) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($last_result)): ?>
      <?php
        $home = $last_result['home_name'] ?? '';
        $away = $last_result['away_name'] ?? '';
        $scoreTxt = isset($last_result['score_home'],$last_result['score_away'])
          ? ((int)$last_result['score_home'].' – '.(int)$last_result['score_away']) : '';
        $lieuParts = [];
        if (!empty($last_result['venue_name'])) $lieuParts[] = $last_result['venue_name'];
        if (!empty($last_result['venue_city'])) $lieuParts[] = $last_result['venue_city'];
        $lieu = implode(' — ', $lieuParts);
      ?>

      <div class="result-line">
        <span class="team"><?= h($home) ?></span>
        <span class="vs">vs</span>
        <span class="team"><?= h($away) ?></span>

        <?php if ($scoreTxt !== ''): ?>
          <span class="score-badge"><?= h($scoreTxt) ?></span>
        <?php endif; ?>
      </div>

      <?php if ($lieu !== ''): ?>
        <div class="meta">
          <span class="meta-item">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5a2.5 2.5 0 1 1 .001-5.001A2.5 2.5 0 0 1 12 11.5z"/></svg>
            <?= h($lieu) ?>
          </span>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <p class="muted">Aucun résultat enregistré.</p>
    <?php endif; ?>

    <div class="card-actions">
      <a class="btn btn-primary" href="rencontres.php">Voir le calendrier complet</a>
      <a class="btn btn-primary" href="matches.php">Voir tous les matchs</a>
    </div>
  </div>
</div>

  <!-- ===== 3 cartes d’accès rapide ===== -->
  <div class="cards-row">
    <!-- Notre équipe -->
    <div class="info-card card reveal">
      <div class="card-body">
        <div class="card-head">
          <span class="emoji">👥</span>
          <strong>Notre équipe</strong>
        </div>
        <p class="muted">Découvrez les joueuses et joueurs qui défendent nos couleurs.</p>
        <a href="equipe.php" class="btn btn-primary">Voir l’équipe</a>
      </div>
    </div>

    <!-- Rencontres -->
    <div class="info-card card reveal">
      <div class="card-body">
        <div class="card-head">
          <span class="emoji">📅</span>
          <strong>Rencontres</strong>
        </div>
        <p class="muted">Calendrier & résultats officiels de notre poule.</p>
        <a href="rencontres.php" class="btn btn-primary">Voir le calendrier</a>
      </div>
    </div>

    <!-- Matchs -->
    <div class="info-card card reveal">
      <div class="card-body">
        <div class="card-head">
          <span class="emoji">🏸</span>
          <strong>Matchs</strong>
        </div>
        <p class="muted">Historique détaillé des matchs de la saison.</p>
        <a href="matches.php" class="btn btn-primary">Voir les matchs</a>
      </div>
    </div>
  </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<!-- ===== micro-animation optionnelle des cartes ===== -->
<script>
  (function () {
    const els = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window) || !els.length) return;
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('show'); io.unobserve(e.target); }});
    }, { threshold: .12 });
    els.forEach(el => io.observe(el));
  })();
</script>
