<?php
declare(strict_types=1);

// ===== PROD (OVH) =====
const DB_HOST = 'bcaintmiochce.mysql.db';   // vu dans ton phpMyAdmin
const DB_NAME = 'bcaintmiochce';            // le nom de la base (généralement identique à l'user)
const DB_USER = 'bcaintmiochce';            // ton utilisateur (même nom que la base sur OVH)
const DB_PASS = 'Leelitdansmonlit1';         // celui que tu as choisi ou réinitialisé

// ===== DEV (local) =====
const DEV_HOST = '127.0.0.1';
const DEV_NAME = 'interclubs';
const DEV_USER = 'root';
const DEV_PASS = '';

$isDev = isset($_SERVER['SERVER_NAME']) && (
    $_SERVER['SERVER_NAME'] === '127.0.0.1' ||
    strpos($_SERVER['SERVER_NAME'], 'localhost') !== false
);

$host = $isDev ? DEV_HOST : DB_HOST;
$name = $isDev ? DEV_NAME : DB_NAME;
$user = $isDev ? DEV_USER : DB_USER;
$pass = $isDev ? DEV_PASS : DB_PASS;

$dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    if ($isDev) {
        die('Erreur de connexion DB: ' . $e->getMessage());
    }
    error_log('DB CONNECT ERROR: ' . $e->getMessage());
    http_response_code(500);
    exit('Erreur serveur.');
}