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

// PROSES TAMBAH KATEGORI BARU
if (isset($_POST['simpan_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($con, $_POST['nama_kategori']);
    $deskripsi     = mysqli_real_escape_string($con, $_POST['deskripsi']);

    if (!empty($nama_kategori)) {
        // Cek apakah nama kategori sudah ada
        $cek = mysqli_query($con, "SELECT id FROM kategori_menu WHERE nama_kategori = '$nama_kategori'");
        if (mysqli_num_rows($cek) > 0) {
            echo '<script>alert("Nama kategori sudah ada!"); window.location.href="index.php";</script>';
            exit();
        } else {
            $insert_kat = mysqli_query($con, "INSERT INTO kategori_menu (nama_kategori, deskripsi) VALUES ('$nama_kategori', '$deskripsi')");
            if ($insert_kat) {
                echo '<script>alert("Kategori baru berhasil ditambahkan!"); window.location.href="index.php";</script>';
                exit();
            } else {
                echo '<script>alert("Gagal menambahkan kategori: ' . mysqli_error($con) . '");</script>';
            }
        }
    } else {
        echo '<script>alert("Nama kategori tidak boleh kosong!");</script>';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Data Menu - Resto Project</title>

    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <?php include '../css.php'; ?>
</head>

<body class="theme-red">
    <div class="overlay"></div>

    <?php include '../navbar.php'; ?>

    <section>
        <?php include '../sidebaradmin.php'; ?>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2>MANAJEMEN DATA MENU</h2>
            </div>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                DAFTAR MENU MASAKAN
                                <small>Kelola data menu dan resep di sini</small>
                            </h2>
                            <ul class="header-dropdown m-r--5">
                                <li>
                                    <button type="button" class="btn bg-purple waves-effect" data-toggle="modal" data-target="#modalTambahKategori">
                                        <i class="material-icons">category</i>
                                        <span>TAMBAH KATEGORI</span>
                                    </button>
                                    <a href="tambah.php" class="btn bg-blue waves-effect m-l-5">
                                        <i class="material-icons">add_box</i>
                                        <span>TAMBAH MENU</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="body table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">No.</th>
                                        <th width="40%">Nama Menu</th>
                                        <th width="20%">Harga Jual</th>
                                        <th width="15%" class="text-center">Status</th>
                                        <th width="20%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_menu = mysqli_query($con, "SELECT menu.*, kategori_menu.nama_kategori AS kategori_nama 
                                                                      FROM menu 
                                                                      LEFT JOIN kategori_menu ON menu.kategori_id = kategori_menu.id 
                                                                      ORDER BY menu.nama_menu ASC");
                                    $no = 1;
                                    
                                    while ($data = mysqli_fetch_array($query_menu)) {
                                        $id_row = $no; 
                                        $id_primary = $data['id']; 
                                    ?>
                                    <tr>
                                        <td class="text-center"><?= $no; ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($data['nama_menu']); ?></strong>
                                            <br>
                                            <small class="text-muted" style="font-size: 11px;">
                                                <i class="material-icons" style="font-size: 11px; vertical-align: middle;">label</i> 
                                                Kategori: <?= htmlspecialchars($data['kategori_nama'] ?? 'Tanpa Kategori'); ?>
                                            </small>
                                        </td>
                                        <td>Rp <?= number_format($data['harga'], 0, ',', '.'); ?></td>
                                        <td class="text-center">
                                            <?php if(strtolower($data['status']) == 'tersedia'): ?>
                                                <span class="label bg-green">Tersedia</span>
                                            <?php else: ?>
                                                <span class="label bg-red">Tidak Tersedia</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-success waves-effect" data-toggle="modal" data-target="#modalDetail<?= $id_row; ?>" title="Detail Bahan"><i class="material-icons">visibility</i></button>
                                            <a href="edit.php?id=<?= urlencode($id_primary); ?>" class="btn btn-sm btn-info waves-effect" title="Edit"><i class="material-icons">edit</i></a>
                                            <a href="hapus.php?id=<?= urlencode($id_primary); ?>" class="btn btn-sm btn-danger waves-effect" title="Hapus" onclick="return confirm('Yakin ingin menghapus menu <?= htmlspecialchars($data['nama_menu']); ?>?');"><i class="material-icons">delete</i></a>
                                        </td>
                                    </tr>

                                    <!-- MODAL DETAIL BAHAN -->
                                    <div class="modal fade" id="modalDetail<?= $id_row; ?>" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header bg-green">
                                                    <h4 class="modal-title">Detail Menu: <?= htmlspecialchars($data['nama_menu']); ?></h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Kategori:</strong> <?= htmlspecialchars($data['kategori_nama'] ?? 'Tanpa Kategori'); ?></p>
                                                    <p><strong>Daftar Bahan Masakan:</strong></p>
                                                    <ul>
                                                        <?php
                                                        $query_cek_bahan = mysqli_query($con, "SELECT bahan_baku.nama_bahan, 
                                                                                                      bahan_baku.satuan AS satuan_bahan,
                                                                                                      resep_detail.jumlah_butuh, 
                                                                                                      resep_detail.satuan AS satuan_resep
                                                                                               FROM resep_detail 
                                                                                               JOIN bahan_baku ON resep_detail.bahan_baku_id = bahan_baku.id 
                                                                                               WHERE resep_detail.menu_id = '$id_primary'");
                                                        if(mysqli_num_rows($query_cek_bahan) > 0) {
                                                            while($rb = mysqli_fetch_assoc($query_cek_bahan)) {
                                                                $satuan_db = strtolower(trim($rb['satuan_bahan']));
                                                                
                                                                if ($satuan_db === 'kg') {
                                                                    $tampil_jumlah = $rb['jumlah_butuh'] * 1000;
                                                                    $tampil_satuan = 'gram';
                                                                } elseif ($satuan_db === 'liter' || $satuan_db === 'l') {
                                                                    $tampil_jumlah = $rb['jumlah_butuh'] * 1000;
                                                                    $tampil_satuan = 'ml';
                                                                } else {
                                                                    $tampil_jumlah = $rb['jumlah_butuh'];
                                                                    $tampil_satuan = $rb['satuan_bahan'];
                                                                }

                                                                echo '<li>' . htmlspecialchars($rb['nama_bahan']) . ' — <strong>' . number_format($tampil_jumlah, 2) . ' ' . $tampil_satuan . '</strong></li>';
                                                            }
                                                        } else {
                                                            echo '<i>Belum ada data bahan yang dimasukkan untuk menu ini.</i>';
                                                        }
                                                        ?>
                                                    </ul>
                                                    <hr>
                                                    <p><strong>Harga Jual:</strong> Rp <?= number_format($data['harga'], 0, ',', '.'); ?></p>
                                                    <p><strong>Status:</strong> <?= ucfirst($data['status']); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">TUTUP</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- END MODAL DETAIL -->

                                    <?php 
                                        $no++;
                                    } 
                                    if(mysqli_num_rows($query_menu) == 0){
                                        echo '<tr><td colspan="5" class="text-center">Belum ada data menu.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MEMANGGIL FOOTER -->
            <?php include '../footer.php'; ?>

        </div>
    </section>

    <!-- MODAL TAMBAH KATEGORI BARU -->
    <div class="modal fade" id="modalTambahKategori" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="POST">
                    <div class="modal-header bg-purple">
                        <h4 class="modal-title">Tambah Kategori Menu Baru</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group form-float">
                            <div class="form-line">
                                <input type="text" name="nama_kategori" class="form-control" required>
                                <label class="form-label">Nama Kategori (contoh: Minuman, Dessert, Paket Hemat)</label>
                            </div>
                        </div>
                        <div class="form-group form-float m-t-20">
                            <div class="form-line">
                                <textarea name="deskripsi" rows="3" class="form-control no-resize"></textarea>
                                <label class="form-label">Deskripsi / Keterangan Kategori (Opsional)</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="simpan_kategori" class="btn btn-link waves-effect bg-purple" style="color: white !important;">SIMPAN KATEGORI</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">BATAL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../script.php'; ?>

</body>
</html>