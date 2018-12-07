<?php
readfile("header.html");
?>
  <!-- DataTables -->
  <link rel="stylesheet" href="public/js/plugins/datetimepicker/jquery.datetimepicker.min.css">
  <link rel="stylesheet" href="public/js/plugins/datatables/dataTables.bootstrap4.css">


  </head>
<?php
include_once("navbar.php");
?>
<div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="formModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="formModalTitle">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
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
<script src="https://cdn.datatables.net/buttons/1.5.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.flash.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/1.5.1/js/buttons.colVis.min.js"></script>
<script src="public/js/plugins/datetimepicker/jquery.datetimepicker.full.min.js"></script>

<?php readfile("adminScripts.html") ?>
<script>
  let userRole = 1;
$(document).ready(function(){
  clickNavLink();
  createModal();
  loadCardSections();
  $(document).on("mouseover mouseout", ".datetimepicker", function(){
    $(this).datetimepicker()
});
  
});
</script>
</body>
</html>