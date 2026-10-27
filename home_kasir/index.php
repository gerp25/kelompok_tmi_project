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

            <!-- WIDGET INFO BOX ATAS -->
            <div class="row clearfix">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-pink hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">attach_money</i>
                        </div>
                        <div class="content">
                            <div class="text">PENJUALAN HARI INI</div>
                            <div class="number">Rp 0</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-cyan hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">shopping_cart</i>
                        </div>
                        <div class="content">
                            <div class="text">TRANSAKSI HARI INI</div>
                            <div class="number">0 Nota</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-light-green hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">trending_up</i>
                        </div>
                        <div class="content">
                            <div class="text">TOTAL PENJUALAN BULAN INI</div>
                            <div class="number">Rp 6.000</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-orange hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">restaurant_menu</i>
                        </div>
                        <div class="content">
                            <div class="text">MENU TERSEDIA</div>
                            <div class="number">2 Item</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KONTEN UTAMA: TABEL TRANSAKSI & MENU TERLARIS -->
            <div class="row clearfix">
                <!-- Tabel Transaksi Terbaru -->
                <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
                    <div class="card">
                        <div class="header">
                            <h2>TRANSAKSI PENJUALAN TERBARU</h2>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table class="table table-hover dashboard-task-infos">
                                    <thead>
                                        <tr>
                                            <th>No. Nota</th>
                                            <th>Tanggal</th>
                                            <th>Tipe</th>
                                            <th>Metode</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>NOTA-20261013-0001</td>
                                            <td>13/10/2026 12:22</td>
                                            <td><span class="label bg-blue">Ditempat</span></td>
                                            <td><span class="label bg-light-green">Tunai</span></td>
                                            <td>Rp 6.000</td>
                                        </tr>
                                        <tr>
                                            <td>NOTA-20260929-0001</td>
                                            <td>29/09/2026 11:42</td>
                                            <td><span class="label bg-blue">Ditempat</span></td>
                                            <td><span class="label bg-light-green">Tunai</span></td>
                                            <td>Rp 200.000</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List Menu Terlaris -->
                <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                    <div class="card">
                        <div class="header">
                            <h2>MENU TERLARIS</h2>
                        </div>
                        <div class="body">
                            <ul class="list-group">
                                <li class="list-group-item">
                                    Gudeg 
                                    <span class="badge bg-teal">20 porsi</span>
                                </li>
                                <li class="list-group-item">
                                    Oseng Gambas 
                                    <span class="badge bg-teal">1 porsi</span>
                                </li>
                                <li class="list-group-item">
                                    Oseng Kangkung 
                                    <span class="badge bg-teal">1 porsi</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- SELESAI KONTEN UTAMA -->

            <?php include '../footer.php'; ?>

        </div>
    </section>

    <script src="../AdminBSB/plugins/jquery/jquery.min.js"></script>
    <script src="../AdminBSB/plugins/bootstrap/js/bootstrap.js"></script>
    <script src="../AdminBSB/plugins/node-waves/waves.js"></script>
    <script src="../AdminBSB/js/admin.js"></script>
</body>
</html>