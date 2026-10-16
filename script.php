<!-- AdminBSB Core & Essential Plugins -->
<script src="../AdminBSB/plugins/jquery/jquery.min.js"></script>
<script src="../AdminBSB/plugins/bootstrap/js/bootstrap.js"></script>
<script src="../AdminBSB/plugins/node-waves/waves.js"></script>
<script src="../AdminBSB/plugins/jquery-slimscroll/jquery.slimscroll.js"></script>

<!-- Bootstrap Select Plugin -->
<script src="../AdminBSB/plugins/bootstrap-select/js/bootstrap-select.js"></script>

<!-- AdminBSB Core JS -->
<script src="../AdminBSB/js/admin.js"></script>

<!-- DataTables Plugin -->
<script src="../assets/plugins/datatables/jquery.dataTables.js"></script>
<script src="../assets/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>

<!-- Helper Script Global -->
<script>
  // Inisialisasi DataTable standar jika ada tabel
  $(function () {
    if ($.fn.DataTable) {
      if ($("#cobatabel").length) {
        $("#cobatabel").DataTable();
      }
      if ($("#example2").length) {
        $('#example2').DataTable({
          "paging": true,
          "lengthChange": true,
          "searching": true,
          "ordering": true,
          "info": true,
          "autoWidth": true,
        });
      }
    }
  });

  // Validasi Input Hanya Angka
  function isNumberKey(evt) {
    var charCode = (evt.which) ? evt.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57))
      return false;
    return true;
  }
</script>