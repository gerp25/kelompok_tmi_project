<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../database/koneksi.php";

if (isset($_POST['login'])) {
    $username = trim(mysqli_real_escape_string($con, $_POST['username']));
    $password = sha1(trim(mysqli_real_escape_string($con, $_POST['password'])));

    $sql_login = mysqli_query($con, "SELECT * FROM tbl_user WHERE username = '$username' AND sandi = '$password'") or die(mysqli_error($con));

    if (mysqli_num_rows($sql_login) > 0) {
        $datanya = mysqli_fetch_assoc($sql_login);
        
        // Simpan data sementara ke Session
        $_SESSION['username'] = $username;
        $_SESSION['peran']    = $datanya['peran'];
        $_SESSION['pin']      = $datanya['pin'];

        // ALAHKAN KE HALAMAN 2FA VERIFIKASI PIN DAHULU
        header("Location: ../login2fa/");
        exit();
    } else {
        $login_gagal = true;
    }
}
?>

<!DOCTYPE html>
<html class="no-js" lang="id">

<head>
  <meta charset="utf-8">
  <title>BAKUL SEGA BU LASTRI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  
  <link rel="stylesheet" href="styles/app.min.css"/>
  <link rel="shortcut icon" href="../image/logo_resto.jpg">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>

<body>

  <div class="app signin v2 usersession">
    <div class="session-wrapper">
      
      <div class="session-carousel slide" data-ride="carousel" data-interval="3000">
        <div class="carousel-inner" role="listbox">
          <div class="item active" style="background-image:url(../image/depan_warung.jpeg);background-size:cover;background-repeat: no-repeat;background-position: 50% 50%;"></div>
          <div class="item" style="background-image:url(../image/nasi_kuning.jpeg);background-size:cover;background-repeat: no-repeat;background-position: 50% 50%;"></div>
          <div class="item" style="background-image:url(../image/nasi_kotak.jpeg);background-size:cover;background-repeat: no-repeat;background-position: 50% 50%;"></div>
          <div class="item" style="background-image:url(../image/tampilan_lauk.jpeg);background-size:cover;background-repeat: no-repeat;background-position: 50% 50%;"></div> 
        </div>
      </div>

      <div class="card bg-white login-panel-card">
        <div class="card-block">
          <form role="form" class="form-layout" action="" method="post">
            
            <div class="text-center m-b">    
              <img src="../image/logo_resto.jpg" class="logo-img" style="width:80px; height:80px; object-fit:contain;" alt="Logo Resto"/> 
              <h4 class="text-uppercase" style="margin-top: 10px;"><b><font color="#000000">BAKUL SEGA BU LASTRI</font></b></h4>
              <h5 class="text-uppercase"><font color="#666666">- KULINER KHAS -</font></h5>
            </div>

            <div class="form-inputs p-b">
              <label class="text-uppercase"><font color="#000000">USERNAME / EMAIL</font></label>
              <input type="text" class="form-control input-lg" name="username" id="username" placeholder="Username / Email" required>
              
              <label class="text-uppercase" style="margin-top: 15px;"><font color="#000000">PASSWORD</font></label>
              <input type="password" class="form-control input-lg" name="password" id="password" placeholder="Password" required>
            </div>
              
            <button class="btn btn-danger btn-block btn-lg" type="submit" name="login" style="background-color:#bb0a1e; margin-top: 20px;">
              <font color="#ffffff"><b>Masuk</b></font>
            </button>
        

            <div class="text-center">
              <img src="../image/font_logo.png" class="footer-logo" style="width:180px; height:auto; object-fit:contain;" alt="Resto Logo"/>
              <p style="margin-top: 5px;">
                <font color="#000000"><small><em>Copyright &copy; Bakul Sega Bu Lastri <?php echo date("Y"); ?></em></small></font>
              </p>
            </div>
          </form>

        </div>
      </div>

    </div>
  </div>

  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div class="modal-header" style="background-color:#042673;">
          <button type="button" class="close" data-dismiss="modal" style="color:white;">&times;</button>
          <h4 class="modal-title"><font color="#ffffff">TERJADI KESALAHAN!</font></h4>
        </div>
        <div class="modal-body">
          <h5><b>Mohon maaf, terjadi kesalahan...</b></h5>
          <p>Username atau Password yang Anda masukkan salah. Silakan coba lagi.</p>
        </div>
        <div class="modal-footer" style="background-color:#f6d106;">
          <button type="button" class="btn btn-default" data-dismiss="modal"><b>TUTUP</b></button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

  <!-- Modalkan kesalahan hanya jika login gagal -->
  <?php if (isset($login_gagal) && $login_gagal): ?>
    <script>
      $(document).ready(function(){
        $('#myModal').modal('show');
      });
    </script>
  <?php endif; ?>

</body>
</html>