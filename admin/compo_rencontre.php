<?php
require 'includes/db.php';

$rencontre_id = $_GET['id'] ?? null;
if (!$rencontre_id) die("Rencontre invalide.");

// Liste des joueurs
$joueurs = $pdo->query("SELECT id, prenom, nom FROM joueurs ORDER BY nom")->fetchAll();

$types = [
    'Simple Homme', 'Simple Dame',
    'Double Hommes', 'Double Dames', 'Double Mixte'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On efface l’ancienne compo
    $pdo->prepare("DELETE FROM matchs_rencontres WHERE rencontre_id = ?")->execute([$rencontre_id]);

    foreach ($types as $i => $type) {
        $j1 = $_POST["joueur1_$i"] ?? null;
        $j2 = $_POST["joueur2_$i"] ?? null;
        $score = $_POST["score_$i"] ?? null;

        if ($j1) {
            $stmt = $pdo->prepare("INSERT INTO matchs_rencontres (rencontre_id, type_match, joueur1_id, joueur2_id, score) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$rencontre_id, $type, $j1, $j2 ?: null, $score]);
        }
    }

    header("Location: rencontres.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Composition Rencontre</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h1>Composition de l’équipe pour la rencontre #<?= htmlspecialchars($rencontre_id) ?></h1>
<form method="post">
    <?php foreach ($types as $i => $type): ?>
        <fieldset>
            <legend><?= $type ?></legend>

            <label>Joueur 1 :
                <select name="joueur1_<?= $i ?>">
                    <option value="">—</option>
                    <?php foreach ($joueurs as $j): ?>
                        <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['prenom'] . ' ' . $j['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label><br>

            <label>Joueur 2 :
                <select name="joueur2_<?= $i ?>">
                    <option value="">—</option>
                    <?php foreach ($joueurs as $j): ?>
                        <option value="<?= $j['id'] ?>"><?= htmlspecialchars($j['prenom'] . ' ' . $j['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label><br>

            <label>Score :
                <input type="text" name="score_<?= $i ?>">
            </label>
        </fieldset><br>
    <?php endforeach; ?>
    <button type="submit">Enregistrer la composition</button>
</form>
</body>
</html>
