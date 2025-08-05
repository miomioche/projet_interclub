<?php


declare(strict_types=1);

require __DIR__ . '/includes/db.php';

function getClassements(string $licence): array
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 
            classement_simple  AS simple,
            classement_double  AS `double`,
            classement_mixte   AS mixte
          FROM joueurs
         WHERE licence = ?
    ");
    $stmt->execute([$licence]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        
        return ['simple'=>'–', 'double'=>'–', 'mixte'=>'–'];
    }

   
    return [
        'simple' => $row['simple'] !== null && $row['simple'] !== '0' ? (string)$row['simple'] : '–',
        'double' => $row['double'] !== null && $row['double'] !== '0' ? (string)$row['double'] : '–',
        'mixte'  => $row['mixte']  !== null && $row['mixte']  !== '0' ? (string)$row['mixte']  : '–',
    ];
}


$stmt = $pdo->query("
    SELECT 
        id, prenom, nom, licence,
        classement_simple, classement_double,
        classement_mixte, photo
      FROM joueurs
     ORDER BY classement_simple DESC
");
$joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Équipe InterClubs Badminton</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <main class="container">
    <h1 class="page-title">Classement de l’équipe</h1>

    <table class="team-table">
  <thead>
    <tr>
      <th class="rank">#</th>
      <th>Joueur</th>
      <th>Licence</th>
      <th class="stat">Simple</th>
      <th class="stat">Double</th>
      <th class="stat">Mixte</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($joueurs as $i => $joueur): ?>
    <tr>
      <td class="rank" data-label="#"><?= $i+1 ?></td>
      <td>
  <div class="player-cell">
    <img src="img/joueurs/<?= htmlspecialchars($joueur['photo']) ?>"
         alt="Portrait de <?= htmlspecialchars($joueur['prenom']) ?>">
    <a href="joueur.php?id=<?= $joueur['id'] ?>">
      <?= htmlspecialchars($joueur['prenom']) ?> <?= htmlspecialchars($joueur['nom']) ?>
    </a>
  </div>
</td>

      <td data-label="Licence"><?= htmlspecialchars($joueur['licence'] ?? '') ?></td>
      <td class="stat" data-label="Simple"><?= htmlspecialchars($joueur['classement_simple'] ?? '') ?></td>
      <td class="stat" data-label="Double"><?= htmlspecialchars($joueur['classement_double'] ?? '') ?></td>
      <td class="stat" data-label="Mixte"><?= htmlspecialchars($joueur['classement_mixte'] ?? '') ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
