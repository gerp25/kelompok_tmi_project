<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

$authority = @$_SESSION['peran'];
$is_2fa_passed = @$_SESSION['is_2fa_passed'];

if (($authority != 'A' && $authority != 'Admin') || $is_2fa_passed !== true) {
    echo '<script>alert ("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href ="../logout.php";</script>';
    exit();
}

// Inisialisasi Filter Tanggal
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d');

// 1. Hitung Total Penjualan (Pendapatan)
$q_tot_penjualan = mysqli_query($con, "SELECT SUM(total_harga) AS total FROM penjualan WHERE DATE(tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'");
$d_tot_penjualan = mysqli_fetch_assoc($q_tot_penjualan);
$total_penjualan = $d_tot_penjualan['total'] ? $d_tot_penjualan['total'] : 0;

// 2. Hitung Total Pembelian (Pengeluaran)
$q_tot_pembelian = mysqli_query($con, "SELECT SUM(total_harga) AS total FROM pembelian WHERE DATE(tgl_pembelian) BETWEEN '$tgl_mulai' AND '$tgl_selesai'");
$d_tot_pembelian = mysqli_fetch_assoc($q_tot_pembelian);
$total_pembelian = $d_tot_pembelian['total'] ? $d_tot_pembelian['total'] : 0;

// 3. Hitung Laba/Rugi (Penjualan - Pembelian)
$laba_rugi = $total_penjualan - $total_pembelian;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Laporan Laba Rugi - BAKUL SEGA BU LASTRI</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <?php include '../css.php'; ?>

    <style>
        /* CSS Scroll Sidebar AdminBSB di Layar Biasa */
        section.sidebar, .sidebar .menu {
            height: calc(100vh - 70px) !important;
            overflow-y: auto !important;
        }

        /* Elemen Khusus Cetak Sembunyi di Layar */
        @media screen {
            .print-only {
                display: none !important;
            }
        }

        /* CSS Khusus Mode Cetak / PDF */
        @page {
            size: A4 portrait;
            margin: 0; /* Menghilangkan Header & Footer Bawaan Browser */
        }

        @media print {
            /* Sembunyikan Komponen Web Navigasi */
            nav, .navbar, aside, #leftsidebar, .sidebar, section.sidebar,
            .user-info, .legal, .overlay, .no-print, .block-header, form, footer {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            body {
                background: #fff !important;
                color: #000 !important;
                margin: 0 !important;
                padding: 1.2cm !important;
                font-family: Arial, sans-serif !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            section.content, .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }

            .card {
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .card .body {
                padding: 0 !important;
            }

            /* Style KOP Laporan berbasis Tabel (Sangat Presisi & Rapi) */
            .table-kop {
                width: 100% !important;
                border-collapse: collapse !important;
                border: none !important;
                margin-bottom: 5px !important;
            }

            .table-kop td {
                border: none !important;
                padding: 0 !important;
                vertical-align: middle !important;
            }

            /* Style Tabel Resmi Laporan */
            .table-report {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 15px !important;
                font-size: 13px !important;
            }

            .table-report th, .table-report td {
                border: 1px solid #333 !important;
                padding: 10px 12px !important;
            }

            .table-report th {
                background-color: #f2f2f2 !important;
                text-transform: uppercase;
                font-weight: bold;
                letter-spacing: 0.5px;
            }
        }
    </style>
</head>

<body class="theme-red">
    <div class="overlay"></div>

    <?php include '../navbar.php'; ?>

    <section>
        <?php include '../sidebaradmin.php'; ?>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="block-header no-print">
                <h2>LAPORAN LABA RUGI</h2>
            </div>

            <!-- Form Filter Tanggal (Tidak Dicetak) -->
            <div class="row clearfix no-print">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="header">
                            <h2>FILTER PERIODE LAPORAN</h2>
                        </div>
                        <div class="body">
                            <form method="GET" action="" class="row clearfix">
                                <div class="col-md-4">
                                    <b>Dari Tanggal</b>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="material-icons">date_range</i></span>
                                        <div class="form-line">
                                            <input type="date" name="tgl_mulai" class="form-control" value="<?= $tgl_mulai; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <b>Sampai Tanggal</b>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="material-icons">date_range</i></span>
                                        <div class="form-line">
                                            <input type="date" name="tgl_selesai" class="form-control" value="<?= $tgl_selesai; ?>" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4" style="margin-top: 20px;">
                                    <button type="submit" class="btn btn-primary waves-effect">
                                        <i class="material-icons">filter_list</i> <span>FILTER</span>
                                    </button>
                                    <button type="button" onclick="window.print()" class="btn btn-success waves-effect">
                                        <i class="material-icons">print</i> <span>CETAK</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOP Laporan Resmi (Hanya Muncul Saat Cetak) -->
            <div class="print-only">
                <table class="table-kop">
                    <tr>
                        <td width="80" style="text-align: left;">
                            <img src="../image/logo_resto.jpg" alt="Logo Resto" style="width: 75px; height: auto; display: block;">
                        </td>
                        <td style="text-align: center; padding-right: 75px;">
                            <h2 style="margin: 0; font-size: 20px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #000;">BAKUL SEGA BU LASTRI</h2>
                            <p style="margin: 4px 0 0 0; font-size: 12px; color: #333;">Laporan Keuangan & Operasional Resto</p>
                        </td>
                    </tr>
                </table>

                <!-- Garis Kop Ganda Standar Laporan -->
                <div style="border-bottom: 2px solid #000; border-top: 1px solid #000; height: 2px; margin: 12px 0 20px 0;"></div>

                <div style="text-align: center; margin-bottom: 25px;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: bold; text-transform: uppercase;">LAPORAN LABA RUGI</h3>
                    <p style="margin: 5px 0 0 0; font-size: 13px;">Periode: <b><?= date('d/m/Y', strtotime($tgl_mulai)); ?></b> s/d <b><?= date('d/m/Y', strtotime($tgl_selesai)); ?></b></p>
                </div>
            </div>

            <!-- Tabel Utama Laporan -->
            <div class="row clearfix">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="card">
                        <div class="header no-print">
                            <h2>PERHITUNGAN LABA / RUGI</h2>
                        </div>
                        <div class="body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-report">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left;">Komponen Kategori</th>
                                            <th width="35%" style="text-align: right;">Jumlah (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><b>(+) Total Penjualan (Pendapatan)</b></td>
                                            <td style="text-align: right; color: green; font-weight: bold;">
                                                Rp <?= number_format($total_penjualan, 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><b>(-) Total Pembelian (Pengeluaran / Beban)</b></td>
                                            <td style="text-align: right; color: red; font-weight: bold;">
                                                (Rp <?= number_format($total_pembelian, 0, ',', '.'); ?>)
                                            </td>
                                        </tr>
                                        <tr style="background-color: #f9f9f9; font-size: 15px;">
                                            <td><b>HASIL <?= ($laba_rugi >= 0) ? 'LABA BERSIH (+)' : 'RUGI BERSIH (-)'; ?></b></td>
                                            <td style="text-align: right; font-weight: bold; color: <?= ($laba_rugi >= 0) ? 'blue' : 'red'; ?>;">
                                                <?php
                                                if ($laba_rugi < 0) {
                                                    echo '(Rp ' . number_format(abs($laba_rugi), 0, ',', '.') . ')';
                                                } else {
                                                    echo 'Rp ' . number_format($laba_rugi, 0, ',', '.');
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blok Tanda Tangan Cetak (Hanya Muncul Saat Cetak) -->
            <div class="print-only" style="margin-top: 40px; width: 100%;">
                <div style="float: right; width: 220px; text-align: center; font-size: 13px;">
                    <p style="margin-bottom: 60px;">
                        Dicetak Tanggal: <?= date('d/m/Y'); ?><br>
                        <b>Admin / Penanggung Jawab</b>
                    </p>
                    <p style="margin: 0; font-weight: bold; text-decoration: underline;">
                        <?= isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator'; ?>
                    </p>
                </div>
                <div style="clear: both;"></div>
            </div>

            <div class="no-print">
                <?php include '../footer.php'; ?>
            </div>
        </div>
    </section>

    <?php include '../script.php'; ?>

</body>
</html>