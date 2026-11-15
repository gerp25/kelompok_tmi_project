<!-- Top Bar -->
<nav class="navbar">
    <div class="container-fluid">
        <div class="navbar-header">
            <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
            <a href="javascript:void(0);" class="bars"></a>
            
            <?php 
            // 1. Inisialisasi default untuk Pelanggan / Publik
            $nav_text = ''; 
            $nav_link = 'menu_pelanggan.php'; 

            // 2. Cek apakah ada session login aktif
            if (isset($_SESSION['peran'])) {
                $peran = strtolower($_SESSION['peran']);
                
                if ($peran == 'k' || $peran == 'kasir') {
                    $nav_text = ' - KASIR';
                    $nav_link = '../home_kasir/index.php';
                } elseif ($peran == 'a' || $peran == 'admin') {
                    $nav_text = ' - ADMIN';
                    $nav_link = '../home_admin/index.php';
                }
            }
            ?>
            
            <a class="navbar-brand" href="<?php echo $nav_link; ?>">BAKUL SEGA BU LASTRI<?php echo $nav_text; ?></a>
        </div>
    </div>
</nav>
<!-- #Top Bar -->