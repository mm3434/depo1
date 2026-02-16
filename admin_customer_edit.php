<?php
require_once __DIR__.'/config.php';
require_login();
$_GET['m'] = 'customers';

$pdo = get_db();
$id = (int)($_GET['id'] ?? 0);

// Müşteri + son aktif kiralama
$stmt = $pdo->prepare("
    SELECT c.*, r.id AS rental_id, r.depot_id, r.start_date, r.end_date, r.three_digit_code
    FROM customers c
    LEFT JOIN rentals r ON r.customer_id = c.id AND r.status='active'
    WHERE c.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$c = $stmt->fetch();

if (!$c) {
    die('Müşteri bulunamadı');
}

// Müsait depolar + şu anki deposu
$freeDepots = $pdo->query("
    SELECT id, code, size_m2 FROM depots 
    WHERE status='available' 
       OR id = ".(int)($c['depot_id'] ?? 0)."
    ORDER BY code
")->fetchAll();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name  = trim($_POST['full_name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $address    = trim($_POST['address'] ?? '');
    $note       = trim($_POST['note'] ?? '');
    $auth_name  = trim($_POST['auth_name'] ?? '');
    $auth_phone = trim($_POST['auth_phone'] ?? '');
    $depot_id   = (int)($_POST['depot_id'] ?? 0);
    $start_date = $_POST['start_date'] ?? null;
    $end_date   = $_POST['end_date'] ?: null;

    $pdo->beginTransaction();

    // müşteri güncelle
    $stmt = $pdo->prepare("
        UPDATE customers 
        SET full_name=?, phone=?, email=?, address=?, note=?, authorized_name=?, authorized_phone=?
        WHERE id=?
    ");
    $stmt->execute([$full_name, $phone, $email, $address, $note, $auth_name, $auth_phone, $id]);

    // kiralama varsa güncelle
    if ($c['rental_id']) {
        // eski depot'u boşalt
        if ($c['depot_id'] && $c['depot_id'] != $depot_id) {
            $pdo->prepare("UPDATE depots SET status='available' WHERE id=?")->execute([$c['depot_id']]);
        }

        $stmt = $pdo->prepare("
            UPDATE rentals
            SET depot_id=?, start_date=?, end_date=?
            WHERE id=?
        ");
        $stmt->execute([$depot_id, $start_date, $end_date, $c['rental_id']]);
    }

    // yeni depot'u dolu işaretle
    if ($depot_id) {
        $pdo->prepare("UPDATE depots SET status='occupied' WHERE id=?")->execute([$depot_id]);
    }

    $pdo->commit();
    $message = 'Müşteri bilgileri güncellendi.';
}
?>
<?php include __DIR__.'/admin_layout_top.php'; ?>

<div class="card card-glass p-3">
    <h5 class="mb-3">Müşteri Düzenle</h5>
    <?php if ($message): ?><div class="alert alert-success py-2"><?php echo e($message); ?></div><?php endif; ?>
    <form method="post" class="small">
        <div class="mb-2">
            <label class="form-label">Ad Soyad</label>
            <input type="text" name="full_name" class="form-control" value="<?php echo e($c['full_name']); ?>" required>
        </div>
        <div class="mb-2 d-flex gap-2">
            <div class="flex-fill">
                <label class="form-label">Telefon</label>
                <input type="text" name="phone" class="form-control" value="<?php echo e($c['phone']); ?>">
            </div>
            <div class="flex-fill">
                <label class="form-label">E-posta</label>
                <input type="email" name="email" class="form-control" value="<?php echo e($c['email']); ?>">
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label">Adres</label>
            <textarea name="address" class="form-control" rows="2"><?php echo e($c['address']); ?></textarea>
        </div>
        <div class="mb-2">
            <label class="form-label">Not</label>
            <textarea name="note" class="form-control" rows="2"><?php echo e($c['note']); ?></textarea>
        </div>
        <hr class="my-2">
        <h6>Diğer Yetkili Kişi</h6>
        <div class="mb-2">
            <label class="form-label">Ad Soyad</label>
            <input type="text" name="auth_name" class="form-control" value="<?php echo e($c['authorized_name']); ?>">
        </div>
        <div class="mb-2">
            <label class="form-label">Telefon</label>
            <input type="text" name="auth_phone" class="form-control" value="<?php echo e($c['authorized_phone']); ?>">
        </div>
        <?php if ($c['rental_id']): ?>
        <hr class="my-2">
        <h6>Aktif Kiralama</h6>
        <div class="mb-2">
            <label class="form-label">Depo</label>
            <select name="depot_id" class="form-select" required>
                <?php foreach ($freeDepots as $d): ?>
                    <option value="<?php echo (int)$d['id']; ?>" <?php echo $d['id'] == $c['depot_id'] ? 'selected' : ''; ?>>
                        <?php echo e($d['code']); ?> (<?php echo (int)$d['size_m2']; ?> m²)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-2 d-flex gap-2">
            <div class="flex-fill">
                <label class="form-label">Başlangıç</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo e($c['start_date']); ?>" required>
            </div>
            <div class="flex-fill">
                <label class="form-label">Bitiş (opsiyonel)</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo e($c['end_date']); ?>">
            </div>
        </div>
        <div class="mb-2">
            <label class="form-label">3 Haneli Kod</label>
            <input type="text" class="form-control" value="<?php echo e($c['three_digit_code']); ?>" readonly>
        </div>
        <?php endif; ?>
        <button class="btn btn-warning mt-2">Kaydet</button>
        <a href="admin_customers.php" class="btn btn-secondary mt-2">Geri</a>
    </form>
</div>

<?php include __DIR__.'/admin_layout_bottom.php'; ?>