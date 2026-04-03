<?php
require_once 'includes/app.php';
include 'includes/header.php';

$search = "";

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search = trim($_GET['search']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY id DESC");
    $searchTerm = "%{$search}%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM products ORDER BY id DESC");
}
?>

<section class="hero-panel mb-4">
    <div class="row align-items-center g-3">
        <div class="col-lg-8">
            <span class="section-kicker"><i class="bi bi-grid-3x3-gap"></i> Catalog</span>
            <h2 class="mt-3 mb-2">Explore products with a cleaner, faster shopping view.</h2>
            <p class="text-secondary mb-0">Browse the latest additions, compare essentials, and jump into product details without visual clutter.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
            <span class="pill-note"><i class="bi bi-box-seam"></i> <?= (int) ($result ? $result->num_rows : 0) ?> items shown</span>
        </div>
    </div>
</section>

<h2 class="mb-4">🛍️ Products</h2>

<?php if ($search !== ''): ?>
    <div class="alert alert-info">
        Results for: <strong><?= e($search) ?></strong>
    </div>
<?php endif; ?>

<div class="row">

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>

            <?php $imagePath = product_image_src($row['image']); ?>

            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card product-card h-100">
                    <img src="<?= e($imagePath) ?>" alt="<?= e($row['name']) ?>" class="card-img-top" style="height:200px; object-fit:cover;">
                    <div class="card-body d-flex flex-column">
                        <span class="pill-note mb-3"><i class="bi bi-tag"></i> In catalog</span>
                        <h5 class="card-title"><?= e($row['name']) ?></h5>
                        <p class="card-text text-muted" style="font-size: 14px;">
                            <?= e(mb_strimwidth((string) $row['description'], 0, 80, '...')) ?>
                        </p>
                        <h6 class="text-success mb-3">$<?= number_format((float) $row['price'], 2) ?></h6>
                        <div class="mt-auto d-grid gap-2">
                            <a href="product.php?id=<?= (int) $row['id'] ?>" class="btn btn-primary btn-sm w-100">View Details</a>
                            <a href="actions/add_to_cart.php?id=<?= (int) $row['id'] ?>" class="btn btn-success btn-sm w-100">Add to Cart</a>
                        </div>
                    </div>
                </div>
            </div>

        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-warning text-center">
                No products found.
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>