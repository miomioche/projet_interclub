<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Club InterClubs Badminton – Arras</title>
  <!-- Votre CSS principal -->
  <link rel="stylesheet" href="css/style.css">

  <!-- Bootstrap Icons (pour les icônes bi-person-fill, bi-people-fill, bi-gender-ambiguous…) -->
  <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

</head>
<body>
  <header class="site-header">
  <div class="container header-bar">
    <a href="index.php" class="brand">
      <img src="img/bca.png" alt="Logo BCA" class="logo-bca">
      <h1>Equipe InterClubs Badminton – Arras</h1>
    </a>

  <div class="container" style="padding:0">
    
    <nav class="site-nav" aria-label="Navigation principale">
      <a href="index"class="<?= ($current_page === 'index.php') ? 'active' : '' ?>">Accueil</a>
      <a href="equipe" class="<?= ($current_page === 'joueurs.php') ? 'active' : '' ?>">Équipe</a>
      <a href="rencontres" class="<?= ($current_page === 'rencontres.php') ? 'active' : '' ?>">Rencontres</a>
      <a href="matches" class="<?= ($current_page === 'matches.php') ? 'active' : '' ?>">Matchs</a>
    </nav>
  </div>
</header>
