<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Plots for Sale — " . SITE_NAME;
$city   = isset($_GET['city'])   ? clean($_GET['city'])   : '';
$type   = isset($_GET['type'])   ? clean($_GET['type'])   : '';
$status = isset($_GET['status']) ? clean($_GET['status']) : 'Available';

$where  = ["1=1"];
$params = [];
if ($city)   { $where[] = "city = ?";   $params[] = $city; }
if ($type)   { $where[] = "type = ?";   $params[] = $type; }
if ($status) { $where[] = "status = ?"; $params[] = $status; }
$whereStr = implode(' AND ', $where);

try {
    $stmt = $pdo->prepare("SELECT * FROM plots WHERE $whereStr ORDER BY created_at DESC");
    $stmt->execute($params);
    $plots = $stmt->fetchAll();
} catch(Exception $e) { $plots = []; }

if (empty($plots)) {
    $allDemo = [
        ['id'=>1,'title'=>'Corner Residential Plot — Sector 62','location'=>'Sector 62','city'=>'Noida','type'=>'Residential','size_sqft'=>900,'size_sqyard'=>100,'price'=>4500000,'facing'=>'Corner','status'=>'Available','images'=>null],
        ['id'=>2,'title'=>'Commercial Plot — Dwarka Expressway','location'=>'Dwarka Expressway','city'=>'Gurugram','type'=>'Commercial','size_sqft'=>2700,'size_sqyard'=>300,'price'=>12000000,'facing'=>'North','status'=>'Available','images'=>null],
        ['id'=>3,'title'=>'East Facing Plot — Rohini','location'=>'Rohini','city'=>'Delhi','type'=>'Residential','size_sqft'=>675,'size_sqyard'=>75,'price'=>3800000,'facing'=>'East','status'=>'Available','images'=>null],
        ['id'=>4,'title'=>'Residential Plot — Greater Noida West','location'=>'Greater Noida West','city'=>'Greater Noida','type'=>'Residential','size_sqft'=>1200,'size_sqyard'=>133,'price'=>2800000,'facing'=>'West','status'=>'Available','images'=>null],
        ['id'=>5,'title'=>'Residential Plot — Sector 22, Faridabad','location'=>'Sector 22','city'=>'Faridabad','type'=>'Residential','size_sqft'=>1500,'size_sqyard'=>166,'price'=>3200000,'facing'=>'South','status'=>'Available','images'=>null],
        ['id'=>6,'title'=>'Commercial Plot — NH-58, Ghaziabad','location'=>'NH-58 Highway','city'=>'Ghaziabad','type'=>'Commercial','size_sqft'=>3600,'size_sqyard'=>400,'price'=>9500000,'facing'=>'Corner','status'=>'Available','images'=>null],
    ];
    if ($city) $allDemo = array_values(array_filter($allDemo, fn($p) => $p['city'] === $city));
    if ($type) $allDemo = array_values(array_filter($allDemo, fn($p) => $p['type'] === $type));
    $plots = $allDemo;
}

$total = count($plots);
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

/* ══ FILTER BAR ══ */
.plt-filter-bar {
  background: var(--white);
  border-bottom: 1.5px solid var(--border);
  padding: 11px 5%;
  position: sticky; top: 66px; z-index: 200;
  box-shadow: 0 2px 10px rgba(26,35,50,.06);
}
@media(max-width:991px) { .plt-filter-bar { top: 60px; } }
.plt-filter-inner {
  max-width: 1500px; margin: 0 auto;
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.plt-fb-badge {
  display: flex; align-items: center; gap: 8px; flex-shrink: 0;
}
.plt-fb-icon {
  width: 34px; height: 34px; border-radius: 9px;
  background: rgba(46,125,50,.10); border: 1.5px solid rgba(46,125,50,.22);
  display: flex; align-items: center; justify-content: center;
  color: var(--gr); font-size: .9rem; flex-shrink: 0;
}
.plt-fb-title  { font-size: .82rem; font-weight: 800; color: var(--txt); }
.plt-fb-count  { font-size: .7rem; color: var(--hint); font-weight: 600; }
.plt-fb-divider { width:1px; height:28px; background:var(--border); flex-shrink:0; display:none; }
@media(min-width:400px) { .plt-fb-divider { display:block; } }

.plt-sel-wrap {
  position: relative; flex: 1; min-width: 140px; max-width: 220px;
}
.plt-sel-wrap .plt-chev {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  pointer-events: none; color: var(--hint); font-size: .7rem;
  transition: transform .18s;
}
.plt-select {
  width: 100%; appearance: none; -webkit-appearance: none;
  border: 1.5px solid var(--border); border-radius: 10px;
  padding: 9px 34px 9px 13px; font-size: .84rem;
  font-family: 'DM Sans', sans-serif; font-weight: 600;
  color: var(--txt); background: var(--bg);
  outline: none; cursor: pointer;
  transition: border-color .18s, box-shadow .18s, background .18s;
}
.plt-select:focus {
  border-color: var(--gr); box-shadow: 0 0 0 3px rgba(46,125,50,.10); background: #fff;
}
.plt-select.active {
  border-color: var(--gr); background: var(--gr-lt); color: var(--gr); font-weight: 700;
}
.plt-fb-clear {
  display: flex; align-items: center; gap: 5px;
  font-size: .75rem; color: var(--gr); font-weight: 700;
  background: var(--gr-lt); border: 1.5px solid var(--gr-b);
  padding: 6px 13px; border-radius: 20px; text-decoration: none;
  white-space: nowrap; flex-shrink: 0; transition: opacity .15s;
}
.plt-fb-clear:hover { opacity: .82; color: var(--gr); }

/* ══ GRID ══ */
.plot-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 16px;
  padding: 28px 5% 60px;
  max-width: 1500px; margin: 0 auto;
  background: var(--bg);
}
@media(min-width:576px)  { .plot-grid { grid-template-columns: repeat(2,1fr); } }
@media(min-width:992px)  { .plot-grid { grid-template-columns: repeat(3,1fr); gap:18px; } }
@media(min-width:1400px) { .plot-grid { grid-template-columns: repeat(4,1fr); } }

/* ══ CARD ══ */
.plot-c {
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 18px; overflow: hidden;
  transition: all .22s;
  box-shadow: 0 2px 12px rgba(26,35,50,.06);
  display: flex; flex-direction: column;
}
.plot-c:hover {
  border-color: rgba(46,125,50,.25);
  box-shadow: 0 10px 32px rgba(26,35,50,.12);
  transform: translateY(-3px);
}
.plot-img-area {
  background: linear-gradient(135deg, var(--gr-lt), #f5fbf5);
  height: 150px;
  display: flex; align-items: center; justify-content: center;
  position: relative; overflow: hidden;
  border-bottom: 1px solid var(--border);
}
.plot-img-area img { width:100%; height:100%; object-fit:cover; }
.plot-no-img {
  display: flex; flex-direction: column; align-items: center; gap: 6px; color: var(--gr);
}
.plot-no-img i    { font-size: 2.4rem; }
.plot-no-img span { font-size: .72rem; font-weight: 600; color: var(--hint); }
.plot-status-chip {
  position: absolute; top: 10px; right: 10px;
  font-size: .6rem; font-weight: 800; padding: 4px 10px;
  border-radius: 20px; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;
}
.plot-status-sold     { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
.plot-status-reserved { background: #fffbeb; color: #d97706; border-color: #fde68a; }

.plot-body { padding: 15px 15px 13px; display: flex; flex-direction: column; flex: 1; }
.plot-badges { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px; }
.plot-type-chip {
  font-size: .6rem; font-weight: 800;
  background: var(--gr-lt); color: var(--gr); border: 1px solid var(--gr-b);
  padding: 3px 9px; border-radius: 18px;
}
.plot-facing-chip {
  font-size: .6rem; font-weight: 700;
  background: var(--bl-lt); color: var(--bl); border: 1px solid var(--bl-b);
  padding: 3px 9px; border-radius: 18px;
}
.plot-title {
  font-family: 'Playfair Display', serif;
  font-size: .95rem; font-weight: 800; color: var(--txt);
  line-height: 1.3; margin-bottom: 5px;
}
@media(max-width:575px) { .plot-title { font-size: .88rem; } }
.plot-loc {
  display: flex; align-items: center; gap: 5px;
  font-size: .76rem; color: var(--light); margin-bottom: 12px;
}
.plot-loc i { color: var(--gr); font-size: .72rem; }
.plot-specs {
  display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px;
}
.plot-spec-box {
  background: var(--bg); border-radius: 9px; padding: 8px 10px; border: 1px solid var(--border);
}
.spec-label {
  font-size: .6rem; color: var(--hint); font-weight: 700;
  text-transform: uppercase; letter-spacing: .5px; margin-bottom: 2px;
}
.spec-val { font-size: .9rem; font-weight: 800; color: var(--txt); }
.plot-price {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem; font-weight: 900; color: var(--gr); margin-bottom: 2px;
}
.plot-psf { font-size: .7rem; color: var(--hint); margin-bottom: 13px; }
.plot-btns { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: auto; }
.btn-plot-view {
  display: flex; align-items: center; justify-content: center; gap: 5px;
  background: var(--bg); color: var(--txt) !important; font-weight: 700;
  font-size: .76rem; padding: 10px; border-radius: 10px;
  text-decoration: none; border: 1.5px solid var(--border); transition: all .18s;
}
.btn-plot-view:hover { background: #e8edf5; }
.btn-plot-enq {
  display: flex; align-items: center; justify-content: center; gap: 5px;
  background: linear-gradient(135deg, var(--gr), #1b5e20); color: #fff;
  font-weight: 700; font-size: .76rem; padding: 10px; border-radius: 10px;
  border: none; cursor: pointer; transition: all .18s;
  box-shadow: 0 3px 10px rgba(46,125,50,.25);
}
.btn-plot-enq:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(46,125,50,.35); }
</style>

<!-- ══ FILTER BAR ══ -->
<div class="plt-filter-bar">
  <div class="plt-filter-inner">

    <div class="plt-fb-badge">
      <div class="plt-fb-icon"><i class="bi bi-map-fill"></i></div>
      <div>
        <div class="plt-fb-title">Plots for Sale</div>
        <div class="plt-fb-count"><?= $total ?> plot<?= $total !== 1 ? 's' : '' ?></div>
      </div>
    </div>

    <div class="plt-fb-divider"></div>

    <!-- Type dropdown -->
    <div class="plt-sel-wrap">
      <select class="plt-select <?= $type ? 'active' : '' ?>"
              onchange="pltGo('type', this.value)">
        <option value="">All Types</option>
        <?php foreach (['Residential','Commercial','Agricultural','Industrial','Farm House'] as $t): ?>
          <option value="<?= $t ?>" <?= $type === $t ? 'selected' : '' ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
      <i class="bi bi-chevron-down plt-chev"></i>
    </div>

    <!-- City dropdown -->
    <div class="plt-sel-wrap">
      <select class="plt-select <?= $city ? 'active' : '' ?>"
              onchange="pltGo('city', this.value)">
        <option value="">All Cities</option>
        <?php foreach (['Delhi','Noida','Gurugram','Ghaziabad','Faridabad','Greater Noida'] as $c): ?>
          <option value="<?= $c ?>" <?= $city === $c ? 'selected' : '' ?>><?= $c ?></option>
        <?php endforeach; ?>
      </select>
      <i class="bi bi-chevron-down plt-chev"></i>
    </div>

    <?php if ($type || $city): ?>
      <a href="/plots.php" class="plt-fb-clear"><i class="bi bi-x-lg"></i> Clear</a>
    <?php endif; ?>

  </div>
</div>

<!-- ══ GRID ══ -->
<div style="background:var(--bg);min-height:70vh;">
<?php if (empty($plots)): ?>
  <div style="text-align:center;padding:80px 20px;">
    <div style="font-size:3.5rem;margin-bottom:16px;">📍</div>
    <h5 style="color:var(--mid);margin-bottom:8px;">No plots found</h5>
    <a href="/plots.php" style="color:var(--gr);font-weight:600;text-decoration:none;">← View all plots</a>
  </div>
<?php else: ?>
<div class="plot-grid">
  <?php foreach ($plots as $pl): ?>
  <div class="plot-c">
    <div class="plot-img-area">
      <?php if (!empty($pl['images'])):
        $imgs = json_decode($pl['images'], true);
        $img  = is_array($imgs) ? $imgs[0] : $pl['images'];
      ?>
        <img src="/uploads/plots/<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($pl['title']) ?>">
      <?php else: ?>
        <div class="plot-no-img">
          <i class="bi bi-geo-alt-fill"></i>
          <span>Plot Image</span>
        </div>
      <?php endif; ?>
      <?php
        $sc = match($pl['status']) { 'Available'=>'', 'Sold'=>' plot-status-sold', 'Reserved'=>' plot-status-reserved', default=>'' };
        $sl = match($pl['status']) { 'Available'=>'✓ Available', 'Sold'=>'✗ Sold', 'Reserved'=>'⏳ Reserved', default=>$pl['status'] };
      ?>
      <span class="plot-status-chip<?= $sc ?>"><?= $sl ?></span>
    </div>

    <div class="plot-body">
      <div class="plot-badges">
        <span class="plot-type-chip"><?= htmlspecialchars($pl['type']) ?></span>
        <span class="plot-facing-chip"><?= htmlspecialchars($pl['facing']) ?> Facing</span>
      </div>
      <div class="plot-title"><?= htmlspecialchars($pl['title']) ?></div>
      <div class="plot-loc">
        <i class="bi bi-geo-alt-fill"></i>
        <?= htmlspecialchars($pl['location']) ?>, <?= htmlspecialchars($pl['city']) ?>
      </div>
      <div class="plot-specs">
        <div class="plot-spec-box">
          <div class="spec-label">Area (Sq.Ft)</div>
          <div class="spec-val"><?= number_format($pl['size_sqft']) ?></div>
        </div>
        <div class="plot-spec-box">
          <div class="spec-label">Area (Sq.Yd)</div>
          <div class="spec-val"><?= number_format($pl['size_sqyard'], 1) ?></div>
        </div>
      </div>
      <div class="plot-price">₹<?= number_format($pl['price']) ?></div>
      <div class="plot-psf">₹<?= number_format($pl['price'] / max(1,$pl['size_sqft'])) ?>/sq.ft</div>
      <div class="plot-btns">
        <a href="/plot-detail.php?id=<?= (int)$pl['id'] ?>" class="btn-plot-view">
          <i class="bi bi-eye"></i> View Details
        </a>
        <button class="btn-plot-enq"
                onclick="openEnquiryPopup('Plot: <?= addslashes(htmlspecialchars($pl['title'])) ?>','plot','Our property expert will call you!')">
          <i class="bi bi-telephone-fill"></i> Enquire Now
        </button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<script>
function pltGo(param, val) {
  var url = new URL(window.location.href);
  if (val) url.searchParams.set(param, val);
  else     url.searchParams.delete(param);
  window.location.href = url.toString();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>