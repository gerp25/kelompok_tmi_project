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

// Ambil data penjualan
$data_penjualan = [];
$query_penjualan = mysqli_query($con, "SELECT * FROM penjualan ORDER BY tanggal DESC");

if ($query_penjualan && mysqli_num_rows($query_penjualan) > 0) {
    while ($row = mysqli_fetch_assoc($query_penjualan)) {
        $data_penjualan[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Data Penjualan - Bakul Sega Bu Lastri</title>
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
                            <h2>RIWAYAT PENJUALAN BARANG</h2>
                        </div>
                        <div class="body">
                            <!-- Tombol mengarah ke tambah.php -->
                            <a href="tambah.php" class="btn btn-primary waves-effect m-b-15">
                                + Transaksi Penjualan Baru
                            </a>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>No. Nota</th>
                                            <th>Tanggal</th>
                                            <th>Tipe</th>
                                            <th>Total Harga</th>
                                            <th>Metode</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (!empty($data_penjualan)) {
                                            $no = 1;
                                            foreach ($data_penjualan as $penjualan) {
                                                $total_harga = isset($penjualan['total_harga']) ? $penjualan['total_harga'] : 0;
                                                $tipe = !empty($penjualan['tipe_pesanan']) ? $penjualan['tipe_pesanan'] : '-';
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($penjualan['no_nota']); ?></td>
                                            <td><?= date('Y-m-d H:i', strtotime($penjualan['tanggal'])); ?></td>
                                            <td><span class="label bg-cyan"><?= htmlspecialchars($tipe); ?></span></td>
                                            <td>Rp <?= number_format($total_harga, 0, ',', '.'); ?></td>
                                            <td><?= htmlspecialchars($penjualan['metode_bayar']); ?></td>
                                            <td>
                                                <a href="detail.php?nota=<?= urlencode($penjualan['no_nota']); ?>" class="btn btn-xs btn-info waves-effect" title="Detail">
                                                    <i class="material-icons">visibility</i>
                                                </a>
                                                <a href="cetak.php?nota=<?= urlencode($penjualan['no_nota']); ?>" target="_blank" class="btn btn-xs btn-success waves-effect" title="Cetak Nota">
                                                    <i class="material-icons">print</i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php 
                                            }
                                        } else {
                                            echo "<tr><td colspan='7' class='text-center'>Belum ada transaksi penjualan.</td></tr>";
                                        }
                                        ?>
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