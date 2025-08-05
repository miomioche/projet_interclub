<?php
require 'includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
  echo "Aucun match spécifié.";
  exit;
}

// Récupérer les infos du match
$stmt = $pdo->prepare("
  SELECT m.*, j.prenom, j.nom, r.adversaire, r.date_match, r.lieu
  FROM match_details m
  JOIN joueurs j ON m.joueur_id = j.id
  JOIN rencontres r ON m.rencontre_id = r.id
  WHERE m.id = ?
");
$stmt->execute([$id]);
$match = $stmt->fetch();

if (!$match) {
  echo "Match introuvable.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Détail du match</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
  <h1>📄 Détail du match</h1>

  <div class="match-detail">
    <p><strong>Joueur :</strong> <?= htmlspecialchars($match['prenom'] . ' ' . $match['nom']) ?></p>
    <p><strong>Adversaire :</strong> <?= htmlspecialchars($match['nom_adversaire']) ?></p>
    <p><strong>Rencontre :</strong> <?= htmlspecialchars($match['adversaire']) ?> (<?= date('d/m/Y H:i', strtotime($match['date_match'])) ?>)</p>
    <p><strong>Lieu :</strong> <?= htmlspecialchars($match['lieu']) ?></p>
    <p><strong>Score :</strong> <?= htmlspecialchars($match['score']) ?></p>
    <p><strong>Type de match :</strong> <?= ucfirst($match['type_match']) ?></p>
    <p><strong>Résultat :</strong> 
      <span class="<?= $match['resultat'] === 'victoire' ? 'victoire' : 'defaite' ?>">
        <?= ucfirst($match['resultat']) ?>
      </span>
    </p>
  </div>

  <p><a class="btn small" href="match_rencontre.php?id=<?= $match['rencontre_id'] ?>">← Voir tous les matchs de cette rencontre</a></p>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
