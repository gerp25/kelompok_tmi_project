<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

$authority     = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];

if (($authority != 'A' && $authority != 'Admin') || $is_2fa_passed !== true) {
    echo '<script>alert("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

// Proses Tambah / Edit Data Supplier
if (isset($_POST['simpan'])) {
    $id_supplier   = $_POST['id_supplier'];
    $nama_supplier = $_POST['nama_supplier'];
    $alamat        = $_POST['alamat'];
    $no_telp       = $_POST['no_telp'];

    if (empty($id_supplier)) {
        $query = "INSERT INTO supplier (nama_supplier, alamat, no_telp) VALUES ('$nama_supplier', '$alamat', '$no_telp')";
    } else {
        $query = "UPDATE supplier SET nama_supplier='$nama_supplier', alamat='$alamat', no_telp='$no_telp' WHERE id_supplier='$id_supplier'";
    }
    
    mysqli_query($con, $query);
    header("Location: index.php");
    exit();
}

// Proses Hapus Data Supplier
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($con, "DELETE FROM supplier WHERE id_supplier='$id'");
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Data Supplier - Bakul Sega Bu Lastri</title>

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
            <div class="block-header">
                <h2>DATA SUPPLIER</h2>
            </div>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <button class="btn btn-primary waves-effect" data-toggle="modal" data-target="#modalSupplier" onclick="resetForm()">+ Tambah Supplier</button>
                        </div>
                        <div class="body table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Supplier</th>
                                        <th>Alamat</th>
                                        <th>No. Telp</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $data = mysqli_query($con, "SELECT * FROM supplier ORDER BY id_supplier DESC");
                                    if ($data && mysqli_num_rows($data) > 0) {
                                        while ($d = mysqli_fetch_array($data)) {
                                    ?>
                                            <tr>
                                                <td><?= $no++; ?></td>
                                                <td><?= htmlspecialchars($d['nama_supplier']); ?></td>
                                                <td><?= htmlspecialchars($d['alamat']); ?></td>
                                                <td><?= htmlspecialchars($d['no_telp']); ?></td>
                                                <td>
                                                    <button class="btn btn-xs btn-warning waves-effect" 
                                                            onclick="editData(<?= $d['id_supplier']; ?>, '<?= addslashes($d['nama_supplier']); ?>', '<?= addslashes($d['alamat']); ?>', '<?= $d['no_telp']; ?>')" 
                                                            title="Edit">
                                                        <i class="material-icons">edit</i>
                                                    </button>

                                                    <a href="index.php?hapus=<?= $d['id_supplier']; ?>" 
                                                       class="btn btn-xs btn-danger waves-effect" 
                                                       onclick="return confirm('Yakin ingin menghapus data?')" 
                                                       title="Hapus">
                                                        <i class="material-icons">delete</i>
                                                    </a>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>Data supplier belum ada.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MEMANGGIL FOOTER -->
            <?php include '../footer.php'; ?>
        </div>
    </section>

    <!-- Modal Form Supplier -->
    <div class="modal fade" id="modalSupplier" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="index.php" method="POST">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modalTitle">Tambah Supplier</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_supplier" id="id_supplier">
                        
                        <div class="form-group form-float">
                            <div class="form-line">
                                <input type="text" name="nama_supplier" id="nama_supplier" class="form-control" required>
                                <label class="form-label">Nama Supplier</label>
                            </div>
                        </div>
                        
                        <div class="form-group form-float">
                            <div class="form-line">
                                <textarea name="alamat" id="alamat" class="form-control" rows="3"></textarea>
                                <label class="form-label">Alamat</label>
                            </div>
                        </div>
                        
                        <div class="form-group form-float">
                            <div class="form-line">
                                <input type="text" name="no_telp" id="no_telp" class="form-control">
                                <label class="form-label">No. Telp</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="simpan" class="btn btn-link waves-effect">SIMPAN</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">BATAL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../script.php'; ?>
    
    <script>
        function resetForm() {
            $('#id_supplier').val('');
            $('#nama_supplier').val('');
            $('#alamat').val('');
            $('#no_telp').val('');
            $('#modalTitle').text('Tambah Supplier');
        }

        function editData(id, nama, alamat, telp) {
            $('#id_supplier').val(id);
            $('#nama_supplier').val(nama);
            $('#alamat').val(alamat);
            $('#no_telp').val(telp);
            $('#modalTitle').text('Edit Supplier');
            $('#modalSupplier').modal('show');
        }
    </script>
</body>

</html>