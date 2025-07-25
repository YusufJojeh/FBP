<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$client_id = $_SESSION['user_id'];
// Fetch stats
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM bookings WHERE client_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_bookings = mysqli_fetch_row($result)[0];
mysqli_stmt_close($stmt);
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM bookings WHERE client_id = ? AND status = 'in_progress'");
mysqli_stmt_bind_param($stmt, 'i', $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$in_progress = mysqli_fetch_row($result)[0];
mysqli_stmt_close($stmt);
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM bookings WHERE client_id = ? AND status = 'delivered'");
mysqli_stmt_bind_param($stmt, 'i', $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$delivered = mysqli_fetch_row($result)[0];
mysqli_stmt_close($stmt);
$stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM bookings WHERE client_id = ? AND status = 'cancelled'");
mysqli_stmt_bind_param($stmt, 'i', $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cancelled = mysqli_fetch_row($result)[0];
mysqli_stmt_close($stmt);
// Fetch username
$stmt = mysqli_prepare($conn, "SELECT username FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$username = ($row = mysqli_fetch_assoc($result)) ? $row['username'] : 'Client';
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Client Dashboard – DesignHub</title>
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
      padding:5rem 1rem 3rem;
      text-align:center;
      animation:fadeInUp 1s both;
    }
    .services-hero h1 {
      font-size:2.5rem;
      font-weight:800;
      text-shadow:0 2px 8px rgba(0,0,0,0.3);
    }
    .services-hero p {font-size:1.1rem;opacity:.85;}
    .services-grid {
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
      gap:1.7rem;
      padding:2.5rem 1rem 3rem;
      max-width:1200px;margin:0 auto;
    }
    .service-card {
      background:rgba(255,255,255,0.13);
      backdrop-filter:blur(12px);
      border-radius:22px;
      box-shadow:0 20px 60px rgba(0,0,0,0.19);
      transition:transform .3s,box-shadow .3s;
      padding:2rem 1.5rem;
      display:flex;flex-direction:column;align-items:center;
      animation:float 7s ease-in-out infinite;
      min-height:190px;
      position:relative;
      overflow:hidden;
    }
    .service-card:hover {
      transform:translateY(-8px) scale(1.03);
      box-shadow:0 30px 80px rgba(0,0,0,0.32);
    }
    .service-card .stat-icon {
      font-size:2.9rem;
      margin-bottom:.95rem;
      color:#ffd700;
      filter:drop-shadow(0 4px 12px #ffec8b40);
      transition: transform .4s;
      animation: bounceIn 2.3s cubic-bezier(.42,.15,.38,.96) infinite;
    }
    .service-card:hover .stat-icon {
      transform: scale(1.12) rotate(-5deg);
    }
    .service-card h3 {
      font-size:2.3rem;
      font-weight:800;
      margin-bottom:.3rem;
      letter-spacing:.5px;
      color:#fff;
    }
    .service-card p {
      font-size:1.13rem;
      opacity:.87;
      letter-spacing:.01rem;
      color:#ffd700;
      font-weight:600;
    }
    @keyframes fadeInUp {from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)} }
    @keyframes float {0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
    @keyframes bounceIn {
      0%,100%{transform:translateY(0)}
      15%{transform:translateY(-7px) scale(1.08)}
      45%{transform:translateY(-2px) scale(1.03)}
      65%{transform:translateY(-4px) scale(1.09)}
      80%{transform:translateY(-2px)}
    }
    .footer-spacer{height:2.2rem;}
    .toast {
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: #4CAF50; /* Green background */
      color: white;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      display: none; /* Hidden by default */
      z-index: 1000;
      opacity: 0.9;
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
          <li class="nav-item"><a class="nav-link active" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="bookings.php">Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="chats.php">Chats</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <section class="services-hero">
    <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
    <p>Quick overview of your bookings and activity</p>
  </section>
  <section class="services-grid">
    <div class="service-card">
      <h5><i class="fas fa-calendar-check me-2"></i>Total Bookings</h5>
      <div class="meta"><?= $total_bookings ?></div>
    </div>
    <div class="service-card">
      <h5><i class="fas fa-spinner me-2"></i>In Progress</h5>
      <div class="meta"><?= $in_progress ?></div>
    </div>
    <div class="service-card">
      <h5><i class="fas fa-check-circle me-2"></i>Delivered</h5>
      <div class="meta"><?= $delivered ?></div>
    </div>
    <div class="service-card">
      <h5><i class="fas fa-times-circle me-2"></i>Cancelled</h5>
      <div class="meta"><?= $cancelled ?></div>
    </div>
  </section>
  <div class="toast" id="toast-success"></div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    const nav = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 50);
    });
    // Toast notification function
    function showToast(msg) {
      const toast = document.getElementById('toast-success');
      toast.textContent = msg;
      toast.style.display = 'flex';
      setTimeout(()=>toast.style.display='none', 3000);
    }
  </script>
</body>
</html>
