<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$msg = '';

// Handle service deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM services WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $msg = 'Service deleted!';
}

// Fetch all services with vendor info
$services = [];
$result = mysqli_query($conn, "SELECT s.*, v.display_name FROM services s LEFT JOIN vendors v ON s.vendor_id = v.id ORDER BY s.id DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }
    mysqli_free_result($result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manage Services – DesignHub Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    * {margin:0;padding:0;box-sizing:border-box;}
    body {
      font-family:'Poppins',sans-serif;
      background: linear-gradient(135deg,#667eea,#764ba2);
      color:#fff;
      min-height:100vh;
      overflow-x:hidden;
    }
    .navbar {
      background:rgba(255,255,255,0.1)!important;
      backdrop-filter:blur(10px);
      transition:background .3s,box-shadow .3s;
    }
    .navbar.scrolled {
      background:rgba(255,255,255,0.95)!important;
      box-shadow:0 2px 20px rgba(0,0,0,0.1);
    }
    .navbar-brand, .nav-link {color:#fff!important;font-weight:500;}
    .nav-link.active, .nav-link:hover {color:#ffd700!important;}
    .nav-link {margin:0 .5rem;position:relative;}
    .nav-link::after {
      content:'';position:absolute;bottom:-4px;left:50%;
      width:0;height:2px;background:#ffd700;
      transform:translateX(-50%);transition:width .3s;
    }
    .nav-link:hover::after, .nav-link.active::after {width:100%;}
    .admin-hero {
      padding:4rem 1rem 2rem;
      text-align:center;
      animation:fadeInUp 1s both;
    }
    .admin-hero h1 {font-size:2.2rem;font-weight:800;text-shadow:0 2px 8px rgba(0,0,0,0.3);}
    .admin-hero p {font-size:1.08rem;opacity:.85;}
    .manage-card {
      background:rgba(255,255,255,0.12);
      backdrop-filter:blur(10px);
      border-radius:20px;
      box-shadow:0 18px 56px rgba(0,0,0,0.18);
      padding:2rem 1rem 1rem 1rem;
      max-width:1200px;
      margin:auto;
      animation:float 7s ease-in-out infinite;
    }
    .manage-card table {
      background:transparent;
      color:#fff;
    }
    .manage-card th, .manage-card td {
      border-color:rgba(255,255,255,0.08);
      vertical-align:middle;
      font-size:1.02rem;
      background:rgba(255,255,255,0.05);
      transition:background .3s;
    }
    .manage-card tr:hover td {
      background:rgba(255,255,255,0.13);
      transition:background .2s;
    }
    .btn-del {
      background:linear-gradient(90deg,#ff537e,#ffd700 99%);
      color:#fff;border:none;
      border-radius:22px;font-size:.97rem;font-weight:600;box-shadow:0 1px 10px #0001;
      padding:6px 18px;
      transition:background .3s;
    }
    .btn-del:hover {
      background:linear-gradient(120deg,#ffd700,#ff537e 90%);
      color:#fff;
    }
    @media (max-width:600px) {
      .manage-card {padding:1.3rem .4rem;}
      table td, table th {font-size:.97rem;}
    }
    @keyframes fadeInUp {from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
    @keyframes float {0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
    .coming-soon-overlay {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-10deg);
      background: rgba(255,215,0,0.97);
      color: #764ba2;
      font-size: 2.7rem;
      font-weight: 900;
      padding: 1.2rem 3.5rem;
      border-radius: 30px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.18);
      z-index: 100;
      opacity: 0.93;
      letter-spacing: 2px;
      text-shadow: 0 2px 8px #fff8;
      pointer-events: none;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top py-2">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php"><i class="fas fa-palette me-2"></i>DesignHub</a>
      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
          <li class="nav-item"><a class="nav-link active" href="manage_services.php">Manage Services</a></li>
          <li class="nav-item"><a class="nav-link" href="site_settings.php">Site Settings</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="awards.php">Awarding</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <section class="admin-hero">
    <h1>Manage Services</h1>
    <p>Add, edit, or remove services from your platform</p>
    <?php if ($msg): ?>
      <div class="alert alert-success text-center mt-3"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
  </section>

  <div class="manage-card shadow-lg">
    <div class="table-responsive text-white">
      <table class="table align-middle text-center text-white table-hover">
        <thead class="text-white">
          <tr style="background:rgba(255,255,255,0.09);">
            <th>ID</th>
            <th>Title</th>
            <th>Vendor</th>
            <th>Category</th>
            <th>Price</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody class="text-white">
        <?php foreach ($services as $service): ?>
          <tr>
            <td><?= $service['id'] ?></td>
            <td><?= htmlspecialchars($service['title']) ?></td>
            <td><?= htmlspecialchars($service['display_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($service['category']) ?></td>
            <td>$<?= number_format($service['price'], 2) ?></td>
            <td><?= date('Y-m-d H:i', strtotime($service['created_at'])) ?></td>
            <td>
              <a href="?delete=<?= $service['id'] ?>" class="btn btn-sm btn-del" onclick="return confirm('Delete this service?')">
                <i class="fas fa-trash"></i> Delete
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div style="height:2rem;"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    const nav = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 50);
    });
  </script>
</body>
</html> 