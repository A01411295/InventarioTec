<?php
readfile("header.html");
?>
  <!-- DataTables -->
  <link rel="stylesheet" href="public/js/plugins/datatables/dataTables.bootstrap4.css">
  </head>
<?php
include_once("navbar.php");
?>
<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="formModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div id="formModalBody">
      </div>
    </div>
  </div>
</div>
<div class="content-wrapper">
        <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 id="title"></h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <section class="content" id="cards-content">

    </section>
    <!-- /.content -->

<?php
readfile("footer.html");
readfile("scripts.html");
?>
<!-- DataTables -->
<script src="public/js/plugins/datatables/jquery.dataTables.js"></script>
<script src="public/js/plugins/datatables/dataTables.bootstrap4.js"></script>
<?php readfile("alumnoScripts.html") ?>
<script>
    let userRole = 0;
$(document).ready(function(){
  clickNavLink();
  createModal();
  loadCardSections();
  
});
</script>
</body>
</html>