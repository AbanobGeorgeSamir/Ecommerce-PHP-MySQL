<?php
require_once 'includes/app.php';
include 'includes/header.php';

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && !empty($message) && filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $stmt = $conn->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            $success = "Message sent successfully!";
            $_POST = array();
        } else {
            $error = "Something went wrong. Please try again.";
        }

        $stmt->close();
    } else {
        $error = "All fields are required and email must be valid.";
    }
}
?>

<div class="row g-4 align-items-start">
    <div class="col-lg-7">
        <div class="checkout-panel">
            <span class="section-kicker"><i class="bi bi-envelope-paper"></i> Contact</span>
            <h2 class="mt-3 mb-3">Let’s help your customers feel heard faster.</h2>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="bi bi-person"></i> Name</label>
                        <input type="text" name="name" class="form-control" required value="<?= e($_POST['name'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold"><i class="bi bi-envelope-at"></i> Email</label>
                        <input type="email" name="email" class="form-control" required value="<?= e($_POST['email'] ?? '') ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold"><i class="bi bi-chat-dots"></i> Message</label>
                        <textarea name="message" class="form-control" rows="6" required><?= e($_POST['message'] ?? '') ?></textarea>
                    </div>
                </div>

                <button class="btn btn-primary mt-3">
                    <i class="bi bi-send"></i> Send Message
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="info-panel">
            <span class="section-kicker"><i class="bi bi-headset"></i> Reach Us</span>
            <h4 class="mt-3">Support information</h4>
            <div class="d-grid gap-3 mt-4">
                <div class="surface-card p-3">
                    <strong><i class="bi bi-envelope-fill text-danger"></i> Email</strong>
                    <div class="text-secondary small mt-1">support@EcommerceStore.com</div>
                </div>
                <div class="surface-card p-3">
                    <strong><i class="bi bi-telephone-fill text-success"></i> Phone</strong>
                    <div class="text-secondary small mt-1">+20 106 899 2594</div>
                </div>
                <div class="surface-card p-3">
                    <strong><i class="bi bi-geo-alt-fill text-primary"></i> Location</strong>
                    <div class="text-secondary small mt-1">Egypt</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>