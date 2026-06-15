<?php
// Page de recharge du solde (faux paiement par carte)

require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = "";

// Traitement du formulaire 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $montant = floatval($_POST['montant']);
    $numeroCarte = trim($_POST['numero_carte']);
    $titulaire = trim($_POST['titulaire']);
    $expiration = trim($_POST['expiration']);
    $cvv = trim($_POST['cvv']);

    // Verifications simples
    if ($montant <= 0) {
        $message = "<div class='message message-error'>Le montant doit etre superieur a 0.</div>";
    } elseif (strlen(str_replace(' ', '', $numeroCarte)) < 12) {
        $message = "<div class='message message-error'>Numero de carte invalide.</div>";
    } elseif (empty($titulaire) || empty($expiration) || empty($cvv)) {
        $message = "<div class='message message-error'>Tous les champs sont obligatoires.</div>";
    } else {
        // Faux paiement : on ajoute simplement le montant au solde
        $stmt = $pdo->prepare("UPDATE users SET solde = solde + ? WHERE id = ?");
        $stmt->execute([$montant, $userId]);

        $message = "<div class='message message-success'>
            Recharge de " . number_format($montant, 2) . " € effectuee avec succes !
        </div>";
    }
}

// On recupere le solde actuel
$stmt = $pdo->prepare("SELECT solde FROM users WHERE id = ?");
$stmt->execute([$userId]);
$solde = $stmt->fetch(PDO::FETCH_ASSOC)['solde'];

include '../includes/header.php';
?>

<h1>Recharger mon compte</h1>

<?php echo $message; ?>

<div class="solde-box">
    Solde actuel : <span class="montant"><?php echo number_format($solde, 2); ?> €</span>
</div>

<div class="message message-info">
    ⚠️ Ceci est une simulation. Aucun vrai paiement n'est effectue. 
    Vous pouvez entrer n'importe quels chiffres.
</div>

<div class="form-container">
    <form method="POST" action="">
        <h2>Montant a recharger</h2>

        <div class="form-group">
            <label for="montant">Montant (€) *</label>
            <input type="number" id="montant" name="montant" step="0.01" min="1" required>
        </div>

        <h2 style="margin-top: 20px;">Informations de carte bancaire</h2>

        <div class="form-group">
            <label for="numero_carte">Numero de carte *</label>
            <input type="text" id="numero_carte" name="numero_carte" 
                   placeholder="1234 5678 9012 3456" maxlength="19" required>
        </div>

        <div class="form-group">
            <label for="titulaire">Titulaire de la carte *</label>
            <input type="text" id="titulaire" name="titulaire" required>
        </div>

        <div class="form-group">
            <label for="expiration">Date d'expiration (MM/AA) *</label>
            <input type="text" id="expiration" name="expiration" 
                   placeholder="12/28" maxlength="5" required>
        </div>

        <div class="form-group">
            <label for="cvv">CVV *</label>
            <input type="text" id="cvv" name="cvv" maxlength="3" required>
        </div>

        <button type="submit" class="btn btn-success">💳 Valider le paiement</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
