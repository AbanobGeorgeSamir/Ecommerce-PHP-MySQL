<?php
require_once '../../includes/app.php';
include '../auth.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    set_flash('Product deleted successfully.', 'success');
}

redirect('../dashboard.php');
