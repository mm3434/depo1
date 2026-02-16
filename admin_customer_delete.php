<?php
require_once __DIR__.'/config.php';
require_login();

$pdo = get_db();
$id = (int)($_GET['id'] ?? 0);

// Müşterinin aktif kiralamasını ve deposunu bul
$stmt = $pdo->prepare("
    SELECT r.id AS rental_id, r.depot_id
    FROM rentals r
    WHERE r.customer_id = ? AND r.status='active'
    LIMIT 1
");
$stmt->execute([$id]);
$r = $stmt->fetch();

$pdo->beginTransaction();

// aktif kiralama varsa pasif yap
if ($r) {
    $pdo->prepare("UPDATE rentals SET status='passive' WHERE id=?")->execute([$r['rental_id']]);
    // depoyu boşalt
    if ($r['depot_id']) {
        $pdo->prepare("UPDATE depots SET status='available' WHERE id=?")->execute([$r['depot_id']]);
    }
}

// istersen müşteriyi tamamen silebilirsin:
$pdo->prepare("DELETE FROM customers WHERE id=?")->execute([$id]);

$pdo->commit();

header('Location: admin_customers.php');
exit;