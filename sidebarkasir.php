<?php
// Deteksi path script yang sedang dibuka (lebih akurat daripada REQUEST_URI)
$current_script = $_SERVER['SCRIPT_NAME'];
?>

<aside id="leftsidebar" class="sidebar">
    <!-- User Info -->
    <div class="user-info">
        <div class="image">
            <img src="../AdminBSB/images/user.png" width="48" height="48" alt="User" />
        </div>
        <div class="info-container">
            <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Kasir'); ?>
            </div>
            <div class="email">Kasir - Bakul Sega Bu Lastri</div>
            <div class="btn-group user-helper-dropdown">
                <i class="material-icons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">keyboard_arrow_down</i>
                <ul class="dropdown-menu pull-right">
                    <li><a href="../kasir_ganti_password/index.php"><i class="material-icons">lock</i>Ganti Password</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="../logout.php"><i class="material-icons">input</i>Sign Out</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- #User Info -->

    <!-- Menu Sidebar -->
    <div class="menu">
        <ul class="list">
            <li class="header">NAVIGASI UTAMA</li>
            
            <!-- Dashboard Kasir -->
            <li class="<?= (strpos($current_script, 'home_kasir') !== false) ? 'active' : ''; ?>">
                <a href="../home_kasir/index.php">
                    <i class="material-icons">dashboard</i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Form Transaksi / POS Utama -->
            <li class="<?= (strpos($current_script, 'kasir_transaksi') !== false) ? 'active' : ''; ?>">
                <a href="../kasir_transaksi/">
                    <i class="material-icons">shopping_cart</i>
                    <span>Transaksi Penjualan</span>
                </a>
            </li>

            <!-- Riwayat Transaksi -->
            <li class="<?= (strpos($current_script, 'kasir_riwayat_transaksi') !== false) ? 'active' : ''; ?>">
                <a href="../kasir_riwayat_transaksi/">
                    <i class="material-icons">receipt</i>
                    <span>Riwayat Transaksi</span>
                </a>
            </li>

            <!-- Daftar Menu & Stok -->
            <li class="<?= (strpos($current_script, 'kasir_daftar_stok_menu') !== false) ? 'active' : ''; ?>">
                <a href="../kasir_daftar_stok_menu/">
                    <i class="material-icons">restaurant_menu</i>
                    <span>Daftar & Stok Menu</span>
                </a>
            </li>

            <li class="<?= (strpos($current_script, 'kasir_rekap_kas') !== false) ? 'active' : ''; ?>">
                <a href="../kasir_rekap_kas/">
                    <i class="material-icons">today</i>
                    <span>Rekap Kas Harian</span>
                </a>
            </li>

            <!-- Ganti Password -->
            <li class="<?= (strpos($current_script, 'kasir_ganti_password') !== false) ? 'active' : ''; ?>">
                <a href="../kasir_ganti_password/index.php">
                    <i class="material-icons">lock</i>
                    <span>Ganti Password</span>
                </a>
            </li>

            <li>
                <a href="../logout.php">
                    <i class="material-icons">input</i>
                    <span>Keluar</span>
                </a>
            </li>
        </ul>
    </div>
    <!-- #Menu Sidebar -->

    <!-- Footer Sidebar -->
    <div class="legal">
        <div class="copyright">
            &copy; <?= date('Y'); ?> <a href="javascript:void(0);">Bakul Sega Bu Lastri</a>.
        </div>
        <div class="version">
            <b>Version: </b> 1.0.0
        </div>
    </div>
    <!-- #Footer Sidebar -->
</aside>