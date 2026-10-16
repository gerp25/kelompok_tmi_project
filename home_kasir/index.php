<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

// Mengambil data peran/authority dari session
$authority = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];

// Penyesuaian pengecekan authority untuk peran Kasir
if (($authority != 'K' && $authority != 'Kasir' && $authority != 'kasir') || $is_2fa_passed !== true) {
    echo '<script>alert ("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

$user_display = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? $_SESSION['no_hp'] ?? 'Kasir';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Dashboard Kasir - BAKUL SEGA BU LASTRI</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <link href="../AdminBSB/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="../AdminBSB/plugins/node-waves/waves.css" rel="stylesheet" />
    <link href="../AdminBSB/plugins/animate-css/animate.css" rel="stylesheet" />
    <link href="../AdminBSB/css/style.css" rel="stylesheet">
    <link href="../AdminBSB/css/themes/all-themes.css" rel="stylesheet" />
</head>

<body class="theme-red">
    <div class="overlay"></div>

    <?php include '../navbar.php'; ?>
    <section>
        <?php include '../sidebarkasir.php'; ?>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>DASHBOARD KASIR</h2>
            </div>

            <!-- KONTEN UTAMA DASBOR KASIR -->
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>SELAMAT DATANG</h2>
                            <small>Panel Transaksi Kasir Bakul Sega Bu Lastri</small>
                        </div>
                        <div class="body">
                            <h4>Halo, <?= htmlspecialchars($user_display); ?>!</h4>
                            <p>Anda telah sukses terhubung ke Panel Kasir <b>Bakul Sega Bu Lastri</b>. Silakan gunakan menu di samping untuk memproses transaksi penjualan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../footer.php'; ?>

        </div>
    </section>

    <script src="../AdminBSB/plugins/jquery/jquery.min.js"></script>
    <script src="../AdminBSB/plugins/bootstrap/js/bootstrap.js"></script>
    <script src="../AdminBSB/plugins/node-waves/waves.js"></script>
    <script src="../AdminBSB/js/admin.js"></script>
</body>
</html>