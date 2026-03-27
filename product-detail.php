<?php
require_once __DIR__ . '/includes/functions.php';

$id      = (int)($_GET['id'] ?? 0);
$product = null; $specs = []; $related = []; $reviews = [];

// Demo products — same as products.php fallback
$_demoProducts = [
    1 => ['id'=>1,'name'=>'BLDC Energy Saving Ceiling Fan 1200mm','cat_name'=>'CEILING FAN','category_id'=>1,'price'=>2800,'original_price'=>3500,'image'=>'','icon'=>'🌀','badge'=>'HOT','unit'=>'piece','rating'=>4.8,'description'=>'High-efficiency BLDC motor ceiling fan with 1200mm sweep. Saves up to 60% energy compared to regular fans. 5-speed remote control, 2-year warranty.'],
    2 => ['id'=>2,'name'=>'1.5 Ton 5-Star Inverter AC','cat_name'=>'SPLIT AC','category_id'=>2,'price'=>42000,'original_price'=>52000,'image'=>'','icon'=>'❄️','badge'=>'SALE','unit'=>'piece','rating'=>4.9,'description'=>'5-star BEE rated 1.5 ton inverter split AC. Auto-clean, Wi-Fi control, 4-way swing, anti-bacterial filter. Perfect for rooms up to 180 sq ft.'],
    3 => ['id'=>3,'name'=>'18W Round LED Panel Light','cat_name'=>'LED LIGHTING','category_id'=>3,'price'=>320,'original_price'=>450,'image'=>'','icon'=>'💡','badge'=>'NEW','unit'=>'piece','rating'=>4.7,'description'=>'18W round LED panel light with warm white 3000K. Slim design, easy installation, 25,000 hours lifespan. Perfect for offices and living rooms.'],
    4 => ['id'=>4,'name'=>'Premium Modular Switch Board 6-Module','cat_name'=>'SWITCHES','category_id'=>4,'price'=>850,'original_price'=>1200,'image'=>'','icon'=>'🔌','badge'=>'','unit'=>'piece','rating'=>4.6,'description'=>'6-module modular switch board with shock-proof body. Comes with 2 switches, 1 socket, 1 fan regulator. ISI marked, fire retardant plastic.'],
    5 => ['id'=>5,'name'=>'Solar Water Heater 100 Litre','cat_name'=>'SOLAR','category_id'=>5,'price'=>18000,'original_price'=>22000,'image'=>'','icon'=>'☀️','badge'=>'HOT','unit'=>'piece','rating'=>4.8,'description'=>'100-litre solar water heater with pressurized tank. Stainless steel inner tank, 5-year warranty, saves 80% electricity on water heating.'],
    6 => ['id'=>6,'name'=>'Ceramic Floor Tiles 2x2 ft (Box)','cat_name'=>'TILES','category_id'=>6,'price'=>780,'original_price'=>950,'image'=>'','icon'=>'🔲','badge'=>'SALE','unit'=>'box','rating'=>4.5,'description'=>'High quality 2x2 feet ceramic floor tiles. Glossy finish, anti-skid, frost-resistant. 6-8 tiles per box covering approx 6 sq ft.'],
    7 => ['id'=>7,'name'=>'Heavy Duty Paint — 20 Litre','cat_name'=>'PAINTS','category_id'=>7,'price'=>3200,'original_price'=>3800,'image'=>'','icon'=>'🎨','badge'=>'','unit'=>'can','rating'=>4.7,'description'=>'Premium exterior emulsion paint 20L. Weather resistant, UV protected, covers 80-100 sq ft per litre. Available in 500+ shades.'],
    8 => ['id'=>8,'name'=>'Stainless Steel Kitchen Sink','cat_name'=>'PLUMBING','category_id'=>8,'price'=>2200,'original_price'=>2800,'image'=>'','icon'=>'🚿','badge'=>'NEW','unit'=>'piece','rating'=>4.6,'description'=>'304 grade stainless steel double bowl kitchen sink. Anti-scratch surface, comes with tap holes, waste coupling and installation hardware.'],
    9 => ['id'=>9,'name'=>'4 mm Electrical Wire (90m Roll)','cat_name'=>'WIRING','category_id'=>9,'price'=>1850,'original_price'=>2200,'image'=>'','icon'=>'⚡','badge'=>'','unit'=>'roll','rating'=>4.8,'description'=>'FR-LSH 4mm copper conductor electrical wire, 90m roll. Triple insulated, heat resistant up to 70°C. ISI marked, suitable for all wiring.'],
    10 => ['id'=>10,'name'=>'Wooden Laminate Flooring (Pack)','cat_name'=>'FLOORING','category_id'=>10,'price'=>4500,'original_price'=>5500,'image'=>'','icon'=>'🪵','badge'=>'HOT','unit'=>'pack','rating'=>4.7,'description'=>'8mm thick AC3 grade wooden laminate flooring. Easy click-lock installation, water resistant, 15-year warranty. Covers approx 2.4 sq m per pack.'],
    11 => ['id'=>11,'name'=>'PVC Water Storage Tank 500L','cat_name'=>'TANKS','category_id'=>11,'price'=>3800,'original_price'=>4500,'image'=>'','icon'=>'🪣','badge'=>'','unit'=>'piece','rating'=>4.9,'description'=>'500-litre 3-layer PVC water storage tank. UV stabilized, food-grade material, with float valve and lid. 5-year manufacturer warranty.'],
    12 => ['id'=>12,'name'=>'Inverter Battery 150Ah Tubular','cat_name'=>'POWER BACKUP','category_id'=>12,'price'=>12500,'original_price'=>15000,'image'=>'','icon'=>'🔋','badge'=>'SALE','unit'=>'piece','rating'=>4.8,'description'=>'150Ah tall tubular inverter battery with 36-month warranty. Low maintenance, fast charging, suitable for 1-2 fan, 4 lights, 1 TV backup.'],
];

if ($id && $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, pc.name AS cat_name FROM products p LEFT JOIN product_categories pc ON p.category_id=pc.id WHERE p.id=? AND p.status=1");
        $stmt->execute([$id]);
        $product = $stmt->fetch();
        if ($product) {
            $specStmt = $pdo->prepare("SELECT * FROM product_specs WHERE product_id=? ORDER BY id");
            $specStmt->execute([$id]);
            $specs = $specStmt->fetchAll();
            $relStmt = $pdo->prepare("SELECT * FROM products WHERE category_id=? AND id!=? AND status=1 ORDER BY rating DESC LIMIT 4");
            $relStmt->execute([$product['category_id'], $id]);
            $related = $relStmt->fetchAll();
            $revStmt = $pdo->prepare("SELECT * FROM reviews WHERE product_id=? AND is_approved=1 ORDER BY id DESC LIMIT 6");
            $revStmt->execute([$id]);
            $reviews = $revStmt->fetchAll();
        }
    } catch (Exception $e) { error_log('product-detail: ' . $e->getMessage()); }
}

// Fallback to demo data
if (!$product) {
    $product = $_demoProducts[$id] ?? null;
    if (!$product) redirect('/products.php');
    // Related: same category from demo
    $related = array_values(array_filter($_demoProducts, fn($p) => $p['category_id'] === $product['category_id'] && $p['id'] !== $id));
    $related = array_slice($related, 0, 4);
}

$discount = (!empty($product['original_price']) && $product['original_price'] > $product['price'])
    ? round((1 - $product['price'] / $product['original_price']) * 100) : 0;
$stars = min(5, max(1, round($product['rating'] ?? 4)));

$pageTitle = $product['name'] . ' — Incredible Heights';
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

/* ── PAGE ── */
.prd-page { background: var(--bg); padding: 22px 5% 60px; min-height: 80vh; }
.prd-inner { max-width: 1200px; margin: 0 auto; }

/* ── CARD ── */
.prd-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 18px; overflow: hidden;
  box-shadow: 0 2px 12px rgba(26,35,50,.05); margin-bottom: 18px;
}
.prd-card-body { padding: 22px; }
.prd-card-title {
  font-size: .9rem; font-weight: 800; color: var(--txt);
  margin-bottom: 16px; display: flex; align-items: center; gap: 7px;
}
.prd-card-title i { color: var(--bl); }

/* ── TOP GRID ── */
.prd-top {
  display: grid; grid-template-columns: 1fr;
  gap: 18px; margin-bottom: 18px;
}
@media(min-width:768px) { .prd-top { grid-template-columns: 1fr 1fr; } }
@media(min-width:992px) { .prd-top { grid-template-columns: 440px 1fr; } }

/* Image area */
.prd-img-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 18px; padding: 28px;
  display: flex; align-items: center; justify-content: center;
  min-height: 280px; position: relative;
  box-shadow: 0 2px 12px rgba(26,35,50,.05);
}
.prd-img-card img {
  max-width: 100%; max-height: 320px; object-fit: contain; border-radius: 10px;
}
.prd-no-img {
  background: linear-gradient(135deg, var(--bl-lt), #f5f8ff);
  width: 100%; height: 240px; border-radius: 14px;
  display: flex; align-items: center; justify-content: center;
  font-size: 6rem;
}
.prd-badge-chip {
  position: absolute; top: 14px; left: 14px;
  font-size: .62rem; font-weight: 800; padding: 4px 10px; border-radius: 18px;
  text-transform: uppercase;
}
.b-hot  { background: #fff0f0; color: #dc2626; border: 1px solid #fecaca; }
.b-new  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.b-sale { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }

/* Product info */
.prd-info { display: flex; flex-direction: column; gap: 0; }
.prd-cat-lbl {
  font-size: .68rem; font-weight: 800; color: var(--bl);
  text-transform: uppercase; letter-spacing: 1px;
  background: var(--bl-lt); border: 1px solid var(--bl-b);
  padding: 3px 10px; border-radius: 18px; display: inline-block;
  margin-bottom: 10px; width: fit-content;
}
.prd-name {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(1.2rem, 3vw, 1.7rem); font-weight: 900;
  color: var(--txt); line-height: 1.25; margin-bottom: 10px;
}
.prd-stars { color: #f59e0b; font-size: .9rem; margin-bottom: 14px; }
.prd-stars span { color: var(--hint); font-size: .75rem; margin-left: 5px; }

/* Price row */
.prd-price-row { display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; margin-bottom: 4px; }
.prd-price {
  font-family: 'Playfair Display', serif;
  font-size: 2rem; font-weight: 900; color: var(--bl);
}
.prd-orig  { text-decoration: line-through; color: var(--hint); font-size: 1.1rem; }
.prd-off   { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; font-size: .72rem; font-weight: 800; padding: 3px 9px; border-radius: 18px; }
.prd-unit  { font-size: .78rem; color: var(--hint); margin-bottom: 18px; }

/* Qty + Cart */
.prd-action-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 14px; }
.prd-qty-box {
  display: flex; align-items: center;
  border: 1.5px solid var(--border); border-radius: 11px; overflow: hidden;
}
.prd-qty-btn {
  width: 38px; height: 42px; background: var(--bg); border: none;
  cursor: pointer; font-size: 1rem; font-weight: 700; color: var(--mid);
  transition: all .15s; display: flex; align-items: center; justify-content: center;
}
.prd-qty-btn:hover { background: var(--bl); color: #fff; }
.prd-qty-val {
  width: 46px; text-align: center; font-size: .9rem; font-weight: 700;
  color: var(--txt); border: none; outline: none; background: transparent;
  height: 42px;
}
.btn-add-cart {
  flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
  background: linear-gradient(135deg, var(--bl), #0d47a1);
  color: #fff; font-weight: 800; font-size: .9rem;
  padding: 12px 22px; border-radius: 11px; border: none; cursor: pointer;
  box-shadow: 0 4px 14px rgba(21,101,192,.28); transition: all .22s;
  white-space: nowrap; min-width: 160px;
}
.btn-add-cart:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(21,101,192,.38); }

/* Secondary buttons */
.prd-sec-btns { display: flex; gap: 9px; flex-wrap: wrap; margin-bottom: 20px; }
.btn-prd-wa {
  display: flex; align-items: center; gap: 6px;
  background: #25d366; color: #fff; font-weight: 700; font-size: .83rem;
  padding: 10px 18px; border-radius: 10px; text-decoration: none;
  box-shadow: 0 3px 10px rgba(37,211,102,.25); transition: all .18s;
}
.btn-prd-wa:hover { background: #1db954; color: #fff; }
.btn-prd-call {
  display: flex; align-items: center; gap: 6px;
  background: var(--bg); color: var(--txt); font-weight: 700; font-size: .83rem;
  padding: 10px 18px; border-radius: 10px; text-decoration: none;
  border: 1.5px solid var(--border); transition: all .15s;
}
.btn-prd-call:hover { border-color: var(--bl); color: var(--bl); background: var(--bl-lt); }

/* Guarantees */
.prd-guarantees {
  display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
  padding-top: 16px; border-top: 1px solid var(--border);
}
.prd-guarantee {
  display: flex; align-items: center; gap: 7px;
  font-size: .76rem; color: var(--mid); font-weight: 600;
}
.prd-guarantee i { font-size: .85rem; flex-shrink: 0; }

/* Specs table */
.prd-specs-table { width: 100%; border-collapse: collapse; }
.prd-specs-table tr { border-bottom: 1px solid var(--border); }
.prd-specs-table tr:last-child { border-bottom: none; }
.prd-specs-table td { padding: 9px 12px; font-size: .82rem; }
.prd-spec-key { font-weight: 700; color: var(--mid); background: var(--bg); width: 40%; }
.prd-spec-val { color: var(--txt); font-weight: 600; }

/* Reviews */
.prd-reviews-grid {
  display: grid; grid-template-columns: 1fr; gap: 10px;
}
@media(min-width:576px) { .prd-reviews-grid { grid-template-columns: repeat(2,1fr); } }
@media(min-width:992px) { .prd-reviews-grid { grid-template-columns: repeat(3,1fr); } }
.prd-review-card {
  background: var(--bg); border: 1.5px solid var(--border);
  border-radius: 12px; padding: 14px;
}
.prd-review-header { display: flex; justify-content: space-between; margin-bottom: 6px; }
.prd-review-name   { font-size: .8rem; font-weight: 700; color: var(--txt); }
.prd-review-stars  { color: #f59e0b; font-size: .78rem; }
.prd-review-text   { font-size: .78rem; color: var(--light); line-height: 1.6; }

/* Related */
.prd-related-grid {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;
}
@media(min-width:576px) { .prd-related-grid { grid-template-columns: repeat(4, 1fr); } }
.prd-rel-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 14px; overflow: hidden; text-decoration: none;
  display: flex; flex-direction: column; transition: all .2s;
  box-shadow: 0 2px 8px rgba(26,35,50,.04);
}
.prd-rel-card:hover {
  border-color: rgba(21,101,192,.2); transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(26,35,50,.1);
}
.prd-rel-img {
  height: 100px; background: var(--bl-lt);
  display: flex; align-items: center; justify-content: center;
  font-size: 2.5rem; overflow: hidden;
}
.prd-rel-img img { width:100%; height:100%; object-fit:contain; padding:8px; }
.prd-rel-body { padding: 10px 12px; }
.prd-rel-name  { font-size: .78rem; font-weight: 700; color: var(--txt); line-height: 1.3; margin-bottom: 4px; }
.prd-rel-price { font-size: .88rem; font-weight: 900; color: var(--bl); }

/* Related listing-style cards */
.prod-card {
  background:var(--white);border:1.5px solid var(--border);border-radius:16px;overflow:hidden;
  display:flex;flex-direction:column;transition:all .22s;box-shadow:0 2px 10px rgba(26,35,50,.05);
}
.prod-card:hover{border-color:rgba(21,101,192,.22);box-shadow:0 10px 30px rgba(26,35,50,.12);transform:translateY(-3px);}
.prod-img{height:130px;background:linear-gradient(135deg,var(--bl-lt),#f5f8ff);display:flex;align-items:center;justify-content:center;position:relative;border-bottom:1px solid var(--border);font-size:2.5rem;overflow:hidden;}
.prod-img img{width:100%;height:100%;object-fit:cover;}
.prod-badge{position:absolute;top:8px;left:8px;font-size:.57rem;font-weight:800;padding:3px 8px;border-radius:16px;text-transform:uppercase;}
.b-hot{background:#fff0f0;color:#dc2626;border:1px solid #fecaca;}
.b-new{background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;}
.b-sale{background:#fffbeb;color:#d97706;border:1px solid #fde68a;}
.prod-body{padding:12px 13px 13px;display:flex;flex-direction:column;flex:1;}
.prod-cat{font-size:.59rem;font-weight:800;letter-spacing:1px;text-transform:uppercase;color:var(--bl);margin-bottom:3px;}
.prod-name{font-weight:700;font-size:.84rem;color:var(--txt);line-height:1.3;margin-bottom:5px;flex-grow:1;}
.prod-stars{color:#f59e0b;font-size:.72rem;margin-bottom:5px;}
.prod-price-row{margin-bottom:3px;}
.prod-price{font-weight:800;font-size:1rem;color:var(--txt);}
.prod-orig{text-decoration:line-through;color:var(--hint);font-size:.78rem;margin-left:4px;}
.prod-off{font-size:.68rem;font-weight:800;color:#16a34a;margin-left:4px;}
.prod-unit{font-size:.68rem;color:var(--hint);margin-bottom:10px;}
.prod-btns{display:flex;gap:7px;margin-top:auto;}
.btn-prod-view{flex:1;display:flex;align-items:center;justify-content:center;gap:5px;background:var(--bg);color:var(--txt)!important;font-weight:700;font-size:.74rem;padding:8px;border-radius:8px;text-decoration:none;border:1.5px solid var(--border);transition:.18s;}
.btn-prod-view:hover{background:#e8edf5;}
.btn-prod-cart{width:38px;height:38px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--bl),#0d47a1);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:.9rem;transition:.18s;box-shadow:0 2px 8px rgba(21,101,192,.28);flex-shrink:0;}
.btn-prod-cart:hover{transform:translateY(-1px);}
</style>

<div class="prd-page">
<div class="prd-inner">

  <!-- TOP: Image + Info -->
  <div class="prd-top">

    <!-- Image -->
    <div class="prd-img-card">
      <?php if(!empty($product['image'])): ?>
        <img src="/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
      <?php else: ?>
        <div class="prd-no-img"><?= htmlspecialchars($product['icon'] ?? '📦') ?></div>
      <?php endif; ?>
      <?php if(!empty($product['badge'])): ?>
        <span class="prd-badge-chip b-<?= strtolower($product['badge']) ?>"><?= $product['badge'] ?></span>
      <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="prd-card">
      <div class="prd-card-body">
        <div class="prd-info">
          <span class="prd-cat-lbl"><?= htmlspecialchars($product['cat_name'] ?? 'Product') ?></span>
          <h1 class="prd-name"><?= htmlspecialchars($product['name']) ?></h1>

          <?php if(!empty($product['rating'])): ?>
          <div class="prd-stars">
            <?= str_repeat('★', $stars) ?><?= str_repeat('☆', 5-$stars) ?>
            <span><?= $product['rating'] ?>/5</span>
          </div>
          <?php endif; ?>

          <div class="prd-price-row">
            <span class="prd-price">₹<?= number_format($product['price']) ?></span>
            <?php if($discount > 0): ?>
              <span class="prd-orig">₹<?= number_format($product['original_price']) ?></span>
              <span class="prd-off"><?= $discount ?>% OFF</span>
            <?php endif; ?>
          </div>
          <div class="prd-unit">Per <?= htmlspecialchars($product['unit'] ?? 'piece') ?> &nbsp;·&nbsp; GST Included</div>

          <?php if(!empty($product['description'])): ?>
          <p style="font-size:.87rem;color:var(--mid);line-height:1.75;margin-bottom:18px;">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
          </p>
          <?php endif; ?>

          <!-- Qty + Add to Cart -->
          <div class="prd-action-row">
            <div class="prd-qty-box">
              <button class="prd-qty-btn" onclick="if(document.getElementById('pqty').value>1) document.getElementById('pqty').value=parseInt(document.getElementById('pqty').value)-1">−</button>
              <input type="number" id="pqty" value="1" min="1" max="100" class="prd-qty-val">
              <button class="prd-qty-btn" onclick="document.getElementById('pqty').value=parseInt(document.getElementById('pqty').value)+1">+</button>
            </div>
            <button class="btn-add-cart"
                    onclick="addToCart('product', <?= $product['id'] ?>, parseInt(document.getElementById('pqty').value), <?= $product['price'] ?>)">
              <i class="bi bi-cart-plus"></i> Add to Cart
            </button>
          </div>

          <!-- WA + Call -->
          <div class="prd-sec-btns">
            <a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '' ?>?text=Hi%2C+I%27m+interested+in+<?= urlencode($product['name']) ?>+(₹<?= $product['price'] ?>).+Can+you+share+more+details%3F"
               target="_blank" class="btn-prd-wa">
              <i class="bi bi-whatsapp"></i> Ask on WhatsApp
            </a>
            <a href="tel:<?= defined('SITE_PHONE') ? SITE_PHONE : '' ?>" class="btn-prd-call">
              <i class="bi bi-telephone-fill"></i> Call to Order
            </a>
          </div>

          <!-- Guarantees -->
          <div class="prd-guarantees">
            <div class="prd-guarantee"><i class="bi bi-patch-check-fill" style="color:var(--bl);"></i> Genuine Product</div>
            <div class="prd-guarantee"><i class="bi bi-truck" style="color:var(--gr);"></i> Fast Delivery</div>
            <div class="prd-guarantee"><i class="bi bi-arrow-return-left" style="color:var(--or);"></i> Easy Returns</div>
            <div class="prd-guarantee"><i class="bi bi-shield-fill-check" style="color:var(--gr);"></i> Quality Assured</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Specs -->
  <?php if(!empty($specs)): ?>
  <div class="prd-card">
    <div class="prd-card-body">
      <div class="prd-card-title"><i class="bi bi-list-check"></i> Technical Specifications</div>
      <table class="prd-specs-table">
        <tbody>
          <?php foreach($specs as $s): ?>
          <tr>
            <td class="prd-spec-key"><?= htmlspecialchars($s['spec_name'] ?? $s['key'] ?? '') ?></td>
            <td class="prd-spec-val"><?= htmlspecialchars($s['spec_value'] ?? $s['value'] ?? '') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <!-- Reviews -->
  <?php if(!empty($reviews)): ?>
  <div class="prd-card">
    <div class="prd-card-body">
      <div class="prd-card-title"><i class="bi bi-star-fill"></i> Customer Reviews</div>
      <div class="prd-reviews-grid">
        <?php foreach($reviews as $r): ?>
        <div class="prd-review-card">
          <div class="prd-review-header">
            <span class="prd-review-name"><?= htmlspecialchars($r['name']) ?></span>
            <span class="prd-review-stars"><?= str_repeat('★', (int)($r['rating'] ?? 5)) ?></span>
          </div>
          <div class="prd-review-text"><?= htmlspecialchars($r['review_text'] ?? $r['review'] ?? '') ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Related Products — full listing-style cards -->
  <?php if(!empty($related)): ?>
  <div class="prd-card">
    <div class="prd-card-body">
      <div class="prd-card-title"><i class="bi bi-grid-fill"></i> Related Products</div>
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:13px;">
        <?php foreach($related as $r):
          $rDisc = (!empty($r['original_price']) && $r['original_price'] > $r['price'])
                   ? round((1 - $r['price'] / $r['original_price']) * 100) : 0;
          $rStars = min(5, max(1, round($r['rating'] ?? 4)));
          $rBadge = strtolower($r['badge'] ?? '');
        ?>
        <div class="prod-card" style="border-radius:14px;">
          <div class="prod-img">
            <?php if(!empty($r['image'])): ?>
              <img src="/<?= htmlspecialchars($r['image']) ?>" alt="<?= htmlspecialchars($r['name']) ?>">
            <?php else: ?>
              <?= htmlspecialchars($r['icon'] ?? '📦') ?>
            <?php endif; ?>
            <?php if(!empty($r['badge'])): ?>
              <span class="prod-badge b-<?= $rBadge ?>"><?= strtoupper($r['badge']) ?></span>
            <?php endif; ?>
          </div>
          <div class="prod-body">
            <div class="prod-cat"><?= htmlspecialchars($r['cat_name'] ?? '') ?></div>
            <div class="prod-name"><?= htmlspecialchars($r['name']) ?></div>
            <div class="prod-stars">
              <?= str_repeat('★', $rStars) ?><?= str_repeat('☆', 5 - $rStars) ?>
              <span style="color:var(--hint);font-size:.7rem;margin-left:2px;">(<?= $r['rating'] ?? 4.5 ?>)</span>
            </div>
            <div class="prod-price-row">
              <span class="prod-price">₹<?= number_format($r['price']) ?></span>
              <?php if($rDisc > 0): ?>
                <span class="prod-orig">₹<?= number_format($r['original_price']) ?></span>
                <span class="prod-off"><?= $rDisc ?>% OFF</span>
              <?php endif; ?>
            </div>
            <div class="prod-unit">Per <?= htmlspecialchars($r['unit'] ?? 'piece') ?></div>
            <div class="prod-btns">
              <a href="/product-detail.php?id=<?= (int)$r['id'] ?>" class="btn-prod-view">
                <i class="bi bi-eye"></i> View
              </a>
              <button class="btn-prod-cart"
                      onclick="addToCart('product', <?= (int)$r['id'] ?>, 1, <?= (float)$r['price'] ?>)"
                      title="Add to Cart">
                <i class="bi bi-cart-plus"></i>
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>
</div>

<script>
const cartCSRF = '<?= generateCSRF() ?>';
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
