<?php
require __DIR__ . '/includes/db.php';

// === Réglages ===
$TEAM_ID = 1; // id de BCA 6 dans ta table teams (à ajuster si besoin)

// === Requêtes ===
// Prochaine rencontre
$sql_next = "
  SELECT f.*, th.short_name AS home_short, ta.short_name AS away_short,
         th.name AS home_name, ta.name AS away_name
  FROM fixtures f
  JOIN teams th ON th.id = f.home_team_id
  JOIN teams ta ON ta.id = f.away_team_id
  WHERE (f.home_team_id = :tid OR f.away_team_id = :tid)
    AND f.status = 'scheduled'
  ORDER BY f.date_time ASC
  LIMIT 1
";
$st = $pdo->prepare($sql_next);
$st->execute(['tid' => $TEAM_ID]);
$next_match = $st->fetch(PDO::FETCH_ASSOC);

// Dernier résultat
$sql_last = "
  SELECT f.*, th.short_name AS home_short, ta.short_name AS away_short,
         th.name AS home_name, ta.name AS away_name
  FROM fixtures f
  JOIN teams th ON th.id = f.home_team_id
  JOIN teams ta ON ta.id = f.away_team_id
  WHERE (f.home_team_id = :tid OR f.away_team_id = :tid)
    AND f.status = 'played'
  ORDER BY f.date_time DESC
  LIMIT 1
";
$st = $pdo->prepare($sql_last);
$st->execute(['tid' => $TEAM_ID]);
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


<main class="container">
  <div class="hero-banner position-relative mb-5">
    <img src="img/teambanner.jpeg"
         alt="Équipe InterClubs Badminton – Arras"
         class="img-fluid w-100 rounded">
    <div class="hero-text position-absolute top-50 start-50 translate-middle text-center text-white">
      <h1>Équipe InterClubs<br>Badminton<br>Arras</h1>
      <p>Passion – Esprit d’équipe – After</p>
      <a href="#next-match" class="btn btn-warning cta-button">Voir la prochaine rencontre</a>
    </div>
  </div>
</main>
<!-- ===== Bloc central : Prochaine rencontre OU Dernier résultat ===== -->
  <section class="home-section mb-4">
    <h3 class="mb-2"><?= h($sectionTitl) ?></h3>

    <div class="card shadow-sm">
      <div class="card-body">
        <?php if ($primary): ?>
          <?php if ($primary['matchday']): ?>
            <div class="text-muted mb-2">Journée <strong>J<?= (int)$primary['matchday'] ?></strong></div>
          <?php endif; ?>

          <div class="mb-1">
            <strong>Date / Heure :</strong>
            <?= h(dt_fr($primary['date_time'])) ?>
          </div>

          <div class="mb-1">
            <strong>Lieu :</strong>
            <?= h($primary['venue_name']) ?>
            <?php if (!empty($primary['venue_city'])): ?>
              — <?= h($primary['venue_city']) ?>
            <?php endif; ?>
          </div>

          <div class="mb-1">
            <strong>Équipes :</strong>
            <?= h($primary['home_name']) ?> – <?= h($primary['away_name']) ?>
          </div>

          <?php if (!$isUpcoming && $primary['status'] === 'played'): ?>
            <div class="mb-1">
              <strong>Score :</strong>
              <?= (int)$primary['score_home'] ?> – <?= (int)$primary['score_away'] ?>
            </div>
          <?php endif; ?>

          <div class="mt-2">
            <a class="btn btn-primary btn-lg" href="rencontres.php">Voir le calendrier complet</a>
          </div>
        <?php else: ?>
          <div class="text-muted">Aucune donnée disponible pour le moment.</div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ===== Tes deux tuiles d’accueil (existant) ===== -->
  <section class="home-tiles">
    <!-- Bloc cartes côte à côte -->
<div class="cards-row">
  <div class="info-card">
    <h3>👥 <strong>Notre équipe</strong></h3>
    <p>Découvrez les joueuses et joueurs qui défendent nos couleurs en interclubs.</p>
    <a class="btn-link" href="equipe.php">Voir l’équipe</a>
  </div>

  <div class="info-card">
    <h3>🏆 <strong>Détails des matchs</strong></h3>
    <p>Plongez dans les scores et performances de chaque match de nos journées.</p>
    <a class="btn-link" href="matches.php">Voir les matchs</a>
  </div>
</div>
  </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
