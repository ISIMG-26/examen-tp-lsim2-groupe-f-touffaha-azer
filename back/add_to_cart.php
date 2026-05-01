<?php
session_start();
require_once 'db.php';
require_once 'helpers.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté.']);
    exit;
}

$product_id = $_POST['product_id'] ?? null;
$user_id = $_SESSION['user_id'];

if ($product_id) {
    // Vérifier si le produit existe déjà dans le panier
    $existing = db_fetch_one($conn, "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?", 'ii', [(int)$user_id, (int)$product_id]);
    
    if ($existing) {
        // Produit déjà dans le panier → incrementer la quantité
        db_exec($conn, "UPDATE cart SET quantity = quantity + 1 WHERE id = ?", 'i', [(int)$existing['id']]);
    } else {
        // Nouveau produit → ajouter au panier
        db_exec($conn, "INSERT INTO cart (user_id, product_id) VALUES (?, ?)", 'ii', [(int)$user_id, (int)$product_id]);
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
}
