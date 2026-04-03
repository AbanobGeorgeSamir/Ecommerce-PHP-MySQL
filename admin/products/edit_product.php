<?php
require_once '../../includes/app.php';
include '../auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_flash('Invalid product ID.', 'danger');
    redirect('../dashboard.php');
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    set_flash('Product not found.', 'danger');
    redirect('../dashboard.php');
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $stock = (int)$_POST['stock'];

    if ($name === '') {
        $error = "Name is required.";
    } elseif ($price < 0) {
        $error = "Price cannot be negative.";
    } elseif ($stock < 0) {
        $error = "Stock cannot be negative.";
    } else {

        $imagePath = $product['image'];

        if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $uploadedPath = validate_uploaded_image($_FILES['image'], dirname(__DIR__, 2) . '/uploads', $error);
            if ($uploadedPath !== null) {
                $imagePath = $uploadedPath;
            }
        }

        if (empty($error)) {

            $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, image=? WHERE id=?");

            $stmt->bind_param("ssdisi", $name, $desc, $price, $stock, $imagePath, $id);

            if ($stmt->execute()) {
                set_flash('Product updated successfully.', 'success');
                redirect('../dashboard.php');
            } else {
                $error = "Failed to update product: " . $stmt->error;
            }
        }
    }
}

$adminPageTitle = 'Edit Product';
include '../../includes/admin_head.php';
?>
<div class="admin-shell" style="max-width: 860px;">
    <div class="admin-topbar">
        <div>
            <span class="eyebrow">Catalog Update</span>
            <h2 class="mt-3 mb-1">Edit Product</h2>
            <div class="text-secondary">Refine product content, pricing, stock, and imagery.</div>
        </div>
        <a href="../dashboard.php" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="admin-panel">
        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-12">
                    <input type="text" name="name" value="<?= e($product['name']) ?>" class="form-control" required>
                </div>

                <div class="col-12">
                    <textarea name="description" class="form-control" rows="5" required><?= e($product['description']) ?></textarea>
                </div>

                <div class="col-md-6">
                    <input type="number" step="0.01" name="price" value="<?= e((string) $product['price']) ?>" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <input type="number" name="stock" value="<?= e((string) $product['stock']) ?>" class="form-control" min="1" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Current Image</label>
                    <?php if (!empty($product['image']) && file_exists('../../' . $product['image'])): ?>
                        <div>
                            <img src="../../<?= e($product['image']) ?>" alt="Current Product Image" style="width:120px; height:120px; object-fit:cover; border-radius:20px;">
                        </div>
                    <?php else: ?>
                        <div class="text-secondary">No image uploaded.</div>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Change Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary" type="submit">Update Product</button>
                <a href="../dashboard.php" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php include '../../includes/admin_footer.php'; ?>