<?php
require_once 'db.php';
require_once 'helpers.php';
include 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get Order History
$orders = db_fetch_all($conn, "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC", 'i', [$user_id]);
?>

<section>
    <h1 style="text-align: center; margin-bottom: 3rem;">Mon Espace Client</h1>

    <div style="display: grid; grid-template-columns: 1fr; gap: 4rem;">
        
        <!-- Commandes -->
        <div>
            <h2 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">📦 Mes Commandes</h2>
            <div style="background: var(--card-bg); border-radius: 1rem; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                            <th style="padding: 1rem;">Commande</th>
                            <th style="padding: 1rem;">Date</th>
                            <th style="padding: 1rem;">Total</th>
                            <th style="padding: 1rem;">Statut</th>
                            <th style="padding: 1rem;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></td>
                            <td style="padding: 1rem;"><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                            <td style="padding: 1rem; font-weight: bold;"><?= money_dt($order['total_ttc']) ?></td>
                            <td style="padding: 1rem;">
                                <span style="padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; background: <?= ($order['status'] === 'Payée' || $order['status'] === 'Livrée') ? '#dcfce7' : '#fee2e2' ?>; color: <?= ($order['status'] === 'Payée' || $order['status'] === 'Livrée') ? '#16a34a' : '#ef4444' ?>;">
                                    <?= e($order['status']) ?>
                                </span>
                            </td>
                            <td style="padding: 1rem;">
                                <a href="invoice.php?id=<?= $order['id'] ?>" class="btn" style="padding: 0.5rem 1rem; font-size: 0.875rem;">Facture</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($orders)): ?>
                            <tr><td colspan="5" style="padding: 2rem; text-align: center; color: #94a3b8;">Aucune commande passée.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</section>

<?php include 'footer.php'; ?>
