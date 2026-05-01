<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Veuillez vous connecter.']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$product_id = (int)($_POST['product_id'] ?? 0);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Produit invalide.']);
    exit;
}

// Vérifier si déjà dans la wishlist
$exists = db_scalar($conn, "SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?", 'ii', [$user_id, $product_id]);

if ($exists) {
    db_exec($conn, "DELETE FROM wishlist WHERE id = ?", 'i', [$exists]);
    echo json_encode(['success' => true, 'message' => 'Retiré de la liste de souhaits.', 'action' => 'removed']);
} else {
    db_exec($conn, "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)", 'ii', [$user_id, $product_id]);
    echo json_encode(['success' => true, 'message' => 'Ajouté à la liste de souhaits !', 'action' => 'added']);
}
