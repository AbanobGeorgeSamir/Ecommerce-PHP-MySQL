<?php
require_once 'includes/app.php';
normalize_cart();

$checkoutItems = [];
$checkoutTotal = 0.0;

if (!empty($_SESSION['cart'])) {
    $summaryStmt = $conn->prepare("SELECT id, name, price FROM products WHERE id = ?");
    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $productId = (int) $productId;
        $quantity = (int) $quantity;

        if ($productId <= 0 || $quantity <= 0) {
            continue;
        }

        $summaryStmt->bind_param("i", $productId);
        $summaryStmt->execute();
        $summaryResult = $summaryStmt->get_result();

        if ($summaryResult->num_rows === 0) {
            continue;
        }

        $product = $summaryResult->fetch_assoc();
        $lineTotal = (float) $product['price'] * $quantity;
        $checkoutTotal += $lineTotal;
        $checkoutItems[] = [
            'name' => $product['name'],
            'quantity' => $quantity,
            'line_total' => $lineTotal,
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['cart'])) {
        set_flash('Your cart is empty.', 'warning');
        redirect('cart.php');
    }

    $customerName = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($customerName === '' || $email === '' || $address === '') {
        set_flash('All checkout fields are required.', 'danger');
        redirect('checkout.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('Please enter a valid email address.', 'danger');
        redirect('checkout.php');
    }

    $total = 0.0;
    $products = [];
    $productStmt = $conn->prepare("SELECT id, price, stock FROM products WHERE id = ?");

    foreach ($_SESSION['cart'] as $productId => $quantity) {
        $productId = (int) $productId;
        $quantity = (int) $quantity;

        if ($productId <= 0 || $quantity <= 0) {
            continue;
        }

        $productStmt->bind_param("i", $productId);
        $productStmt->execute();
        $result = $productStmt->get_result();

        if ($result->num_rows === 0) {
            continue;
        }

        $product = $result->fetch_assoc();

        if ((int) $product['stock'] < $quantity) {
            set_flash("Not enough stock for product #{$productId}.", 'danger');
            redirect('cart.php');
        }

        $subtotal = (float) $product['price'] * $quantity;
        $total += $subtotal;
        $products[] = [
            'id' => $productId,
            'quantity' => $quantity,
            'price' => (float) $product['price'],
        ];
    }

    if ($products === []) {
        set_flash('There are no valid products in your cart.', 'danger');
        redirect('cart.php');
    }

    $conn->begin_transaction();

    try {
        $userId = current_user_id();
        $stmt = $conn->prepare("INSERT INTO orders (user_id, customer_name, email, address, total) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssd", $userId, $customerName, $email, $address, $total);
        $stmt->execute();

        $orderId = $conn->insert_id;
        $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

        foreach ($products as $item) {
            $itemStmt->bind_param("iiid", $orderId, $item['id'], $item['quantity'], $item['price']);
            $itemStmt->execute();
        }

        if ($userId !== null) {
            $deleteCartStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $deleteCartStmt->bind_param("i", $userId);
            $deleteCartStmt->execute();
        }

        $conn->commit();
        unset($_SESSION['cart']);
        set_flash('Order placed successfully. Total: $' . number_format($total, 2), 'success');
        redirect('checkout.php');
    } catch (Throwable $exception) {
        $conn->rollback();
        set_flash('Could not place the order. Please try again.', 'danger');
        redirect('checkout.php');
    }
}

include 'includes/header.php';
$flash = get_flash();
?>

<div class="checkout-panel">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <span class="section-kicker"><i class="bi bi-credit-card"></i> Checkout</span>
            <h2 class="mt-3 mb-1">Complete your order with clear, simple details.</h2>
            <p class="text-secondary mb-0">Enter your contact and delivery information to place the order.</p>
        </div>
        <span class="pill-note"><i class="bi bi-shield-check"></i> Secure order flow</span>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['cart'])): ?>
        <div class="row g-4">
            <div class="col-lg-7">
                <form method="post">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="name" placeholder="Full name" class="form-control" required value="<?= e(current_username() ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <input type="email" name="email" placeholder="Email address" class="form-control" required value="<?= e($_POST['email'] ?? current_user_email() ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <textarea name="address" placeholder="Delivery address" class="form-control" rows="5" required><?= e($_POST['address'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <?php if (!is_logged_in()): ?>
                        <div class="small text-secondary mt-3">
                            Want faster checkout next time?
                            <a href="<?= e(auth_page_url('login', 'checkout.php')) ?>" class="text-decoration-none fw-semibold">Log in</a>
                            or
                            <a href="<?= e(auth_page_url('register', 'checkout.php')) ?>" class="text-decoration-none fw-semibold">create an account</a>.
                        </div>
                    <?php endif; ?>
                    <button class="btn btn-primary mt-3" type="submit">Place Order</button>
                </form>
            </div>
            <div class="col-lg-5">
                <div class="surface-card p-3 h-100">
                    <strong class="d-block mb-3">Order Summary</strong>
                    <div class="d-grid gap-2">
                        <?php foreach ($checkoutItems as $item): ?>
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div>
                                    <div class="fw-semibold"><?= e($item['name']) ?></div>
                                    <div class="small text-secondary">Qty: <?= (int) $item['quantity'] ?></div>
                                </div>
                                <div class="fw-semibold">$<?= number_format((float) $item['line_total'], 2) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary">Total</span>
                        <strong>$<?= number_format($checkoutTotal, 2) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Your cart is empty.</div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>