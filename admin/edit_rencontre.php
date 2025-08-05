<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: login.php');
  exit;
}
require '../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
  echo "ID rencontre manquant."; exit;
}

$stmt = $pdo->prepare("SELECT * FROM rencontres WHERE id = ?");
$stmt->execute([$id]);
$rencontre = $stmt->fetch();
if (!$rencontre) {
  echo "Rencontre introuvable."; exit;
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $adversaire = trim($_POST['adversaire']);
  $date_match = $_POST['date_match'];
  $lieu = trim($_POST['lieu']);
  $type = $_POST['type_rencontre'];

  $stmt = $pdo->prepare("UPDATE rencontres SET adversaire = ?, date_match = ?, lieu = ?, type_rencontre = ? WHERE id = ?");
  $stmt->execute([$adversaire, $date_match, $lieu, $type, $id]);

  $success = "Rencontre mise à jour.";
  $rencontre = $pdo->query("SELECT * FROM rencontres WHERE id = $id")->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier une rencontre</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container">
  <h1>Modifier une rencontre</h1>

  <?php if ($success): ?>
    <p class="alert success"><?= htmlspecialchars($success) ?></p>
  <?php endif; ?>

  <form method="post">
    <label>Adversaire :</label>
    <input type="text" name="adversaire" value="<?= htmlspecialchars($rencontre['adversaire']) ?>" required>

    <label>Date et heure :</label>
    <input type="datetime-local" name="date_match" value="<?= date('Y-m-d\TH:i', strtotime($rencontre['date_match'])) ?>" required>

    <label>Lieu :</label>
    <input type="text" name="lieu" value="<?= htmlspecialchars($rencontre['lieu']) ?>" required>

    <label>Type de rencontre :</label>
    <select name="type_rencontre">
      <option value="aller" <?= $rencontre['type_rencontre'] === 'aller' ? 'selected' : '' ?>>Aller</option>
      <option value="retour" <?= $rencontre['type_rencontre'] === 'retour' ? 'selected' : '' ?>>Retour</option>
    </select>

    <button type="submit">Mettre à jour</button>
  </form>

  <p><a class="btn small" href="dashboard.php">← Retour</a></p>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
