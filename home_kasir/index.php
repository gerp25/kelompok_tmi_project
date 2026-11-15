<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

// Mengambil data peran/authority dari session
$authority = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];

if (($authority != 'K' && $authority != 'Kasir' && $authority != 'kasir') || $is_2fa_passed !== true) {
    echo '<script>alert ("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

$user_display = $_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? $_SESSION['no_hp'] ?? 'Kasir';

// --- QUERY UNTUK WIDGET ATAS ---
$tanggal_sekarang = date('Y-m-d');
$bulan_sekarang = date('m');
$tahun_sekarang = date('Y');

// 1. Penjualan Hari Ini
$query_penjualan_hari = mysqli_query($con, "SELECT SUM(total_harga) AS total FROM penjualan WHERE DATE(tanggal) = '$tanggal_sekarang'");
$data_penjualan_hari = mysqli_fetch_assoc($query_penjualan_hari);
$penjualan_hari = $data_penjualan_hari['total'] ?? 0;

// 2. Transaksi Hari Ini
$query_transaksi_hari = mysqli_query($con, "SELECT COUNT(id_penjualan) AS total FROM penjualan WHERE DATE(tanggal) = '$tanggal_sekarang'");
$data_transaksi_hari = mysqli_fetch_assoc($query_transaksi_hari);
$transaksi_hari = $data_transaksi_hari['total'] ?? 0;

// 3. Total Penjualan Bulan Ini
$query_penjualan_bulan = mysqli_query($con, "SELECT SUM(total_harga) AS total FROM penjualan WHERE MONTH(tanggal) = '$bulan_sekarang' AND YEAR(tanggal) = '$tahun_sekarang'");
$data_penjualan_bulan = mysqli_fetch_assoc($query_penjualan_bulan);
$penjualan_bulan = $data_penjualan_bulan['total'] ?? 0;

// 4. Menu Tersedia 
$query_menu = mysqli_query($con, "SELECT COUNT(*) AS total FROM menu WHERE status = 'tersedia'"); 
$data_menu = mysqli_fetch_assoc($query_menu);
$menu_tersedia = $data_menu['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Dashboard Kasir - BAKUL SEGA BU LASTRI</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">
    
    <?php include '../css.php'; ?>
    
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
                        <div class="icon"><i class="material-icons">attach_money</i></div>
                        <div class="content">
                            <div class="text">PENJUALAN HARI INI</div>
                            <div class="number">Rp <?= number_format($penjualan_hari, 0, ',', '.'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-cyan hover-expand-effect">
                        <div class="icon"><i class="material-icons">shopping_cart</i></div>
                        <div class="content">
                            <div class="text">TRANSAKSI HARI INI</div>
                            <div class="number"><?= $transaksi_hari; ?> Nota</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-light-green hover-expand-effect">
                        <div class="icon"><i class="material-icons">trending_up</i></div>
                        <div class="content">
                            <div class="text">PENJUALAN BULAN INI</div>
                            <div class="number">Rp <?= number_format($penjualan_bulan, 0, ',', '.'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box bg-orange hover-expand-effect">
                        <div class="icon"><i class="material-icons">restaurant_menu</i></div>
                        <div class="content">
                            <div class="text">MENU TERSEDIA</div>
                            <div class="number"><?= $menu_tersedia; ?> Item</div>
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
                                        <?php
                                        // Menampilkan 5 transaksi terakhir secara dinamis
                                        $query_terbaru = mysqli_query($con, "SELECT * FROM penjualan ORDER BY tanggal DESC LIMIT 5");
                                        if ($query_terbaru && mysqli_num_rows($query_terbaru) > 0) {
                                            while ($data_terbaru = mysqli_fetch_assoc($query_terbaru)) {
                                                echo "<tr>";
                                                echo "<td>{$data_terbaru['no_nota']}</td>";
                                                echo "<td>{$data_terbaru['tanggal']}</td>";
                                                echo "<td><span class='label bg-blue'>{$data_terbaru['tipe_pesanan']}</span></td>";
                                                echo "<td><span class='label bg-light-green'>{$data_terbaru['metode_bayar']}</span></td>";
                                                echo "<td>Rp " . number_format($data_terbaru['total_harga'], 0, ',', '.') . "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>Belum ada transaksi penjualan terbaru.</td></tr>";
                                        }
                                        ?>
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
                                <?php
                                // Mengambil 5 menu dengan jumlah penjualan terbanyak (JOIN penjualan_detail dan menu)
                                $query_terlaris = mysqli_query($con, "
                                    SELECT menu.nama_menu, SUM(penjualan_detail.jumlah) AS total_terjual 
                                    FROM penjualan_detail 
                                    JOIN menu ON penjualan_detail.id_menu = menu.id 
                                    GROUP BY penjualan_detail.id_menu 
                                    ORDER BY total_terjual DESC 
                                    LIMIT 5
                                ");

                                if ($query_terlaris && mysqli_num_rows($query_terlaris) > 0) {
                                    while ($data_terlaris = mysqli_fetch_assoc($query_terlaris)) {
                                        echo "<li class='list-group-item'>";
                                        echo $data_terlaris['nama_menu'];
                                        echo "<span class='badge bg-teal'>{$data_terlaris['total_terjual']} porsi</span>";
                                        echo "</li>";
                                    }
                                } else {
                                    echo "<li class='list-group-item text-center'>Belum ada data penjualan</li>";
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- SELESAI KONTEN UTAMA -->

            <?php include '../footer.php'; ?>
        </div>
    </section>

    <?php include '../script.php'; ?>

</body>
</html>