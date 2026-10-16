<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

$authority = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];

if (($authority != 'A' && $authority != 'Admin') || $is_2fa_passed !== true) {
    echo '<script>alert("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

$tanggal_hari_ini = date('Y-m-d');
$bulan_ini = date('m');
$tahun_ini  = date('Y');

// 1. Total Penjualan Hari Ini
$query_penjualan_hari_ini = mysqli_query($con, "SELECT SUM(total_harga) as total, COUNT(id_penjualan) as jumlah_transaksi FROM penjualan WHERE DATE(tanggal) = '$tanggal_hari_ini'");
$data_penjualan_hari_ini = mysqli_fetch_assoc($query_penjualan_hari_ini);
$total_penjualan_hari_ini = $data_penjualan_hari_ini['total'] ? $data_penjualan_hari_ini['total'] : 0;
$jumlah_transaksi_hari_ini = $data_penjualan_hari_ini['jumlah_transaksi'] ? $data_penjualan_hari_ini['jumlah_transaksi'] : 0;

// 2. Total Pembelian Bahan Bulan Ini (menggunakan kolom tgl_pembelian)
$query_pembelian_bulan_ini = mysqli_query($con, "SELECT SUM(total_harga) as total FROM pembelian WHERE MONTH(tgl_pembelian) = '$bulan_ini' AND YEAR(tgl_pembelian) = '$tahun_ini'");
$data_pembelian_bulan_ini = mysqli_fetch_assoc($query_pembelian_bulan_ini);
$total_pembelian_bulan_ini = $data_pembelian_bulan_ini['total'] ? $data_pembelian_bulan_ini['total'] : 0;

// 3. Total Penjualan Bulan Ini
$query_penjualan_bulan_ini = mysqli_query($con, "SELECT SUM(total_harga) as total FROM penjualan WHERE MONTH(tanggal) = '$bulan_ini' AND YEAR(tanggal) = '$tahun_ini'");
$data_penjualan_bulan_ini = mysqli_fetch_assoc($query_penjualan_bulan_ini);
$total_penjualan_bulan_ini = $data_penjualan_bulan_ini['total'] ? $data_penjualan_bulan_ini['total'] : 0;

// Estimasi Laba Bulan Ini (Penjualan - Pembelian)
$laba_bulan_ini = $total_penjualan_bulan_ini - $total_pembelian_bulan_ini;

// 4. Statistik Menu Aktif (status = tersedia)
$query_menu_aktif = mysqli_query($con, "SELECT COUNT(*) as total FROM menu WHERE status = 'tersedia'");
$data_menu_aktif = mysqli_fetch_assoc($query_menu_aktif);
$total_menu_aktif = $data_menu_aktif['total'];

// 5. Transaksi Terbaru (5 Data)
$query_transaksi_terbaru = mysqli_query($con, "SELECT * FROM penjualan ORDER BY tanggal DESC LIMIT 5");

// 6. Top 5 Menu Terlaris (menggunakan id_menu dan jumlah di penjualan_detail)
$query_menu_terlaris = mysqli_query($con, "SELECT menu.nama_menu, SUM(penjualan_detail.jumlah) as total_terjual 
                                      FROM penjualan_detail 
                                      JOIN menu ON penjualan_detail.id_menu = menu.id 
                                      GROUP BY penjualan_detail.id_menu 
                                      ORDER BY total_terjual DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Dashboard Admin - Bakul Sega Bu Lastri</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <?php include '../css.php'; ?>
</head>

<body class="theme-red">
    <!-- Top Bar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
                <a href="javascript:void(0);" class="bars"></a>
                <a class="navbar-brand" href="index.php">BAKUL SEGA BU LASTRI - ADMIN</a>
            </div>
        </div>
    </nav>

    <!-- Left Sidebar -->
    <section>
        <?php include '../sidebaradmin.php'; ?>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>DASHBOARD UTAMA ADMIN</h2>
            </div>

            <!-- Widgets Summary -->
            <div class="row clearfix">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-pink hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">attach_money</i>
                        </div>
                        <div class="content">
                            <div class="text">PENJUALAN HARI INI</div>
                            <div class="number">Rp <?= number_format($total_penjualan_hari_ini, 0, ',', '.'); ?></div>
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
                            <div class="number"><?= $jumlah_transaksi_hari_ini; ?> Nota</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-light-green hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">trending_up</i>
                        </div>
                        <div class="content">
                            <div class="text">ESTIMASI LABA BULAN INI</div>
                            <div class="number">Rp <?= number_format($laba_bulan_ini, 0, ',', '.'); ?></div>
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
                            <div class="number"><?= $total_menu_aktif; ?> Item</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Widgets Summary -->

            <div class="row clearfix">
                <!-- Tabel Transaksi Terbaru -->
                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
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
                                        <?php if ($query_transaksi_terbaru && mysqli_num_rows($query_transaksi_terbaru) > 0): ?>
                                            <?php while ($transaksi = mysqli_fetch_assoc($query_transaksi_terbaru)): ?>
                                                <tr>
                                                    <td><b><?= htmlspecialchars($transaksi['no_nota']); ?></b></td>
                                                    <td><?= date('d/m/Y H:i', strtotime($transaksi['tanggal'])); ?></td>
                                                    <td><span class="label bg-blue"><?= htmlspecialchars($transaksi['tipe_pesanan']); ?></span></td>
                                                    <td><span class="label bg-green"><?= htmlspecialchars($transaksi['metode_bayar']); ?></span></td>
                                                    <td><b>Rp <?= number_format($transaksi['total_harga'], 0, ',', '.'); ?></b></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">Belum ada data transaksi penjualan.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top 5 Menu Terlaris -->
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>MENU TERLARIS</h2>
                        </div>
                        <div class="body">
                            <ul class="list-group">
                                <?php if ($query_menu_terlaris && mysqli_num_rows($query_menu_terlaris) > 0): ?>
                                    <?php while ($menu = mysqli_fetch_assoc($query_menu_terlaris)): ?>
                                        <li class="list-group-item">
                                            <?= htmlspecialchars($menu['nama_menu']); ?>
                                            <span class="badge bg-teal"><?= $menu['total_terjual']; ?> porsi</span>
                                        </li>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <li class="list-group-item text-center">Belum ada data penjualan menu.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../footer.php'; ?>
        </div>
    </section>

    <?php include '../script.php'; ?>
</body>

</html>