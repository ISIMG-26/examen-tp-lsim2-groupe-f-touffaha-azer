<?php
require_once 'db.php';
require_once 'helpers.php';
include 'header.php';

if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'] ?? null;

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review']) && $user_id) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    if ($rating >= 1 && $rating <= 5) {
        db_exec($conn, "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)", 'iiis', [$id, $user_id, $rating, $comment]);
        $success_review = "Merci pour votre avis !";
    }
}

// Fetch Product
$product = db_fetch_one($conn, "
    SELECT p.*, c.name as category_name, c.icon as category_icon 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ?
", 'i', [$id]);

if (!$product) {
    echo "<section><p>Produit introuvable.</p></section>";
    include 'footer.php';
    exit;
}

// Fetch Reviews
$reviews = db_fetch_all($conn, "
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
", 'i', [$id]);

$avg_rating = db_scalar($conn, "SELECT AVG(rating) FROM reviews WHERE product_id = ?", 'i', [$id]);
?>

<section>
    <div style="max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
        <!-- Image -->
        <div>
            <img src="<?= e($product['image_url']) ?>" style="width: 100%; border-radius: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3);">
        </div>
        
        <!-- Info -->
        <div>
            <?php if($product['category_name']): ?>
                <span style="background: var(--primary-color); padding: 0.25rem 0.75rem; border-radius: 2rem; font-size: 0.875rem;">
                    <?= e($product['category_icon'] . ' ' . $product['category_name']) ?>
                </span>
            <?php endif; ?>
            
            <h1 style="font-size: 2.5rem; margin: 1rem 0;"><?= e($product['name']) ?></h1>
            
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="color: #fbbf24; font-size: 1.25rem;">
                    <?= $avg_rating ? number_format($avg_rating, 1) . ' ⭐' : '' ?>
                </div>
                <span style="color: #94a3b8;">(<?= count($reviews) ?> avis clients)</span>
            </div>

            <p style="font-size: 1.125rem; color: #cbd5e1; margin-bottom: 2rem;"><?= nl2br(e($product['description'])) ?></p>
            
            <div style="margin-bottom: 2rem;">
                <span style="font-size: 2rem; font-weight: bold; color: var(--primary-color);">
                    <?= money_dt($product['price']) ?>
                </span>
                <?php if($product['stock'] > 0): ?>
                    <span style="margin-left: 1rem; color: #22c55e;">✅ <?= $product['stock'] ?> en stock</span>
                <?php else: ?>
                    <span style="margin-left: 1rem; color: #ef4444;">❌ Rupture de stock</span>
                <?php endif; ?>
            </div>

            <?php if ($product['stock'] > 0): ?>
                <button class="btn add-to-cart-btn btn-block" data-id="<?= $id ?>" style="padding: 1rem; font-size: 1.25rem;">
                    🛒 Ajouter au panier
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Avis Section -->
    <div style="max-width: 1000px; margin: 4rem auto 0 auto;">
        <h2 style="margin-bottom: 2rem; border-bottom: 2px solid var(--border-color); padding-bottom: 1rem;">Avis des clients</h2>
        
        <?php if($user_id): ?>
            <div style="background: var(--card-bg); padding: 2rem; border-radius: 1rem; margin-bottom: 3rem;">
                <h3 style="margin-bottom: 1rem;">Laisser un avis</h3>
                <?php if(isset($success_review)): ?>
                    <div style="color: #22c55e; margin-bottom: 1rem;"><?= $success_review ?></div>
                <?php endif; ?>
                <form action="product_details.php?id=<?= $id ?>" method="POST">
                    <div class="form-group">
                        <label>Note</label>
                        <select name="rating" style="width: 100%; padding: 0.75rem; background: var(--bg-color); color: white; border: 1px solid var(--border-color); border-radius: 0.5rem;">
                            <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                            <option value="4">⭐⭐⭐⭐ (Très bon)</option>
                            <option value="3">⭐⭐⭐ (Moyen)</option>
                            <option value="2">⭐⭐ (Bof)</option>
                            <option value="1">⭐ (Mauvais)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Commentaire</label>
                        <textarea name="comment" rows="3" style="width: 100%; padding: 0.75rem; background: var(--bg-color); color: white; border: 1px solid var(--border-color); border-radius: 0.5rem;" required></textarea>
                    </div>
                    <button type="submit" name="add_review" class="btn">Publier l'avis</button>
                </form>
            </div>
        <?php else: ?>
            <p style="color: #94a3b8; text-align: center; margin-bottom: 3rem;">Veuillez vous connecter pour laisser un avis.</p>
        <?php endif; ?>

        <div style="display: grid; gap: 1.5rem;">
            <?php foreach($reviews as $rev): ?>
                <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                        <strong><?= e($rev['username']) ?></strong>
                        <span style="color: #fbbf24;"><?= str_repeat('⭐', $rev['rating']) ?></span>
                    </div>
                    <p style="color: #cbd5e1;"><?= nl2br(e($rev['comment'])) ?></p>
                    <small style="color: #64748b;"><?= date('d/m/Y', strtotime($rev['created_at'])) ?></small>
                </div>
            <?php endforeach; ?>
            <?php if(empty($reviews)): ?>
                <p style="text-align: center; color: #64748b;">Aucun avis pour le moment.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
