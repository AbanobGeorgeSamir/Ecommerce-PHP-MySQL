<?php
require_once '../../includes/app.php';
include '../auth.php';

$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
$flash = get_flash();
$adminPageTitle = 'Manage Users';
include '../../includes/admin_head.php';
?>
<div class="admin-shell">
    <div class="admin-topbar">
        <div>
            <span class="eyebrow">User Management</span>
            <h2 class="mt-3 mb-1">Manage Users</h2>
            <div class="text-secondary">Review roles, update accounts, and keep access organized.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="../dashboard.php" class="btn btn-outline-secondary">Back</a>
            <a href="add_user.php" class="btn btn-primary">Add New User</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> mb-4"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="admin-panel">
        <div class="admin-table-wrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int) $row['id'] ?></td>
                            <td><?= e($row['username']) ?></td>
                            <td><?= e($row['email']) ?></td>
                            <td><?= e(ucfirst($row['role'])) ?></td>
                            <td>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="edit_user.php?id=<?= (int) $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete_user.php?id=<?= (int) $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../../includes/admin_footer.php'; ?>