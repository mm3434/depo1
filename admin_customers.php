<?php
require_once __DIR__.'/config.php';
require_login();
$_GET['m'] = 'customers';

$pdo = get_db();
$message = '';

// Kiralama bitiş kontrolü
require_once __DIR__.'/rental_cron.php';

// Yeni müşteri + kiralama kaydı
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name       = trim($_POST['full_name'] ?? '');
    $phone           = trim($_POST['phone'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $address         = trim($_POST['address'] ?? '');
    $note            = trim($_POST['note'] ?? '');
    $auth_name       = trim($_POST['auth_name'] ?? '');
    $auth_phone      = trim($_POST['auth_phone'] ?? '');
    $depot_id        = (int)($_POST['depot_id'] ?? 0);
    $start_date      = $_POST['start_date'] ?? null;
    $end_date        = $_POST['end_date'] ?: null; // boş ise NULL
    $three_digit     = str_pad((string)rand(0,999), 3, '0', STR_PAD_LEFT); // otomatik 3 haneli kod

    if ($full_name && $depot_id && $start_date) {
        try {
            $pdo->beginTransaction();

            // 1) müşteri
            $stmt = $pdo->prepare("INSERT INTO customers 
                (full_name, phone, email, address, note, authorized_name, authorized_phone)
                VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$full_name, $phone, $email, $address, $note, $auth_name, $auth_phone]);
            $customer_id = (int)$pdo->lastInsertId();

            // 2) kiralama
            $stmt = $pdo->prepare("INSERT INTO rentals 
                (depot_id, customer_id, start_date, end_date, three_digit_code, status)
                VALUES (?,?,?,?,?, 'active')");
            $stmt->execute([$depot_id, $customer_id, $start_date, $end_date, $three_digit]);

            // 3) depo durumu
            $pdo->prepare("UPDATE depots SET status='occupied' WHERE id=?")->execute([$depot_id]);

            $pdo->commit();
            $message = 'Müşteri ve kiralama başarıyla oluşturuldu. Müşteri kodu: '.$three_digit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $message = 'Hata: '.$e->getMessage();
        }
    } else {
        $message = 'Lütfen zorunlu alanları doldurun.';
    }
}

// Müsait depolar
$freeDepots = $pdo->query("
    SELECT id, code, size_m2 
    FROM depots 
    WHERE status='available' 
    ORDER BY code
")->fetchAll();

// Mevcut müşteriler + son aktif kiralama
$customers = $pdo->query("
    SELECT c.*, r.three_digit_code, r.start_date, r.end_date, r.status AS rental_status, d.code AS depot_code
    FROM customers c
    LEFT JOIN rentals r ON r.customer_id = c.id AND r.status='active'
    LEFT JOIN depots d ON d.id = r.depot_id
    ORDER BY c.id DESC
")->fetchAll();
?>
<?php include __DIR__.'/admin_layout_top.php'; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card card-glass p-3">
            <h5 class="mb-3 text-light fw-semibold" style="letter-spacing:0.03em;">
                Yeni Müşteri + Kiralama
            </h5>
            <?php if ($message): ?>
                <div class="alert alert-info py-2"><?php echo e($message); ?></div>
            <?php endif; ?>
            <form method="post" class="small">
                <div class="mb-2">
                    <label class="form-label">Ad Soyad</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="mb-2 d-flex gap-2">
                    <div class="flex-fill">
                        <label class="form-label">Telefon</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="flex-fill">
                        <label class="form-label">E-posta</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label">Adres</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label">Not</label>
                    <textarea name="note" class="form-control" rows="2"></textarea>
                </div>

                <hr class="my-2">
                <h6 class="mt-2 text-light fw-semibold">Diğer Yetkili Kişi</h6>
                <div class="mb-2">
                    <label class="form-label">Ad Soyad</label>
                    <input type="text" name="auth_name" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Telefon</label>
                    <input type="text" name="auth_phone" class="form-control">
                </div>

                <hr class="my-2">
                <h6 class="mt-2 text-light fw-semibold">Depo & Süre</h6>
                <div class="mb-2">
                    <label class="form-label">Kiralanacak Depo</label>
                    <select name="depot_id" class="form-select" required>
                        <option value="">Seçiniz...</option>
                        <?php foreach ($freeDepots as $d): ?>
                            <option value="<?php echo (int)$d['id']; ?>">
                                <?php echo e($d['code']); ?> (<?php echo (int)$d['size_m2']; ?> m²)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2 d-flex gap-2">
                    <div class="flex-fill">
                        <label class="form-label">Başlangıç Tarihi</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="flex-fill">
                        <label class="form-label">Bitiş Tarihi (opsiyonel)</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </div>
                <button class="btn btn-warning w-100 mt-2 fw-semibold">Kaydet</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-glass p-3">
            <h5 class="mb-3">Mevcut Müşteriler</h5>
            <div class="table-responsive small">
                <table class="table table-dark table-striped align-middle mb-0">
                    <thead>
                    <tr>
                        <th>Müşteri</th>
                        <th>Depo</h4></th>
                        <th>Kod</th>
                        <th>Başlangıç</th>
                        <th>Bitiş</th>
                        <th>Durum</th>
                        <th>İşlemler</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><?php echo e($c['full_name']); ?></td>
                            <td><?php echo e($c['depot_code'] ?? '-'); ?></td>
                            <td><?php echo e($c['three_digit_code'] ?? '-'); ?></td>
                            <td><?php echo e($c['start_date'] ?? '-'); ?></td>
                            <td><?php echo e($c['end_date'] ?? 'Süresiz'); ?></td>
                            <td>
                                <?php if (($c['rental_status'] ?? '') === 'active'): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pasif</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="admin_customer_edit.php?id=<?php echo (int)$c['id']; ?>" class="btn btn-sm btn-outline-warning">Düzenle</a>
                                <a href="admin_customer_view.php?id=<?php echo (int)$c['id']; ?>" class="btn btn-sm btn-outline-info">Detay</a>
                                <a href="admin_customer_delete.php?id=<?php echo (int)$c['id']; ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Bu müşteriyi ve aktif kiralamasını silmek istiyor musunuz?');">
                                    Sil
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$customers): ?>
                        <tr>
                            <td colspan="7" class="text-center text-secondary">Henüz müşteri kaydı yok.</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__.'/admin_layout_bottom.php'; ?>