<?php
require 'includes/db.php';

// AJOUT
if (!empty($_POST['add_nom'])) {
    $nom = trim($_POST['add_nom']);
    if ($nom) {
        $stmt = $pdo->prepare("INSERT INTO adversaires (nom) VALUES (?)");
        $stmt->execute([$nom]);
        header('Location: adversaires.php');
        exit;
    }
}

// MODIF
if (!empty($_POST['edit_id']) && !empty($_POST['edit_nom'])) {
    $id = (int)$_POST['edit_id'];
    $nom = trim($_POST['edit_nom']);
    if ($nom) {
        $stmt = $pdo->prepare("UPDATE adversaires SET nom=? WHERE id=?");
        $stmt->execute([$nom, $id]);
        header('Location: adversaires.php');
        exit;
    }
}

// SUPPR
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM adversaires WHERE id=?");
    $stmt->execute([$id]);
    header('Location: adversaires.php');
    exit;
}

// Récupérer tous les adversaires
$adversaires = $pdo->query("SELECT * FROM adversaires ORDER BY nom")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des adversaires</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h1>Gestion des adversaires</h1>

<!-- Formulaire d'ajout -->
<form method="post" style="margin-bottom:20px;">
    <input type="text" name="add_nom" placeholder="Nom de l'adversaire" required>
    <button type="submit">Ajouter</button>
</form>

<!-- Tableau des adversaires -->
<table>
    <tr>
        <th>ID</th>
        <th>Nom</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($adversaires as $adv): ?>
        <tr>
            <td><?= $adv['id'] ?></td>
            <td>
                <!-- Formulaire inline de modification -->
                <form method="post" style="display:inline;">
                    <input type="hidden" name="edit_id" value="<?= $adv['id'] ?>">
                    <input type="text" name="edit_nom" value="<?= htmlspecialchars($adv['nom']) ?>" required>
                    <button type="submit">💾</button>
                </form>
            </td>
            <td>
                <a href="?delete=<?= $adv['id'] ?>" onclick="return confirm('Supprimer cet adversaire ?')">🗑️</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<p><a href="index.php">← Retour accueil</a></p>
</body>
</html>
