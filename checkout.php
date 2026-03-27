<?php
// CHECKOUT — INCREDIBLE HEIGHTS
require_once __DIR__ . '/includes/functions.php';

$cartItems = getCartItems();
if (empty($cartItems)) {
    setFlash('warning', 'Your cart is empty. Please add items before checkout.');
    redirect('/cart.php');
}

$errors   = [];
$coupon   = $_SESSION['coupon'] ?? null;
$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems));
$discount = $coupon['discount'] ?? 0;
$gst      = round(($subtotal - $discount) * GST_PERCENT / 100);
$total    = $subtotal - $discount + $gst;

// Enrich cart items with names
foreach ($cartItems as &$item) {
    $item['name'] = 'Item'; $item['icon'] = '📦';
    if (!$pdo) continue;
    try {
        $tbl = ($item['item_type'] === 'product') ? 'products' : 'services';
        $r   = $pdo->prepare("SELECT name, icon FROM $tbl WHERE id=?");
        $r->execute([$item['item_id']]);
        $d = $r->fetch();
        if ($d) { $item['name'] = $d['name']; $item['icon'] = $d['icon'] ?? '📦'; }
    } catch (Exception $e) {}
}
unset($item);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf'] ?? '')) {
        $errors[] = 'Security error. Please refresh the page.';
    } else {
        $cName   = sanitizeInput($_POST['name']    ?? '');
        $phone   = sanitizeInput($_POST['phone']   ?? '');
        $email   = sanitizeInput($_POST['email']   ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $city    = sanitizeInput($_POST['city']    ?? '');
        $state   = sanitizeInput($_POST['state']   ?? '');
        $pincode = sanitizeInput($_POST['pincode'] ?? '');
        $payMode = sanitizeInput($_POST['payment_mode'] ?? 'pay_after_work');
        $notes   = sanitizeInput($_POST['notes']   ?? '');

        if (!$cName)   $errors[] = 'Full name is required.';
        if (!$phone)   $errors[] = 'Phone number is required.';
        if (!$address) $errors[] = 'Address is required.';
        if (!$city)    $errors[] = 'City is required.';

        if (empty($errors) && $pdo) {
            try {
                $userId  = isLoggedIn() ? $_SESSION['user_id'] : null;
                $orderNo = generateOrderNumber();
                $pmMethod = ($payMode === 'razorpay') ? 'online' : 'pay_after_work';
                $addressId = null;
                if ($userId) {
                    $pdo->prepare("INSERT INTO user_addresses (user_id, name, phone, address, city, state, pincode) VALUES (?,?,?,?,?,?,?)")
                        ->execute([$userId, $cName, $phone, $address, $city, $state, $pincode]);
                    $addressId = $pdo->lastInsertId();
                }
                $fullNotes = "Customer: $cName | Phone: $phone | Email: $email | Address: $address, $city, $state $pincode" . ($notes ? " | Notes: $notes" : "");
                $pdo->prepare("INSERT INTO orders (order_number, user_id, name, phone, email, address, city, state, pincode, total_amount, discount, gst_amount, final_amount, coupon_code, payment_method, payment_status, order_status, notes, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'unpaid','Pending',?,NOW())")
                    ->execute([$orderNo, $userId, $cName, $phone, $email, $address, $city, $state, $pincode, $subtotal, $discount, $gst, $total, $coupon['code'] ?? null, $pmMethod, $notes]);
                $orderId = $pdo->lastInsertId();
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, item_type, item_id, name, quantity, price, subtotal) VALUES (?,?,?,?,?,?,?)");
                foreach ($cartItems as $ci) {
                    $stmt->execute([$orderId, $ci['item_type'], $ci['item_id'], $ci['name'], $ci['quantity'], $ci['price'], $ci['price']*$ci['quantity']]);
                }
                if ($coupon) {
                    $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")->execute([$coupon['id']]);
                }
                if ($userId) { $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]); }
                else         { $pdo->prepare("DELETE FROM cart WHERE session_id = ?")->execute([session_id()]); }
                unset($_SESSION['coupon']);
                if ($payMode === 'razorpay') { redirect('/payment.php?order_id=' . $orderId); }
                else                         { redirect('/order-success.php?order=' . $orderNo); }
            } catch (Exception $e) {
                error_log('Checkout error: ' . $e->getMessage());
                $errors[] = 'Could not place order. Please try again or call us.';
            }
        }
    }
}

$states    = getIndianStates();
$user      = currentUser();
$pageTitle = 'Checkout — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
:root {
  --or:#e8560a; --or-lt:#fff5f0; --or-b:rgba(232,86,10,.18);
  --bl:#1565c0; --bl-lt:#f0f5ff; --bl-b:rgba(21,101,192,.18);
  --gr:#2e7d32; --gr-lt:#f0faf0; --gr-b:rgba(46,125,50,.2);
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb; --white:#fff;
}

/* ── PAGE HEADER ── */
.co-hdr {
  background: var(--white);
  border-bottom: 1.5px solid var(--border);
  padding: 16px 5%;
  box-shadow: 0 2px 10px rgba(26,35,50,.05);
}
.co-hdr-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
}
.co-hdr-icon {
  width: 38px; height: 38px; border-radius: 11px;
  background: var(--or-lt); border: 1.5px solid var(--or-b);
  display: flex; align-items: center; justify-content: center;
  color: var(--or); font-size: 1rem; flex-shrink: 0;
}
.co-hdr-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.4rem; font-weight: 900; color: var(--txt);
}
.co-hdr-crumb {
  font-size: .76rem; color: var(--hint); margin-left: auto;
}
.co-hdr-crumb a { color: var(--hint); text-decoration: none; }
.co-hdr-crumb a:hover { color: var(--or); }

/* Progress steps */
.co-steps {
  display: flex; align-items: center; gap: 0;
  margin-left: auto;
}
.co-step {
  display: flex; align-items: center; gap: 6px;
  font-size: .74rem; font-weight: 700; color: var(--hint);
  padding: 5px 12px; border-radius: 20px;
}
.co-step.done  { color: var(--gr); }
.co-step.active { color: var(--or); background: var(--or-lt); border: 1px solid var(--or-b); }
.co-step-dot {
  width: 18px; height: 18px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .6rem; border: 2px solid currentColor;
}
.co-step.done  .co-step-dot { background: var(--gr); color: #fff; border-color: var(--gr); }
.co-step.active .co-step-dot { background: var(--or); color: #fff; border-color: var(--or); }
.co-step-arrow { color: var(--border); font-size: .7rem; margin: 0 2px; }

/* ── PAGE AREA ── */
.co-page { background: var(--bg); padding: 24px 5% 60px; }
.co-page-inner { max-width: 1200px; margin: 0 auto; }

/* Error alert */
.co-alert-err {
  background: #fef2f2; color: #991b1b;
  border: 1.5px solid #fecaca; border-radius: 12px;
  padding: 14px 18px; margin-bottom: 20px; font-size: .88rem;
}

/* ── LAYOUT ── */
.co-layout {
  display: grid; grid-template-columns: 1fr;
  gap: 20px; align-items: start;
}
@media(min-width:992px) { .co-layout { grid-template-columns: 1fr 360px; } }

/* ── FORM CARDS ── */
.co-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 18px; padding: 24px 22px;
  box-shadow: 0 2px 12px rgba(26,35,50,.05);
  margin-bottom: 16px;
}
.co-card:last-child { margin-bottom: 0; }
.co-card-title {
  font-size: .9rem; font-weight: 800; color: var(--txt);
  margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
}
.co-card-title i { color: var(--or); font-size: 1rem; }

/* Form fields */
.co-label {
  display: block; font-size: .8rem; font-weight: 700; color: var(--txt); margin-bottom: 6px;
}
.co-label span { color: #ef4444; }
.co-input, .co-select, .co-textarea {
  width: 100%; border: 1.5px solid var(--border); border-radius: 10px;
  padding: 10px 14px; font-size: .87rem;
  font-family: 'DM Sans', sans-serif; color: var(--txt);
  background: var(--bg); outline: none;
  transition: border-color .18s, box-shadow .18s, background .18s;
}
.co-input:focus, .co-select:focus, .co-textarea:focus {
  border-color: var(--or); box-shadow: 0 0 0 3px rgba(232,86,10,.10); background: #fff;
}
.co-input::placeholder, .co-textarea::placeholder { color: #c4cdd6; }
.co-textarea { resize: vertical; min-height: 80px; }

/* 2-col row */
.co-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.co-row3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; }
@media(max-width:575px) {
  .co-row2 { grid-template-columns: 1fr; }
  .co-row3 { grid-template-columns: 1fr 1fr; }
}
.co-field { display: flex; flex-direction: column; }

/* Payment options */
.pay-option {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 16px 16px; border-radius: 13px;
  border: 2px solid var(--border); cursor: pointer;
  transition: all .18s; background: var(--bg);
}
.pay-option:hover { border-color: var(--or-b); background: var(--or-lt); }
.pay-option.selected { border-color: var(--or); background: var(--or-lt); }
.pay-option input[type="radio"] { margin-top: 2px; accent-color: var(--or); flex-shrink: 0; }
.pay-option-title { font-size: .88rem; font-weight: 800; color: var(--txt); margin-bottom: 3px; }
.pay-option-sub   { font-size: .75rem; color: var(--light); line-height: 1.4; }
.pay-option-icon  { font-size: 1.5rem; flex-shrink: 0; }

/* ── ORDER SUMMARY ── */
.co-summary {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 18px; padding: 22px;
  box-shadow: 0 2px 12px rgba(26,35,50,.05);
  position: sticky; top: 80px;
}
.co-summary-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.05rem; font-weight: 800; color: var(--txt); margin-bottom: 18px;
  display: flex; align-items: center; gap: 8px;
}
.co-summary-title i { color: var(--or); }

/* Item row */
.co-sum-item {
  display: flex; align-items: center; justify-content: space-between;
  gap: 8px; padding: 8px 0; border-bottom: 1px solid var(--border);
  font-size: .83rem;
}
.co-sum-item:last-of-type { border-bottom: none; }
.co-sum-item-left {
  display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0;
}
.co-sum-item-icon {
  width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
  background: var(--or-lt); border: 1px solid var(--or-b);
  display: flex; align-items: center; justify-content: center; font-size: .9rem;
}
.co-sum-item-name { font-weight: 600; color: var(--txt); line-height: 1.3; font-size: .8rem; }
.co-sum-item-qty  { font-size: .7rem; color: var(--hint); }
.co-sum-item-price { font-weight: 800; color: var(--txt); white-space: nowrap; }

/* Totals */
.co-sum-divider { border: none; border-top: 1.5px solid var(--border); margin: 14px 0 10px; }
.co-sum-row {
  display: flex; align-items: center; justify-content: space-between;
  font-size: .83rem; margin-bottom: 8px;
}
.co-sum-row-lbl { color: var(--mid); }
.co-sum-row-val { font-weight: 700; color: var(--txt); }
.co-sum-row-disc { color: #16a34a; font-weight: 700; }
.co-sum-row-gst  { color: var(--mid); }
.co-sum-total-row {
  display: flex; align-items: center; justify-content: space-between;
  margin: 14px 0 4px;
}
.co-sum-total-lbl { font-size: .92rem; font-weight: 800; color: var(--txt); }
.co-sum-total-val { font-size: 1.3rem; font-weight: 900; color: var(--or); }
.co-sum-gst-note  { font-size: .7rem; color: var(--hint); margin-bottom: 18px; }

/* Buttons */
.btn-place-order {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; background: linear-gradient(135deg, #f0a070, var(--or));
  color: #fff; font-weight: 800; font-size: .95rem;
  padding: 14px; border-radius: 12px; border: none; cursor: pointer;
  box-shadow: 0 4px 16px rgba(232,86,10,.3); transition: all .22s;
  margin-bottom: 10px;
}
.btn-place-order:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(232,86,10,.4); }
.btn-back-cart {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  width: 100%; background: var(--bg); color: var(--mid);
  font-weight: 700; font-size: .83rem;
  padding: 11px; border-radius: 11px; text-decoration: none;
  border: 1.5px solid var(--border); transition: all .15s;
}
.btn-back-cart:hover { border-color: var(--or); color: var(--or); background: var(--or-lt); }

/* Secure badge */
.co-secure {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  font-size: .73rem; color: var(--hint); font-weight: 600; margin-top: 12px;
}
.co-secure i { color: var(--gr); }
</style>

<!-- ── PAGE HEADER ── -->
<div class="co-hdr">
  <div class="co-hdr-inner">
    <div class="co-hdr-icon"><i class="bi bi-bag-check-fill"></i></div>
    <div class="co-hdr-title">Checkout</div>

    <!-- Steps -->
    <div class="co-steps">
      <div class="co-step done">
        <div class="co-step-dot"><i class="bi bi-check-lg"></i></div> Cart
      </div>
      <span class="co-step-arrow">›</span>
      <div class="co-step active">
        <div class="co-step-dot">2</div> Details
      </div>
      <span class="co-step-arrow">›</span>
      <div class="co-step">
        <div class="co-step-dot">3</div> Confirm
      </div>
    </div>
  </div>
</div>

<!-- ── MAIN ── -->
<div class="co-page">
  <div class="co-page-inner">

    <?php if (!empty($errors)): ?>
    <div class="co-alert-err">
      <?php foreach ($errors as $e): ?>
        <div><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($e) ?></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <input type="hidden" name="csrf" value="<?= generateCSRF() ?>">

      <div class="co-layout">

        <!-- LEFT -->
        <div>
          <!-- Delivery Details -->
          <div class="co-card">
            <div class="co-card-title">
              <i class="bi bi-geo-alt-fill"></i> Delivery Details
            </div>

            <div class="co-row2" style="margin-bottom:14px;">
              <div class="co-field">
                <label class="co-label">Full Name <span>*</span></label>
                <input type="text" name="name" class="co-input" required
                       placeholder="Your full name"
                       value="<?= htmlspecialchars($user['name'] ?? $_POST['name'] ?? '') ?>">
              </div>
              <div class="co-field">
                <label class="co-label">Phone Number <span>*</span></label>
                <input type="tel" name="phone" class="co-input" required
                       placeholder="+91 XXXXX XXXXX"
                       value="<?= htmlspecialchars($user['phone'] ?? $_POST['phone'] ?? '') ?>">
              </div>
            </div>

            <div class="co-field" style="margin-bottom:14px;">
              <label class="co-label">Email Address</label>
              <input type="email" name="email" class="co-input"
                     placeholder="your@email.com"
                     value="<?= htmlspecialchars($user['email'] ?? $_POST['email'] ?? '') ?>">
            </div>

            <div class="co-field" style="margin-bottom:14px;">
              <label class="co-label">Full Address <span>*</span></label>
              <textarea name="address" class="co-textarea" required
                        placeholder="House/Flat no., Street, Locality"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
            </div>

            <div class="co-row3">
              <div class="co-field">
                <label class="co-label">City <span>*</span></label>
                <input type="text" name="city" class="co-input" required
                       placeholder="New Delhi"
                       value="<?= htmlspecialchars($_POST['city'] ?? '') ?>">
              </div>
              <div class="co-field">
                <label class="co-label">State</label>
                <select name="state" class="co-select">
                  <option value="">Select State</option>
                  <?php foreach ($states as $st): ?>
                    <option value="<?= $st ?>" <?= ($_POST['state'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="co-field">
                <label class="co-label">Pincode</label>
                <input type="text" name="pincode" class="co-input" maxlength="6"
                       placeholder="110025"
                       value="<?= htmlspecialchars($_POST['pincode'] ?? '') ?>">
              </div>
            </div>

            <div class="co-field" style="margin-top:14px;">
              <label class="co-label">Special Instructions</label>
              <textarea name="notes" class="co-textarea"
                        placeholder="Any special requirements or instructions..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
            </div>
          </div>

          <!-- Payment Method -->
          <div class="co-card">
            <div class="co-card-title">
              <i class="bi bi-credit-card-fill"></i> Payment Method
            </div>
            <div class="co-row2">
              <?php
              $paySelected = $_POST['payment_mode'] ?? 'pay_after_work';
              ?>
              <label class="pay-option <?= $paySelected === 'pay_after_work' ? 'selected' : '' ?>"
                     onclick="selectPay(this)">
                <input type="radio" name="payment_mode" value="pay_after_work"
                       <?= $paySelected === 'pay_after_work' ? 'checked' : '' ?>>
                <span class="pay-option-icon">🤝</span>
                <div>
                  <div class="pay-option-title">Pay After Work</div>
                  <div class="pay-option-sub">Pay cash / UPI after work done</div>
                </div>
              </label>
              <label class="pay-option <?= $paySelected === 'razorpay' ? 'selected' : '' ?>"
                     onclick="selectPay(this)">
                <input type="radio" name="payment_mode" value="razorpay"
                       <?= $paySelected === 'razorpay' ? 'checked' : '' ?>>
                <span class="pay-option-icon">💳</span>
                <div>
                  <div class="pay-option-title">Pay Online Now</div>
                  <div class="pay-option-sub">UPI, Cards, Net Banking via Razorpay</div>
                </div>
              </label>
            </div>
          </div>
        </div>

        <!-- RIGHT: Order Summary -->
        <div>
          <div class="co-summary">
            <div class="co-summary-title">
              <i class="bi bi-receipt-cutoff"></i> Order Summary
            </div>

            <!-- Items -->
            <?php foreach ($cartItems as $ci): ?>
            <div class="co-sum-item">
              <div class="co-sum-item-left">
                <div class="co-sum-item-icon"><?= htmlspecialchars($ci['icon'] ?? '📦') ?></div>
                <div>
                  <div class="co-sum-item-name"><?= htmlspecialchars(mb_substr($ci['name'], 0, 40)) ?></div>
                  <div class="co-sum-item-qty">× <?= $ci['quantity'] ?></div>
                </div>
              </div>
              <span class="co-sum-item-price">₹<?= number_format($ci['price'] * $ci['quantity']) ?></span>
            </div>
            <?php endforeach; ?>

            <hr class="co-sum-divider">

            <div class="co-sum-row">
              <span class="co-sum-row-lbl">Subtotal</span>
              <span class="co-sum-row-val">₹<?= number_format($subtotal) ?></span>
            </div>
            <?php if ($discount > 0): ?>
            <div class="co-sum-row">
              <span class="co-sum-row-lbl">Coupon (<?= htmlspecialchars($coupon['code']) ?>)</span>
              <span class="co-sum-row-disc">− ₹<?= number_format($discount) ?></span>
            </div>
            <?php endif; ?>
            <div class="co-sum-row">
              <span class="co-sum-row-lbl">GST (<?= GST_PERCENT ?>%)</span>
              <span class="co-sum-row-gst">₹<?= number_format($gst) ?></span>
            </div>

            <div class="co-sum-total-row">
              <span class="co-sum-total-lbl">Total</span>
              <span class="co-sum-total-val">₹<?= number_format($total) ?></span>
            </div>
            <div class="co-sum-gst-note">* GST & taxes included</div>

            <button type="submit" class="btn-place-order">
              <i class="bi bi-bag-check-fill"></i> Place Order
            </button>
            <a href="/cart.php" class="btn-back-cart">
              <i class="bi bi-arrow-left"></i> Back to Cart
            </a>
            <div class="co-secure">
              <i class="bi bi-shield-fill-check"></i> 100% Secure Checkout
            </div>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>

<script>
function selectPay(label) {
  document.querySelectorAll('.pay-option').forEach(l => l.classList.remove('selected'));
  label.classList.add('selected');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>