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

// Ambil nomor nota dari parameter GET
$no_nota = isset($_GET['nota']) ? $_GET['nota'] : '';

if (empty($no_nota)) {
    echo '<script>alert("Nomor nota tidak valid.");</script>';
    echo '<script>window.close();</script>';
    exit();
}

// Ambil data utama penjualan
$query_penjualan = mysqli_query($con, "SELECT * FROM penjualan WHERE no_nota = '" . mysqli_real_escape_string($con, $no_nota) . "'");
$penjualan = mysqli_fetch_assoc($query_penjualan);

if (!$penjualan) {
    echo '<script>alert("Data transaksi tidak ditemukan.");</script>';
    echo '<script>window.close();</script>';
    exit();
}

// Ambil detail transaksi (join dengan tabel menu menggunakan m.id)
$query_detail = mysqli_query($con, "SELECT pd.*, m.nama_menu FROM penjualan_detail pd JOIN menu m ON pd.id_menu = m.id WHERE pd.no_nota = '" . mysqli_real_escape_string($con, $no_nota) . "'");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struk - <?= htmlspecialchars($penjualan['no_nota']); ?></title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0; /* Menghilangkan header/footer bawaan browser (tanggal, URL, dll) */
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 4px;
        }

        .receipt-container {
            width: 100%;
            max-width: 58mm;
            margin: 0 auto;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .line {
            border-bottom: 1px dashed #000;
            margin: 4px 0;
        }

        .row-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2px;
        }

        .no-print {
            margin-bottom: 10px;
        }

        @media print {
            .no-print {
                display: none;
            }
            body {
                padding: 2px;
            }
        }
    </style>
</head>

<body>

    <!-- Tombol aksi (hanya tampil di layar) -->
    <div class="text-center no-print">
        <button onclick="window.print()" style="padding: 5px 10px; cursor: pointer;">Cetak Struk</button>
        <button onclick="window.close()" style="padding: 5px 10px; cursor: pointer;">Tutup</button>
    </div>

    <div class="receipt-container">
        <!-- Header Struk -->
        <div class="text-center">
            <span class="bold" style="font-size: 12px;">BAKUL SEGA BU LASTRI</span><br>
            <span>Jl. Raya Utama Resto</span><br>
            <span>Telp: 0812-3456-7890</span>
        </div>

        <div class="line"></div>

        <!-- Info Nota -->
        <div class="row-flex">
            <span>Nota</span>
            <span class="bold"><?= htmlspecialchars($penjualan['no_nota']); ?></span>
        </div>
        <div class="row-flex">
            <span>Tgl</span>
            <span><?= date('d/m/y H:i', strtotime($penjualan['tanggal'])); ?></span>
        </div>
        <div class="row-flex">
            <span>Tipe</span>
            <span><?= htmlspecialchars($penjualan['tipe_pesanan'] ?? '-'); ?></span>
        </div>
        <div class="row-flex">
            <span>Kasir</span>
            <span><?= htmlspecialchars($_SESSION['username'] ?? 'Kasir'); ?></span>
        </div>

        <div class="line"></div>

        <!-- Daftar Item -->
        <?php
        if ($query_detail && mysqli_num_rows($query_detail) > 0) {
            while ($detail = mysqli_fetch_assoc($query_detail)) {
                $harga = isset($detail['harga']) ? $detail['harga'] : 0;
                $jumlah = isset($detail['jumlah']) ? $detail['jumlah'] : (isset($detail['qty']) ? $detail['qty'] : 0);
                $subtotal = isset($detail['subtotal']) ? $detail['subtotal'] : ($harga * $jumlah);
        ?>
                <div class="bold"><?= htmlspecialchars($detail['nama_menu']); ?></div>
                <div class="row-flex" style="padding-left: 5px;">
                    <span><?= $jumlah; ?> x <?= number_format($harga, 0, ',', '.'); ?></span>
                    <span><?= number_format($subtotal, 0, ',', '.'); ?></span>
                </div>
        <?php
            }
        }
        ?>

        <div class="line"></div>

        <!-- Total Pembayaran -->
        <div class="row-flex bold" style="font-size: 11px;">
            <span>TOTAL</span>
            <span>Rp <?= number_format($penjualan['total_harga'], 0, ',', '.'); ?></span>
        </div>
        <div class="row-flex">
            <span>Bayar (<?= htmlspecialchars($penjualan['metode_bayar']); ?>)</span>
            <span>Rp <?= number_format($penjualan['total_harga'], 0, ',', '.'); ?></span>
        </div>

        <div class="line"></div>

        <!-- Footer Struk -->
        <div class="text-center" style="margin-top: 6px;">
            <span>*** TERIMA KASIH ***</span><br>
            <span>Selamat Menikmati</span>
        </div>
    </div>

    <!-- Script Otomatis Panggil Dialog Print -->
    <script>
        window.onload = function() {
            window.print();
        }
    </script>

</body>

</html>