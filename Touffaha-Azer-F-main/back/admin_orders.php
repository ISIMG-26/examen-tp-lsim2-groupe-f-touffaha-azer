<?php
require_once 'db.php';
require_once 'helpers.php';
include 'header.php';

// Protection admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || strtolower($_SESSION['role']) !== 'admin') {
    echo "<section><p style='text-alig  n: center; margin-top: 2rem; color: #ef4444;'>Accès refusé. Cette page est réservée aux administrateurs.</p></section>";
    include 'footer.php';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int) $_POST['order_id'];
    $new_status = trim($_POST['status']);

    db_exec($conn, "UPDATE orders SET status = ? WHERE id = ?", 'si', [$new_status, $order_id]);
    $success = "Statut mis à jour en '" . $new_status . "'.";
}

// Suppression d'une commande
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    db_exec($conn, "DELETE FROM orders WHERE id = ?", 'i', [$delete_id]);
    header("Location: admin_orders.php?success=Commande+supprimée");
    exit;
}

// Recherche
$search = trim($_GET['search'] ?? '');
$query = "SELECT o.*, u.email FROM orders o JOIN users u ON o.user_id = u.id";
$params = [];
$types = "";

if ($search) {
    $query .= " WHERE o.full_name LIKE ? OR o.id LIKE ?";
    $params = ["%$search%", "%$search%"];
    $types = "ss";
}
$query .= " ORDER BY o.created_at DESC";

$orders = db_fetch_all($conn, $query, $types, $params);

// Statistiques
$stats_total_ca = db_scalar($conn, "SELECT SUM(total_ttc) FROM orders WHERE status != 'Annulée'");
$stats_count_orders = db_scalar($conn, "SELECT COUNT(*) FROM orders");
$stats_pending_ca = db_scalar($conn, "SELECT SUM(total_ttc) FROM orders WHERE status = 'Non Payée'");
$stats_count_products = db_scalar($conn, "SELECT COUNT(*) FROM products");

if (isset($_GET['success'])) $success = $_GET['success'];
?>

<section>
    <h1 style="text-align: center; margin-bottom: 2rem;">Gestion des Commandes</h1>

    <?php if (isset($success)): ?>
        <div style="background: #dcfce7; color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem; max-width: 1200px; margin-inline: auto;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques Dashboard -->
    <div style="max-width: 1200px; margin: 0 auto 2rem auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem;">
        <div style="background: var(--card-bg); padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border-color); text-align: center;">
            <p style="color: #94a3b8; font-size: 0.875rem; margin-bottom: 0.5rem;">Chiffre d'Affaires (Total)</p>
            <h2 style="font-size: 1.75rem; color: #22c55e;"><?= number_format((float)$stats_total_ca, 2, ',', ' ') ?> DT</h2>
        </div>
        <div style="background: var(--card-bg); padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border-color); text-align: center;">
            <p style="color: #94a3b8; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Commandes</p>
            <h2 style="font-size: 1.75rem; color: var(--primary-color);"><?= $stats_count_orders ?></h2>
        </div>
        <div style="background: var(--card-bg); padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border-color); text-align: center;">
            <p style="color: #94a3b8; font-size: 0.875rem; margin-bottom: 0.5rem;">En attente de paiement</p>
            <h2 style="font-size: 1.75rem; color: #ef4444;"><?= number_format((float)$stats_pending_ca, 2, ',', ' ') ?> DT</h2>
        </div>
        <div style="background: var(--card-bg); padding: 1.5rem; border-radius: 1rem; border: 1px solid var(--border-color); text-align: center;">
            <p style="color: #94a3b8; font-size: 0.875rem; margin-bottom: 0.5rem;">Produits Actifs</p>
            <h2 style="font-size: 1.75rem; color: #fbbf24;"><?= $stats_count_products ?></h2>
        </div>
    </div>

    <!-- Barre de Recherche -->
    <div style="max-width: 1200px; margin: 0 auto 2rem auto;">
        <form method="GET" style="display: flex; gap: 1rem;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher par client ou N° commande..." 
                   style="flex: 1; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background: var(--card-bg); color: var(--text-color);">
            <button type="submit" class="btn">Filtrer</button>
            <?php if($search): ?>
                <a href="admin_orders.php" class="btn" style="background: #64748b;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div
        style="max-width: 1200px; margin: 0 auto; background: var(--card-bg); padding: 2rem; border-radius: 1rem; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                    <th style="padding: 1rem;">ID</th>
                    <th style="padding: 1rem;">Client</th>
                    <th style="padding: 1rem;">Date</th>
                    <th style="padding: 1rem;">Total</th>
                    <th style="padding: 1rem;">Paiement</th>
                    <th style="padding: 1rem;">Statut</th>
                    <th style="padding: 1rem;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 1rem;">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></td>
                        <td style="padding: 1rem;">
                            <strong><?= htmlspecialchars($order['full_name']) ?></strong><br>
                            <small style="color: #94a3b8;"><?= htmlspecialchars($order['email']) ?></small>
                        </td>
                        <td style="padding: 1rem;"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                        <td style="padding: 1rem; font-weight: bold;"><?= number_format($order['total_ttc'], 2, ',', ' ') ?>
                            DT</td>
                        <td style="padding: 1rem;"><?= htmlspecialchars($order['payment_method']) ?></td>
                        <td style="padding: 1rem;">
                            <span style="padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 500; 
                            background: <?= ($order['status'] === 'Non Payée') ? '#fee2e2' : '#dcfce7' ?>; 
                            color: <?= ($order['status'] === 'Non Payée') ? '#ef4444' : '#16a34a' ?>;">
                                <?= htmlspecialchars($order['status']) ?>
                            </span>
                        </td>
                        <td style="padding: 1rem;">
                            <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                <select name="status"
                                    style="padding: 0.5rem; border-radius: 0.5rem; border: 1px solid var(--border-color); background: var(--bg-color); color: var(--text-color); font-size: 0.875rem;">
                                    <option value="Payée" <?= $order['status'] === 'Payée' ? 'selected' : '' ?>>Payée</option>
                                    <option value="Non Payée" <?= $order['status'] === 'Non Payée' ? 'selected' : '' ?>>Non
                                        Payée</option>
                                    <option value="Livrée" <?= $order['status'] === 'Livrée' ? 'selected' : '' ?>>Livrée
                                    </option>
                                    <option value="Annulée" <?= $order['status'] === 'Annulée' ? 'selected' : '' ?>>Annulée
                                    </option>
                                </select>
                                <button type="submit" class="btn"
                                    style="padding: 0.5rem 1rem; font-size: 0.875rem;">OK</button>
                                <a href="invoice.php?id=<?= $order['id'] ?>" class="btn"
                                    style="padding: 0.5rem 1rem; font-size: 0.875rem; background: #64748b;">Détails</a>
                                <a href="admin_orders.php?delete_id=<?= $order['id'] ?>" class="btn"
                                    style="padding: 0.5rem 1rem; font-size: 0.875rem; background: #ef4444;"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?');">🗑️</a>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" style="padding: 2rem; text-align: center; color: #94a3b8;">Aucune commande trouvée.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include 'footer.php'; ?>