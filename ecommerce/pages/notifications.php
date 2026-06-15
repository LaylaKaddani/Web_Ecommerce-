<?php
// Page des notifications du vendeur et de l'acheteur

require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}

$userId = $_SESSION['user_id'];

// On marque toutes les notifications comme lues a l'ouverture de la page
$stmt = $pdo->prepare("UPDATE notifications SET lu = 1 WHERE user_id = ?");
$stmt->execute([$userId]);

// On recupere toutes les notifications de l'utilisateur
$stmt = $pdo->prepare(
    "SELECT * FROM notifications WHERE user_id = ? ORDER BY date_notif DESC"
);
$stmt->execute([$userId]);
$notifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>Mes notifications</h1>

<?php if (count($notifs) === 0): ?>
    <div class="message message-info">Vous n'avez aucune notification.</div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notifs as $n): ?>
                <tr>
                    <td><?php echo date('d/m/Y H:i', strtotime($n['date_notif'])); ?></td>
                    <td><?php echo htmlspecialchars($n['message']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
