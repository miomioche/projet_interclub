<?php
// matches.php
date_default_timezone_set('Europe/Paris');
require __DIR__ . '/includes/db.php';

// 1) On récupère tous les mini‐matchs de match_details
try {
    $sql = <<<SQL
SELECT
  md.id,
  md.date_match,
  md.lieu,
  md.type_match,
  CONCAT(j.prenom, ' ', j.nom) AS joueur_dom,
  md.binome,
  COALESCE(a.nom, md.nom_adversaire) AS adversaire,
  md.score,
  md.resultat
FROM match_details AS md
JOIN joueurs         AS j  ON j.id = md.joueur_id
LEFT JOIN adversaires AS a ON a.id = md.adversaire_id
ORDER BY md.date_match,
         FIELD(md.type_match, 'simple', 'double', 'mixte')
SQL;
    $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur BDD : ' . $e->getMessage());
}

// 2) On regroupe par date & lieu
$byGroup = [];
foreach ($rows as $m) {
    $key = $m['date_match'] . '||' . $m['lieu'];
    if (!isset($byGroup[$key])) {
        $byGroup[$key] = [
            'date_match' => $m['date_match'],
            'lieu'       => $m['lieu'],
            'matches'    => []
        ];
    }
    $byGroup[$key]['matches'][] = $m;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Équipe InterClubs Badminton – Détails des Matches</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    table { width:100%; border-collapse: collapse; margin-bottom: 2rem; }
    th, td { padding: .5rem; border: 1px solid #ddd; text-align: left; vertical-align: middle; }
    th { background: #f7f7f7; }
    .set-win  { color: #2a7; }  /* vert */
    .set-lose { color: #e22; }  /* rouge */
    .icon-cell { width:1.5rem; text-align:center; }
    .result-cell { width:2.5rem; text-align:center; }
  </style>
</head>
<body>
  <?php include __DIR__ . '/includes/header.php'; ?>

  <main class="container py-5">
    <h1 class="mb-4">Détails des Matches</h1>

    <?php if (empty($byGroup)): ?>
      <p>Aucun match enregistré pour le moment.</p>
    <?php else: ?>
      <?php foreach ($byGroup as $grp): 
        $dt    = new DateTime($grp['date_match']);
        $date  = $dt->format('d/m/Y');
        $time  = $dt->format('H:i');
      ?>
        <h2><?= $date ?> à <?= $time ?> — <em><?= htmlspecialchars($grp['lieu']) ?></em></h2>
        <table>
          <thead>
            <tr>
              <th class="icon-cell"></th>
              <th>Type</th>
              <th>Joueur(s)</th>
              <th>Adversaire</th>
              <th>Score par set</th>
              <th class="result-cell">✔/✗</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($grp['matches'] as $m): 
              // icône
              switch ($m['type_match']) {
                case 'double': $icon = 'bi-people-fill'; break;
                case 'mixte':  $icon = 'bi-gender-ambiguous'; break;
                default:       $icon = 'bi-person-fill';
              }
              // joueurs
              $players = htmlspecialchars($m['joueur_dom']);
              if (!empty($m['binome'])) {
                $players .= ' &amp; ' . htmlspecialchars($m['binome']);
              }
              // sets colorisés
              $parts = array_filter(
                preg_split('/\s+/', trim($m['score'])),
                fn($v)=> $v!== ''
              );
              $colored = [];
              foreach ($parts as $pt) {
                if (!str_contains($pt,'-')) continue;
                list($a,$b) = explode('-',$pt,2);
                $a=(int)$a; $b=(int)$b;
                $classA = ($a>$b) ? 'set-win':'set-lose';
                $classB = ($b>$a) ? 'set-win':'set-lose';
                $colored[] = "<span class=\"$classA\">{$a}</span>-<span class=\"$classB\">{$b}</span>";
              }
              $displayScore = implode(' ', $colored);
            ?>
            <tr>
              <td class="icon-cell"><i class="bi <?= $icon ?>"></i></td>
              <td><?= htmlspecialchars(ucfirst($m['type_match'])) ?></td>
              <td><?= $players ?></td>
              <td><?= htmlspecialchars($m['adversaire']) ?></td>
              <td><?= $displayScore ?></td>
              <td class="result-cell">
                <?php if ($m['resultat']==='victoire'): ?>
                  <span class="text-success">✓</span>
                <?php else: ?>
                  <span class="text-danger">✗</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
