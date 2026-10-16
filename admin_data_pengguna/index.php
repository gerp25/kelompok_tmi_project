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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Data Pengguna - BAKUL SEGA BU LASTRI</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <link href="../AdminBSB/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
    <!-- Waves Effect Css -->
    <link href="../AdminBSB/plugins/node-waves/waves.css" rel="stylesheet" />
    <!-- Animation Css -->
    <link href="../AdminBSB/plugins/animate-css/animate.css" rel="stylesheet" />
    <!-- Custom Css -->
    <link href="../AdminBSB/css/style.css" rel="stylesheet">
    <!-- AdminBSB Themes -->
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
                                DAFTAR AKUN PENGGUNA
                                <small>Kelola data akun pengguna dan hak akses sistem di sini</small>
                            </h2>
                            <ul class="header-dropdown m-r--5">
                                <li>
                                    <a href="tambah.php" class="btn bg-blue waves-effect">
                                        <i class="material-icons">person_add</i>
                                        <span>TAMBAH PENGGUNA</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="body table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th width="30%">Username / No. HP</th>
                                        <th width="20%" class="text-center">Peran (Role)</th>
                                        <th width="20%" class="text-center">Status PIN Security</th>
                                        <th width="25%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Mengambil data pengguna berdasarkan username
                                    $query = mysqli_query($con, "SELECT * FROM tbl_user ORDER BY username ASC");
                                    $no = 1;

                                    if ($query && mysqli_num_rows($query) > 0) {
                                        while ($data = mysqli_fetch_array($query)) {
                                            $username = $data['username'];
                                            $peran = $data['peran'];
                                            $pin = $data['pin'];
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no; ?></td>
                                        <td><strong><?= htmlspecialchars($username); ?></strong></td>
                                        <td class="text-center">
                                            <?php if ($peran == 'A'): ?>
                                                <span class="label bg-red">Administrator</span>
                                            <?php else: ?>
                                                <span class="label bg-blue">Kasir</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($pin)): ?>
                                                <span class="label bg-green">Terset (6 Digit)</span>
                                            <?php else: ?>
                                                <span class="label bg-orange">Belum Set PIN</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="edit.php?username=<?= urlencode($username); ?>" class="btn btn-sm btn-info waves-effect" title="Edit"><i class="material-icons">edit</i></a>
                                            <a href="hapus.php?username=<?= urlencode($username); ?>" class="btn btn-sm btn-danger waves-effect" title="Hapus" onclick="return confirm('Yakin ingin menghapus akun <?= htmlspecialchars($username); ?>?');"><i class="material-icons">delete</i></a>
                                        </td>
                                    </tr>
                                    <?php 
                                            $no++;
                                        }
                                    } else {
                                        echo '<tr><td colspan="5" class="text-center">Belum ada data pengguna.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php include '../footer.php'; ?>

        </div>
    </section>

    <!-- Jquery Core Js -->
    <script src="../AdminBSB/plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap Core Js -->
    <script src="../AdminBSB/plugins/bootstrap/js/bootstrap.js"></script>
    <!-- Slimscroll Plugin Js -->
    <script src="../AdminBSB/plugins/jquery-slimscroll/jquery.slimscroll.js"></script>
    <!-- Waves Effect Plugin Js -->
    <script src="../AdminBSB/plugins/node-waves/waves.js"></script>
    <!-- Custom Js -->
    <script src="../AdminBSB/js/admin.js"></script>

    <script>
    // Inisialisasi SlimScroll agar sidebar bisa di-scroll dengan rapi
    $(document).ready(function() {
        if ($.fn.slimScroll) {
            $('.list').slimscroll({
                height: 'calc(100vh - 60px)',
                size: '4px',
                position: 'right',
                color: 'rgba(0,0,0,0.2)',
                alwaysVisible: false,
                borderRadius: '0',
                railBorderRadius: '0'
            });
        }
    });
    </script>
</body>
</html>