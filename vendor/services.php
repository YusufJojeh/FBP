<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$vendor_user_id = $_SESSION['user_id'];
// Get vendor's id from vendors table
$stmt = mysqli_prepare($conn, "SELECT id FROM vendors WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $vendor_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$vendor = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
if (!$vendor) {
    // Create vendor profile if not exists
    mysqli_query($conn, "INSERT INTO vendors (user_id) VALUES ($vendor_user_id)");
    $vendor['id'] = mysqli_insert_id($conn);
}
$vendor_id = $vendor['id'];
$services = [];
$stmt = mysqli_prepare($conn, "SELECT * FROM services WHERE vendor_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $vendor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $services[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>My Services – DesignHub</title>
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
    .services-hero {
      padding:5rem 1rem 2.5rem;
      text-align:center;
      animation:fadeInUp 1s both;
    }
    .services-hero h1 {
      font-size:2.2rem;
      font-weight:800;
      text-shadow:0 2px 8px rgba(0,0,0,0.3);
    }
    .services-hero p {font-size:1.1rem;opacity:.85;}
    .services-grid {
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
      gap:2rem;
      padding:2rem 1rem 3rem;
      max-width:1200px;margin:0 auto;
    }
    .service-card {
      background:rgba(255,255,255,0.13);
      backdrop-filter:blur(12px);
      border-radius:20px;
      box-shadow:0 20px 60px rgba(0,0,0,0.19);
      transition:transform .3s,box-shadow .3s;
      padding:2rem 1.5rem 1.5rem 1.5rem;
      display:flex;flex-direction:column;align-items:flex-start;
      animation:float 7s ease-in-out infinite;
      min-height:210px;
      position:relative;
      overflow:hidden;
    }
    .service-card:hover {
      transform:translateY(-8px) scale(1.03);
      box-shadow:0 30px 80px rgba(0,0,0,0.32);
    }
    .service-card h5 {
      font-size:1.35rem;
      font-weight:700;
      margin-bottom:.5rem;
      color:#fff;
    }
    .service-card .meta {
      font-size:.98rem;
      color:#ffd700;
      margin-bottom:.7rem;
      font-weight:600;
    }
    .service-card .desc {
      color:rgba(255,255,255,0.89);
      font-size:1.01rem;
      margin-bottom:.7rem;
      flex:1;
    }
    .service-card .badge {
      font-size:.93rem;
      margin-right:.5rem;
      border-radius:12px;
      padding:.3em .9em;
      font-weight:600;
    }
    .service-card .badge-status-active {background:#48d9ad;color:#fff;}
    .service-card .badge-status-inactive {background:#ffc107;color:#253053;}
    .service-card .badge-status-suspended {background:#dc3545;color:#fff;}
    .service-card .badge-status-inactive, .service-card .badge-status-suspended {margin-right:0;}
    .service-card .actions {
      margin-top:1.2rem;
      display:flex;gap:.7rem;
    }
    .service-card .btn {
      border-radius:50px;
      font-weight:600;
      background:linear-gradient(45deg,#ff6b6b,#ffd93d);
      border:none;color:#fff;
      box-shadow:0 10px 30px rgba(255,107,107,0.3);
      padding:.6rem 1.5rem;
      transition:transform .3s,box-shadow .3s;
    }
    .service-card .btn:hover {
      transform:translateY(-3px);
      box-shadow:0 15px 40px rgba(255,107,107,0.4);
      background:linear-gradient(120deg,#ffd700,#ff537e 90%);
      color:#fff;
    }
    .add-btn {
      margin-bottom:2rem;
      background:linear-gradient(90deg,#48d9ad,#764ba2);
      color:#fff;font-weight:700;border:none;
      box-shadow:0 2px 12px #764ba242;
      border-radius:22px;
      transition:background .3s;
      padding:.7rem 2.2rem;
      font-size:1.1rem;
    }
    .add-btn:hover {
      background:linear-gradient(120deg,#764ba2,#48d9ad 90%);
      color:#ffd700;
    }
    @media (max-width:600px) {
      .services-grid {gap:1rem;}
      .service-card {padding:1.2rem .7rem 1.2rem .7rem;}
    }
    @keyframes fadeInUp {from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
    @keyframes float {0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
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
          <li class="nav-item"><a class="nav-link active" href="services.php">My Services</a></li>
          <li class="nav-item"><a class="nav-link" href="add_service.php">Add Service</a></li>
          <li class="nav-item"><a class="nav-link" href="update_order.php">Orders</a></li>
          <li class="nav-item"><a class="nav-link" href="chats.php">Chats</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <section class="services-hero">
    <h1>My Services</h1>
    <p>Manage your offerings and keep your portfolio up to date</p>
    <a href="add_service.php" class="add-btn"><i class="fas fa-plus me-2"></i>Add New Service</a>
  </section>

  <section class="services-grid">
    <?php foreach ($services as $service): ?>
      <div class="service-card">
        <h5><i class="fas fa-gem me-2"></i><?= htmlspecialchars($service['title']) ?></h5>
        <div class="meta">
          <span class="badge badge-status-<?= $service['status'] ?>"><?= ucfirst($service['status']) ?></span>
          <span><i class="fas fa-tags me-1"></i><?= htmlspecialchars($service['category']) ?></span>
          <span><i class="fas fa-calendar-alt ms-2 me-1"></i><?= date('M j, Y', strtotime($service['created_at'])) ?></span>
        </div>
        <div class="desc"><?= htmlspecialchars($service['description']) ?></div>
        <div class="meta"><i class="fas fa-dollar-sign me-1"></i><?= number_format($service['price'],2) ?></div>
        <div class="actions">
          <a href="edit_service.php?id=<?= $service['id'] ?>" class="btn btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
          <a href="delete_service.php?id=<?= $service['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this service?')"><i class="fas fa-trash me-1"></i>Delete</a>
        </div>
      </div>
    <?php endforeach; ?>
  </section>

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