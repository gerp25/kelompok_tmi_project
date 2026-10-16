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

// Handler AJAX untuk update status ketersediaan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    header('Content-Type: application/json');
    
    if (isset($_POST['id']) && isset($_POST['status'])) {
        $id = mysqli_real_escape_string($con, $_POST['id']);
        $status = mysqli_real_escape_string($con, $_POST['status']); // 'tersedia' atau 'habis'
        
        $update = mysqli_query($con, "UPDATE menu SET status = '$status' WHERE id = '$id'");
        if ($update) {
            echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status']);
        }
    } else if (isset($_POST['kategori_id']) && isset($_POST['status'])) {
        // Update massal per kategori
        $kategori_id = mysqli_real_escape_string($con, $_POST['kategori_id']);
        $status = mysqli_real_escape_string($con, $_POST['status']);
        
        $query = !empty($kategori_id) 
            ? "UPDATE menu SET status = '$status' WHERE kategori_id = '$kategori_id'"
            : "UPDATE menu SET status = '$status'";
            
        $update = mysqli_query($con, $query);
        if ($update) {
            echo json_encode(['success' => true, 'message' => 'Semua status menu dalam kategori berhasil diperbarui']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status massal']);
        }
    }
    exit();
}

// Menangkap ID Kategori dari URL
$kategori_id = isset($_GET['kategori_id']) ? mysqli_real_escape_string($con, $_GET['kategori_id']) : '';

// Mengambil info nama kategori
$nama_kategori = 'Semua Menu';
if (!empty($kategori_id)) {
    $query_kat_info = mysqli_query($con, "SELECT * FROM kategori_menu WHERE id = '$kategori_id'");
    if ($query_kat_info && mysqli_num_rows($query_kat_info) > 0) {
        $kat_data = mysqli_fetch_assoc($query_kat_info);
        $nama_kategori = $kat_data['nama_kategori'] ?? $kat_data['kategori'] ?? $kat_data['nama'] ?? 'Kategori';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Daftar Menu - <?= htmlspecialchars($nama_kategori); ?></title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <link href="../AdminBSB/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="../AdminBSB/plugins/node-waves/waves.css" rel="stylesheet" />
    <link href="../AdminBSB/plugins/animate-css/animate.css" rel="stylesheet" />
    <link href="../AdminBSB/css/style.css" rel="stylesheet">
    <link href="../AdminBSB/css/themes/all-themes.css" rel="stylesheet" />

    <style>
        .menu-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
            position: relative;
            transition: all 0.3s ease;
        }
        
        /* TAMPILAN GELAP / DIMMED KETIKA STATUS HABIS (OFF) */
        .menu-card.is-disabled {
            background: #f7f7f7;
            border-color: #dcdcdc;
        }

        .menu-card.is-disabled .menu-title {
            color: #888;
        }

        .menu-card.is-disabled .menu-price {
            color: #aaa;
        }

        .menu-card.is-disabled .menu-desc {
            color: #aaa;
        }

        .menu-img-container {
            width: 85% !important;
            aspect-ratio: 4 / 3 !important;
            position: relative !important;
            overflow: hidden !important;
            background-color: #f5f5f5;
            display: block;
            margin: 15px auto 0 auto;
            border-radius: 12px;
        }

        .menu-img-container img {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            transition: filter 0.3s ease;
        }

        /* EFEK FOTO REDUP/GELAP KETIKA HABIS */
        .menu-card.is-disabled .menu-img-container img {
            filter: grayscale(80%) brightness(0.7) opacity(0.75);
        }

        .menu-body {
            padding: 15px;
            text-align: center;
        }

        .menu-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .menu-price {
            font-size: 14px;
            font-weight: bold;
            color: #fb8c00;
            margin-bottom: 8px;
        }

        .menu-desc {
            font-size: 13px;
            color: #666;
            height: 20px;
            overflow: hidden;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .block-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .block-header-flex h2 {
            margin: 0;
            color: #333;
            font-weight: bold;
        }

        .search-container {
            position: relative;
            min-width: 280px;
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

        .btn-back {
            border-radius: 20px;
            padding: 6px 16px;
            font-weight: bold;
            margin-bottom: 15px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* STYLING TOGGLE SWITCH ON/OFF */
        .switch-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 12px;
            background: #f9f9f9;
            padding: 6px 12px;
            border-radius: 15px;
            border: 1px solid #eee;
        }

        .switch-label {
            font-size: 12px;
            font-weight: bold;
            color: #555;
            margin: 0;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 24px;
            margin: 0;
        }

        .switch input {
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
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }

        input:checked + .slider {
            background-color: #4CAF50;
        }

        input:checked + .slider:before {
            transform: translateX(22px);
        }

        .status-badge {
            font-size: 11px;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 10px;
            text-transform: uppercase;
        }

        .status-badge.available {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-badge.unavailable {
            background-color: #ffebee;
            color: #c62828;
        }

        .global-toggle-box {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #fff;
            padding: 8px 15px;
            border-radius: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
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
            <!-- Tombol Kembali ke Kategori & Toggle Massal -->
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <a href="index.php" class="btn btn-default waves-effect btn-back">
                    <i class="material-icons" style="font-size: 18px;">arrow_back</i> KEMBALI KE KATEGORI
                </a>

                <!-- Toggle Aktifkan / Menolong Seluruh Menu Kategori -->
                <div class="global-toggle-box">
                    <span style="font-weight: bold; font-size: 13px; color: #444;">Ketersediaan Seluruh Menu:</span>
                    <label class="switch">
                        <input type="checkbox" id="globalToggle">
                        <span class="slider"></span>
                    </label>
                    <span id="globalStatusText" style="font-size: 12px; font-weight: bold; color: #666;">-</span>
                </div>
            </div>

            <!-- Header dengan Search Bar -->
            <div class="block-header block-header-flex">
                <h2>DAFTAR MENU: <?= strtoupper(htmlspecialchars($nama_kategori)); ?></h2>
                
                <div class="search-container">
                    <i class="material-icons">search</i>
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari menu masakan...">
                </div>
            </div>

            <div class="row clearfix" id="menuList">
                <?php
                // Filter berdasarkan kategori_id jika parameter tersedia
                if (!empty($kategori_id)) {
                    $query_menu = mysqli_query($con, "SELECT * FROM menu WHERE kategori_id = '$kategori_id' ORDER BY nama_menu ASC");
                } else {
                    $query_menu = mysqli_query($con, "SELECT * FROM menu ORDER BY nama_menu ASC");
                }

                if ($query_menu && mysqli_num_rows($query_menu) > 0) {
                    while ($menu = mysqli_fetch_assoc($query_menu)) {
                        $nama_file_gambar = !empty($menu['gambar']) ? $menu['gambar'] : ($menu['foto'] ?? '');
                        $gambar_menu = !empty($nama_file_gambar) ? '../image/' . $nama_file_gambar : '../image/sayur_dan_lauk.png';
                        $harga = isset($menu['harga']) ? 'Rp ' . number_format($menu['harga'], 0, ',', '.') : '-';
                        
                        // Status ketersediaan (tersedia vs habis)
                        $is_available = ($menu['status'] ?? 'tersedia') === 'tersedia';
                ?>
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 menu-item">
                    <div class="menu-card <?= $is_available ? '' : 'is-disabled'; ?>" id="card-<?= $menu['id']; ?>">
                        <div class="menu-img-container">
                            <img src="<?= $gambar_menu; ?>" alt="<?= htmlspecialchars($menu['nama_menu']); ?>">
                        </div>
                        <div class="menu-body">
                            <div class="menu-title"><?= htmlspecialchars($menu['nama_menu']); ?></div>
                            <div class="menu-price"><?= $harga; ?></div>
                            <div class="menu-desc"><?= htmlspecialchars($menu['deskripsi'] ?? ''); ?></div>
                            
                            <!-- Toggle Switch ON/OFF Per Menu -->
                            <div class="switch-container">
                                <span class="switch-label">Status:</span>
                                <label class="switch">
                                    <input type="checkbox" class="item-toggle" data-id="<?= $menu['id']; ?>" <?= $is_available ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                                <span class="status-badge <?= $is_available ? 'available' : 'unavailable'; ?>" id="badge-<?= $menu['id']; ?>">
                                    <?= $is_available ? 'Tersedia' : 'Habis'; ?>
                                </span>
                            </div>

                            <a href="detail_menu.php?id=<?= $menu['id']; ?>" class="btn <?= $is_available ? 'btn-danger' : 'btn-default'; ?> btn-block waves-effect" id="btn-detail-<?= $menu['id']; ?>">
                                <i class="material-icons" style="font-size: 16px; vertical-align: middle;">visibility</i> 
                                <span style="vertical-align: middle; font-weight: bold;">LIHAT DETAIL</span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo '<div class="col-md-12 text-center" style="padding: 30px;"><p class="text-muted">Belum ada menu masakan pada kategori ini.</p></div>';
                }
                ?>
                
                <!-- Pesan ketika pencarian tidak ditemukan -->
                <div id="noResults" class="col-md-12 text-center" style="display: none; padding: 20px;">
                    <p class="text-muted">Menu yang anda cari tidak ditemukan.</p>
                </div>
            </div>

            <!-- MEMANGGIL FOOTER -->
            <?php include '../footer.php'; ?>
        </div>
    </section>

    <script src="../AdminBSB/plugins/jquery/jquery.min.js"></script>
    <script src="../AdminBSB/plugins/bootstrap/js/bootstrap.js"></script>
    <script src="../AdminBSB/plugins/node-waves/waves.js"></script>
    <script src="../AdminBSB/js/admin.js"></script>

    <!-- Script Real-time Filter & Toggle ON/OFF Switch -->
    <script>
        $(document).ready(function() {
            // Function untuk mengupdate status centang global toggle
            function checkGlobalState() {
                var total = $('.item-toggle').length;
                var checkedCount = $('.item-toggle:checked').length;
                
                if (total > 0 && total === checkedCount) {
                    $('#globalToggle').prop('checked', true);
                    $('#globalStatusText').text('ON').css('color', '#4CAF50');
                } else {
                    $('#globalToggle').prop('checked', false);
                    $('#globalStatusText').text('OFF / Parsial').css('color', '#f44336');
                }
            }

            checkGlobalState();

            // Function memperbarui UI kartu menu (Gelap / Normal)
            function updateCardUI(menuId, isAvailable) {
                var card = $('#card-' + menuId);
                var badge = $('#badge-' + menuId);
                var btnDetail = $('#btn-detail-' + menuId);

                if (isAvailable) {
                    card.removeClass('is-disabled');
                    badge.removeClass('unavailable').addClass('available').text('Tersedia');
                    btnDetail.removeClass('btn-default').addClass('btn-danger');
                } else {
                    card.addClass('is-disabled');
                    badge.removeClass('available').addClass('unavailable').text('Habis');
                    btnDetail.removeClass('btn-danger').addClass('btn-default');
                }
            }

            // Handle Per Perubahan Switch Item (Per Menu)
            $('.item-toggle').on('change', function() {
                var menuId = $(this).data('id');
                var isChecked = $(this).is(':checked');
                var newStatus = isChecked ? 'tersedia' : 'habis';

                $.ajax({
                    url: 'daftar_menu.php',
                    type: 'POST',
                    data: {
                        action: 'update_status',
                        id: menuId,
                        status: newStatus
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            updateCardUI(menuId, isChecked);
                            checkGlobalState();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan koneksi server.');
                    }
                });
            });

            // Handle Switch Massal (Seluruh Menu Kategori)
            $('#globalToggle').on('change', function() {
                var isChecked = $(this).is(':checked');
                var newStatus = isChecked ? 'tersedia' : 'habis';
                var katId = '<?= $kategori_id; ?>';

                $.ajax({
                    url: 'daftar_menu.php',
                    type: 'POST',
                    data: {
                        action: 'update_status',
                        kategori_id: katId,
                        status: newStatus
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('.item-toggle').each(function() {
                                $(this).prop('checked', isChecked);
                                var menuId = $(this).data('id');
                                updateCardUI(menuId, isChecked);
                            });
                            checkGlobalState();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Terjadi kesalahan koneksi server.');
                    }
                });
            });

            // Filter Search Bar
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase().trim();
                var visibleCount = 0;

                $('.menu-item').each(function() {
                    var title = $(this).find('.menu-title').text().toLowerCase();
                    var desc = $(this).find('.menu-desc').text().toLowerCase();

                    if (title.indexOf(value) > -1 || desc.indexOf(value) > -1) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });

                if (visibleCount === 0) {
                    $('#noResults').show();
                } else {
                    $('#noResults').hide();
                }
            });
        });
    </script>
</body>
</html>