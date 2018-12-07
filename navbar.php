<!-- Navbar -->
<body class="hold-transition sidebar-mini">
<div class="wrapper">
<nav class="main-header navbar navbar-expand bg-white navbar-light border-bottom">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fa fa-bars"></i></a>
          </li>
        </ul>
      </nav>
      <!-- /.navbar -->
  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="#" class="brand-link">
          <span class="brand-text font-weight-light">InventarioTEC</span>
        </a>
    
        <!-- Sidebar -->
        <div class="sidebar">
          <!-- Sidebar user (optional) -->
          <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
              <a href="#" class="d-block">Bienvenido <?= $_SESSION["user"]["nombre"] ?></a>
            </div>
          </div>
    
          <!-- Sidebar Menu -->
          <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
              <!-- Add icons to the links using the .nav-icon class
                   with font-awesome or any other icon font library -->
              <li class="nav-item">
                <a id="articulo" class="nav-link item-nav">
                  <p>
                    Inventario
                  </p>
                </a>
              </li>
              <?php if ($_SESSION["user"]["rol"] == 1): ?>
              <li class="nav-item">
                <a id="users" class="nav-link item-nav">
                  <p>
                    Alumnos
                  </p>
                </a>
              </li>
              <?php endif; ?>
              <li class="nav-item">
                <a id="historial" class="nav-link item-nav">
                  <p>
                    Movimientos
                  </p>
                </a>
              </li>
            </ul>
          </nav>
          <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
      </aside>
    