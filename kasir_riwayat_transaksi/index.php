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

// Ambil parameter filter dari form (GET)
$filter_tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';

// Buat query dasar
$query_str = "SELECT * FROM penjualan WHERE 1=1";

// Tambahkan kondisi filter tanggal (hari) jika diisi
if (!empty($filter_tanggal)) {
    $tgl_esc = mysqli_real_escape_string($con, $filter_tanggal);
    $query_str .= " AND DATE(tanggal) = '$tgl_esc'";
}

// Tambahkan kondisi filter bulan jika diisi
if (!empty($filter_bulan)) {
    $bln_esc = mysqli_real_escape_string($con, $filter_bulan);
    $query_str .= " AND MONTH(tanggal) = '$bln_esc'";
}

$query_str .= " ORDER BY tanggal DESC";

// Ambil data penjualan khusus untuk laporan/riwayat
$data_penjualan = [];
$query_penjualan = mysqli_query($con, $query_str);

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
    <title>Riwayat Transaksi - Bakul Sega Bu Lastri</title>
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
                            <h2>RIWAYAT TRANSAKSI PENJUALAN</h2>
                        </div>
                        <div class="body">
                            <!-- Form Filter Hari dan Bulan -->
                            <form method="GET" action="" style="margin-bottom: 25px;">
                                <div class="row clearfix">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Filter Berdasarkan Tanggal (Hari):</strong></label>
                                            <div class="form-line">
                                                <input type="date" name="tanggal" class="form-control" value="<?= htmlspecialchars($filter_tanggal); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><strong>Filter Berdasarkan Bulan:</strong></label>
                                            <div class="form-line">
                                                <select name="bulan" class="form-control">
                                                    <option value="">-- Pilih Bulan --</option>
                                                    <option value="1" <?= $filter_bulan == '1' ? 'selected' : ''; ?>>Januari</option>
                                                    <option value="2" <?= $filter_bulan == '2' ? 'selected' : ''; ?>>Februari</option>
                                                    <option value="3" <?= $filter_bulan == '3' ? 'selected' : ''; ?>>Maret</option>
                                                    <option value="4" <?= $filter_bulan == '4' ? 'selected' : ''; ?>>April</option>
                                                    <option value="5" <?= $filter_bulan == '5' ? 'selected' : ''; ?>>Mei</option>
                                                    <option value="6" <?= $filter_bulan == '6' ? 'selected' : ''; ?>>Juni</option>
                                                    <option value="7" <?= $filter_bulan == '7' ? 'selected' : ''; ?>>Juli</option>
                                                    <option value="8" <?= $filter_bulan == '8' ? 'selected' : ''; ?>>Agustus</option>
                                                    <option value="9" <?= $filter_bulan == '9' ? 'selected' : ''; ?>>September</option>
                                                    <option value="10" <?= $filter_bulan == '10' ? 'selected' : ''; ?>>Oktober</option>
                                                    <option value="11" <?= $filter_bulan == '11' ? 'selected' : ''; ?>>November</option>
                                                    <option value="12" <?= $filter_bulan == '12' ? 'selected' : ''; ?>>Desember</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="margin-top: 23px;">
                                        <button type="submit" class="btn btn-primary waves-effect">
                                            <i class="material-icons">filter_list</i> Filter
                                        </button>
                                        <a href="<?= basename($_SERVER['PHP_SELF']); ?>" class="btn btn-default waves-effect">
                                            <i class="material-icons">refresh</i> Reset
                                        </a>
                                    </div>
                                </div>
                            </form>
                            <!-- End Form Filter -->

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>No. Nota</th>
                                            <th>Tanggal & Waktu</th>
                                            <th>Tipe Pesanan</th>
                                            <th>Total Harga</th>
                                            <th>Metode Bayar</th>
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
                                            <td><strong><?= htmlspecialchars($penjualan['no_nota']); ?></strong></td>
                                            <td><?= date('d-m-Y H:i:s', strtotime($penjualan['tanggal'])); ?></td>
                                            <td><span class="label bg-cyan"><?= htmlspecialchars($tipe); ?></span></td>
                                            <td>Rp <?= number_format($total_harga, 0, ',', '.'); ?></td>
                                            <td><span class="label bg-teal"><?= htmlspecialchars($penjualan['metode_bayar']); ?></span></td>
                                            <td>
                                                <a href="detail_transaksi.php?id=<?= $penjualan['id_penjualan']; ?>" class="btn btn-xs btn-info waves-effect" title="Lihat Detail">
                                                    <i class="material-icons">visibility</i>
                                                </a>
                                                <a href="cetak_nota.php?id=<?= $penjualan['id_penjualan']; ?>" target="_blank" class="btn btn-xs btn-success waves-effect" title="Cetak Ulang Nota">
                                                    <i class="material-icons">print</i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php 
                                            }
                                        } else {
                                            echo "<tr><td colspan='7' class='text-center'>Belum ada riwayat transaksi.</td></tr>";
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