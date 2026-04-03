<?php
require_once 'includes/app.php';

$userId = current_user_id();
if ($userId === null) {
    set_flash('Please log in to view your orders.', 'warning');
    redirect(auth_page_url('login', 'orders.php'));
}

$ordersStmt = $conn->prepare("
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
    WHERE o.user_id = ?
    GROUP BY o.id, o.customer_name, o.email, o.address, o.total, o.status, o.created_at
    ORDER BY o.created_at DESC, o.id DESC
");
$ordersStmt->bind_param("i", $userId);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();

$orders = [];
$totalSpent = 0.0;

while ($order = $ordersResult->fetch_assoc()) {
    $orders[] = $order;
    $totalSpent += (float) $order['total'];
}

$deliveredCount = 0;
foreach ($orders as $orderSummary) {
    if (($orderSummary['status'] ?? '') === 'delivered') {
        $deliveredCount++;
    }
}

$itemsStmt = $conn->prepare("
    SELECT
        oi.order_id,
        p.name AS product_name,
        oi.quantity,
        oi.price
    FROM order_items oi
    INNER JOIN orders o ON o.id = oi.order_id
    INNER JOIN products p ON p.id = oi.product_id
    WHERE o.user_id = ?
    ORDER BY oi.order_id DESC, oi.id ASC
");
$itemsStmt->bind_param("i", $userId);
$itemsStmt->execute();
$itemsResult = $itemsStmt->get_result();

$orderItems = [];
while ($item = $itemsResult->fetch_assoc()) {
    $orderItems[(int) $item['order_id']][] = $item;
}

include 'includes/header.php';
?>

<section class="hero-panel mb-4">
    <div class="row align-items-center g-3">
        <div class="col-lg-8">
            <span class="section-kicker"><i class="bi bi-receipt-cutoff"></i> Your Orders</span>
            <h2 class="mt-3 mb-2">Track every order you have placed from one clean page.</h2>
            <p class="text-secondary mb-0">Review purchased items, totals, delivery details, and order status whenever you need them.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <span class="pill-note"><i class="bi bi-cash-stack"></i> Total spent: $<?= number_format($totalSpent, 2) ?></span>
        </div>
    </div>
</section>

<?php if ($orders !== []): ?>
    <div class="stats-grid mb-4">
        <div class="stat-chip">
            <strong><?= count($orders) ?></strong>
            <span class="text-secondary">Orders placed</span>
        </div>
        <div class="stat-chip">
            <strong><?= $deliveredCount ?></strong>
            <span class="text-secondary">Delivered orders</span>
        </div>
        <div class="stat-chip">
            <strong>$<?= number_format($totalSpent, 2) ?></strong>
            <span class="text-secondary">Lifetime spending</span>
        </div>
    </div>
<?php endif; ?>

<?php if ($orders === []): ?>
    <div class="empty-state text-center">
        <span class="section-kicker"><i class="bi bi-bag-x"></i> No Orders Yet</span>
        <h2 class="mt-3">You have not placed any orders yet.</h2>
        <p class="text-secondary">Start shopping and your order history will appear here automatically.</p>
        <a href="products.php" class="btn btn-primary">Browse Products</a>
    </div>
<?php else: ?>
    <div class="d-grid gap-4">
        <?php foreach ($orders as $order): ?>
            <?php
            $status = $order['status'];
            $statusClass = match ($status) {
                'pending' => 'warning',
                'processing' => 'primary',
                'shipped' => 'info',
                'delivered' => 'success',
                'cancelled' => 'danger',
                default => 'secondary',
            };
            $orderId = (int) $order['id'];
            ?>
            <section class="checkout-panel">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <span class="section-kicker"><i class="bi bi-hash"></i> Order #<?= $orderId ?></span>
                        <h3 class="mt-3 mb-1"><?= e($order['customer_name']) ?></h3>
                        <div class="text-secondary small"><?= e($order['email']) ?> | <?= e($order['created_at']) ?></div>
                    </div>
                    <div class="text-end">
                        <span class="badge text-bg-<?= $statusClass ?>" style="font-size:0.9rem; padding:0.6rem 0.8rem; border-radius:999px;">
                            <?= e(ucfirst($status)) ?>
                        </span>
                        <div class="fw-bold mt-2">$<?= number_format((float) $order['total'], 2) ?></div>
                        <div class="small text-secondary"><?= (int) $order['total_items'] ?> item(s)</div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="surface-card p-3 h-100">
                            <strong class="d-block mb-2">Delivery Details</strong>
                            <div class="small text-secondary mb-2">Shipping address</div>
                            <div><?= nl2br(e($order['address'])) ?></div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="surface-card p-3 h-100">
                            <strong class="d-block mb-3">Items in this order</strong>
                            <?php if (!empty($orderItems[$orderId])): ?>
                                <div class="d-grid gap-2">
                                    <?php foreach ($orderItems[$orderId] as $item): ?>
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 border rounded-4 px-3 py-2" style="border-color: rgba(31, 42, 45, 0.08) !important;">
                                            <div>
                                                <div class="fw-semibold"><?= e($item['product_name']) ?></div>
                                                <div class="small text-secondary">Quantity: <?= (int) $item['quantity'] ?></div>
                                            </div>
                                            <div class="fw-semibold">$<?= number_format((float) $item['price'], 2) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-secondary">No items found for this order.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>