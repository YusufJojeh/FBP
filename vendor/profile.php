<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$vendor_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM vendors WHERE user_id = ?");
$stmt->execute([$vendor_id]);
$profile = $stmt->fetch();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $display_name = $_POST['display_name'];
    $bio = $_POST['bio'];
    $stmt = $pdo->prepare("UPDATE vendors SET display_name = ?, bio = ? WHERE user_id = ?");
    $stmt->execute([$display_name, $bio, $vendor_id]);
    $msg = 'Profile updated!';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vendor Profile</title>
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
          <div class="card-header"><h3 class="card-title">Profile</h3></div>
          <div class="card-body">
            <?php if ($msg): ?><div class="alert alert-success">Profile updated!</div><?php endif; ?>
            <form method="POST">
              <div class="form-group">
                <label>Display Name</label>
                <input type="text" name="display_name" class="form-control" value="<?= htmlspecialchars($profile['display_name'] ?? '') ?>">
              </div>
              <div class="form-group">
                <label>Bio</label>
                <textarea name="bio" class="form-control"><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Update Profile</button>
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