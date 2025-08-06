<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';

// Handle AJAX chat fetch/send
if (isset($_GET['chat']) && isset($_GET['booking_id'])) {
    // Fetch chat messages for this booking
    $booking_id = intval($_GET['booking_id']);
    $messages = [];
    $stmt = mysqli_prepare($conn, "SELECT m.*, u.username, u.role FROM order_messages m JOIN users u ON m.sender_id = u.id WHERE m.booking_id = ? ORDER BY m.sent_at ASC");
    mysqli_stmt_bind_param($stmt, 'i', $booking_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = $row;
    }
    mysqli_stmt_close($stmt);
    header('Content-Type: application/json');
    echo json_encode($messages);
    exit;
}
if (isset($_POST['send_chat']) && isset($_POST['booking_id']) && isset($_POST['message'])) {
    // Send chat message
    $booking_id = intval($_POST['booking_id']);
    $message = trim($_POST['message']);
    $sender_id = $_SESSION['user_id'];
    if ($message !== '') {
        $stmt = mysqli_prepare($conn, "INSERT INTO order_messages (booking_id, sender_id, message) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iis', $booking_id, $sender_id, $message);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    exit;
}

// Get vendor's id from vendors table
$vendor_user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn, "SELECT id FROM vendors WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, 'i', $vendor_user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$vendor = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
if (!$vendor) {
    die('Vendor profile not found');
}
$vendor_id = $vendor['id'];

$error = '';
$success = '';

// Handle status updates and vendor cancel with excuse
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancel_booking_id'], $_POST['excuse_message'])) {
        // Vendor cancel with excuse
        $booking_id = intval($_POST['cancel_booking_id']);
        $excuse = trim($_POST['excuse_message']);
        // Only allow cancel if not delivered/cancelled
        $stmt = mysqli_prepare($conn, "SELECT id, status FROM bookings WHERE id = ? AND vendor_id = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $vendor_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $booking = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$booking) {
            $error = 'Order not found or access denied.';
        } elseif (in_array($booking['status'], ['delivered', 'cancelled'])) {
            $error = 'Cannot cancel completed or already cancelled orders.';
        } else {
            mysqli_begin_transaction($conn);
            try {
                $stmt = mysqli_prepare($conn, "UPDATE bookings SET status = 'cancelled', excuse = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, 'si', $excuse, $booking_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                $stmt = mysqli_prepare($conn, "INSERT INTO order_status (booking_id, status, changed_at) VALUES (?, 'cancelled', NOW())");
                mysqli_stmt_bind_param($stmt, 'i', $booking_id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                mysqli_commit($conn);
                $success = 'Order cancelled and excuse sent!';
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = 'Error cancelling order. Please try again.';
            }
        }
    } elseif (isset($_POST['booking_id'], $_POST['new_status'])) {
        $booking_id = intval($_POST['booking_id']);
        $new_status = $_POST['new_status'];
        $valid_statuses = ['pending', 'in_progress', 'delivered', 'cancelled'];
        
        if (!in_array($new_status, $valid_statuses)) {
            $error = 'Invalid status selected.';
        } else {
            // Verify booking belongs to vendor and get current status
            $stmt = mysqli_prepare($conn, "SELECT id, status FROM bookings WHERE id = ? AND vendor_id = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $booking_id, $vendor_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $booking = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if (!$booking) {
                $error = 'Order not found or access denied.';
            } else {
                $current_status = $booking['status'];
                
                // Validate status transition
                $valid_transition = true;
                if ($current_status === 'delivered' || $current_status === 'cancelled') {
                    $valid_transition = false;
                    $error = 'Cannot update completed or cancelled orders.';
                } elseif ($current_status === 'pending' && $new_status === 'delivered') {
                    $valid_transition = false;
                    $error = 'Order must be in progress before marking as delivered.';
                }

                if ($valid_transition) {
                    mysqli_begin_transaction($conn);
                    try {
                        // Update booking status
                        $stmt = mysqli_prepare($conn, "UPDATE bookings SET status = ? WHERE id = ?");
                        mysqli_stmt_bind_param($stmt, 'si', $new_status, $booking_id);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                        
                        // Record status change in history
                        $stmt = mysqli_prepare($conn, "INSERT INTO order_status (booking_id, status, changed_at) VALUES (?, ?, NOW())");
                        mysqli_stmt_bind_param($stmt, 'is', $booking_id, $new_status);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_close($stmt);
                        
                        mysqli_commit($conn);
                        $success = "Order status updated successfully!";
                    } catch (Exception $e) {
                        mysqli_rollback($conn);
                        $error = "Error updating status. Please try again.";
                    }
                }
            }
        }
    }
}

// Fetch orders with client and service details
$orders = [];
$query = "SELECT b.*, 
          s.title AS service_title, 
          s.price,
          u.username AS client_name,
          u.email AS client_email,
          (SELECT changed_at 
           FROM order_status os 
           WHERE os.booking_id = b.id 
           ORDER BY os.changed_at DESC 
           LIMIT 1) as last_updated
          FROM bookings b
          JOIN services s ON b.service_id = s.id
          JOIN users u ON b.client_id = u.id
          WHERE b.vendor_id = ?
          ORDER BY 
            CASE b.status 
              WHEN 'pending' THEN 1
              WHEN 'in_progress' THEN 2
              WHEN 'delivered' THEN 3
              WHEN 'cancelled' THEN 4
            END,
            b.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $vendor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $orders[] = $row;
}
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Manage Orders – DesignHub</title>
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
    .orders-hero {
      padding:5rem 1rem 2.5rem;
      text-align:center;
      animation:fadeInUp 1s both;
    }
    .orders-hero h1 {
      font-size:2.2rem;
      font-weight:800;
      text-shadow:0 2px 8px rgba(0,0,0,0.3);
    }
    .orders-hero p {font-size:1.1rem;opacity:.85;}
    .orders-grid {
      display:grid;
      grid-template-columns:repeat(auto-fit,minmax(340px,1fr));
      gap:2rem;
      padding:2rem 1rem 3rem;
      max-width:1200px;margin:0 auto;
    }
    .order-card {
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
    .order-card:hover {
      transform:translateY(-8px) scale(1.02);
      box-shadow:0 30px 80px rgba(0,0,0,0.32);
    }
    .order-card h5 {
      font-size:1.35rem;
      font-weight:700;
      margin-bottom:.8rem;
      color:#fff;
    }
    .order-meta {
      font-size:.98rem;
      color:#ffd700;
      margin-bottom:.7rem;
      font-weight:500;
    }
    .order-meta i {
      width:22px;
      color:#ffd700;
    }
    .order-requirements {
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
    .status-select {
      background:rgba(255,255,255,0.2);
      border:none;
      color:#fff;
      padding:.5rem 1rem;
      border-radius:50px;
      margin-right:.5rem;
      font-weight:500;
    }
    .status-select:focus {
      outline:none;
      box-shadow:0 0 0 2px #ffd700;
    }
    .status-select:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    .status-select option {
      background: #292a3c;
      color: #fff;
      padding: 10px;
    }
    .btn-update {
      background:linear-gradient(45deg,#ff6b6b,#ffd93d);
      border:none;
      color:#fff;
      font-weight:600;
      padding:.5rem 1.2rem;
      border-radius:50px;
      transition:all .3s;
      cursor: pointer;
    }
    .btn-update:hover:not(:disabled) {
      transform:translateY(-2px);
      box-shadow:0 5px 15px rgba(255,107,107,0.4);
      background:linear-gradient(45deg,#ffd93d,#ff6b6b);
    }
    .btn-update:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }
    .order-actions {
      margin-top:auto;
      display:flex;
      gap:.7rem;
      flex-wrap:wrap;
      align-items:center;
    }
    .client-info {
      position:relative;
      cursor:pointer;
    }
    .client-tooltip {
      display:none;
      position:absolute;
      left:0;
      top:100%;
      background:rgba(44,39,77,.95);
      color:#ffd700;
      border-radius:9px;
      padding:10px 16px;
      font-size:.92rem;
      z-index:99;
      min-width:200px;
      box-shadow:0 6px 20px #2228;
      animation:fadeInUp .4s;
    }
    .client-info:hover .client-tooltip {
      display:block;
    }
    @media (max-width:600px) {
      .orders-grid {gap:1rem;}
      .order-card {padding:1.2rem;}
      .order-actions {flex-direction:column;gap:.5rem;}
    }
    @keyframes fadeInUp {from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
    @keyframes float {0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
    .toast-notify {
      position: fixed;
      bottom: 32px;
      right: 32px;
      background: rgba(44,44,56,.98);
      color: #ffd700;
      font-weight: 600;
      font-size: 1.08rem;
      border-radius: 13px;
      box-shadow: 0 3px 12px #2228;
      padding: 15px 28px;
      z-index: 1200;
      display: none;
      align-items: center;
      animation: fadeInUp .75s;
    }
    .toast-notify.error {
      background: rgba(220,53,69,.98);
      color: #fff;
    }
    @media (max-width:600px) {
      .toast-notify { left:10px; right:10px; bottom:18px; font-size:.98rem; }
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
          <li class="nav-item"><a class="nav-link" href="services.php">My Services</a></li>
          <li class="nav-item"><a class="nav-link" href="add_service.php">Add Service</a></li>
          <li class="nav-item"><a class="nav-link active" href="update_order.php">Orders</a></li>
          <li class="nav-item"><a class="nav-link" href="chats.php">Chats</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <section class="orders-hero">
    <h1>Manage Orders</h1>
    <p>View and update the status of your client orders</p>
    <?php if (isset($_GET['msg'])): ?>
      <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
  </section>

  <section class="orders-grid">
    <?php foreach ($orders as $order): ?>
      <div class="order-card">
        <h5><i class="fas fa-shopping-bag me-2"></i><?= htmlspecialchars($order['service_title']) ?></h5>
        <div class="order-meta">
          <p class="mb-2">
            <i class="fas fa-user me-2"></i>
            <span class="client-info">
              <?= htmlspecialchars($order['client_name']) ?>
              <span class="client-tooltip">
                Email: <?= htmlspecialchars($order['client_email']) ?>
              </span>
            </span>
          </p>
          <p class="mb-2">
            <i class="fas fa-calendar me-2"></i>
            Ordered: <?= date('M j, Y', strtotime($order['created_at'])) ?>
          </p>
          <p class="mb-2">
            <i class="fas fa-clock me-2"></i>
            Updated: <?= date('M j, Y g:i A', strtotime($order['last_updated'] ?? $order['created_at'])) ?>
          </p>
          <p class="mb-2">
            <i class="fas fa-dollar-sign me-2"></i>
            Price: $<?= number_format($order['price'], 2) ?>
          </p>
        </div>
        <div class="order-requirements">
          <i class="fas fa-quote-left me-2 opacity-50"></i>
          <?= nl2br(htmlspecialchars($order['requirements'])) ?>
        </div>
        <?php if ($order['status'] === 'cancelled' && $order['excuse']): ?>
          <div class="alert alert-warning mt-2" style="background:rgba(255,193,7,0.13);color:#ffc107;border:none;">
            <i class="fas fa-info-circle me-1"></i> <strong>Excuse:</strong> <?= nl2br(htmlspecialchars($order['excuse'])) ?>
          </div>
        <?php endif; ?>
        <div class="order-actions">
          <span class="badge-status badge-<?= $order['status'] ?> bg-white">
            <?= ucwords(str_replace('_', ' ', $order['status'])) ?>
          </span>
          <form method="POST" class="d-flex align-items-center" onsubmit="return confirmStatusUpdate(this);">
            <input type="hidden" name="booking_id" value="<?= $order['id'] ?>">
            <select name="new_status" class="status-select me-2" <?= $order['status'] === 'delivered' || $order['status'] === 'cancelled' ? 'disabled' : '' ?>>
              <option value="pending" <?= $order['status']==='pending'?'selected':'' ?>>Pending</option>
              <option value="in_progress" <?= $order['status']==='in_progress'?'selected':'' ?>>In Progress</option>
              <option value="delivered" <?= $order['status']==='delivered'?'selected':'' ?>>Delivered</option>
              <option value="cancelled" <?= $order['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
            </select>
            <button type="submit" class="btn-update me-2" <?= $order['status'] === 'delivered' || $order['status'] === 'cancelled' ? 'disabled' : '' ?>>
              <i class="fas fa-save me-1"></i>Update
            </button>
          </form>
          <?php if (!in_array($order['status'], ['delivered', 'cancelled'])): ?>
            <button class="btn btn-danger btn-sm" onclick="showCancelModal(<?= $order['id'] ?>)"><i class="fas fa-times me-1"></i>Cancel</button>
          <?php endif; ?>
          
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?>
      <div class="text-center opacity-75 py-5">
        <i class="fas fa-inbox fa-3x mb-3"></i>
        <h3>No Orders Yet</h3>
        <p>When clients book your services, they'll appear here.</p>
      </div>
    <?php endif; ?>
  </section>

  <!-- Cancel Modal -->
  <div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
      <form method="POST" class="modal-content" id="cancelForm">
        <div class="modal-header">
          <h5 class="modal-title text-dark"><i class="fas fa-times me-2 text-dark"></i>Cancel Order</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="cancel_booking_id" id="cancel_booking_id">
          <div class="mb-3">
            <label for="excuse_message" class="form-label text-dark">Excuse Message (required)</label>
            <textarea name="excuse_message" id="excuse_message" class="form-control" required rows="4" placeholder="Explain why you are cancelling this order..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger w-100"><i class="fas fa-times me-2"></i>Cancel Order</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Chat Modal -->
  <div class="modal fade chat-modal" id="chatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-comments me-2"></i>Order Chat with <span id="chatClientName"></span></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="chat-messages" id="chatMessages"></div>
          <form id="chatForm" class="chat-input-row" autocomplete="off" onsubmit="return sendChatMessage();">
            <textarea id="chatInput" rows="2" placeholder="Type your message..." required></textarea>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div id="toast" class="toast-notify"></div>

  <div style="height:2rem;"></div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    const nav = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 50);
    });

    // Status update confirmation
    function confirmStatusUpdate(form) {
      const select = form.querySelector('select[name="new_status"]');
      const currentStatus = select.options[select.selectedIndex].text;
      return confirm(`Update order status to "${currentStatus}"?`);
    }

    // Show toast notification
    function showToast(message, isError = false) {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.className = 'toast-notify' + (isError ? ' error' : '');
      toast.style.display = 'flex';
      setTimeout(() => toast.style.display = 'none', 3000);
    }

    <?php if ($error): ?>
      showToast(<?= json_encode($error) ?>, true);
    <?php endif; ?>
    
    <?php if ($success): ?>
      showToast(<?= json_encode($success) ?>);
    <?php endif; ?>

    // Disable invalid status transitions
    document.querySelectorAll('select[name="new_status"]').forEach(select => {
      select.addEventListener('change', function() {
        const form = this.closest('form');
        const currentStatus = form.querySelector('.badge-status').textContent.trim().toLowerCase();
        const newStatus = this.value;
        
        let error = null;
        if (currentStatus === 'pending' && newStatus === 'delivered') {
          error = 'Order must be in progress before marking as delivered';
        }
        
        const submitBtn = form.querySelector('button[type="submit"]');
        if (error) {
          submitBtn.disabled = true;
          showToast(error, true);
        } else {
          submitBtn.disabled = false;
        }
      });
    });

    function showCancelModal(bookingId) {
      document.getElementById('cancel_booking_id').value = bookingId;
      document.getElementById('excuse_message').value = '';
      var modal = new bootstrap.Modal(document.getElementById('cancelModal'));
      modal.show();
    }

    let currentBookingId = null;
    let chatInterval = null;
    function openChatModal(bookingId, clientName) {
      currentBookingId = bookingId;
      document.getElementById('chatClientName').textContent = clientName;
      document.getElementById('chatInput').value = '';
      document.getElementById('chatMessages').innerHTML = '<div class="text-center text-muted">Loading...</div>';
      var modal = new bootstrap.Modal(document.getElementById('chatModal'));
      modal.show();
      fetchChatMessages();
      if (chatInterval) clearInterval(chatInterval);
      chatInterval = setInterval(fetchChatMessages, 3000);
    }
    function fetchChatMessages() {
      if (!currentBookingId) return;
      fetch('?chat=1&booking_id=' + currentBookingId)
        .then(res => res.json())
        .then(messages => {
          const box = document.getElementById('chatMessages');
          if (!messages.length) {
            box.innerHTML = '<div class="text-center text-muted">No messages yet.</div>';
            return;
          }
          box.innerHTML = messages.map(m => `
            <div class="chat-message ${m.role}">
              <span class="sender">${m.username} (${m.role})</span>
              <span class="meta">${new Date(m.sent_at).toLocaleString()}</span>
              <div class="text">${escapeHtml(m.message)}</div>
            </div>
          `).join('');
          box.scrollTop = box.scrollHeight;
        });
    }
    function sendChatMessage() {
      const input = document.getElementById('chatInput');
      const msg = input.value.trim();
      if (!msg) {
        showToast('Cannot send empty message.', true);
        return false;
      }
      input.disabled = true;
      fetch('', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'send_chat=1&booking_id=' + encodeURIComponent(currentBookingId) + '&message=' + encodeURIComponent(msg)
      }).then(() => {
        input.value = '';
        input.disabled = false;
        fetchChatMessages();
      });
      return false;
    }
    function escapeHtml(text) {
      var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
      return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    // Stop polling when modal closes
    document.getElementById('chatModal').addEventListener('hidden.bs.modal', function(){
      if (chatInterval) clearInterval(chatInterval);
      chatInterval = null;
      currentBookingId = null;
    });
  </script>
</body>
</html> 