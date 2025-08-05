<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("INSERT INTO interviews (auteur, contenu, date_interview) VALUES (?, ?, NOW())");
  $stmt->execute([$_POST['auteur'], $_POST['contenu']]);
  header('Location: dashboard.php');
  exit;
}
?>
<form method="post">
  <input name="auteur" placeholder="Auteur" required>
  <textarea name="contenu" placeholder="Contenu de l'interview" required></textarea>
  <button>Ajouter</button>
</form>
