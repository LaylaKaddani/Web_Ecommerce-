<?php
// Page profil utilisateur
// Permet de modifier ses infos et d'ajouter IBAN + piece d'identite
// (necessaires pour vendre)

require_once '../includes/db.php';
session_start();

// On verifie qu'on est connecte
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = "";

//  Traitement du formulaire 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $adresse = trim($_POST['adresse']);
    $iban = trim($_POST['iban']);

    //  Upload de la piece d'identite 
    // On garde l'ancien chemin si rien n'est uploade
    $stmt = $pdo->prepare("SELECT piece_identite FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    $cheminPiece = $userData['piece_identite'];

    if (isset($_FILES['piece_identite']) && $_FILES['piece_identite']['error'] === UPLOAD_ERR_OK) {
        $fichier = $_FILES['piece_identite'];
        // On verifie le type (jpg, png, pdf)
        $extensionsOk = ['jpg', 'jpeg', 'png', 'pdf'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

        if (in_array($extension, $extensionsOk)) {
            // On donne un nom unique au fichier
            $nomFichier = "id_" . $userId . "_" . time() . "." . $extension;
            $cheminPiece = "uploads/identites/" . $nomFichier;

            move_uploaded_file($fichier['tmp_name'], "../" . $cheminPiece);
        } else {
            $message = "<div class='message message-error'>Format non autorise (jpg, png, pdf).</div>";
        }
    }

    //  Mise a jour en BDD 
    if (empty($message)) {
        $stmt = $pdo->prepare(
            "UPDATE users 
             SET nom = ?, prenom = ?, adresse = ?, iban = ?, piece_identite = ? 
             WHERE id = ?"
        );
        $stmt->execute([$nom, $prenom, $adresse, $iban, $cheminPiece, $userId]);

        $_SESSION['nom'] = $nom;
        $_SESSION['prenom'] = $prenom;

        $message = "<div class='message message-success'>Profil mis a jour avec succes.</div>";
    }
}

// Recuperation des infos utilisateur 
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>Mon profil</h1>

<?php echo $message; ?>

<!-- Affichage du solde -->
<div class="solde-box">
    Solde disponible : <span class="montant"><?php echo number_format($user['solde'], 2); ?> €</span>
</div>

<div class="form-container" style="max-width: 600px;">
    <form method="POST" action="" enctype="multipart/form-data">
        <h2>Informations personnelles</h2>

        <div class="form-group">
            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
        </div>

        <div class="form-group">
            <label for="prenom">Prenom</label>
            <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
        </div>

        <div class="form-group">
            <label>Email (non modifiable)</label>
            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
        </div>

        <div class="form-group">
            <label for="adresse">Adresse</label>
            <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($user['adresse'] ?? ''); ?>">
        </div>

        <h2 style="margin-top: 25px;">Infos vendeur (obligatoires pour vendre)</h2>

        <div class="form-group">
            <label for="iban">IBAN (pour recevoir les paiements)</label>
            <input type="text" id="iban" name="iban" value="<?php echo htmlspecialchars($user['iban'] ?? ''); ?>" placeholder="FR76 ...">
        </div>

        <div class="form-group">
            <label for="piece_identite">Piece d'identite (jpg, png ou pdf)</label>
            <input type="file" id="piece_identite" name="piece_identite" accept=".jpg,.jpeg,.png,.pdf">
            <?php if (!empty($user['piece_identite'])): ?>
                <p style="font-size: 13px; color: #27ae60; margin-top: 5px;">
                    ✓ Piece d'identite deja envoyee
                </p>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn">Enregistrer</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
