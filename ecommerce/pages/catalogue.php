<?php
// Catalogue : affichage des articles avec recherche + filtres
require_once '../includes/db.php';
session_start();

// On recupere les filtres depuis l'URL (methode GET)
$recherche = isset($_GET['recherche']) ? trim($_GET['recherche']) : '';
$categorie = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$prixMax = isset($_GET['prix_max']) && is_numeric($_GET['prix_max']) ? floatval($_GET['prix_max']) : 0;

// On construit la requete SQL dynamiquement
$sql = "SELECT * FROM products WHERE disponible = 1";
$params = [];

// Recherche par mot-cle (sur titre et auteur)
if (!empty($recherche)) {
    $sql .= " AND (titre LIKE ? OR auteur LIKE ?)";
    $params[] = "%$recherche%";
    $params[] = "%$recherche%";
}

// Filtre par categorie
if (!empty($categorie)) {
    $sql .= " AND categorie = ?";
    $params[] = $categorie;
}

// Filtre par prix maximum
if ($prixMax > 0) {
    $sql .= " AND prix <= ?";
    $params[] = $prixMax;
}

$sql .= " ORDER BY date_ajout DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<h1>Catalogue des livres</h1>

<!--  BARRE DE RECHERCHE + FILTRES -->
<div class="filters">
    <form method="GET" action="">
        <div class="form-group">
            <label for="recherche">Rechercher</label>
            <input type="text" id="recherche" name="recherche" 
                   value="<?php echo htmlspecialchars($recherche); ?>" 
                   placeholder="Titre ou auteur...">
        </div>

        <div class="form-group">
            <label for="categorie">Categorie</label>
            <select id="categorie" name="categorie">
                <option value="">Toutes</option>
                <?php
                $cats = ['Roman','BD','Manga','Sciences','Jeunesse','Scolaire','Autre'];
                foreach ($cats as $c) {
                    $selected = ($categorie === $c) ? 'selected' : '';
                    echo "<option value='$c' $selected>$c</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="prix_max">Prix max (€)</label>
            <input type="number" id="prix_max" name="prix_max" step="0.01" min="0" 
                   value="<?php echo $prixMax > 0 ? $prixMax : ''; ?>">
        </div>

        <div class="form-group" style="flex: 0;">
            <button type="submit" class="btn">Filtrer</button>
        </div>
        <div class="form-group" style="flex: 0;">
            <a href="catalogue.php" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<!--  AFFICHAGE DES RESULTATS -->
<p><?php echo count($produits); ?> livre(s) trouve(s)</p>

<?php if (count($produits) === 0): ?>
    <div class="message message-info">Aucun livre ne correspond a votre recherche.</div>
<?php else: ?>
    <div class="products-grid">
        <?php foreach ($produits as $p): ?>
            <a href="detail_article.php?id=<?php echo $p['id']; ?>" style="text-decoration: none; color: inherit;">
                <div class="product-card">
                    <?php if (!empty($p['image']) && file_exists("../" . $p['image'])): ?>
                        <img src="../<?php echo htmlspecialchars($p['image']); ?>" alt="">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/220x200?text=Pas+d%27image" alt="">
                    <?php endif; ?>
                    <div class="product-card-content">
                        <h3><?php echo htmlspecialchars($p['titre']); ?></h3>
                        <p class="auteur"><?php echo htmlspecialchars($p['auteur']); ?></p>
                        <p class="prix"><?php echo number_format($p['prix'], 2); ?> €</p>
                        <span class="etat-tag"><?php echo htmlspecialchars($p['etat']); ?></span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
