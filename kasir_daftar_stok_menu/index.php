<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

$authority = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];

if (($authority != 'K' && $authority != 'Kasir') || $is_2fa_passed !== true) {
    echo '<script>alert("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

// Ambil pencarian jika ada
$keyword = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';

// Menggunakan kolom yang benar-benar ada di tabel menu (tanpa JOIN dan tanpa kolom stok)
$query_str = "SELECT * FROM menu WHERE nama_menu LIKE '%$keyword%' OR kategori_id LIKE '%$keyword%' ORDER BY nama_menu ASC";

$data_menu = [];
$query_menu = mysqli_query($con, $query_str);

if ($query_menu && mysqli_num_rows($query_menu) > 0) {
    while ($row = mysqli_fetch_assoc($query_menu)) {
        $data_menu[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Daftar & Stok Menu - Bakul Sega Bu Lastri</title>
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
                <a class="navbar-brand" href="../home_kasir/index.php">BAKUL SEGA BU LASTRI - KASIR</a>
            </div>
        </div>
    </nav>

    <!-- Left Sidebar -->
    <section>
        <?php include '../sidebarkasir.php'; ?>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>DAFTAR & STOK MENU</h2>
                        </div>
                        <div class="body">
                            <!-- Form Pencarian -->
                            <form method="GET" action="" style="margin-bottom: 25px;">
                                <div class="row clearfix">
                                    <div class="col-md-6 col-sm-8">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="text" name="search" class="form-control" placeholder="Cari nama menu atau ID kategori..." value="<?= htmlspecialchars($keyword); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-sm-4">
                                        <button type="submit" class="btn btn-primary waves-effect">
                                            <i class="material-icons">search</i> Cari
                                        </button>
                                        <?php if (!empty($keyword)): ?>
                                            <a href="<?= basename($_SERVER['PHP_SELF']); ?>" class="btn btn-default waves-effect">
                                                <i class="material-icons">refresh</i> Reset
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                            <!-- End Form Pencarian -->

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Menu</th>
                                            <th>ID Kategori</th>
                                            <th>Harga</th>
                                            <th>Stok Tersedia</th>
                                            <th>Status Stok</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (!empty($data_menu)) {
                                            $no = 1;
                                            foreach ($data_menu as $menu) {
                                                $harga = isset($menu['harga']) ? $menu['harga'] : 0;
                                                $kategori_id = isset($menu['kategori_id']) ? $menu['kategori_id'] : '-';
                                                $status_db = isset($menu['status']) ? strtolower($menu['status']) : 'habis';
                                                
                                                // Menyesuaikan badge berdasarkan kolom status di database ('tersedia' / 'habis')
                                                if ($status_db == 'tersedia') {
                                                    $status_badge = '<span class="label bg-green">Tersedia</span>';
                                                } else {
                                                    $status_badge = '<span class="label bg-red">Habis</span>';
                                                }
                                        ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><strong><?= htmlspecialchars($menu['nama_menu']); ?></strong></td>
                                            <td><?= htmlspecialchars($kategori_id); ?></td>
                                            <td>Rp <?= number_format($harga, 0, ',', '.'); ?></td>
                                            <td><strong>-</strong></td>
                                            <td><?= $status_badge; ?></td>
                                        </tr>
                                        <?php 
                                            }
                                        } else {
                                            echo "<tr><td colspan='6' class='text-center'>Data menu tidak ditemukan.</td></tr>";
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

    <?php include '../script.php'; ?>
</body>

</html>