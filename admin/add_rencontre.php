<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("INSERT INTO rencontres (adversaire, date_match, lieu, type_rencontre) VALUES (?, ?, ?, ?)");
  $stmt->execute([
    $_POST['adversaire'],
    $_POST['date_match'],
    $_POST['lieu'],
    $_POST['type_rencontre']
  ]);
  header('Location: dashboard.php');
  exit;
}
?>
<form method="post">
  <input name="adversaire" placeholder="Adversaire" required>
  <input type="datetime-local" name="date_match" required>
  <input name="lieu" placeholder="Lieu" required>
  <select name="type_rencontre">
    <option value="aller">Aller</option>
    <option value="retour">Retour</option>
  </select>
  <button>Ajouter</button>
</form>
