<?php
// Page panier : voir, supprimer, et acheter les articles

require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = "";

//  Suppression d'un article du panier 
if (isset($_GET['supprimer'])) {
    $idCart = intval($_GET['supprimer']);
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$idCart, $userId]);
    header("Location: panier.php");
    exit;
}

// Achat de tous les articles du panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acheter'])) {

    //  On recupere le solde actuel
    $stmt = $pdo->prepare("SELECT solde FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $solde = $stmt->fetch(PDO::FETCH_ASSOC)['solde'];

    //  On recupere tous les articles du panier celle encore disponibles
    $stmt = $pdo->prepare(
        "SELECT p.id, p.prix, p.titre, p.vendeur_id, c.id AS cart_id 
         FROM cart c 
         JOIN products p ON c.product_id = p.id 
         WHERE c.user_id = ? AND p.disponible = 1"
    );
    $stmt->execute([$userId]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //  On calcule le total
    $total = 0;
    foreach ($articles as $a) {
        $total += $a['prix'];
    }

    //  On verifie le solde
    if ($total == 0) {
        $message = "<div class='message message-error'>Votre panier est vide.</div>";
    } elseif ($solde < $total) {
        $message = "<div class='message message-error'>
            Solde insuffisant. Total : " . number_format($total, 2) . " € - 
            Solde : " . number_format($solde, 2) . " €. 
            <a href='recharge.php'>Recharger</a>
        </div>";
    } else {
        //  On retire le montant du solde de l'acheteur
        $stmt = $pdo->prepare("UPDATE users SET solde = solde - ? WHERE id = ?");
        $stmt->execute([$total, $userId]);

        // Pour chaque article : creer la commande, marquer indispo, notifier vendeur
        foreach ($articles as $a) {
            // Creer la commande
            $stmt = $pdo->prepare(
                "INSERT INTO orders (acheteur_id, vendeur_id, product_id, prix) 
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$userId, $a['vendeur_id'], $a['id'], $a['prix']]);

            // Marquer le produit indisponible
            $stmt = $pdo->prepare("UPDATE products SET disponible = 0 WHERE id = ?");
            $stmt->execute([$a['id']]);

            // Notification au vendeur
            $msgNotif = "Votre livre '" . $a['titre'] . "' a ete achete !";
            $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt->execute([$a['vendeur_id'], $msgNotif]);
        }

        // On vide le panier
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);

        $message = "<div class='message message-success'>Achat effectue ! <a href='commandes.php'>Voir mes commandes</a></div>";
    }
}

//  Recuperation du panier pour affichage 
$stmt = $pdo->prepare(
    "SELECT p.*, c.id AS cart_id 
     FROM cart c 
     JOIN products p ON c.product_id = p.id 
     WHERE c.user_id = ?"
);
$stmt->execute([$userId]);
$panier = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total
$total = 0;
foreach ($panier as $item) {
    $total += $item['prix'];
}

// Solde de l'utilisateur
$stmt = $pdo->prepare("SELECT solde FROM users WHERE id = ?");
$stmt->execute([$userId]);
$solde = $stmt->fetch(PDO::FETCH_ASSOC)['solde'];

include '../includes/header.php';
?>

<h1>Mon panier</h1>

<?php echo $message; ?>

<div class="solde-box">
    Solde disponible : <span class="montant"><?php echo number_format($solde, 2); ?> €</span>
</div>

<?php if (count($panier) === 0): ?>
    <div class="message message-info">
        Votre panier est vide. <a href="catalogue.php">Voir le catalogue</a>
    </div>
<?php else: ?>

    <table>
        <thead>
            <tr>
                <th>Livre</th>
                <th>Etat</th>
                <th>Prix</th>
                <th>Disponibilite</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($panier as $item): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($item['titre']); ?></strong><br>
                        <small><?php echo htmlspecialchars($item['auteur']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($item['etat']); ?></td>
                    <td><?php echo number_format($item['prix'], 2); ?> €</td>
                    <td>
                        <?php if ($item['disponible'] == 1): ?>
                            <span style="color: green;">✓ Disponible</span>
                        <?php else: ?>
                            <span style="color: red;">✗ Vendu</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?supprimer=<?php echo $item['cart_id']; ?>" class="btn btn-danger">Supprimer</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: right;">
        <h2>Total : <?php echo number_format($total, 2); ?> €</h2>

        <form method="POST" action="" style="margin-top: 15px;">
            <button type="submit" name="acheter" class="btn btn-success" 
                    onclick="return confirm('Confirmer l achat ?');">
                💳 Proceder a l'achat
            </button>
        </form>
    </div>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>
