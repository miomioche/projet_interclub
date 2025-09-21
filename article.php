<?php
require_once __DIR__ . '/includes/db.php';

$BASE = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // ex: /projet_interclub
$id   = (int)($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(404); exit('Article introuvable'); }

$sql = "SELECT id, titre, contenu, auteur, cover_url, excerpt,
               DATE_FORMAT(date_publication, '%d/%m/%Y à %Hh%i') AS publie_le
        FROM articles
        WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) { http_response_code(404); exit('Article introuvable'); }
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="container">
  <section class="card article-card">
    <div class="card-body">


      <h1><?= htmlspecialchars($article['titre']) ?></h1>

      <div class="muted article-meta">
        Publié le <?= htmlspecialchars($article['publie_le']) ?>
        <?= !empty($article['auteur']) ? ' — par ' . htmlspecialchars($article['auteur']) : '' ?>
      </div>

      <?php if (!empty($article['excerpt'])): ?>
        <p class="article-excerpt"><strong><?= htmlspecialchars($article['excerpt']) ?></strong></p>
      <?php endif; ?>

      <?php
        // Gestion de l'image de couverture optionnelle
        $src = trim((string)($article['cover_url'] ?? ''));
        if ($src !== '') {
          $src = (strpos($src, '/') === false)
            ? $BASE . '/uploads/articles/' . $src
            : $BASE . '/' . ltrim($src, '/');
        }
      ?>
      <?php if ($src !== ''): ?>
        <div class="article-cover">
          <img src="<?= htmlspecialchars($src) ?>" alt="">
        </div>
      <?php endif; ?>

      <article class="article-content">
        <?= nl2br(htmlspecialchars((string)$article['contenu'])) ?>
      </article>

      <div class="card-actions">
        <a class="btn btn-outline" href="<?= $BASE ?>/news.php">← Retour aux actualités</a>
      </div>
    </div>
  </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
