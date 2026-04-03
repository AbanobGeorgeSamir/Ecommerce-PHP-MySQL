<?php require_once __DIR__ . '/app.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecommerce Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --bg: #f5efe6;
            --paper: rgba(255, 252, 247, 0.86);
            --surface: #fffaf3;
            --surface-strong: #fff;
            --ink: #1f2a2d;
            --muted: #5b6a6f;
            --line: rgba(31, 42, 45, 0.12);
            --brand: #cf5c36;
            --brand-deep: #a84323;
            --accent: #1f6f78;
            --shadow-lg: 0 24px 60px rgba(55, 40, 20, 0.12);
            --shadow-md: 0 12px 32px rgba(55, 40, 20, 0.08);
            --radius-xl: 28px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            font-family: "Manrope", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(207, 92, 54, 0.16), transparent 28%),
                radial-gradient(circle at 85% 12%, rgba(31, 111, 120, 0.18), transparent 24%),
                linear-gradient(180deg, #fbf6ef 0%, #f5efe6 44%, #efe7db 100%);
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        .navbar-brand {
            font-family: "Space Grotesk", sans-serif;
            letter-spacing: -0.03em;
        }

        .container {
            max-width: 1200px;
            width: 100%;
            padding: 0 15px;
        }

        .site-shell {
            position: relative;
            padding-bottom: 3rem;
        }

        .site-nav {
            margin: 18px auto 0;
            background: rgba(31, 42, 45, 0.88);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            box-shadow: var(--shadow-md);
        }

        .navbar-brand {
            font-size: 1.15rem;
            color: #fff3ea !important;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.82) !important;
            font-weight: 600;
            border-radius: 999px;
            padding: 0.55rem 0.85rem !important;
        }

        .nav-link:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.08);
        }

        .nav-link.is-active {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.14);
        }

        .navbar .form-control {
            min-width: 220px;
        }

        .search-bar {
            background: rgba(255, 255, 255, 0.12);
            border-radius: 999px;
            padding: 4px;
        }

        .search-bar .form-control {
            border: 0;
            background: transparent;
            color: #fff;
            box-shadow: none;
        }

        .search-bar .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .page-frame {
            padding-top: 2rem;
        }

        .btn {
            border-radius: 999px;
            font-weight: 700;
            letter-spacing: -0.01em;
            padding: 0.75rem 1.2rem;
        }

        .btn-primary,
        .btn-success {
            border: 0;
            background: linear-gradient(135deg, var(--brand), var(--brand-deep));
            box-shadow: 0 12px 22px rgba(207, 92, 54, 0.24);
        }

        .btn-primary:hover,
        .btn-success:hover {
            background: linear-gradient(135deg, var(--brand-deep), var(--brand));
        }

        .btn-light {
            background: #fff8f1;
            border-color: #fff8f1;
            color: var(--ink);
        }

        .btn-outline-primary {
            color: var(--brand);
            border-color: rgba(207, 92, 54, 0.35);
        }

        .btn-outline-primary:hover {
            background: var(--brand);
            border-color: var(--brand);
        }

        .btn-outline-secondary {
            color: var(--accent);
            border-color: rgba(31, 111, 120, 0.28);
        }

        .btn-outline-secondary:hover {
            background: var(--accent);
            border-color: var(--accent);
        }

        .card,
        .surface-card,
        .hero-panel,
        .info-panel,
        .checkout-panel,
        .empty-state,
        .table-shell {
            background: var(--paper);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-md);
            backdrop-filter: blur(18px);
        }

        .card {
            overflow: hidden;
        }

        .hero-panel {
            padding: 3rem;
            box-shadow: var(--shadow-lg);
        }

        .hero-panel+h2.mb-4 {
            display: none;
        }

        .info-panel,
        .checkout-panel,
        .empty-state {
            padding: 2rem;
        }

        .section-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.85rem;
            border-radius: 999px;
            background: rgba(31, 111, 120, 0.12);
            color: var(--accent);
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 1.75rem;
        }

        .stat-chip {
            padding: 1rem 1.1rem;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(31, 42, 45, 0.08);
        }

        .stat-chip strong {
            display: block;
            font-size: 1.4rem;
            font-family: "Space Grotesk", sans-serif;
        }

        .product-card .card-body {
            padding: 1.25rem;
        }

        .product-card:hover {
            transform: translateY(-4px);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            box-shadow: 0 24px 40px rgba(55, 40, 20, 0.12);
        }

        .pill-note {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.55rem 0.9rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.72);
            color: var(--muted);
            font-weight: 700;
        }

        .form-control,
        .form-select,
        textarea {
            border-radius: 18px !important;
            border: 1px solid rgba(31, 42, 45, 0.12) !important;
            padding: 0.85rem 1rem !important;
            background: rgba(255, 255, 255, 0.82) !important;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus,
        textarea:focus {
            border-color: rgba(31, 111, 120, 0.45) !important;
            box-shadow: 0 0 0 0.25rem rgba(31, 111, 120, 0.12) !important;
        }

        .table-shell {
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #203033;
            color: #fff;
            border: 0;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            background: rgba(255, 255, 255, 0.5);
            border-color: rgba(31, 42, 45, 0.06);
        }

        .alert {
            border: 0;
            border-radius: 20px;
            box-shadow: var(--shadow-md);
        }

        .alert-info {
            background: #e5f3f6;
            color: #16434a;
        }

        .alert-success {
            background: #dff2e3;
            color: #225b2d;
        }

        .alert-warning {
            background: #fff0cc;
            color: #735204;
        }

        .alert-danger {
            background: #ffe3de;
            color: #8a2d18;
        }

        .site-footer {
            margin-top: 2rem;
            padding: 2rem 0 0;
        }

        .site-footer-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border-radius: 28px;
            background: rgba(31, 42, 45, 0.92);
            color: rgba(255, 255, 255, 0.8);
            box-shadow: var(--shadow-md);
        }

        .site-footer-card strong {
            color: #fff;
        }

        @media (max-width: 991px) {
            .hero-panel {
                padding: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .page-frame {
                padding-top: 1rem;
            }

            .hero-panel,
            .info-panel,
            .checkout-panel,
            .empty-state {
                padding: 1.35rem;
            }

            .site-footer-card {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="site-shell">
        <?php $currentPage = current_page_name(); ?>
        <nav class="navbar navbar-expand-lg navbar-dark site-nav">
            <div class="container">
                <a class="navbar-brand" href="index.php">Ecommerce Store</a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">

                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'products.php' || $currentPage === 'product.php' ? 'is-active' : '' ?>" href="products.php">
                                <i class="bi bi-bag"></i> Products
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'about.php' ? 'is-active' : '' ?>" href="about.php">
                                <i class="bi bi-info-circle"></i> About
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link <?= $currentPage === 'contact.php' ? 'is-active' : '' ?>" href="contact.php">
                                <i class="bi bi-envelope"></i> Contact
                            </a>
                        </li>
                    </ul>

                    <form class="d-flex search-bar" method="GET" action="products.php">
                        <input
                            class="form-control me-2"
                            type="search"
                            name="search"
                            placeholder="Search products..."
                            value="<?= e($_GET['search'] ?? '') ?>">
                        <button class="btn btn-outline-light" type="submit">Search</button>
                    </form>

                    <div class="d-flex align-items-center gap-2 ms-3">
                        <a href="cart.php" class="btn btn-light">Cart</a>
                        <?php if (is_logged_in()): ?>
                            <a href="orders.php" class="btn <?= $currentPage === 'orders.php' ? 'btn-light' : 'btn-outline-light' ?>">Orders</a>
                        <?php endif; ?>
                        <?php if (is_admin_user()): ?>
                            <a href="admin/dashboard.php" class="btn btn-outline-light">Admin</a>
                        <?php endif; ?>
                        <?php if (current_username() !== null): ?>
                            <span class="text-light small">Hi, <?= e(current_username()) ?></span>
                            <a href="actions/logout_action.php" class="btn btn-outline-light">Logout</a>
                        <?php else: ?>
                            <a href="<?= e(auth_page_url('login')) ?>" class="btn btn-outline-light">Login</a>
                            <a href="<?= e(auth_page_url('register')) ?>" class="btn btn-light">Register</a>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </nav>

        <div class="container page-frame">