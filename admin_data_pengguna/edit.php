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

// Ambil parameter username dari URL
$username_param = isset($_GET['username']) ? $_GET['username'] : '';

if (empty($username_param)) {
    echo '<script>alert("Username tidak valid."); window.location.href="index.php";</script>';
    exit();
}

$query = mysqli_prepare($con, "SELECT * FROM tbl_user WHERE username = ?");
mysqli_stmt_bind_param($query, "s", $username_param);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo '<script>alert("Data pengguna tidak ditemukan."); window.location.href="index.php";</script>';
    exit();
}

$error = '';
$success = '';

// Proses Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username']);
    $peran = trim($_POST['peran']);
    $password = trim($_POST['password']);
    $pin = trim($_POST['pin']);

    if (empty($new_username) || empty($peran)) {
        $error = 'Username dan Peran wajib diisi!';
    } else {
        
        if ($new_username !== $username_param) {
            $check_stmt = mysqli_prepare($con, "SELECT username FROM tbl_user WHERE username = ?");
            mysqli_stmt_bind_param($check_stmt, "s", $new_username);
            mysqli_stmt_execute($check_stmt);
            mysqli_stmt_store_result($check_stmt);
            if (mysqli_stmt_num_rows($check_stmt) > 0) {
                $error = 'Username sudah digunakan oleh akun lain!';
            }
            mysqli_stmt_close($check_stmt);
        }

        if (empty($error)) {
            
            if (!empty($password) && !empty($pin)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = mysqli_prepare($con, "UPDATE tbl_user SET username = ?, peran = ?, password = ?, pin = ? WHERE username = ?");
                mysqli_stmt_bind_param($update_stmt, "sssss", $new_username, $peran, $hashed_password, $pin, $username_param);
            } elseif (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = mysqli_prepare($con, "UPDATE tbl_user SET username = ?, peran = ?, password = ? WHERE username = ?");
                mysqli_stmt_bind_param($update_stmt, "ssss", $new_username, $peran, $hashed_password, $username_param);
            } elseif (!empty($pin)) {
                $update_stmt = mysqli_prepare($con, "UPDATE tbl_user SET username = ?, peran = ?, pin = ? WHERE username = ?");
                mysqli_stmt_bind_param($update_stmt, "ssss", $new_username, $peran, $pin, $username_param);
            } else {
                $update_stmt = mysqli_prepare($con, "UPDATE tbl_user SET username = ?, peran = ? WHERE username = ?");
                mysqli_stmt_bind_param($update_stmt, "sss", $new_username, $peran, $username_param);
            }

            if (mysqli_stmt_execute($update_stmt)) {
                echo '<script>alert("Data pengguna berhasil diperbarui!"); window.location.href="index.php";</script>';
                exit();
            } else {
                $error = 'Gagal memperbarui data: ' . mysqli_error($con);
            }
            mysqli_stmt_close($update_stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Edit Pengguna - BAKUL SEGA BU LASTRI</title>

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
                <h2>MANAJEMEN DATA PENGGUNA</h2>
            </div>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                EDIT AKUN PENGGUNA
                                <small>Ubah informasi akun dan hak akses pengguna</small>
                            </h2>
                        </div>
                        <div class="body">
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?= $error; ?></div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" required>
                                        <label class="form-label">Username / No. HP</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Peran (Role)</label>
                                    <select name="peran" class="form-control show-tick" required>
                                        <option value="">-- Pilih Peran --</option>
                                        <option value="A" <?= ($user['peran'] == 'A') ? 'selected' : ''; ?>>Administrator (A)</option>
                                        <option value="Kasir" <?= ($user['peran'] == 'Kasir' || $user['peran'] != 'A') ? 'selected' : ''; ?>>Kasir</option>
                                    </select>
                                </div>

                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <input type="password" id="password" name="password" class="form-control">
                                        <label class="form-label">Password Baru (Kosongkan jika tidak ingin diubah)</label>
                                    </div>
                                </div>

                                <div class="form-group form-float">
                                    <div class="form-line">
                                        <input type="text" id="pin" name="pin" class="form-control" maxlength="6" value="<?= htmlspecialchars($user['pin']); ?>">
                                        <label class="form-label">PIN Security (6 Digit)</label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary m-t-15 waves-effect">SIMPAN PERUBAHAN</button>
                                <a href="index.php" class="btn btn-default m-t-15 waves-effect">KEMBALI</a>
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