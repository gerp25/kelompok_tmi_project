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

if (isset($_POST['simpan_transaksi'])) {
    $no_faktur     = mysqli_real_escape_string($con, $_POST['no_faktur']);
    $tgl_pembelian = mysqli_real_escape_string($con, $_POST['tgl_pembelian']);
    $grand_total   = (float)$_POST['total_harga'];
    $keterangan    = mysqli_real_escape_string($con, $_POST['keterangan']);

    // Jika id_supplier diisi, masukkan nilainya. Jika kosong, gunakan NULL tanpa tanda kutip.
    $id_supplier_val = !empty($_POST['id_supplier']) ? "'" . mysqli_real_escape_string($con, $_POST['id_supplier']) . "'" : "NULL";

    $q_header = "INSERT INTO pembelian (no_faktur, tgl_pembelian, id_supplier, total_harga, keterangan) 
                 VALUES ('$no_faktur', '$tgl_pembelian', $id_supplier_val, '$grand_total', '$keterangan')";
    
    if (mysqli_query($con, $q_header)) {
        header("Location: index.php");
        exit();
    } else {
        echo "<script>alert('Gagal menyimpan transaksi: " . mysqli_error($con) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Input Pengeluaran Modal - Bakul Sega Bu Lastri</title>
    <!-- Google Fonts -->
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
            <div class="block-header">
                <h2>INPUT PENGELUARAN MODAL / PEMBELIAN</h2>
            </div>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>FORM PENGELUARAN MODAL HARIAN</h2>
                        </div>
                        <div class="body">
                            <form action="" method="POST">
                                <div class="row clearfix">
                                    <div class="col-md-4">
                                        <label for="no_faktur">No. Nota / TR</label>
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="text" id="no_faktur" name="no_faktur" class="form-control" value="TRSUP-<?= date('YmdHis'); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="tgl_pembelian">Tanggal Transaksi</label>
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="date" id="tgl_pembelian" name="tgl_pembelian" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="id_supplier">Supplier / Sumber (Opsional)</label>
                                        <div class="form-group">
                                            <select name="id_supplier" id="id_supplier" class="form-control show-tick">
                                                <option value="">-- Pilih Supplier --</option>
                                                <?php
                                                $supplier = mysqli_query($con, "SELECT * FROM supplier ORDER BY nama_supplier ASC");
                                                while ($s = mysqli_fetch_array($supplier)) {
                                                    echo "<option value='".$s['id_supplier']."'>".$s['nama_supplier']."</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row clearfix">
                                    <div class="col-md-12">
                                        <label for="total_harga">Total Pengeluaran / Biaya (Rp)</label>
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="number" id="total_harga" name="total_harga" class="form-control" placeholder="Contoh: 1500000" min="0" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row clearfix">
                                    <div class="col-md-12">
                                        <label for="keterangan">Keterangan / Catatan Belanja</label>
                                        <div class="form-group">
                                            <div class="form-line">
                                                <textarea id="keterangan" name="keterangan" rows="3" class="form-control no-resize" placeholder="Catatan tambahan (misal: Belanja bahan baku, operasional gas, listrik, dll)"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" name="simpan_transaksi" class="btn btn-primary m-t-15 waves-effect">Simpan Transaksi</button>
                                <a href="index.php" class="btn btn-default m-t-15 waves-effect">Batal</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../script.php'; ?>
</body>

</html>