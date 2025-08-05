<?php
session_start();
if (!isset($_SESSION['admin'])) { header('Location: login.php'); exit; }
require '../includes/db.php';

$id = intval($_GET['id'] ?? 0);
$pdo->prepare("DELETE FROM match_details WHERE id = ?")->execute([$id]);
header('Location: dashboard.php');
exit;
?>