<?php
require_once 'db.php';
require_once 'helpers.php';
include 'header.php';

// Protection : seul un admin peut accéder à cette page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || strtolower($_SESSION['role']) !== 'admin') {
    echo "<section><p style='text-align: center; margin-top: 2rem; color: #ef4444;'>Accès refusé. Cette page est réservée aux administrateurs.</p></section>";
    include 'footer.php';
    exit;
}

// Récupérer les catégories
$categories = db_fetch_all($conn, "SELECT * FROM categories ORDER BY name ASC");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? 0;
    $unite = trim($_POST['unite'] ?? 'pièce');
    $taux_tva = $_POST['taux_tva'] ?? 10.00;
    $remise = $_POST['remise'] ?? 0.00;
    $image_url = trim($_POST['image_url'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
    $stock = (int) ($_POST['stock'] ?? 10);

    if (!empty($name) && !empty($price)) {
        $affected = db_exec(
            $conn,
            "INSERT INTO products (name, description, price, unite, taux_tva, remise, image_url, category_id, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            'ssdsddsii',
            [$name, $description, (float) $price, $unite, (float) $taux_tva, (float) $remise, $image_url, $category_id, $stock]
        );
        if ($affected > 0) {
            $message = "<div class='alert alert-success'>Le produit a été ajouté avec succès !</div>";
        } else {
            $message = "<div class='alert alert-error'>Erreur lors de l'ajout du produit.</div>";
        }
    } else {
        $message = "<div class='alert alert-error'>Le nom et le prix sont obligatoires.</div>";
    }
}
?>

<section>
    <h1 style="text-align: center; margin-bottom: 2rem;">Ajouter un Produit (Admin)</h1>

    <div class="auth-container" style="max-width: 600px; margin-top: 0;">
        <?= $message ?>

        <form action="admin_add_product.php" method="POST" class="auth-form">
            <div class="form-group">
                <label for="name">Nom du produit</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"
                    style="width: 100%; padding: 0.75rem; background-color: var(--bg-color); border: 1px solid var(--border-color); border-radius: 0.5rem; color: var(--text-color); outline: none;"
                    required></textarea>
            </div>

            <div class="form-group">
                <label for="price">Prix HT (DT)</label>
                <input type="number" step="0.01" id="price" name="price" min="0" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="unite">Unité</label>
                    <input type="text" id="unite" name="unite" value="pièce" required>
                </div>
                <div class="form-group">
                    <label for="taux_tva">TVA (%)</label>
                    <input type="number" step="0.01" id="taux_tva" name="taux_tva" value="10.00" min="0" required>
                </div>
                <div class="form-group">
                    <label for="remise">Remise (%)</label>
                    <input type="number" step="0.01" id="remise" name="remise" value="0.00" min="0" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="category_id">Catégorie</label>
                    <select id="category_id" name="category_id"
                        style="width: 100%; padding: 0.75rem; background-color: var(--bg-color); border: 1px solid var(--border-color); border-radius: 0.5rem; color: var(--text-color); outline: none;">
                        <option value="">-- Sans catégorie --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['icon'] . ' ' . $cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stock">Quantité en Stock</label>
                    <input type="number" id="stock" name="stock" value="10" min="0" required>
                </div>
            </div>

            <div class="form-group">
                <label for="image_url">URL de l'image</label>
                <input type="url" id="image_url" name="image_url" placeholder="https://exemple.com/image.jpg">
            </div>

            <button type="submit" class="btn">Ajouter le produit</button>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>