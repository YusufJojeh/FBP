<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$client_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT b.*, s.title, v.display_name FROM bookings b JOIN services s ON b.service_id = s.id JOIN vendors v ON b.vendor_id = v.user_id WHERE b.client_id = ?");
$stmt->execute([$client_id]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings</title>
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
          <div class="card-header"><h3 class="card-title">My Bookings</h3></div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead><tr><th>Service</th><th>Vendor</th><th>Status</th><th>Created</th></tr></thead>
              <tbody>
                <?php foreach ($bookings as $booking): ?>
                  <tr>
                    <td><?= htmlspecialchars($booking['title']) ?></td>
                    <td><?= htmlspecialchars($booking['display_name']) ?></td>
                    <td><?= htmlspecialchars($booking['status']) ?></td>
                    <td><?= htmlspecialchars($booking['created_at']) ?></td>
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