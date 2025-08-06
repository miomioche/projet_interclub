<?php
require 'includes/db.php';

$interviews = $pdo->query("SELECT * FROM interviews ORDER BY date_interview DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Interviews des joueurs</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
  <h1>🎤 Interviews des joueurs</h1>

  <?php if (empty($interviews)): ?>
    <p>Aucune interview enregistrée pour le moment.</p>
  <?php else: ?>
    <ul class="interview-list">
      <?php foreach ($interviews as $i): ?>
        <li>
          <a href="view_interview.php?id=<?= $i['id'] ?>">
            <?= htmlspecialchars($i['auteur']) ?> - 
            <em><?= date('d/m/Y', strtotime($i['date_interview'])) ?></em>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>

<?php
require 'includes/db.php';

$interviews = $pdo->query("SELECT * FROM interviews ORDER BY date_interview DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Interviews des joueurs</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container">
  <h1>🎤 Interviews des joueurs</h1>

  <?php if (empty($interviews)): ?>
    <p>Aucune interview enregistrée pour le moment.</p>
  <?php else: ?>
    <ul class="interview-list">
      <?php foreach ($interviews as $i): ?>
        <li>
          <a href="view_interview.php?id=<?= $i['id'] ?>">
            <?= htmlspecialchars($i['auteur']) ?> - 
            <em><?= date('d/m/Y', strtotime($i['date_interview'])) ?></em>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
</body>
</html>
