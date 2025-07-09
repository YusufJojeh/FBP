<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
    $stmt->execute([$id]);
}
$stmt = $pdo->query("SELECT s.*, v.display_name FROM services s JOIN vendors v ON s.vendor_id = v.user_id");
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Services</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
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
      <span class="brand-text font-weight-light">DesignHub Admin</span>
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
            <a href="manage_users.php" class="nav-link">
              <i class="nav-icon fas fa-users"></i>
              <p>Manage Users</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="manage_services.php" class="nav-link">
              <i class="nav-icon fas fa-briefcase"></i>
              <p>Manage Services</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="site_settings.php" class="nav-link">
              <i class="nav-icon fas fa-cogs"></i>
              <p>Site Settings</p>
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
          <div class="card-header"><h3 class="card-title">Manage Services</h3></div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead><tr><th>ID</th><th>Title</th><th>Vendor</th><th>Category</th><th>Price</th><th>Actions</th></tr></thead>
              <tbody>
                <?php foreach ($services as $service): ?>
                  <tr>
                    <td><?= $service['id'] ?></td>
                    <td><?= htmlspecialchars($service['title']) ?></td>
                    <td><?= htmlspecialchars($service['display_name']) ?></td>
                    <td><?= htmlspecialchars($service['category']) ?></td>
                    <td>$<?= $service['price'] ?></td>
                    <td>
                      <a href="?delete=<?= $service['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this service?')">Delete</a>
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