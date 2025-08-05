<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: login.php');
  exit;
}
require '../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
  echo "ID joueur manquant."; exit;
}

// Charger les données du joueur
$stmt = $pdo->prepare("SELECT * FROM joueurs WHERE id = ?");
$stmt->execute([$id]);
$joueur = $stmt->fetch();
if (!$joueur) {
  echo "Joueur introuvable."; exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $prenom = trim($_POST['prenom']);
  $nom = trim($_POST['nom']);
  $simple = intval($_POST['classement_simple']);
  $double = intval($_POST['classement_double']);
  $mixte = intval($_POST['classement_mixte']);
  $photoName = $joueur['photo'];

  // Nouvelle photo ?
  if (!empty($_FILES['photo']['name'])) {
    $targetDir = '../photos/';
    $filename = basename($_FILES['photo']['name']);
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($extension, $allowed)) {
      $photoName = uniqid() . '.' . $extension;
      move_uploaded_file($_FILES['photo']['tmp_name'], $targetDir . $photoName);
    } else {
      $error = "Format de photo non valide.";
    }
  }

  if (!$error) {
    $stmt = $pdo->prepare("UPDATE joueurs SET prenom = ?, nom = ?, classement_simple = ?, classement_double = ?, classement_mixte = ?, photo = ? WHERE id = ?");
    $stmt->execute([$prenom, $nom, $simple, $double, $mixte, $photoName, $id]);
    $success = "Joueur mis à jour avec succès.";
    // Recharger les infos du joueur
    $joueur = $pdo->query("SELECT * FROM joueurs WHERE id = $id")->fetch();
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier joueur</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container">
  <h1>Modifier un joueur</h1>

  <?php if ($success): ?>
    <p class="alert success"><?= htmlspecialchars($success) ?></p>
  <?php elseif ($error): ?>
    <p class="alert error"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form action="edit_joueur.php?id=<?= $joueur['id'] ?>" method="post" enctype="multipart/form-data">
    <label>Prénom :</label>
    <input type="text" name="prenom" value="<?= htmlspecialchars($joueur['prenom']) ?>" required>

    <label>Nom :</label>
    <input type="text" name="nom" value="<?= htmlspecialchars($joueur['nom']) ?>" required>

    <label>Classement simple :</label>
    <input type="number" name="classement_simple" value="<?= htmlspecialchars($joueur['classement_simple']) ?>" required>

    <label>Classement double :</label>
    <input type="number" name="classement_double" value="<?= htmlspecialchars($joueur['classement_double']) ?>" required>

    <label>Classement mixte :</label>
    <input type="number" name="classement_mixte" value="<?= htmlspecialchars($joueur['classement_mixte']) ?>" required>

    <label>Photo actuelle :</label><br>
    <?php if ($joueur['photo']): ?>
      <img src="../photos/<?= htmlspecialchars($joueur['photo']) ?>" alt="Photo" style="height:80px;border-radius:8px;"><br><br>
    <?php endif; ?>

    <label>Changer la photo :</label>
    <input type="file" name="photo" accept="image/*">

    <button type="submit">Mettre à jour</button>
  </form>

  <p><a class="btn" href="dashboard.php">← Retour au tableau de bord</a></p>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
