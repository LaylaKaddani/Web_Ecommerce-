<?php
// Header commun a toutes les pages
// On demarre la session si ce n'est pas deja fait

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// On verifie si l'utilisateur est connecte
$estConnecte = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LivresOcc - Achat et vente de livres</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="header-content">
        <a href="accueil.php" class="logo">📚 LivresOcc</a>

        <!-- Menu de navigation -->
        <nav class="nav">
            <a href="accueil.php">Accueil</a>
            <a href="catalogue.php">Catalogue</a>

            <?php if ($estConnecte): ?>
                <!-- Liens visibles seulement si on est connecte -->
                <a href="ajout_article.php">Vendre un livre</a>
                <a href="panier.php">Panier</a>
                <a href="commandes.php">Mes commandes</a>

                <!-- Menu deroulant pour le compte -->
                <div class="dropdown">
                    <button class="dropdown-btn">
                        Mon compte ▼
                    </button>
                    <div class="dropdown-content">
                        <a href="profil.php">Profil</a>
                        <a href="recharge.php">Recharger le solde</a>
                        <a href="notifications.php">Notifications</a>
                        <a href="logout.php">Deconnexion</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="connexion.php">Connexion</a>
                <a href="inscription.php">Inscription</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<!-- Conteneur principal pour le contenu de chaque page -->
<main class="main-content">
