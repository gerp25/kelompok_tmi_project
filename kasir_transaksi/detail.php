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

// Ambil nomor nota dari parameter GET
$no_nota = isset($_GET['nota']) ? $_GET['nota'] : '';

if (empty($no_nota)) {
    echo '<script>alert("Nomor nota tidak valid.");</script>';
    echo '<script>window.location.href ="index.php";</script>';
    exit();
}

// Ambil data utama penjualan berdasarkan no_nota
$query_penjualan = mysqli_query($con, "SELECT * FROM penjualan WHERE no_nota = '" . mysqli_real_escape_string($con, $no_nota) . "'");
$penjualan = mysqli_fetch_assoc($query_penjualan);

if (!$penjualan) {
    echo '<script>alert("Data transaksi tidak ditemukan.");</script>';
    echo '<script>window.location.href ="index.php";</script>';
    exit();
}

// Ambil data detail barang/menu yang dibeli pada transaksi ini
$query_detail = mysqli_query($con, "SELECT pd.*, m.nama_menu FROM penjualan_detail pd JOIN menu m ON pd.id_menu = m.id WHERE pd.no_nota = '" . mysqli_real_escape_string($con, $no_nota) . "'");
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
    <style>
        .table-info th {
            width: 250px;
            font-weight: bold;
            color: #555;
            border-top: none !important;
        }
        .table-info td {
            border-top: none !important;
        }
        .table-info tr {
            border-bottom: 1px solid #eee;
        }
    </style>
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

    <!-- Content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>DETAIL TRANSAKSI PENJUALAN</h2>
                        </div>
                        <div class="body">

                            <!-- Informasi Utama Transaksi -->
                            <div class="row clearfix">
                                <div class="col-md-12">
                                    <table class="table table-info">
                                        <tr>
                                            <th>No. Nota</th>
                                            <td>: <?= htmlspecialchars($penjualan['no_nota']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Penjualan</th>
                                            <td>: <?= date('Y-m-d H:i', strtotime($penjualan['tanggal'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Nama Pemesan</th>
                                            <td>: <i><?= htmlspecialchars($penjualan['nama_pemesan'] ?? '(Walk-in)'); ?></i></td>
                                        </tr>
                                        <tr>
                                            <th>Alamat Pengiriman</th>
                                            <td>: <i><?= htmlspecialchars($penjualan['alamat_pengiriman'] ?? 'Beli di Tempat'); ?></i></td>
                                        </tr>
                                        <tr>
                                            <th>Tipe Pesanan</th>
                                            <td>: <span class="label bg-cyan"><?= htmlspecialchars($penjualan['tipe_pesanan'] ?? 'Ditempat'); ?></span></td>
                                        </tr>
                                        <tr>
                                            <th>Metode Pembayaran</th>
                                            <td>: <?= htmlspecialchars($penjualan['metode_bayar']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Status Pesanan</th>
                                            <td>: <span class="label bg-green"><?= htmlspecialchars($penjualan['status_pesanan'] ?? 'Selesai'); ?></span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <br>
                            <h4>Rincian Menu Penjualan</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">No</th>
                                            <th>Nama Menu</th>
                                            <th>Jumlah</th>
                                            <th>Harga Satuan</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($query_detail && mysqli_num_rows($query_detail) > 0) {
                                            $no = 1;
                                            while ($detail = mysqli_fetch_assoc($query_detail)) {
                                                $harga = isset($detail['harga']) ? $detail['harga'] : 0;
                                                $jumlah = isset($detail['jumlah']) ? $detail['jumlah'] : (isset($detail['qty']) ? $detail['qty'] : 0);
                                                $subtotal = isset($detail['subtotal']) ? $detail['subtotal'] : ($harga * $jumlah);
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($detail['nama_menu']); ?></td>
                                            <td><?= $jumlah; ?></td>
                                            <td>Rp <?= number_format($harga, 0, ',', '.'); ?></td>
                                            <td>Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                        </tr>
                                        <?php 
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>Tidak ada item detail untuk transaksi ini.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="4" class="text-right" style="vertical-align: middle;"><strong>Total Transaksi:</strong></td>
                                            <td><strong>Rp <?= number_format($penjualan['total_harga'], 0, ',', '.'); ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                            <!-- Tombol Kembali diletakkan di bawah seperti Admin -->
                            <a href="index.php" class="btn btn-default waves-effect m-t-15">
                                <i class="material-icons" style="font-size: 16px; vertical-align: middle;">arrow_back</i> Kembali
                            </a>

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