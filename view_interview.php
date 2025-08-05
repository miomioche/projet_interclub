<?php
require 'includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
  echo "Aucune interview spécifiée.";
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM interviews WHERE id = ?");
$stmt->execute([$id]);
$interview = $stmt->fetch();

if (!$interview) {
  echo "Interview introuvable.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Interview de <?= htmlspecialchars($interview['auteur']) ?></title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
  <h1>🎤 Interview : <?= htmlspecialchars($interview['auteur']) ?></h1>
  <p><em><?= date('d/m/Y', strtotime($interview['date_interview'])) ?></em></p>
  <div class="interview-content">
    <p><?= nl2br(htmlspecialchars($interview['contenu'])) ?></p>
  </div>
  <p><a href="interviews.php" class="btn small">← Retour aux interviews</a></p>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
