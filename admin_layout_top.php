<?php require_once __DIR__.'/config.php'; ?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>DepoDaDepo - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        body {
            background: #0f172a;
            color: #e5e7eb;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #020617 0%, #0f172a 50%, #1e293b 100%);
        }
        .sidebar a {
            color: #9ca3af;
            font-weight: 500;
        }
        .sidebar a.active,
        .sidebar a:hover {
            color: #f97316;
            background: rgba(249,115,22,0.1);
        }
        .brand-title {
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .card-glass {
            background: rgba(15,23,42,0.85);
            border: 1px solid rgba(148,163,184,0.3);
            box-shadow: 0 18px 45px rgba(0,0,0,0.55);
            border-radius: 18px;
        }
        .badge-depo-free {
            background: rgba(22,163,74,0.15);
            color: #4ade80;
            border: 1px solid rgba(22,163,74,0.7);
        }
        .badge-depo-full {
            background: rgba(220,38,38,0.15);
            color: #fca5a5;
            border: 1px solid rgba(220,38,38,0.7);
        }

        /* FORM GÖRÜNÜRLÜĞÜ İÇİN EK STİLLER */

        /* Etiketler daha parlak ve okunaklı */
        .form-label {
            color: #f9fafb !important;   /* neredeyse beyaz */
            font-weight: 600;            /* yarı kalın */
            letter-spacing: 0.02em;      /* hafif aralıklı */
            font-size: 0.85rem;
        }

        /* Input ve textarea’lar daha belirgin olsun */
        .card-glass .form-control,
        .card-glass .form-select,
        .card-glass textarea {
            background-color: rgba(15,23,42,0.95);
            color: #e5e7eb;
            border-color: rgba(148,163,184,0.7);
            font-size: 0.85rem;
        }

        .card-glass .form-control::placeholder,
        .card-glass textarea::placeholder {
            color: #6b7280;
        }

        .card-glass .form-control:focus,
        .card-glass .form-select:focus,
        .card-glass textarea:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 0.15rem rgba(249,115,22,0.35);
        }

        /* Tablo başlıkları daha net */
        table thead th {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <aside class="col-12 col-md-3 col-lg-2 sidebar d-flex flex-column p-3">
            <div class="d-flex align-items-center mb-4">
                <i class="bi bi-box-seam-fill text-warning fs-3 me-2"></i>
                <span class="brand-title">DepoDaDepo</span>
            </div>
            <ul class="nav nav-pills flex-column mb-auto gap-1">
                <li class="nav-item">
                    <a href="admin_dashboard.php" class="nav-link <?php echo ($_GET['m'] ?? '') === 'dashboard' ? 'active' : ''; ?>">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="admin_customers.php" class="nav-link <?php echo ($_GET['m'] ?? '') === 'customers' ? 'active' : ''; ?>">
                        <i class="bi bi-people me-2"></i> Müşteriler
                    </a>
                </li>
                <li>
                    <a href="admin_depots.php" class="nav-link <?php echo ($_GET['m'] ?? '') === 'depots' ? 'active' : ''; ?>">
                        <i class="bi bi-grid-3x3-gap-fill me-2"></i> Depo Tipleri / Durumları
                    </a>
                </li>
                <li>
                    <a href="admin_depot_types.php" class="nav-link <?php echo ($_GET['m'] ?? '') === 'depot_types' ? 'active' : ''; ?>">
                        <i class="bi bi-boxes me-2"></i> Depo Tip Yönetimi
                    </a>
                </li>
                <li>
                    <a href="admin_settings.php" class="nav-link <?php echo ($_GET['m'] ?? '') === 'settings' ? 'active' : ''; ?>">
                        <i class="bi bi-gear me-2"></i> Ayarlar
                    </a>
                </li>
                <li class="mt-3 border-top pt-3">
                    <a href="admin_logout.php" class="nav-link text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Çıkış
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main content -->
        <main class="col-12 col-md-9 col-lg-10 py-4 px-4 px-lg-5">