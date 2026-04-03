<?php
require_once '../includes/app.php';

$flash = get_flash();
$error = '';
$next = safe_internal_path($_GET['next'] ?? $_POST['next'] ?? 'index.php');
$username = trim((string) ($_POST['username'] ?? ''));
$email = normalize_email($_POST['email'] ?? '');

if (is_logged_in()) {
    $destination = is_admin_user() ? 'admin/dashboard.php' : $next;
    redirect('../' . safe_internal_path($destination));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwordInput = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($username === '' || $email === '' || $passwordInput === '' || $confirmPassword === '') {
        $error = 'Please complete all required fields.';
    } elseif (!preg_match('/^[A-Za-z0-9 _.-]{3,50}$/', $username)) {
        $error = 'Username must be 3 to 50 characters and use only letters, numbers, spaces, dots, dashes, or underscores.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($passwordInput) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($passwordInput !== $confirmPassword) {
        $error = 'Password confirmation does not match.';
    } else {
        $checkStmt = $conn->prepare('SELECT id, username, email FROM users WHERE email = ? OR username = ? LIMIT 1');
        $checkStmt->bind_param('ss', $email, $username);
        $checkStmt->execute();
        $existingUser = $checkStmt->get_result()->fetch_assoc();
        $checkStmt->close();

        if ($existingUser) {
            if (strcasecmp($existingUser['email'], $email) === 0) {
                $error = 'That email address is already registered.';
            } else {
                $error = 'That username is already taken.';
            }
        } else {
            $passwordHash = password_hash($passwordInput, PASSWORD_DEFAULT);
            $role = 'user';

            $stmt = $conn->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $username, $email, $passwordHash, $role);

            if ($stmt->execute()) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $stmt->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                $_SESSION['id'] = (int) $stmt->insert_id;
                $stmt->close();

                set_flash('Account created successfully. Welcome to Ecommerce Store!', 'success');
                redirect('../' . $next);
            }

            $stmt->close();
            $error = 'We could not create your account right now. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | EcommerceStore</title>
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
                radial-gradient(circle at 15% 12%, rgba(31, 111, 120, 0.18), transparent 24%),
                radial-gradient(circle at 88% 15%, rgba(207, 92, 54, 0.18), transparent 22%),
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
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 26%),
                linear-gradient(160deg, rgba(31, 42, 45, 0.94), rgba(207, 92, 54, 0.9));
        }

        .auth-hero::after {
            content: "";
            position: absolute;
            inset: auto auto 1.2rem 1.4rem;
            width: 190px;
            height: 190px;
            border-radius: 40px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.02));
            border: 1px solid rgba(255, 255, 255, 0.12);
            transform: rotate(-14deg);
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

        .hero-points,
        .feature-list {
            display: grid;
            gap: 1rem;
            margin-top: 1.4rem;
        }

        .hero-point,
        .feature-item {
            padding: 1rem 1.1rem;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .hero-point strong {
            display: block;
            margin-bottom: 0.25rem;
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

        .form-control {
            border-radius: 18px;
            border: 1px solid var(--line);
            padding: 0.95rem 1rem;
            box-shadow: none;
            background: #fff;
        }

        .form-control:focus {
            border-color: rgba(31, 111, 120, 0.45);
            box-shadow: 0 0 0 0.25rem rgba(31, 111, 120, 0.12);
        }

        .password-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
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

        .btn-outline-secondary {
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
                max-width: 720px;
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

            .password-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="auth-shell">
        <div class="auth-card">
            <div class="row g-0">
                <div class="col-lg-6 order-lg-2">
                    <div class="auth-hero d-flex flex-column justify-content-between">
                        <div>
                            <span class="auth-kicker"><i class="bi bi-person-plus"></i> New Account</span>
                            <h1 class="mt-3 mb-3">Create your account and move from browsing to checkout without friction.</h1>
                            <p class="mb-0 text-white-50">
                                The signup flow now adds stronger validation, clearer expectations, and a friendlier first impression.
                            </p>
                        </div>

                        <div class="hero-points">
                            <div class="hero-point">
                                <strong>Cleaner onboarding</strong>
                                <span class="small text-white-50">Helpful field guidance makes the form feel lighter and easier to finish.</span>
                            </div>
                            <div class="hero-point">
                                <strong>Better account safety</strong>
                                <span class="small text-white-50">Passwords are validated more clearly and sessions are renewed after signup.</span>
                            </div>
                        </div>

                        <div class="feature-list">
                            <div class="feature-item">
                                <strong>Duplicate protection</strong>
                                <div class="small text-white-50 mt-1">The flow now catches reused emails and usernames before creating the account.</div>
                            </div>
                            <div class="feature-item">
                                <strong>Smoother next step</strong>
                                <div class="small text-white-50 mt-1">If you came here from checkout or orders, you go right back after signup.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 order-lg-1">
                    <div class="auth-form">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                            <div>
                                <span class="eyebrow"><i class="bi bi-stars"></i> Quick Signup</span>
                                <h2 class="mt-3 mb-1">Create your account</h2>
                                <div class="text-secondary">A few details now, then you are ready to shop and place orders.</div>
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
                                After signup, you will continue to <strong><?= e($next) ?></strong>.
                            </div>
                        <?php endif; ?>

                        <form method="POST" novalidate>
                            <input type="hidden" name="next" value="<?= e($next) ?>">

                            <div class="mb-3">
                                <label class="form-label" for="username">Username</label>
                                <input id="username" type="text" name="username" placeholder="Choose a username" class="form-control" required maxlength="50" value="<?= e($username) ?>" autocomplete="username">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="email">Email Address</label>
                                <input id="email" type="email" name="email" placeholder="you@example.com" class="form-control" required value="<?= e($email) ?>" autocomplete="email">
                            </div>

                            <div class="password-grid mb-2">
                                <div>
                                    <label class="form-label" for="password">Password</label>
                                    <input id="password" type="password" name="password" placeholder="At least 8 characters" class="form-control" required minlength="8" autocomplete="new-password">
                                </div>
                                <div>
                                    <label class="form-label" for="confirm_password">Confirm Password</label>
                                    <input id="confirm_password" type="password" name="confirm_password" placeholder="Repeat your password" class="form-control" required minlength="8" autocomplete="new-password">
                                </div>
                            </div>

                            <div class="field-note mb-4">Use a name you will recognize easily. Your email is stored in lowercase so login stays consistent.</div>

                            <button class="btn btn-primary w-100" type="submit">
                                <i class="bi bi-person-check me-1"></i> Create Account
                            </button>
                        </form>

                        <p class="mt-4 mb-0 text-secondary text-center">
                            Already have an account?
                            <a class="soft-link" href="login_action.php?next=<?= rawurlencode($next) ?>">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>