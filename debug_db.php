<?php
require_once __DIR__.'/config.php';

try {
    $pdo = get_db();
    echo "<pre>";

    // Hangi veritabanına bağlıyız?
    $dbName = $pdo->query("SELECT DATABASE() AS db")->fetch()['db'] ?? 'YOK';
    echo "Aktif veritabanı: {$dbName}\n";

    // depots tablosu var mı?
    $tables = $pdo->query("SHOW TABLES LIKE 'depots'")->fetchAll();
    echo "depots tablosu var mı?: " . (count($tables) ? 'EVET' : 'HAYIR') . "\n";

    // Kaç satır var?
    $countRow = $pdo->query("SELECT COUNT(*) AS c FROM depots")->fetch();
    echo "depots satır sayısı: " . ($countRow['c'] ?? '0') . "\n";

    // Örnek birkaç depo
    $sample = $pdo->query("SELECT code, block, number, size_m2, status FROM depots LIMIT 5")->fetchAll();
    echo "Örnek depolar:\n";
    print_r($sample);

    echo "</pre>";
} catch (Throwable $e) {
    echo "HATA: " . $e->getMessage();
}