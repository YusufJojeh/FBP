<?php
// Client dashboard page 
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$client_id = $_SESSION['user_id'];
$total_bookings = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE client_id = ?");
$total_bookings->execute([$client_id]);
$total_bookings = $total_bookings->fetchColumn();
$bookings_in_progress = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE client_id = ? AND status = 'in_progress'");
$bookings_in_progress->execute([$client_id]);
$bookings_in_progress = $bookings_in_progress->fetchColumn();
$bookings_delivered = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE client_id = ? AND status = 'delivered'");
$bookings_delivered->execute([$client_id]);
$bookings_delivered = $bookings_delivered->fetchColumn();
$total_spent = $pdo->prepare("SELECT SUM(s.price) FROM bookings b JOIN services s ON b.service_id = s.id WHERE b.client_id = ? AND b.status = 'delivered'");
$total_spent->execute([$client_id]);
$total_spent = $total_spent->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Client Dashboard</title>
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
      <span class="brand-text font-weight-light">DesignHub Client</span>
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
              <i class="nav-icon fas fa-search"></i>
              <p>Browse Services</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="bookings.php" class="nav-link">
              <i class="nav-icon fas fa-calendar-check"></i>
              <p>My Bookings</p>
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
            <h1 class="m-0">Client Dashboard</h1>
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
                <h3><?= $total_bookings ?></h3>
                <p>Total Bookings</p>
              </div>
              <div class="icon"><i class="fas fa-calendar-check"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
              <div class="inner">
                <h3><?= $bookings_in_progress ?></h3>
                <p>Bookings In Progress</p>
              </div>
              <div class="icon"><i class="fas fa-spinner"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= $bookings_delivered ?></h3>
                <p>Bookings Delivered</p>
              </div>
              <div class="icon"><i class="fas fa-check"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
              <div class="inner">
                <h3>$<?= $total_spent ?></h3>
                <p>Total Spent</p>
              </div>
              <div class="icon"><i class="fas fa-dollar-sign"></i></div>
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