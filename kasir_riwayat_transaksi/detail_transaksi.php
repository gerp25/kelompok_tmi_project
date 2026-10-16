<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

$authority = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];

if (($authority != 'K' && $authority != 'Kasir') || $is_2fa_passed !== true) {
    echo '<script>alert("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

// Ambil ID penjualan dari URL
$id_penjualan = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_penjualan <= 0) {
    echo '<script>alert("ID Transaksi tidak valid."); window.location.href="index.php";</script>';
    exit();
}

// Ambil data utama penjualan
$query_penjualan = mysqli_query($con, "SELECT * FROM penjualan WHERE id_penjualan = $id_penjualan");
$data_penjualan = mysqli_fetch_assoc($query_penjualan);

if (!$data_penjualan) {
    echo '<script>alert("Data transaksi tidak ditemukan."); window.location.href="index.php";</script>';
    exit();
}

$no_nota = mysqli_real_escape_string($con, $data_penjualan['no_nota']);

// Ambil data detail item penjualan (menggunakan m.id karena primary key tabel menu adalah id)
$query_detail = mysqli_query($con, "SELECT dp.*, m.nama_menu FROM penjualan_detail dp JOIN menu m ON dp.id_menu = m.id WHERE dp.no_nota = '$no_nota'");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Detail Transaksi - Bakul Sega Bu Lastri</title>
    <!-- Google Fonts -->
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
                <a class="navbar-brand" href="../home_kasir/index.php">BAKUL SEGA BU LASTRI - KASIR</a>
            </div>
        </div>
    </nav>

    <!-- Left Sidebar -->
    <section>
        <?php include '../sidebarkasir.php'; ?>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>DETAIL TRANSAKSI PENJUALAN</h2>
                        </div>
                        <div class="body">
                            <!-- Informasi Utama Nota -->
                            <div class="row clearfix" style="margin-bottom: 20px;">
                                <div class="col-md-6">
                                    <table class="table table-striped">
                                        <tr>
                                            <th style="width: 40%;">No. Nota</th>
                                            <td>: <strong><?= htmlspecialchars($data_penjualan['no_nota']); ?></strong></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal & Waktu</th>
                                            <td>: <?= date('d-m-Y H:i:s', strtotime($data_penjualan['tanggal'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tipe Pesanan</th>
                                            <td>: <span class="label bg-cyan"><?= htmlspecialchars($data_penjualan['tipe_pesanan'] ?? '-'); ?></span></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-striped">
                                        <tr>
                                            <th style="width: 40%;">Metode Bayar</th>
                                            <td>: <span class="label bg-teal"><?= htmlspecialchars($data_penjualan['metode_bayar']); ?></span></td>
                                        </tr>
                                        <tr>
                                            <th>Total Harga</th>
                                            <td>: <strong>Rp <?= number_format($data_penjualan['total_harga'], 0, ',', '.'); ?></strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Tabel Daftar Menu yang Dipesan -->
                            <h4 style="margin-bottom: 15px;">Daftar Menu yang Dipesan</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Menu</th>
                                            <th>Harga Satuan</th>
                                            <th>Jumlah</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($query_detail && mysqli_num_rows($query_detail) > 0) {
                                            $no = 1;
                                            while ($row = mysqli_fetch_assoc($query_detail)) {
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($row['nama_menu']); ?></td>
                                            <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                                            <td><?= htmlspecialchars($row['jumlah']); ?></td>
                                            <td>Rp <?= number_format($row['subtotal'], 0, ',', '.'); ?></td>
                                        </tr>
                                        <?php
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>Tidak ada detail item untuk transaksi ini.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Tombol Aksi -->
                            <div class="form-group" style="margin-top: 25px;">
                                <a href="index.php" class="btn btn-default waves-effect">
                                    <i class="material-icons">arrow_back</i> Kembali
                                </a>
                                <a href="cetak_nota.php?id=<?= $data_penjualan['id_penjualan']; ?>" target="_blank" class="btn btn-success waves-effect">
                                    <i class="material-icons">print</i> Cetak Nota
                                </a>
                            </div>

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