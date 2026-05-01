<?php
require_once 'db.php';
require_once 'helpers.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get cart items and total
$cart_items = db_fetch_all(
    $conn,
    "SELECT c.quantity, p.name, p.price, p.id as product_id, p.unite, p.taux_tva, p.remise
     FROM cart c
     JOIN products p ON c.product_id = p.id
     WHERE c.user_id = ?",
    'i',
    [(int)$user_id]
);

if (empty($cart_items)) {
    header("Location: cart.php");
    exit;
}

$total_ht = 0;
$total_tva = 0;
$timbre_fiscale = 1.000;

foreach ($cart_items as &$item) {
    $item_ht = $item['price'] * $item['quantity'];
    $item_remise_amount = $item_ht * ($item['remise'] / 100);
    $item_net_ht = $item_ht - $item_remise_amount;
    $item_tva_amount = $item_net_ht * ($item['taux_tva'] / 100);
    
    $item['total_ht'] = $item_net_ht;
    $item['total_ttc'] = $item_net_ht + $item_tva_amount;
    
    $total_ht += $item_net_ht;
    $total_tva += $item_tva_amount;
}
unset($item);

$total_ttc_order = $total_ht + $total_tva + $timbre_fiscale;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($full_name && $address && $city && $postal_code && $phone) {
        $payment_method = trim($_POST['payment_method'] ?? 'Carte Bancaire');
        $status = ($payment_method === 'Paiement à la livraison') ? 'Non Payée' : 'Payée';
        
        try {
            mysqli_begin_transaction($conn);

            // Create Order
            db_exec(
                $conn,
                "INSERT INTO orders (user_id, total_amount, total_ht, total_tva, timbre_fiscale, total_ttc, full_name, address, city, postal_code, phone, payment_method, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                'idddddsssssss',
                [(int)$user_id, (float)$total_ttc_order, (float)$total_ht, (float)$total_tva, (float)$timbre_fiscale, (float)$total_ttc_order, $full_name, $address, $city, $postal_code, $phone, $payment_method, $status]
            );
            $order_id = mysqli_insert_id($conn);

            // Insert Order Items and Update Stock
            foreach ($cart_items as $item) {
                db_exec(
                    $conn,
                    "INSERT INTO order_items (order_id, product_id, product_name, price, quantity, unite, taux_tva, remise, total_ht, total_ttc)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    'iisdisdddd',
                    [(int)$order_id, (int)$item['product_id'], (string)$item['name'], (float)$item['price'], (int)$item['quantity'], (string)$item['unite'], (float)$item['taux_tva'], (float)$item['remise'], (float)$item['total_ht'], (float)$item['total_ttc']]
                );

                // Déduire le stock avec la nouvelle fonction
                update_product_stock($conn, (int)$item['product_id'], (int)$item['quantity']);
            }

            // Clear Cart
            db_exec($conn, "DELETE FROM cart WHERE user_id = ?", 'i', [(int)$user_id]);

            mysqli_commit($conn);

            header("Location: invoice.php?id=" . $order_id);
            exit;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Erreur lors de la commande: " . $e->getMessage();
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<section>
    <h1 style="text-align: center; margin-bottom: 2rem;">Confirmation de la commande</h1>

    <div style="max-width: 800px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <!-- Resumé de la commande -->
        <div style="background: var(--card-bg); padding: 2rem; border-radius: 1rem;">
            <h2 style="margin-bottom: 1rem;">Résumé de votre panier</h2>
            <ul style="list-style: none; padding: 0;">
                <?php foreach($cart_items as $item): ?>
                    <li style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                        <span><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?> <?= htmlspecialchars($item['unite']) ?>)</span>
                        <strong><?= number_format($item['total_ttc'], 2, ',', ' ') ?> DT</strong>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div style="text-align: right; margin-top: 1rem; font-size: 1.1rem; color: #94a3b8;">
                Total HT: <?= number_format($total_ht, 2, ',', ' ') ?> DT<br>
                TVA: <?= number_format($total_tva, 2, ',', ' ') ?> DT<br>
                Timbre Fiscale: <?= number_format($timbre_fiscale, 3, ',', ' ') ?> DT
            </div>
            <div style="text-align: right; margin-top: 0.5rem; font-size: 1.25rem;">
                Total TTC: <span style="color: var(--primary-color); font-weight: bold;"><?= number_format($total_ttc_order, 2, ',', ' ') ?> DT</span>
            </div>
        </div>

        <!-- Formulaire de facturation -->
        <div style="background: var(--card-bg); padding: 2rem; border-radius: 1rem;">
            <h2 style="margin-bottom: 1rem;">Informations de facturation</h2>
            <?php if(isset($error)): ?>
                <div style="background: #fee2e2; color: #ef4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form action="checkout.php" method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
                <div class="form-group">
                    <label>Nom complet</label>
                    <input type="text" name="full_name" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color);">
                </div>
                <div class="form-group">
                    <label>Adresse de livraison</label>
                    <input type="text" name="address" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color);">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Ville</label>
                        <input type="text" name="city" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color);">
                    </div>
                    <div class="form-group">
                        <label>Code Postal</label>
                        <input type="text" name="postal_code" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color);">
                    </div>
                </div>
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="text" name="phone" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color);">
                </div>
                <div class="form-group">
                    <label>Moyen de paiement</label>
                    <select name="payment_method" required style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color);">
                        <option value="Carte Bancaire">Carte Bancaire</option>
                        <option value="PayPal">PayPal</option>
                        <option value="Virement Bancaire">Virement Bancaire</option>
                        <option value="Paiement à la livraison">Paiement à la livraison</option>
                    </select>
                </div>
                <button type="submit" class="btn" style="margin-top: 1rem; width: 100%;">Confirmer et Payer</button>
            </form>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
