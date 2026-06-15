<?php
// Page : ajout d'un article a vendre
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit;
}

$userId = $_SESSION['user_id'];
$message = "";

// On verifie que l'utilisateur a bien rempli ses infos vendeur
$stmt = $pdo->prepare("SELECT iban, piece_identite FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$peutVendre = !empty($user['iban']) && !empty($user['piece_identite']);

//  Traitement du formulaire 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $peutVendre) {
    $titre = trim($_POST['titre']);
    $auteur = trim($_POST['auteur']);
    $description = trim($_POST['description']);
    $categorie = $_POST['categorie'];
    $etat = $_POST['etat'];
    $prix = floatval($_POST['prix']);

    if (empty($titre) || empty($auteur) || $prix <= 0) {
        $message = "<div class='message message-error'>Veuillez remplir correctement tous les champs.</div>";
    } else {
        // Upload de l'image 
        $cheminImage = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fichier = $_FILES['image'];
            $extensionsOk = ['jpg', 'jpeg', 'png', 'gif'];
            $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

            if (in_array($extension, $extensionsOk)) {
                $nomFichier = "produit_" . time() . "_" . rand(1000, 9999) . "." . $extension;
                $cheminImage = "uploads/products/" . $nomFichier;
                move_uploaded_file($fichier['tmp_name'], "../" . $cheminImage);
            } else {
                $message = "<div class='message message-error'>Format d'image non autorise.</div>";
            }
        }

        //Insertion en BDD 
        if (empty($message)) {
            $stmt = $pdo->prepare(
                "INSERT INTO products (vendeur_id, titre, auteur, description, categorie, etat, prix, image) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$userId, $titre, $auteur, $description, $categorie, $etat, $prix, $cheminImage]);

            $message = "<div class='message message-success'>Article mis en vente avec succes ! <a href='catalogue.php'>Voir le catalogue</a></div>";
        }
    }
}

include '../includes/header.php';
?>

<h1>Vendre un livre</h1>

<?php echo $message; ?>

<?php if (!$peutVendre): ?>
    <div class="message message-error">
        Pour vendre un livre, vous devez d'abord renseigner votre IBAN et envoyer une piece d'identite dans 
        <a href="profil.php">votre profil</a>.
    </div>
<?php else: ?>

    <div class="form-container" style="max-width: 600px;">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titre">Titre du livre *</label>
                <input type="text" id="titre" name="titre" required>
            </div>

            <div class="form-group">
                <label for="auteur">Auteur *</label>
                <input type="text" id="auteur" name="auteur" required>
            </div>

            <div class="form-group">
                <label for="categorie">Categorie *</label>
                <select id="categorie" name="categorie" required>
                    <option value="Roman">Roman</option>
                    <option value="BD">BD</option>
                    <option value="Manga">Manga</option>
                    <option value="Sciences">Sciences</option>
                    <option value="Jeunesse">Jeunesse</option>
                    <option value="Scolaire">Scolaire</option>
                    <option value="Autre">Autre</option>
                </select>
            </div>

            <div class="form-group">
                <label for="etat">Etat du livre *</label>
                <select id="etat" name="etat" required>
                    <option value="Neuf">Neuf</option>
                    <option value="Tres bon">Tres bon</option>
                    <option value="Bon">Bon</option>
                    <option value="Correct">Correct</option>
                    <option value="Use">Use</option>
                </select>
            </div>

            <div class="form-group">
                <label for="prix">Prix (€) *</label>
                <input type="number" id="prix" name="prix" step="0.01" min="0.01" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Decrivez votre livre..."></textarea>
            </div>

            <div class="form-group">
                <label for="image">Photo du livre</label>
                <input type="file" id="image" name="image" accept="image/*">
            </div>

            <button type="submit" class="btn">Mettre en vente</button>
        </form>
    </div>

<?php endif; ?>

<?php include '../includes/footer.php'; ?>
