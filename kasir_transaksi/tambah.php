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

// Format No. Nota otomatis: NOTA-YYYYMMDD-0001
$today = date("Ymd");
$query_max = mysqli_query($con, "SELECT MAX(no_nota) as max_nota FROM penjualan WHERE no_nota LIKE 'NOTA-$today-%'");
$data_max = mysqli_fetch_assoc($query_max);
$last_nota =$data_max['max_nota'];

if ($last_nota) {$last_num = (int) substr($last_nota, -4);$next_num = sprintf("%04d", $last_num + 1); } else {$next_num = "0001";
}
$no_nota_auto = "NOTA-" . $today . "-" . $next_num;

// Ambil data menu dari database (menggunakan kolom id)
$data_menu = [];
$query_menu = mysqli_query($con, "SELECT id, nama_menu, harga FROM menu WHERE status = 'tersedia' ORDER BY nama_menu ASC");
if ($query_menu) {
    while ($row = mysqli_fetch_assoc($query_menu)) {
        $data_menu[] =$row;
    }
}

// Proses Simpan Transaksi
if (isset($_POST['simpan_transaksi'])) {
    $no_nota      = mysqli_real_escape_string($con, $_POST['no_nota']);$tanggal      = str_replace('T', ' ', mysqli_real_escape_string($con,$_POST['tanggal']));
    $tipe_pesanan = mysqli_real_escape_string($con, $_POST['tipe_pesanan']);$metode_bayar = mysqli_real_escape_string($con,$_POST['metode_bayar']);
    $total_harga  = floatval($_POST['total_harga']);
    $items        = isset($_POST['items']) && is_array($_POST['items']) ?$_POST['items'] : [];

    if (!empty($items)) {
        mysqli_begin_transaction($con);

        try {
            // Insert ke tabel penjualan
            $stmt_penjualan = mysqli_prepare($con, "INSERT INTO penjualan (no_nota, tanggal, tipe_pesanan, total_harga, metode_bayar) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_penjualan, "sssds", $no_nota, $tanggal,$tipe_pesanan, $total_harga,$metode_bayar);
            
            if (!mysqli_stmt_execute($stmt_penjualan)) {
                throw new Exception(mysqli_stmt_error($stmt_penjualan));
            }
            mysqli_stmt_close($stmt_penjualan);

            // Insert ke tabel penjualan_detail (kolom menu_id)
            $stmt_detail = mysqli_prepare($con, "INSERT INTO penjualan_detail (no_nota, menu_id, qty, harga, subtotal) VALUES (?, ?, ?, ?, ?)");

            foreach ($items as$item) {
                if (!empty($item['menu_id']) && intval($item['qty']) > 0) {$menu_id  = intval($item['menu_id']);$qty      = intval($item['qty']);$harga    = floatval($item['harga']);$subtotal = $qty * $harga;

                    mysqli_stmt_bind_param($stmt_detail, "siidd", $no_nota, $menu_id,$qty, $harga,$subtotal);
                    
                    if (!mysqli_stmt_execute($stmt_detail)) {
                        throw new Exception(mysqli_stmt_error($stmt_detail));
                    }
                }
            }
            mysqli_stmt_close($stmt_detail);

            mysqli_commit($con);
            echo '<script>alert("Transaksi berhasil disimpan!"); window.location.href="index.php";</script>';
            exit();

        } catch (Exception $e) {
            mysqli_rollback($con);
            echo '<script>alert("Gagal menyimpan transaksi: ' . addslashes($e->getMessage()) . '");</script>';
        }
    } else {
        echo '<script>alert("Pilih minimal satu menu!");</script>';
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>TAMBAH TRANSAKSI PENJUALAN - Bakul Sega Bu Lastri</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <?php include '../css.php'; ?>

    <style>
        .table-item th {
            font-weight: bold;
            background-color: #fcfcfc;
        }
        .table-item td, .table-item th {
            vertical-align: middle !important;
        }
        .btn-action-add {
            background-color: #4CAF50;
            color: #fff;
        }
        .btn-action-add:hover {
            background-color: #45a049;
            color: #fff;
        }
        .text-right-input {
            text-align: right;
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
                <a class="navbar-brand" href="../home_kasir/index.php">BAKUL SEGA BU LASTRI - KASIR</a>
            </div>
        </div>
    </nav>

    <!-- Left Sidebar -->
    <section>
        <?php include '../sidebarkasir.php'; ?>
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
                            <form method="POST" action="">
                                <!-- Baris Field Atas -->
                                <div class="row clearfix m-b-20">
                                    <div class="col-md-3">
                                        <label for="no_nota">No. Nota</label>
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="text" id="no_nota" name="no_nota" class="form-control" value="<?= $no_nota_auto; ?>" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="tanggal">Tanggal Transaksi</label>
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="datetime-local" id="tanggal" name="tanggal" class="form-control" value="<?= date('Y-m-d\TH:i'); ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="tipe_pesanan">Tipe Pesanan</label>
                                        <div class="form-group">
                                            <select name="tipe_pesanan" id="tipe_pesanan" class="form-control show-tick" required>
                                                <option value="Ditempat">Ditempat</option>
                                                <option value="Bungkus">Bungkus</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="metode_bayar">Metode Bayar</label>
                                        <div class="form-group">
                                            <select name="metode_bayar" id="metode_bayar" class="form-control show-tick" required>
                                                <option value="Tunai">Tunai</option>
                                                <option value="Transfer">Transfer</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section Tabel Item Menu -->
                                <p><b>Item Menu Penjualan:</b></p>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-item" id="tabel_items">
                                        <thead>
                                            <tr>
                                                <th>Pilih Menu</th>
                                                <th width="180">Harga Satuan (Rp)</th>
                                                <th width="120">Jumlah</th>
                                                <th width="200">Subtotal (Rp)</th>
                                                <th width="60" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody_items">
                                            <!-- Row pertama standar PHP -->
                                            <tr class="item-row">
                                                <td>
                                                    <select class="form-control select-menu" name="items[0][menu_id]" onchange="updateHarga(this)" required>
                                                        <option value="">-- Pilih Menu --</option>
                                                        <?php foreach ($data_menu as$m): ?>
                                                            <option value="<?= $m['id']; ?>" data-harga="<?= $m['harga']; ?>">
                                                                <?= htmlspecialchars($m['nama_menu'], ENT_QUOTES); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control input-harga text-right-input" name="items[0][harga]" value="0" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control input-qty text-right-input" name="items[0][qty]" value="1" min="1" oninput="hitungRow(this)" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control input-subtotal text-right-input" name="items[0][subtotal]" value="0" readonly>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-xs btn-danger waves-effect" onclick="hapusRow(this)">
                                                        <i class="material-icons">delete</i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-right"><b>Total Harga:</b></td>
                                                <td>
                                                    <input type="number" id="input_total_harga" class="form-control text-right-input" name="total_harga" value="0" readonly>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-xs btn-action-add waves-effect" id="btn_tambah_row">
                                                        <i class="material-icons">add</i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Tombol Aksi -->
                                <div class="m-t-20">
                                    <button type="submit" name="simpan_transaksi" class="btn btn-primary waves-effect">Simpan Transaksi</button>
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
        let rowIndex = 1;

        document.getElementById('btn_tambah_row').addEventListener('click', function() {
            const tbody = document.getElementById('tbody_items');
            const tr = document.createElement('tr');
            tr.className = 'item-row';

            // Mengambil innerHTML dari select pertama secara otomatis
            const menuOptions = document.querySelector('.select-menu').innerHTML;

            tr.innerHTML = `
                <td>
                    <select class="form-control select-menu" name="items[${rowIndex}][menu_id]" onchange="updateHarga(this)" required>
                        ${menuOptions}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control input-harga text-right-input" name="items[${rowIndex}][harga]" value="0" readonly>
                </td>
                <td>
                    <input type="number" class="form-control input-qty text-right-input" name="items[${rowIndex}][qty]" value="1" min="1" oninput="hitungRow(this)" required>
                </td>
                <td>
                    <input type="number" class="form-control input-subtotal text-right-input" name="items[${rowIndex}][subtotal]" value="0" readonly>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-xs btn-danger waves-effect" onclick="hapusRow(this)">
                        <i class="material-icons">delete</i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
            rowIndex++;
        });

        function updateHarga(selectEl) {
            const row = selectEl.closest('tr');
            const selectedOption = selectEl.options[selectEl.selectedIndex];
            const harga = parseFloat(selectedOption.getAttribute('data-harga')) || 0;

            row.querySelector('.input-harga').value = harga;
            hitungRow(selectEl);
        }

        function hitungRow(element) {
            const row = element.closest('tr');
            const harga = parseFloat(row.querySelector('.input-harga').value) || 0;
            const qty = parseFloat(row.querySelector('.input-qty').value) || 0;
            const subtotal = harga * qty;

            row.querySelector('.input-subtotal').value = subtotal;
            hitungTotal();
        }

        function hapusRow(btn) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) {
                btn.closest('tr').remove();
                hitungTotal();
            } else {
                alert('Minimal harus ada 1 item menu!');
            }
        }

        function hitungTotal() {
            let total = 0;
            document.querySelectorAll('.input-subtotal').forEach(function(input) {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('input_total_harga').value = total;
        }
    </script>
</body>

</html>