<?php
// Page de detail d'un article

require_once '../includes/db.php';
session_start();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = "";

// On recupere l'article + le nom du vendeur
$stmt = $pdo->prepare(
    "SELECT p.*, u.nom AS vendeur_nom, u.prenom AS vendeur_prenom 
     FROM products p 
     JOIN users u ON p.vendeur_id = u.id 
     WHERE p.id = ?"
);
$stmt->execute([$id]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    include '../includes/header.php';
    echo "<div class='message message-error'>Article introuvable.</div>";
    include '../includes/footer.php';
    exit;
}

// Ajout au panier 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_panier'])) {
    if (!isset($_SESSION['user_id'])) {
        $message = "<div class='message message-error'>Vous devez etre connecte. <a href='connexion.php'>Se connecter</a></div>";
    } elseif ($_SESSION['user_id'] == $produit['vendeur_id']) {
        $message = "<div class='message message-error'>Vous ne pouvez pas acheter votre propre article.</div>";
    } elseif ($produit['disponible'] == 0) {
        $message = "<div class='message message-error'>Cet article n'est plus disponible.</div>";
    } else {
        // On verifie qu'il n'est pas deja dans le panier
        $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $id]);

        if ($stmt->fetch()) {
            $message = "<div class='message message-info'>Cet article est deja dans votre panier.</div>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $id]);
            $message = "<div class='message message-success'>Article ajoute au panier ! <a href='panier.php'>Voir le panier</a></div>";
        }
    }
}

include '../includes/header.php';
?>

<h1>Detail de l'article</h1>

<?php echo $message; ?>

<div class="product-detail">
    <div>
        <?php if (!empty($produit['image']) && file_exists("../" . $produit['image'])): ?>
            <img src="../<?php echo htmlspecialchars($produit['image']); ?>" alt="">
        <?php else: ?>
            <img src="https://via.placeholder.com/400x500?text=Pas+d%27image" alt="">
        <?php endif; ?>
    </div>

    <div>
        <h2><?php echo htmlspecialchars($produit['titre']); ?></h2>
        <p style="font-style: italic; color: #777; font-size: 18px;">
            par <?php echo htmlspecialchars($produit['auteur']); ?>
        </p>

        <p class="prix-grand"><?php echo number_format($produit['prix'], 2); ?> €</p>

        <p><strong>Categorie :</strong> <?php echo htmlspecialchars($produit['categorie']); ?></p>
        <p><strong>Etat :</strong> <span class="etat-tag"><?php echo htmlspecialchars($produit['etat']); ?></span></p>
        <p><strong>Vendeur :</strong> <?php echo htmlspecialchars($produit['vendeur_prenom'] . ' ' . $produit['vendeur_nom']); ?></p>

        <h2 style="margin-top: 20px;">Description</h2>
        <p><?php echo nl2br(htmlspecialchars($produit['description'] ?? 'Pas de description.')); ?></p>

        <?php if ($produit['disponible'] == 1): ?>
            <form method="POST" action="" style="margin-top: 25px;">
                <button type="submit" name="ajouter_panier" class="btn">🛒 Ajouter au panier</button>
            </form>
        <?php else: ?>
            <p style="margin-top: 20px;" class="message message-error">Article vendu</p>
        <?php endif; ?>

        <p style="margin-top: 20px;">
            <a href="catalogue.php" class="btn btn-secondary">← Retour au catalogue</a>
        </p>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
