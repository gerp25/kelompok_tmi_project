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

$no_nota = mysqli_real_escape_string($con, $_GET['nota'] ?? '');

$query_penjualan = mysqli_query($con, "SELECT * FROM penjualan WHERE no_nota = '$no_nota'");
$penjualan = mysqli_fetch_assoc($query_penjualan);

if (!$penjualan) {
    echo '<script>alert("Data transaksi tidak ditemukan!"); window.location.href="index.php";</script>';
    exit();
}

if (isset($_POST['konfirmasi_pesanan'])) {
    // UPDATE KE 'Selesai'
    $update = mysqli_query($con, "UPDATE penjualan SET status_pesanan = 'Selesai' WHERE no_nota = '$no_nota'");
    if ($update) {
        echo '<script>alert("Pesanan berhasil dikonfirmasi! Status diubah menjadi Selesai."); window.location.href="index.php";</script>';
        exit();
    } else {
        echo '<script>alert("Gagal mengonfirmasi pesanan: ' . mysqli_error($con) . '");</script>';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Konfirmasi Pesanan - <?= htmlspecialchars($penjualan['no_nota']); ?></title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <?php include '../css.php'; ?>
</head>

<body class="theme-red">
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
                <a href="javascript:void(0);" class="bars"></a>
                <a class="navbar-brand" href="../home_admin/index.php">BAKUL SEGA BU LASTRI - ADMIN</a>
            </div>
        </div>
    </nav>

    <section>
        <?php include '../sidebaradmin.php'; ?>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>KONFIRMASI & VALIDASI PESANAN</h2>
                        </div>
                        <div class="body">
                            <div class="row">
                                <div class="col-md-7 col-sm-12">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="180"><strong>No. Nota</strong></td>
                                            <td width="20">:</td>
                                            <td><?= htmlspecialchars($penjualan['no_nota']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Transaksi</strong></td>
                                            <td>:</td>
                                            <td><?= date('Y-m-d H:i', strtotime($penjualan['tanggal'])); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nama Pemesan</strong></td>
                                            <td>:</td>
                                            <td><?= !empty($penjualan['nama_pelanggan']) ? htmlspecialchars($penjualan['nama_pelanggan']) : '<i>(Walk-in)</i>'; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Alamat Pengiriman</strong></td>
                                            <td>:</td>
                                            <td>
                                                <?php 
                                                $alamat = !empty($penjualan['keterangan_lokasi']) ? $penjualan['keterangan_lokasi'] : (!empty($penjualan['alamat']) ? $penjualan['alamat'] : '');
                                                echo !empty($alamat) ? htmlspecialchars($alamat) : '<i>- (Beli di Tempat / Dine-in)</i>';
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tipe Pesanan</strong></td>
                                            <td>:</td>
                                            <td><span class="label bg-cyan"><?= htmlspecialchars($penjualan['tipe_pesanan'] ? $penjualan['tipe_pesanan'] : '-'); ?></span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Metode Pembayaran</strong></td>
                                            <td>:</td>
                                            <td><b><?= htmlspecialchars($penjualan['metode_bayar']); ?></b></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total Pembayaran</strong></td>
                                            <td>:</td>
                                            <td><b class="col-red font-18">Rp <?= number_format($penjualan['total_harga'], 0, ',', '.'); ?></b></td>
                                        </tr>
                                    </table>
                                </div>

                                <div class="col-md-5 col-sm-12">
                                    <div class="card" style="box-shadow: none; border: 1px solid #ddd; padding: 15px;">
                                        <h4>Bukti Pembayaran / Transfer</h4>
                                        <hr style="margin: 10px 0;">
                                        <?php if ($penjualan['metode_bayar'] == 'Transfer' && !empty($penjualan['bukti_transfer'])): ?>
                                            <?php $file_bukti = '../image/bukti_transfer/' . $penjualan['bukti_transfer']; ?>
                                            <?php if (file_exists($file_bukti)): ?>
                                                <a href="<?= $file_bukti; ?>" target="_blank" title="Klik untuk memperbesar">
                                                    <img src="<?= $file_bukti; ?>" class="img-responsive img-thumbnail" style="max-height: 250px; margin: 0 auto;" alt="Bukti Transfer">
                                                </a>
                                                <p class="text-center font-12 col-grey m-t-5">*Klik gambar untuk melihat dalam ukuran penuh</p>
                                            <?php else: ?>
                                                <div class="alert alert-warning">File bukti transfer tidak ditemukan di server.</div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="text-muted">Metode pembayaran Tunai / Tidak memerlukan unggah bukti transfer.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <h4 class="m-t-20">Rincian Menu Pesanan</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Menu</th>
                                            <th>Jumlah</th>
                                            <th>Harga Satuan</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        $query_detail = mysqli_query($con, "
                                            SELECT penjualan_detail.*, menu.nama_menu, menu.harga AS harga_menu 
                                            FROM penjualan_detail 
                                            LEFT JOIN menu ON penjualan_detail.id_menu = menu.id 
                                            WHERE penjualan_detail.no_nota = '$no_nota'
                                        ");

                                        if ($query_detail && mysqli_num_rows($query_detail) > 0) {
                                            while ($detail = mysqli_fetch_assoc($query_detail)) {
                                                $jumlah = $detail['jumlah'];
                                                $harga = ($detail['harga'] > 0) ? $detail['harga'] : $detail['harga_menu'];
                                                $subtotal = ($detail['subtotal'] > 0) ? $detail['subtotal'] : ($jumlah * $harga);
                                                $nama_menu = $detail['nama_menu'] ? $detail['nama_menu'] : 'Menu Tidak Ditemukan';
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($nama_menu); ?></td>
                                            <td><?= $jumlah; ?></td>
                                            <td>Rp <?= number_format($harga, 0, ',', '.'); ?></td>
                                            <td>Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                        </tr>
                                        <?php 
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>Data rincian tidak ditemukan.</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-right">Total Transaksi:</th>
                                            <th>Rp <?= number_format($penjualan['total_harga'], 0, ',', '.'); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <form method="POST" class="m-t-20">
                                <button type="submit" name="konfirmasi_pesanan" class="btn btn-success btn-lg waves-effect" onclick="return confirm('Apakah Anda yakin pesanan ini sudah selesai?');">
                                    <i class="material-icons">check_circle</i> PESANAN SELESAI
                                </button>
                                <a href="index.php" class="btn btn-default btn-lg waves-effect m-l-10">
                                    <i class="material-icons">arrow_back</i> Kembali
                                </a>
                            </form>

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