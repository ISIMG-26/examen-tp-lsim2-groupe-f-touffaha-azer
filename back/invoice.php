<?php
require_once 'db.php';
require_once 'helpers.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "<section><p>Facture introuvable.</p></section>";
    include 'footer.php';
    exit;
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get order details
$order = db_fetch_one(
    $conn,
    "SELECT o.*, u.email
     FROM orders o
     JOIN users u ON o.user_id = u.id
     WHERE o.id = ? AND o.user_id = ?",
    'ii',
    [$order_id, (int)$user_id]
);

if (!$order) {
    echo "<section><p>Facture introuvable ou vous n'avez pas l'autorisation de la voir.</p></section>";
    include 'footer.php';
    exit;
}

// Get order items
$items = db_fetch_all($conn, "SELECT * FROM order_items WHERE order_id = ?", 'i', [$order_id]);
?>

<section style="display: flex; justify-content: center;">
    <div style="background: #fff; color: #000; width: 100%; max-width: 800px; padding: 3rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e2e8f0; padding-bottom: 1.5rem; margin-bottom: 2rem;">
            <div>
                <h1 style="margin: 0; color: #1e293b; font-size: 2.5rem;">FACTURE</h1>
                <p style="color: #64748b; margin-top: 0.5rem;">N° de commande : #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></p>
            </div>
            <div style="text-align: right;">
                <h2 style="margin: 0; color: var(--primary-color);">TechStore</h2>
                <p style="color: #64748b; margin: 0;">6014 Rue ibne khaldoun</p>
                <p style="color: #64748b; margin: 0;">mtorich,Gabes</p>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
            <div>
                <h3 style="color: #1e293b; margin-bottom: 0.5rem;">Facturé à :</h3>
                <p style="margin: 0; font-weight: bold;"><?= htmlspecialchars($order['full_name']) ?></p>
                <p style="margin: 0;"><?= htmlspecialchars($order['address']) ?></p>
                <p style="margin: 0;"><?= htmlspecialchars($order['postal_code']) ?> <?= htmlspecialchars($order['city']) ?></p>
                <p style="margin: 0;">Tél : <?= htmlspecialchars($order['phone']) ?></p>
                <p style="margin: 0;"><?= htmlspecialchars($order['email']) ?></p>
            </div>
            <div style="text-align: right;">
                <h3 style="color: #1e293b; margin-bottom: 0.5rem;">Détails de la facture :</h3>
                <p style="margin: 0;">Date : <?= date('d/m/Y', strtotime($order['created_at'])) ?></p>
                <p style="margin: 0;">Paiement : <strong><?= htmlspecialchars($order['payment_method'] ?? 'Carte Bancaire') ?></strong></p>
                <p style="margin: 0;">Statut : <span style="color: <?= ($order['status'] === 'Non Payée') ? '#ef4444' : '#16a34a' ?>; font-weight: bold;"><?= htmlspecialchars($order['status'] ?? 'Payée') ?></span></p>
            </div>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 2rem;">
            <thead>
                <tr style="background: #f1f5f9;">
                    <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #cbd5e1;">Description</th>
                    <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #cbd5e1;">Prix unitaire (sans remise)</th>
                    <th style="padding: 1rem; text-align: center; border-bottom: 2px solid #cbd5e1;">Quantité</th>
                    <th style="padding: 1rem; text-align: right; border-bottom: 2px solid #cbd5e1;">Total TTC</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <?php
                    $unit_price = (float)$item['price'];
                    $qty = (int)$item['quantity'];
                    $line_total_ttc = isset($item['total_ttc'])
                        ? (float)$item['total_ttc']
                        : ($unit_price * $qty);
                ?>
                <tr>
                    <td style="padding: 1rem; border-bottom: 1px solid #e2e8f0;"><?= htmlspecialchars($item['product_name']) ?></td>
                    <td style="padding: 1rem; text-align: center; border-bottom: 1px solid #e2e8f0;"><?= number_format($unit_price, 2, ',', ' ') ?> DT</td>
                    <td style="padding: 1rem; text-align: center; border-bottom: 1px solid #e2e8f0;"><?= $qty ?></td>
                    <td style="padding: 1rem; text-align: right; border-bottom: 1px solid #e2e8f0; font-weight: bold;"><?= number_format($line_total_ttc, 2, ',', ' ') ?> DT</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
            $total_ht = isset($order['total_ht']) ? (float)$order['total_ht'] : null;
            $total_tva = isset($order['total_tva']) ? (float)$order['total_tva'] : null;
            $timbre_fiscale = isset($order['timbre_fiscale']) ? (float)$order['timbre_fiscale'] : 0.0;
            $total_ttc = isset($order['total_ttc']) ? (float)$order['total_ttc'] : (float)$order['total_amount'];
            $total_brut_ht = 0.0;
            $total_remise = 0.0;
            foreach ($items as $it) {
                $p = isset($it['price']) ? (float)$it['price'] : 0.0;
                $q = isset($it['quantity']) ? (int)$it['quantity'] : 0;
                $r = isset($it['remise']) ? (float)$it['remise'] : 0.0;
                $line_brut = $p * $q;
                $total_brut_ht += $line_brut;
                $total_remise += ($line_brut * ($r / 100));
            }

            if ($total_ht === null || $total_tva === null) {
                $computed_total_ht = 0.0;
                $computed_total_tva = 0.0;
                foreach ($items as $it) {
                    if (isset($it['total_ht'], $it['total_ttc'])) {
                        $computed_total_ht += (float)$it['total_ht'];
                        $computed_total_tva += ((float)$it['total_ttc'] - (float)$it['total_ht']);
                    }
                }
                if ($total_ht === null) $total_ht = $computed_total_ht;
                if ($total_tva === null) $total_tva = $computed_total_tva;
            }
        ?>

        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 300px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span>Total brut (HT) :</span>
                    <span><?= number_format($total_brut_ht, 2, ',', ' ') ?> DT</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #64748b;">
                    <span>Remise :</span>
                    <span>-<?= number_format($total_remise, 2, ',', ' ') ?> DT</span>
                </div>
               
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #64748b;">
                    <span>TVA :</span>
                    <span><?= number_format($total_tva, 2, ',', ' ') ?> DT</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; color: #64748b;">
                    <span>Timbre fiscale :</span>
                    <span><?= number_format($timbre_fiscale, 3, ',', ' ') ?> DT</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 2px solid #e2e8f0; padding-top: 1rem; margin-top: 0.5rem;">
                    <span style="font-size: 1.25rem; font-weight: bold; color: #1e293b;">Total TTC:</span>
                    <span style="font-size: 1.5rem; font-weight: bold; color: var(--primary-color);"><?= number_format($total_ttc, 2, ',', ' ') ?> DT</span>
                </div>
            </div>
        </div>

        <div style="margin-top: 4rem; text-align: center;">
            <button onclick="window.print()" class="btn" style="padding: 0.75rem 2rem; margin-right: 1rem; background: #334155;">Imprimer la facture</button>
            <a href="products.php" class="btn" style="padding: 0.75rem 2rem;">Continuer vos achats</a>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
