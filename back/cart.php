<?php
require_once 'db.php';
require_once 'helpers.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    echo "<section><p style='text-align: center; margin-top: 2rem;'>Veuillez vous <a href='auth.php' style='color: var(--primary-color);'>connecter</a> pour voir votre panier.</p></section>";
    include 'footer.php';
    exit;
}

$user_id = $_SESSION['user_id'];

// UPDATE logic
if (isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $new_quantity = (int)$_POST['quantity'];
    if ($new_quantity > 0) {
        db_exec($conn, "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?", 'iii', [(int)$new_quantity, (int)$cart_id, (int)$user_id]);
    } else {
        db_exec($conn, "DELETE FROM cart WHERE id = ? AND user_id = ?", 'ii', [(int)$cart_id, (int)$user_id]);
    }
    header("Location: cart.php");
    exit;
}

// DELETE logic
if (isset($_GET['remove'])) {
    $cart_id = $_GET['remove'];
    db_exec($conn, "DELETE FROM cart WHERE id = ? AND user_id = ?", 'ii', [(int)$cart_id, (int)$user_id]);
    header("Location: cart.php");
    exit;
}

// Fetch cart items
$cart_items = db_fetch_all($conn, "
    SELECT c.id as cart_id, c.quantity, p.name, p.price, p.image_url 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
", 'i', [(int)$user_id]);

// Fetch cart items
?>

<section>
    <h1 style="text-align: center; margin-bottom: 3rem;">Votre Panier</h1>
    
    <div style="max-width: 800px; margin: 0 auto; background: var(--card-bg); padding: 2rem; border-radius: 1rem;">
        <?php if(empty($cart_items)): ?>
            <p style="text-align: center; color: #94a3b8;">Votre panier est vide.</p>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 1rem;">Produit</th>
                    <th style="padding: 1rem;">Prix Unitaire</th>
                    <th style="padding: 1rem;">Quantité</th>
                    <th style="padding: 1rem;">Action</th>
                </tr>
                <?php $total = 0; foreach($cart_items as $item): 
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal; 
                ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 1rem; display: flex; align-items: center; gap: 1rem;">
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" style="width: 50px; height: 50px; border-radius: 0.5rem; object-fit: cover;">
                            <?= htmlspecialchars($item['name']) ?>
                        </td>
                        <td style="padding: 1rem; color: var(--primary-color); font-weight: bold;">
                            <?= number_format($item['price'], 2, ',', ' ') ?> DT
                        </td>
                        <td style="padding: 1rem;">
                            <form action="cart.php" method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" style="width: 60px; padding: 0.5rem; border-radius: 0.5rem; background: var(--bg-color); color: var(--text-color); border: 1px solid var(--border-color);">
                                <button type="submit" name="update_quantity" class="btn" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Maj</button>
                            </form>
                        </td>
                        <td style="padding: 1rem;">
                            <a href="cart.php?remove=<?= $item['cart_id'] ?>" style="color: #ef4444; text-decoration: none;">Retirer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--border-color); display: flex; justify-content: space-between; align-items: flex-start;">
                <div style="text-align: right;">
                    <div style="font-size: 1.75rem; font-weight: bold; margin-top: 0.5rem;">
                        Total : <span style="color: var(--primary-color);"><?= number_format($total, 2, ',', ' ') ?> DT</span>
                    </div>
                    <div style="margin-top: 1.5rem;">
                        <a href="checkout.php" class="btn" style="padding: 0.75rem 2rem; font-size: 1.1rem; display: inline-block; text-decoration: none;">Passer la commande</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>
