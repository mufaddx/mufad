<?php
require_once __DIR__ . '/includes/functions.php';

$orderId = (int)($_GET['order_id'] ?? 0);
$order   = null;

if ($orderId && $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND payment_status = 'unpaid' LIMIT 1");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
    } catch (Exception $e) {}
}
if (!$order) redirect('/');

// Handle Razorpay payment success callback
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['razorpay_payment_id'])) {
    $razorpayPaymentId = sanitizeInput($_POST['razorpay_payment_id'] ?? '');
    $razorpayOrderId   = sanitizeInput($_POST['razorpay_order_id'] ?? '');
    $razorpaySignature = sanitizeInput($_POST['razorpay_signature'] ?? '');

    if ($razorpayPaymentId && $pdo) {
        try {
            $pdo->prepare("
                UPDATE orders
                SET payment_status='paid', razorpay_payment_id=?, razorpay_order_id=?, order_status='Confirmed'
                WHERE id=?
            ")->execute([$razorpayPaymentId, $razorpayOrderId, $orderId]);

            redirect('/order-success.php?order=' . $order['order_number']);
        } catch (Exception $e) {
            error_log('Payment update error: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Complete Payment — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-5">
      <div class="ih-card p-5 text-center">
        <div style="font-size:3rem;margin-bottom:16px">💳</div>
        <h3 class="fw-800 mb-2">Complete Your Payment</h3>
        <p class="text-muted mb-4">Order #<?= htmlspecialchars($order['order_number']) ?></p>

        <div style="background:#f9fafb;border-radius:12px;padding:20px;margin-bottom:24px;text-align:left">
          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted small">Subtotal</span>
            <span class="fw-600">₹<?= number_format($order['total_amount']) ?></span>
          </div>
          <?php if ($order['discount'] > 0): ?>
            <div class="d-flex justify-content-between mb-2 text-success">
              <span class="small">Discount</span>
              <span class="fw-600">−₹<?= number_format($order['discount']) ?></span>
            </div>
          <?php endif; ?>
          <hr style="margin:8px 0">
          <div class="d-flex justify-content-between">
            <span class="fw-700">Total to Pay</span>
            <span class="fw-800 fs-5 text-gold">₹<?= number_format($order['final_amount']) ?></span>
          </div>
        </div>

        <!-- Razorpay Payment Button -->
        <form method="POST" id="paymentForm">
          <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
          <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
          <input type="hidden" name="razorpay_signature" id="razorpay_signature">

          <button id="rzp-button" class="btn btn-gold btn-lg w-100 fw-700 mb-3">
            <i class="bi bi-credit-card me-2"></i>Pay ₹<?= number_format($order['final_amount']) ?> Now
          </button>
        </form>

        <a href="/cart.php" class="btn btn-outline-secondary w-100 mb-3">← Back</a>

        <div class="text-muted small">
          <i class="bi bi-shield-check me-1"></i>
          Payments secured by Razorpay. We accept UPI, Cards & Net Banking.
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
  key: '<?= RAZORPAY_KEY_ID ?>',
  amount: <?= (int)($order['final_amount'] * 100) ?>,
  currency: 'INR',
  name: '<?= SITE_NAME ?>',
  description: 'Order #<?= $order['order_number'] ?>',
  order_id: '<?= htmlspecialchars($order['razorpay_order_id'] ?? '') ?>',
  handler: function(response) {
    document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
    document.getElementById('razorpay_order_id').value   = response.razorpay_order_id || '';
    document.getElementById('razorpay_signature').value  = response.razorpay_signature || '';
    document.getElementById('paymentForm').submit();
  },
  prefill: {
    name:  '<?= addslashes($order['notes'] ? (explode(' | ', $order['notes'])[0] ?? '') : '') ?>',
  },
  theme: { color: '#c9a84c' },
  modal: {
    ondismiss: function() {
      document.getElementById('rzp-button').disabled = false;
    }
  }
};
document.getElementById('rzp-button').onclick = function(e) {
  e.preventDefault();
  this.disabled = true;
  var rzp = new Razorpay(options);
  rzp.open();
};
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
