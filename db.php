<?php
// includes/db.php

// 1) paramètres de connexion
$host   = '127.0.0.1';
$dbname = 'interclubs';    // le nom de ta base
$user   = 'root';         // utilisateur par défaut sous XAMPP/WAMP
$pass   = '';             // mot de passe vide sous XAMPP/WAMP

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    // 2) instanciation PDO
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // si échec de connexion, on coupe tout
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$pdo = new PDO('mysql:host=localhost;dbname=interclubs;charset=utf8', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
