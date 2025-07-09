<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$client_id = $_SESSION['user_id'];
$service_id = $_GET['service_id'] ?? null;
if (!$service_id) { die('Service not found.'); }
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();
if (!$service) { die('Service not found.'); }
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requirements = $_POST['requirements'];
    $stmt = $pdo->prepare("INSERT INTO bookings (service_id, client_id, vendor_id, requirements) VALUES (?, ?, ?, ?)");
    $stmt->execute([$service_id, $client_id, $service['vendor_id'], $requirements]);
    $msg = 'Booking placed!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Book Service</title>
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
          <div class="card-header"><h3 class="card-title">Book: <?= htmlspecialchars($service['title']) ?></h3></div>
          <div class="card-body">
            <?php if ($msg): ?><div class="alert alert-success">Booking placed!</div><?php endif; ?>
            <form method="POST">
              <div class="form-group">
                <label>Your Requirements</label>
                <textarea class="form-control" name="requirements" required></textarea>
              </div>
              <button type="submit" class="btn btn-success">Submit Booking</button>
            </form>
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