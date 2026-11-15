<?php
session_start();
require_once 'database/koneksi.php';

if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    foreach ($_SESSION['keranjang'] as $key => $item) {
        if ($item['id_menu'] == $id_hapus) {
            unset($_SESSION['keranjang'][$key]);
            break;
        }
    }

    $_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
    header("Location: keranjang.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Keranjang Pesanan - Bakul Sega Bu Lastri</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <!-- AdminBSB CSS -->
    <link href="AdminBSB/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="AdminBSB/plugins/node-waves/waves.css" rel="stylesheet" />
    <link href="AdminBSB/plugins/animate-css/animate.css" rel="stylesheet" />
    <link href="AdminBSB/css/style.css" rel="stylesheet">
    <link href="AdminBSB/css/themes/all-themes.css" rel="stylesheet" />

    <style>
        /* CSS Info Rekening Sidebar Checkout */
        .info-rekening-card {
            background-color: #f9f9f9;
            border-left: 4px solid #F44336; 
            border-radius: 6px;
            padding: 12px 15px;
            margin-top: 10px;
            margin-bottom: 15px;
            border-right: 1px solid #eee;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        .bank-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .bank-item:last-child {
            margin-bottom: 0;
        }
        .bank-icon {
            background-color: #fce4e4;
            color: #F44336;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .bank-details h4 {
            margin: 0 0 2px 0;
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        .bank-details p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }
        .bank-details .rek-number {
            font-family: monospace;
            font-size: 14px;
            color: #d32f2f;
            font-weight: bold;
        }
    </style>
</head>

<body class="theme-red">
    <div class="overlay"></div>

    <?php include 'navbar.php'; ?>

    <section class="content" style="margin-left: 15px; margin-right: 15px; margin-top: 100px;">
        <div class="container-fluid">
            
            <?php if (isset($_SESSION['pesan_sukses'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <?= $_SESSION['pesan_sukses']; ?>
                </div>
                <?php unset($_SESSION['pesan_sukses']); ?>
            <?php endif; ?>

            <div class="row clearfix">
                <!-- Kolom Detail Pesanan -->
                <div class="col-lg-7 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>DETAIL PESANAN SAYA</h2>
                        </div>
                        <div class="body table-responsive">
                            <?php if (empty($_SESSION['keranjang'])): ?>
                                <div class="text-center" style="padding: 30px 0;">
                                    <i class="material-icons" style="font-size: 64px; color: #ccc;">remove_shopping_cart</i>
                                    <p class="m-t-15 font-16">Keranjang pesanan Anda masih kosong.</p>
                                    <a href="menu_pelanggan.php" class="btn btn-primary waves-effect m-t-10">PILIH MENU</a>
                                </div>
                            <?php else: ?>
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Menu</th>
                                            <th class="text-center">Harga</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-right">Subtotal</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total_belanja = 0;
                                        foreach ($_SESSION['keranjang'] as $item): 
                                            $subtotal = $item['harga'] * $item['qty'];
                                            $total_belanja += $subtotal;
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item['nama_menu']); ?></td>
                                                <td class="text-center">Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                                <td class="text-center"><?= $item['qty']; ?></td>
                                                <td class="text-right">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                                <td class="text-center">
                                                    <a href="keranjang.php?hapus=<?= $item['id_menu']; ?>" class="btn btn-danger btn-xs waves-effect" onclick="return confirm('Hapus menu ini dari keranjang?');" title="Hapus">
                                                        <i class="material-icons" style="font-size: 16px;">delete</i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-bold">
                                            <td colspan="3" class="text-right">TOTAL PEMBAYARAN</td>
                                            <td class="text-right col-red" style="font-size: 16px;">Rp <?= number_format($total_belanja, 0, ',', '.'); ?></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                                <a href="menu_pelanggan.php" class="btn btn-default waves-effect m-t-10">TAMBAH MENU LAIN</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Kolom Form Checkout -->
                <?php if (!empty($_SESSION['keranjang'])): ?>
                <div class="col-lg-5 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header bg-red">
                            <h2>INFORMASI PEMESANAN & CHECKOUT</h2>
                        </div>
                        <div class="body">
                            <form action="proses_checkout.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="total_bayar" value="<?= $total_belanja; ?>">

                                <div class="form-group">
                                    <p><b>Tipe Pesanan</b></p>
                                    <input name="tipe_pesanan" type="radio" id="radio_dinein" value="Ditempat" checked />
                                    <label for="radio_dinein">Beli di Tempat </label>
                                    <input name="tipe_pesanan" type="radio" id="radio_takeaway" value="Dikirim" />
                                    <label for="radio_takeaway">Pesan Antar (Delivery)</label>
                                </div>

                                <!-- Form Nama Pelanggan -->
                                <div class="form-group form-float" id="form_nama_pelanggan" style="display: none;">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="nama_pelanggan" id="input_nama_pelanggan">
                                        <label class="form-label">Nama Lengkap</label>
                                    </div>
                                </div>

                                <!-- Form Keterangan Lokasi/Alamat -->
                                <div class="form-group form-float" id="form_keterangan_lokasi" style="display: none;">
                                    <div class="form-line">
                                        <input type="text" class="form-control" name="keterangan_lokasi" id="input_keterangan_lokasi" placeholder="Sertakan Alamat (Jika Delivery)">
                                    </div>
                                </div>

                                <!-- Pilihan Metode Pembayaran -->
                                <div class="form-group">
                                    <p><b>Metode Pembayaran</b></p>
                                    <input name="metode_pembayaran" type="radio" id="radio_transfer" value="Transfer" checked />
                                    <label for="radio_transfer">Transfer Bank</label>
                                    <input name="metode_pembayaran" type="radio" id="radio_tunai" value="Tunai" />
                                    <label for="radio_tunai">Tunai</label>
                                </div>

                                <!-- Form Upload Bukti Transfer (Otomatis Hilang Jika Tunai Dipilih) -->
                                <div class="form-group" id="form_bukti_transfer">
                                    <p><b>Upload Bukti Transfer</b> <small class="col-red">*Wajib jika Transfer</small></p>
                                    
                                    <!-- INFO REKENING PINDAH KESINI -->
                                    <div class="info-rekening-card">
                                        <div class="bank-item">
                                            <div class="bank-icon"><i class="material-icons" style="font-size: 18px;">account_balance</i></div>
                                            <div class="bank-details">
                                                <h4>Bank BCA</h4>
                                                <p class="rek-number">3580437060</p>
                                                <p>A.N Dinda Maria Agatha</p>
                                            </div>
                                        </div>
                                        <div class="bank-item">
                                            <div class="bank-icon"><i class="material-icons" style="font-size: 18px;">account_balance</i></div>
                                            <div class="bank-details">
                                                <h4>Bank BRI</h4>
                                                <p class="rek-number">3685-01-004005-53-0</p>
                                                <p>A.N Sulastri</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <p class="font-12 col-grey" style="margin-top: -5px;">Format: JPG, JPEG, PNG. Maksimal 2MB.<br>Transfer sejumlah <b>Rp <?= number_format($total_belanja, 0, ',', '.'); ?></b> ke salah satu rekening di atas.</p>
                                    <div class="form-line">
                                        <input type="file" class="form-control" name="bukti_transfer" id="input_bukti_transfer" accept="image/jpeg, image/png, image/jpg" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg btn-block waves-effect" style="border-radius: 20px;">
                                    <i class="material-icons" style="font-size: 18px; vertical-align: bottom;">check_circle</i> PROSES CHECKOUT
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
            
            <?php include 'footer.php'; ?>
        </div>
    </section>

    <!-- AdminBSB JS -->
    <script src="AdminBSB/plugins/jquery/jquery.min.js"></script>
    <script src="AdminBSB/plugins/bootstrap/js/bootstrap.js"></script>
    <script src="AdminBSB/plugins/node-waves/waves.js"></script>
    <script src="AdminBSB/js/admin.js"></script>

    <!-- Script Logika Metode Pembayaran dan Tipe Pesanan -->
    <script>
        $(document).ready(function() {
            
            // Logika Tipe Pesanan
            $('input[type=radio][name=tipe_pesanan]').change(function() {
                if (this.value == 'Dikirim') {
                    // Tampilkan form nama dan alamat, jadikan wajib isi
                    $('#form_nama_pelanggan').slideDown();
                    $('#input_nama_pelanggan').prop('required', true);
                    $('#form_keterangan_lokasi').slideDown();
                    $('#input_keterangan_lokasi').prop('required', true);
                }
                else if (this.value == 'Ditempat') {
                    // Sembunyikan form nama dan alamat, hilangkan wajib isi
                    $('#form_nama_pelanggan').slideUp();
                    $('#input_nama_pelanggan').prop('required', false);
                    $('#form_keterangan_lokasi').slideUp();
                    $('#input_keterangan_lokasi').prop('required', false);
                }
            });

            // Logika Metode Pembayaran
            $('input[type=radio][name=metode_pembayaran]').change(function() {
                if (this.value == 'Transfer') {
                    $('#form_bukti_transfer').slideDown();
                    $('#input_bukti_transfer').prop('required', true);
                }
                else if (this.value == 'Tunai') {
                    $('#form_bukti_transfer').slideUp();
                    $('#input_bukti_transfer').prop('required', false);
                    $('#input_bukti_transfer').val('');
                }
            });
        });
    </script>
</body>
</html>