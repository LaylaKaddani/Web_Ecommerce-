<?php
// Page de connexion

require_once '../includes/db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: accueil.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mdp = $_POST['mot_de_passe'];

    if (empty($email) || empty($mdp)) {
        $message = "<div class='message message-error'>Veuillez remplir tous les champs.</div>";
    } else {
        // On cherche l'utilisateur par email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // password_verify compare le mot de passe avec le hash
        if ($user && password_verify($mdp, $user['mot_de_passe'])) {
            // On stocke les infos en session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
            $_SESSION['prenom'] = $user['prenom'];

            header("Location: accueil.php");
            exit;
        } else {
            $message = "<div class='message message-error'>Email ou mot de passe incorrect.</div>";
        }
    }
}

include '../includes/header.php';
?>

<h1>Connexion</h1>

<?php echo $message; ?>

<div class="form-container">
    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="mot_de_passe">Mot de passe</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </div>

        <button type="submit" class="btn">Se connecter</button>
        <p style="margin-top: 15px; font-size: 14px;">
            Pas encore de compte ? <a href="inscription.php">S'inscrire</a>
        </p>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
