<?php
require_once '../../includes/app.php';
include '../auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id === current_user_id()) {
    set_flash('You cannot delete the currently logged-in administrator.', 'warning');
    redirect('manage_users.php');
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

set_flash('User deleted successfully.', 'success');
redirect('manage_users.php');
