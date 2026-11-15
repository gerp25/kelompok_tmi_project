<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

$authority = $_SESSION['peran'] ?? '';
$is_2fa_passed = $_SESSION['is_2fa_passed'] ?? false;

if (($authority != 'A' && $authority != 'Admin') || $is_2fa_passed !== true) {
    echo '<script>alert("Akses ditolak. Silakan login kembali."); window.location.href="../logout.php";</script>';
    exit();
}

if (isset($_POST['simpan'])) {
    $username = trim($_POST['username']);
    $sandi    = $_POST['password'];
    $peran    = $_POST['peran'];
    $pin      = '';

    try {
        $query_cek = mysqli_prepare($con, "SELECT username FROM tbl_user WHERE username = ?");
        mysqli_stmt_bind_param($query_cek, "s", $username);
        mysqli_stmt_execute($query_cek);
        mysqli_stmt_store_result($query_cek);

        if (mysqli_stmt_num_rows($query_cek) > 0) {
            echo '<script>alert("Gagal: Username sudah terdaftar! Gunakan username lain.");</script>';
        } else {
            $query_insert = mysqli_prepare($con, "INSERT INTO tbl_user (username, sandi, peran, pin) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($query_insert, "ssss", $username, $sandi, $peran, $pin);

            if (mysqli_stmt_execute($query_insert)) {
                echo '<script>alert("Pengguna baru berhasil ditambahkan!"); window.location.href="index.php";</script>';
                exit();
            }
            mysqli_stmt_close($query_insert);
        }
        mysqli_stmt_close($query_cek);
    } catch (mysqli_sql_exception $e) {
        $error_msg = addslashes($e->getMessage());
        echo '<script>alert("Gagal menambahkan pengguna: ' . $error_msg . '");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Tambah Pengguna - BAKUL SEGA BU LASTRI</title>

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
                <h2>MANAJEMEN DATA PENGGUNA</h2>
            </div>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                TAMBAH AKUN PENGGUNA
                                <small>Masukkan data kredensial pengguna baru di bawah ini</small>
                            </h2>
                        </div>
                        <div class="body">
                            <form action="" method="POST">
                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="username" maxlength="13" required>
                                        <label class="form-label">Username / No. HP (Maks. 13 karakter)</label>
                                    </div>
                                </div>

                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <input type="password" class="form-control" name="password" required>
                                        <label class="form-label">Password / Sandi</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <p><b>Peran (Role)</b></p>
                                    <select name="peran" class="form-control show-tick" required>
                                        <option value="">-- Pilih Peran --</option>
                                        <option value="A">Administrator</option>
                                        <option value="K">Kasir</option>
                                    </select>
                                </div>

                                <button type="submit" name="simpan" class="btn btn-primary m-t-15 waves-effect">SIMPAN DATA</button>
                                <a href="index.php" class="btn btn-default m-t-15 waves-effect">BATAL</a>
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