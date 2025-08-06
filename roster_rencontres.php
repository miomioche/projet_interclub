<?php
require 'includes/db.php';

$rencontres = $pdo->query("SELECT * FROM rencontres ORDER BY date_rencontre DESC")->fetchAll();
?>

<?php include 'includes/header.php'; ?>
<h1>Rosters des rencontres</h1>

<?php foreach ($rencontres as $r): ?>
    <div class="rencontre">
        <h2><?= htmlspecialchars($r['date_rencontre']) ?> vs <?= htmlspecialchars($r['adversaire']) ?></h2>
        <p><strong>Lieu :</strong> <?= htmlspecialchars($r['lieu']) ?></p>
        <p><strong>Score :</strong> <?= htmlspecialchars($r['score']) ?: 'À venir' ?></p>

        <!-- Affichage composition d'équipe (si existante) -->
        <h4>Composition d’équipe :</h4>
        <ul>
        <?php
        $stmt = $pdo->prepare("SELECT m.type_match, j1.prenom AS j1_prenom, j1.nom AS j1_nom, j2.prenom AS j2_prenom, j2.nom AS j2_nom
                               FROM matchs m
                               LEFT JOIN joueurs j1 ON m.joueur1_id = j1.id
                               LEFT JOIN joueurs j2 ON m.joueur2_id = j2.id
                               WHERE m.rencontre_id = ?");
        $stmt->execute([$r['id']]);
        $compo = $stmt->fetchAll();
        foreach ($compo as $m):
        ?>
            <li>
                <strong><?= htmlspecialchars($m['type_match']) ?> :</strong>
                <?= htmlspecialchars($m['j1_prenom'] . ' ' . $m['j1_nom']) ?>
                <?php if ($m['j2_nom']): ?>
                    & <?= htmlspecialchars($m['j2_prenom'] . ' ' . $m['j2_nom']) ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>

        <!-- CONSEIL AJOUTÉ : Bouton pour modifier la composition -->
        <a href="compo_rencontre.php?rencontre_id=<?= $r['id'] ?>">📝 Modifier la compo</a>
        <hr>
    </div>
<?php endforeach; ?>

<?php include 'includes/footer.php'; ?>

<?php
require 'includes/db.php';

$rencontres = $pdo->query("SELECT * FROM rencontres ORDER BY date_rencontre DESC")->fetchAll();
?>

<?php include 'includes/header.php'; ?>
<h1>Rosters des rencontres</h1>

<?php foreach ($rencontres as $r): ?>
    <div class="rencontre">
        <h2><?= htmlspecialchars($r['date_rencontre']) ?> vs <?= htmlspecialchars($r['adversaire']) ?></h2>
        <p><strong>Lieu :</strong> <?= htmlspecialchars($r['lieu']) ?></p>
        <p><strong>Score :</strong> <?= htmlspecialchars($r['score']) ?: 'À venir' ?></p>

        <!-- Affichage composition d'équipe (si existante) -->
        <h4>Composition d’équipe :</h4>
        <ul>
        <?php
        $stmt = $pdo->prepare("SELECT m.type_match, j1.prenom AS j1_prenom, j1.nom AS j1_nom, j2.prenom AS j2_prenom, j2.nom AS j2_nom
                               FROM matchs m
                               LEFT JOIN joueurs j1 ON m.joueur1_id = j1.id
                               LEFT JOIN joueurs j2 ON m.joueur2_id = j2.id
                               WHERE m.rencontre_id = ?");
        $stmt->execute([$r['id']]);
        $compo = $stmt->fetchAll();
        foreach ($compo as $m):
        ?>
            <li>
                <strong><?= htmlspecialchars($m['type_match']) ?> :</strong>
                <?= htmlspecialchars($m['j1_prenom'] . ' ' . $m['j1_nom']) ?>
                <?php if ($m['j2_nom']): ?>
                    & <?= htmlspecialchars($m['j2_prenom'] . ' ' . $m['j2_nom']) ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
        </ul>

        <!-- CONSEIL AJOUTÉ : Bouton pour modifier la composition -->
        <a href="compo_rencontre.php?rencontre_id=<?= $r['id'] ?>">📝 Modifier la compo</a>
        <hr>
    </div>
<?php endforeach; ?>

<?php include 'includes/footer.php'; ?>
