<?php
// Mulai session untuk menyimpan data keranjang
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan request benar-benar berasal dari Form POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Ambil data dari form menu_pelanggan.php
    $id_menu   = isset($_POST['id_menu']) ? $_POST['id_menu'] : null;
    $nama_menu = isset($_POST['nama_menu']) ? $_POST['nama_menu'] : null;
    $harga     = isset($_POST['harga']) ? (int)$_POST['harga'] : 0;
    $qty       = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;

    // 2. Validasi data (pastikan ID dan Nama ada, dan harga/qty lebih dari 0)
    if ($id_menu && $nama_menu && $harga > 0 && $qty > 0) {
        
        // 3. Inisialisasi array keranjang di dalam Session jika belum pernah dibuat
        if (!isset($_SESSION['keranjang'])) {
            $_SESSION['keranjang'] = array();
        }

        // 4. Cek apakah menu yang sama sudah ada di keranjang sebelumnya
        $item_exists = false;
        foreach ($_SESSION['keranjang'] as $key => $item) {
            if ($item['id_menu'] == $id_menu) {
                // Jika sudah ada, cukup tambahkan jumlah porsinya (qty)
                $_SESSION['keranjang'][$key]['qty'] += $qty;
                $item_exists = true;
                break;
            }
        }

        // 5. Jika menu belum ada di keranjang, tambahkan sebagai data baru
        if (!$item_exists) {
            $_SESSION['keranjang'][] = array(
                'id_menu'   => $id_menu,
                'nama_menu' => $nama_menu,
                'harga'     => $harga,
                'qty'       => $qty
            );
        }

        // 6. Set notifikasi sukses
        $_SESSION['pesan_sukses'] = "Menu <b>$nama_menu</b> berhasil ditambahkan ke keranjang!";
        
        // 7. Arahkan pengguna ke halaman keranjang untuk melihat pesanannya
        header("Location: keranjang.php");
        exit();

    } else {
        // Jika data tidak lengkap atau error
        echo "<script>
                alert('Gagal menambahkan ke keranjang. Data menu tidak valid!');
                window.location.href='menu_pelanggan.php';
              </script>";
        exit();
    }
} else {
    // Jika seseorang mencoba mengakses file ini secara langsung melalui URL
    header("Location: menu_pelanggan.php");
    exit();
}
?>