<?php
require __DIR__ . '/includes/db.php';

// Prochaine rencontre
$stmtNext = $pdo->prepare("
    SELECT r.*, a.nom AS adversaire, l.latitude, l.longitude, l.nom AS lieu_nom
      FROM rencontres r
 LEFT JOIN adversaires a ON r.adversaire_id = a.id
 LEFT JOIN lieux      l ON r.lieu_id       = l.id
     WHERE r.date_rencontre >= NOW()
  ORDER BY r.date_rencontre ASC
     LIMIT 1
");
$stmtNext->execute();
$next = $stmtNext->fetch(PDO::FETCH_ASSOC);

// Dernier résultat
$stmtLast = $pdo->prepare("
    SELECT r.*, a.nom AS adversaire, l.nom AS lieu_nom
      FROM rencontres r
 LEFT JOIN adversaires a ON r.adversaire_id = a.id
 LEFT JOIN lieux      l ON r.lieu_id       = l.id
     WHERE r.date_rencontre < NOW()
  ORDER BY r.date_rencontre DESC
     LIMIT 1
");
$stmtLast->execute();
$last = $stmtLast->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Club InterClubs Badminton – Accueil</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>
<body>

  <?php include 'includes/header.php'; ?>

  <!-- BANNIÈRE HERO -->
   <main class="container">
  <div class="hero-banner">
    <img src="img/teambanner.jpeg" alt="Équipe InterClubs Badminton – Arras">
    <div class="hero-text">
      <h1>Équipe InterClubs<br>Badminton<br>Arras</h1>
      <p>Passion – Esprit d’équipe – After</p>
      <a href="#next-match" class="cta-button">Voir la prochaine rencontre</a>
    </div>
  </div>

  
    <!-- Prochaine rencontre -->
    <div id="next-match" class="card orange-card">
      <h2><span class="icon">📅</span>Prochaine rencontre</h2>
      <?php if ($next): ?>
        <p><strong>Contre :</strong> <?= htmlspecialchars($next['adversaire'] ?? 'À définir') ?></p>
        <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($next['date_rencontre'])) ?></p>
        <p><strong>Lieu :</strong> <?= htmlspecialchars($next['lieu_nom'] ?? 'Inconnu') ?></p>
        <div class="map-container" id="map"></div>
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
        <script>
          <?php if ($next): ?>
            var map = L.map('map').setView([<?= $next['latitude'] ?>, <?= $next['longitude'] ?>], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
              attribution: '© OpenStreetMap'
            }).addTo(map);
            L.marker([<?= $next['latitude'] ?>, <?= $next['longitude'] ?>])
             .addTo(map)
             .bindPopup("<?= addslashes($next['lieu_nom']) ?>")
             .openPopup();
          <?php endif; ?>
        </script>
      <?php else: ?>
        <p>Aucune rencontre programmée pour l’instant.</p>
      <?php endif; ?>
    </div>

    <!-- Dernier résultat -->
    <div class="card orange-card">
      <h2><span class="icon">📌</span>Dernier résultat</h2>
      <?php if ($last): ?>
        <p><strong>Contre :</strong> <?= htmlspecialchars($last['adversaire']) ?></p>
        <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($last['date_rencontre'])) ?></p>
        <p><strong>Lieu :</strong> <?= htmlspecialchars($last['lieu_nom']) ?></p>
        <?php if (!empty($last['score'])): ?>
          <p><strong>Score :</strong> <?= htmlspecialchars($last['score']) ?></p>
          <p><strong>Résultat :</strong> <?= htmlspecialchars($last['resultat']) ?></p>
        <?php else: ?>
          <p>Résultat à venir ou non renseigné.</p>
        <?php endif; ?>
      <?php else: ?>
        <p>Aucune rencontre passée trouvée.</p>
      <?php endif; ?>
    </div>
  </main>

  <?php include 'includes/footer.php'; ?>

</body>
</html>
