<?php
// Clés CinetPay
define('API_KEY', 'VOTRE_API_KEY_ICI');
define('SITE_ID', 'VOTRE_SITE_ID_ICI');

// Connexion à la base de données
$host = 'localhost';
$db = 'smtalentetoile';
$user = 'root';
$pass = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>