<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: login.php');
  exit;
}
require '../includes/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
  echo "ID match manquant."; exit;
}

// Charger le match
$stmt = $pdo->prepare("SELECT * FROM match_details WHERE id = ?");
$stmt->execute([$id]);
$match = $stmt->fetch();
if (!$match) {
  echo "Match introuvable."; exit;
}

$joueurs = $pdo->query("SELECT id, prenom, nom FROM joueurs ORDER BY prenom")->fetchAll();
$rencontres = $pdo->query("SELECT id, adversaire FROM rencontres ORDER BY date_match DESC")->fetchAll();

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $joueur_id = $_POST['joueur_id'];
  $rencontre_id = $_POST['rencontre_id'];
  $nom_adversaire = trim($_POST['nom_adversaire']);
  $score = trim($_POST['score']);
  $type_match = $_POST['type_match'];
  $resultat = $_POST['resultat'];

  $stmt = $pdo->prepare("UPDATE match_details SET joueur_id = ?, rencontre_id = ?, nom_adversaire = ?, score = ?, type_match = ?, resultat = ? WHERE id = ?");
  $stmt->execute([$joueur_id, $rencontre_id, $nom_adversaire, $score, $type_match, $resultat, $id]);

  $success = "Match mis à jour.";
  $match = $pdo->query("SELECT * FROM match_details WHERE id = $id")->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Modifier un match</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="container">
  <h1>Modifier un match</h1>

  <?php if ($success): ?>
    <p class="alert success"><?= htmlspecialchars($success) ?></p>
  <?php endif; ?>

  <form method="post">
    <label>Joueur :</label>
    <select name="joueur_id" required>
      <?php foreach ($joueurs as $j): ?>
        <option value="<?= $j['id'] ?>" <?= $j['id'] == $match['joueur_id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($j['prenom'] . ' ' . $j['nom']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Rencontre :</label>
    <select name="rencontre_id" required>
      <?php foreach ($rencontres as $r): ?>
        <option value="<?= $r['id'] ?>" <?= $r['id'] == $match['rencontre_id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($r['adversaire']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label>Adversaire :</label>
    <input type="text" name="nom_adversaire" value="<?= htmlspecialchars($match['nom_adversaire']) ?>" required>

    <label>Score :</label>
    <input type="text" name="score" value="<?= htmlspecialchars($match['score']) ?>" required>

    <label>Type de match :</label>
    <select name="type_match">
      <option value="simple" <?= $match['type_match'] === 'simple' ? 'selected' : '' ?>>Simple</option>
      <option value="double" <?= $match['type_match'] === 'double' ? 'selected' : '' ?>>Double</option>
      <option value="mixte" <?= $match['type_match'] === 'mixte' ? 'selected' : '' ?>>Mixte</option>
    </select>

    <label>Résultat :</label>
    <select name="resultat">
      <option value="victoire" <?= $match['resultat'] === 'victoire' ? 'selected' : '' ?>>Victoire</option>
      <option value="défaite" <?= $match['resultat'] === 'défaite' ? 'selected' : '' ?>>Défaite</option>
    </select>

    <button type="submit">Mettre à jour</button>
  </form>

  <p><a class="btn small" href="dashboard.php">← Retour</a></p>
</div>
<?php include '../includes/footer.php'; ?>
</body>
</html>
