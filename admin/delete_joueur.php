<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require '../includes/db.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT photo FROM joueurs WHERE id = ?");
$stmt->execute([$id]);
if ($j = $stmt->fetch() and $j['photo']) {
  @unlink('../photos/' . $j['photo']);
}
$pdo->prepare("DELETE FROM joueurs WHERE id = ?")->execute([$id]);
header('Location: dashboard.php');
exit;
?>