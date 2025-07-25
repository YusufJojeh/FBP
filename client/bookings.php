<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$client_id = $_SESSION['user_id'];
$msg = '';
$success = false;
$error = '';
// Handle cancel booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $booking_id = intval($_POST['cancel_booking_id']);
    // Only allow cancel if booking belongs to client and is not delivered/cancelled
    $stmt = mysqli_prepare($conn, "SELECT status FROM bookings WHERE id = ? AND client_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $client_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $booking = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if (!$booking) {
        $error = 'Booking not found.';
    } elseif (in_array($booking['status'], ['delivered', 'cancelled'])) {
        $error = 'Cannot cancel delivered or already cancelled bookings.';
    } else {
        mysqli_begin_transaction($conn);
        try {
            $stmt = mysqli_prepare($conn, "UPDATE bookings SET status = 'cancelled' WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $booking_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($conn, "INSERT INTO order_status (booking_id, status, changed_at) VALUES (?, 'cancelled', NOW())");
            mysqli_stmt_bind_param($stmt, 'i', $booking_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            mysqli_commit($conn);
            header('Location: bookings.php?msg=Booking+cancelled!');
            exit;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = 'Error cancelling booking. Please try again.';
        }
    }
}
// Handle edit requirements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_booking_id'], $_POST['new_requirements'])) {
    $booking_id = intval($_POST['edit_booking_id']);
    $new_req = trim($_POST['new_requirements']);
    // Only allow edit if booking belongs to client and is pending
    $stmt = mysqli_prepare($conn, "SELECT status FROM bookings WHERE id = ? AND client_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $client_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $booking = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if (!$booking) {
        $error = 'Booking not found.';
    } elseif ($booking['status'] !== 'pending') {
        $error = 'Can only edit requirements for pending bookings.';
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE bookings SET requirements = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $new_req, $booking_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        header('Location: bookings.php?msg=Requirements+updated!');
        exit;
    }
}
// Fetch all bookings with service and vendor info
$bookings = [];
$query = "SELECT b.*, s.title AS service_title, s.price, v.display_name AS vendor_name, v.profile_image FROM bookings b JOIN services s ON b.service_id = s.id JOIN vendors v ON b.vendor_id = v.id WHERE b.client_id = ? ORDER BY b.created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $bookings[] = $row;
}
mysqli_stmt_close($stmt);
// Stats
$total = count($bookings);
$in_progress = count(array_filter($bookings, fn($b) => $b['status']==='in_progress'));
$delivered = count(array_filter($bookings, fn($b) => $b['status']==='delivered'));
$cancelled = count(array_filter($bookings, fn($b) => $b['status']==='cancelled'));
if (isset($_GET['msg'])) {
    $msg = $_GET['msg'];
    $success = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>My Bookings – DesignHub</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
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
    .bookings-hero {
      padding:5rem 1rem 2.5rem;
      text-align:center;
      animation:fadeInUp 1s both;
    }
    .bookings-hero h1 {
      font-size:2.2rem;
      font-weight:800;
      text-shadow:0 2px 8px rgba(0,0,0,0.3);
    }
    .bookings-hero p {font-size:1.1rem;opacity:.85;}
    .stats-bar {
      display:flex;gap:1.2rem;justify-content:center;flex-wrap:wrap;
      margin-bottom:2.2rem;
    }
    .stat-pill {
      background:rgba(255,255,255,0.13);
      border-radius:50px;
      padding:.7rem 2.2rem;
      color:#ffd700;
      font-weight:700;
      font-size:1.08rem;
      display:flex;align-items:center;gap:.7rem;
      box-shadow:0 2px 12px #764ba242;
      margin-bottom:.7rem;
    }
    .stat-pill i {font-size:1.2rem;}
    .search-bar {
      max-width:420px;margin:0 auto 2.2rem auto;
      display:flex;gap:.5rem;
    }
    .search-bar input {
      border-radius:50px 0 0 50px;
      border:none;
      padding:.7rem 1.2rem;
      background:rgba(255,255,255,0.18);
      color:#fff;
      font-size:1.05rem;
      flex:1;
    }
    .search-bar input:focus {outline:none;background:rgba(255,255,255,0.28);}
    .search-bar button {
      border-radius:0 50px 50px 0;
      border:none;
      background:#ffd700;
      color:#253053;
      font-weight:700;
      padding:.7rem 1.3rem;
      font-size:1.1rem;
      transition:background .3s;
    }
    .search-bar button:hover {background:#ff6b6b;color:#fff;}
    .bookings-grid {
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(340px,1fr));
      gap:2rem;
      padding:2rem 1rem 3rem;
      max-width:1200px;margin:0 auto;
    }
    .booking-card {
      background:rgba(255,255,255,0.13);
      backdrop-filter:blur(12px);
      border-radius:20px;
      box-shadow:0 20px 60px rgba(0,0,0,0.19);
      transition:transform .3s,box-shadow .3s;
      padding:2rem 1.5rem 1.5rem 1.5rem;
      animation:float 7s ease-in-out infinite;
      position:relative;
      overflow:hidden;
      display:flex;
      flex-direction:column;
      min-height:340px;
    }
    .booking-card:hover {
      transform:translateY(-8px) scale(1.02);
      box-shadow:0 30px 80px rgba(0,0,0,0.32);
    }
    .booking-card h5 {
      font-size:1.35rem;
      font-weight:700;
      margin-bottom:.8rem;
      color:#fff;
    }
    .booking-meta {
      font-size:.98rem;
      color:#ffd700;
      margin-bottom:.7rem;
      font-weight:500;
    }
    .booking-meta i {
      width:22px;
      color:#ffd700;
    }
    .booking-requirements {
      color:rgba(255,255,255,0.89);
      font-size:1.01rem;
      margin:1rem 0;
      padding:1rem;
      background:rgba(255,255,255,0.1);
      border-radius:12px;
      border-left:3px solid #ffd700;
    }
    .badge-status {
      font-size:.93rem;
      border-radius:12px;
      padding:.4em 1em;
      font-weight:600;
    }
    .badge-pending     {background:rgba(255,193,7,0.2);color:#ffc107;}
    .badge-in_progress {background:rgba(23,162,184,0.2);color:#17a2b8;}
    .badge-delivered   {background:rgba(40,167,69,0.2);color:#28a745;}
    .badge-cancelled   {background:rgba(220,53,69,0.2);color:#dc3545;}
    .vendor-avatar {
      width:44px;height:44px;border-radius:50%;background:#ffd700;color:#253053;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;margin-right:.7rem;}
    .vendor-name {font-weight:700;color:#ffd700;}
    .vendor-row {display:flex;align-items:center;gap:.7rem;margin-bottom:.5rem;}
    .alert-excuse {
      background:rgba(255,193,7,0.13);color:#ffc107;border:none;
      border-radius:10px;padding:.7rem 1.2rem;margin-top:.7rem;font-size:.98rem;
    }
    @media (max-width:600px) {
      .bookings-grid {gap:1rem;}
      .booking-card {padding:1.2rem;}
      .stats-bar {gap:.5rem;}
    }
    @keyframes fadeInUp {from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)} }
    @keyframes float {0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
    .empty-state {
      text-align:center;
      opacity:.85;
      padding:4rem 1rem 2rem 1rem;
    }
    .empty-state i {font-size:3.5rem;color:#ffd700;margin-bottom:1.2rem;}
    .toast {
      position: fixed;
      top: 20px;
      right: 20px;
      background-color: #4CAF50;
      color: white;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      display: none;
      z-index: 1000;
      opacity: 0.9;
    }
    .action-btn {border-radius:50px;font-weight:600;background:linear-gradient(45deg,#ff6b6b,#ffd93d);border:none;color:#fff;box-shadow:0 10px 30px rgba(255,107,107,0.3);padding:.5rem 1.2rem;transition:transform .3s,box-shadow .3s;}
    .action-btn:hover {transform:translateY(-3px);box-shadow:0 15px 40px rgba(255,107,107,0.4);background:linear-gradient(120deg,#ffd700,#ff537e 90%);color:#fff;}
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
          <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
          <li class="nav-item"><a class="nav-link active" href="bookings.php">Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="chats.php">Chats</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <section class="bookings-hero">
    <h1>My Bookings</h1>
    <p>Track all your service bookings and their status</p>
  </section>
  <div class="stats-bar">
    <div class="stat-pill"><i class="fas fa-list"></i> Total: <span><?= $total ?></span></div>
    <div class="stat-pill"><i class="fas fa-spinner"></i> In Progress: <span><?= $in_progress ?></span></div>
    <div class="stat-pill"><i class="fas fa-check-circle"></i> Delivered: <span><?= $delivered ?></span></div>
    <div class="stat-pill"><i class="fas fa-times-circle"></i> Cancelled: <span><?= $cancelled ?></span></div>
  </div>
  <form class="search-bar" method="get" onsubmit="return filterBookings();">
    <input type="text" id="searchInput" placeholder="Search by service, vendor, or status...">
    <button type="submit"><i class="fas fa-search"></i></button>
  </form>
  <section class="bookings-grid" id="bookingsGrid">
    <?php if (empty($bookings)): ?>
      <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>No bookings yet</h3>
        <p>Book a service to get started!</p>
      </div>
    <?php endif; ?>
    <?php foreach ($bookings as $b): ?>
      <div class="booking-card" data-service="<?= htmlspecialchars(strtolower($b['service_title'])) ?>" data-vendor="<?= htmlspecialchars(strtolower($b['vendor_name'])) ?>" data-status="<?= htmlspecialchars(strtolower($b['status'])) ?>">
        <div class="vendor-row mb-2">
          <div class="vendor-avatar">
            <i class="fas fa-user-tie"></i>
          </div>
          <div>
            <div class="vendor-name"><?= htmlspecialchars($b['vendor_name']) ?></div>
            <div style="font-size:.97rem;color:#fff;opacity:.7;">Service: <?= htmlspecialchars($b['service_title']) ?></div>
          </div>
        </div>
        <div class="booking-meta mb-2">
          <span class="badge-status badge-<?= $b['status'] ?>">
            <i class="fas fa-circle me-1"></i><?= ucfirst($b['status']) ?>
          </span>
          <span class="ms-2"><i class="fas fa-calendar-alt me-1"></i><?= date('M j, Y', strtotime($b['created_at'])) ?></span>
          <span class="ms-2"><i class="fas fa-dollar-sign me-1"></i><?= number_format($b['price'],2) ?></span>
        </div>
        <div class="booking-requirements">
          <i class="fas fa-quote-left me-2 opacity-50"></i>
          <?= nl2br(htmlspecialchars($b['requirements'])) ?>
        </div>
        <?php if ($b['status'] === 'cancelled' && $b['excuse']): ?>
          <div class="alert-excuse">
            <i class="fas fa-info-circle me-1"></i> <strong>Excuse:</strong> <?= nl2br(htmlspecialchars($b['excuse'])) ?>
          </div>
        <?php endif; ?>
        <div class="d-flex gap-2 mt-auto">
          <?php if ($b['status'] === 'pending'): ?>
            <button class="action-btn" 
              data-bs-toggle="modal" 
              data-bs-target="#editModal"
              data-booking-id="<?= $b['id'] ?>"
              data-requirements="<?= htmlspecialchars($b['requirements'], ENT_QUOTES) ?>">
              <i class="fas fa-edit me-1"></i>Edit Requirements
            </button>
            <button class="action-btn btn-danger" onclick="showCancelModal(<?= $b['id'] ?>)"><i class="fas fa-times me-1"></i>Cancel Booking</button>
          <?php elseif ($b['status'] === 'in_progress'): ?>
            <button class="action-btn btn-danger" onclick="showCancelModal(<?= $b['id'] ?>)"><i class="fas fa-times me-1"></i>Cancel Booking</button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </section>
  <!-- Edit Requirements Modal -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Requirements</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="edit_booking_id" id="edit_booking_id">
          <div class="mb-3">
            <label for="new_requirements" class="form-label">Requirements</label>
            <textarea name="new_requirements" id="new_requirements" class="form-control" required rows="4"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100"><i class="fas fa-save me-2"></i>Save Changes</button>
        </div>
      </form>
    </div>
  </div>
  <!-- Cancel Booking Modal -->
  <div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-times me-2"></i>Cancel Booking</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="cancel_booking_id" id="cancel_booking_id">
          <p>Are you sure you want to cancel this booking?</p>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger w-100"><i class="fas fa-times me-2"></i>Cancel Booking</button>
        </div>
      </form>
    </div>
  </div>
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
    <?php if ($success): ?>
      showToast('<?= htmlspecialchars($msg) ?>');
    <?php endif; ?>
    <?php if ($error): ?>
      showToast('<?= htmlspecialchars($error) ?>');
    <?php endif; ?>
    // Search/filter logic
    function filterBookings() {
      const q = document.getElementById('searchInput').value.trim().toLowerCase();
      document.querySelectorAll('.booking-card').forEach(card => {
        const service = card.getAttribute('data-service');
        const vendor = card.getAttribute('data-vendor');
        const status = card.getAttribute('data-status');
        if (!q || service.includes(q) || vendor.includes(q) || status.includes(q)) {
          card.style.display = '';
        } else {
          card.style.display = 'none';
        }
      });
      return false;
    }
    // Edit Requirements Modal
    document.addEventListener('DOMContentLoaded', function() {
      var editModal = document.getElementById('editModal');
      if (editModal) {
        editModal.addEventListener('show.bs.modal', function (event) {
          var button = event.relatedTarget;
          var bookingId = button.getAttribute('data-booking-id');
          var requirements = button.getAttribute('data-requirements') || '';
          document.getElementById('edit_booking_id').value = bookingId;
          document.getElementById('new_requirements').value = requirements;
          setTimeout(function(){
            document.getElementById('new_requirements').focus();
          }, 350);
        });
      }
    });
    // Cancel Booking Modal
    function showCancelModal(bookingId) {
      document.getElementById('cancel_booking_id').value = bookingId;
      var modal = new bootstrap.Modal(document.getElementById('cancelModal'));
      modal.show();
    }
  </script>
</body>
</html>
