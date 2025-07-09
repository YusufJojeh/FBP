<?php
// Vendor dashboard page 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$vendor_id = $_SESSION['user_id'];
$total_services = $pdo->prepare("SELECT COUNT(*) FROM services WHERE vendor_id = ?");
$total_services->execute([$vendor_id]);
$total_services = $total_services->fetchColumn();
$total_orders = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE vendor_id = ?");
$total_orders->execute([$vendor_id]);
$total_orders = $total_orders->fetchColumn();
$orders_in_progress = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE vendor_id = ? AND status = 'in_progress'");
$orders_in_progress->execute([$vendor_id]);
$orders_in_progress = $orders_in_progress->fetchColumn();
$orders_delivered = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE vendor_id = ? AND status = 'delivered'");
$orders_delivered->execute([$vendor_id]);
$orders_delivered = $orders_delivered->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vendor Dashboard</title>
  <!-- AdminLTE CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="dashboard.php" class="nav-link">Home</a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
      <span class="brand-text font-weight-light">DesignHub Vendor</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="dashboard.php" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="services.php" class="nav-link">
              <i class="nav-icon fas fa-briefcase"></i>
              <p>My Services</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="add_service.php" class="nav-link">
              <i class="nav-icon fas fa-plus-circle"></i>
              <p>Add Service</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="update_order.php" class="nav-link">
              <i class="nav-icon fas fa-tasks"></i>
              <p>Orders</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="profile.php" class="nav-link">
              <i class="nav-icon fas fa-user"></i>
              <p>Profile</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="../logout.php" class="nav-link">
              <i class="nav-icon fas fa-sign-out-alt"></i>
              <p>Logout</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Vendor Dashboard</h1>
          </div>
        </div>
      </div>
    </div>
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= $total_services ?></h3>
                <p>My Services</p>
              </div>
              <div class="icon"><i class="fas fa-briefcase"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= $total_orders ?></h3>
                <p>Total Orders</p>
              </div>
              <div class="icon"><i class="fas fa-tasks"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?= $orders_in_progress ?></h3>
                <p>Orders In Progress</p>
              </div>
              <div class="icon"><i class="fas fa-spinner"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= $orders_delivered ?></h3>
                <p>Orders Delivered</p>
              </div>
              <div class="icon"><i class="fas fa-check"></i></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
<!-- AdminLTE JS -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html> 