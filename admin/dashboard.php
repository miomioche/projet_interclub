
<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: login.php');
  exit;
}
require '../includes/db.php';

$joueurs = $pdo->query("SELECT * FROM joueurs ORDER BY prenom")->fetchAll();
$rencontres = $pdo->query("SELECT * FROM rencontres ORDER BY date_match DESC")->fetchAll();
$matchs = $pdo->query("SELECT * FROM match_details ORDER BY id DESC")->fetchAll();
$interviews = $pdo->query("SELECT * FROM interviews ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Tableau de bord</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../includes/header.php'; ?>

<div class="container">
  <h1>🏸 Tableau de bord Admin</h1>

  <section>
    <h2>👤 Joueurs <a href="add_joueur.php">➕</a></h2>
    <ul>
      <?php foreach ($joueurs as $j): ?>
        <li>
          <?= htmlspecialchars($j['prenom'] . ' ' . $j['nom']) ?>
          <a href="edit_joueur.php?id=<?= $j['id'] ?>">✏️</a>
          <a href="delete_joueur.php?id=<?= $j['id'] ?>" onclick="return confirm('Supprimer ce joueur ?')">🗑️</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>

  <section>
    <h2>📅 Rencontres <a href="add_rencontre.php">➕</a></h2>
    <ul>
      <?php foreach ($rencontres as $r): ?>
        <li>
          <?= htmlspecialchars($r['adversaire']) ?> - <?= date('d/m/Y', strtotime($r['date_match'])) ?>
          <a href="edit_rencontre.php?id=<?= $r['id'] ?>">✏️</a>
          <a href="delete_rencontre.php?id=<?= $r['id'] ?>" onclick="return confirm('Supprimer ?')">🗑️</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>

  <section>
    <h2>🎯 Matchs <a href="add_match.php">➕</a></h2>
    <ul>
      <?php foreach ($matchs as $m): ?>
        <li>
          <?= htmlspecialchars($m['nom_adversaire']) ?> (<?= $m['score'] ?>)
          <a href="edit_match.php?id=<?= $m['id'] ?>">✏️</a>
          <a href="delete_match.php?id=<?= $m['id'] ?>" onclick="return confirm('Supprimer ce match ?')">🗑️</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>

  <section>
    <h2>📝 Interviews <a href="add_interview.php">➕</a></h2>
    <ul>
      <?php foreach ($interviews as $i): ?>
        <li>
          <?= htmlspecialchars($i['auteur']) ?>
          <a href="edit_interview.php?id=<?= $i['id'] ?>">✏️</a>
          <a href="delete_interview.php?id=<?= $i['id'] ?>" onclick="return confirm('Supprimer ?')">🗑️</a>
        </li>
      <?php endforeach; ?>
    </ul>
  </section>

  <p><a href="logout.php" class="btn">🚪 Se déconnecter</a></p>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
