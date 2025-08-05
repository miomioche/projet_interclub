<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require '../includes/db.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM interviews WHERE id = ?");
$stmt->execute([$id]);
$interview = $stmt->fetch();
if (!$interview) { echo "Introuvable."; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("UPDATE interviews SET auteur = ?, contenu = ? WHERE id = ?");
  $stmt->execute([$_POST['auteur'], $_POST['contenu'], $id]);
  header('Location: dashboard.php');
  exit;
}
?>
<form method="post">
  <input name="auteur" value="<?= htmlspecialchars($interview['auteur']) ?>" required>
  <textarea name="contenu" required><?= htmlspecialchars($interview['contenu']) ?></textarea>
  <button>Mettre à jour</button>
</form>
