<?php
// Connexion a la base de donnees

$host = "localhost";
$dbname = "ecommerce_livres";
$user = "root";       // utilisateur par defaut sur XAMPP / WAMP
$pass = "";           // mot de passe vide par defaut

try {
    // PDO permet de communiquer avec MySQL de maniere securisee
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );
    // En cas d'erreur, on lance une exception pour le debogage
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion a la base : " . $e->getMessage());
}
?>
