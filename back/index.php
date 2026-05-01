<?php
require_once 'db.php';
require_once 'helpers.php';
include 'header.php';
// Récupérer les 3 derniers produits
$featured_products = db_fetch_all($conn, "SELECT * FROM products ORDER BY created_at DESC LIMIT 3");
?>

<section class="hero" style="background-image: url('https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=2070'); background-size: cover; background-position: center; height: 500px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-shadow: 0 4px 10px rgba(0,0,0,0.5);">
    <h1 style="font-size: 4rem; margin-bottom: 1.5rem;">Vivez le Futur du Tech</h1>
    <p style="font-size: 1.5rem; color: #fff; max-width: 700px; margin-bottom: 2rem;">Des performances de pointe. Un design d'exception. Découvrez la collection Pro X.</p>
    <div style="display: flex; gap: 1rem;">
        <a href="products.php" class="btn" style="padding: 1rem 3rem; font-size: 1.25rem;">Shop Now</a>
        <?php if(!isset($_SESSION['user_id'])): ?>
            <a href="auth.php" class="btn" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(5px); padding: 1rem 3rem; font-size: 1.25rem;">Rejoignez-nous</a>
        <?php endif; ?>
    </div>
</section>

<section>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem;">
        <h2 style="font-size: 2rem;">Produits Vedettes</h2>
        <a href="products.php" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Voir tout →</a>
    </div>
    
    <div class="products-grid">
        <?php foreach($featured_products as $product): ?>
            <article class="product-card">
                <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>" class="product-image">
                <div class="product-info">
                    <h3><?= e($product['name']) ?></h3>
                    <p style="font-size: 0.875rem; color: #94a3b8;"><?= e($product['description']) ?></p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                        <span class="price" style="margin: 0;"><?= money_dt($product['price']) ?></span>
                        <a href="product_details.php?id=<?= $product['id'] ?>" class="btn" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Détails</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section style="margin-top: 4rem;">
    <h2 style="text-align: center; margin-bottom: 3rem;">L'Expérience TechStore</h2>
    <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 2rem;">
        <article style="background: var(--card-bg); padding: 2.5rem; border-radius: 1.5rem; flex: 1; min-width: 250px; text-align: center; border: 1px solid var(--border-color); transition: 0.3s transform;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">💎</div>
            <h3>Qualité Supérieure</h3>
            <p style="color: #94a3b8; margin-top: 1rem;">Sélection d'élite garantie par nos ingénieurs.</p>
        </article>
        <article style="background: var(--card-bg); padding: 2.5rem; border-radius: 1.5rem; flex: 1; min-width: 250px; text-align: center; border: 1px solid var(--border-color); transition: 0.3s transform;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🚀</div>
            <h3>Livraison Éclair</h3>
            <p style="color: #94a3b8; margin-top: 1rem;">Vos produits chez vous en moins de 48h.</p>
        </article>
        <article style="background: var(--card-bg); padding: 2.5rem; border-radius: 1.5rem; flex: 1; min-width: 250px; text-align: center; border: 1px solid var(--border-color); transition: 0.3s transform;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📞</div>
            <h3>Support Expert</h3>
            <p style="color: #94a3b8; margin-top: 1rem;">Une assistance technique disponible 24/7.</p>
        </article>
    </div>
</section>

<?php include 'footer.php'; ?>
