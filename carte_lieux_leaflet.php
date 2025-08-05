<?php
require 'includes/db.php';

// Récupérer la prochaine rencontre
$stmt = $pdo->query("SELECT * FROM rencontres WHERE date_match >= NOW() ORDER BY date_match ASC LIMIT 1");
$prochaine = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Accueil | Interclubs Badminton</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
  <h1>🏸 Bienvenue sur le site du Club Interclubs</h1>

  <?php if ($prochaine): ?>
    <div class="prochaine-rencontre">
      <h2>📅 Prochaine rencontre</h2>
      <p><strong><?= htmlspecialchars($prochaine['adversaire']) ?></strong></p>
      <p>Le <?= date('d/m/Y H:i', strtotime($prochaine['date_match'])) ?> à <?= htmlspecialchars($prochaine['lieu']) ?></p>

      <div id="map" style="height: 400px; width: 100%; margin-top: 10px;"></div>
    </div>
  <?php else: ?>
    <p>Pas de rencontre prévue prochainement.</p>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<?php if ($prochaine): ?>
<script>
const adresse = "<?= urlencode($prochaine['lieu']) ?>";
fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${adresse}`)
  .then(res => res.json())
  .then(data => {
    if (data[0]) {
      const map = L.map('map').setView([data[0].lat, data[0].lon], 13);
      L.tileLayer('https://{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
      }).addTo(map);
      L.marker([data[0].lat, data[0].lon])
        .addTo(map)
        .bindPopup("<?= htmlspecialchars($prochaine['lieu']) ?>")
        .openPopup();
    }
  });
</script>
<?php endif; ?>
</body>
</html>
