<?php
require __DIR__ . '/includes/db.php';

// 1) Récupérer l'ID du joueur
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: equipe.php');
    exit;
}

// 2) Profil du joueur
$stmt = $pdo->prepare("
  SELECT nom, prenom, photo, classement_simple, classement_double, classement_mixte
  FROM joueurs
  WHERE id = ?
");
$stmt->execute([$id]);
$joueur = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$joueur) {
    header('Location: equipe.php');
    exit;
}

// 3) Statistiques globales
$stmt = $pdo->prepare("
  SELECT 
    COUNT(*)                   AS total,
    SUM(resultat = 'victoire') AS victoires,
    SUM(resultat = 'défaite')  AS defaites
  FROM match_details
  WHERE joueur_id = ?
");
$stmt->execute([$id]);
$g = $stmt->fetch(PDO::FETCH_ASSOC);
$total     = (int)$g['total'];
$victoires = (int)$g['victoires'];
$defaites  = (int)$g['defaites'];
$winrate   = $total > 0 ? round($victoires/$total*100,1) : 0;

// 4) Statistiques par discipline
$disciplines = [
  'simple' => 'Simple',
  'double' => 'Double',
  'mixte'  => 'Mixte',
];
$statsDisc = [];
foreach ($disciplines as $type => $label) {
    $sth = $pdo->prepare("
      SELECT 
        COUNT(*)                   AS total,
        SUM(resultat = 'victoire') AS victoires,
        SUM(resultat = 'défaite')  AS defaites
      FROM match_details
      WHERE joueur_id = ?
        AND type_match = ?
    ");
    $sth->execute([$id, $type]);
    $r = $sth->fetch(PDO::FETCH_ASSOC);
    $t = (int)$r['total'];
    $v = (int)$r['victoires'];
    $d = (int)$r['defaites'];
    $statsDisc[$type] = [
      'label'     => $label,
      'victoires' => $v,
      'defaites'  => $d
    ];
}

// 5) Dernier et prochain match
$stmtLast = $pdo->prepare("
  SELECT date_match, nom_adversaire, type_match, score, resultat, lieu
  FROM match_details
  WHERE joueur_id = ? AND date_match < NOW()
  ORDER BY date_match DESC
  LIMIT 1
");
$stmtLast->execute([$id]);
$last = $stmtLast->fetch(PDO::FETCH_ASSOC);

$stmtNext = $pdo->prepare("
  SELECT date_match, nom_adversaire, type_match, lieu
  FROM match_details
  WHERE joueur_id = ? AND date_match >= NOW()
  ORDER BY date_match ASC
  LIMIT 1
");
$stmtNext->execute([$id]);
$next = $stmtNext->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Profil de <?= htmlspecialchars($joueur['prenom'].' '.$joueur['nom']) ?></title>
  <link rel="stylesheet" href="css/style.css">
  <!-- Chart.js et plugin Datalabels -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
  <style>
    .stat-charts {
      display: flex;
      justify-content: center;
      gap: 2rem;
      margin: 2rem 0;
    }
    .stat-charts canvas {
      max-width: 200px !important;
      max-height: 200px !important;
    }
  </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="container">
  <!-- Profil -->
  <div class="player-profile" style="text-align:center;">
    <img src="img/joueurs/<?= htmlspecialchars($joueur['photo']) ?>"
         alt="Photo de <?= htmlspecialchars($joueur['prenom']) ?>"
         class="photo-profil">
    <h1>
      <?= htmlspecialchars(strtoupper($joueur['nom'])) ?>
      <?= htmlspecialchars($joueur['prenom']) ?>
    </h1>
    <ul class="classements" style="list-style:none;padding:0;display:inline-block;text-align:left;">
      <li><strong>Simple :</strong> <?= htmlspecialchars($joueur['classement_simple']) ?></li>
      <li><strong>Double :</strong> <?= htmlspecialchars($joueur['classement_double']) ?></li>
      <li><strong>Mixte :</strong> <?= htmlspecialchars($joueur['classement_mixte']) ?></li>
    </ul>
  </div>

  <!-- Statistiques globales -->
  <div class="player-stats" style="max-width:250px;margin:1rem auto;">
    <h3>Statistiques globales</h3>
    <ul>
      <li>Total de matches : <?= $total ?></li>
      <li>Victoires : <?= $victoires ?></li>
      <li>Défaites : <?= $defaites ?></li>
      <li>Taux de réussite : <?= $winrate ?> %</li>
    </ul>
  </div>

  <!-- Camemberts par discipline -->
  <h3 style="text-align:center;">ratio victoires / défaites</h3>
  <div class="stat-charts">
    <?php foreach ($statsDisc as $type => $sd): ?>
      <div>
        <canvas
          id="<?= $type ?>Chart"
          width="400" height="400"
          style="max-width:200px;max-height:200px;">
        </canvas>
        <p style="text-align:center;"><strong><?= $sd['label'] ?></strong></p>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Dernier et prochain match -->
  <div class="match-blocks" style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:center;">
    <div class="match-card" style="flex:1;min-width:280px;">
      <h3>Dernier match</h3>
      <?php if ($last): ?>
        <p><strong>Date :</strong> <?= date('d/m/Y H:i',strtotime($last['date_match'])) ?></p>
        <p><strong>Adversaire :</strong> <?= htmlspecialchars($last['nom_adversaire']) ?></p>
        <p><strong>Type :</strong> <?= htmlspecialchars($last['type_match']) ?></p>
        <p><strong>Score :</strong> <?= htmlspecialchars($last['score'] ?: '–') ?></p>
        <p><strong>Résultat :</strong>
          <span class="<?= $last['resultat']=='victoire'?'victoire':'defaite' ?>">
            <?= ucfirst($last['resultat']) ?>
          </span>
        </p>
        <p><strong>Lieu :</strong> <?= htmlspecialchars($last['lieu'] ?: '–') ?></p>
      <?php else: ?>
        <p>Aucun match passé trouvé.</p>
      <?php endif; ?>
    </div>
    <div class="match-card" style="flex:1;min-width:280px;">
      <h3>Prochain match</h3>
      <?php if ($next): ?>
        <p><strong>Date :</strong> <?= date('d/m/Y H:i',strtotime($next['date_match'])) ?></p>
        <p><strong>Adversaire :</strong> <?= htmlspecialchars($next['nom_adversaire']) ?></p>
        <p><strong>Type :</strong> <?= htmlspecialchars($next['type_match']) ?></p>
        <p><strong>Lieu :</strong> <?= htmlspecialchars($next['lieu'] ?: '–') ?></p>
      <?php else: ?>
        <p>Aucun match programmé.</p>
      <?php endif; ?>
    </div>
  </div>

  <p style="text-align:center;"><a href="equipe.php">← Retour à l’équipe</a></p>
</main>

<?php include 'includes/footer.php'; ?>

<!-- Script Chart.js + Datalabels -->
<script>
document.addEventListener('DOMContentLoaded', ()=> {
  // Enregistrer le plugin et forcer un backing-store 2×
  Chart.register(ChartDataLabels);
  Chart.defaults.devicePixelRatio = 2;

  const colors = ['#5cb85c','#d9534f'];

  <?php foreach ($statsDisc as $type => $sd): ?>
  new Chart(
    document.getElementById('<?= $type ?>Chart'),
    {
      type: 'pie',
      data: {
        labels: ['Victoires','Défaites'],
        datasets: [{
          data: [<?= $sd['victoires'] ?>, <?= $sd['defaites'] ?>],
          backgroundColor: colors,
           devicePixelRatio: window.devicePixelRatio,
    maintainAspectRatio: false,
        }]
      },
      options: {
        plugins: {
          // Labels internes en pourcentage
          datalabels: {
            color: '#fff',
            font: {
              size: 14,
              weight: 'bold'
            },
            formatter: (value, ctx) => {
              const sum = ctx.dataset.data.reduce((a,b)=>a+b,0) || 1;
              return Math.round(value/sum*100) + '%';
            }
          },
          // On conserve la légende globale si besoin, sinon disable
          legend: { display: false }
        },
        layout: {
          padding: 10
        }
      },
      plugins: [ChartDataLabels]
    }
  );
  <?php endforeach; ?>
});
</script>
</body>
</html>
