<?php
require_once __DIR__ . '/includes/functions.php';

$orderNo = sanitizeInput($_GET['order'] ?? '');
$order   = null;

if ($orderNo && $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
        $stmt->execute([$orderNo]);
        $order = $stmt->fetch();
    } catch (Exception $e) {}
}
if (!$order) redirect('/');

// Extract customer phone from notes field
$notesStr    = $order['notes'] ?? '';
$customerPhone = '';
if (preg_match('/Phone: ([+\d\s]+)/', $notesStr, $m)) {
    $customerPhone = trim($m[1]);
}

$pageTitle = 'Order Confirmed — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6 text-center">
      <div class="ih-card p-5">
        <div style="margin-bottom:18px;">
          <img src="/assets/images/logo.jpg" alt="Incredible Heights" style="height:72px;width:auto;object-fit:contain;filter:drop-shadow(0 3px 14px rgba(201,168,76,.35));">
        </div>
        <div style="font-size:3rem;margin-bottom:12px">✅</div>
        <h2 style="font-family:'Playfair Display',serif;font-weight:900;color:#166534;margin-bottom:6px;">Order Confirmed!</h2>
        <p class="text-muted mb-3">Thank you for choosing Incredible Heights.</p>
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px;margin-bottom:20px">
          <div class="fw-700 text-success">Order #<?= htmlspecialchars($order['order_number']) ?></div>
          <div class="text-muted small mt-1">Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></div>
        </div>
        <div class="row g-3 mb-4 text-start">
          <div class="col-6">
            <div class="text-muted small">Total Amount</div>
            <div class="fw-700 fs-5">₹<?= number_format($order['final_amount']) ?></div>
          </div>
          <div class="col-6">
            <div class="text-muted small">Payment</div>
            <div class="fw-700"><?= $order['payment_method'] === 'online' ? '💳 Online' : '🏗️ Pay After Work' ?></div>
          </div>
          <div class="col-6">
            <div class="text-muted small">Order Status</div>
            <div class="fw-700"><?= htmlspecialchars($order['order_status']) ?></div>
          </div>
          <div class="col-6">
            <div class="text-muted small">Payment Status</div>
            <div class="fw-700"><?= ucfirst($order['payment_status']) ?></div>
          </div>
        </div>
        <div class="alert alert-info text-start small">
          <i class="bi bi-whatsapp me-1 text-success"></i>
          Our team will contact you within <strong>30 minutes</strong> to confirm your order.
        </div>
        <div class="d-flex gap-2 flex-wrap justify-content-center">
          <?php if (isLoggedIn()): ?>
            <a href="/user/orders.php" class="btn btn-gold px-4">View My Orders</a>
          <?php endif; ?>
          <a href="/" class="btn btn-outline-secondary px-4">Continue Shopping</a>
          <a href="https://wa.me/<?= SITE_WHATSAPP ?>?text=Hi!+My+order+number+is+<?= urlencode($order['order_number']) ?>"
             target="_blank" class="btn btn-success px-4">
            <i class="bi bi-whatsapp me-1"></i>WhatsApp Us
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
