<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$vendor_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM services WHERE vendor_id = ?");
$stmt->execute([$vendor_id]);
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Services</title>
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
            <a href="dashboard.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="services.php" class="nav-link active">
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
  <div class="content-wrapper">
    <section class="content pt-4">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">My Services</h3>
            <a href="add_service.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Service</a>
          </div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead><tr><th>Title</th><th>Category</th><th>Price</th><th>Actions</th></tr></thead>
              <tbody>
                <?php foreach ($services as $service): ?>
                  <tr>
                    <td><?= htmlspecialchars($service['title']) ?></td>
                    <td><?= htmlspecialchars($service['category']) ?></td>
                    <td>$<?= $service['price'] ?></td>
                    <td>
                      <a href="edit_service.php?id=<?= $service['id'] ?>" class="btn btn-sm btn-info">Edit</a>
                      <a href="delete_service.php?id=<?= $service['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this service?')">Delete</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html> 