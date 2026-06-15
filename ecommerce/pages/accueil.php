<?php
// Page d'accueil
require_once '../includes/db.php';

// On recupere les 4 derniers livres ajoutes pour les afficher
$stmt = $pdo->query(
    "SELECT * FROM products WHERE disponible = 1 
     ORDER BY date_ajout DESC LIMIT 4"
);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<!--  CARROUSEL  -->
<div class="carousel" id="carousel">
    <div class="carousel-slides" id="slides">
        <div class="carousel-slide" style="background: linear-gradient(135deg, #e67e22, #d35400);">
            <h2>📚 Bienvenue sur LivresOcc</h2>
            <p>Achetez et vendez vos livres d'occasion entre particuliers</p>
        </div>
        <div class="carousel-slide" style="background: linear-gradient(135deg, #3498db, #2980b9);">
            <h2>💰 Vendez vos livres</h2>
            <p>Donnez une seconde vie a vos livres et gagnez de l'argent</p>
        </div>
        <div class="carousel-slide" style="background: linear-gradient(135deg, #27ae60, #229954);">
            <h2>🔍 Trouvez des bonnes affaires</h2>
            <p>Romans, BD, mangas, livres scolaires : pour tous les gouts</p>
        </div>
    </div>

    <button class="carousel-btn carousel-prev" onclick="changerSlide(-1)">‹</button>
    <button class="carousel-btn carousel-next" onclick="changerSlide(1)">›</button>
</div>

<!--  DERNIERS LIVRES -->
<h2>Derniers livres ajoutes</h2>

<?php if (count($produits) === 0): ?>
    <p>Aucun livre disponible pour le moment.</p>
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

    <p style="margin-top: 20px;">
        <a href="catalogue.php" class="btn">Voir tout le catalogue</a>
    </p>
<?php endif; ?>

<!--  JS POUR LE CARROUSEL  -->
<script>
    // Carrousel simple : on change l'index et on translate les slides
    let indexSlide = 0;
    const slides = document.getElementById('slides');
    const nbSlides = slides.children.length;

    function changerSlide(direction) {
        indexSlide += direction;
        if (indexSlide < 0) indexSlide = nbSlides - 1;
        if (indexSlide >= nbSlides) indexSlide = 0;
        slides.style.transform = 'translateX(-' + (indexSlide * 100) + '%)';
    }

    // Defilement automatique toutes les 5 secondes
    setInterval(function() {
        changerSlide(1);
    }, 5000);
</script>

<?php include '../includes/footer.php'; ?>
