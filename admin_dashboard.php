<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/rental_cron.php';

require_login();
$_GET['m'] = 'dashboard';

$pdo = get_db();

// Doluluk sayıları
$totalDepots = $pdo->query("SELECT COUNT(*) AS c FROM depots")->fetch()['c'] ?? 0;
$occupiedDepots = $pdo->query("SELECT COUNT(*) AS c FROM depots WHERE status = 'occupied'")->fetch()['c'] ?? 0;
$freeDepots = $totalDepots - $occupiedDepots;

// Depo kartları
$stmt = $pdo->query("
    SELECT d.*, 
           (SELECT COUNT(*) FROM rentals r WHERE r.depot_id = d.id AND r.status='active') AS active_rental_count
    FROM depots d
    ORDER BY block, number
");
$depots = $stmt->fetchAll();
?>
<?php include __DIR__.'/admin_layout_top.php'; ?>

<div class="mb-4 d-flex flex-wrap gap-3">
    <div class="card card-glass p-3 flex-fill">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-secondary text-uppercase small">Toplam Depo</span>
            <i class="bi bi-box-seam text-primary fs-3"></i>
        </div>
        <div class="fs-2 fw-bold mt-2"><?php echo $totalDepots; ?></div>
    </div>
    <div class="card card-glass p-3 flex-fill">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-secondary text-uppercase small">Dolu Depo</span>
            <i class="bi bi-circle-fill text-danger fs-3"></i>
        </div>
        <div class="fs-2 fw-bold mt-2"><?php echo $occupiedDepots; ?></div>
    </div>
    <div class="card card-glass p-3 flex-fill">
        <div class="d-flex justify-content-between align-items-center">
            <span class="text-secondary text-uppercase small">Müsait Depo</span>
            <i class="bi bi-circle-fill text-success fs-3"></i>
        </div>
        <div class="fs-2 fw-bold mt-2"><?php echo $freeDepots; ?></div>
    </div>
</div>

<div class="card card-glass p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Depo Haritası</h5>
        <span class="small text-secondary">
            <span class="badge badge-depo-free me-1">&nbsp;</span> Boş
            <span class="badge badge-depo-full ms-3 me-1">&nbsp;</span> Dolu
        </span>
    </div>
    <div class="row g-2">
        <?php foreach ($depots as $depo): 
            $isOccupied = $depo['status'] === 'occupied' || $depo['active_rental_count'] > 0;
            $badgeClass = $isOccupied ? 'badge-depo-full' : 'badge-depo-free';
        ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <div class="border rounded-3 p-2 text-center">
                    <div class="small text-secondary"><?php echo e($depo['block']); ?> Blok</div>
                    <div class="fw-bold"><?php echo e($depo['code']); ?></div>
                    <div class="small text-secondary"><?php echo (int)$depo['size_m2']; ?> m²</div>
                    <span class="badge <?php echo $badgeClass; ?> mt-1 small">
                        <?php echo $isOccupied ? 'Dolu' : 'Müsait'; ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__.'/admin_layout_bottom.php'; ?>
