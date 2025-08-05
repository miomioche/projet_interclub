<?php
require 'includes/db.php';

$joueur_id = $_GET['id'] ?? 0;

// Statistiques principales
$stmt = $pdo->prepare("SELECT COUNT(*) AS matchs_joues FROM matchs WHERE joueur1_id = ? OR joueur2_id = ?");
$stmt->execute([$joueur_id, $joueur_id]);
$stats = $stmt->fetch();

// Liste des adversaires battus (exemple si tu as la colonne 'vainqueur_id' dans matchs)
$stmt = $pdo->prepare("SELECT DISTINCT IF(joueur1_id = ?, joueur2_id, joueur1_id) AS adversaire_id
                       FROM matchs WHERE (joueur1_id = ? OR joueur2_id = ?) AND vainqueur_id = ?");
$stmt->execute([$joueur_id, $joueur_id, $joueur_id, $joueur_id]);
$adversaires = $stmt->fetchAll();

$adversaire_noms = [];
foreach ($adversaires as $a) {
    if ($a['adversaire_id']) {
        $nom_stmt = $pdo->prepare("SELECT prenom, nom FROM joueurs WHERE id = ?");
        $nom_stmt->execute([$a['adversaire_id']]);
        $n = $nom_stmt->fetch();
        if ($n) {
            $adversaire_noms[] = htmlspecialchars($n['prenom'] . ' ' . $n['nom']);
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<h1>Statistiques du joueur</h1>

<p>Nombre de matchs joués : <strong><?= $stats['matchs_joues'] ?></strong></p>

<!-- CONSEIL AJOUTÉ : Affichage adversaires battus -->
<?php if ($adversaire_noms): ?>
    <p>Adversaires battus : <?= implode(', ', $adversaire_noms) ?></p>
<?php else: ?>
    <p>Aucun adversaire battu (ou stat indisponible)</p>
<?php endif; ?>

<!-- CONSEIL BONUS : Évolution dans le temps (affichage simplifié) -->
<h3>Évolution classement simple</h3>
<ul>
<?php
$stmt = $pdo->prepare("SELECT date_classement, simple FROM classements WHERE joueur_id = ? ORDER BY date_classement ASC");
$stmt->execute([$joueur_id]);
while ($row = $stmt->fetch()):
?>
    <li><?= $row['date_classement'] ?> : <?= $row['simple'] ?></li>
<?php endwhile; ?>
</ul>
<?php include 'includes/footer.php'; ?>
