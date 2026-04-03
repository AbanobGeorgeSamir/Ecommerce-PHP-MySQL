<?php
require_once '../../includes/app.php';
include '../auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = normalize_email($_POST['email'] ?? '');
    $passwordInput = $_POST['password'] ?? '';
    $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'user';

    if ($username === '' || $email === '' || $passwordInput === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
        $checkStmt->bind_param("ss", $email, $username);
        $checkStmt->execute();
        $existing = $checkStmt->get_result();
        $checkStmt->close();

        if ($existing && $existing->num_rows > 0) {
            $error = 'Email or username already exists.';
        } else {
            $password = password_hash($passwordInput, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $password, $role);

            if ($stmt->execute()) {
                set_flash('User created successfully.', 'success');
                redirect('manage_users.php');
            }

            $error = 'Could not create user right now.';
        }
    }
}

$adminPageTitle = 'Add User';
include '../../includes/admin_head.php';
?>
<div class="admin-shell" style="max-width: 760px;">
    <div class="admin-topbar">
        <div>
            <span class="eyebrow">New Account</span>
            <h2 class="mt-3 mb-1">Add User</h2>
            <div class="text-secondary">Create a new storefront or administrator account.</div>
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
                    <input type="text" name="username" placeholder="Username" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <input type="email" name="email" placeholder="Email" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <input type="password" name="password" placeholder="Password" class="form-control" required>
                </div>
                <div class="col-12">
                    <select name="role" class="form-select">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Add User</button>
                <a href="manage_users.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php include '../../includes/admin_footer.php'; ?>