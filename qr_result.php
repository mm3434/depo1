<?php
require_once __DIR__.'/config.php';

$depoCode = strtoupper(trim($_GET['depo'] ?? ''));
$kod      = trim($_GET['kod'] ?? '');
$error    = '';
$data     = null;

if ($depoCode && $kod) {
    $pdo = get_db();
    $stmt = $pdo->prepare("
        SELECT c.full_name, r.start_date, r.end_date, d.code AS depot_code, r.status
        FROM rentals r
        JOIN depots d   ON d.id = r.depot_id
        JOIN customers c ON c.id = r.customer_id
        WHERE d.code = ? 
          AND r.three_digit_code = ?
        ORDER BY r.id DESC
        LIMIT 1
    ");
    $stmt->execute([$depoCode, $kod]);
    $row = $stmt->fetch();
    if ($row) {
        // İsim maskeleme: N*** E****
        $parts = explode(' ', $row['full_name']);
        $first = $parts[0] ?? '';
        $last  = $parts[1] ?? '';

        $mask = function ($name) {
            if ($name === '') return '';
            $firstChar = mb_substr($name, 0, 1, 'UTF-8');
            return $firstChar . str_repeat('*', max(0, mb_strlen($name, 'UTF-8') - 1));
        };

        $maskedName = trim($mask($first).' '.$mask($last));
        $data = [
            'masked_name' => $maskedName,
            'start'       => $row['start_date'],
            'end'         => $row['end_date'],
            'depot'       => $row['depot_code'],
            'status'      => $row['status'] === 'active' ? 'Aktif' : 'Pasif',
        ];
    } else {
        $error = 'Bilgiler bulunamadı. Depo numarası veya kod hatalı olabilir.';
    }
} else {
    $error = 'Eksik bilgi gönderildi.';
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>DepoDaDepo - Sözleşme Bilgisi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: radial-gradient(circle at top, #22c55e, #020617 70%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e5e7eb;
        }
        .card-glass {
            background: rgba(15,23,42,0.95);
            border-radius: 18px;
            border: 1px solid rgba(148,163,184,0.6);
            box-shadow: 0 26px 60px rgba(0,0,0,0.7);
            max-width: 420px;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="card-glass p-4">
    <?php if ($error): ?>
        <div class="alert alert-danger small mb-3"><?php echo e($error); ?></div>
        <a href="qr_index.php" class="btn btn-outline-light btn-sm w-100">Geri Dön</a>
    <?php elseif ($data): ?>
        <h5 class="mb-3 text-center">Depo Sözleşme Bilgisi</h5>
        <ul class="list-group list-group-flush small mb-3">
            <li class="list-group-item bg-transparent text-light d-flex justify-content-between">
                <span>Müşteri</span>
                <strong><?php echo e($data['masked_name']); ?></strong>
            </li>
            <li class="list-group-item bg-transparent text-light d-flex justify-content-between">
                <span>Depo Numarası</span>
                <strong><?php echo e($data['depot']); ?></strong>
            </li>
            <li class="list-group-item bg-transparent text-light d-flex justify-content-between">
                <span>Başlangıç</span>
                <strong><?php echo e($data['start']); ?></strong>
            </li>
            <li class="list-group-item bg-transparent text-light d-flex justify-content-between">
                <span>Bitiş</span>
                <strong><?php echo e($data['end'] ?? 'Süresiz'); ?></strong>
            </li>
            <li class="list-group-item bg-transparent text-light d-flex justify-content-between">
                <span>Durum</span>
                <strong><?php echo e($data['status']); ?></strong>
            </li>
        </ul>
        <a href="qr_index.php" class="btn btn-outline-light btn-sm w-100">Yeni Sorgu</a>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
