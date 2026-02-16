<?php
require_once __DIR__.'/config.php';

if (is_logged_in()) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'E-posta veya şifre hatalı.';
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>DepoDaDepo - Admin Giriş</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: radial-gradient(circle at top, #1d4ed8, #020617 55%, #000 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e5e7eb;
        }
        .login-card {
            background: rgba(15,23,42,0.92);
            border-radius: 20px;
            border: 1px solid rgba(148,163,184,0.5);
            box-shadow: 0 35px 80px rgba(0,0,0,0.7);
            max-width: 420px;
            width: 100%;
        }
        .brand {
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<div class="login-card p-4 p-md-5">
    <div class="text-center mb-4">
        <div class="brand">DEPO DA DEPO</div>
        <div class="text-secondary small mt-1">Yönetim Paneli</div>
    </div>
    <?php if ($error): ?>
        <div class="alert alert-danger py-2"><?php echo e($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">E-posta</label>
            <input type="email" name="email" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
            <label class="form-label">Şifre</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-warning w-100 fw-semibold">Giriş Yap</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
