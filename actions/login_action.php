<?php
require_once '../includes/app.php';

$flash = get_flash();
$error = '';
$next = safe_internal_path($_GET['next'] ?? $_POST['next'] ?? 'index.php');
$prefillEmail = normalize_email($_POST['email'] ?? '');

if (is_logged_in()) {
    $destination = is_admin_user() ? 'admin/dashboard.php' : $next;
    redirect('../' . safe_internal_path($destination));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');

    if ($prefillEmail === '' || $password === '') {
        $error = 'Enter both your email address and password.';
    } elseif (!filter_var($prefillEmail, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare('SELECT id, username, email, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $prefillEmail);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$user || !password_verify($password, $user['password'])) {
            $error = 'We could not match that email and password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['id'] = (int) $user['id'];

            set_flash('Welcome back, ' . $user['username'] . '!', 'success');

            if ($user['role'] === 'admin') {
                redirect('../admin/dashboard.php');
            }

            redirect('../' . $next);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | EcommerceStore</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --ink: #1f2a2d;
            --muted: #607074;
            --brand: #cf5c36;
            --brand-deep: #a84323;
            --accent: #1f6f78;
            --accent-soft: rgba(31, 111, 120, 0.12);
            --surface: rgba(255, 252, 248, 0.85);
            --surface-strong: rgba(255, 255, 255, 0.94);
            --line: rgba(31, 42, 45, 0.1);
            --shadow: 0 24px 60px rgba(49, 37, 22, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            color: var(--ink);
            font-family: "Manrope", sans-serif;
            background:
                radial-gradient(circle at 12% 12%, rgba(207, 92, 54, 0.18), transparent 24%),
                radial-gradient(circle at 88% 14%, rgba(31, 111, 120, 0.18), transparent 20%),
                linear-gradient(145deg, #fcf5ec 0%, #f3e9dd 50%, #ede4d8 100%);
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: "Space Grotesk", sans-serif;
            letter-spacing: -0.03em;
        }

        .auth-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
        }

        .auth-card {
            width: 100%;
            max-width: 1120px;
            border-radius: 34px;
            overflow: hidden;
            background: var(--surface);
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }

        .auth-hero {
            position: relative;
            height: 100%;
            padding: 2.4rem;
            color: #fff;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, 0.18), transparent 24%),
                linear-gradient(160deg, rgba(31, 42, 45, 0.96), rgba(31, 111, 120, 0.92));
        }

        .auth-hero::after {
            content: "";
            position: absolute;
            inset: auto 1.5rem 1.5rem auto;
            width: 170px;
            height: 170px;
            border-radius: 32px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0.03));
            border: 1px solid rgba(255, 255, 255, 0.12);
            transform: rotate(16deg);
        }

        .auth-hero>* {
            position: relative;
            z-index: 1;
        }

        .auth-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.85rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .hero-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .hero-metric,
        .feature-item {
            padding: 1rem 1.1rem;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .hero-metric strong {
            display: block;
            font-size: 1.4rem;
        }

        .feature-list {
            display: grid;
            gap: 1rem;
            margin-top: 1rem;
        }

        .auth-form {
            padding: 2.4rem;
            background: var(--surface-strong);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 0.8rem;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .form-label {
            font-weight: 700;
            margin-bottom: 0.45rem;
        }

        .form-control,
        .input-group-text {
            border-radius: 18px;
            border: 1px solid var(--line);
            padding: 0.95rem 1rem;
            box-shadow: none;
            background: #fff;
        }

        .input-group .form-control {
            border-right: 0;
            border-radius: 18px 0 0 18px;
        }

        .input-group .btn {
            border-radius: 0 18px 18px 0;
        }

        .form-control:focus {
            border-color: rgba(31, 111, 120, 0.45);
            box-shadow: 0 0 0 0.25rem rgba(31, 111, 120, 0.12);
        }

        .field-note {
            color: var(--muted);
            font-size: 0.92rem;
        }

        .btn-primary {
            border: 0;
            border-radius: 999px;
            padding: 0.95rem 1.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--brand), var(--brand-deep));
            box-shadow: 0 14px 24px rgba(207, 92, 54, 0.22);
        }

        .btn-outline-secondary,
        .btn-outline-dark {
            border-radius: 999px;
            font-weight: 700;
        }

        .soft-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
        }

        .soft-link:hover {
            text-decoration: underline;
        }

        .alert {
            border: 0;
            border-radius: 18px;
        }

        .next-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.65rem 0.9rem;
            border-radius: 18px;
            background: #f5f3ef;
            color: var(--muted);
            font-weight: 600;
        }

        @media (max-width: 991px) {
            .auth-card {
                max-width: 680px;
            }

            .auth-hero {
                min-height: 320px;
            }
        }

        @media (max-width: 575px) {

            .auth-form,
            .auth-hero {
                padding: 1.5rem;
            }

            .hero-metrics {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="auth-shell">
        <div class="auth-card">
            <div class="row g-0">
                <div class="col-lg-6">
                    <div class="auth-hero d-flex flex-column justify-content-between">
                        <div>
                            <span class="auth-kicker"><i class="bi bi-person-check"></i> Welcome Back</span>
                            <h1 class="mt-3 mb-3">Sign in and pick up exactly where you left off.</h1>
                            <p class="mb-0 text-white-50">
                                Your cart, checkout, and order history stay easy to reach with a cleaner and more reliable sign-in flow.
                            </p>
                        </div>

                        <div class="hero-metrics">
                            <div class="hero-metric">
                                <strong>1 step</strong>
                                <span class="small text-white-50">Fast access to checkout and saved order history.</span>
                            </div>
                            <div class="hero-metric">
                                <strong>Safer session</strong>
                                <span class="small text-white-50">Fresh session IDs after login for a stronger auth flow.</span>
                            </div>
                        </div>

                        <div class="feature-list">
                            <div class="feature-item">
                                <strong>Smarter redirects</strong>
                                <div class="small text-white-50 mt-1">If a page sent you here, we return you there after login.</div>
                            </div>
                            <div class="feature-item">
                                <strong>Clearer feedback</strong>
                                <div class="small text-white-50 mt-1">Validation and error states now guide the user more gently.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="auth-form">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <span class="eyebrow"><i class="bi bi-shield-lock"></i> Secure Login</span>
                                <h2 class="mt-3 mb-1">Login to your account</h2>
                                <div class="text-secondary">Use your email and password to continue shopping.</div>
                            </div>
                            <a href="../index.php" class="btn btn-outline-secondary btn-sm">Back Home</a>
                        </div>

                        <?php if ($flash): ?>
                            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
                        <?php endif; ?>

                        <?php if ($error !== ''): ?>
                            <div class="alert alert-danger"><?= e($error) ?></div>
                        <?php endif; ?>

                        <?php if ($next !== 'index.php'): ?>
                            <div class="next-chip mb-4">
                                <i class="bi bi-arrow-return-right"></i>
                                You will go back to <strong><?= e($next) ?></strong> after login.
                            </div>
                        <?php endif; ?>

                        <form method="POST" novalidate>
                            <input type="hidden" name="next" value="<?= e($next) ?>">

                            <div class="mb-3">
                                <label class="form-label" for="email">Email Address</label>
                                <input id="email" type="email" name="email" class="form-control" required value="<?= e($prefillEmail) ?>" placeholder="you@example.com" autocomplete="email">
                            </div>

                            <div class="mb-2">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group">
                                    <input id="password" type="password" name="password" class="form-control" required placeholder="Enter your password" autocomplete="current-password">
                                    <button class="btn btn-outline-dark toggle-password" type="button" data-target="password" aria-label="Show password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="field-note mb-4">Tip: email matching is now case-insensitive, so `User@Mail.com` and `user@mail.com` work the same.</div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Login
                            </button>

                            <p class="mt-4 mb-0 text-secondary text-center">
                                Don&apos;t have an account?
                                <a class="soft-link" href="register_action.php?next=<?= rawurlencode($next) ?>">Create one here</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.toggle-password').forEach(function(button) {
            button.addEventListener('click', function() {
                var target = document.getElementById(button.getAttribute('data-target'));
                if (!target) {
                    return;
                }

                var nextType = target.type === 'password' ? 'text' : 'password';
                target.type = nextType;
                button.innerHTML = nextType === 'password' ?
                    '<i class="bi bi-eye"></i>' :
                    '<i class="bi bi-eye-slash"></i>';
            });
        });
    </script>
</body>

</html>