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
    die('Vendor profile not found.');
}
$vendor_id = $vendor['id'];

// Get service ID from query
$service_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$service_id) die('Invalid service ID.');

// Fetch service data
$stmt = mysqli_prepare($conn, "SELECT * FROM services WHERE id = ? AND vendor_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $service_id, $vendor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$service = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
if (!$service) die('Service not found or not yours.');

// Service chat backend logic
if (isset($_GET['service_chat']) && isset($_GET['service_id'])) {
    $service_id = intval($_GET['service_id']);
    $messages = [];
    $stmt = mysqli_prepare($conn, "SELECT m.*, u.username, u.role FROM service_messages m JOIN users u ON m.sender_id = u.id WHERE m.service_id = ? ORDER BY m.sent_at ASC");
    mysqli_stmt_bind_param($stmt, 'i', $service_id);
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
if (isset($_POST['send_service_chat']) && isset($_POST['service_id'])) {
    $service_id = intval($_POST['service_id']);
    $sender_id = $_SESSION['user_id'];
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $file = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg','jpeg','png','gif','svg','pdf','zip','rar','doc','docx','xls','xlsx','txt'];
        $filename = $_FILES['file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('svcmsg_') . '.' . $ext;
            $upload_path = '../uploads/' . $new_filename;
            if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                $file = $new_filename;
            }
        }
    }
    if ($message !== '' || $file) {
        $stmt = mysqli_prepare($conn, "INSERT INTO service_messages (service_id, sender_id, message, file) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iiss', $service_id, $sender_id, $message, $file);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $category = trim($_POST['category']);
    $status = $_POST['status'] === 'inactive' ? 'inactive' : 'active';
    $stmt = mysqli_prepare($conn, "UPDATE services SET title=?, description=?, price=?, category=?, status=? WHERE id=? AND vendor_id=?");
    mysqli_stmt_bind_param($stmt, 'ssdssii', $title, $desc, $price, $category, $status, $service_id, $vendor_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header('Location: services.php?msg=Service+updated!');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Service – DesignHub</title>
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
    .edit-hero {
      padding:5rem 1rem 2.5rem;
      text-align:center;
      animation:fadeInUp 1s both;
    }
    .edit-hero h1 {
      font-size:2.2rem;
      font-weight:800;
      text-shadow:0 2px 8px rgba(0,0,0,0.3);
    }
    .edit-hero p {font-size:1.1rem;opacity:.85;}
    .edit-card {
      background:rgba(255,255,255,0.13);
      backdrop-filter:blur(12px);
      border-radius:20px;
      box-shadow:0 20px 60px rgba(0,0,0,0.19);
      padding:2.5rem 2rem 2rem 2rem;
      max-width:500px;
      margin:2.5rem auto 0;
      animation:float 7s ease-in-out infinite;
    }
    .edit-card h3 {
      font-size:1.5rem;
      font-weight:700;
      margin-bottom:1.2rem;
      color:#ffd700;
    }
    .form-label { color: #ffd700; font-weight:500; }
    .form-control, .form-select {
      background:rgba(255,255,255,0.2);
      border: none;
      color: #fff;
      padding: 12px 15px;
      border-radius: 50px;
      transition: background 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
      background: rgba(255,255,255,0.3);
      outline: none;
      box-shadow: none;
    }
    .btn-update {
      width: 100%;
      padding: 12px;
      font-weight: 600;
      border-radius: 50px;
      background: linear-gradient(45deg, #ff6b6b, #ffd93d);
      border: none;
      color: #fff;
      margin-top: 20px;
      box-shadow: 0 10px 30px rgba(255,107,107,0.3);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .btn-update:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 40px rgba(255,107,107,0.4);
    }
    @keyframes fadeInUp {from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
    @keyframes float {0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
    .chat-btn { background: #ffd700; color: #253053; border-radius: 50px; font-weight: 600; border: none; padding: .4rem 1.2rem; margin-left: .5rem; }
    .chat-btn:hover { background: #ff6b6b; color: #fff; }
    .chat-modal .modal-content { background: #292a3c; color: #fff; border-radius: 18px; }
    .chat-messages { max-height: 340px; overflow-y: auto; background: #23223a; border-radius: 12px; padding: 1rem; margin-bottom: 1rem; }
    .chat-message { margin-bottom: .7rem; }
    .chat-message .sender { font-weight: 700; color: #ffd700; font-size: .98rem; }
    .chat-message .meta { font-size: .85rem; color: #aaa; margin-left: .5rem; }
    .chat-message .text { margin-top: .1rem; font-size: 1.05rem; color: #fff; }
    .chat-message.vendor .sender { color: #48d9ad; }
    .chat-message.client .sender { color: #ff6b6b; }
    .chat-input-row { display: flex; gap: .5rem; }
    .chat-input-row textarea { flex: 1; border-radius: 12px; border: none; padding: .7rem; resize: none; background: #23223a; color: #fff; }
    .chat-input-row input[type=file] { display:none; }
    .chat-input-row label[for=serviceChatFile] { background: #ffd700; color: #253053; border-radius: 50%; width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 1.2rem; margin-right: .5rem; }
    .chat-input-row label[for=serviceChatFile]:hover { background: #ff6b6b; color: #fff; }
    .chat-input-row button { border-radius: 50px; background: linear-gradient(45deg,#ff6b6b,#ffd93d); color: #fff; border: none; font-weight: 600; padding: .6rem 1.5rem; }
    .chat-input-row button:disabled { opacity: .6; }
    .chat-message .file-link { display: block; margin-top: .3rem; }
    .chat-message .file-link img { max-width: 120px; max-height: 90px; border-radius: 7px; margin-top: 3px; }
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
          <li class="nav-item"><a class="nav-link" href="update_order.php">Orders</a></li>
          <li class="nav-item"><a class="nav-link" href="chats.php">Chats</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <section class="edit-hero">
    <h1>Edit Service</h1>
    <p>Update your service details below</p>
  </section>

  <div class="edit-card shadow-lg">
    <h3><i class="fas fa-edit me-2"></i>Edit Service</h3>
    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($service['title']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" required style="border-radius:18px;min-height:90px;resize:vertical;"><?= htmlspecialchars($service['description']) ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Price</label>
        <input type="number" name="price" class="form-control" required min="0" step="0.01" value="<?= htmlspecialchars($service['price']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" required value="<?= htmlspecialchars($service['category']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
          <option value="active" <?= $service['status']==='active'?'selected':'' ?>>Active</option>
          <option value="inactive" <?= $service['status']==='inactive'?'selected':'' ?>>Inactive</option>
        </select>
      </div>
      <button type="submit" class="btn-update"><i class="fas fa-save me-2"></i>Update Service</button>
    </form>
    <button class="chat-btn mt-3" onclick="openServiceChatModal(<?= $service['id'] ?>, '<?= htmlspecialchars($service['title'], ENT_QUOTES) ?>')"><i class="fas fa-comments me-1"></i>Service Chat</button>
  </div>

  <div style="height:2rem;"></div>

  <!-- Service Chat Modal -->
  <div class="modal fade chat-modal" id="serviceChatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="fas fa-comments me-2"></i>Service Chat: <span id="serviceChatTitle"></span></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="chat-messages" id="serviceChatMessages"></div>
          <form id="serviceChatForm" class="chat-input-row" autocomplete="off" enctype="multipart/form-data" onsubmit="return sendServiceChatMessage();">
            <textarea id="serviceChatInput" rows="2" placeholder="Type your message..."></textarea>
            <input type="file" id="serviceChatFile" name="file" accept="image/*,.pdf,.zip,.rar,.doc,.docx,.xls,.xlsx,.txt">
            <label for="serviceChatFile" title="Send File"><i class="fas fa-paperclip"></i></label>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Navbar scroll effect
    const nav = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 50);
    });

    let currentServiceId = <?= $service['id'] ?>;
    let serviceChatInterval = null;
    function openServiceChatModal(serviceId, serviceTitle) {
      document.getElementById('serviceChatTitle').textContent = serviceTitle;
      document.getElementById('serviceChatInput').value = '';
      document.getElementById('serviceChatFile').value = '';
      document.getElementById('serviceChatMessages').innerHTML = '<div class="text-center text-muted">Loading...</div>';
      var modal = new bootstrap.Modal(document.getElementById('serviceChatModal'));
      modal.show();
      fetchServiceChatMessages();
      if (serviceChatInterval) clearInterval(serviceChatInterval);
      serviceChatInterval = setInterval(fetchServiceChatMessages, 3000);
    }
    function fetchServiceChatMessages() {
      fetch('?service_chat=1&service_id=' + currentServiceId)
        .then(res => res.json())
        .then(messages => {
          const box = document.getElementById('serviceChatMessages');
          if (!messages.length) {
            box.innerHTML = '<div class="text-center text-muted">No messages yet.</div>';
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
        });
    }
    function sendServiceChatMessage() {
      const input = document.getElementById('serviceChatInput');
      const fileInput = document.getElementById('serviceChatFile');
      const msg = input.value.trim();
      if (!msg && !fileInput.files[0]) {
        alert('Cannot send empty message.');
        return false;
      }
      const formData = new FormData();
      formData.append('send_service_chat', '1');
      formData.append('service_id', currentServiceId);
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
        fetchServiceChatMessages();
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
    document.getElementById('serviceChatModal').addEventListener('hidden.bs.modal', function(){
      if (serviceChatInterval) clearInterval(serviceChatInterval);
      serviceChatInterval = null;
    });
  </script>
</body>
</html> 