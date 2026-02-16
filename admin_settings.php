<?php
require_once __DIR__.'/config.php';

require_login();
$_GET['m'] = 'settings';

$pdo = get_db();
$adminId = (int)($_SESSION['admin_id'] ?? 0);

$stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM admins WHERE id = ? LIMIT 1');
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $newPasswordConfirm = $_POST['new_password_confirm'] ?? '';

    if ($name === '' || $email === '') {
        $error = 'Ad soyad ve e-posta alanları zorunludur.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Geçerli bir e-posta adresi giriniz.';
    } else {
        $passwordUpdateSql = '';
        $params = [$name, $email];

        $wantsPasswordChange = ($currentPassword !== '' || $newPassword !== '' || $newPasswordConfirm !== '');

        if ($wantsPasswordChange) {
            if (!password_verify($currentPassword, $admin['password_hash'])) {
                $error = 'Mevcut şifre hatalı.';
            } elseif (strlen($newPassword) < 6) {
                $error = 'Yeni şifre en az 6 karakter olmalıdır.';
            } elseif ($newPassword !== $newPasswordConfirm) {
                $error = 'Yeni şifre ve tekrar şifre eşleşmiyor.';
            } else {
                $passwordUpdateSql = ', password_hash = ?';
                $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
            }
        }

        if ($error === '') {
            $params[] = $adminId;
            $update = $pdo->prepare("UPDATE admins SET name = ?, email = ?{$passwordUpdateSql} WHERE id = ?");

            try {
                $update->execute($params);
                $_SESSION['admin_name'] = $name;
                $message = 'Ayarlar başarıyla güncellendi.';

                $stmt->execute([$adminId]);
                $admin = $stmt->fetch();
            } catch (PDOException $e) {
                $error = 'Bu e-posta adresi başka bir hesapta kayıtlı olabilir.';
            }
        }
    }
}
?>
<?php include __DIR__.'/admin_layout_top.php'; ?>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card card-glass p-3 p-md-4">
            <h5 class="mb-3">Ayarlar</h5>
            <p class="small text-secondary mb-3">Profil bilgilerinizi güncelleyebilir, isterseniz şifrenizi değiştirebilirsiniz.</p>

            <?php if ($message): ?>
                <div class="alert alert-success py-2"><?php echo e($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?php echo e($error); ?></div>
            <?php endif; ?>

            <form method="post" class="small">
                <div class="mb-3">
                    <label class="form-label">Ad Soyad</label>
                    <input type="text" name="name" class="form-control" value="<?php echo e($admin['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">E-posta</label>
                    <input type="email" name="email" class="form-control" value="<?php echo e($admin['email']); ?>" required>
                </div>

                <hr class="my-3">
                <h6 class="mb-2">Şifre Değiştir (Opsiyonel)</h6>

                <div class="mb-2">
                    <label class="form-label">Mevcut Şifre</label>
                    <input type="password" name="current_password" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Yeni Şifre</label>
                    <input type="password" name="new_password" class="form-control" minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Yeni Şifre (Tekrar)</label>
                    <input type="password" name="new_password_confirm" class="form-control" minlength="6">
                </div>

                <button class="btn btn-warning fw-semibold">Kaydet</button>
            </form>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card card-glass p-3 p-md-4 h-100">
            <h6 class="mb-3">Bilgi</h6>
            <ul class="small text-secondary mb-0 ps-3">
                <li>Şifre değişikliği için mevcut şifrenizi girmeniz gerekir.</li>
                <li>Yeni şifre en az 6 karakter olmalıdır.</li>
                <li>E-posta adresi benzersiz olmalıdır.</li>
            </ul>
        </div>
    </div>
</div>

<?php include __DIR__.'/admin_layout_bottom.php'; ?>
