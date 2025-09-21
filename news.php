<?php
require_once __DIR__ . '/includes/db.php';

$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // ex: /projet_interclub

// Pagination
$perPage = 6;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$total  = (int)$pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$pages  = max(1, (int)ceil($total / $perPage));

$sql = "SELECT id, titre, contenu, auteur, cover_url, excerpt,
               DATE_FORMAT(date_publication, '%d/%m/%Y') AS d
        FROM articles
        ORDER BY date_publication DESC
        LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function fallback_excerpt($txt, $len=240){
  $t = trim(strip_tags((string)$txt));
  return mb_strlen($t,'UTF-8') <= $len ? $t : mb_strimwidth($t, 0, $len, '…', 'UTF-8');
}
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="container">
  <section class="card">
    <div class="card-body">
      <h1>Actualités</h1>

      <?php if (!$rows): ?>
        <p class="muted">Pas encore d’articles.</p>
      <?php else: ?>

        <div class="article-list">
          <?php foreach ($rows as $r): ?>
            <?php
              // Résumé : on prend excerpt s'il existe, sinon un extrait du contenu
              $summary = trim((string)($r['excerpt'] ?? ''));
              if ($summary === '') {
                $summary = fallback_excerpt($r['contenu']);
              }

              // Image: on autorise nom de fichier simple ou chemin relatif
              $thumb = trim((string)($r['cover_url'] ?? ''));
              if ($thumb !== '') {
                $thumb = (strpos($thumb, '/') === false)
                  ? $BASE . '/uploads/articles/' . $thumb
                  : $BASE . '/' . ltrim($thumb, '/');
              }
            ?>

            <article class="card article-item">
              <div class="card-body">
                <?php if ($thumb !== ''): ?>
                  <a class="article-thumb-link" href="<?= $BASE ?>/article.php?id=<?= (int)$r['id'] ?>">
                    <img class="article-thumb" src="<?= htmlspecialchars($thumb) ?>" alt="">
                  </a>
                <?php endif; ?>

                <h2 class="match-title">
                  <a href="<?= $BASE ?>/article.php?id=<?= (int)$r['id'] ?>">
                    <?= htmlspecialchars($r['titre']) ?>
                  </a>
                </h2>

                <div class="muted">
                  Le <?= htmlspecialchars($r['d']) ?>
                  <?= !empty($r['auteur']) ? ' — ' . htmlspecialchars($r['auteur']) : '' ?>
                </div>

                <p><?= htmlspecialchars($summary) ?></p>

                <div class="card-actions">
                  <a class="btn btn-primary" href="<?= $BASE ?>/article.php?id=<?= (int)$r['id'] ?>">Lire</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <?php if ($pages > 1): ?>
          <nav class="card-actions">
            <?php if ($page > 1): ?>
              <a class="btn btn-outline" href="<?= $BASE ?>/news.php?page=<?= $page-1 ?>">« Précédent</a>
            <?php endif; ?>

            <span class="muted">Page <?= $page ?> / <?= $pages ?></span>

            <?php if ($page < $pages): ?>
              <a class="btn btn-primary" href="<?= $BASE ?>/news.php?page=<?= $page+1 ?>">Suivant »</a>
            <?php endif; ?>
          </nav>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
