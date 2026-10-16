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

// Opsi daftar menu (Hanya mengambil menu yang statusnya tersedia/aktif)
$query_menu = mysqli_query($con, "SELECT * FROM menu WHERE status = 'tersedia' ORDER BY nama_menu ASC");
$menu_options = [];
if ($query_menu && mysqli_num_rows($query_menu) > 0) {
    while ($menu = mysqli_fetch_assoc($query_menu)) {
        $menu_options[] = $menu;
    }
}

// Generate otomatis No. Nota 
$today = date('Ymd');
$query_nota = mysqli_query($con, "SELECT no_nota FROM penjualan WHERE no_nota LIKE 'NOTA-$today%' ORDER BY no_nota DESC LIMIT 1");
if ($query_nota && mysqli_num_rows($query_nota) > 0) {
    $last_nota = mysqli_fetch_assoc($query_nota)['no_nota'];
    $last_num = (int) substr($last_nota, -4);
    $next_num = str_pad($last_num + 1, 4, '0', STR_PAD_LEFT);
} else {
    $next_num = '0001';
}
$auto_nota = "NOTA-" . $today . "-" . $next_num;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Tambah Penjualan - Bakul Sega Bu Lastri</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <?php include '../css.php'; ?>
    
    <style>
        /* CSS khusus agar dropdown native tampil rapi, pas 100%, dan tidak terpotong */
        .select-menu-plain {
            display: block !important;
            width: 100% !important;
            height: 34px !important;
            padding: 6px 12px !important;
            font-size: 14px !important;
            line-height: 1.42857143 !important;
            color: #555 !important;
            background-color: #fff !important;
            border: 1px solid #ccc !important;
            border-radius: 4px !important;
            box-shadow: inset 0 1px 1px rgba(0,0,0,.075) !important;
            outline: none !important;
        }
        /* Membunuh pembungkus bawaan plugin bootstrap-select jika ada */
        .bootstrap-select.select-menu-plain {
            display: none !important;
        }
        #tableMenu td {
            vertical-align: middle;
        }
    </style>
</head>

<body class="theme-red">
    <!-- Top Bar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
                <a href="javascript:void(0);" class="bars"></a>
                <a class="navbar-brand" href="../home_admin/index.php">BAKUL SEGA BU LASTRI - ADMIN</a>
            </div>
        </div>
    </nav>

    <!-- Left Sidebar -->
    <section>
        <?php include '../sidebaradmin.php'; ?>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>TAMBAH TRANSAKSI PENJUALAN</h2>
                        </div>
                        <div class="body">
                            <form action="simpan.php" method="POST" id="formPenjualan">
                                <div class="row clearfix">
                                    <div class="col-md-4">
                                        <label for="no_nota">No. Nota</label>
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="text" id="no_nota" name="no_nota" class="form-control" value="<?= htmlspecialchars($auto_nota); ?>" readonly required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="tanggal">Tanggal Transaksi</label>
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="datetime-local" id="tanggal" name="tanggal" class="form-control" value="<?= date('Y-m-d\TH:i'); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="tipe_pesanan">Tipe Pesanan</label>
                                        <div class="form-group">
                                            <div class="form-line">
                                                <select name="tipe_pesanan" id="tipe_pesanan" class="form-control" required>
                                                    <option value="Ditempat">Ditempat</option>
                                                    <option value="Dikirim">Dikirim</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="metode_bayar">Metode Bayar</label>
                                        <div class="form-group">
                                            <div class="form-line">
                                                <select name="metode_bayar" id="metode_bayar" class="form-control" required>
                                                    <option value="Tunai">Tunai</option>
                                                    <option value="Transfer">Transfer</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h5 class="m-t-20">Item Menu Penjualan:</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="tableMenu">
                                        <thead>
                                            <tr>
                                                <th style="min-width: 250px;">Pilih Menu</th>
                                                <th width="150">Harga Satuan (Rp)</th>
                                                <th width="120">Jumlah</th>
                                                <th width="180">Subtotal (Rp)</th>
                                                <th width="60">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="menuContainer">
                                            <tr class="menu-row">
                                                <td>
                                                    <select name="id_menu[]" class="select-menu-plain" required>
                                                        <option value="">-- Pilih Menu --</option>
                                                        <?php foreach ($menu_options as $menu) : 
                                                            $id_item = isset($menu['id_menu']) ? $menu['id_menu'] : (isset($menu['id']) ? $menu['id'] : '');
                                                            $val_harga = isset($menu['harga']) ? $menu['harga'] : (isset($menu['harga_jual']) ? $menu['harga_jual'] : 0);
                                                        ?>
                                                            <option value="<?= htmlspecialchars($id_item); ?>" data-harga="<?= $val_harga; ?>">
                                                                <?= htmlspecialchars($menu['nama_menu']); ?> - Rp <?= number_format($val_harga, 0, ',', '.'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="form-line">
                                                        <input type="number" name="harga_satuan[]" class="form-control harga-satuan" readonly value="0">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-line">
                                                        <input type="number" name="jumlah[]" class="form-control jumlah-input" min="1" value="1" required>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-line">
                                                        <input type="number" name="subtotal[]" class="form-control subtotal-input" readonly value="0">
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-xs btn-danger waves-effect btn-hapus">
                                                        <i class="material-icons">delete</i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-right"><strong>Total Harga:</strong></td>
                                                <td>
                                                    <div class="form-line">
                                                        <input type="number" id="total_harga" name="total_harga" class="form-control" readonly value="0">
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-xs btn-success waves-effect btn-tambah">
                                                        <i class="material-icons">add</i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="m-t-20">
                                    <button type="submit" class="btn btn-primary waves-effect">Simpan Transaksi</button>
                                    <a href="index.php" class="btn btn-default waves-effect">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php include '../footer.php'; ?>
        </div>
    </section>
    
    <?php include '../script.php'; ?>

    <script>
    $(document).ready(function() {
        // Matikan inisialisasi plugin selectpicker jika sempat dijalankan oleh script.php
        if ($.fn.selectpicker) {
            $('.select-menu-plain').selectpicker('destroy');
        }

        // Event listener saat opsi menu dipilih
        $(document).on('change', '.select-menu-plain', function() {
            var $selected = $(this).find('option:selected');
            var harga = parseFloat($selected.attr('data-harga')) || parseFloat($selected.data('harga')) || 0;
            
            var $row = $(this).closest('tr');
            $row.find('.harga-satuan').val(harga);
            hitungSubtotal($row);
        });

        $(document).on('input', '.jumlah-input', function() {
            var $row = $(this).closest('tr');
            hitungSubtotal($row);
        });

        function hitungSubtotal($row) {
            var harga = parseFloat($row.find('.harga-satuan').val()) || 0;
            var jumlah = parseFloat($row.find('.jumlah-input').val()) || 0;
            var subtotal = harga * jumlah;
            
            $row.find('.subtotal-input').val(subtotal);
            hitungTotalAkhir();
        }

        function hitungTotalAkhir() {
            var total = 0;
            $('.subtotal-input').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#total_harga').val(total);
        }

        // Tambah Baris
        $(document).on('click', '.btn-tambah', function() {
            var $container = $('#menuContainer');
            var $firstRow = $container.find('.menu-row').first();
            var $newRow = $firstRow.clone();
            
            $newRow.find('.select-menu-plain').val('');
            $newRow.find('.harga-satuan').val('0');
            $newRow.find('.jumlah-input').val('1');
            $newRow.find('.subtotal-input').val('0');
            
            $container.append($newRow);
        });

        // Hapus Baris
        $(document).on('click', '.btn-hapus', function() {
            var rowCount = $('.menu-row').length;
            if (rowCount > 1) {
                $(this).closest('tr').remove();
                hitungTotalAkhir();
            } else {
                alert('Minimal harus ada 1 item menu untuk transaksi.');
            }
        });
    });
    </script>
</body>

</html>