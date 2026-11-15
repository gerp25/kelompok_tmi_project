<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

// Validasi akses khusus Kasir
$authority = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];

if (($authority != 'K' && $authority != 'Kasir') || $is_2fa_passed !== true) {
    echo '<script>alert("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

if (isset($_GET['nota'])) {
    $no_nota = mysqli_real_escape_string($con, $_GET['nota']);

    // Opsional: Cek dan hapus file bukti transfer fisik dari server jika ada
    $query_cek_gambar = mysqli_query($con, "SELECT bukti_transfer FROM penjualan WHERE no_nota = '$no_nota'");
    if ($query_cek_gambar && mysqli_num_rows($query_cek_gambar) > 0) {
        $data = mysqli_fetch_assoc($query_cek_gambar);
        if (!empty($data['bukti_transfer'])) {
            $file_path = "../image/bukti_transfer/" . $data['bukti_transfer'];
            if (file_exists($file_path)) {
                unlink($file_path); // Menghapus file gambar
            }
        }
    }

    // 1. Hapus data dari tabel penjualan_detail terlebih dahulu (menghindari error foreign key)
    $hapus_detail = mysqli_query($con, "DELETE FROM penjualan_detail WHERE no_nota = '$no_nota'");

    // 2. Hapus data utama dari tabel penjualan
    $hapus_penjualan = mysqli_query($con, "DELETE FROM penjualan WHERE no_nota = '$no_nota'");

    if ($hapus_penjualan) {
        echo '<script>alert("Data transaksi berhasil dihapus secara permanen!"); window.location.href="index.php";</script>';
    } else {
        echo '<script>alert("Gagal menghapus data transaksi: ' . mysqli_error($con) . '"); window.location.href="index.php";</script>';
    }
} else {
    // Jika tidak ada parameter nota, kembalikan ke halaman utama transaksi
    header("Location: index.php");
    exit();
}
?>