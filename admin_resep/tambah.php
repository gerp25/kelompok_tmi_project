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

// Proses Simpan Data Baru
if (isset($_POST['simpan'])) {
    $nama_menu   = mysqli_real_escape_string($con, $_POST['nama_menu']);
    $harga       = mysqli_real_escape_string($con, $_POST['harga']);
    $deskripsi   = mysqli_real_escape_string($con, $_POST['deskripsi']);
    $status      = mysqli_real_escape_string($con, $_POST['status']);
    $kategori_id = mysqli_real_escape_string($con, $_POST['kategori_id']); 
    $created_at  = date('Y-m-d H:i:s');
    $updated_at  = date('Y-m-d H:i:s');

    // Proses Upload Gambar
    $nama_gambar = '';
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($file_ext, $allowed_ext)) {
            $nama_gambar = time() . '_' . uniqid() . '.' . $file_ext;
            $target_dir  = '../image/' . $nama_gambar;

            if (!move_uploaded_file($file_tmp, $target_dir)) {
                $error = "Gagal mengunggah file gambar ke server.";
            }
        } else {
            $error = "Format gambar tidak didukung! Format yang diperbolehkan: JPG, JPEG, PNG, WEBP.";
        }
    }

    if (!isset($error)) {
        // 1. Masukkan data ke tabel menu
        $query_insert_menu = mysqli_query($con, "INSERT INTO menu (kategori_id, nama_menu, harga, deskripsi, status, gambar, created_at, updated_at) 
                                                 VALUES ('$kategori_id', '$nama_menu', '$harga', '$deskripsi', '$status', '$nama_gambar', '$created_at', '$updated_at')");

        if ($query_insert_menu) {
            $id_menu_baru = mysqli_insert_id($con);

            // 2. Proses Simpan Detail Resep/Bahan Baku (jika ada input bahan baku)
            if (isset($_POST['id_bahan']) && is_array($_POST['id_bahan'])) {
                foreach ($_POST['id_bahan'] as $index => $id_bahan) {
                    $id_bahan_clean = mysqli_real_escape_string($con, $id_bahan);
                    $jumlah_input   = floatval($_POST['jumlah_kebutuhan'][$index]);

                    // Cek satuan dasar dari tabel bahan
                    $q_satuan = mysqli_query($con, "SELECT satuan FROM bahan WHERE id_bahan = '$id_bahan_clean'");
                    if ($q_satuan && mysqli_num_rows($q_satuan) > 0) {
                        $dt_satuan = mysqli_fetch_assoc($q_satuan);
                        $satuan_db = strtolower(trim($dt_satuan['satuan']));

                        // Jika satuan dasar adalah kg atau liter, bagi 1000 agar nilai tersimpan sesuai standar stok DB
                        if ($satuan_db === 'kg' || $satuan_db === 'liter' || $satuan_db === 'l') {
                            $jumlah_simpan = $jumlah_input / 1000;
                        } else {
                            $jumlah_simpan = $jumlah_input;
                        }

                        // Simpan ke detail resep
                        mysqli_query($con, "INSERT INTO detail_resep (menu_id, bahan_id, jumlah_kebutuhan) 
                                           VALUES ('$id_menu_baru', '$id_bahan_clean', '$jumlah_simpan')");
                    }
                }
            }

            // Arahkan kembali ke halaman Daftar Menu
            echo '<script>alert("Menu baru berhasil ditambahkan!"); window.location.href="../admin_kategori_menu/index.php";</script>';
            exit();
        } else {
            $error = "Gagal menambahkan menu: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Tambah Menu - Resto Project</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <?php include '../css.php'; ?>
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
                <h2>MANAJEMEN DATA MENU</h2>
            </div>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                TAMBAH MENU MASAKAN BARU
                                <small>Masukkan informasi menu masakan</small>
                            </h2>
                        </div>
                        <div class="body">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= $error; ?></div>
                            <?php endif; ?>

                            <form action="" method="POST" enctype="multipart/form-data" autocomplete="off">
                                <h4 class="card-inside-title">1. Informasi Utama Menu</h4>
                                
                                <input type="hidden" name="kategori_id" value="2">

                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <input type="text" name="nama_menu" class="form-control" required>
                                        <label class="form-label">Nama Menu Masakan</label>
                                    </div>
                                </div>

                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <input type="number" step="0.01" name="harga" class="form-control" required>
                                        <label class="form-label">Harga Jual (Rp)</label>
                                    </div>
                                </div>

                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <textarea name="deskripsi" rows="4" class="form-control no-resize" placeholder="Deskripsi Menu..."></textarea>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Status Menu</label>
                                    <select name="status" class="form-control show-tick">
                                        <option value="tersedia">Tersedia</option>
                                        <option value="Tidak Tersedia">Tidak Tersedia</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Gambar Menu</label>
                                    <div class="form-line">
                                        <input type="file" name="gambar" class="form-control" accept="image/png, image/jpeg, image/jpg, image/webp">
                                    </div>
                                    <small class="help-block">Format yang didukung: JPG, JPEG, PNG, WEBP</small>
                                </div>

                                <button type="submit" name="simpan" class="btn btn-primary m-t-15 waves-effect">
                                    <i class="material-icons">save</i>
                                    <span>SIMPAN MENU</span>
                                </button>
                                <a href="../admin_kategori_menu/index.php" class="btn btn-default m-t-15 waves-effect">
                                    <i class="material-icons">arrow_back</i>
                                    <span>KEMBALI</span>
                                </a>
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