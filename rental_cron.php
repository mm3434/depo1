<?php
// rental_cron.php
require_once __DIR__.'/config.php';
$pdo = get_db();

// Bugünden eski bitiş tarihli aktif kiralamaları pasifle
$pdo->exec("
    UPDATE rentals 
    SET status='passive'
    WHERE status='active'
      AND end_date IS NOT NULL
      AND end_date < CURDATE()
");

// Bu kiralamalara ait depoları tekrar müsait yap
$pdo->exec("
    UPDATE depots d
    SET d.status = 'available'
    WHERE d.id IN (
        SELECT depot_id FROM rentals 
        WHERE status='passive' 
          AND end_date IS NOT NULL 
          AND end_date < CURDATE()
    )
      AND NOT EXISTS (
        SELECT 1 FROM rentals r 
        WHERE r.depot_id = d.id AND r.status='active'
    )
");
