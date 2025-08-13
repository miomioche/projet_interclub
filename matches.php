<?php
declare(strict_types=1);
require __DIR__ . '/includes/db.php';

/* ID à surligner quand on arrive depuis une pastille */
$highlightId = isset($_GET['match_id']) ? (int)$_GET['match_id'] : 0;

/* Helpers */
function renderTypeIcon(string $type): string {
  $t = function_exists('mb_strtolower') ? mb_strtolower(trim($type), 'UTF-8') : strtolower(trim($type));
  return match ($t) {
    'simple' => '🏸 Simple',
    'double' => '👥 Double',
    'mixte'  => '⚤ Mixte',
    default  => htmlspecialchars($type, ENT_QUOTES, 'UTF-8'),
  };
}
function fullName(array $r): string { return trim(($r['prenom'] ?? '').' '.($r['nom'] ?? '')); }
function renderTick(string $res): string {
  $r = function_exists('mb_strtolower') ? mb_strtolower($res,'UTF-8') : strtolower($res);
  if ($r === 'victoire') return '<span style="color:#0f7a3a;font-weight:700;">✓</span>';
  if ($r === 'défaite' || $r === 'defaite') return '<span style="color:#c0342d;font-weight:700;">✗</span>';
  return htmlspecialchars($res, ENT_QUOTES, 'UTF-8');
}

/* Récupération des matchs (rich) */
$sql = "
  SELECT md.id, md.date_match, md.type_match, md.resultat, md.score, md.lieu,
         md.nom_adversaire, md.binome,
         j.prenom, j.nom
  FROM match_details md
  JOIN joueurs j ON j.id = md.joueur_id
  ORDER BY md.date_match DESC, FIELD(md.type_match,'simple','double','mixte'), md.id
";
$rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

/* Grouper par “date/heure + lieu” */
$groups = [];
foreach ($rows as $r) {
  $dt = strtotime($r['date_match']);
  $key = date('d/m/Y H:i', $dt).'|'.trim($r['lieu'] ?? '');
  if (!isset($groups[$key])) $groups[$key] = [
    'date'=>date('d/m/Y',$dt), 'time'=>date('H:i',$dt), 'lieu'=>trim($r['lieu'] ?? ''), 'items'=>[]
  ];
  $joueurs = fullName($r);
  if (!empty($r['binome'])) $joueurs .= ' & '.$r['binome'];
  $groups[$key]['items'][] = [
    'id'=>(int)$r['id'],
    'type'=>$r['type_match'],
    'joueurs'=>$joueurs,
    'adv'=>$r['nom_adversaire'] ?: '—',
    'score'=>$r['score'] ?: '—',
    'res'=>$r['resultat'] ?: '',
  ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Matchs</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .matches-wrap{ width:90%; max-width:1100px; margin:2rem auto; }
    .matches-title{ font-size:2rem; margin:0 0 1.25rem; }
    .meet-title{ font-weight:700; font-size:1.05rem; margin:1.75rem 0 .5rem; }
    .matches-table{ width:100%; border-collapse:collapse; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,.08); }
    .matches-table th, .matches-table td{ padding:.6rem .8rem; border-top:1px solid #f0f2f5; }
    .matches-table thead th{ background:#f8f9fb; text-transform:uppercase; font-size:.78rem; letter-spacing:.02em; text-align:left; }
    .type-col{ width:120px; white-space:nowrap; }
    .score-col{ white-space:nowrap; font-variant-numeric:tabular-nums; }
    .tick-col{ width:60px; text-align:center; }
    .txt-green{ color:#0f7a3a; font-weight:700; }
    .txt-red{ color:#c0342d; font-weight:700; }
    /* surlignage de la ligne ciblée */
    tr.highlight{ background:#fff9c4 !important; box-shadow: inset 0 0 0 2px #f4d03f; }
  </style>
</head>
<!-- Bouton retour en haut -->
<button id="backToTop" title="Retour en haut">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path d="M4 15l8-8 8 8H4z"/>
    </svg>
</button>

<script>
// Affiche / masque le bouton
window.onscroll = function() {
    let btn = document.getElementById("backToTop");
    if (document.documentElement.scrollTop > 200) {
        btn.style.display = "flex";
    } else {
        btn.style.display = "none";
    }
};

// Scroll vers le haut
document.getElementById("backToTop").onclick = function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
};
</script>
<?php include __DIR__.'/includes/header.php'; ?>

<main class="matches-wrap">
  <h1 class="matches-title">Détails des Matchs</h1>

  <?php if (empty($groups)): ?>
    <p>Aucun match trouvé.</p>
  <?php else: ?>
    <?php foreach ($groups as $g): ?>
      <div class="meet-title">
        <?= htmlspecialchars($g['date']) ?> à <?= htmlspecialchars($g['time']) ?>
        <?php if ($g['lieu']!==''): ?> — <?= htmlspecialchars($g['lieu']) ?><?php endif; ?>
      </div>
      <table class="matches-table">
        <thead>
          <tr>
            <th class="type-col">Type</th>
            <th>Joueur(s)</th>
            <th>Adversaire</th>
            <th class="score-col">Score par set</th>
            <th class="tick-col">✓ / ✗</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($g['items'] as $it): ?>
          <?php
            $mid   = (int)$it['id'];
            $rowId = 'row-m'.$mid;
            $clsHL = ($highlightId === $mid) ? ' highlight' : '';
            $r = function_exists('mb_strtolower') ? mb_strtolower($it['res'],'UTF-8') : strtolower($it['res']);
            $scoreCls = ($r==='victoire') ? 'txt-green' : (($r==='défaite'||$r==='defaite')?'txt-red':'');
          ?>
          <tr id="<?= $rowId ?>" class="<?= $clsHL ?>">
            <td class="type-col"><?= renderTypeIcon($it['type']) ?></td>
            <td><?= htmlspecialchars($it['joueurs']) ?></td>
            <td><?= htmlspecialchars($it['adv']) ?></td>
            <td class="score-col"><span class="<?= $scoreCls ?>"><?= htmlspecialchars($it['score']) ?></span></td>
            <td class="tick-col"><?= renderTick($it['res']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const hl = document.querySelector('tr.highlight');
  if (hl) {
    hl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    hl.animate([
      { boxShadow: 'inset 0 0 0 2px #f4d03f', offset: 0 },
      { boxShadow: 'inset 0 0 0 4px #f4d03f', offset: .5 },
      { boxShadow: 'inset 0 0 0 2px #f4d03f', offset: 1 }
    ], { duration: 800 });
  }
});
</script>
</body>
</html>
