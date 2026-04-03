<?php
require_once '../includes/app.php';
normalize_cart();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (isset($_SESSION['cart'][$id])) {
    unset($_SESSION['cart'][$id]);

    $userId = current_user_id();
    if ($userId !== null) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("ii", $userId, $id);
        $stmt->execute();
    }
}

redirect('../cart.php');
