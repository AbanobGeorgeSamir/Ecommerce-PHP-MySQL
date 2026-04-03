<?php
require_once '../../includes/app.php';
include '../auth.php';

$allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $status = trim($_POST['status'] ?? '');

    if ($orderId <= 0 || !in_array($status, $allowedStatuses, true)) {
        set_flash('Invalid order update request.', 'danger');
        redirect('manage_orders.php');
    }

    $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $updateStmt->bind_param("si", $status, $orderId);

    if ($updateStmt->execute()) {
        set_flash('Order status updated successfully.', 'success');
    } else {
        set_flash('Could not update order status.', 'danger');
    }

    redirect('manage_orders.php');
}

$ordersSql = "
    SELECT
        o.id,
        o.customer_name,
        o.email,
        o.address,
        o.total,
        o.status,
        o.created_at,
        COUNT(oi.id) AS item_lines,
        COALESCE(SUM(oi.quantity), 0) AS total_items
    FROM orders o
    LEFT JOIN order_items oi ON oi.order_id = o.id
    GROUP BY o.id, o.customer_name, o.email, o.address, o.total, o.status, o.created_at
    ORDER BY o.created_at DESC, o.id DESC
";

$ordersResult = $conn->query($ordersSql);
$orders = [];
$pendingCount = 0;
$totalRevenue = 0.0;

if ($ordersResult) {
    while ($order = $ordersResult->fetch_assoc()) {
        $orders[] = $order;
        if ($order['status'] === 'pending') {
            $pendingCount++;
        }
        $totalRevenue += (float) $order['total'];
    }
}

$itemRows = [];
$itemResult = $conn->query("
    SELECT
        oi.order_id,
        p.name AS product_name,
        oi.quantity,
        oi.price
    FROM order_items oi
    INNER JOIN products p ON p.id = oi.product_id
    ORDER BY oi.order_id DESC, oi.id ASC
");

if ($itemResult) {
    while ($item = $itemResult->fetch_assoc()) {
        $itemRows[(int) $item['order_id']][] = $item;
    }
}

$flash = get_flash();
$adminPageTitle = 'Manage Orders';
include '../../includes/admin_head.php';
?>
<div class="admin-shell">
    <div class="admin-topbar">
        <div>
            <span class="eyebrow">Order Management</span>
            <h2 class="mt-3 mb-1">Orders</h2>
            <div class="text-secondary">Track purchases, inspect items, and update fulfillment status.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="../dashboard.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> mb-4"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="metric-grid">
        <div class="metric-card">
            <span class="text-secondary small">Total Orders</span>
            <strong><?= count($orders) ?></strong>
        </div>
        <div class="metric-card">
            <span class="text-secondary small">Pending Orders</span>
            <strong><?= $pendingCount ?></strong>
        </div>
        <div class="metric-card">
            <span class="text-secondary small">Recorded Revenue</span>
            <strong>$<?= number_format($totalRevenue, 2) ?></strong>
        </div>
    </div>

    <div class="admin-panel">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h3 class="mb-0">Order Overview</h3>
            <span class="text-secondary small">Each order includes customer details, purchased items, and a status editor.</span>
        </div>

        <?php if ($orders === []): ?>
            <div class="alert alert-warning mb-0">No orders have been placed yet.</div>
        <?php else: ?>
            <div class="d-grid gap-4">
                <?php foreach ($orders as $order): ?>
                    <?php
                    $orderId = (int) $order['id'];
                    $status = $order['status'];
                    $statusClass = match ($status) {
                        'pending' => 'warning',
                        'processing' => 'primary',
                        'shipped' => 'info',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    };
                    ?>
                    <div class="admin-panel" style="padding: 1.25rem; background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(247,250,251,0.9));">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                            <div>
                                <div class="small text-secondary">Order #<?= $orderId ?></div>
                                <h4 class="mb-1"><?= e($order['customer_name']) ?></h4>
                                <div class="text-secondary small"><?= e($order['email']) ?> | <?= e($order['created_at']) ?></div>
                            </div>
                            <div class="text-end">
                                <span class="badge text-bg-<?= $statusClass ?>" style="font-size:0.9rem; padding:0.6rem 0.8rem; border-radius:999px;">
                                    <?= e(ucfirst($status)) ?>
                                </span>
                                <div class="mt-2 fw-bold">$<?= number_format((float) $order['total'], 2) ?></div>
                                <div class="small text-secondary"><?= (int) $order['total_items'] ?> item(s)</div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="surface-card p-3 h-100">
                                    <strong class="d-block mb-2">Customer Details</strong>
                                    <div class="small text-secondary mb-2">Shipping address</div>
                                    <div><?= nl2br(e($order['address'])) ?></div>
                                </div>
                            </div>

                            <div class="col-lg-5">
                                <div class="surface-card p-3 h-100">
                                    <strong class="d-block mb-3">Ordered Items</strong>
                                    <?php if (!empty($itemRows[$orderId])): ?>
                                        <div class="d-grid gap-2">
                                            <?php foreach ($itemRows[$orderId] as $item): ?>
                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 border rounded-4 px-3 py-2" style="border-color: rgba(24,37,43,0.08) !important;">
                                                    <div>
                                                        <div class="fw-semibold"><?= e($item['product_name']) ?></div>
                                                        <div class="small text-secondary">Qty: <?= (int) $item['quantity'] ?></div>
                                                    </div>
                                                    <div class="fw-semibold">$<?= number_format((float) $item['price'], 2) ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-secondary">No order items found for this order.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="surface-card p-3 h-100">
                                    <strong class="d-block mb-3">Update Status</strong>
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?= $orderId ?>">
                                        <select name="status" class="form-select mb-3">
                                            <?php foreach ($allowedStatuses as $option): ?>
                                                <option value="<?= e($option) ?>" <?= $status === $option ? 'selected' : '' ?>>
                                                    <?= e(ucfirst($option)) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-primary w-100">Save Status</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include '../../includes/admin_footer.php'; ?>