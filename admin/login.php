<?php
require_once '../includes/app.php';

$error = "";
$flash = get_flash();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "All fields are required!";
    } else {

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin' LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {

                session_regenerate_id(true);

                $_SESSION['admin'] = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['id'] = (int) $user['id'];

                redirect('dashboard.php');
            } else {
                $error = "Wrong password!";
            }
        } else {
            $error = "Admin account not found!";
        }
    }
}

$adminPageTitle = 'Admin Login';
include '../includes/admin_head.php';
?>
<div class="admin-login-wrap">
    <form method="POST" class="admin-login-card">
        <span class="eyebrow">Admin Access</span>
        <h3 class="mt-3 mb-2">Sign in to manage the store</h3>
        <p class="text-secondary mb-4">Use your administrator account to update products, users, and inventory.</p>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <input type="text" name="username" placeholder="Username" class="form-control mb-3" required>
        <input type="password" name="password" placeholder="Password" class="form-control mb-3" required>

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>
<?php include '../includes/admin_footer.php'; ?>