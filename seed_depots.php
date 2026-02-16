<?php
require_once __DIR__.'/config.php';

$pdo = get_db();

try {
    // Yabancı anahtarları geçici olarak devre dışı bırak
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Önce rentals'ı, sonra depots'u boşalt
    $pdo->exec("DELETE FROM rentals");
    $pdo->exec("DELETE FROM depots");

    // Sonra tekrar yabancı anahtar kontrolünü aç
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // 80 depoyu ekle
    $sql = "
    INSERT INTO depots (code, block, number, size_m2)
    SELECT 
      CONCAT(blocks.b, nums.n) AS code,
      blocks.b AS block,
      nums.n AS number,
      CASE 
        WHEN nums.n BETWEEN 101 AND 108 THEN 20
        WHEN nums.n BETWEEN 109 AND 115 THEN 30
        ELSE 35
      END AS size_m2
    FROM 
      (SELECT 'A' AS b UNION SELECT 'B' UNION SELECT 'C' UNION SELECT 'D') AS blocks,
      (SELECT 101 AS n UNION SELECT 102 UNION SELECT 103 UNION SELECT 104 UNION SELECT 105 UNION SELECT 106 UNION
              SELECT 107 UNION SELECT 108 UNION SELECT 109 UNION SELECT 110 UNION SELECT 111 UNION SELECT 112 UNION
              SELECT 113 UNION SELECT 114 UNION SELECT 115 UNION SELECT 116 UNION SELECT 117 UNION SELECT 118 UNION
              SELECT 119 UNION SELECT 120) AS nums;
    ";

    $pdo->exec($sql);

    echo "Depolar başarıyla sıfırlandı ve 80 depo eklendi.";
} catch (Throwable $e) {
    echo "HATA: " . $e->getMessage();
}