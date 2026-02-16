<?php
require_once __DIR__.'/config.php';
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>DepoDaDepo - Depo Sorgu</title>
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
            background: rgba(15,23,42,0.92);
            border-radius: 18px;
            border: 1px solid rgba(148,163,184,0.6);
            box-shadow: 0 26px 60px rgba(0,0,0,0.7);
            max-width: 400px;
            width: 100%;
        }
        .brand {
            font-weight: 800;
            letter-spacing: .2em;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<div class="card-glass p-4">
    <div class="text-center mb-3">
        <div class="brand small">DEPO DA DEPO</div>
        <div class="small text-secondary mt-1">Depo Sözleşme Bilgisi</div>
    </div>
    <form method="get" action="qr_result.php" class="small">
        <div class="mb-3">
            <label class="form-label">Depo Numarası (ör: A107)</label>
            <input type="text" name="depo" class="form-control text-uppercase" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Sisteme Girilen Müşteri Telefonu</label>
            <input type="text" name="phone" class="form-control" inputmode="numeric" pattern="0[0-9]+" title="Telefon numarası 0 ile başlamalı ve sadece rakamlardan oluşmalıdır." oninput="this.value=this.value.replace(/[^0-9]/g,'');" required>
        </div>
        <button class="btn btn-success w-100 fw-semibold">Bilgileri Göster</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
