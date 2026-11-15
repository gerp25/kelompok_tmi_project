<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

$authority = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];

if (($authority != 'K' && $authority != 'Kasir') || $is_2fa_passed !== true) {
    echo '<script>alert("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

// Cek parameter nota dikirimkan lewat URL
if (isset($_GET['nota'])) {
    $no_nota = mysqli_real_escape_string($con, $_GET['nota']);

    // 1. Hapus data dari tabel penjualan_detail terlebih dahulu (berdasarkan no_nota)
    mysqli_query($con, "DELETE FROM penjualan_detail WHERE no_nota = '$no_nota'");

    // 2. Hapus data dari tabel utama (penjualan)
    $hapus_penjualan = mysqli_query($con, "DELETE FROM penjualan WHERE no_nota = '$no_nota'");

    if ($hapus_penjualan) {
        echo "<script>alert('Data transaksi dengan Nota $no_nota berhasil dihapus!');</script>";
    } else {
        echo "<script>alert('Gagal menghapus data transaksi. Silakan coba lagi.');</script>";
    }
} else {
    echo "<script>alert('Nomor Nota tidak valid atau tidak ditemukan.');</script>";
}

echo "<script>window.location.href = 'index.php';</script>";
?>