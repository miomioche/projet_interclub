<?php
require __DIR__ . '/includes/db.php';

/*
 * On lit depuis match_details et on groupe par date/lieu côté PHP.
 * Tri : les plus récents en haut.
 */

$sql = <<<SQL
SELECT
  md.id           AS match_id,
  md.date_match,
  md.lieu,
  md.type_match,
  md.joueur_id,
  j.prenom        AS joueur_prenom,
  j.nom           AS joueur_nom,
  md.binome,
  md.nom_adversaire,
  md.score,
  md.resultat
FROM match_details md
LEFT JOIN joueurs j ON j.id = md.joueur_id
ORDER BY md.date_match DESC,
         FIELD(md.type_match,'simple','double','mixte'),
         md.id
SQL;

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Regrouper par "rencontre" (même date/lieu) */
$rencontres = [];
foreach ($rows as $r) {
    $key = ($r['date_match'] ?? '') . '||' . ($r['lieu'] ?? '');
    if (!isset($rencontres[$key])) {
        $rencontres[$key] = [
            'date_match' => $r['date_match'],
            'lieu'       => $r['lieu'],
            'matches'    => []
        ];
    }
    $rencontres[$key]['matches'][] = $r;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Matchs</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    table { width:100%; border-collapse: collapse; }
    th, td { padding: .55rem .7rem; border-bottom: 1px solid #eee; }
    th { text-align: left; }
    .victoire { color: #28a745; font-weight: bold; }
    .defaite  { color: #dc3545; font-weight: bold; }
    .score-v  { color: #28a745; font-weight: bold; }
    .score-d  { color: #dc3545; font-weight: bold; }
    /* Optionnel: surligner la ligne en fonction du résultat
    tr.win  td { background: #eaf7ef; }
    tr.loss td { background: #fdeaea; }
    */
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="container">
  <h1>Détails des Matchs</h1>

  <?php if (!$rencontres): ?>
    <p>Aucune rencontre.</p>
  <?php else: ?>
    <?php foreach ($rencontres as $rec): ?>
      <h3>
        <?= $rec['date_match'] ? date('d/m/Y à H:i', strtotime($rec['date_match'])) : '—' ?>
        — <?= htmlspecialchars($rec['lieu'] ?? 'Lieu non renseigné') ?>
      </h3>

      <table>
        <thead>
          <tr>
            <th>Type</th>
            <th>Joueur(s)</th>
            <th>Adversaire</th>
            <th>Score par set</th>
            <th>✓ / ✗</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rec['matches'] as $m): ?>
            <?php
              $rowClass = $m['resultat'] === 'victoire' ? 'win' : ($m['resultat'] === 'défaite' ? 'loss' : '');
            ?>
            <tr class="<?= $rowClass ?>">
              <td>
                <?php
                  if ($m['type_match'] === 'simple') echo 'Simple';
                  elseif ($m['type_match'] === 'double') echo 'Double';
                  elseif ($m['type_match'] === 'mixte') echo 'Mixte';
                  else echo htmlspecialchars($m['type_match'] ?? '—');
                ?>
              </td>
              <td>
                <?= htmlspecialchars(trim(($m['joueur_prenom'] ?? '').' '.($m['joueur_nom'] ?? ''))) ?>
                <?php if (!empty($m['binome'])): ?>
                  &amp; <?= htmlspecialchars($m['binome']) ?>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($m['nom_adversaire'] ?? '—') ?></td>
              <td>
                <?php
                  if ($m['resultat'] === 'victoire') {
                      echo '<span class="score-v">'.htmlspecialchars($m['score'] ?? '—').'</span>';
                  } elseif ($m['resultat'] === 'défaite') {
                      echo '<span class="score-d">'.htmlspecialchars($m['score'] ?? '—').'</span>';
                  } else {
                      echo htmlspecialchars($m['score'] ?? '—');
                  }
                ?>
              </td>
              <td>
                <?php
                  if ($m['resultat'] === 'victoire') echo '<span class="victoire">✓</span>';
                  elseif ($m['resultat'] === 'défaite') echo '<span class="defaite">✗</span>';
                  else echo '–';
                ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
</body>
</html>
