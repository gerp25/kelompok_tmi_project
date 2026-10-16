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

$id_penjualan = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_penjualan <= 0) {
    echo '<script>alert("ID Transaksi tidak valid."); window.location.href="index.php";</script>';
    exit();
}

// Ambil data utama penjualan
$query_penjualan = mysqli_query($con, "SELECT * FROM penjualan WHERE id_penjualan = $id_penjualan");
$data_penjualan = mysqli_fetch_assoc($query_penjualan);

if (!$data_penjualan) {
    echo '<script>alert("Data transaksi tidak ditemukan."); window.location.href="index.php";</script>';
    exit();
}

$no_nota = mysqli_real_escape_string($con, $data_penjualan['no_nota']);

// Ambil data detail item penjualan
$query_detail = mysqli_query($con, "SELECT dp.*, m.nama_menu FROM penjualan_detail dp JOIN menu m ON dp.id_menu = m.id WHERE dp.no_nota = '$no_nota'");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Nota - <?= htmlspecialchars($data_penjualan['no_nota']); ?></title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            width: 58mm;
            margin: 0 auto;
            padding: 4mm 2mm;
            color: #000;
            background-color: #fff;
            line-height: 1.2;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .line {
            border-bottom: 1px dashed #000;
            margin: 4px 0;
        }
        table {
            width: 100%;
            font-size: 11px;
            border-collapse: collapse;
        }
        td {
            padding: 1px 0;
            vertical-align: top;
        }
        .item-row td {
            padding-bottom: 2px;
        }
        .no-print {
            margin-top: 15px;
            text-align: center;
        }
        @media print {
            body {
                width: 48mm;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="text-center">
        <span class="bold" style="font-size: 12px;">BAKUL SEGA BU LASTRI</span><br>
        <span>Bumiayu, Brebes</span><br>
        <span>Telp: 08xxxxxxxxxx</span>
    </div>
    
    <div class="line"></div>
    
    <table style="font-size: 10px;">
        <tr>
            <td style="width: 28%;">No Nota</td>
            <td style="width: 4%;">:</td>
            <td style="width: 68%;"><?= htmlspecialchars($data_penjualan['no_nota']); ?></td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td><?= date('d-m-Y H:i', strtotime($data_penjualan['tanggal'])); ?></td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td><?= htmlspecialchars($_SESSION['nama'] ?? 'Kasir'); ?></td>
        </tr>
        <tr>
            <td>Tipe</td>
            <td>:</td>
            <td><?= htmlspecialchars($data_penjualan['tipe_pesanan'] ?? '-'); ?></td>
        </tr>
    </table>
    
    <div class="line"></div>
    
    <table>
        <?php
        if ($query_detail && mysqli_num_rows($query_detail) > 0) {
            while ($row = mysqli_fetch_assoc($query_detail)) {
        ?>
        <tr class="item-row">
            <td colspan="2" class="bold"><?= htmlspecialchars($row['nama_menu']); ?></td>
        </tr>
        <tr>
            <td style="width: 55%;"><?= $row['jumlah']; ?> x <?= number_format($row['harga'], 0, ',', '.'); ?></td>
            <td style="width: 45%;" class="text-right">Rp <?= number_format($row['subtotal'], 0, ',', '.'); ?></td>
        </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='2' class='text-center'>Tidak ada item</td></tr>";
        }
        ?>
    </table>
    
    <div class="line"></div>
    
    <table>
        <tr>
            <td class="bold">TOTAL</td>
            <td class="text-right bold">Rp <?= number_format($data_penjualan['total_harga'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>Bayar (<?= htmlspecialchars($data_penjualan['metode_bayar']); ?>)</td>
            <td class="text-right">Rp <?= number_format($data_penjualan['total_harga'], 0, ',', '.'); ?></td>
        </tr>
    </table>
    
    <div class="line"></div>
    
    <div class="text-center" style="font-size: 10px;">
        <span>Terima Kasih Atas Kunjungan Anda</span><br>
        <span>Silakan Datang Kembali</span>
    </div>

    <!-- Tombol navigasi jika dicancel/tidak otomatis cetak -->
    <div class="no-print">
        <button onclick="window.print()" style="padding: 5px 10px; cursor: pointer; font-size: 11px;">Cetak Ulang</button>
        <br><br>
        <a href="index.php" style="text-decoration: none; color: #000; background: #ddd; padding: 3px 8px; border-radius: 3px; font-size: 11px;">Kembali</a>
    </div>
</body>

</html>