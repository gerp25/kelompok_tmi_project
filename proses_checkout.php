<?php
session_start();
require_once 'database/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['keranjang'])) {
    
    // 1. Tangkap data dari form keranjang.php
    $tipe_pesanan      = mysqli_real_escape_string($con, $_POST['tipe_pesanan']);
    $metode_bayar      = mysqli_real_escape_string($con, $_POST['metode_pembayaran']);
    $total_harga       = (int)$_POST['total_bayar'];
    
    // Variabel ini bisa kosong jika pengguna memilih "Beli di Tempat"
    $nama_pelanggan    = isset($_POST['nama_pelanggan']) ? mysqli_real_escape_string($con, $_POST['nama_pelanggan']) : '';
    $keterangan_lokasi = isset($_POST['keterangan_lokasi']) ? mysqli_real_escape_string($con, $_POST['keterangan_lokasi']) : '';
    
    // 2. Nominal bayar
    $bayar   = ($metode_bayar === 'Transfer') ? $total_harga : 0; 
    $kembali = 0; 
    
    // 3. Generate Nomor Nota dan Tanggal
    $no_nota = "NOTA-" . date('YmdHis');
    $tanggal = date('Y-m-d H:i:s');
    
    // 4. Logika Upload Bukti Transfer (Hanya jika Transfer)
    $nama_gambar_baru = NULL; 

    if ($metode_bayar === 'Transfer' && isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] == 0) {
        $nama_file = $_FILES['bukti_transfer']['name'];
        $ukuran_file = $_FILES['bukti_transfer']['size'];
        $tmp_file = $_FILES['bukti_transfer']['tmp_name'];
        
        $ekstensi_diperbolehkan = array('png', 'jpg', 'jpeg');
        $x = explode('.', $nama_file);
        $ekstensi = strtolower(end($x));
        
        if (in_array($ekstensi, $ekstensi_diperbolehkan)) {
            if ($ukuran_file < 2048000) { 
                $nama_gambar_baru = $no_nota . '.' . $ekstensi;
                $path_upload = "image/bukti_transfer/" . $nama_gambar_baru;
                
                if (!move_uploaded_file($tmp_file, $path_upload)) {
                    echo "<script>alert('Gagal mengunggah bukti transfer.'); window.history.back();</script>";
                    exit();
                }
            } else {
                echo "<script>alert('Ukuran file terlalu besar. Maksimal 2MB!'); window.history.back();</script>";
                exit();
            }
        } else {
            echo "<script>alert('Ekstensi file tidak diperbolehkan!'); window.history.back();</script>";
            exit();
        }
    }

    // 5. Simpan Data ke Tabel Penjualan dengan Status Awal 'Diproses'
    $query_penjualan = "INSERT INTO penjualan 
                        (no_nota, tanggal, tipe_pesanan, total_harga, bayar, kembali, metode_bayar, nama_pelanggan, keterangan_lokasi, bukti_transfer, status_pesanan) 
                        VALUES 
                        ('$no_nota', '$tanggal', '$tipe_pesanan', '$total_harga', '$bayar', '$kembali', '$metode_bayar', '$nama_pelanggan', '$keterangan_lokasi', '$nama_gambar_baru', 'Diproses')";
    
    $insert_penjualan = mysqli_query($con, $query_penjualan);

    // 6. Simpan detail keranjang
    if ($insert_penjualan) {
        foreach ($_SESSION['keranjang'] as $item) {
            $id_menu = $item['id_menu'];
            $harga   = $item['harga'];
            $qty     = $item['qty'];
            $subtotal = $harga * $qty;
            
            $query_detail = "INSERT INTO penjualan_detail (no_nota, id_menu, harga, jumlah, subtotal) 
                             VALUES ('$no_nota', '$id_menu', '$harga', '$qty', '$subtotal')";
            mysqli_query($con, $query_detail);
        }

        // 7. Bersihkan Session Keranjang
        unset($_SESSION['keranjang']);
        
        // 8. Alihkan ke halaman sukses
        header("Location: pesanan_sukses.php?id=" . $no_nota);
        exit();
        
    } else {
        echo "<script>alert('Gagal menyimpan pesanan: " . mysqli_error($con) . "'); window.history.back();</script>";
    }
} else {
    header("Location: menu_pelanggan.php");
    exit();
}
?>