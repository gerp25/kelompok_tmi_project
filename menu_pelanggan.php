<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database/koneksi.php';

// ini nomor wa nya awalan harus 62 
$nomor_wa_resto = "6281578075554"; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Katalog Menu - Bakul Sega Bu Lastri</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <!-- Memanggil CSS Bawaan Template AdminBSB dari folder lokalmu -->
    <link href="AdminBSB/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="AdminBSB/plugins/node-waves/waves.css" rel="stylesheet" />
    <link href="AdminBSB/plugins/animate-css/animate.css" rel="stylesheet" />
    <link href="AdminBSB/css/style.css" rel="stylesheet">
    <link href="AdminBSB/css/themes/all-themes.css" rel="stylesheet" />

    <style>
        /* CSS buat Hal Menu */
        .category-section { margin-bottom: 35px; }
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
        .category-header p { margin: 0; font-size: 13px; color: #777; }
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
            background: rgba(244, 67, 54, 0.9); /* Merah khas AdminBSB */
            color: #fff;
            padding: 6px 16px;
            font-weight: bold;
            font-size: 13px;
            border-radius: 20px;
            letter-spacing: 1px;
            z-index: 2;
        }

        .menu-card.is-disabled .out-of-stock-badge { display: block; }

        .menu-body { text-align: center; }
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
            color: #F44336;
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
        .btn-wa {
            width: 100%;
            background-color: #4CAF50 !important;
            color: #fff !important;
            border-radius: 6px;
            font-weight: bold;
            font-size: 13px;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: none;
            box-shadow: none;
            text-decoration: none;
        }
        .btn-wa:hover {
            background-color: #388E3C !important;
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
            margin: 0; color: #555; font-weight: bold;
        }
        .search-container {
            position: relative; min-width: 280px;
        }
        .search-container input {
            padding-left: 38px; border-radius: 20px;
            border: 1px solid #ccc; box-shadow: none !important;
        }
        .search-container .material-icons {
            position: absolute; left: 12px; top: 50%;
            transform: translateY(-50%); color: #888; font-size: 20px;
        }

        /* --- CSS Tambahan untuk Info Rekening --- */
        .info-rekening-card {
            background-color: #fff;
            border-left: 5px solid #F44336; /* Warna merah AdminBSB */
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
        }
        
        .info-rekening-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            width: 100%;
        }

        .bank-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            flex: 1;
            min-width: 250px;
            margin-bottom: 10px;
        }

        .bank-icon {
            background-color: #fce4e4;
            color: #F44336;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bank-details h4 {
            margin: 0 0 3px 0;
            font-size: 15px;
            font-weight: bold;
            color: #222;
        }

        .bank-details p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }

        .bank-details .rek-number {
            font-family: monospace;
            font-size: 16px;
            color: #d32f2f;
            font-weight: bold;
            letter-spacing: 1px;
        }
    </style>
</head>

<body class="theme-red">
    <div class="overlay"></div>

    <?php include 'navbar.php';?>

    <section class="content" style="margin-left: 15px; margin-right: 15px; margin-top: 100px;">
        <div class="container-fluid">
            <!-- Header Pencarian -->
            <div class="block-header block-header-flex">
                <h2>KATALOG MENU BAKUL SEGA BU LASTRI</h2>
                
                <div class="header-actions">
                    <div class="search-container">
                        <i class="material-icons">search</i>
                        <input type="text" id="searchInput" class="form-control" placeholder="Cari menu masakan yang ingin dipesan...">
                    </div>
                </div>
            </div>

            <!-- INFO REKENING -->
            <div class="info-rekening-card">
                <div class="info-rekening-title">
                    <i class="material-icons" style="font-size: 18px; vertical-align: text-bottom; margin-right: 5px;">payment</i>
                    Metode Pembayaran Transfer
                </div>
                
                <div class="row" style="width: 100%; margin: 0;">
                    <!-- Bank BCA -->
                    <div class="col-md-6 col-sm-12" style="padding-left: 0;">
                        <div class="bank-item">
                            <div class="bank-icon">
                                <i class="material-icons">account_balance</i>
                            </div>
                            <div class="bank-details">
                                <h4>Bank BCA</h4>
                                <p class="rek-number">3580437060</p>
                                <p>A.N Dinda Maria Agatha</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bank BRI -->
                    <div class="col-md-6 col-sm-12">
                        <div class="bank-item">
                            <div class="bank-icon">
                                <i class="material-icons">account_balance</i>
                            </div>
                            <div class="bank-details">
                                <h4>Bank BRI</h4>
                                <p class="rek-number">3685-01-004005-53-0</p>
                                <p>A.N Sulastri</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KATALOG MENU -->
            <div id="menuContainer">
                <?php
                // Ambil seluruh data Kategori dari database
                $query_kat = mysqli_query($con, "SELECT * FROM kategori_menu ORDER BY id ASC");
                $total_menu_found = 0;

                if ($query_kat && mysqli_num_rows($query_kat) > 0) {
                    while ($kat = mysqli_fetch_assoc($query_kat)) {
                        $id_kategori   = $kat['id'];
                        $nama_kategori = $kat['nama_kategori'] ?? $kat['kategori'] ?? 'Kategori';
                        $sub_deskripsi = $kat['deskripsi'] ?? '';

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
                                        $gambar    = !empty($menu['gambar']) ? 'image/' . $menu['gambar'] : 'image/sayur_dan_lauk.png';
                                        
                                        $status_menu = isset($menu['status']) ? strtolower($menu['status']) : 'tersedia';
                                        $is_available = ($status_menu === 'tersedia' || $status_menu === '1' || $status_menu === 'on');
                                        
                                        $format_harga = number_format($harga, 0, ',', '.');
                                        $teks_pesan = "Halo Bakul Sega Bu Lastri, saya ingin memesan menu *" . $nama_menu . "* seharga Rp " . $format_harga . ". Apakah masih bisa dipesan?";
                                        $link_whatsapp = "https://wa.me/" . $nomor_wa_resto . "?text=" . urlencode($teks_pesan);
                                    ?>
                                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 menu-item">
                                            <div class="menu-card <?= !$is_available ? 'is-disabled' : ''; ?>" id="card-<?= $id_menu; ?>">
                                                <div class="menu-img-container">
                                                    <div class="out-of-stock-badge">HABIS</div>
                                                    <img src="<?= $gambar; ?>" alt="<?= htmlspecialchars($nama_menu); ?>">
                                                </div>
                                                <div class="menu-body">
                                                    <div class="menu-title"><?= htmlspecialchars($nama_menu); ?></div>
                                                    <div class="menu-price">Rp <?= $format_harga; ?></div>
                                                    <div class="menu-desc"><?= htmlspecialchars($deskripsi); ?></div>
                                                    
                                                    <div class="menu-actions">
                                                        <?php if ($is_available): ?>
                                                            <!-- Tombol Pesan WA -->
                                                            <a href="<?= $link_whatsapp; ?>" target="_blank" class="btn waves-effect btn-wa">
                                                                <i class="material-icons" style="font-size: 18px;">message</i> 
                                                                <span>Pesan via WhatsApp</span>
                                                            </a>
                                                        <?php else: ?>
                                                            <!-- Tombol Disable -->
                                                            <button class="btn btn-default waves-effect" disabled style="width: 100%; border-radius: 6px; font-weight: bold; padding: 8px;">
                                                                Maaf, Menu Habis
                                                            </button>
                                                        <?php endif; ?>
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
                
                <div id="noResults" class="col-md-12 text-center" style="display: none;">
                    <p class="text-muted">Menu masakan yang Anda cari tidak ditemukan.</p>
                </div>
            </div>

            <!-- FOOTER -->
            <?php include 'footer.php'; ?>
        </div>
    </section>

    <!-- Memanggil Script JS Bawaan Template AdminBSB dari folder lokalmu -->
    <script src="AdminBSB/plugins/jquery/jquery.min.js"></script>
    <script src="AdminBSB/plugins/bootstrap/js/bootstrap.js"></script>
    <script src="AdminBSB/plugins/node-waves/waves.js"></script>
    <script src="AdminBSB/js/admin.js"></script>

    <!-- Script Filter Search Real-Time -->
    <script>
        $(document).ready(function() {
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