<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';
require_once '../AdminBSB/fpdf/fpdf.php'; 

$authority = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];

if (($authority != 'A' && $authority != 'Admin') || $is_2fa_passed !== true) {
    echo '<script>alert("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

$no_nota = isset($_GET['nota']) ? mysqli_real_escape_string($con, $_GET['nota']) : '';

if (empty($no_nota)) {
    echo "Nomor nota tidak ditemukan.";
    exit();
}

// Ambil data utama penjualan
$query_penjualan = mysqli_query($con, "SELECT * FROM penjualan WHERE no_nota = '$no_nota'");
$penjualan = mysqli_fetch_assoc($query_penjualan);

if (!$penjualan) {
    echo "Data transaksi tidak ditemukan.";
    exit();
}

// Ambil detail barang yang dibeli
$query_detail = mysqli_query($con, "SELECT penjualan_detail.*, menu.nama_menu FROM penjualan_detail 
LEFT JOIN menu ON penjualan_detail.id_menu = menu.id WHERE penjualan_detail.no_nota = '$no_nota'");

// Hitung estimasi tinggi halaman secara dinamis
$jumlah_item = mysqli_num_rows($query_detail);
$tinggi_dasar = 85; 
$tinggi_per_item = 7; 
$total_tinggi = $tinggi_dasar + ($jumlah_item * $tinggi_per_item);

// Inisialisasi FPDF: Lebar Kertas 58mm
$pdf = new FPDF('P', 'mm', array(58, $total_tinggi));

// Margin Kiri=2mm, Atas=2mm, Kanan=2mm. Lebar cetak aktif = 40mm (Sangat aman untuk printer 58mm)
$pdf->SetMargins(2, 2, 2); 
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

// Gunakan font Courier (monospaced)
$pdf->SetFont('Courier', 'B', 8.5);

// --- HEADER RESTO ---
$pdf->Cell(40, 3.5, 'BAKUL SEGA BU LASTRI', 0, 1, 'C');
$pdf->SetFont('Courier', '', 6.5);
$pdf->Cell(40, 2.5, 'Krajan 2, Jatisawit, Bumiayu', 0, 1, 'C');
$pdf->Cell(40, 2.5, '(Gang Sinar Motor Bumiayu)', 0, 1, 'C');
$pdf->Cell(40, 2.5, 'Telp: 0857-2165-7146', 0, 1, 'C');

// Garis Pembatas (28 Karakter)
$pdf->Cell(40, 2, '----------------------------', 0, 1, 'C');

// --- INFORMASI TRANSAKSI ---
$pdf->SetFont('Courier', '', 6.5);

$pdf->Cell(14, 2.5, 'No. Nota', 0, 0, 'L');
$pdf->Cell(2, 2.5, ':', 0, 0, 'C');
$pdf->Cell(24, 2.5, $penjualan['no_nota'], 0, 1, 'R');

$pdf->Cell(14, 2.5, 'Tanggal', 0, 0, 'L');
$pdf->Cell(2, 2.5, ':', 0, 0, 'C');
$pdf->Cell(24, 2.5, date('d/m/y H:i', strtotime($penjualan['tanggal'])), 0, 1, 'R');

$pdf->Cell(14, 2.5, 'Tipe', 0, 0, 'L');
$pdf->Cell(2, 2.5, ':', 0, 0, 'C');
$pdf->Cell(24, 2.5, !empty($penjualan['tipe_pesanan']) ? $penjualan['tipe_pesanan'] : '-', 0, 1, 'R');

$pdf->Cell(14, 2.5, 'Bayar Via', 0, 0, 'L');
$pdf->Cell(2, 2.5, ':', 0, 0, 'C');
$pdf->Cell(24, 2.5, $penjualan['metode_bayar'], 0, 1, 'R');

// Garis Pembatas
$pdf->Cell(40, 2, '----------------------------', 0, 1, 'C');

// --- ITEM PENJUALAN ---
if ($query_detail && $jumlah_item > 0) {
    while ($item = mysqli_fetch_assoc($query_detail)) {
        $nama_item = !empty($item['nama_menu']) ? $item['nama_menu'] : (isset($item['nama_barang']) ? $item['nama_barang'] : 'Menu');
        $subtotal = $item['jumlah'] * $item['harga'];

        // Baris 1: Nama Item
        $pdf->SetFont('Courier', 'B', 7);
        $pdf->Cell(40, 3, $nama_item, 0, 1, 'L');

        // Baris 2: Qty x Harga & Subtotal
        $pdf->SetFont('Courier', '', 6.5);
        $rincian = $item['jumlah'] . ' x ' . number_format($item['harga'], 0, ',', '.');
        $pdf->Cell(20, 2.5, $rincian, 0, 0, 'L');
        $pdf->Cell(20, 2.5, number_format($subtotal, 0, ',', '.'), 0, 1, 'R');
    }
} else {
    $pdf->SetFont('Courier', '', 6.5);
    $pdf->Cell(40, 2.5, 'Detail tidak tersedia', 0, 1, 'C');
}

// Garis Pembatas
$pdf->Cell(40, 2, '----------------------------', 0, 1, 'C');

// --- TOTAL PEMBAYARAN ---
$pdf->SetFont('Courier', 'B', 7.5);
$pdf->Cell(18, 3, 'TOTAL', 0, 0, 'L');
$pdf->Cell(22, 3, 'Rp ' . number_format($penjualan['total_harga'], 0, ',', '.'), 0, 1, 'R');

if (isset($penjualan['bayar']) && isset($penjualan['kembali'])) {
    $pdf->SetFont('Courier', '', 6.5);
    $pdf->Cell(18, 2.5, 'BAYAR', 0, 0, 'L');
    $pdf->Cell(22, 2.5, 'Rp ' . number_format($penjualan['bayar'], 0, ',', '.'), 0, 1, 'R');

    $pdf->Cell(18, 2.5, 'KEMBALI', 0, 0, 'L');
    $pdf->Cell(22, 2.5, 'Rp ' . number_format($penjualan['kembali'], 0, ',', '.'), 0, 1, 'R');
}

// Garis Pembatas
$pdf->Cell(40, 2, '----------------------------', 0, 1, 'C');

// --- FOOTER ---
$pdf->SetFont('Courier', '', 6.5);
$pdf->Cell(40, 2.5, '-- Terima Kasih --', 0, 1, 'C');
$pdf->Cell(40, 2.5, 'Selamat Menikmati', 0, 1, 'C');

// Output PDF
$pdf->Output('I', 'Struk-' . $penjualan['no_nota'] . '.pdf');
?>