<?php
require_once 'includes/app.php';
include 'includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = null;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
}
?>

<?php if (!$product): ?>
    <div class="alert alert-warning">The requested product could not be found.</div>
    <a href="products.php" class="btn btn-outline-primary">Back to Products</a>
<?php else: ?>
    <div class="checkout-panel">
        <div class="row g-4 align-items-center">
            <div class="col-md-5">
                <img src="<?= e(product_image_src($product['image'])) ?>" alt="<?= e($product['name']) ?>" class="img-fluid rounded-4 shadow-sm">
            </div>
            <div class="col-md-7">
                <span class="section-kicker"><i class="bi bi-bag-check"></i> Product Detail</span>
                <h2><?= e($product['name']) ?></h2>
                <p class="text-muted mb-4"><?= nl2br(e($product['description'])) ?></p>
                <div class="d-flex flex-wrap gap-3 align-items-center mb-4">
                    <h4 class="text-success mb-0">$<?= number_format((float) $product['price'], 2) ?></h4>
                    <span class="pill-note"><i class="bi bi-box"></i> Stock: <?= (int) $product['stock'] ?></span>
                </div>
                <?php if ((int) $product['stock'] > 0): ?>
                    <a href="actions/add_to_cart.php?id=<?= (int) $product['id'] ?>" class="btn btn-success">Add to Cart</a>
                <?php else: ?>
                    <button class="btn btn-secondary" disabled>Out of Stock</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>