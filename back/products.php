<?php
require_once 'db.php';
require_once 'helpers.php';
include 'header.php';

// Récupération des catégories pour le filtre
$categories = db_fetch_all($conn, "SELECT * FROM categories ORDER BY name ASC");

// Récupération des produits avec filtre
$search = trim($_GET['search'] ?? '');
$cat_id = isset($_GET['category']) ? (int)$_GET['category'] : null;

$query = "SELECT p.*, c.name as category_name, c.icon as category_icon, 
          (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $query .= " AND p.name LIKE ?";
    $params[] = '%' . $search . '%';
    $types .= 's';
}

if ($cat_id) {
    $query .= " AND p.category_id = ?";
    $params[] = $cat_id;
    $types .= 'i';
}

$query .= " ORDER BY p.created_at DESC";
$products = db_fetch_all($conn, $query, $types, $params);
?>

<section>
    <h1 class="page-title">Nos Produits</h1>

    <div class="search-wrap" style="max-width: 900px;">
        <form action="products.php" method="GET" class="search-form" style="display: grid; grid-template-columns: 2fr 1.5fr auto auto; gap: 1rem;">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Rechercher un produit..." class="search-input">
            <select name="category" class="search-input">
                <option value="">Toutes les catégories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $cat_id === (int)$cat['id'] ? 'selected' : '' ?>>
                        <?= e($cat['icon'] . ' ' . $cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Filtrer</button>
            <?php if ($search || $cat_id): ?>
                <a href="products.php" class="btn btn-muted">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="products-grid">
        <?php foreach ($products as $product): ?>
            <article class="product-card" style="position: relative;">
                <?php if ($product['category_name']): ?>
                    <span
                        style="position: absolute; top: 10px; left: 10px; background: rgba(0,0,0,0.6); color: white; padding: 0.25rem 0.5rem; border-radius: 0.5rem; font-size: 0.75rem; backdrop-filter: blur(5px); z-index: 10;">
                        <?= e($product['category_icon'] . ' ' . $product['category_name']) ?>
                    </span>
                <?php endif; ?>

                <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>" class="product-image">
                <div class="product-info">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                        <h3 style="margin: 0;">
                            <a href="product_details.php?id=<?= (int) $product['id'] ?>" style="color: inherit; text-decoration: none;"><?= e($product['name']) ?></a>
                        </h3>
                        <div style="color: #fbbf24; font-size: 0.875rem;">
                            <?= $product['avg_rating'] ? number_format($product['avg_rating'], 1) . ' ⭐' : '' ?>
                        </div>
                    </div>
                    <p style="font-size: 0.875rem; height: 3em; overflow: hidden;"><?= e($product['description']) ?></p>

                    <div style="margin: 1rem 0; display: flex; justify-content: space-between; align-items: center;">
                        <span class="price" style="margin: 0;">
                            <?php
                            $price = (float) ($product['price'] ?? 0);
                            $remise = (float) ($product['remise'] ?? 0);
                            $price_after_remise = $remise > 0 ? ($price - ($price * ($remise / 100))) : $price;
                            ?>
                            <?php if ($remise > 0): ?>
                                <span class="price-wrap">
                                    <span class="price-old"><?= money_dt($price) ?></span>
                                    <span class="price-new"><?= money_dt($price_after_remise) ?></span>
                                </span>
                            <?php else: ?>
                                <?= money_dt($price) ?>
                            <?php endif; ?>
                        </span>

                        <?php if ($product['stock'] <= 0): ?>
                            <span style="color: #ef4444; font-size: 0.75rem; font-weight: bold;">Rupture de stock</span>
                        <?php else: ?>
                            <span style="color: #22c55e; font-size: 0.75rem;"><?= $product['stock'] ?> en stock</span>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                        <a href="product_details.php?id=<?= (int) $product['id'] ?>" class="btn" style="flex: 1; background: #334155; text-align: center;">Détails</a>
                        <?php if ($product['stock'] > 0): ?>
                            <button class="btn add-to-cart-btn" style="flex: 2;"
                                data-id="<?= (int) $product['id'] ?>">
                                🛒 Panier
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($_SESSION['username']) && strtolower($_SESSION['username']) === 'admin'): ?>
                        <div class="admin-actions">
                            <a href="admin_edit_product.php?id=<?= (int) $product['id'] ?>"
                                class="btn btn-warn btn-flex">Modifier</a>
                            <a href="admin_delete_product.php?id=<?= (int) $product['id'] ?>" class="btn btn-danger btn-flex"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">Supprimer</a>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <?php if (empty($products)): ?>
        <?php if ($search || $cat_id): ?>
            <p class="muted-center">Aucun produit ne correspond à votre recherche.</p>
        <?php else: ?>
            <p class="muted-center">Aucun produit disponible pour le moment.</p>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>