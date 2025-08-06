<?php
require 'includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
  echo "Rencontre non spécifiée.";
  exit;
}

// Récupérer la rencontre
$stmt = $pdo->prepare("SELECT * FROM rencontres WHERE id = ?");
$stmt->execute([$id]);
$rencontre = $stmt->fetch();
if (!$rencontre) {
  echo "Rencontre introuvable.";
  exit;
}

// Récupérer les matchs de cette rencontre
$stmt = $pdo->prepare("
  SELECT m.*, j.prenom, j.nom
  FROM match_details m
  JOIN joueurs j ON m.joueur_id = j.id
  WHERE m.rencontre_id = ?
  ORDER BY m.id ASC
");
$stmt->execute([$id]);
$matchs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Matchs vs <?= htmlspecialchars($rencontre['adversaire']) ?></title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
  <h1>🆚 Matchs contre <?= htmlspecialchars($rencontre['adversaire']) ?></h1>
  <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($rencontre['date_match'])) ?></p>
  <p><strong>Lieu :</strong> <?= htmlspecialchars($rencontre['lieu']) ?></p>
  <p><strong>Type :</strong> <?= htmlspecialchars($rencontre['type_rencontre']) ?></p>

  <h2>🎯 Résultats des matchs</h2>

  <?php if (empty($matchs)): ?>
    <p>Aucun match enregistré pour cette rencontre.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Joueur</th>
          <th>Adversaire</th>
          <th>Type</th>
          <th>Score</th>
          <th>Résultat</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($matchs as $m): ?>
        <tr>
          <td><?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></td>
          <td><?= htmlspecialchars($m['nom_adversaire']) ?></td>
          <td><?= ucfirst($m['type_match']) ?></td>
          <td><?= htmlspecialchars($m['score']) ?></td>
          <td class="<?= $m['resultat'] === 'victoire' ? 'victoire' : 'defaite' ?>">
            <?= ucfirst($m['resultat']) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

<?php
require 'includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
  echo "Rencontre non spécifiée.";
  exit;
}

// Récupérer la rencontre
$stmt = $pdo->prepare("SELECT * FROM rencontres WHERE id = ?");
$stmt->execute([$id]);
$rencontre = $stmt->fetch();
if (!$rencontre) {
  echo "Rencontre introuvable.";
  exit;
}

// Récupérer les matchs de cette rencontre
$stmt = $pdo->prepare("
  SELECT m.*, j.prenom, j.nom
  FROM match_details m
  JOIN joueurs j ON m.joueur_id = j.id
  WHERE m.rencontre_id = ?
  ORDER BY m.id ASC
");
$stmt->execute([$id]);
$matchs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Matchs vs <?= htmlspecialchars($rencontre['adversaire']) ?></title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
  <h1>🆚 Matchs contre <?= htmlspecialchars($rencontre['adversaire']) ?></h1>
  <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($rencontre['date_match'])) ?></p>
  <p><strong>Lieu :</strong> <?= htmlspecialchars($rencontre['lieu']) ?></p>
  <p><strong>Type :</strong> <?= htmlspecialchars($rencontre['type_rencontre']) ?></p>

  <h2>🎯 Résultats des matchs</h2>

  <?php if (empty($matchs)): ?>
    <p>Aucun match enregistré pour cette rencontre.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Joueur</th>
          <th>Adversaire</th>
          <th>Type</th>
          <th>Score</th>
          <th>Résultat</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($matchs as $m): ?>
        <tr>
          <td><?= htmlspecialchars($m['prenom'] . ' ' . $m['nom']) ?></td>
          <td><?= htmlspecialchars($m['nom_adversaire']) ?></td>
          <td><?= ucfirst($m['type_match']) ?></td>
          <td><?= htmlspecialchars($m['score']) ?></td>
          <td class="<?= $m['resultat'] === 'victoire' ? 'victoire' : 'defaite' ?>">
            <?= ucfirst($m['resultat']) ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
