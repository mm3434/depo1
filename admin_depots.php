<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/rental_cron.php';
require_login();
$_GET['m'] = 'depots';

$pdo = get_db();
$depots = $pdo->query("SELECT * FROM depots ORDER BY block, number")->fetchAll();
?>
<?php include __DIR__.'/admin_layout_top.php'; ?>

<div class="card card-glass p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Depo Tipleri / Durumları</h5>
        <span class="small text-secondary">
            <span class="badge badge-depo-free me-1">&nbsp;</span> Boş
            <span class="badge badge-depo-full ms-3 me-1">&nbsp;</span> Dolu
        </span>
    </div>
    <div class="row g-2">
        <?php foreach ($depots as $depo): 
            $isOccupied = $depo['status'] === 'occupied';
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
