<?php
// index.php
require __DIR__ . '/includes/db.php';

try {
    // On récupère la prochaine rencontre programmée
    $stmt = $pdo->prepare(<<<SQL
        SELECT
            r.journee,
            r.date_rencontre,
            r.heure,
            l.nom            AS lieu_nom,
            c1.nom           AS equipe_dom,
            r.score_domicile,
            r.score_exterieur,
            c2.nom           AS equipe_ext
        FROM rencontres AS r
        JOIN lieux       AS l  ON l.id  = r.lieu_id
        JOIN adversaires AS c1 ON c1.id = r.domicile_id
        JOIN adversaires AS c2 ON c2.id = r.exterieur_id
        WHERE CONCAT(r.date_rencontre,' ',r.heure) >= NOW()
        ORDER BY r.date_rencontre, r.heure
        LIMIT 1
    SQL);
    $stmt->execute();
    $next = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur BDD : ' . $e->getMessage());
}

?>

<!-- BANNIÈRE HERO -->
 <!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Équipe InterClubs Badminton</title>
  <link rel="stylesheet" href="css/style.css">
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

<!-- SECTION PROCHAINE RENCONTRE -->
<main id="next-match" class="container py-5">
  <h2 class="mb-4 text-center">Prochaine Rencontre</h2>

  <?php if ($next): ?>
    <div class="card mx-auto" style="max-width: 600px;">
      <div class="card-body">
        <h3 class="card-title">Journée J<?= htmlspecialchars($next['journee']) ?></h3>
        <p class="card-text mb-1">
          <strong>Date / Heure :</strong>
          <?= date('d/m/Y', strtotime($next['date_rencontre'])) ?>
          à <?= substr($next['heure'], 0, 5) ?>
        </p>
        <p class="card-text mb-1">
          <strong>Lieu :</strong> <?= htmlspecialchars($next['lieu_nom']) ?>
        </p>
        <p class="card-text mb-1">
          <strong>Équipes :</strong>
          <?= htmlspecialchars($next['equipe_dom']) ?> – <?= htmlspecialchars($next['equipe_ext']) ?>
        </p>
        <p class="card-text mb-3">
          <strong>Score :</strong>
          <?= $next['score_domicile'] ?> – <?= $next['score_exterieur'] ?>
        </p>
        <a href="rencontres.php" class="btn btn-primary">Voir le calendrier complet</a>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-info text-center mx-auto" style="max-width: 600px;">
      Aucune rencontre programmée pour le moment.
    </div>
  <?php endif; ?>

  <!-- CARTES TEASER : Notre Équipe + Détails des Matchs -->

 <div class="col-sm-6">
    <div class="card teaser-card h-100 border-0 shadow-sm text-center">
      <div class="card-body d-flex flex-column">
        <div class="icon-wrapper mb-3">
          <i class="bi bi-people-fill"></i>
        </div>
        <h5 class="card-title">Notre Équipe</h5>
        <p class="card-text flex-grow-1 text-muted">
          Découvrez les joueuses et joueurs qui défendent nos couleurs en interclubs.
        </p>
        <a href="equipe.php" class="btn btn-outline-primary mt-auto">Voir l’équipe</a>
      </div>
    </div>
  </div>

  <!-- Carte Détails des Matchs -->
  <div class="col-sm-6">
    <div class="card teaser-card h-100 border-0 shadow-sm text-center">
      <div class="card-body d-flex flex-column">
        <div class="icon-wrapper mb-3">
          <i class="bi bi-trophy-fill"></i>
        </div>
        <h5 class="card-title">Détails des Matchs</h5>
        <p class="card-text flex-grow-1 text-muted">
          Plongez dans les scores et performances de chaque match de nos journées.
        </p>
        <a href="matches.php" class="btn btn-outline-primary mt-auto">Voir les matchs</a>
      </div>
    </div>
  </div>
</div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
