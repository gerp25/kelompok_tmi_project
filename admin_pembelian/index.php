<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

$authority = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];

if (($authority != 'A' && $authority != 'Admin') || $is_2fa_passed !== true) {
    echo '<script>alert ("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

// SIMPAN SEMUA DATA PEMBELIAN KE ARRAY SUPAYA MODAL BISA DIRENDER DI LUAR TABEL
$data_pembelian = [];
$query_pembelian = mysqli_query($con, "SELECT pembelian.*, supplier.nama_supplier 
        FROM pembelian 
        LEFT JOIN supplier ON pembelian.id_supplier = supplier.id_supplier 
        ORDER BY pembelian.tgl_pembelian DESC");

if ($query_pembelian && mysqli_num_rows($query_pembelian) > 0) {
    while ($row = mysqli_fetch_assoc($query_pembelian)) {
        $data_pembelian[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Data Pembelian / Pengeluaran Modal - Bakul Sega Bu Lastri</title>
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
                <a class="navbar-brand" href="../home_admin/index.php">BAKUL SEGA BU LASTRI - ADMIN</a>
            </div>
        </div>
    </nav>

    <!-- Left Sidebar -->
    <section>
        <?php include '../sidebaradmin.php'; ?>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>RIWAYAT PENGELUARAN MODAL / PEMBELIAN</h2>
                        </div>
                        <div class="body">
                            <!-- Tombol Transaksi Baru -->
                            <a href="tambah.php" class="btn btn-primary waves-effect m-b-15">
                                + Transaksi Pengeluaran Baru
                            </a>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>No. Nota</th>
                                            <th>Tanggal</th>
                                            <th>Supplier / Sumber</th>
                                            <th>Total Pengeluaran Modal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (!empty($data_pembelian)) {
                                            $no = 1;
                                            foreach ($data_pembelian as $pembelian) {
                                                $id_pembelian = isset($pembelian['id_pembelian']) ? $pembelian['id_pembelian'] : (isset($pembelian['id']) ? $pembelian['id'] : '');
                                                $total_harga = isset($pembelian['total_harga']) ? $pembelian['total_harga'] : (isset($pembelian['total_transaksi']) ? $pembelian['total_transaksi'] : 0);
                                                $nama_supplier = !empty($pembelian['nama_supplier']) ? $pembelian['nama_supplier'] : '-';
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($pembelian['no_faktur']); ?></td>
                                            <td><?= htmlspecialchars($pembelian['tgl_pembelian']); ?></td>
                                            <td><?= htmlspecialchars($nama_supplier); ?></td>
                                            <td>Rp <?= number_format($total_harga, 0, ',', '.'); ?></td>
                                            <td>
                                                <!-- Tombol Detail (Memicu Modal) -->
                                                <button type="button" class="btn btn-xs btn-info waves-effect" data-toggle="modal" data-target="#modalDetail<?= $id_pembelian; ?>" title="Detail">
                                                    <i class="material-icons">visibility</i>
                                                </button>
                                                
                                                <!-- Tombol Hapus -->
                                                <a href="hapus.php?id=<?= $id_pembelian; ?>" class="btn btn-xs btn-danger waves-effect" onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?');" title="Hapus">
                                                    <i class="material-icons">delete</i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php 
                                            }
                                        } else {
                                            echo "<tr><td colspan='6' class='text-center'>Belum ada transaksi pengeluaran.</td></tr>";
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

    <!-- Modal Detail Pengeluaran Modal -->
    <?php
    if (!empty($data_pembelian)) {
        foreach ($data_pembelian as $pembelian) {
            $id_pembelian = isset($pembelian['id_pembelian']) ? $pembelian['id_pembelian'] : (isset($pembelian['id']) ? $pembelian['id'] : '');
            $total_harga = isset($pembelian['total_harga']) ? $pembelian['total_harga'] : (isset($pembelian['total_transaksi']) ? $pembelian['total_transaksi'] : 0);
            $nama_supplier = !empty($pembelian['nama_supplier']) ? $pembelian['nama_supplier'] : '-';
            $keterangan = !empty($pembelian['keterangan']) ? $pembelian['keterangan'] : '-';
    ?>
    <div class="modal fade" id="modalDetail<?= $id_pembelian; ?>" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">DETAIL TRANSAKSI PENGELUARAN MODAL</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="200"><strong>No. Nota</strong></td>
                            <td width="20">:</td>
                            <td><?= htmlspecialchars($pembelian['no_faktur']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Tanggal Transaksi</strong></td>
                            <td>:</td>
                            <td><?= htmlspecialchars($pembelian['tgl_pembelian']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Supplier / Sumber</strong></td>
                            <td>:</td>
                            <td><?= htmlspecialchars($nama_supplier); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Total Pengeluaran Modal</strong></td>
                            <td>:</td>
                            <td><b class="col-pink">Rp <?= number_format($total_harga, 0, ',', '.'); ?></b></td>
                        </tr>
                        <tr>
                            <td><strong>Keterangan / Catatan</strong></td>
                            <td>:</td>
                            <td><?= nl2br(htmlspecialchars($keterangan)); ?></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary waves-effect" data-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    <?php 
        }
    }
    ?>

    <?php include '../script.php'; ?>
    
</body>

</html>