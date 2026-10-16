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

// PROSES AJAX UPDATE STATUS INDIVIDUAL (ON/OFF)
if (isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    header('Content-Type: application/json');
    $id_status = mysqli_real_escape_string($con, $_POST['id']);
    $status    = mysqli_real_escape_string($con, $_POST['status']); // 'tersedia' / 'habis'

    $query_update = mysqli_query($con, "UPDATE menu SET status = '$status' WHERE id = '$id_status'");
    if ($query_update) {
        echo json_encode(['success' => true, 'status' => $status]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
    exit();
}

// PROSES AJAX UPDATE STATUS MASSAL (ON SEMUA / OFF SEMUA)
if (isset($_POST['action']) && $_POST['action'] === 'toggle_all_status') {
    header('Content-Type: application/json');
    $status = mysqli_real_escape_string($con, $_POST['status']); // 'tersedia' / 'habis'

    $query_update_all = mysqli_query($con, "UPDATE menu SET status = '$status'");
    if ($query_update_all) {
        echo json_encode(['success' => true, 'status' => $status]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
    exit();
}

// Proses Hapus Menu
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($con, $_GET['hapus']);

    // Cek data gambar sebelum dihapus
    $q_cek = mysqli_query($con, "SELECT gambar FROM menu WHERE id = '$id_hapus'");
    if ($q_cek && mysqli_num_rows($q_cek) > 0) {
        $data_menu = mysqli_fetch_assoc($q_cek);
        
        // Hapus file gambar jika ada
        if (!empty($data_menu['gambar']) && file_exists("../image/" . $data_menu['gambar'])) {
            unlink("../image/" . $data_menu['gambar']);
        }

        // Hapus data dari database
        $q_delete = mysqli_query($con, "DELETE FROM menu WHERE id = '$id_hapus'");
        if ($q_delete) {
            echo '<script>alert("Menu berhasil dihapus!"); window.location.href="index.php";</script>';
        } else {
            echo '<script>alert("Gagal menghapus menu: ' . mysqli_error($con) . '"); window.location.href="index.php";</script>';
        }
    }
}

// PROSES TAMBAH KATEGORI BARU VIA MODAL
if (isset($_POST['simpan_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($con, $_POST['nama_kategori']);
    $deskripsi     = mysqli_real_escape_string($con, $_POST['deskripsi']);

    if (!empty($nama_kategori)) {
        $cek = mysqli_query($con, "SELECT id FROM kategori_menu WHERE nama_kategori = '$nama_kategori'");
        if (mysqli_num_rows($cek) > 0) {
            echo '<script>alert("Nama kategori sudah ada!"); window.location.href="index.php";</script>';
            exit();
        } else {
            $insert_kat = mysqli_query($con, "INSERT INTO kategori_menu (nama_kategori, deskripsi) VALUES ('$nama_kategori', '$deskripsi')");
            if ($insert_kat) {
                echo '<script>alert("Kategori baru berhasil ditambahkan!"); window.location.href="index.php";</script>';
                exit();
            } else {
                echo '<script>alert("Gagal menambahkan kategori: ' . mysqli_error($con) . '");</script>';
            }
        }
    } else {
        echo '<script>alert("Nama kategori tidak boleh kosong!");</script>';
    }
}

// Cek apakah ada menu yang berstatus habis untuk menentukan status awal toggle global
$q_check_global = mysqli_query($con, "SELECT id FROM menu WHERE status = 'habis' LIMIT 1");
$all_on_default = (mysqli_num_rows($q_check_global) == 0);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Daftar Menu - Bakul Sega Bu Lastri</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <?php include '../css.php'; ?>

    <style>
        .category-section {
            margin-bottom: 35px;
        }

        .category-header {
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }

        .category-header h3 {
            margin: 0 0 5px 0;
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .category-header p {
            margin: 0;
            font-size: 13px;
            color: #777;
        }

        .menu-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #e0e0e0;
        }
        
        .menu-img-container {
            width: 100%;
            height: 180px;
            position: relative;
            overflow: hidden;
            background-color: #f5f5f5;
            border-radius: 12px;
            margin-bottom: 12px;
        }

        .menu-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: filter 0.3s ease, opacity 0.3s ease;
        }

        /* EFEK GELAP / HABIS SAAT STATUS OFF */
        .menu-card.is-disabled .menu-img-container img {
            filter: grayscale(100%) brightness(50%);
        }

        /* BADGE OVERLAY HABIS */
        .out-of-stock-badge {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(217, 83, 79, 0.9);
            color: #fff;
            padding: 6px 16px;
            font-weight: bold;
            font-size: 13px;
            border-radius: 20px;
            letter-spacing: 1px;
            z-index: 2;
            pointer-events: none;
        }

        .menu-card.is-disabled .out-of-stock-badge {
            display: block;
        }

        /* TOGGLE SWITCH STYLING (INDIVIDUAL) */
        .status-switch-container {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 5;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(4px);
            padding: 4px 8px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .switch-label {
            font-size: 10px;
            font-weight: bold;
            color: #333;
            text-transform: uppercase;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 38px;
            height: 20px;
            margin: 0;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .3s;
            border-radius: 20px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 14px;
            width: 14px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #4CAF50;
        }

        input:checked + .slider:before {
            transform: translateX(18px);
        }

        /* TOGGLE SWITCH GLOBAL STYLING */
        .global-toggle-wrapper {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 8px;
            background: #fff;
            padding: 5px 12px;
            border-radius: 20px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            width: fit-content;
            margin-left: auto;
        }

        .global-toggle-title {
            font-size: 12px;
            font-weight: bold;
            color: #555;
        }

        .menu-body {
            text-align: center;
        }

        .menu-title {
            font-size: 16px;
            font-weight: bold;
            color: #222;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .menu-price {
            font-size: 14px;
            font-weight: bold;
            color: #d9534f;
            margin-bottom: 6px;
        }

        .menu-desc {
            font-size: 12px;
            color: #777;
            height: 32px;
            overflow: hidden;
            margin-bottom: 15px;
            line-height: 1.3;
        }

        .menu-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .menu-actions .btn-detail {
            flex: 1;
            background-color: #f44336 !important;
            color: #fff !important;
            border-radius: 6px;
            font-weight: bold;
            font-size: 12px;
            padding: 7px 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            border: none;
            box-shadow: none;
            text-decoration: none;
        }

        .menu-actions .btn-edit {
            border: 1px solid #e0e0e0;
            background: #fff;
            border-radius: 6px;
            padding: 6px 8px;
            color: #2196F3;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .menu-actions .btn-edit:hover {
            background-color: #e3f2fd;
        }

        .menu-actions .btn-hapus {
            border: 1px solid #e0e0e0;
            background: #fff;
            border-radius: 6px;
            padding: 6px 8px;
            color: #f44336;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .menu-actions .btn-hapus:hover {
            background-color: #ffebee;
        }

        .block-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .block-header-flex h2 {
            margin: 0;
            color: #555;
            font-weight: bold;
        }

        .header-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }

        .header-buttons {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-container {
            position: relative;
            min-width: 230px;
        }

        .search-container input {
            padding-left: 38px;
            border-radius: 20px;
            border: 1px solid #ccc;
            box-shadow: none !important;
        }

        .search-container .material-icons {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: 20px;
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
            <!-- Header dengan Search Bar dan Tombol Aksi -->
            <div class="block-header block-header-flex">
                <h2>DAFTAR MENU MASAKAN</h2>
                
                <div class="header-actions">
                    <div class="header-buttons">
                        <div class="search-container">
                            <i class="material-icons">search</i>
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari menu masakan...">
                        </div>

                        <!-- Tombol Tambah Kategori -->
                        <button type="button" class="btn bg-purple waves-effect" data-toggle="modal" data-target="#modalTambahKategori">
                            <i class="material-icons">category</i>
                            <span>TAMBAH KATEGORI</span>
                        </button>

                        <!-- Tombol Tambah Menu -->
                        <a href="../admin_resep/tambah.php" class="btn bg-blue waves-effect">
                            <i class="material-icons">add_box</i>
                            <span>TAMBAH MENU</span>
                        </a>
                    </div>

                    <!-- TOGGLE BUTTON ON/OFF SEMUA DI BAWAH TOMBOL TAMBAH MENU -->
                    <div class="global-toggle-wrapper">
                        <span class="global-toggle-title">Status Semua Menu:</span>
                        <span class="switch-label" id="globalLabel"><?= $all_on_default ? 'ON' : 'OFF'; ?></span>
                        <label class="toggle-switch">
                            <input type="checkbox" id="toggleAllMenu" <?= $all_on_default ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div id="menuContainer">
                <?php
                // 1. Ambil seluruh data Kategori dari database
                $query_kat = mysqli_query($con, "SELECT * FROM kategori_menu ORDER BY id ASC");
                $total_menu_found = 0;

                if ($query_kat && mysqli_num_rows($query_kat) > 0) {
                    while ($kat = mysqli_fetch_assoc($query_kat)) {
                        $id_kategori   = $kat['id'];
                        $nama_kategori = $kat['nama_kategori'] ?? $kat['kategori'] ?? 'Kategori';
                        $sub_deskripsi = $kat['deskripsi'] ?? '';

                        // 2. Ambil menu masakan sesuai kategori_id masing-masing
                        $query_menu = mysqli_query($con, "SELECT * FROM menu WHERE kategori_id = '$id_kategori' ORDER BY id ASC");

                        if ($query_menu && mysqli_num_rows($query_menu) > 0) {
                            $total_menu_found += mysqli_num_rows($query_menu);
                ?>
                            <!-- Kelompok Kategori -->
                            <div class="category-section">
                                <div class="category-header">
                                    <h3><?= htmlspecialchars($nama_kategori); ?></h3>
                                    <?php if (!empty($sub_deskripsi)): ?>
                                        <p><?= htmlspecialchars($sub_deskripsi); ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="row clearfix">
                                    <?php
                                    while ($menu = mysqli_fetch_assoc($query_menu)) {
                                        $id_menu   = $menu['id'];
                                        $nama_menu = $menu['nama_menu'] ?? $menu['nama_masakan'] ?? $menu['nama'] ?? 'Menu';
                                        $harga     = $menu['harga'] ?? 0;
                                        $deskripsi = $menu['deskripsi'] ?? '';
                                        $gambar    = !empty($menu['gambar']) ? '../image/' . $menu['gambar'] : '../image/sayur_dan_lauk.png';
                                        
                                        // Pengecekan status ketersediaan
                                        $status_menu = isset($menu['status']) ? strtolower($menu['status']) : 'tersedia';
                                        $is_available = ($status_menu === 'tersedia' || $status_menu === '1' || $status_menu === 'on');
                                    ?>
                                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 menu-item">
                                            <div class="menu-card <?= !$is_available ? 'is-disabled' : ''; ?>" id="card-<?= $id_menu; ?>">
                                                <div class="menu-img-container">
                                                    <!-- TOGGLE SWITCH STATUS INDIVIDUAL -->
                                                    <div class="status-switch-container">
                                                        <span class="switch-label" id="label-<?= $id_menu; ?>"><?= $is_available ? 'ON' : 'OFF'; ?></span>
                                                        <label class="toggle-switch">
                                                            <input type="checkbox" class="toggle-status" data-id="<?= $id_menu; ?>" <?= $is_available ? 'checked' : ''; ?>>
                                                            <span class="slider"></span>
                                                        </label>
                                                    </div>

                                                    <div class="out-of-stock-badge">HABIS</div>
                                                    <img src="<?= $gambar; ?>" alt="<?= htmlspecialchars($nama_menu); ?>">
                                                </div>
                                                <div class="menu-body">
                                                    <div class="menu-title"><?= htmlspecialchars($nama_menu); ?></div>
                                                    <div class="menu-price">Rp <?= number_format($harga, 0, ',', '.'); ?></div>
                                                    <div class="menu-desc"><?= htmlspecialchars($deskripsi); ?></div>
                                                    
                                                    <div class="menu-actions">
                                                        <a href="detail_menu.php?id=<?= $id_menu; ?>" class="btn waves-effect btn-detail">
                                                            <i class="material-icons" style="font-size: 16px;">visibility</i> 
                                                            <span>LIHAT DETAIL</span>
                                                        </a>
                                                        <a href="../admin_resep/edit.php?id=<?= $id_menu; ?>" class="btn-edit waves-effect" title="Edit Menu">
                                                            <i class="material-icons" style="font-size: 18px;">edit</i>
                                                        </a>
                                                        <a href="index.php?hapus=<?= $id_menu; ?>" class="btn-hapus waves-effect" onclick="return confirm('Apakah Anda yakin ingin menghapus menu \'<?= htmlspecialchars($nama_menu); ?>\'?');" title="Hapus Menu">
                                                            <i class="material-icons" style="font-size: 18px;">delete</i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php 
                                    } 
                                    ?>
                                </div>
                            </div>
                <?php
                        }
                    }
                }

                if ($total_menu_found == 0) {
                    echo '<div class="col-md-12 text-center"><p>Belum ada menu masakan yang tersedia.</p></div>';
                }
                ?>
                
                <!-- Pesan jika pencarian tidak ditemukan -->
                <div id="noResults" class="col-md-12 text-center" style="display: none;">
                    <p class="text-muted">Menu masakan yang Anda cari tidak ditemukan.</p>
                </div>
            </div>

            <!-- FOOTER -->
            <?php include '../footer.php'; ?>
        </div>
    </section>

    <!-- MODAL TAMBAH KATEGORI BARU -->
    <div class="modal fade" id="modalTambahKategori" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="POST">
                    <div class="modal-header bg-purple">
                        <h4 class="modal-title">Tambah Kategori Menu Baru</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group form-float">
                            <div class="form-line">
                                <input type="text" name="nama_kategori" class="form-control" required>
                                <label class="form-label">Nama Kategori (contoh: Minuman, Dessert, Paket Hemat)</label>
                            </div>
                        </div>
                        <div class="form-group form-float m-t-20">
                            <div class="form-line">
                                <textarea name="deskripsi" rows="3" class="form-control no-resize"></textarea>
                                <label class="form-label">Deskripsi / Keterangan Kategori (Opsional)</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="simpan_kategori" class="btn btn-link waves-effect bg-purple" style="color: white !important;">SIMPAN KATEGORI</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">BATAL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../script.php'; ?>

    <!-- Script AJAX Toggle Status Ketersediaan & Filter Search -->
    <script>
        $(document).ready(function() {
            // TOGGLE STATUS INDIVIDUAL
            $('.toggle-status').on('change', function() {
                var menuId = $(this).data('id');
                var isChecked = $(this).is(':checked');
                var statusVal = isChecked ? 'tersedia' : 'habis';
                var card = $('#card-' + menuId);
                var label = $('#label-' + menuId);

                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        action: 'toggle_status',
                        id: menuId,
                        status: statusVal
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (isChecked) {
                                card.removeClass('is-disabled');
                                label.text('ON');
                            } else {
                                card.addClass('is-disabled');
                                label.text('OFF');
                            }
                        } else {
                            alert('Gagal memperbarui status ketersediaan.');
                            $(this).prop('checked', !isChecked);
                        }
                    }.bind(this),
                    error: function() {
                        alert('Terjadi kesalahan koneksi ke server.');
                        $(this).prop('checked', !isChecked);
                    }.bind(this)
                });
            });

            // SINGLE TOGGLE SWITCH GLOBAL (ON/OFF UNTUK SEMUA MENU)
            $('#toggleAllMenu').on('change', function() {
                var isChecked = $(this).is(':checked');
                var statusVal = isChecked ? 'tersedia' : 'habis';
                var confirmMsg = isChecked ? 
                    'Apakah Anda yakin ingin MENGAKTIFKAN (ON) semua menu?' : 
                    'Apakah Anda yakin ingin MENONAKTIFKAN (OFF) semua menu?';

                if (!confirm(confirmMsg)) {
                    $(this).prop('checked', !isChecked);
                    return;
                }

                $.ajax({
                    url: '',
                    type: 'POST',
                    data: {
                        action: 'toggle_all_status',
                        status: statusVal
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (isChecked) {
                                $('.toggle-status').prop('checked', true);
                                $('.menu-card').removeClass('is-disabled');
                                $('.switch-label').text('ON');
                                $('#globalLabel').text('ON');
                            } else {
                                $('.toggle-status').prop('checked', false);
                                $('.menu-card').addClass('is-disabled');
                                $('.switch-label').text('OFF');
                                $('#globalLabel').text('OFF');
                            }
                        } else {
                            alert('Gagal memperbarui semua status menu.');
                            $('#toggleAllMenu').prop('checked', !isChecked);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan koneksi ke server.');
                        $('#toggleAllMenu').prop('checked', !isChecked);
                    }
                });
            });

            // FILTER PENCARIAN REAL-TIME
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase().trim();
                var totalVisible = 0;

                $('.category-section').each(function() {
                    var sectionVisibleCount = 0;

                    $(this).find('.menu-item').each(function() {
                        var title = $(this).find('.menu-title').text().toLowerCase();
                        var desc = $(this).find('.menu-desc').text().toLowerCase();

                        if (title.indexOf(value) > -1 || desc.indexOf(value) > -1) {
                            $(this).show();
                            sectionVisibleCount++;
                            totalVisible++;
                        } else {
                            $(this).hide();
                        }
                    });

                    if (sectionVisibleCount === 0) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });

                if (totalVisible === 0) {
                    $('#noResults').show();
                } else {
                    $('#noResults').hide();
                }
            });
        });
    </script>
</body>
</html>