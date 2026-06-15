<?php
// Page d'inscription

require_once '../includes/db.php';
session_start();

// Si l'utilisateur est deja connecte, on le redirige
if (isset($_SESSION['user_id'])) {
    header("Location: accueil.php");
    exit;
}

$message = "";

// Quand le formulaire est envoye
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On recupere les donnees du formulaire
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $mdp = $_POST['mot_de_passe'];
    $adresse = trim($_POST['adresse']);

    // Verification simple : tous les champs sont remplis
    if (empty($nom) || empty($prenom) || empty($email) || empty($mdp)) {
        $message = "<div class='message message-error'>Veuillez remplir tous les champs obligatoires.</div>";
    } else {
        // On verifie si l'email existe deja
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $message = "<div class='message message-error'>Cet email est deja utilise.</div>";
        } else {
            // On hash le mot de passe pour la securite
            $mdpHash = password_hash($mdp, PASSWORD_DEFAULT);

            // On insere le nouvel utilisateur
            $stmt = $pdo->prepare(
                "INSERT INTO users (nom, prenom, email, mot_de_passe, adresse) 
                 VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([$nom, $prenom, $email, $mdpHash, $adresse]);

            $message = "<div class='message message-success'>Inscription reussie ! Vous pouvez vous <a href='connexion.php'>connecter</a>.</div>";
        }
    }
}

include '../includes/header.php';
?>

<h1>Inscription</h1>

<?php echo $message; ?>

<div class="form-container">
    <form method="POST" action="">
        <div class="form-group">
            <label for="nom">Nom *</label>
            <input type="text" id="nom" name="nom" required>
        </div>

        <div class="form-group">
            <label for="prenom">Prenom *</label>
            <input type="text" id="prenom" name="prenom" required>
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="mot_de_passe">Mot de passe *</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>

        <div class="form-group">
            <label for="adresse">Adresse</label>
            <input type="text" id="adresse" name="adresse" placeholder="Ex: 12 rue des Lilas, 75001 Paris">
        </div>

        <button type="submit" class="btn">S'inscrire</button>
        <p style="margin-top: 15px; font-size: 14px;">
            Deja un compte ? <a href="connexion.php">Se connecter</a>
        </p>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
