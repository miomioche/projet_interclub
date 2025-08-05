<?php
session_start();

$login = 'admin';
$password = 'bad2025'; // à personnaliser

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($_POST['login'] === $login && $_POST['password'] === $password) {
    $_SESSION['admin'] = true;
    header('Location: dashboard.php');
    exit;
  } else {
    $error = "Identifiants incorrects.";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Connexion admin</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="container">
  <h1>Connexion Admin</h1>
  <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
  <form method="post">
    <input type="text" name="login" placeholder="Identifiant">
    <input type="password" name="password" placeholder="Mot de passe">
    <button type="submit">Connexion</button>
  </form>
</div>
</body>
</html>
