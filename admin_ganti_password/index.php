<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['username'])) {
    echo '<script>alert("Silakan login terlebih dahulu."); window.location.href="../index.php";</script>';
    exit();
}

$username_session = $_SESSION['username'];
$pesan = '';
$tipe_pesan = '';

if (isset($_POST['btn_simpan'])) {
    $sandi_lama = $_POST['sandi_lama'];
    $sandi_baru = $_POST['sandi_baru'];
    $konfirmasi_sandi = $_POST['konfirmasi_sandi'];

    // Ambil sandi pengguna saat ini dari database
    $query = mysqli_query($con, "SELECT sandi FROM tbl_user WHERE username = '" . mysqli_real_escape_string($con, $username_session) . "'");
    $data = mysqli_fetch_assoc($query);

    if ($data) {
        $db_sandi = $data['sandi'];
        $sandi_lama_valid = false;

        // Verifikasi dengan SHA1
        if (sha1($sandi_lama) === $db_sandi) {
            $sandi_lama_valid = true;
        }

        if (!$sandi_lama_valid) {
            $pesan = 'Password lama yang Anda masukkan salah!';
            $tipe_pesan = 'danger';
        } elseif ($sandi_baru !== $konfirmasi_sandi) {
            $pesan = 'Konfirmasi password baru tidak cocok!';
            $tipe_pesan = 'warning';
        } elseif (strlen($sandi_baru) < 4) {
            $pesan = 'Password baru minimal 4 karakter!';
            $tipe_pesan = 'warning';
        } else {
            // Encrypt password baru menggunakan SHA1
            $sandi_baru_hashed = sha1($sandi_baru);
            $update = mysqli_query($con, "UPDATE tbl_user SET sandi = '$sandi_baru_hashed' WHERE username = '" . mysqli_real_escape_string($con, $username_session) . "'");

            if ($update) {
                $pesan = 'Password berhasil diperbarui!';
                $tipe_pesan = 'success';
            } else {
                $pesan = 'Gagal memperbarui password: ' . mysqli_error($con);
                $tipe_pesan = 'danger';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Ganti Password - BAKUL SEGA BU LASTRI</title>

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
                <h2>PENGATURAN AKUN</h2>
            </div>

            <div class="row clearfix">
                <!-- Ubah class di baris bawah ini menjadi col-xs-12 col-sm-12 col-md-12 col-lg-12 -->
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                GANTI PASSWORD
                                <small>Perbarui kata sandi akun Anda untuk menjaga keamanan sistem</small>
                            </h2>
                        </div>
                        <div class="body">
                            <?php if (!empty($pesan)): ?>
                                <div class="alert alert-<?= $tipe_pesan; ?> alert-dismissible" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <?= $pesan; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <input type="password" name="sandi_lama" class="form-control" required>
                                        <label class="form-label">Password Saat Ini</label>
                                    </div>
                                </div>

                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <input type="password" name="sandi_baru" class="form-control" required>
                                        <label class="form-label">Password Baru</label>
                                    </div>
                                </div>

                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <input type="password" name="konfirmasi_sandi" class="form-control" required>
                                        <label class="form-label">Konfirmasi Password Baru</label>
                                    </div>
                                </div>

                                <button type="submit" name="btn_simpan" class="btn btn-primary m-t-15 waves-effect">
                                    <i class="material-icons">save</i>
                                    <span>SIMPAN</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../footer.php'; ?>

        </div>
    </section>

    <script src="../AdminBSB/plugins/jquery/jquery.min.js"></script>
    <script src="../AdminBSB/plugins/bootstrap/js/bootstrap.js"></script>
    <!-- Tambahkan plugin SlimScroll -->
    <script src="../AdminBSB/plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <script src="../AdminBSB/plugins/node-waves/waves.js"></script>
    <script src="../AdminBSB/js/admin.js"></script>

    <!-- Inisialisasi SlimScroll untuk sidebar -->
    <script>
        $(function () {
            $('.sidebar .list').slimScroll({
                height: 'calc(100vh - 100px)',
                color: 'rgba(0,0,0,0.5)',
                size: '4px',
                alwaysVisible: false
            });
        });
    </script>
</body>
</html>