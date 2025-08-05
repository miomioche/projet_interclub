<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require '../includes/db.php';

$joueurs = $pdo->query("SELECT id, prenom, nom FROM joueurs")->fetchAll();
$rencontres = $pdo->query("SELECT id, adversaire FROM rencontres")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $stmt = $pdo->prepare("INSERT INTO match_details (joueur_id, rencontre_id, nom_adversaire, score, type_match, resultat) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->execute([
    $_POST['joueur_id'], $_POST['rencontre_id'], $_POST['nom_adversaire'],
    $_POST['score'], $_POST['type_match'], $_POST['resultat']
  ]);
  header('Location: dashboard.php');
  exit;
}
?>
<form method="post">
  <select name="joueur_id"><?php foreach ($joueurs as $j): ?><option value="<?= $j['id'] ?>"><?= $j['prenom'] ?> <?= $j['nom'] ?></option><?php endforeach; ?></select>
  <select name="rencontre_id"><?php foreach ($rencontres as $r): ?><option value="<?= $r['id'] ?>"><?= $r['adversaire'] ?></option><?php endforeach; ?></select>
  <input name="nom_adversaire" required>
  <input name="score" required>
  <select name="type_match"><option value="simple">Simple</option><option value="double">Double</option><option value="mixte">Mixte</option></select>
  <select name="resultat"><option value="victoire">Victoire</option><option value="défaite">Défaite</option></select>
  <button>Ajouter</button>
</form>