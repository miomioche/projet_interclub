<?php
require_once __DIR__ . '/includes/db.php';
if (!isset($pdo) || !($pdo instanceof PDO)) { http_response_code(500); exit('DB non initialisée'); }

$TEAM_ID = 1;
$SEASON  = '2024-2025';

// Prochaine rencontre
$sqlNext = "
  SELECT f.id, f.date_time, f.venue_name, f.venue_city,
         th.short_name AS home_short, ta.short_name AS away_short,
         th.name AS home_name, ta.name AS away_name,
         f.competition, f.matchday
  FROM fixtures f
  JOIN teams th ON th.id = f.home_team_id
  JOIN teams ta ON ta.id = f.away_team_id
  WHERE f.season = :season
    AND f.home_team_id = :tid
    AND f.status = 'scheduled'
    AND f.date_time > NOW()
  ORDER BY f.date_time ASC
  LIMIT 1";
$stNext = $pdo->prepare($sqlNext);
$stNext->execute([':season'=>$SEASON, ':tid'=>$TEAM_ID]);
$next = $stNext->fetch(PDO::FETCH_ASSOC);

// Dernier résultat
$sqlLast = "
  SELECT f.id, f.date_time, f.venue_name, f.venue_city,
         th.short_name AS home_short, ta.short_name AS away_short,
         th.name AS home_name, ta.name AS away_name,
         f.score_home, f.score_away, f.competition, f.matchday
  FROM fixtures f
  JOIN teams th ON th.id = f.home_team_id
  JOIN teams ta ON ta.id = f.away_team_id
  WHERE f.season = :season
    AND f.home_team_id = :tid
    AND f.status = 'played'
    AND f.date_time <= NOW()
  ORDER BY f.date_time DESC
  LIMIT 1";
$stLast = $pdo->prepare($sqlLast);
$stLast->execute([':season'=>$SEASON, ':tid'=>$TEAM_ID]);
$last = $stLast->fetch(PDO::FETCH_ASSOC);

// Dernier article
$sqlNews = "
  SELECT id, titre, contenu,
         DATE_FORMAT(date_publication, '%d/%m/%Y à %Hh%i') AS publie_le,
         auteur
  FROM articles
  ORDER BY date_publication DESC
  LIMIT 1";
$article = $pdo->query($sqlNews)->fetch(PDO::FETCH_ASSOC);

function excerpt($txt, $len=180){
  $t = trim(strip_tags((string)$txt));
  if (mb_strlen($t,'UTF-8') <= $len) return $t;
  return mb_strimwidth($t, 0, $len, '…', 'UTF-8');
}
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<div class="container no-stretch">


<!-- ===== HERO ===== -->
<section class="hero">
  <img src="img/teambanner.jpeg" alt="Équipe InterClubs" class="hero-img">
  <div class="hero-overlay">
    <div class="hero-content">
      <h1>Équipe InterClubs<br>Badminton Arras</h1>
      <p>Passion — Esprit d’équipe — After</p>
      <a href="rencontres.php" class="btn btn-primary btn-lg">Voir la prochaine rencontre</a>
    </div>
  </div>
</section>

  <!-- Bloc central : Résultat + Actualités -->
  <section class="home-central">

    <!-- Dernier Résultat -->
    <div class="card result-card">
      <div class="card-body">
        <h2 class="section-title">Dernier Résultat</h2>
        <?php if ($last): ?>
          <div class="result-line">
            <span class="team"><?= htmlspecialchars($last['home_name']) ?></span>
            <span class="vs">vs</span>
            <span class="team"><?= htmlspecialchars($last['away_name']) ?></span>
            <span class="score-badge">
              <?= (int)$last['score_home'] ?> — <?= (int)$last['score_away'] ?>
            </span>
          </div>
          <div class="meta">
            <div class="meta-item">📍 <?= htmlspecialchars($last['venue_name']) ?> — <?= htmlspecialchars($last['venue_city']) ?></div>
            <div class="meta-item">🏷️ <?= htmlspecialchars($last['competition']) ?> • J<?= (int)$last['matchday'] ?></div>
          </div>
        <?php else: ?>
          <p class="muted">Aucun résultat disponible.</p>
        <?php endif; ?>
        <div class="card-actions">
      <a class="btn btn-primary" href="rencontres">Voir le calendrier complet</a>
      <a class="btn btn-primary" href="matches">Voir tous les matchs</a>
    </div>
      </div>
    </div>

    <!-- Actualités -->
    <div class="card news-card">
      <div class="card-body">
        <h2 class="section-title">Actualités du Club</h2>
        <?php if ($article): ?>
          <div class="muted">
            Publié le <?= htmlspecialchars($article['publie_le']) ?>
            <?php if ($article['auteur']): ?> — par <?= htmlspecialchars($article['auteur']) ?><?php endif; ?>
          </div>
          <h3 class="match-title"><?= htmlspecialchars($article['titre']) ?></h3>
          <p><?= htmlspecialchars(excerpt($article['contenu'])) ?></p>
          <div class="card-actions">
            <a class="btn btn-primary" href="article.php?id=<?= (int)$article['id'] ?>">Lire l’article</a>
<a class="btn btn-primary" href="news.php">Toutes les actualités</a>
          </div>
        <?php else: ?>
          <p class="muted">Aucune actualité pour le moment.</p>
        <?php endif; ?>
      </div>
    </div>

  </section>
<section class="cards-row">
  <div class="info-card">
    <h3>👥 Notre équipe</h3>
    <p class="muted">Découvrez les joueuses et joueurs qui défendent nos couleurs.</p>
    <a href="equipe" class="btn btn-primary <?= ($current_page === 'joueurs.php') ? 'active' : '' ?>">Équipe</a>
  </div>

  <div class="info-card">
    <h3>📅 Rencontres</h3>
    <p class="muted">Calendrier & résultats officiels de notre poule.</p>
    <a href="rencontres"class="btn btn-primary <?= ($current_page === 'rencontres.php') ? 'active' : '' ?>">Voir le Calendrier</a>
  </div>

  <div class="info-card">
    <h3>📊 Matchs</h3>
    <p class="muted">Historique détaillé des matchs de la saison.</p>
    <a href="matches" 
   class="btn btn-primary <?= ($current_page === 'matches.php') ? 'active' : '' ?>">Voir les matchs</a>
  </div>
</section>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
