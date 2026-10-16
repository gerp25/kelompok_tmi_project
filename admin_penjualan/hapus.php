<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

$authority = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];

if (($authority != 'A' && $authority != 'Admin') || $is_2fa_passed !== true) {
    echo '<script>alert("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

if (isset($_GET['nota'])) {
    $nota = mysqli_real_escape_string($con, $_GET['nota']);

    mysqli_begin_transaction($con);

    try {
        $hapus_detail = mysqli_query($con, "DELETE FROM penjualan_detail WHERE no_nota = '$nota'");
        if (!$hapus_detail) {
            throw new Exception("Gagal menghapus detail penjualan.");
        }

        $hapus_master = mysqli_query($con, "DELETE FROM penjualan WHERE no_nota = '$nota'");
        if (!$hapus_master) {
            throw new Exception("Gagal menghapus master penjualan.");
        }

        mysqli_commit($con);
        echo '<script>alert("Transaksi penjualan berhasil dihapus!"); window.location.href="index.php";</script>';
    } catch (Exception $e) {
        mysqli_rollback($con);
        echo '<script>alert("Gagal menghapus data: ' . addslashes($e->getMessage()) . '"); window.location.href="index.php";</script>';
    }
} else {
    header("Location: index.php");
    exit();
}
?>