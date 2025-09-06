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

/* Index rapide des joueurs (pour lier un binôme si le nom correspond) */
$players = $pdo->query("SELECT id, CONCAT(prenom,' ',nom) AS fullname FROM joueurs")->fetchAll(PDO::FETCH_ASSOC);
$nameToId = [];
foreach ($players as $p) {
  $nameToId[strtolower(trim($p['fullname']))] = (int)$p['id'];
}

/* Récupération des matchs (rich) */
$sql = "
  SELECT md.id, md.date_match, md.type_match, md.resultat, md.score, md.lieu,
         md.nom_adversaire, md.binome,
         j.id AS joueur_id, j.prenom, j.nom
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
  if (!isset($groups[$key])) {
    $groups[$key] = [
      'date'=>date('d/m/Y',$dt),
      'time'=>date('H:i',$dt),
      'lieu'=>trim($r['lieu'] ?? ''),
      'items'=>[]
    ];
  }

  /* Joueurs : lien vers le joueur + lien sur le binôme si trouvé */
  $primaryFull = fullName($r);
  $joueurs = '<a class="player-link" href="joueur.php?id='.(int)$r['joueur_id'].'">'.htmlspecialchars($primaryFull,ENT_QUOTES).'</a>';
  if (!empty($r['binome'])) {
    $bn   = trim((string)$r['binome']);
    $bnId = $nameToId[strtolower($bn)] ?? null;
    $bnHtml = $bnId
      ? '<a class="player-link" href="joueur.php?id='.$bnId.'">'.htmlspecialchars($bn,ENT_QUOTES).'</a>'
      : htmlspecialchars($bn,ENT_QUOTES);
    $joueurs .= ' & '.$bnHtml;
  }

  $groups[$key]['items'][] = [
    'id'     => (int)$r['id'],
    'type'   => $r['type_match'],
    'joueurs'=> $joueurs,
    'adv'    => $r['nom_adversaire'] ?: '—',
    'score'  => $r['score'] ?: '—',
    'res'    => $r['resultat'] ?: '',
  ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Matchs</title>
  <link rel="stylesheet" href="style.css"><!-- si ton CSS est à la racine ; sinon garde css/style.css -->
  <style>
    /* conteneur */
    .matches-wrap{ width:90%; max-width:1100px; margin:2rem auto; }
    .matches-title{ font-size:2rem; margin:0 0 1.25rem; }

    /* carte de rencontre (séparation visuelle nette) */
    .meet-card{
      background:#fff; border:1px solid #e8ecf1; border-radius:12px;
      box-shadow:0 6px 16px rgba(0,0,0,.06); margin:1.2rem 0; overflow:hidden;
    }
    .meet-header{
      display:flex; gap:10px; align-items:center; justify-content:space-between;
      padding:.75rem 1rem; background:#f8f9fb; border-bottom:1px solid #e8ecf1;
      font-weight:700;
    }
    .meet-meta{ color:#6b7280; font-weight:600; font-size:.92rem; }
    .meet-count{ color:#1f3b66; font-weight:800; }

    /* table */
    .matches-table{ width:100%; border-collapse:collapse; background:#fff; }
    .matches-table th, .matches-table td{ padding:.65rem .85rem; border-top:1px solid #f0f2f5; }
    .matches-table thead th{
      background:#f8f9fb; text-transform:uppercase; font-size:.78rem; letter-spacing:.02em; text-align:left;
    }
    .matches-table tbody tr:hover{ background:#f9fbff; } /* hover demandé */
    .type-col{ width:120px; white-space:nowrap; }
    .score-col{ white-space:nowrap; font-variant-numeric:tabular-nums; }
    .tick-col{ width:60px; text-align:center; }

    /* surlignage de la ligne ciblée (deep-link) */
    tr.highlight{ background:#fff9c4 !important; box-shadow: inset 0 0 0 2px #f4d03f; }

    /* petit ajustement de lien joueur si le style global n’est pas chargé */
    .player-link{ border-bottom:1px dotted rgba(0,0,0,.25); text-decoration:none; }
    .player-link:hover{ border-bottom-color:#B61D3C; color:#B61D3C; text-decoration:none; }
  </style>
</head>

<!-- Bouton retour en haut -->
<button id="backToTop" title="Retour en haut">
  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 15l8-8 8 8H4z"/></svg>
</button>
<script>
  // Affiche / masque le bouton
  window.addEventListener('scroll', () => {
    const btn = document.getElementById('backToTop');
    btn.style.display = (document.documentElement.scrollTop > 200) ? 'flex' : 'none';
  });
  // Scroll smooth
  document.addEventListener('DOMContentLoaded', ()=>{
    document.getElementById('backToTop').onclick = () =>
      window.scrollTo({ top: 0, behavior: 'smooth' });
  });
</script>

<?php include __DIR__.'/includes/header.php'; ?>

<main class="matches-wrap">
  <h1 class="matches-title">Détails des Matchs</h1>

  <?php if (empty($groups)): ?>
    <p>Aucun match trouvé.</p>
  <?php else: ?>
    <?php foreach ($groups as $g): ?>
      <section class="meet-card">
        <header class="meet-header">
          <div>
            <?= htmlspecialchars($g['date']) ?> à <?= htmlspecialchars($g['time']) ?>
            <?php if ($g['lieu']!==''): ?>
              <span class="meet-meta">— <?= htmlspecialchars($g['lieu']) ?></span>
            <?php endif; ?>
          </div>
          <div class="meet-count"><?= count($g['items']) ?> match<?= count($g['items'])>1?'s':'' ?></div>
        </header>

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
              $r     = function_exists('mb_strtolower') ? mb_strtolower($it['res'],'UTF-8') : strtolower($it['res']);
              $scoreCls = ($r==='victoire') ? 'color:#0f7a3a;font-weight:700;' : (($r==='défaite'||$r==='defaite')?'color:#c0342d;font-weight:700;':'');
            ?>
            <tr id="<?= $rowId ?>" class="<?= $clsHL ?>">
              <td class="type-col"><?= renderTypeIcon($it['type']) ?></td>
              <td><?= $it['joueurs'] /* déjà échappé + liens */ ?></td>
              <td><?= htmlspecialchars($it['adv']) ?></td>
              <td class="score-col"><span style="<?= $scoreCls ?>"><?= htmlspecialchars($it['score']) ?></span></td>
              <td class="tick-col"><?= renderTick($it['res']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<?php include __DIR__.'/includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
  /* Animation sur la ligne ciblée */
  const hl = document.querySelector('tr.highlight');
  if (hl) {
    hl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    hl.animate(
      [
        { boxShadow: 'inset 0 0 0 2px #f4d03f', offset: 0 },
        { boxShadow: 'inset 0 0 0 4px #f4d03f', offset: .5 },
        { boxShadow: 'inset 0 0 0 2px #f4d03f', offset: 1 }
      ],
      { duration: 800 }
    );
  }
});
</script>
</body>
</html>
