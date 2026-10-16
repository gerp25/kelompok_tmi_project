<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

$authority = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];
$id_pengguna = @$_SESSION['id_pengguna'] ?? null; // Ambil ID pengguna yang sedang login jika ada

if (($authority != 'A' && $authority != 'Admin') || $is_2fa_passed !== true) {
    echo '<script>alert("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $no_nota      = mysqli_real_escape_string($con, $_POST['no_nota']);
    $tanggal      = mysqli_real_escape_string($con, $_POST['tanggal']);
    $tipe_pesanan = mysqli_real_escape_string($con, $_POST['tipe_pesanan']);
    $metode_bayar = mysqli_real_escape_string($con, $_POST['metode_bayar']);
    $total_harga  = (float) $_POST['total_harga'];
    
    // Nilai bayar & kembali (jika form belum menyediakannya, diset sama dengan total atau 0)
    $bayar        = $total_harga; 
    $kembali      = 0;

    $id_menu_arr  = $_POST['id_menu'] ?? [];
    $harga_arr    = $_POST['harga_satuan'] ?? []; // Diperbaiki dari 'harga' menjadi 'harga_satuan'
    $jumlah_arr   = $_POST['jumlah'] ?? [];
    $subtotal_arr = $_POST['subtotal'] ?? [];

    if (empty($no_nota) || empty($id_menu_arr)) {
        echo '<script>alert("Data tidak lengkap!"); window.history.back();</script>';
        exit();
    }

    mysqli_begin_transaction($con);

    try {
        // 1. Simpan Header Penjualan (diselaraskan dengan struktur tabel database)
        if ($id_pengguna) {
            $query_penjualan = "INSERT INTO penjualan (no_nota, tanggal, tipe_pesanan, total_harga, bayar, kembali, metode_bayar, id_pengguna) 
                                VALUES ('$no_nota', '$tanggal', '$tipe_pesanan', '$total_harga', '$bayar', '$kembali', '$metode_bayar', '$id_pengguna')";
        } else {
            $query_penjualan = "INSERT INTO penjualan (no_nota, tanggal, tipe_pesanan, total_harga, bayar, kembali, metode_bayar) 
                                VALUES ('$no_nota', '$tanggal', '$tipe_pesanan', '$total_harga', '$bayar', '$kembali', '$metode_bayar')";
        }

        if (!mysqli_query($con, $query_penjualan)) {
            throw new Exception("Gagal menyimpan data master penjualan: " . mysqli_error($con));
        }

        // 2. Simpan Detail Penjualan
        for ($i = 0; $i < count($id_menu_arr); $i++) {
            $id_menu  = mysqli_real_escape_string($con, $id_menu_arr[$i]);
            $harga    = (float) ($harga_arr[$i] ?? 0);
            $jumlah   = (int) ($jumlah_arr[$i] ?? 0);
            $subtotal = (float) ($subtotal_arr[$i] ?? 0);

            if (!empty($id_menu) && $jumlah > 0) {
                $query_detail = "INSERT INTO penjualan_detail (no_nota, id_menu, jumlah, harga, subtotal) 
                                 VALUES ('$no_nota', '$id_menu', '$jumlah', '$harga', '$subtotal')";
                if (!mysqli_query($con, $query_detail)) {
                    throw new Exception("Gagal menyimpan rincian menu penjualan: " . mysqli_error($con));
                }
            }
        }

        mysqli_commit($con);
        echo '<script>alert("Transaksi penjualan berhasil disimpan!"); window.location.href="index.php";</script>';
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo '<script>alert("Terjadi kesalahan: ' . addslashes($e->getMessage()) . '"); window.history.back();</script>';
    }
} else {
    header("Location: index.php");
    exit();
}
?>