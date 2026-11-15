<?php
require_once 'database/koneksi.php';

// Jika tidak ada ID pesanan di URL, kembalikan ke halaman menu
if (!isset($_GET['id'])) {
    header("Location: menu_pelanggan.php");
    exit();
}

// Mengambil nomor nota langsung dari URL
$no_nota = $_GET['id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Pesanan Berhasil - Bakul Sega Bu Lastri</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <!-- AdminBSB CSS -->
    <link href="AdminBSB/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="AdminBSB/plugins/node-waves/waves.css" rel="stylesheet" />
    <link href="AdminBSB/css/style.css" rel="stylesheet">
    <link href="AdminBSB/css/themes/all-themes.css" rel="stylesheet" />
</head>

<body class="theme-red">
    <div class="overlay"></div>

    <!-- Memanggil Navbar -->
    <?php include 'navbar.php'; ?>

    <section class="content" style="margin-left: 15px; margin-right: 15px; margin-top: 100px;">
        <div class="container-fluid">
            <div class="row clearfix">
                <!-- Membuat form berada di tengah layar -->
                <div class="col-lg-6 col-md-8 col-sm-12 col-xs-12" style="float: none; margin: 0 auto;">
                    <div class="card text-center" style="padding: 40px 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                        
                        <!-- Ikon Sukses -->
                        <i class="material-icons col-green" style="font-size: 80px;">check_circle</i>
                        
                        <h2 class="m-t-20" style="font-weight: bold; color: #333;">Pesanan Berhasil Dibuat!</h2>
                        <p class="font-16 text-muted m-t-15">Terima kasih, pesanan Anda telah masuk ke sistem kami dan sedang menunggu proses.</p>
                        
                        <!-- Kotak Info Nomor Nota -->
                        <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0; border: 2px dashed #ccc;">
                            <p class="font-14 m-b-5">Nomor Nota / Pesanan Anda:</p>
                            <h3 class="col-red m-t-0" style="font-weight: bold; font-family: monospace;">
                                <?= htmlspecialchars($no_nota); ?>
                            </h3>
                            <p class="font-12 col-grey m-b-0">Harap simpan atau tunjukkan nomor nota ini kepada kasir/kurir kami.</p>
                        </div>

                        <!-- Tombol Kembali -->
                        <a href="menu_pelanggan.php" class="btn btn-primary btn-lg waves-effect m-t-15" style="border-radius: 30px; padding: 10px 30px; font-weight: bold;">
                            <i class="material-icons" style="font-size: 20px; vertical-align: bottom;">restaurant_menu</i> KEMBALI KE KATALOG MENU
                        </a>
                        
                    </div>
                </div>
            </div>
            
            <?php include 'footer.php'; ?>
        </div>
    </section>

    <!-- AdminBSB JS -->
    <script src="AdminBSB/plugins/jquery/jquery.min.js"></script>
    <script src="AdminBSB/plugins/bootstrap/js/bootstrap.js"></script>
    <script src="AdminBSB/plugins/node-waves/waves.js"></script>
</body>
</html>