<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$stmt = $pdo->query("SELECT s.*, v.display_name FROM services s JOIN vendors v ON s.vendor_id = v.user_id");
$services = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Browse Services</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
  <?php include 'sidebar.php'; ?>
  <div class="content-wrapper">
    <section class="content pt-4">
      <div class="container-fluid">
        <div class="card">
          <div class="card-header"><h3 class="card-title">Available Services</h3></div>
          <div class="card-body">
            <div class="row">
              <?php foreach ($services as $service): ?>
                <div class="col-md-4">
                  <div class="card mb-4">
                    <div class="card-body">
                      <h5 class="card-title"><?= htmlspecialchars($service['title']) ?></h5>
                      <p class="card-text"><?= htmlspecialchars($service['description']) ?></p>
                      <p class="card-text"><strong>Vendor:</strong> <?= htmlspecialchars($service['display_name']) ?></p>
                      <p class="card-text"><strong>Price:</strong> $<?= $service['price'] ?></p>
                      <a href="book.php?service_id=<?= $service['id'] ?>" class="btn btn-primary">Book Now</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
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