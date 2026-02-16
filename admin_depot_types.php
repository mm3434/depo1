<?php
require_once __DIR__.'/config.php';

require_login();
$_GET['m'] = 'depot_types';

$pdo = get_db();

$pdo->exec("CREATE TABLE IF NOT EXISTS depot_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    size_m2 INT NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $size = (int)($_POST['size_m2'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $size <= 0) {
            $error = 'Depo tipi adı ve m² değeri zorunludur.';
        } else {
            try {
                $stmt = $pdo->prepare('INSERT INTO depot_types (name, size_m2, description) VALUES (?, ?, ?)');
                $stmt->execute([$name, $size, $description !== '' ? $description : null]);
                $message = 'Yeni depo tipi eklendi.';
            } catch (PDOException $e) {
                $error = 'Bu depo tipi adı zaten kayıtlı olabilir.';
            }
        }
    } elseif ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $size = (int)($_POST['size_m2'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        if ($id <= 0 || $name === '' || $size <= 0) {
            $error = 'Güncelleme için geçerli bilgiler giriniz.';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE depot_types SET name = ?, size_m2 = ?, description = ? WHERE id = ?');
                $stmt->execute([$name, $size, $description !== '' ? $description : null, $id]);
                $message = 'Depo tipi güncellendi.';
            } catch (PDOException $e) {
                $error = 'Güncelleme sırasında bir hata oluştu. Depo tipi adı benzersiz olmalıdır.';
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $error = 'Silme işlemi için geçerli kayıt bulunamadı.';
        } else {
            $typeStmt = $pdo->prepare('SELECT size_m2 FROM depot_types WHERE id = ? LIMIT 1');
            $typeStmt->execute([$id]);
            $type = $typeStmt->fetch();

            if (!$type) {
                $error = 'Depo tipi bulunamadı.';
            } else {
                $usageStmt = $pdo->prepare('SELECT COUNT(*) AS c FROM depots WHERE size_m2 = ?');
                $usageStmt->execute([(int)$type['size_m2']]);
                $usageCount = (int)($usageStmt->fetch()['c'] ?? 0);

                if ($usageCount > 0) {
                    $error = 'Bu m² tipinde depo bulunduğu için silinemez.';
                } else {
                    $del = $pdo->prepare('DELETE FROM depot_types WHERE id = ?');
                    $del->execute([$id]);
                    $message = 'Depo tipi silindi.';
                }
            }
        }
    }
}

$types = $pdo->query('SELECT * FROM depot_types ORDER BY size_m2 ASC, name ASC')->fetchAll();
?>
<?php include __DIR__.'/admin_layout_top.php'; ?>

<div class="row g-3">
    <div class="col-12 col-lg-4">
        <div class="card card-glass p-3 p-md-4 h-100">
            <h5 class="mb-3">Yeni Depo Tipi Ekle</h5>
            <form method="post" class="small">
                <input type="hidden" name="action" value="create">
                <div class="mb-2">
                    <label class="form-label">Tip Adı</label>
                    <input type="text" name="name" class="form-control" placeholder="Örn: Mini Depo" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">m²</label>
                    <input type="number" name="size_m2" class="form-control" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Açıklama</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Opsiyonel"></textarea>
                </div>
                <button class="btn btn-warning fw-semibold w-100">Depo Tipi Ekle</button>
            </form>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card card-glass p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Depo Tipleri</h5>
                <span class="small text-secondary">Ekle / Düzenle / Sil</span>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success py-2"><?php echo e($message); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?php echo e($error); ?></div>
            <?php endif; ?>

            <div class="small d-flex flex-column gap-2">
                <?php foreach ($types as $t): ?>
                    <div class="border rounded-3 p-3">
                        <form method="post" class="row g-2 align-items-end">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">

                            <div class="col-12 col-md-4">
                                <label class="form-label mb-1">Tip Adı</label>
                                <input type="text" name="name" class="form-control form-control-sm" value="<?php echo e($t['name']); ?>" required>
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label mb-1">m²</label>
                                <input type="number" name="size_m2" class="form-control form-control-sm" min="1" value="<?php echo (int)$t['size_m2']; ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label mb-1">Açıklama</label>
                                <input type="text" name="description" class="form-control form-control-sm" value="<?php echo e((string)($t['description'] ?? '')); ?>">
                            </div>
                            <div class="col-12 col-md-2 d-grid">
                                <button class="btn btn-sm btn-outline-warning" type="submit">Kaydet</button>
                            </div>
                        </form>

                        <form method="post" class="mt-2" onsubmit="return confirm('Bu depo tipini silmek istiyor musunuz?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Sil</button>
                        </form>
                    </div>
                <?php endforeach; ?>

                <?php if (!$types): ?>
                    <div class="text-center text-secondary border rounded-3 p-3">Henüz depo tipi eklenmedi.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__.'/admin_layout_bottom.php'; ?>
