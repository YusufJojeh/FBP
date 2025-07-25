<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../login.php');
    exit;
}
require_once '../includes/db.php';
$client_id = $_SESSION['user_id'];
// Get all vendors the client has orders with
$vendors = [];
$query = "SELECT DISTINCT v.id, v.display_name, u.email
          FROM bookings b
          JOIN vendors v ON b.vendor_id = v.id
          JOIN users u ON v.user_id = u.id
          WHERE b.client_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $client_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $vendors[] = $row;
}
mysqli_stmt_close($stmt);
// AJAX: fetch chat messages with a vendor (across all bookings)
if (isset($_GET['chat']) && isset($_GET['vendor_id'])) {
    $vendor_id = intval($_GET['vendor_id']);
    $messages = [];
    $query = "SELECT m.*, u.username, u.role
              FROM order_messages m
              JOIN users u ON m.sender_id = u.id
              JOIN bookings b ON m.booking_id = b.id
              WHERE (b.client_id = ? AND b.vendor_id = ?)
              ORDER BY m.sent_at ASC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $client_id, $vendor_id);
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
// AJAX: send chat message
if (isset($_POST['send_chat']) && isset($_POST['vendor_id']) && isset($_POST['message'])) {
    $vendor_id = intval($_POST['vendor_id']);
    $message = trim($_POST['message']);
    $sender_id = $_SESSION['user_id'];
    $file = null;
    // Find a booking between this client and vendor (use the latest)
    $stmt = mysqli_prepare($conn, "SELECT id FROM bookings WHERE client_id = ? AND vendor_id = ? ORDER BY created_at DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'ii', $client_id, $vendor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $booking = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if (!$booking) exit;
    $booking_id = $booking['id'];
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg','jpeg','png','gif','svg','pdf','zip','rar','doc','docx','xls','xlsx','txt'];
        $filename = $_FILES['file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('ordermsg_') . '.' . $ext;
            $upload_path = '../uploads/' . $new_filename;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                $file = $new_filename;
            }
        }
    }
    if ($message !== '' || $file) {
        $stmt = mysqli_prepare($conn, "INSERT INTO order_messages (booking_id, sender_id, message, file) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iiss', $booking_id, $sender_id, $message, $file);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Chats – DesignHub</title>
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
    .chats-hero {
      padding:5rem 1rem 2.5rem;
      text-align:center;
      animation:fadeInUp 1s both;
    }
    .chats-hero h1 {
      font-size:2.2rem;
      font-weight:800;
      text-shadow:0 2px 8px rgba(0,0,0,0.3);
    }
    .chats-hero p {font-size:1.1rem;opacity:.85;}
    .chats-container {
      display:flex;
      max-width:1100px;
      margin:0 auto 2rem auto;
      background:rgba(255,255,255,0.13);
      border-radius:20px;
      box-shadow:0 20px 60px rgba(0,0,0,0.19);
      overflow:hidden;
      min-height:500px;
    }
    .vendors-list {
      width:320px;
      background:rgba(44,39,77,0.97);
      border-right:1px solid rgba(255,255,255,0.08);
      padding:0;
      overflow-y:auto;
    }
    .vendor-item {
      padding:1.1rem 1.2rem;
      border-bottom:1px solid rgba(255,255,255,0.07);
      cursor:pointer;
      transition:background .2s;
      display:flex;
      align-items:center;
      gap:.9rem;
    }
    .vendor-item.active, .vendor-item:hover {
      background:rgba(255,255,255,0.08);
    }
    .vendor-avatar {
      width:44px;height:44px;border-radius:50%;background:#ffd700;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:700;}
    .vendor-info-list {flex:1;}
    .vendor-name {font-weight:700;color:#fff;}
    .vendor-email {font-size:.97rem;color:#ffd700;}
    .last-msg-preview {font-size:.93rem;color:#ccc;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .chat-area {flex:1;display:flex;flex-direction:column;}
    .chat-messages {flex:1;max-height:600px;overflow-y:auto;padding:2rem 2rem 1rem 2rem;}
    .chat-message {margin-bottom:1.2rem;}
    .chat-message .sender {font-weight:700;color:#ffd700;font-size:.98rem;}
    .chat-message .meta {font-size:.85rem;color:#aaa;margin-left:.5rem;}
    .chat-message .text {margin-top:.1rem;font-size:1.05rem;color:#fff;}
    .chat-message.vendor .sender {color:#48d9ad;}
    .chat-message.client .sender {color:#ff6b6b;}
    .chat-message .file-link {display:block;margin-top:.3rem;}
    .chat-message .file-link img {max-width:120px;max-height:90px;border-radius:7px;margin-top:3px;}
    .chat-input-row {display:flex;gap:.5rem;padding:1.2rem 2rem 1.2rem 2rem;background:rgba(255,255,255,0.07);}
    .chat-input-row textarea {flex:1;border-radius:12px;border:none;padding:.7rem;resize:none;background:#23223a;color:#fff;}
    .chat-input-row input[type=file] {display:none;}
    .chat-input-row label[for=chatFile] {background:#ffd700;color:#253053;border-radius:50%;width:42px;height:42px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:1.2rem;margin-right:.5rem;}
    .chat-input-row label[for=chatFile]:hover {background:#ff6b6b;color:#fff;}
    .chat-input-row button {border-radius:50px;background:linear-gradient(45deg,#ff6b6b,#ffd93d);color:#fff;border:none;font-weight:600;padding:.6rem 1.5rem;}
    .chat-input-row button:disabled {opacity:.6;}
    @media (max-width:900px) {
      .chats-container {flex-direction:column;}
      .vendors-list {width:100%;max-height:180px;min-height:unset;display:flex;overflow-x:auto;overflow-y:hidden;}
      .vendor-item {flex-direction:column;align-items:center;min-width:180px;min-height:100px;}
      .chat-area {min-height:320px;}
    }
    @media (max-width:600px) {
      .chat-messages {padding:1rem .5rem .5rem .5rem;}
      .chat-input-row {padding:.7rem .5rem;}
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg fixed-top py-2">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php"><i class="fas fa-palette me-2"></i>DesignHub</a>
      <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
      <div class="collapse navbar-collapse" id="nav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="bookings.php">Bookings</a></li>
          <li class="nav-item"><a class="nav-link active" href="chats.php">Chats</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <section class="chats-hero">
    <h1>Chats</h1>
    <p>Chat with your vendors in real time</p>
  </section>
  <div class="chats-container mt-4">
    <div class="vendors-list" id="vendorsList">
      <?php foreach ($vendors as $i => $vendor): ?>
        <div class="vendor-item<?= $i===0 ? ' active' : '' ?>" data-vendor-id="<?= $vendor['id'] ?>" onclick="selectVendor(this, <?= $vendor['id'] ?>, '<?= htmlspecialchars($vendor['display_name'], ENT_QUOTES) ?>')">
          <div class="vendor-avatar"><i class="fas fa-user-tie"></i></div>
          <div class="vendor-info-list">
            <div class="vendor-name"><?= htmlspecialchars($vendor['display_name']) ?></div>
            <div class="vendor-email"><?= htmlspecialchars($vendor['email']) ?></div>
            <div class="last-msg-preview" id="preview-<?= $vendor['id'] ?>">&nbsp;</div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($vendors)): ?>
        <div class="text-center text-muted p-4">No vendors yet.</div>
      <?php endif; ?>
    </div>
    <div class="chat-area">
      <div class="chat-messages" id="chatMessages"></div>
      <form id="chatForm" class="chat-input-row" autocomplete="off" enctype="multipart/form-data" onsubmit="return sendChatMessage();">
        <textarea id="chatInput" rows="2" placeholder="Type your message..."></textarea>
        <input type="file" id="chatFile" name="file" accept="image/*,.pdf,.zip,.rar,.doc,.docx,.xls,.xlsx,.txt">
        <label for="chatFile" title="Send File"><i class="fas fa-paperclip"></i></label>
        <button type="submit"><i class="fas fa-paper-plane"></i></button>
      </form>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let currentVendorId = <?= count($vendors) ? $vendors[0]['id'] : 'null' ?>;
    let chatInterval = null;
    function selectVendor(el, vendorId, vendorName) {
      document.querySelectorAll('.vendor-item').forEach(item => item.classList.remove('active'));
      el.classList.add('active');
      currentVendorId = vendorId;
      document.getElementById('chatInput').value = '';
      document.getElementById('chatFile').value = '';
      document.getElementById('chatMessages').innerHTML = '<div class="text-center text-muted">Loading...</div>';
      fetchChatMessages();
      if (chatInterval) clearInterval(chatInterval);
      chatInterval = setInterval(fetchChatMessages, 3000);
    }
    function fetchChatMessages() {
      if (!currentVendorId) return;
      fetch('?chat=1&vendor_id=' + currentVendorId)
        .then(res => res.json())
        .then(messages => {
          const box = document.getElementById('chatMessages');
          if (!messages.length) {
            box.innerHTML = '<div class="text-center text-muted">No messages yet.</div>';
            document.getElementById('preview-' + currentVendorId).textContent = '';
            return;
          }
          box.innerHTML = messages.map(m => `
            <div class="chat-message ${m.role}">
              <span class="sender">${m.username} (${m.role})</span>
              <span class="meta">${new Date(m.sent_at).toLocaleString()}</span>
              <div class="text">${escapeHtml(m.message)}</div>
              ${m.file ? renderFileLink(m.file) : ''}
            </div>
          `).join('');
          box.scrollTop = box.scrollHeight;
          // Update last message preview
          const last = messages[messages.length-1];
          document.getElementById('preview-' + currentVendorId).textContent = (last.message ? last.message : (last.file ? '[File]' : ''));
        });
    }
    function sendChatMessage() {
      if (!currentVendorId) return false;
      const input = document.getElementById('chatInput');
      const fileInput = document.getElementById('chatFile');
      const msg = input.value.trim();
      if (!msg && !fileInput.files[0]) {
        alert('Cannot send empty message.');
        return false;
      }
      const formData = new FormData();
      formData.append('send_chat', '1');
      formData.append('vendor_id', currentVendorId);
      formData.append('message', msg);
      if (fileInput.files[0]) {
        formData.append('file', fileInput.files[0]);
      }
      input.disabled = true;
      fileInput.disabled = true;
      fetch('', {
        method: 'POST',
        body: formData
      }).then(() => {
        input.value = '';
        fileInput.value = '';
        input.disabled = false;
        fileInput.disabled = false;
        fetchChatMessages();
      });
      return false;
    }
    function renderFileLink(filename) {
      const ext = filename.split('.').pop().toLowerCase();
      if (["jpg","jpeg","png","gif","svg"].includes(ext)) {
        return `<a href="../uploads/${filename}" target="_blank" class="file-link"><img src="../uploads/${filename}" alt="Image"></a>`;
      } else {
        return `<a href="../uploads/${filename}" target="_blank" class="file-link"><i class="fas fa-file me-1"></i>Download File</a>`;
      }
    }
    function escapeHtml(text) {
      if (!text) return '';
      var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
      return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    // Initial load
    if (currentVendorId) {
      fetchChatMessages();
      chatInterval = setInterval(fetchChatMessages, 3000);
    }
    // Navbar scroll effect
    const nav = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 50);
    });
  </script>
</body>
</html> 