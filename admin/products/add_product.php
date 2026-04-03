<?php
require_once '../../includes/app.php';
include '../auth.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 0);

    if ($name === '') {
        $error = "Product name is required.";
    } elseif ($price < 0) {
        $error = "Price cannot be negative.";
    } elseif ($quantity < 0) {
        $error = "Stock cannot be negative.";
    } else {
        $imagePath = validate_uploaded_image($_FILES['image'] ?? [], dirname(__DIR__, 2) . '/uploads', $error);

        if (empty($error)) {
            $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdis", $name, $desc, $price, $quantity, $imagePath);
            if ($stmt->execute()) {
                set_flash('Product added successfully.', 'success');
                redirect('../dashboard.php');
            }

            $error = "Could not save the product.";
        }
    }
}

$adminPageTitle = 'Add Product';
include '../../includes/admin_head.php';
?>
<div class="admin-shell" style="max-width: 860px;">
    <div class="admin-topbar">
        <div>
            <span class="eyebrow">Catalog Entry</span>
            <h2 class="mt-3 mb-1">Add Product</h2>
            <div class="text-secondary">Create a polished product listing with pricing, stock, and an image.</div>
        </div>
        <a href="../dashboard.php" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="admin-panel">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-12">
                    <input type="text" name="name" placeholder="Product name" class="form-control" required>
                </div>
                <div class="col-12">
                    <textarea name="description" placeholder="Product description" class="form-control" rows="5"></textarea>
                </div>
                <div class="col-md-6">
                    <input type="number" name="price" placeholder="Price" class="form-control" step="0.01" required>
                </div>
                <div class="col-md-6">
                    <input type="number" name="quantity" placeholder="Quantity" class="form-control" min="1" required>
                </div>
                <div class="col-12">
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-success" type="submit">Add Product</button>
                <a href="../dashboard.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php include '../../includes/admin_footer.php'; ?>