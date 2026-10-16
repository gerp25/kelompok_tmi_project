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

// ngambil id menu dari url
$id_menu = isset($_GET['id']) ? mysqli_real_escape_string($con, $_GET['id']) : '';

if (empty($id_menu)) {
    echo '<script>alert("ID Menu tidak valid!"); window.location.href="../admin_kategori_menu/index.php";</script>';
    exit();
}

// ngambil data menu utama
$query_menu = mysqli_query($con, "SELECT * FROM menu WHERE id = '$id_menu'");
$menu = mysqli_fetch_assoc($query_menu);

if (!$menu) {
    echo '<script>alert("Data menu tidak ditemukan!"); window.location.href="../admin_kategori_menu/index.php";</script>';
    exit();
}

// Proses Hapus Bahan Baku dari Resep
if (isset($_GET['hapus_resep_id'])) {
    $id_resep_hapus = mysqli_real_escape_string($con, $_GET['hapus_resep_id']);
    $query_hapus = mysqli_query($con, "DELETE FROM resep_detail WHERE id = '$id_resep_hapus' AND menu_id = '$id_menu'");
    
    if ($query_hapus) {
        echo '<script>alert("Bahan baku berhasil dihapus dari resep!"); window.location.href="edit.php?id=' . $id_menu . '";</script>';
        exit();
    } else {
        $error = "Gagal menghapus bahan dari resep: " . mysqli_error($con);
    }
}

// Proses Tambah Bahan Baku yang Sudah Ada ke Resep dari Modal
if (isset($_POST['tambah_bahan_ke_resep'])) {
    $bahan_baku_id  = mysqli_real_escape_string($con, $_POST['bahan_baku_id']);
    $jumlah_input   = floatval($_POST['jumlah_butuh']);

    if (!empty($bahan_baku_id) && !empty($jumlah_input)) {
        // Ambil satuan bahan baku dari tabel master
        $q_bb = mysqli_query($con, "SELECT satuan FROM bahan_baku WHERE id = '$bahan_baku_id'");
        $d_bb = mysqli_fetch_assoc($q_bb);
        $satuan = $d_bb['satuan'];
        $satuan_lower = strtolower(trim($satuan));

        // Konversi dari Gram/ML ke KG/Liter jika satuan dasar di DB adalah kg/liter
        if ($satuan_lower === 'kg' || $satuan_lower === 'liter' || $satuan_lower === 'l') {
            $jumlah_simpan = $jumlah_input / 1000;
        } else {
            $jumlah_simpan = $jumlah_input;
        }

        // Cek apakah bahan sudah ada di resep ini
        $cek_resep = mysqli_query($con, "SELECT id FROM resep_detail WHERE menu_id = '$id_menu' AND bahan_baku_id = '$bahan_baku_id'");
        if (mysqli_num_rows($cek_resep) > 0) {
            $insert_rd = mysqli_query($con, "UPDATE resep_detail SET jumlah_butuh = '$jumlah_simpan', satuan = '$satuan' WHERE menu_id = '$id_menu' AND bahan_baku_id = '$bahan_baku_id'");
        } else {
            $insert_rd = mysqli_query($con, "INSERT INTO resep_detail (menu_id, bahan_baku_id, jumlah_butuh, satuan) VALUES ('$id_menu', '$bahan_baku_id', '$jumlah_simpan', '$satuan')");
        }

        if ($insert_rd) {
            echo '<script>alert("Bahan baku berhasil ditambahkan ke resep!"); window.location.href="edit.php?id=' . $id_menu . '";</script>';
            exit();
        } else {
            $error = "Gagal menambah bahan ke resep: " . mysqli_error($con);
        }
    } else {
        $error = "Pilih bahan baku dan isi jumlah kebutuhan!";
    }
}

// PROSES TAMBAH BAHAN BAKU BARU & LANGSUNG MASUK KE RESEP
if (isset($_POST['tambah_bahan_baru_dan_resep'])) {
    $nama_bahan   = mysqli_real_escape_string($con, $_POST['nama_bahan']);
    $stok         = mysqli_real_escape_string($con, $_POST['stok']);
    $satuan       = mysqli_real_escape_string($con, $_POST['satuan']);
    $jumlah_input = floatval($_POST['jumlah_butuh']);

    if (!empty($nama_bahan) && isset($stok) && !empty($satuan) && !empty($jumlah_input)) {
        // 1. Simpan bahan baru ke tabel master bahan_baku
        $insert_bb = mysqli_query($con, "INSERT INTO bahan_baku (nama_bahan, stok, satuan) VALUES ('$nama_bahan', '$stok', '$satuan')");
        
        if ($insert_bb) {
            // Ambil ID bahan baku yang baru saja dimasukkan
            $bahan_baku_id_baru = mysqli_insert_id($con);

            $satuan_lower = strtolower(trim($satuan));
            // Konversi jika inputan berbasis gram/ml ke kg/liter
            if ($satuan_lower === 'kg' || $satuan_lower === 'liter' || $satuan_lower === 'l') {
                $jumlah_simpan = $jumlah_input / 1000;
            } else {
                $jumlah_simpan = $jumlah_input;
            }

            // 2. Masukkan ke tabel resep_detail
            $insert_rd = mysqli_query($con, "INSERT INTO resep_detail (menu_id, bahan_baku_id, jumlah_butuh, satuan) VALUES ('$id_menu', '$bahan_baku_id_baru', '$jumlah_simpan', '$satuan')");

            if ($insert_rd) {
                echo '<script>alert("Bahan baku baru berhasil dibuat dan ditambahkan ke resep!"); window.location.href="edit.php?id=' . $id_menu . '";</script>';
                exit();
            } else {
                $error = "Gagal menambahkan bahan baru ke resep: " . mysqli_error($con);
            }
        } else {
            $error = "Gagal menyimpan bahan baku baru ke database: " . mysqli_error($con);
        }
    } else {
        $error = "Semua kolom pada form bahan baku baru wajib diisi!";
    }
}

// PROSES UPDATE MENU & GAMBAR
if (isset($_POST['update'])) {
    $nama_menu   = mysqli_real_escape_string($con, $_POST['nama_menu']);
    $deskripsi   = mysqli_real_escape_string($con, $_POST['deskripsi']);
    $harga_raw   = str_replace('.', '', $_POST['harga']);
    $harga       = mysqli_real_escape_string($con, $harga_raw);
    $kategori_id = mysqli_real_escape_string($con, $_POST['kategori_id']); 
    $status      = mysqli_real_escape_string($con, $_POST['status']);
    $updated_at  = date('Y-m-d H:i:s');

    $gambar_nama = $menu['gambar']; // Default menggunakan gambar lama

    // Penanganan Upload Gambar
    if (isset($_FILES['gambar']['name']) && $_FILES['gambar']['name'] != '') {
        $filename   = $_FILES['gambar']['name'];
        $filesize   = $_FILES['gambar']['size'];
        $filetmp    = $_FILES['gambar']['tmp_name'];
        $ext_valid  = array('png', 'jpg', 'jpeg', 'webp');
        $ext_file   = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext_file, $ext_valid)) {
            if ($filesize <= 2097152) { // Maksimal 2MB
                $gambar_baru = time() . '_' . uniqid() . '.' . $ext_file;
                $target_dir  = "../image/";

                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                if (move_uploaded_file($filetmp, $target_dir . $gambar_baru)) {
                    if (!empty($menu['gambar']) && file_exists($target_dir . $menu['gambar'])) {
                        unlink($target_dir . $menu['gambar']);
                    }
                    $gambar_nama = $gambar_baru;
                } else {
                    $error = "Gagal mengunggah file gambar ke server.";
                }
            } else {
                $error = "Ukuran gambar terlalu besar! Maksimal 2MB.";
            }
        } else {
            $error = "Format file gambar tidak valid! Gunakan JPG, JPEG, PNG, atau WEBP.";
        }
    }

    if (!isset($error)) {
        // 1. Update tabel menu
        $update_menu = mysqli_query($con, "UPDATE menu SET 
                            nama_menu = '$nama_menu', 
                            deskripsi = '$deskripsi',
                            harga = '$harga', 
                            kategori_id = '$kategori_id', 
                            status = '$status', 
                            gambar = '$gambar_nama', 
                            updated_at = '$updated_at' 
                            WHERE id = '$id_menu'");

        if ($update_menu) {
            // 2. Update takaran bahan yang ada pada tabel
            if (isset($_POST['jumlah']) && is_array($_POST['jumlah'])) {
                foreach ($_POST['jumlah'] as $resep_id => $jumlah_val) {
                    $resep_id_clean = mysqli_real_escape_string($con, $resep_id);
                    $jumlah_input   = floatval($jumlah_val);

                    // Ambil data bahan baku untuk cek satuan
                    $q_sat = mysqli_query($con, "
                        SELECT bahan_baku.satuan 
                        FROM resep_detail 
                        JOIN bahan_baku ON resep_detail.bahan_baku_id = bahan_baku.id 
                        WHERE resep_detail.id = '$resep_id_clean'
                    ");
                    if ($q_sat && mysqli_num_rows($q_sat) > 0) {
                        $d_sat = mysqli_fetch_assoc($q_sat);
                        $satuan_db = strtolower(trim($d_sat['satuan']));

                        if ($satuan_db === 'kg' || $satuan_db === 'liter' || $satuan_db === 'l') {
                            $jumlah_simpan = $jumlah_input / 1000;
                        } else {
                            $jumlah_simpan = $jumlah_input;
                        }

                        mysqli_query($con, "UPDATE resep_detail SET jumlah_butuh = '$jumlah_simpan' WHERE id = '$resep_id_clean'");
                    }
                }
            }

            // Redirect ke halaman Daftar Menu
            echo '<script>alert("Data menu dan resep berhasil diperbarui!"); window.location.href="../admin_kategori_menu/index.php";</script>';
            exit();
        } else {
            $error = "Gagal memperbarui menu: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Edit Menu & Resep - Resto Project</title>

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
                <h2>MANAJEMEN DATA MENU & RESEP</h2>
            </div>

            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>
                                EDIT MENU: <?= htmlspecialchars($menu['nama_menu']); ?>
                                <small>Ubah informasi menu beserta takaran bahan bakunya</small>
                            </h2>
                        </div>
                        <div class="body">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= $error; ?></div>
                            <?php endif; ?>

                            <form action="" method="POST" enctype="multipart/form-data">
                                <h4 class="card-inside-title">1. Informasi Utama Menu</h4>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group form-float">
                                            <div class="form-line">
                                                <input type="text" name="nama_menu" class="form-control" value="<?= htmlspecialchars($menu['nama_menu']); ?>" required>
                                                <label class="form-label">Nama Menu</label>
                                            </div>
                                        </div>

                                        <div class="form-group form-float">
                                            <div class="form-line focused">
                                                <textarea name="deskripsi" rows="3" class="form-control no-resize"><?= htmlspecialchars($menu['deskripsi'] ?? ''); ?></textarea>
                                                <label class="form-label">Deskripsi Menu</label>
                                            </div>
                                        </div>

                                        <div class="form-group form-float">
                                            <div class="form-line">
                                                <input type="text" id="hargaInput" name="harga" class="form-control" value="<?= number_format($menu['harga'], 0, ',', '.'); ?>" required>
                                                <label class="form-label">Harga (Rp)</label>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Kategori Menu</label>
                                            <select name="kategori_id" class="form-control show-tick" required>
                                                <option value="">-- Pilih Kategori --</option>
                                                <?php
                                                $query_kategori = mysqli_query($con, "SELECT * FROM kategori_menu ORDER BY nama_kategori ASC");
                                                while ($kategori = mysqli_fetch_assoc($query_kategori)) {
                                                    $selected = ($kategori['id'] == $menu['kategori_id']) ? 'selected' : '';
                                                    echo '<option value="' . $kategori['id'] . '" ' . $selected . '>' . htmlspecialchars($kategori['nama_kategori']) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Status Menu</label>
                                            <select name="status" class="form-control show-tick">
                                                <option value="tersedia" <?= ($menu['status'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                                <option value="Tidak Tersedia" <?= ($menu['status'] == 'Tidak Tersedia') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Foto / Gambar Menu</label>
                                            <input type="file" name="gambar" id="inputGambar" class="form-control" accept="image/*">
                                            <small class="text-muted">* Format yang diperbolehkan: JPG, JPEG, PNG, WEBP. Maksimal 2MB. Kosongkan jika tidak ingin mengubah gambar.</small>
                                        </div>
                                    </div>

                                    <div class="col-md-4 text-center">
                                        <label style="display: block; text-align: center;">Preview Gambar</label>
                                        <?php 
                                        $gambar_path = "../image/" . $menu['gambar'];
                                        $src_gambar = (!empty($menu['gambar']) && file_exists($gambar_path)) ? $gambar_path : "https://via.placeholder.com/200?text=No+Image";
                                        ?>
                                        <img id="imgPreview" src="<?= $src_gambar; ?>" alt="Gambar Menu" class="img-thumbnail" style="max-height: 220px; object-fit: cover; width: 100%;">
                                    </div>
                                </div>

                                <button type="submit" name="update" class="btn btn-primary m-t-15 waves-effect">
                                    <i class="material-icons">save</i>
                                    <span>SIMPAN MENU</span>
                                </button>
                                <a href="../admin_kategori_menu/index.php" class="btn btn-default m-t-15 waves-effect">
                                    <i class="material-icons">arrow_back</i>
                                    <span>KEMBALI</span>
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- MODAL 1: PILIH BAHAN BAKU YANG SUDAH ADA -->
    <div class="modal fade" id="modalTambahBahan" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="POST">
                    <div class="modal-header bg-cyan">
                        <h4 class="modal-title">Pilih & Tambah Bahan Baku yang Ada</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Pilih Bahan Baku</label>
                            <select name="bahan_baku_id" class="form-control show-tick" required>
                                <option value="">-- Pilih Bahan Baku --</option>
                                <?php
                                $query_semua_bahan = mysqli_query($con, "SELECT * FROM bahan_baku ORDER BY nama_bahan ASC");
                                while ($bb = mysqli_fetch_assoc($query_semua_bahan)) {
                                    echo '<option value="' . $bb['id'] . '">' . htmlspecialchars($bb['nama_bahan']) . ' (Stok: ' . $bb['stok'] . ' ' . $bb['satuan'] . ')</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group form-float m-t-20">
                            <div class="form-line">
                                <input type="number" step="0.01" name="jumlah_butuh" class="form-control" required placeholder="Isi takaran (contoh: 50 untuk 50 gram)">
                                <label class="form-label">Jumlah Kebutuhan Takaran</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="tambah_bahan_ke_resep" class="btn btn-link waves-effect bg-cyan" style="color: white !important;">TAMBAH KE RESEP</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">BATAL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: TAMBAH BAHAN BAKU BARU -->
    <div class="modal fade" id="modalTambahBahanBaru" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" method="POST">
                    <div class="modal-header bg-green">
                        <h4 class="modal-title">Buat & Tambah Bahan Baku Baru</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group form-float">
                            <div class="form-line">
                                <input type="text" name="nama_bahan" class="form-control" required>
                                <label class="form-label">Nama Bahan Baku Baru</label>
                            </div>
                        </div>
                        <div class="form-group form-float">
                            <div class="form-line">
                                <input type="number" step="0.01" name="stok" class="form-control" required>
                                <label class="form-label">Stok Awal</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Satuan Stok Utama</label>
                            <select name="satuan" class="form-control show-tick" required>
                                <option value="">-- Pilih Satuan --</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="gram">Gram (gram)</option>
                                <option value="liter">Liter (liter)</option>
                                <option value="ml">Mililiter (ml)</option>
                                <option value="pcs">Pcs (pcs)</option>
                                <option value="bungkus">Bungkus</option>
                                <option value="ikat">Ikat</option>
                            </select>
                        </div>
                        <div class="form-group form-float m-t-15">
                            <div class="form-line">
                                <input type="number" step="0.01" name="jumlah_butuh" class="form-control" required placeholder="Isi takaran (contoh: 50 untuk 50 gram)">
                                <label class="form-label">Jumlah Kebutuhan untuk Resep Ini</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="tambah_bahan_baru_dan_resep" class="btn btn-link waves-effect bg-green" style="color: white !important;">SIMPAN & TAMBAHKAN</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">BATAL</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../script.php'; ?>

    <script>
        $(document).ready(function(){
            // Live filter pencarian bahan
            $("#searchBahan").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#tabelBahan tbody tr.bahan-row").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            // Auto Format Titik pada Input Harga
            $('#hargaInput').on('keyup input', function() {
                var value = $(this).val().replace(/[^0-9]/g, '');
                if (value !== '') {
                    $(this).val(parseInt(value, 10).toLocaleString('id-ID'));
                } else {
                    $(this).val('');
                }
            });

            // Live Preview Gambar
            $("#inputGambar").change(function() {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imgPreview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
    </script>
</body>
</html>