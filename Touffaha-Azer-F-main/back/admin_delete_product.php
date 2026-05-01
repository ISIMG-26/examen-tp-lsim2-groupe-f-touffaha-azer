<?php
session_start();
require_once 'db.php';
require_once 'helpers.php';

// Protection : seul un admin peut accéder à cette page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || strtolower($_SESSION['username']) !== 'admin') {
    header("Location: index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Supprimer le produit de la base de données
    // Grâce au ON DELETE CASCADE, les éléments du panier associés seront supprimés
    db_exec($conn, "DELETE FROM products WHERE id = ?", 'i', [$id]);
}

header("Location: products.php");
exit;
?>
