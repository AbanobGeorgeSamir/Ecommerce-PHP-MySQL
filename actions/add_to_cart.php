<?php
require_once '../includes/app.php';
normalize_cart();

if (!isset($_GET['id'])) {
    redirect('../index.php');
}

$id = (int)$_GET['id'];

if ($id <= 0) {
    set_flash('Invalid product selected.', 'danger');
    redirect('../products.php');
}

$productStmt = $conn->prepare("SELECT id, stock FROM products WHERE id = ?");
$productStmt->bind_param("i", $id);
$productStmt->execute();
$product = $productStmt->get_result()->fetch_assoc();

if (!$product) {
    set_flash('Product not found.', 'danger');
    redirect('../products.php');
}

if (!isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id] = 1;
} else {
    $_SESSION['cart'][$id]++;
}

if ($_SESSION['cart'][$id] > (int) $product['stock']) {
    $_SESSION['cart'][$id] = (int) $product['stock'];
    set_flash('You reached the available stock for this product.', 'warning');
    redirect('../cart.php');
}

$userId = current_user_id();
if ($userId !== null) {
    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $quantity = (int) $_SESSION['cart'][$id];
        $stmt->bind_param("iii", $quantity, $userId, $id);
        $stmt->execute();
    } else {
        $quantity = (int) $_SESSION['cart'][$id];
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $id, $quantity);
        $stmt->execute();
    }
}

redirect('../cart.php');
