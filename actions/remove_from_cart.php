<?php
require_once '../includes/app.php';
normalize_cart();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id]--;

    if ($_SESSION['cart'][$id] <= 0) {
        unset($_SESSION['cart'][$id]);
    }

    $userId = current_user_id();
    if ($userId !== null) {
        if (isset($_SESSION['cart'][$id])) {
            $quantity = (int) $_SESSION['cart'][$id];
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("iii", $quantity, $userId, $id);
            $stmt->execute();
        } else {
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->bind_param("ii", $userId, $id);
            $stmt->execute();
        }
    }
}

redirect('../cart.php');
