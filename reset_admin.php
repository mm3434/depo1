<?php
require_once __DIR__.'/config.php';

$pdo = get_db();

$email = 'admin@test.com';   // İstersen burayı değiştir
$newPass = '123456';         // Yeni şifre

$hash = password_hash($newPass, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE email = ?");
$stmt->execute([$hash, $email]);

if ($stmt->rowCount() > 0) {
    echo "Şifre güncellendi: {$email} / {$newPass}";
} else {
    echo "Bu e-posta ile admin bulunamadı.";
}