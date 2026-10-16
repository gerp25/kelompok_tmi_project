<?php
// Deteksi URL halaman yang sedang dibuka
$current_page = $_SERVER['REQUEST_URI'];
?>
<!-- Left Sidebar -->
<aside id="leftsidebar" class="sidebar">
    <!-- User Info -->
    <div class="user-info">
        <div class="image">
            <img src="../AdminBSB/images/user.png" width="48" height="48" alt="User" />
        </div>
        <div class="info-container">
            <div class="name" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?= htmlspecialchars($_SESSION['nama_lengkap'] ?? $_SESSION['username'] ?? 'Admin'); ?>
            </div>
            <div class="email">Admin - Bakul Sega Bu Lastri</div>
            <div class="btn-group user-helper-dropdown">
                <i class="material-icons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">keyboard_arrow_down</i>
                <ul class="dropdown-menu pull-right">
                    <li><a href="ganti_password.php"><i class="material-icons">lock</i>Ganti Password</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="../logout.php"><i class="material-icons">input</i>Sign Out</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- #User Info -->

    <!-- Menu Navigasi -->
    <div class="menu">
        <ul class="list">    
            <li class="<?= (strpos($current_page, 'home_admin') !== false) ? 'active' : ''; ?>">
                <a href="../home_admin/index.php">
                    <i class="material-icons">home</i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="<?= (strpos($current_page, 'admin_resep') !== false) ? 'active' : ''; ?>">
                <a href="../admin_resep/index.php">
                    <i class="material-icons">restaurant_menu</i>
                    <span>Data Resep</span>
                </a>
            </li>

            <!-- Menu Data Bahan yang ditambahkan kembali -->
            <!-- <li class="<?= (strpos($current_page, 'admin_data_bahan') !== false) ? 'active' : ''; ?>">
                <a href="../admin_data_bahan/index.php">
                    <i class="material-icons">inventory_2</i>
                    <span>Data Bahan</span>
                </a>
            </li> -->
            
            <li class="<?= (strpos($current_page, 'admin_kategori_menu') !== false) ? 'active' : ''; ?>">
                <a href="../admin_kategori_menu/index.php">
                    <i class="material-icons">category</i>
                    <span>Pilihan Menu</span>
                </a>
            </li>

            <!-- Menu Data Supplier -->
            <li class="<?= (strpos($current_page, 'admin_supplier') !== false) ? 'active' : ''; ?>">
                <a href="../admin_supplier/index.php">
                    <i class="material-icons">local_shipping</i>
                    <span>Data Supplier</span>
                </a>
            </li>

            <!-- Menu Data Pembelian -->
            <li class="<?= (strpos($current_page, 'admin_pembelian') !== false) ? 'active' : ''; ?>">
                <a href="../admin_pembelian/index.php">
                    <i class="material-icons">shopping_cart</i>
                    <span>Data Pembelian</span>
                </a>
            </li>

            <!-- Menu Data Pemnjual -->
            <li class="<?= (strpos($current_page, 'admin_penjualan') !== false) ? 'active' : ''; ?>">
                <a href="../admin_penjualan/">
                    <i class="material-icons">shopping_cart</i>
                    <span>Data Penjualan</span>
                </a>
            </li>

            <li class="<?= (strpos($current_page, 'data_laporan') !== false) ? 'active' : ''; ?>">
                <a href="../admin_data_laporan/">
                    <i class="material-icons">assessment</i>
                    <span>Laporan Laba Rugi</span>
                </a>
            </li>
            
            <li class="<?= (strpos($current_page, 'data_pengguna') !== false) ? 'active' : ''; ?>">
                <a href="../admin_data_pengguna/">
                    <i class="material-icons">people</i>
                    <span>Data Pengguna</span>
                </a>
            </li>
            
            <li class="<?= (strpos($current_page, 'ganti_password') !== false) ? 'active' : ''; ?>">
                <a href="../admin_ganti_password/index.php">
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
</aside>