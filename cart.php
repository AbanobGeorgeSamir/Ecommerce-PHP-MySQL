<?php
require_once 'includes/app.php';
normalize_cart();
include 'includes/header.php';

$total = 0;
$flash = get_flash();
$productStmt = $conn->prepare("SELECT id, name, price FROM products WHERE id = ?");
$invalidCartItems = [];
?>

<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['cart'])): ?>
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <span class="section-kicker"><i class="bi bi-cart3"></i> Your Cart</span>
            <h2 class="mt-3 mb-0">Review your items before checkout.</h2>
        </div>
        <span class="pill-note"><i class="bi bi-receipt"></i> Ready to confirm your order</span>
    </div>
    <div class="table-shell">
        <table class="table">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>

            <?php foreach ($_SESSION['cart'] as $id => $quantity): ?>
                <?php
                $id = (int) $id;
                $productStmt->bind_param("i", $id);
                $productStmt->execute();
                $result = $productStmt->get_result();

                if ($result->num_rows === 0) {
                    $invalidCartItems[] = $id;
                    continue;
                }

                $product = $result->fetch_assoc();
                $subtotal = (float) $product['price'] * (int) $quantity;
                $total += $subtotal;
                ?>
                <tr>
                    <td><?= e($product['name']) ?></td>
                    <td>$<?= number_format((float) $product['price'], 2) ?></td>
                    <td><?= (int) $quantity ?></td>
                    <td>$<?= number_format($subtotal, 2) ?></td>
                    <td>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="actions/add_to_cart.php?id=<?= $id ?>" class="btn btn-outline-primary btn-sm">+1</a>
                            <a href="actions/remove_from_cart.php?id=<?= $id ?>" class="btn btn-outline-danger btn-sm">-1</a>
                            <a href="actions/delete_from_cart.php?id=<?= $id ?>" class="btn btn-danger btn-sm">Remove All</a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <?php
    if ($invalidCartItems !== []) {
        foreach ($invalidCartItems as $invalidItemId) {
            unset($_SESSION['cart'][$invalidItemId]);
        }
    }
    ?>

    <div class="checkout-panel mt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <div class="text-secondary">Order total</div>
            <h3 class="mb-0">$<?= number_format($total, 2) ?></h3>
        </div>
        <span class="pill-note"><i class="bi bi-basket2"></i> <?= array_sum($_SESSION['cart']) ?> item(s)</span>
        <div class="d-flex gap-2 flex-wrap">
            <a href="products.php" class="btn btn-outline-secondary">Continue Shopping</a>
            <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
        </div>
    </div>
<?php else: ?>
    <div class="empty-state text-center">
        <span class="section-kicker"><i class="bi bi-cart-x"></i> Empty Cart</span>
        <h2 class="mt-3">Your cart is empty.</h2>
        <p class="text-secondary">Add a few products to continue with checkout.</p>
        <a href="products.php" class="btn btn-primary">Browse Products</a>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>