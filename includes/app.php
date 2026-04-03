<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit();
}

function normalize_email(?string $email): string
{
    return strtolower(trim((string) $email));
}

function current_relative_url(): string
{
    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    $path = ltrim(str_replace('\\', '/', $scriptName), '/');

    if (str_contains($path, '/')) {
        $path = substr($path, strrpos($path, '/') + 1);
    }

    $query = trim((string) ($_SERVER['QUERY_STRING'] ?? ''));
    return $query !== '' ? $path . '?' . $query : $path;
}

function safe_internal_path(?string $path, string $default = 'index.php'): string
{
    $path = trim((string) $path);
    if ($path === '') {
        return $default;
    }

    $path = str_replace('\\', '/', $path);

    if (
        str_contains($path, '://')
        || str_starts_with($path, '//')
        || str_starts_with($path, '/')
        || str_contains($path, "\0")
    ) {
        return $default;
    }

    if (preg_match('#(^|/)\.\.(?:/|$)#', $path)) {
        return $default;
    }

    return ltrim($path, './');
}

function auth_page_url(string $page = 'login', ?string $next = null): string
{
    $file = $page === 'register' ? 'actions/register_action.php' : 'actions/login_action.php';
    $target = safe_internal_path($next ?? current_relative_url());

    return $file . '?next=' . rawurlencode($target);
}

function set_flash(string $message, string $type = 'info'): void
{
    $_SESSION['flash'] = $message;
    $_SESSION['flash_type'] = $type;
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = [
        'message' => $_SESSION['flash'],
        'type' => $_SESSION['flash_type'] ?? 'info',
    ];

    unset($_SESSION['flash'], $_SESSION['flash_type']);

    return $flash;
}

function current_user_id(): ?int
{
    if (isset($_SESSION['user_id'])) {
        return (int) $_SESSION['user_id'];
    }

    if (isset($_SESSION['id'])) {
        return (int) $_SESSION['id'];
    }

    return null;
}

function current_username(): ?string
{
    return isset($_SESSION['username']) ? (string) $_SESSION['username'] : null;
}

function current_user_email(): ?string
{
    return isset($_SESSION['email']) ? (string) $_SESSION['email'] : null;
}

function current_page_name(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    return basename((string) $scriptName);
}

function is_logged_in(): bool
{
    return current_user_id() !== null;
}

function is_admin_user(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function product_image_src(?string $imagePath, string $prefix = ''): string
{
    $imagePath = trim((string) $imagePath);
    if ($imagePath !== '') {
        $fullPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $imagePath);
        if (is_file($fullPath)) {
            return $prefix . ltrim(str_replace('\\', '/', $imagePath), '/');
        }
    }

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="600" height="400">'
        . '<rect width="100%" height="100%" fill="#e9ecef"/>'
        . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#6c757d" font-size="32" font-family="Arial">No Image</text>'
        . '</svg>';

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

function normalize_cart(): void
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
}

function require_admin_auth(): void
{
    if (!is_admin_user()) {
        set_flash('Please log in as an administrator.', 'warning');
        redirect('login.php');
    }
}

function require_user_auth(string $redirectPath = 'actions/login_action.php'): void
{
    if (!is_logged_in()) {
        set_flash('Please log in to continue.', 'warning');
        if ($redirectPath === 'actions/login_action.php') {
            redirect(auth_page_url('login'));
        }

        redirect($redirectPath);
    }
}

function validate_uploaded_image(array $file, string $destinationDir, ?string &$error = null): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $error = 'Image upload failed.';
        return null;
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $error = 'Uploaded file must be a valid image.';
        return null;
    }

    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($imageInfo['mime'], $allowedMimeTypes, true)) {
        $error = 'Only JPG, PNG, WEBP, and GIF images are allowed.';
        return null;
    }

    if (!is_dir($destinationDir) && !mkdir($destinationDir, 0755, true) && !is_dir($destinationDir)) {
        $error = 'Upload directory is not available.';
        return null;
    }

    $extension = image_type_to_extension($imageInfo[2], false) ?: 'jpg';
    $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    $targetFile = rtrim($destinationDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
        $error = 'Could not save uploaded image.';
        return null;
    }

    return 'uploads/' . $filename;
}
