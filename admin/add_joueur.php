<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $prenom = $_POST['prenom'];
  $nom = $_POST['nom'];
  $simple = intval($_POST['classement_simple']);
  $double = intval($_POST['classement_double']);
  $mixte = intval($_POST['classement_mixte']);
  $photo = '';

  if (!empty($_FILES['photo']['name'])) {
    $extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    if (in_array($extension, ['jpg','jpeg','png'])) {
      $photo = uniqid() . '.' . $extension;
      move_uploaded_file($_FILES['photo']['tmp_name'], '../photos/' . $photo);
    }
  }

  $stmt = $pdo->prepare("INSERT INTO joueurs (prenom, nom, classement_simple, classement_double, classement_mixte, photo) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute([$prenom, $nom, $simple, $double, $mixte, $photo]);
  header('Location: dashboard.php');
  exit;
}
?>
<form method="post" enctype="multipart/form-data">
  <input name="prenom" required>
  <input name="nom" required>
  <input name="classement_simple" type="number" required>
  <input name="classement_double" type="number" required>
  <input name="classement_mixte" type="number" required>
  <input type="file" name="photo">
  <button>Ajouter</button>
</form>