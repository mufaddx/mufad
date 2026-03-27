<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "My Cart — " . SITE_NAME;

try {
    if (isLoggedIn()) {
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$_SESSION['user_id']]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE session_id = ? ORDER BY created_at DESC");
        $stmt->execute([session_id()]);
    }
    $cartItems = $stmt->fetchAll();
    foreach($cartItems as &$item) {
        if ($item['item_type'] === 'product') {
            $r = $pdo->prepare("SELECT name, icon, image FROM products WHERE id = ?");
            $r->execute([$item['item_id']]);
            $data = $r->fetch();
        } elseif ($item['item_type'] === 'service') {
            $r = $pdo->prepare("SELECT name, icon, image FROM services WHERE id = ?");
            $r->execute([$item['item_id']]);
            $data = $r->fetch();
        } else { $data = null; }
        $item['name']  = $data['name']  ?? 'Item';
        $item['icon']  = $data['icon']  ?? '📦';
        $item['image'] = $data['image'] ?? '';
    }
    unset($item);
} catch(Exception $e) { $cartItems = []; }

$coupon   = $_SESSION['coupon'] ?? null;
$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems));
$discount = 0;
if ($coupon) {
    $discount = $coupon['type'] === 'percent'
        ? ($subtotal * $coupon['value'] / 100)
        : min($coupon['value'], $subtotal);
}
$total = max(0, $subtotal - $discount);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
:root {
  --or:#e8560a; --or-lt:#fff5f0; --or-b:rgba(232,86,10,.18);
  --bl:#1565c0; --bl-lt:#f0f5ff;
  --gr:#2e7d32; --gr-lt:#f0faf0; --gr-b:rgba(46,125,50,.2);
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb; --white:#fff;
}

/* ── PAGE HEADER BAR ── */
.cart-hdr {
  background: var(--white);
  border-bottom: 1.5px solid var(--border);
  padding: 16px 5%;
  box-shadow: 0 2px 10px rgba(26,35,50,.05);
}
.cart-hdr-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; align-items: center; gap: 12px;
}
.cart-hdr-icon {
  width: 38px; height: 38px; border-radius: 11px;
  background: var(--or-lt); border: 1.5px solid var(--or-b);
  display: flex; align-items: center; justify-content: center;
  color: var(--or); font-size: 1rem; flex-shrink: 0;
}
.cart-hdr-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.4rem; font-weight: 900; color: var(--txt);
}
.cart-hdr-count {
  background: var(--or); color: #fff;
  font-size: .72rem; font-weight: 800;
  padding: 3px 10px; border-radius: 20px;
  margin-left: 4px;
}
.cart-hdr-crumb {
  font-size: .76rem; color: var(--hint); margin-left: auto;
}
.cart-hdr-crumb a { color: var(--hint); text-decoration: none; }
.cart-hdr-crumb a:hover { color: var(--or); }

/* ── PAGE AREA ── */
.cart-page { background: var(--bg); padding: 24px 5% 60px; min-height: 70vh; }
.cart-page-inner { max-width: 1200px; margin: 0 auto; }

/* ── EMPTY STATE ── */
.cart-empty {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 20px; padding: 60px 20px;
  text-align: center; box-shadow: 0 2px 12px rgba(26,35,50,.05);
}
.cart-empty-icon { font-size: 4.5rem; margin-bottom: 16px; display: block; opacity: .7; }
.cart-empty-title { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 800; color: var(--txt); margin-bottom: 8px; }
.cart-empty-sub { font-size: .9rem; color: var(--light); margin-bottom: 28px; }
.cart-empty-btns { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.btn-empty-primary {
  display: flex; align-items: center; gap: 7px;
  background: linear-gradient(135deg, #f0a070, var(--or));
  color: #fff; font-weight: 700; font-size: .9rem;
  padding: 12px 28px; border-radius: 11px; text-decoration: none;
  box-shadow: 0 3px 12px rgba(232,86,10,.28); transition: all .2s;
}
.btn-empty-primary:hover { color:#fff; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(232,86,10,.38); }
.btn-empty-sec {
  display: flex; align-items: center; gap: 7px;
  background: var(--white); color: var(--mid); font-weight: 700; font-size: .9rem;
  padding: 12px 28px; border-radius: 11px; text-decoration: none;
  border: 1.5px solid var(--border); transition: all .2s;
}
.btn-empty-sec:hover { background: var(--bg); border-color: var(--or); color: var(--or); }

/* ── LAYOUT ── */
.cart-layout {
  display: grid; grid-template-columns: 1fr;
  gap: 20px; align-items: start;
}
@media(min-width:992px) { .cart-layout { grid-template-columns: 1fr 340px; } }

/* ── CART ITEMS CARD ── */
.cart-items-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 18px; overflow: hidden;
  box-shadow: 0 2px 12px rgba(26,35,50,.05);
}
.cart-items-head {
  padding: 16px 20px; border-bottom: 1.5px solid var(--border);
  font-size: .85rem; font-weight: 800; color: var(--txt);
  display: flex; align-items: center; gap: 8px;
}
.cart-items-head i { color: var(--or); }

/* Cart row */
.cart-row {
  display: flex; align-items: center; gap: 14px;
  padding: 16px 20px; border-bottom: 1px solid var(--border);
  transition: background .15s;
}
.cart-row:last-child { border-bottom: none; }
.cart-row:hover { background: #fafbfd; }
.cart-row-img {
  width: 64px; height: 64px; border-radius: 12px; flex-shrink: 0;
  background: var(--or-lt); border: 1.5px solid var(--or-b);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.8rem; overflow: hidden;
}
.cart-row-img img { width: 100%; height: 100%; object-fit: cover; border-radius: 10px; }
.cart-row-info { flex: 1; min-width: 0; }
.cart-row-type {
  font-size: .6rem; font-weight: 800; text-transform: uppercase;
  letter-spacing: .8px; color: var(--or);
  background: var(--or-lt); border: 1px solid var(--or-b);
  padding: 2px 8px; border-radius: 12px;
  display: inline-block; margin-bottom: 4px;
}
.cart-row-name {
  font-size: .88rem; font-weight: 700; color: var(--txt);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  max-width: 260px;
}
.cart-row-price { font-size: .82rem; font-weight: 800; color: var(--or); margin-top: 2px; }
.cart-row-controls {
  display: flex; align-items: center; gap: 10px; flex-shrink: 0;
}
.qty-box {
  display: flex; align-items: center; gap: 0;
  border: 1.5px solid var(--border); border-radius: 9px; overflow: hidden;
}
.qty-btn {
  width: 32px; height: 32px; background: var(--bg); border: none;
  cursor: pointer; font-size: .9rem; font-weight: 700; color: var(--mid);
  transition: all .15s; display: flex; align-items: center; justify-content: center;
}
.qty-btn:hover { background: var(--or); color: #fff; }
.qty-val {
  width: 36px; text-align: center; font-size: .85rem; font-weight: 700;
  color: var(--txt); border-left: 1px solid var(--border); border-right: 1px solid var(--border);
  height: 32px; display: flex; align-items: center; justify-content: center;
}
.cart-row-subtotal {
  font-size: .9rem; font-weight: 800; color: var(--txt);
  min-width: 70px; text-align: right;
}
.btn-cart-remove {
  width: 32px; height: 32px; border-radius: 8px;
  background: #fef2f2; border: 1.5px solid #fecaca;
  color: #dc2626; cursor: pointer; font-size: .78rem;
  display: flex; align-items: center; justify-content: center;
  transition: all .15s;
}
.btn-cart-remove:hover { background: #dc2626; color: #fff; }

/* Hide some controls on mobile */
@media(max-width:575px) {
  .cart-row { flex-wrap: wrap; }
  .cart-row-controls { width: 100%; justify-content: flex-end; }
  .cart-row-name { max-width: 180px; }
}

/* ── COUPON CARD ── */
.coupon-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 16px; padding: 18px 20px; margin-top: 16px;
  box-shadow: 0 2px 10px rgba(26,35,50,.05);
}
.coupon-card-title {
  font-size: .84rem; font-weight: 800; color: var(--txt);
  margin-bottom: 12px; display: flex; align-items: center; gap: 6px;
}
.coupon-card-title i { color: var(--or); }
.coupon-input-row { display: flex; gap: 8px; }
.coupon-input {
  flex: 1; border: 1.5px solid var(--border); border-radius: 10px;
  padding: 9px 14px; font-size: .85rem;
  font-family: 'DM Sans', sans-serif; color: var(--txt);
  background: var(--bg); outline: none;
  transition: border-color .18s, box-shadow .18s;
}
.coupon-input:focus { border-color: var(--or); box-shadow: 0 0 0 3px rgba(232,86,10,.10); background: #fff; }
.btn-coupon-apply {
  background: var(--or-lt); color: var(--or);
  border: 1.5px solid var(--or-b); border-radius: 10px;
  padding: 9px 18px; font-weight: 700; font-size: .83rem;
  cursor: pointer; transition: all .15s; white-space: nowrap;
}
.btn-coupon-apply:hover { background: var(--or); color: #fff; }
.btn-coupon-remove {
  background: #fef2f2; color: #dc2626;
  border: 1.5px solid #fecaca; border-radius: 10px;
  padding: 9px 14px; font-weight: 700; font-size: .83rem;
  cursor: pointer; transition: all .15s;
}
.btn-coupon-remove:hover { background: #dc2626; color: #fff; }
.coupon-success {
  background: var(--gr-lt); color: var(--gr);
  border: 1px solid var(--gr-b);
  border-radius: 9px; padding: 9px 13px;
  font-size: .8rem; font-weight: 600;
  margin-top: 10px; display: flex; align-items: center; gap: 6px;
}

/* ── ORDER SUMMARY ── */
.order-summary {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 18px; padding: 22px;
  box-shadow: 0 2px 12px rgba(26,35,50,.05);
  position: sticky; top: 80px;
}
.summary-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.05rem; font-weight: 800; color: var(--txt); margin-bottom: 18px;
}
.summary-row {
  display: flex; align-items: center; justify-content: space-between;
  font-size: .84rem; margin-bottom: 10px;
}
.summary-row-label { color: var(--mid); }
.summary-row-val   { font-weight: 700; color: var(--txt); }
.summary-row-free  { color: var(--gr); font-weight: 700; }
.summary-row-disc  { color: #16a34a; font-weight: 700; }
.summary-divider   { border: none; border-top: 1.5px solid var(--border); margin: 14px 0; }
.summary-total-row {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 4px;
}
.summary-total-label { font-size: .9rem; font-weight: 800; color: var(--txt); }
.summary-total-val   { font-size: 1.25rem; font-weight: 900; color: var(--or); }
.summary-gst-note    { font-size: .7rem; color: var(--hint); margin-bottom: 18px; }

.btn-checkout {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; background: linear-gradient(135deg, #f0a070, var(--or));
  color: #fff; font-weight: 800; font-size: .95rem;
  padding: 14px; border-radius: 12px; text-decoration: none;
  box-shadow: 0 4px 16px rgba(232,86,10,.3); transition: all .22s;
  margin-bottom: 10px;
}
.btn-checkout:hover { color:#fff; transform: translateY(-1px); box-shadow: 0 8px 22px rgba(232,86,10,.4); }
.btn-cont-shop {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  width: 100%; background: var(--bg); color: var(--mid);
  font-weight: 700; font-size: .83rem;
  padding: 11px; border-radius: 11px; text-decoration: none;
  border: 1.5px solid var(--border); transition: all .15s;
}
.btn-cont-shop:hover { border-color: var(--or); color: var(--or); background: var(--or-lt); }

/* Trust badges */
.trust-list { margin-top: 18px; display: flex; flex-direction: column; gap: 9px; }
.trust-item {
  display: flex; align-items: center; gap: 9px;
  font-size: .78rem; color: var(--mid); font-weight: 600;
}
.trust-item i { font-size: .9rem; flex-shrink: 0; }
</style>

<!-- ── PAGE HEADER ── -->
<div class="cart-hdr">
  <div class="cart-hdr-inner">
    <div class="cart-hdr-icon"><i class="bi bi-cart3"></i></div>
    <div>
      <span class="cart-hdr-title">My Cart</span>
      <span class="cart-hdr-count"><?= count($cartItems) ?></span>
    </div>

  </div>
</div>

<!-- ── PAGE AREA ── -->
<div class="cart-page">
  <div class="cart-page-inner">

    <?php if(empty($cartItems)): ?>
    <!-- EMPTY STATE -->
    <div class="cart-empty">
      <span class="cart-empty-icon">🛒</span>
      <div class="cart-empty-title">Your cart is empty</div>
      <p class="cart-empty-sub">Add some services or products to get started.</p>
      <div class="cart-empty-btns">
        <a href="/services.php" class="btn-empty-primary">
          <i class="bi bi-tools"></i> Browse Services
        </a>
        <a href="/products.php" class="btn-empty-sec">
          <i class="bi bi-bag-fill"></i> Browse Products
        </a>
      </div>
    </div>

    <?php else: ?>
    <!-- CART LAYOUT -->
    <div class="cart-layout">

      <!-- LEFT: Items + Coupon -->
      <div>
        <!-- Items card -->
        <div class="cart-items-card">
          <div class="cart-items-head">
            <i class="bi bi-bag-check-fill"></i>
            Cart Items (<?= count($cartItems) ?>)
          </div>
          <?php foreach($cartItems as $item): ?>
          <div class="cart-row" id="cart-row-<?= $item['id'] ?>">
            <!-- Image -->
            <div class="cart-row-img">
              <?php if($item['image']): ?>
                <img src="/uploads/<?= $item['item_type'] ?>s/<?= htmlspecialchars($item['image']) ?>" alt="">
              <?php else: ?>
                <?= htmlspecialchars($item['icon'] ?? '📦') ?>
              <?php endif; ?>
            </div>
            <!-- Info -->
            <div class="cart-row-info">
              <span class="cart-row-type"><?= htmlspecialchars($item['item_type']) ?></span>
              <div class="cart-row-name" title="<?= htmlspecialchars($item['name']) ?>">
                <?= htmlspecialchars($item['name']) ?>
              </div>
              <div class="cart-row-price">₹<?= number_format($item['price']) ?></div>
            </div>
            <!-- Controls -->
            <div class="cart-row-controls">
              <div class="qty-box">
                <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, -1)">−</button>
                <span class="qty-val" id="qty-<?= $item['id'] ?>"><?= $item['quantity'] ?></span>
                <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, 1)">+</button>
              </div>
              <span class="cart-row-subtotal" id="subtotal-<?= $item['id'] ?>">
                ₹<?= number_format($item['price'] * $item['quantity']) ?>
              </span>
              <button class="btn-cart-remove" onclick="removeItem(<?= $item['id'] ?>)" title="Remove">
                <i class="bi bi-trash3-fill"></i>
              </button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Coupon card -->
        <div class="coupon-card">
          <div class="coupon-card-title">
            <i class="bi bi-tag-fill"></i> Apply Coupon Code
          </div>
          <div class="coupon-input-row">
            <input type="text" id="couponInput" class="coupon-input"
                   placeholder="Enter coupon code"
                   value="<?= $coupon ? htmlspecialchars($coupon['code']) : '' ?>">
            <button onclick="applyCoupon()" class="btn-coupon-apply">Apply</button>
            <?php if($coupon): ?>
              <button onclick="removeCoupon()" class="btn-coupon-remove">Remove</button>
            <?php endif; ?>
          </div>
          <div id="couponMsg"></div>
          <?php if($coupon): ?>
            <div class="coupon-success">
              <i class="bi bi-check-circle-fill"></i>
              Coupon <strong><?= htmlspecialchars($coupon['code']) ?></strong> applied!
              Saving <?= $coupon['type']==='percent' ? $coupon['value'].'%' : '₹'.number_format($coupon['value']) ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- RIGHT: Order Summary -->
      <div>
        <div class="order-summary">
          <div class="summary-title">Order Summary</div>

          <div class="summary-row">
            <span class="summary-row-label">Subtotal (<?= count($cartItems) ?> items)</span>
            <span class="summary-row-val">₹<?= number_format($subtotal) ?></span>
          </div>
          <?php if($discount > 0): ?>
          <div class="summary-row">
            <span class="summary-row-label">Coupon Discount</span>
            <span class="summary-row-disc">− ₹<?= number_format($discount) ?></span>
          </div>
          <?php endif; ?>
          <div class="summary-row">
            <span class="summary-row-label">Inspection / Delivery</span>
            <span class="summary-row-free">FREE</span>
          </div>

          <hr class="summary-divider">

          <div class="summary-total-row">
            <span class="summary-total-label">Total Amount</span>
            <span class="summary-total-val" id="grandTotal">₹<?= number_format($total) ?></span>
          </div>
          <div class="summary-gst-note">* GST & taxes included</div>

          <a href="/checkout.php" class="btn-checkout">
            <i class="bi bi-bag-check-fill"></i> Proceed to Checkout
          </a>
          <a href="/products.php" class="btn-cont-shop">
            <i class="bi bi-arrow-left"></i> Continue Shopping
          </a>

          <!-- Trust badges -->
          <div class="trust-list">
            <div class="trust-item">
              <i class="bi bi-shield-fill-check" style="color:var(--gr);"></i>
              100% Secure Checkout
            </div>
            <div class="trust-item">
              <i class="bi bi-receipt" style="color:var(--bl);"></i>
              GST Invoice Available
            </div>
            <div class="trust-item">
              <i class="bi bi-headset" style="color:var(--or);"></i>
              24/7 Expert Support
            </div>
            <div class="trust-item">
              <i class="bi bi-truck" style="color:var(--gr);"></i>
              Free Pickup & Delivery
            </div>
          </div>
        </div>
      </div>

    </div>
    <?php endif; ?>

  </div>
</div>

<script>
const csrf = '<?= generateCSRF() ?>';

function updateQty(cartId, change) {
  fetch('/api/cart-update.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({cart_id: cartId, change: change, csrf: csrf})
  }).then(r=>r.json()).then(d => {
    if (d.success) {
      if (d.removed) { document.getElementById('cart-row-'+cartId).remove(); }
      else {
        document.getElementById('qty-'+cartId).textContent = d.quantity;
        document.getElementById('subtotal-'+cartId).textContent = '₹'+d.subtotal.toLocaleString('en-IN');
      }
      location.reload();
    }
  });
}

function removeItem(cartId) {
  if (!confirm('Remove this item from cart?')) return;
  fetch('/api/cart-remove.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({cart_id: cartId, csrf: csrf})
  }).then(r=>r.json()).then(d => { if(d.success) location.reload(); });
}

function applyCoupon() {
  const code = document.getElementById('couponInput').value.trim();
  if (!code) return;
  fetch('/api/coupon-apply.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({code: code, csrf: csrf})
  }).then(r=>r.json()).then(d => {
    const msg = document.getElementById('couponMsg');
    if (d.success) {
      msg.innerHTML = '<div style="background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;border-radius:9px;padding:9px 13px;font-size:.8rem;font-weight:600;margin-top:10px;">✓ '+d.message+'</div>';
      setTimeout(()=>location.reload(), 1000);
    } else {
      msg.innerHTML = '<div style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:9px;padding:9px 13px;font-size:.8rem;font-weight:600;margin-top:10px;">'+d.message+'</div>';
    }
  });
}

function removeCoupon() {
  fetch('/api/coupon-apply.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({remove: true, csrf: csrf})
  }).then(()=>location.reload());
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>