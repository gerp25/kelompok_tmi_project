<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/koneksi.php';

$authority = $_SESSION['peran'] ?? '';
$is_2fa_passed = $_SESSION['is_2fa_passed'] ?? false;

if (($authority !== 'K' && $authority !== 'Kasir') || $is_2fa_passed !== true) {
    echo '<script>alert("Akses ditolak. Silakan login kembali.");</script>';
    echo '<script>window.location.href = "../logout.php";</script>';
    exit();
}

// Format No. Nota otomatis: NOTA-YYYYMMDD-0001
$today = date("Ymd");
$no_nota_pattern = "NOTA-" . $today . "-%";
$stmt_max = mysqli_prepare($con, "SELECT MAX(no_nota) AS max_nota FROM penjualan WHERE no_nota LIKE ?");
$last_nota = null;

if ($stmt_max) {
    mysqli_stmt_bind_param($stmt_max, "s", $no_nota_pattern);
    mysqli_stmt_execute($stmt_max);
    $result_max = mysqli_stmt_get_result($stmt_max);
    if ($result_max) {
        $data_max = mysqli_fetch_assoc($result_max);
        $last_nota = $data_max['max_nota'] ?? null;
    }
    mysqli_stmt_close($stmt_max);
}

if ($last_nota) {
    $last_num = (int) substr($last_nota, -4);
    $next_num = sprintf("%04d", $last_num + 1);
} else {
    $next_num = "0001";
}
$no_nota_auto = "NOTA-" . $today . "-" . $next_num;

// Ambil data menu yang tersedia dari database
$data_menu = [];
$harga_menu_map = [];
$query_menu = mysqli_query($con, "SELECT id, nama_menu, harga FROM menu WHERE status = 'tersedia' ORDER BY nama_menu ASC");
if ($query_menu) {
    while ($row = mysqli_fetch_assoc($query_menu)) {
        $menu_id_int = (int) $row['id'];
        $menu_harga_float = (float) $row['harga'];
        $data_menu[] = [
            'id' => $menu_id_int,
            'nama_menu' => $row['nama_menu'],
            'harga' => $menu_harga_float
        ];
        $harga_menu_map[$menu_id_int] = $menu_harga_float;
    }
}

if (isset($_POST['simpan_transaksi'])) {
    $no_nota = trim($_POST['no_nota'] ?? '');
    $tanggal_raw = trim($_POST['tanggal'] ?? '');
    $tanggal = str_replace('T', ' ', $tanggal_raw);
    if (strlen($tanggal) === 16) {
        $tanggal .= ':00';
    }
    $tipe_pesanan = trim($_POST['tipe_pesanan'] ?? 'Ditempat');
    $metode_bayar = trim($_POST['metode_bayar'] ?? 'Tunai');
    $items = (isset($_POST['items']) && is_array($_POST['items'])) ? $_POST['items'] : [];

    // Filter item yang valid
    $valid_items = [];
    $calculated_total_harga = 0.0;

    foreach ($items as $item) {
        $menu_id = isset($item['menu_id']) ? (int) $item['menu_id'] : 0;
        $qty = isset($item['qty']) ? (int) $item['qty'] : 0;

        if ($menu_id > 0 && $qty > 0 && isset($harga_menu_map[$menu_id])) {
            $harga_satuan = $harga_menu_map[$menu_id];
            $subtotal = $qty * $harga_satuan;
            $calculated_total_harga += $subtotal;

            $valid_items[] = [
                'menu_id' => $menu_id,
                'qty' => $qty,
                'harga' => $harga_satuan,
                'subtotal' => $subtotal
            ];
        }
    }

    if (!empty($valid_items)) {
        mysqli_begin_transaction($con);

        try {
            // Insert ke tabel penjualan
            $stmt_penjualan = mysqli_prepare(
                $con,
                "INSERT INTO penjualan (no_nota, tanggal, tipe_pesanan, total_harga, metode_bayar) VALUES (?, ?, ?, ?, ?)"
            );
            if (!$stmt_penjualan) {
                throw new Exception(mysqli_error($con));
            }

            mysqli_stmt_bind_param(
                $stmt_penjualan,
                "sssds",
                $no_nota,
                $tanggal,
                $tipe_pesanan,
                $calculated_total_harga,
                $metode_bayar
            );

            if (!mysqli_stmt_execute($stmt_penjualan)) {
                throw new Exception(mysqli_stmt_error($stmt_penjualan));
            }
            mysqli_stmt_close($stmt_penjualan);

            // Insert ke tabel penjualan_detail
            $stmt_detail = mysqli_prepare(
                $con,
                "INSERT INTO penjualan_detail (no_nota, menu_id, qty, harga, subtotal) VALUES (?, ?, ?, ?, ?)"
            );
            if (!$stmt_detail) {
                throw new Exception(mysqli_error($con));
            }

            foreach ($valid_items as $valid_item) {
                mysqli_stmt_bind_param(
                    $stmt_detail,
                    "siidd",
                    $no_nota,
                    $valid_item['menu_id'],
                    $valid_item['qty'],
                    $valid_item['harga'],
                    $valid_item['subtotal']
                );

                if (!mysqli_stmt_execute($stmt_detail)) {
                    throw new Exception(mysqli_stmt_error($stmt_detail));
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
        echo '<script>alert("Pilih minimal satu menu yang tersedia dengan jumlah minimal 1!");</script>';
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
        @media (min-width: 768px) {
            .table-responsive {
                overflow: visible;
            }
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
                            <form method="POST" action="" id="form_transaksi">
                                <!-- Baris Field Atas -->
                                <div class="row clearfix m-b-20">
                                    <div class="col-md-3">
                                        <label for="no_nota">No. Nota</label>
                                        <div class="form-group">
                                            <div class="form-line">
                                                <input type="text" id="no_nota" name="no_nota" class="form-control" value="<?= htmlspecialchars($no_nota_auto, ENT_QUOTES); ?>" readonly>
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
                                            <tr class="item-row">
                                                <td>
                                                    <select class="form-control select-menu" name="items[0][menu_id]" required>
                                                        <option value="">-- Pilih Menu --</option>
                                                        <?php foreach ($data_menu as $menu_item): ?>
                                                            <option value="<?= $menu_item['id']; ?>" data-harga="<?= $menu_item['harga']; ?>">
                                                                <?= htmlspecialchars($menu_item['nama_menu'], ENT_QUOTES); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control input-harga text-right-input" name="items[0][harga]" value="0" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control input-qty text-right-input" name="items[0][qty]" value="1" min="1" required>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control input-subtotal text-right-input" name="items[0][subtotal]" value="0" readonly>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-xs btn-danger waves-effect btn-hapus-row">
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
        (function() {
            let rowIndex = 1;

            const menuOptionsHtml = `
                <option value="">-- Pilih Menu --</option>
                <?php foreach ($data_menu as $menu_item): ?>
                    <option value="<?= $menu_item['id']; ?>" data-harga="<?= $menu_item['harga']; ?>">
                        <?= htmlspecialchars($menu_item['nama_menu'], ENT_QUOTES); ?>
                    </option>
                <?php endforeach; ?>
            `;

            function initSelectPicker(targetContainer) {
                if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.selectpicker === 'function') {
                    window.jQuery(targetContainer).find('select.select-menu').selectpicker('render');
                }
            }

            function updateHargaDariSelect(selectElement) {
                const row = selectElement.closest('tr');
                if (!row) {
                    return;
                }

                const selectedOption = selectElement.options[selectElement.selectedIndex];
                const harga = selectedOption ? (parseFloat(selectedOption.getAttribute('data-harga')) || 0) : 0;

                row.querySelector('.input-harga').value = harga;
                hitungRow(row);
            }

            function hitungRow(rowElement) {
                if (!rowElement) {
                    return;
                }

                const harga = parseFloat(rowElement.querySelector('.input-harga').value) || 0;
                const qty = parseFloat(rowElement.querySelector('.input-qty').value) || 0;
                const subtotal = harga * qty;

                rowElement.querySelector('.input-subtotal').value = subtotal;
                hitungTotal();
            }

            function hitungTotal() {
                let total = 0;
                document.querySelectorAll('.input-subtotal').forEach(function(subtotalInput) {
                    total += parseFloat(subtotalInput.value) || 0;
                });
                document.getElementById('input_total_harga').value = total;
            }

            document.getElementById('btn_tambah_row').addEventListener('click', function() {
                const tbody = document.getElementById('tbody_items');
                const newRow = document.createElement('tr');
                newRow.className = 'item-row';

                newRow.innerHTML = `
                    <td>
                        <select class="form-control select-menu" name="items[${rowIndex}][menu_id]" required>
                            ${menuOptionsHtml}
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control input-harga text-right-input" name="items[${rowIndex}][harga]" value="0" readonly>
                    </td>
                    <td>
                        <input type="number" class="form-control input-qty text-right-input" name="items[${rowIndex}][qty]" value="1" min="1" required>
                    </td>
                    <td>
                        <input type="number" class="form-control input-subtotal text-right-input" name="items[${rowIndex}][subtotal]" value="0" readonly>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-xs btn-danger waves-effect btn-hapus-row">
                            <i class="material-icons">delete</i>
                        </button>
                    </td>
                `;

                tbody.appendChild(newRow);
                initSelectPicker(newRow);
                rowIndex++;
            });

            document.getElementById('tbody_items').addEventListener('change', function(event) {
                if (event.target && event.target.classList.contains('select-menu')) {
                    updateHargaDariSelect(event.target);
                }
            });

            document.getElementById('tbody_items').addEventListener('input', function(event) {
                if (event.target && event.target.classList.contains('input-qty')) {
                    hitungRow(event.target.closest('tr'));
                }
            });

            document.getElementById('tbody_items').addEventListener('click', function(event) {
                const hapusButton = event.target.closest('.btn-hapus-row');
                if (!hapusButton) {
                    return;
                }

                const rows = document.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    hapusButton.closest('tr').remove();
                    hitungTotal();
                } else {
                    alert('Minimal harus ada 1 item menu!');
                }
            });

            if (typeof window.jQuery !== 'undefined') {
                window.jQuery(document).ready(function() {
                    initSelectPicker(document);
                    hitungTotal();
                });
            } else {
                hitungTotal();
            }
        })();
    </script>
</body>

</html>