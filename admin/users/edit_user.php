<?php
require_once '../../includes/app.php';
include '../auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    set_flash('User not found.', 'danger');
    redirect('manage_users.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

    if ($username === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please provide a valid username and email.';
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $role, $id);

        if ($stmt->execute()) {
            set_flash('User updated successfully.', 'success');
            redirect('manage_users.php');
        }

        $error = 'Could not update user.';
    }
}

$adminPageTitle = 'Edit User';
include '../../includes/admin_head.php';
?>
<div class="admin-shell" style="max-width: 760px;">
    <div class="admin-topbar">
        <div>
            <span class="eyebrow">User Profile</span>
            <h2 class="mt-3 mb-1">Edit User</h2>
            <div class="text-secondary">Adjust account details and permissions with confidence.</div>
        </div>
        <a href="manage_users.php" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="admin-panel">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="row g-3">
                <div class="col-12">
                    <input type="text" name="username" value="<?= e($user['username']) ?>" class="form-control" required>
                </div>
                <div class="col-12">
                    <input type="email" name="email" value="<?= e($user['email']) ?>" class="form-control" required>
                </div>
                <div class="col-12">
                    <select name="role" class="form-select">
                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Update User</button>
                <a href="manage_users.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php include '../../includes/admin_footer.php'; ?>