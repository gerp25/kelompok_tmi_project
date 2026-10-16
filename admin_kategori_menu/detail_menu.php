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

// Mengambil parameter ID Menu
$id_menu = isset($_GET['id']) ? mysqli_real_escape_string($con, $_GET['id']) : '';

if (empty($id_menu)) {
    echo '<script>alert("ID Menu tidak valid!"); window.location.href="index.php";</script>';
    exit();
}

// Query untuk mengambil data detail menu beserta nama kategorinya (jika ada hubungan relasi)
$query_detail = mysqli_query($con, "SELECT menu.*, kategori_menu.nama_kategori 
                                   FROM menu 
                                   LEFT JOIN kategori_menu ON menu.kategori_id = kategori_menu.id 
                                   WHERE menu.id = '$id_menu'");
$menu = mysqli_fetch_assoc($query_detail);

if (!$menu) {
    echo '<script>alert("Data menu tidak ditemukan!"); window.location.href="index.php";</script>';
    exit();
}

$gambar_menu = !empty($menu['gambar']) ? '../image/' . $menu['gambar'] : '../image/sayur_dan_lauk.png';
$status = strtolower($menu['status'] ?? 'tersedia');
$badge_class = ($status == 'tersedia') ? 'bg-green' : 'bg-red';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Detail - <?= htmlspecialchars($menu['nama_menu']); ?> - Bakul Sega Bu Lastri</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <?php include '../css.php'; ?>

    <style>
        .detail-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
        }

        .detail-img-container {
            width: 100%;
            aspect-ratio: 4 / 3;
            border-radius: 10px;
            overflow: hidden;
            background-color: #f5f5f5;
            position: relative;
            border: 1px solid #ddd;
        }

        .detail-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .detail-info {
            padding-left: 15px;
        }

        .detail-info h3 {
            margin-top: 0;
            font-weight: bold;
            color: #333;
        }

        .detail-info .price {
            font-size: 22px;
            color: #e91e63;
            font-weight: bold;
            margin: 15px 0;
        }

        .detail-info .description {
            font-size: 14px;
            color: #555;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 8px 0;
            font-size: 14px;
        }

        .info-table td:first-child {
            width: 130px;
            font-weight: bold;
            color: #666;
        }
    </style>
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
                <h2>DETAIL MENU MASAKAN</h2>
            </div>

            <div style="margin-bottom: 20px;">
                <a href="index.php" class="btn btn-default waves-effect">
                    <i class="material-icons">arrow_back</i> <span>KEMBALI KE DAFTAR MENU</span>
                </a>
            </div>

            <div class="row clearfix">
                <div class="col-xs-12">
                    <div class="detail-card">
                        <div class="row clearfix">
                            <!-- Kolom Gambar Menu -->
                            <div class="col-md-5 col-sm-12">
                                <div class="detail-img-container">
                                    <img src="<?= $gambar_menu; ?>" alt="<?= htmlspecialchars($menu['nama_menu']); ?>">
                                </div>
                            </div>

                            <!-- Kolom Informasi Rinci -->
                            <div class="col-md-7 col-sm-12">
                                <div class="detail-info">
                                    <h3><?= htmlspecialchars($menu['nama_menu']); ?></h3>
                                    <div class="price">
                                        Rp <?= number_format($menu['harga'], 0, ',', '.'); ?>
                                    </div>

                                    <table class="info-table">
                                        <tr>
                                            <td>Kategori</td>
                                            <td>: <?= htmlspecialchars($menu['nama_kategori'] ?? 'Tidak Ada Kategori'); ?></td>
                                        </tr>
                                        <tr>
                                            <td>Status Stok</td>
                                            <td>: 
                                                <span class="label <?= $badge_class; ?>" style="font-size: 12px; padding: 4px 10px;">
                                                    <?= ucfirst($menu['status'] ?? 'Tersedia'); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    </table>

                                    <hr>
                                    <h5>Deskripsi Menu:</h5>
                                    <p class="description">
                                        <?= !empty($menu['deskripsi']) ? nl2br(htmlspecialchars($menu['deskripsi'])) : '<em>Tidak ada deskripsi untuk menu ini.</em>'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../script.php'; ?>
</body>
</html>