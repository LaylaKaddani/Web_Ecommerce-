<?php
// Page des commandes
//  L'acheteur voit ses achats et peut confirmer la reception
//  Le vendeur voit ses ventes et peut marquer comme envoye

require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = "";

//  Vendeur : marquer comme envoye 
if (isset($_GET['envoyer'])) {
    $idOrder = intval($_GET['envoyer']);
    // On verifie que c'est bien le vendeur de la commande
    $stmt = $pdo->prepare("UPDATE orders SET statut = 'envoye' WHERE id = ? AND vendeur_id = ?");
    $stmt->execute([$idOrder, $userId]);

    // Notifier l'acheteur
    $stmt = $pdo->prepare(
        "SELECT o.acheteur_id, p.titre 
         FROM orders o JOIN products p ON o.product_id = p.id 
         WHERE o.id = ?"
    );
    $stmt->execute([$idOrder]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($info) {
        $msgNotif = "Le vendeur a envoye votre livre '" . $info['titre'] . "'.";
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->execute([$info['acheteur_id'], $msgNotif]);
    }

    header("Location: commandes.php");
    exit;
}

// Acheteur : confirmer la reception
if (isset($_GET['recevoir'])) {
    $idOrder = intval($_GET['recevoir']);

    // On verifie c'est bien l'acheteur et que statut = envoye
    $stmt = $pdo->prepare(
        "SELECT vendeur_id, prix FROM orders 
         WHERE id = ? AND acheteur_id = ? AND statut = 'envoye'"
    );
    $stmt->execute([$idOrder, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // On change le statut
        $stmt = $pdo->prepare("UPDATE orders SET statut = 'recu' WHERE id = ?");
        $stmt->execute([$idOrder]);

        // Maintenant le vendeur recoit l'argent sur son solde
        $stmt = $pdo->prepare("UPDATE users SET solde = solde + ? WHERE id = ?");
        $stmt->execute([$order['prix'], $order['vendeur_id']]);

        // Notification au vendeur
        $msgNotif = "Le paiement de " . number_format($order['prix'], 2) . " € a ete credite sur votre compte.";
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->execute([$order['vendeur_id'], $msgNotif]);
    }

    header("Location: commandes.php");
    exit;
}

//Recuperation des achats par l'acheteur 
$stmt = $pdo->prepare(
    "SELECT o.*, p.titre, p.auteur, p.image, u.nom AS vendeur_nom, u.prenom AS vendeur_prenom 
     FROM orders o 
     JOIN products p ON o.product_id = p.id 
     JOIN users u ON o.vendeur_id = u.id 
     WHERE o.acheteur_id = ? 
     ORDER BY o.date_commande DESC"
);
$stmt->execute([$userId]);
$achats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recuperation des ventes par le vendeur
$stmt = $pdo->prepare(
    "SELECT o.*, p.titre, p.auteur, p.image, u.nom AS acheteur_nom, u.prenom AS acheteur_prenom, u.adresse 
     FROM orders o 
     JOIN products p ON o.product_id = p.id 
     JOIN users u ON o.acheteur_id = u.id 
     WHERE o.vendeur_id = ? 
     ORDER BY o.date_commande DESC"
);
$stmt->execute([$userId]);
$ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>Mes commandes</h1>

<!-- MES ACHATS -->
<h2>🛒 Mes achats (<?php echo count($achats); ?>)</h2>

<?php if (count($achats) === 0): ?>
    <p>Vous n'avez encore rien achete.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Livre</th>
                <th>Vendeur</th>
                <th>Prix</th>
                <th>Date</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($achats as $a): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($a['titre']); ?></strong><br>
                        <small><?php echo htmlspecialchars($a['auteur']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($a['vendeur_prenom'] . ' ' . $a['vendeur_nom']); ?></td>
                    <td><?php echo number_format($a['prix'], 2); ?> €</td>
                    <td><?php echo date('d/m/Y', strtotime($a['date_commande'])); ?></td>
                    <td>
                        <span class="statut statut-<?php echo $a['statut']; ?>">
                            <?php
                            $libelles = ['en_attente' => 'En attente d envoi', 'envoye' => 'Envoye', 'recu' => 'Recu'];
                            echo $libelles[$a['statut']];
                            ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($a['statut'] === 'envoye'): ?>
                            <a href="?recevoir=<?php echo $a['id']; ?>" class="btn btn-success"
                               onclick="return confirm('Confirmer la reception ?');">
                                Confirmer reception
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<!--  MES VENTES  -->
<h2 style="margin-top: 40px;">📦 Mes ventes (<?php echo count($ventes); ?>)</h2>

<?php if (count($ventes) === 0): ?>
    <p>Vous n'avez encore rien vendu.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Livre</th>
                <th>Acheteur</th>
                <th>Adresse de livraison</th>
                <th>Prix</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventes as $v): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($v['titre']); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($v['acheteur_prenom'] . ' ' . $v['acheteur_nom']); ?></td>
                    <td><?php echo htmlspecialchars($v['adresse'] ?? 'Non renseignee'); ?></td>
                    <td><?php echo number_format($v['prix'], 2); ?> €</td>
                    <td>
                        <span class="statut statut-<?php echo $v['statut']; ?>">
                            <?php
                            $libelles = ['en_attente' => 'A envoyer', 'envoye' => 'Envoye', 'recu' => 'Recu - Paye'];
                            echo $libelles[$v['statut']];
                            ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($v['statut'] === 'en_attente'): ?>
                            <a href="?envoyer=<?php echo $v['id']; ?>" class="btn btn-secondary"
                               onclick="return confirm('Marquer comme envoye ?');">
                                Marquer envoye
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
