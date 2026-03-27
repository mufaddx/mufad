<?php
require_once __DIR__ . '/includes/functions.php';

$catId   = (int)($_GET['cat']  ?? 0);
$sort    = sanitizeInput($_GET['sort'] ?? 'popular');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 24;
$offset  = ($page - 1) * $perPage;

$categories = [];
$products   = [];
$total      = 0;

if ($pdo) {
    try {
        $categories = $pdo->query("SELECT * FROM product_categories ORDER BY name")->fetchAll();
        $where  = ['p.status = 1'];
        $params = [];
        if ($catId) { $where[] = 'p.category_id = ?'; $params[] = $catId; }
        $whereSQL = 'WHERE ' . implode(' AND ', $where);
        $orderSQL = match($sort) {
            'price_asc'  => 'p.price ASC',
            'price_desc' => 'p.price DESC',
            'newest'     => 'p.created_at DESC',
            default      => 'p.is_featured DESC, p.rating DESC',
        };
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereSQL");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT p.*, pc.name AS cat_name FROM products p LEFT JOIN product_categories pc ON p.category_id=pc.id $whereSQL ORDER BY $orderSQL LIMIT $perPage OFFSET $offset");
        $stmt->execute($params);
        $products = $stmt->fetchAll();
    } catch (Exception $e) {}
}

if (empty($products)) {
    $total = 12;
    $products = [
        ['id'=>1,'name'=>'BLDC Energy Saving Ceiling Fan 1200mm','cat_name'=>'CEILING FAN','price'=>2800,'original_price'=>3500,'image'=>'','icon'=>'🌀','badge'=>'HOT','unit'=>'piece','rating'=>4.8],
        ['id'=>2,'name'=>'1.5 Ton 5-Star Inverter AC','cat_name'=>'SPLIT AC','price'=>42000,'original_price'=>52000,'image'=>'','icon'=>'❄️','badge'=>'SALE','unit'=>'piece','rating'=>4.9],
        ['id'=>3,'name'=>'18W Round LED Panel Light','cat_name'=>'LED LIGHTING','price'=>320,'original_price'=>450,'image'=>'','icon'=>'💡','badge'=>'NEW','unit'=>'piece','rating'=>4.7],
        ['id'=>4,'name'=>'Premium Modular Switch Board 6-Module','cat_name'=>'SWITCHES','price'=>850,'original_price'=>1200,'image'=>'','icon'=>'🔌','badge'=>'','unit'=>'piece','rating'=>4.6],
        ['id'=>5,'name'=>'Solar Water Heater 100 Litre','cat_name'=>'SOLAR','price'=>18000,'original_price'=>22000,'image'=>'','icon'=>'☀️','badge'=>'HOT','unit'=>'piece','rating'=>4.8],
        ['id'=>6,'name'=>'Ceramic Floor Tiles 2x2 ft (Box)','cat_name'=>'TILES','price'=>780,'original_price'=>950,'image'=>'','icon'=>'🔲','badge'=>'SALE','unit'=>'box','rating'=>4.5],
        ['id'=>7,'name'=>'Heavy Duty Paint — 20 Litre','cat_name'=>'PAINTS','price'=>3200,'original_price'=>3800,'image'=>'','icon'=>'🎨','badge'=>'','unit'=>'can','rating'=>4.7],
        ['id'=>8,'name'=>'Stainless Steel Kitchen Sink','cat_name'=>'PLUMBING','price'=>2200,'original_price'=>2800,'image'=>'','icon'=>'🚿','badge'=>'NEW','unit'=>'piece','rating'=>4.6],
        ['id'=>9,'name'=>'4 mm Electrical Wire (90m Roll)','cat_name'=>'WIRING','price'=>1850,'original_price'=>2200,'image'=>'','icon'=>'⚡','badge'=>'','unit'=>'roll','rating'=>4.8],
        ['id'=>10,'name'=>'Wooden Laminate Flooring (Pack)','cat_name'=>'FLOORING','price'=>4500,'original_price'=>5500,'image'=>'','icon'=>'🪵','badge'=>'HOT','unit'=>'pack','rating'=>4.7],
        ['id'=>11,'name'=>'PVC Water Storage Tank 500L','cat_name'=>'TANKS','price'=>3800,'original_price'=>4500,'image'=>'','icon'=>'🪣','badge'=>'','unit'=>'piece','rating'=>4.9],
        ['id'=>12,'name'=>'Inverter Battery 150Ah Tubular','cat_name'=>'POWER BACKUP','price'=>12500,'original_price'=>15000,'image'=>'','icon'=>'🔋','badge'=>'SALE','unit'=>'piece','rating'=>4.8],
    ];
}
if (empty($categories)) {
    $categories = [
        ['id'=>1,'name'=>'Ceiling Fans'],['id'=>2,'name'=>'Air Conditioners'],['id'=>3,'name'=>'LED Lighting'],
        ['id'=>4,'name'=>'Switches'],['id'=>5,'name'=>'Solar'],['id'=>6,'name'=>'Tiles'],
        ['id'=>7,'name'=>'Paints'],['id'=>8,'name'=>'Plumbing'],['id'=>9,'name'=>'Wiring'],
    ];
}

$pages     = ceil($total / $perPage);
$pageTitle = 'Building Materials & Products — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
:root {
  --or:#e8560a; --or-lt:#fff5f0; --or-b:rgba(232,86,10,.18);
  --bl:#1565c0; --bl-lt:#f0f5ff; --bl-b:rgba(21,101,192,.18);
  --gr:#2e7d32; --gr-lt:#f0faf0;
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb; --white:#fff;
}

/* ══ FILTER BAR ══ */
.prd-filter-bar {
  background: var(--white);
  border-bottom: 1.5px solid var(--border);
  padding: 11px 5%;
  position: sticky; top: 66px; z-index: 200;
  box-shadow: 0 2px 10px rgba(26,35,50,.06);
}
@media(max-width:991px) { .prd-filter-bar { top: 60px; } }
.prd-filter-inner {
  max-width: 1400px; margin: 0 auto;
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.prd-fb-badge {
  display: flex; align-items: center; gap: 8px; flex-shrink: 0;
}
.prd-fb-icon {
  width: 34px; height: 34px; border-radius: 9px;
  background: rgba(21,101,192,.10); border: 1.5px solid rgba(21,101,192,.22);
  display: flex; align-items: center; justify-content: center;
  color: var(--bl); font-size: .9rem; flex-shrink: 0;
}
.prd-fb-title  { font-size: .82rem; font-weight: 800; color: var(--txt); }
.prd-fb-count  { font-size: .7rem; color: var(--hint); font-weight: 600; }
.prd-fb-divider { width:1px; height:28px; background:var(--border); flex-shrink:0; display:none; }
@media(min-width:400px) { .prd-fb-divider { display:block; } }

.prd-sel-wrap {
  position: relative; flex: 1; min-width: 150px; max-width: 260px;
}
.prd-sel-wrap .prd-chev {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  pointer-events: none; color: var(--hint); font-size: .7rem; transition: transform .18s;
}
.prd-select {
  width: 100%; appearance: none; -webkit-appearance: none;
  border: 1.5px solid var(--border); border-radius: 10px;
  padding: 9px 34px 9px 13px; font-size: .84rem;
  font-family: 'DM Sans', sans-serif; font-weight: 600;
  color: var(--txt); background: var(--bg);
  outline: none; cursor: pointer;
  transition: border-color .18s, box-shadow .18s, background .18s;
}
.prd-select:focus {
  border-color: var(--bl); box-shadow: 0 0 0 3px rgba(21,101,192,.10); background: #fff;
}
.prd-select.active {
  border-color: var(--bl); background: var(--bl-lt); color: var(--bl); font-weight: 700;
}
.prd-fb-clear {
  display: flex; align-items: center; gap: 5px;
  font-size: .75rem; color: var(--bl); font-weight: 700;
  background: var(--bl-lt); border: 1.5px solid var(--bl-b);
  padding: 6px 13px; border-radius: 20px; text-decoration: none;
  white-space: nowrap; flex-shrink: 0; transition: opacity .15s;
}
.prd-fb-clear:hover { opacity: .82; color: var(--bl); }

/* ══ GRID AREA ══ */
.prod-grid-area {
  background: var(--bg); padding: 22px 5% 60px; min-height: 60vh;
}
.prod-grid-inner { max-width: 1400px; margin: 0 auto; }

/* ══ CARDS GRID ══ */
.prod-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
}
@media(min-width:576px)  { .prod-grid { grid-template-columns: repeat(3,1fr); gap:16px; } }
@media(min-width:992px)  { .prod-grid { grid-template-columns: repeat(4,1fr); gap:18px; } }
@media(min-width:1400px) { .prod-grid { grid-template-columns: repeat(5,1fr); } }

/* ══ CARD ══ */
.prod-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 16px; overflow: hidden;
  display: flex; flex-direction: column;
  transition: all .22s; box-shadow: 0 2px 10px rgba(26,35,50,.05);
}
.prod-card:hover {
  border-color: rgba(21,101,192,.22);
  box-shadow: 0 10px 30px rgba(26,35,50,.12); transform: translateY(-3px);
}
.prod-img {
  height: 150px;
  background: linear-gradient(135deg, var(--bl-lt), #f5f8ff);
  display: flex; align-items: center; justify-content: center;
  position: relative; border-bottom: 1px solid var(--border);
  font-size: 3rem; overflow: hidden;
}
.prod-img img { width:100%; height:100%; object-fit:cover; }
.prod-badge {
  position: absolute; top: 9px; left: 9px;
  font-size: .58rem; font-weight: 800;
  padding: 3px 9px; border-radius: 18px; text-transform: uppercase;
}
.b-hot  { background:#fff0f0; color:#dc2626; border:1px solid #fecaca; }
.b-new  { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
.b-sale { background:#fffbeb; color:#d97706; border:1px solid #fde68a; }
.prod-body { padding: 13px 14px 14px; display:flex; flex-direction:column; flex:1; }
.prod-cat  { font-size:.6rem; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:var(--bl); margin-bottom:4px; }
.prod-name { font-weight:700; font-size:.87rem; color:var(--txt); line-height:1.35; margin-bottom:7px; flex-grow:1; }
.prod-stars{ color:#f59e0b; font-size:.74rem; margin-bottom:6px; }
.prod-price-row { margin-bottom:4px; }
.prod-price { font-weight:800; font-size:1.05rem; color:var(--txt); }
.prod-orig  { text-decoration:line-through; color:var(--hint); font-size:.8rem; margin-left:5px; }
.prod-off   { font-size:.7rem; font-weight:800; color:#16a34a; margin-left:5px; }
.prod-unit  { font-size:.7rem; color:var(--hint); margin-bottom:12px; }
.prod-btns  { display:flex; gap:8px; margin-top:auto; }
.btn-prod-view {
  flex:1; display:flex; align-items:center; justify-content:center; gap:5px;
  background:var(--bg); color:var(--txt) !important; font-weight:700;
  font-size:.75rem; padding:9px; border-radius:9px; text-decoration:none;
  border:1.5px solid var(--border); transition:.18s;
}
.btn-prod-view:hover { background:#e8edf5; }
.btn-prod-cart {
  width:40px; height:40px; display:flex; align-items:center; justify-content:center;
  background:linear-gradient(135deg,var(--bl),#0d47a1); color:#fff;
  border:none; border-radius:9px; cursor:pointer; font-size:.95rem;
  transition:.18s; box-shadow:0 2px 8px rgba(21,101,192,.28); flex-shrink:0;
}
.btn-prod-cart:hover { transform:translateY(-1px); box-shadow:0 5px 14px rgba(21,101,192,.38); }
</style>

<!-- ══ FILTER BAR ══ -->
<div class="prd-filter-bar">
  <div class="prd-filter-inner">

    <div class="prd-fb-badge">
      <div class="prd-fb-icon"><i class="bi bi-bag-fill"></i></div>
      <div>
        <div class="prd-fb-title">Building Materials & Products</div>
        <div class="prd-fb-count"><?= number_format($total) ?> product<?= $total !== 1 ? 's' : '' ?></div>
      </div>
    </div>

    <div class="prd-fb-divider"></div>

    <!-- Category dropdown -->
    <div class="prd-sel-wrap">
      <select class="prd-select <?= $catId ? 'active' : '' ?>"
              onchange="window.location.href='/products.php?sort=<?= urlencode($sort) ?>'+(this.value?'&cat='+this.value:'')">
        <option value="">All Categories</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= $catId === (int)$c['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <i class="bi bi-chevron-down prd-chev"></i>
    </div>

    <!-- Sort dropdown -->
    <div class="prd-sel-wrap" style="max-width:200px;">
      <select class="prd-select"
              onchange="prdSort(this.value)">
        <option value="popular"    <?= $sort==='popular'    ? 'selected':'' ?>>Most Popular</option>
        <option value="newest"     <?= $sort==='newest'     ? 'selected':'' ?>>Newest First</option>
        <option value="price_asc"  <?= $sort==='price_asc'  ? 'selected':'' ?>>Price: Low → High</option>
        <option value="price_desc" <?= $sort==='price_desc' ? 'selected':'' ?>>Price: High → Low</option>
      </select>
      <i class="bi bi-chevron-down prd-chev"></i>
    </div>

    <?php if ($catId): ?>
      <a href="/products.php?sort=<?= urlencode($sort) ?>" class="prd-fb-clear">
        <i class="bi bi-x-lg"></i> Clear
      </a>
    <?php endif; ?>

  </div>
</div>

<!-- ══ GRID ══ -->
<div class="prod-grid-area">
  <div class="prod-grid-inner">

    <?php if (empty($products)): ?>
      <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:3.5rem;margin-bottom:16px;">🔍</div>
        <h5 style="color:var(--mid);margin-bottom:8px;">No products found</h5>
        <a href="/products.php" style="color:var(--bl);font-weight:600;text-decoration:none;">Clear filters</a>
      </div>
    <?php else: ?>

    <div class="prod-grid">
      <?php foreach ($products as $p):
        $disc  = (!empty($p['original_price']) && $p['original_price'] > $p['price'])
                 ? round((1 - $p['price'] / $p['original_price']) * 100) : 0;
        $stars = min(5, max(1, round($p['rating'] ?? 4)));
        $badge = strtolower($p['badge'] ?? '');
      ?>
      <div class="prod-card">
        <div class="prod-img">
          <?php if (!empty($p['image'])): ?>
            <img src="/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
          <?php else: ?>
            <?= htmlspecialchars($p['icon'] ?? '📦') ?>
          <?php endif; ?>
          <?php if (!empty($p['badge'])): ?>
            <span class="prod-badge b-<?= $badge ?>"><?= strtoupper($p['badge']) ?></span>
          <?php endif; ?>
        </div>
        <div class="prod-body">
          <div class="prod-cat"><?= htmlspecialchars($p['cat_name'] ?? '') ?></div>
          <div class="prod-name"><?= htmlspecialchars($p['name']) ?></div>
          <div class="prod-stars">
            <?= str_repeat('★', $stars) ?><?= str_repeat('☆', 5 - $stars) ?>
            <span style="color:var(--hint);font-size:.7rem;margin-left:2px;">(<?= $p['rating'] ?? 4.5 ?>)</span>
          </div>
          <div class="prod-price-row">
            <span class="prod-price">₹<?= number_format($p['price']) ?></span>
            <?php if ($disc > 0): ?>
              <span class="prod-orig">₹<?= number_format($p['original_price']) ?></span>
              <span class="prod-off"><?= $disc ?>% OFF</span>
            <?php endif; ?>
          </div>
          <div class="prod-unit">Per <?= htmlspecialchars($p['unit'] ?? 'piece') ?></div>
          <div class="prod-btns">
            <a href="/product-detail.php?id=<?= (int)$p['id'] ?>" class="btn-prod-view">
              <i class="bi bi-eye"></i> View
            </a>
            <button class="btn-prod-cart"
                    onclick="addToCart('product', <?= (int)$p['id'] ?>, 1, <?= (float)$p['price'] ?>)"
                    title="Add to Cart">
              <i class="bi bi-cart-plus"></i>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
      <div style="margin-top:28px;display:flex;justify-content:center;">
        <?php
        $baseUrl = '?' . http_build_query(array_filter(['cat'=>$catId,'sort'=>$sort]));
        echo buildPagination($page, $pages, $baseUrl);
        ?>
      </div>
    <?php endif; ?>

    <?php endif; ?>
  </div>
</div>

<script>
function prdSort(val) {
  var url = new URL(window.location.href);
  url.searchParams.set('sort', val);
  window.location.href = url.toString();
}
const cartCSRF = '<?= generateCSRF() ?>';
function addToCart(type, id, qty, price) {
  fetch('/api/cart-add.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({item_type:type,item_id:id,quantity:qty,price:price,csrf:cartCSRF})
  })
  .then(r=>r.json())
  .then(d=>{
    if(d.success){
      document.querySelectorAll('.cart-badge').forEach(el=>el.textContent=d.cart_count);
      showToast('Added to cart!','success');
    } else { showToast(d.message||'Error','error'); }
  }).catch(()=>showToast('Added!','success'));
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>