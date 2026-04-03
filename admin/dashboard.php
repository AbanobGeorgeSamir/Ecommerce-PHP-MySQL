<?php
require_once '../includes/app.php';
include 'auth.php';

$flash = get_flash();
$result = $conn->query("SELECT * FROM products ORDER BY id DESC");
$productCount = $result ? $result->num_rows : 0;
$inventoryValue = 0;
$ordersCountResult = $conn->query("SELECT COUNT(*) AS total_orders FROM orders");
$ordersCount = 0;
$messagesCountResult = $conn->query("SELECT COUNT(*) AS total_messages FROM messages");
$messagesCount = 0;
$recentMessages = [];

if ($ordersCountResult) {
    $ordersCountRow = $ordersCountResult->fetch_assoc();
    $ordersCount = (int) ($ordersCountRow['total_orders'] ?? 0);
}

if ($messagesCountResult) {
    $messagesCountRow = $messagesCountResult->fetch_assoc();
    $messagesCount = (int) ($messagesCountRow['total_messages'] ?? 0);
}

if ($result) {
    while ($inventoryRow = $result->fetch_assoc()) {
        $inventoryValue += ((float) $inventoryRow['price'] * (int) $inventoryRow['stock']);
    }
    $result->data_seek(0);
}

$messagesResult = $conn->query("SELECT id, name, email, message, created_at FROM messages ORDER BY created_at DESC, id DESC LIMIT 5");
if ($messagesResult) {
    while ($messageRow = $messagesResult->fetch_assoc()) {
        $recentMessages[] = $messageRow;
    }
}

$adminPageTitle = 'Admin Dashboard';
include '../includes/admin_head.php';
?>
<div class="admin-shell">
    <div class="admin-topbar">
        <div>
            <span class="eyebrow">Inventory Control</span>
            <h2 class="mt-3 mb-1">Admin Dashboard</h2>
            <div class="text-secondary">Monitor products, orders, manage users, and keep the catalog polished.</div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="products/add_product.php" class="btn btn-success">Add Product</a>
            <a href="orders/manage_orders.php" class="btn btn-primary">Orders</a>
            <a href="users/manage_users.php" class="btn btn-primary">Manage Users</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> mb-4"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="metric-grid">
        <div class="metric-card">
            <span class="text-secondary small">Products</span>
            <strong><?= (int) $productCount ?></strong>
        </div>
        <div class="metric-card">
            <span class="text-secondary small">Inventory Value</span>
            <strong>$<?= number_format($inventoryValue, 2) ?></strong>
        </div>
        <div class="metric-card">
            <span class="text-secondary small">Orders</span>
            <strong><?= $ordersCount ?></strong>
        </div>
        <div class="metric-card">
            <span class="text-secondary small">Messages</span>
            <strong><?= $messagesCount ?></strong>
        </div>
    </div>

    <div class="admin-panel">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h3 class="mb-0">Product Overview</h3>
            <span class="text-secondary small">Edit stock, pricing, and visuals from one place.</span>
        </div>

        <div class="admin-table-wrap">
            <table class="table table-hover align-middle text-center">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()):
                        $imagePath = product_image_src($row['image'], '../');
                    ?>
                        <tr>
                            <td><?= (int) $row['id'] ?></td>
                            <td><img src="<?= e($imagePath) ?>" alt="Product Image" style="width:72px; height:72px; object-fit:cover; border-radius:18px;"></td>
                            <td class="text-start"><?= e($row['name']) ?></td>
                            <td>$<?= number_format((float) $row['price'], 2) ?></td>
                            <td><?= (int) $row['stock'] ?></td>
                            <td>
                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <a href="products/edit_product.php?id=<?= (int) $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="products/delete_product.php?id=<?= (int) $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="admin-panel mt-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h3 class="mb-0">Recent Messages</h3>
            <span class="text-secondary small">Latest contact form submissions from customers.</span>
        </div>

        <?php if ($recentMessages === []): ?>
            <div class="alert alert-warning mb-0">No messages have been received yet.</div>
        <?php else: ?>
            <div class="d-grid gap-3">
                <?php foreach ($recentMessages as $message): ?>
                    <div class="surface-card p-3">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                            <div>
                                <strong><?= e($message['name']) ?></strong>
                                <div class="small text-secondary"><?= e($message['email']) ?></div>
                            </div>
                            <div class="small text-secondary"><?= e($message['created_at']) ?></div>
                        </div>
                        <div class="text-secondary">
                            <?= e(mb_strimwidth((string) $message['message'], 0, 220, '...')) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/admin_footer.php'; ?>