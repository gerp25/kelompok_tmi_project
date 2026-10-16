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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query_get = mysqli_query($con, "SELECT * FROM bahan_baku WHERE id = '$id'");

if (mysqli_num_rows($query_get) == 0) {
    echo '<script>alert("Data bahan tidak ditemukan!"); window.location.href="index.php";</script>';
    exit();
}

$data = mysqli_fetch_assoc($query_get);

if (isset($_POST['update'])) {
    $nama_bahan   = mysqli_real_escape_string($con, $_POST['nama_bahan']);
    $stok         = floatval($_POST['stok']);
    $satuan       = mysqli_real_escape_string($con, $_POST['satuan']);
    $harga_satuan = floatval($_POST['harga_satuan']);

    $query_update = "UPDATE bahan_baku SET 
                        nama_bahan = '$nama_bahan', 
                        stok = '$stok', 
                        satuan = '$satuan', 
                        harga_satuan = '$harga_satuan' 
                     WHERE id = '$id'";

    if (mysqli_query($con, $query_update)) {
        echo '<script>alert("Data bahan baku berhasil diperbarui!"); window.location.href="index.php";</script>';
    } else {
        echo '<script>alert("Gagal memperbarui data: ' . mysqli_error($con) . '");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Edit Bahan Baku - Resto Project</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <link href="../AdminBSB/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="../AdminBSB/plugins/node-waves/waves.css" rel="stylesheet" />
    <link href="../AdminBSB/plugins/animate-css/animate.css" rel="stylesheet" />
    <link href="../AdminBSB/css/style.css" rel="stylesheet">
    <link href="../AdminBSB/css/themes/all-themes.css" rel="stylesheet" />
</head>

<body class="theme-red">
    <div class="overlay"></div>

    <?php include '../navbar.php'; ?>

    <section>
        <?php include '../sidebaradmin.php'; ?>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>MANAJEMEN DATA BAHAN BAKU</h2>
            </div>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>EDIT BAHAN BAKU</h2>
                        </div>
                        <div class="body">
                            <form method="POST" action="">
                                <div class="form-group form-float">
                                    <div class="form-line focused">
                                        <input type="text" name="nama_bahan" class="form-control" value="<?= htmlspecialchars($data['nama_bahan']); ?>" required>
                                        <label class="form-label">Nama Bahan Baku</label>
                                    </div>
                                </div>

                                <div class="form-group form-float">
                                    <div class="form-line focused">
                                        <input type="number" step="0.01" name="stok" class="form-control" value="<?= $data['stok']; ?>" required>
                                        <label class="form-label">Jumlah Stok</label>
                                    </div>
                                </div>

                                <div class="form-group form-float">
                                    <div class="form-line focused">
                                        <input type="text" name="satuan" class="form-control" value="<?= htmlspecialchars($data['satuan']); ?>" required>
                                        <label class="form-label">Satuan</label>
                                    </div>
                                </div>

                                <div class="form-group form-float">
                                    <div class="form-line focused">
                                        <input type="number" step="0.01" name="harga_satuan" class="form-control" value="<?= $data['harga_satuan']; ?>" required>
                                        <label class="form-label">Harga Satuan (Rp)</label>
                                    </div>
                                </div>

                                <button type="submit" name="update" class="btn btn-primary m-t-15 waves-effect">
                                    <i class="material-icons">save</i> <span>UPDATE</span>
                                </button>
                                <a href="index.php" class="btn btn-danger m-t-15 waves-effect">
                                    <i class="material-icons">cancel</i> <span>BATAL</span>
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