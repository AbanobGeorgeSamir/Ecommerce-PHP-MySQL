<?php require_once __DIR__ . '/app.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminPageTitle ?? 'Admin') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --admin-bg: #f1f5f7;
            --admin-ink: #18252b;
            --admin-muted: #61737a;
            --admin-brand: #22577a;
            --admin-line: rgba(24, 37, 43, 0.1);
            --admin-card: rgba(255, 255, 255, 0.88);
            --admin-shadow: 0 18px 40px rgba(28, 41, 47, 0.09);
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: "Manrope", sans-serif;
            color: var(--admin-ink);
            background:
                radial-gradient(circle at 8% 0%, rgba(255, 138, 91, 0.18), transparent 22%),
                radial-gradient(circle at 90% 10%, rgba(34, 87, 122, 0.15), transparent 24%),
                linear-gradient(180deg, #f7fafb 0%, #eef3f5 100%);
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: "Space Grotesk", sans-serif;
            letter-spacing: -0.03em;
        }

        .admin-shell {
            max-width: 1180px;
            margin: 0 auto;
            padding: 2rem 1rem 3rem;
        }

        .admin-topbar,
        .admin-panel,
        .admin-login-card {
            background: var(--admin-card);
            border: 1px solid rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            box-shadow: var(--admin-shadow);
        }

        .admin-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem 1.5rem;
            border-radius: 28px;
            margin-bottom: 1.5rem;
        }

        .admin-topbar .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: #e3eef4;
            color: var(--admin-brand);
            font-weight: 800;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .admin-panel {
            border-radius: 28px;
            padding: 1.5rem;
        }

        .admin-login-wrap {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
        }

        .admin-login-card {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
            border-radius: 28px;
        }

        .admin-table-wrap {
            overflow: hidden;
            border-radius: 24px;
            border: 1px solid var(--admin-line);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #1f3846;
            color: #fff;
            border: 0;
            padding: 1rem;
        }

        .table tbody td {
            padding: 0.95rem 1rem;
            vertical-align: middle;
            border-color: rgba(24, 37, 43, 0.06);
        }

        .btn {
            border-radius: 999px;
            font-weight: 700;
            padding: 0.72rem 1.15rem;
        }

        .btn-primary,
        .btn-success {
            border: 0;
            background: linear-gradient(135deg, var(--admin-brand), #18384d);
        }

        .btn-warning {
            border: 0;
            background: linear-gradient(135deg, #ffbf69, #ff9f45);
            color: #2f2200;
        }

        .btn-danger {
            border: 0;
            background: linear-gradient(135deg, #e76f51, #c44536);
        }

        .btn-outline-secondary {
            color: var(--admin-brand);
            border-color: rgba(34, 87, 122, 0.3);
        }

        .form-control,
        .form-select,
        textarea {
            border-radius: 18px !important;
            border: 1px solid rgba(24, 37, 43, 0.12) !important;
            padding: 0.85rem 1rem !important;
            box-shadow: none !important;
        }

        .alert {
            border: 0;
            border-radius: 18px;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .metric-card {
            padding: 1.1rem 1.2rem;
            border-radius: 22px;
            background: linear-gradient(180deg, #fff, #f6f9fb);
            border: 1px solid var(--admin-line);
        }

        .metric-card strong {
            display: block;
            font-family: "Space Grotesk", sans-serif;
            font-size: 1.45rem;
        }

        @media (max-width: 767px) {
            .admin-topbar {
                flex-direction: column;
                align-items: flex-start;
            }

        }
    </style>
</head>

<body>