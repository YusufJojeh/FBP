<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$vendor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ? AND vendor_id = ?");
    $stmt->execute([$status, $booking_id, $vendor_id]);
    $stmt2 = $pdo->prepare("INSERT INTO order_status (booking_id, status) VALUES (?, ?)");
    $stmt2->execute([$booking_id, $status]);
}

$stmt = $pdo->prepare("SELECT b.*, s.title, u.username as client_name FROM bookings b JOIN services s ON b.service_id = s.id JOIN users u ON b.client_id = u.id WHERE b.vendor_id = ?");
$stmt->execute([$vendor_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders</title>
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
          <div class="card-header"><h3 class="card-title">Orders</h3></div>
          <div class="card-body">
            <table class="table table-bordered">
              <thead><tr><th>Service</th><th>Client</th><th>Status</th><th>Update Status</th></tr></thead>
              <tbody>
                <?php foreach ($orders as $order): ?>
                  <tr>
                    <td><?= htmlspecialchars($order['title']) ?></td>
                    <td><?= htmlspecialchars($order['client_name']) ?></td>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                    <td>
                      <form method="POST" class="form-inline">
                        <input type="hidden" name="booking_id" value="<?= $order['id'] ?>">
                        <select name="status" class="form-control form-control-sm">
                          <option value="pending" <?= $order['status']=='pending'?'selected':'' ?>>Pending</option>
                          <option value="in_progress" <?= $order['status']=='in_progress'?'selected':'' ?>>In Progress</option>
                          <option value="delivered" <?= $order['status']=='delivered'?'selected':'' ?>>Delivered</option>
                          <option value="cancelled" <?= $order['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary ml-2">Update</button>
                      </form>
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