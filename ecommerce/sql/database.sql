-- Base de donnees pour le site 

-- Creation de la base
CREATE DATABASE IF NOT EXISTS ecommerce_livres CHARACTER SET utf8mb4;
USE ecommerce_livres;

-- Table des utilisateurs (acheteurs et vendeurs)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    adresse VARCHAR(200) DEFAULT NULL,
    iban VARCHAR(50) DEFAULT NULL,           -- pour vendeurs
    piece_identite VARCHAR(255) DEFAULT NULL, -- chemin du fichier (pour vendeurs)
    solde DECIMAL(10,2) DEFAULT 0.00,        -- argent disponible sur le compte
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des articles (livres) en vente
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vendeur_id INT NOT NULL,
    titre VARCHAR(150) NOT NULL,
    auteur VARCHAR(100) NOT NULL,
    description TEXT,
    categorie VARCHAR(50),       -- Roman, BD, Manga, Sciences, Jeunesse...
    etat VARCHAR(30),            -- Neuf, Bon, Correct, Use
    prix DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),          -- chemin de l'image
    disponible TINYINT(1) DEFAULT 1,  -- 1 = en vente, 0 = vendu
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vendeur_id) REFERENCES users(id)
);

-- Table du panier
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Table des commandes
-- statut: en_attente  envoye  recu
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    acheteur_id INT NOT NULL,
    vendeur_id INT NOT NULL,
    product_id INT NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    statut VARCHAR(20) DEFAULT 'en_attente',
    date_commande DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (acheteur_id) REFERENCES users(id),
    FOREIGN KEY (vendeur_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Table des notifications (pour le vendeur quand on achete)
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,        -- a qui est destinee la notif
    message VARCHAR(255) NOT NULL,
    lu TINYINT(1) DEFAULT 0,
    date_notif DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
