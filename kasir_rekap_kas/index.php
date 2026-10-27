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

// Ambil tanggal filter (default hari ini)
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Query total transaksi harian
$query_rekap = "SELECT 
                    COUNT(id_penjualan) AS total_transaksi,
                    SUM(CASE WHEN metode_bayar = 'Tunai' THEN total_harga ELSE 0 END) AS total_tunai,
                    SUM(CASE WHEN metode_bayar != 'Tunai' THEN total_harga ELSE 0 END) AS total_nontunai,
                    SUM(total_harga) AS grand_total
                FROM penjualan 
                WHERE DATE(tanggal) = '$tanggal'";

$result_rekap = mysqli_query($con, $query_rekap);
$data_rekap = mysqli_fetch_assoc($result_rekap);

// Query detail transaksi harian
$query_detail = "SELECT * FROM penjualan WHERE DATE(tanggal) = '$tanggal' ORDER BY tanggal DESC";
$result_detail = mysqli_query($con, $query_detail);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Rekap Kas Harian - Bakul Sega Bu Lastri</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <?php include '../css.php'; ?>

    <!-- CSS khusus untuk menyembunyikan elemen saat dicetak -->
    <style>
        @media print {
            /* Sembunyikan Navbar, Sidebar, Form Filter, dan Footer bawaan web */
            .navbar, section:not(.content), .form-inline, hr, .footer, footer, .sidebar {
                display: none !important;
            }
            /* Atur ulang posisi konten utama agar memenuhi halaman cetak */
            section.content {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
            body {
                background-color: white !important;
                font-size: 12px;
            }
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

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>REKAP KAS HARIAN</h2>
                        </div>
                        <div class="body">
                            <!-- Filter Tanggal -->
                            <form method="GET" class="form-inline m-b-20">
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="m-r-10"><strong>Pilih Tanggal Rekap:</strong></label>
                                    <div class="form-line" style="display: inline-block; width: auto;">
                                        <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($tanggal); ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary waves-effect m-l-10">
                                    <i class="material-icons">filter_list</i> <span>Tampilkan</span>
                                </button>
                                <button type="button" onclick="window.print()" class="btn btn-success waves-effect m-l-5">
                                    <i class="material-icons">print</i> <span>Cetak Rekap</span>
                                </button>
                            </form>

                            <hr>

                            <!-- Ringkasan Kas Berupa Daftar/Teks -->
                            <h4 class="card-inside-title">Ringkasan Rekap Kas Tanggal <?= date('d-m-Y', strtotime($tanggal)); ?></h4>
                            <ul class="list-group m-b-30" style="max-width: 500px;">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Total Transaksi</span>
                                    <strong><?= $data_rekap['total_transaksi'] ?? 0; ?> Transaksi</strong>
                                </li>
                                <li class="list-group-item">
                                    <span>Pembayaran Tunai</span>
                                    <strong class="pull-right">Rp <?= number_format($data_rekap['total_tunai'] ?? 0, 0, ',', '.'); ?></strong>
                                </li>
                                <li class="list-group-item">
                                    <span>Non-Tunai (Transfer)</span>
                                    <strong class="pull-right">Rp <?= number_format($data_rekap['total_nontunai'] ?? 0, 0, ',', '.'); ?></strong>
                                </li>
                                <li class="list-group-item list-group-item-info">
                                    <span><strong>Total Pendapatan</strong></span>
                                    <strong class="pull-right">Rp <?= number_format($data_rekap['grand_total'] ?? 0, 0, ',', '.'); ?></strong>
                                </li>
                            </ul>

                            <!-- Tabel Rincian Transaksi -->
                            <h4 class="card-inside-title">Rincian Transaksi</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="50">No</th>
                                            <th>No. Nota</th>
                                            <th>Jam</th>
                                            <th>Tipe Pesanan</th>
                                            <th>Metode Bayar</th>
                                            <th>Total Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?>
                                        <?php 
                                        $no = 1;
                                        if ($result_detail && mysqli_num_rows($result_detail) > 0):
                                            while ($row = mysqli_fetch_assoc($result_detail)): 
                                        ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><strong><?= htmlspecialchars($row['no_nota']); ?></strong></td>
                                                <td><?= date('H:i:s', strtotime($row['tanggal'])); ?></td>
                                                <td><span class="label bg-cyan"><?= htmlspecialchars($row['tipe_pesanan']); ?></span></td>
                                                <td><span class="label bg-green"><?= htmlspecialchars($row['metode_bayar']); ?></span></td>
                                                <td>Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="6" class="text-center">Belum ada transaksi pada tanggal ini.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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