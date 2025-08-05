<?php
require __DIR__ . '/includes/db.php';

try {
    $sql = <<<SQL
SELECT
  r.journee,
  r.date_rencontre,
  r.heure,
  l.nom               AS lieu_nom,
  c1.nom              AS equipe_dom,
  r.score_domicile,
  r.score_exterieur,
  c2.nom              AS equipe_ext
FROM rencontres AS r
JOIN lieux       AS l  ON l.id  = r.lieu_id
JOIN adversaires AS c1 ON c1.id = r.domicile_id
JOIN adversaires AS c2 ON c2.id = r.exterieur_id
ORDER BY r.journee, r.date_rencontre, r.heure
SQL;
    $stmt = $pdo->query($sql);
    $rencontres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur BDD : ' . $e->getMessage());
}
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
<div class="container py-4">
  <h1 class="mb-4">Calendrier des Rencontres</h1>

  <?php
    // 1) On regroupe les rencontres par journée
    $byJournee = [];
    foreach ($rencontres as $r) {
        $byJournee[$r['journee']][] = $r;
    }
  ?>

  <?php foreach ($byJournee as $j => $matches): ?>
    <h2 class="mt-5">Journée J<?= $j ?></h2>
    <!-- 2) Table responsive Bootstrap centrée à largeur “auto” -->
    <div class="table-responsive mx-auto" style="width:auto;">
      <table class="table table-bordered table-sm text-center">
        <thead class="table-secondary">
          <tr>
            <th>Date / Heure</th>
            <th>Lieu</th>
            <th>Équipe Domicile</th>
            <th>Score</th>
            <th>Équipe Extérieure</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($matches as $m): ?>
            <tr>
              <td>
                <?= date('d/m', strtotime($m['date_rencontre'])) ?><br>
                <?= substr($m['heure'], 0, 5) ?>
              </td>
              <td><?= htmlspecialchars($m['lieu_nom']) ?></td>
              <td><?= htmlspecialchars($m['equipe_dom']) ?></td>
              <td><?= $m['score_domicile'] ?> – <?= $m['score_exterieur'] ?></td>
              <td><?= htmlspecialchars($m['equipe_ext']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>
</div>


  <?php include 'includes/footer.php'; ?>

</body>
</html>
