<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Fichier CSS Externe -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php" class="logo">TechStore</a>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="products.php">Produits</a></li>
                
                <li>
                    <a href="cart.php" class="nav-cart-link">
                        🛒 Panier
                        <?php
                        $cart_count = 0;
                        if (isset($_SESSION['user_id'])) {
                            require_once __DIR__.'/db.php';
                            require_once __DIR__.'/helpers.php';
                            $cart_count = (int)db_scalar($conn, "SELECT SUM(quantity) FROM cart WHERE user_id = ?", 'i', [(int)$_SESSION['user_id']]);
                        } elseif (isset($_SESSION['cart'])) {
                            $cart_count = array_sum($_SESSION['cart']);
                        }
                        if ($cart_count > 0) echo '<span class="cart-badge">'.$cart_count.'</span>';
                        ?>
                    </a>
                </li>

                <!-- Admin Tools -->
                <?php if(isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin'): ?>
                    <li style="border-left: 1px solid var(--border-color); padding-left: 1rem; margin-left: 0.5rem;">
                        <span style="font-size: 0.75rem; color: #94a3b8; display: block;">ADMIN</span>
                        <div style="display: flex; gap: 1rem;">
                            <a href="admin_add_product.php" title="Ajouter Produit">➕</a>
                            <a href="admin_orders.php" title="Commandes">📦</a>
                            <a href="admin_users.php" title="Utilisateurs">👥</a>
                        </div>
                    </li>
                <?php endif; ?>

                <!-- User Account -->
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php">👤 Mon Compte</a></li>
                <?php endif; ?>

                <!-- Auth -->
                <li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="logout.php" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.875rem; background: #ef4444;">Sortir</a>
                    <?php else: ?>
                        <a href="auth.php" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.875rem;">Connexion</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </header>
    <main>
