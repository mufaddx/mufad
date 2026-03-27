<?php
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) redirect('/plots.php');

$plot = null; $similar = [];

// Demo plots — same as plots.php fallback
$_demoPlots = [
    1 => ['id'=>1,'title'=>'Corner Residential Plot — Sector 62','location'=>'Sector 62','city'=>'Noida','type'=>'Residential','size_sqft'=>900,'size_sqyard'=>100,'price'=>4500000,'facing'=>'Corner','status'=>'Available','images'=>null,'description'=>'Prime corner plot in Sector 62, Noida. Excellent connectivity to metro and expressway. Suitable for residential construction.','features'=>'Metro Nearby,Park View,Corner Plot,24x7 Security,Wide Road','map_embed'=>''],
    2 => ['id'=>2,'title'=>'Commercial Plot — Dwarka Expressway','location'=>'Dwarka Expressway','city'=>'Gurugram','type'=>'Commercial','size_sqft'=>2700,'size_sqyard'=>300,'price'=>12000000,'facing'=>'North','status'=>'Available','images'=>null,'description'=>'High-visibility commercial plot on Dwarka Expressway. Ideal for office complex or retail development.','features'=>'Main Road Facing,High Visibility,Commercial Zone,Excellent ROI','map_embed'=>''],
    3 => ['id'=>3,'title'=>'East Facing Plot — Rohini','location'=>'Rohini','city'=>'Delhi','type'=>'Residential','size_sqft'=>675,'size_sqyard'=>75,'price'=>3800000,'facing'=>'East','status'=>'Available','images'=>null,'description'=>'Vastu-compliant East facing plot in Rohini. Well-connected locality with all amenities nearby.','features'=>'East Facing,Vastu Compliant,Metro Access,Schools Nearby','map_embed'=>''],
    4 => ['id'=>4,'title'=>'Residential Plot — Greater Noida West','location'=>'Greater Noida West','city'=>'Greater Noida','type'=>'Residential','size_sqft'=>1200,'size_sqyard'=>133,'price'=>2800000,'facing'=>'West','status'=>'Available','images'=>null,'description'=>'Spacious residential plot in developing Greater Noida West. Great investment opportunity.','features'=>'Developing Area,Investment Opportunity,Wide Roads,Green Surroundings','map_embed'=>''],
    5 => ['id'=>5,'title'=>'Residential Plot — Sector 22, Faridabad','location'=>'Sector 22','city'=>'Faridabad','type'=>'Residential','size_sqft'=>1500,'size_sqyard'=>166,'price'=>3200000,'facing'=>'South','status'=>'Available','images'=>null,'description'=>'Good sized residential plot in Faridabad. Peaceful locality with schools, hospitals nearby.','features'=>'Schools Nearby,Hospital Access,Peaceful Locality','map_embed'=>''],
    6 => ['id'=>6,'title'=>'Commercial Plot — NH-58, Ghaziabad','location'=>'NH-58 Highway','city'=>'Ghaziabad','type'=>'Commercial','size_sqft'=>3600,'size_sqyard'=>400,'price'=>9500000,'facing'=>'Corner','status'=>'Available','images'=>null,'description'=>'Excellent commercial opportunity on NH-58. High traffic zone perfect for showroom or warehouse.','features'=>'Highway Facing,High Traffic Zone,Warehouse Suitable,Large Size','map_embed'=>''],
];

try {
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM plots WHERE id = ?");
        $stmt->execute([$id]);
        $plot = $stmt->fetch();
        if ($plot) {
            $simStmt = $pdo->prepare("SELECT * FROM plots WHERE city = ? AND id != ? AND status = 'Available' LIMIT 3");
            $simStmt->execute([$plot['city'], $id]);
            $similar = $simStmt->fetchAll();
        }
    }
} catch(Exception $e) { /* use demo */ }

// Fallback to demo data
if (!$plot) {
    $plot = $_demoPlots[$id] ?? null;
    if (!$plot) redirect('/plots.php');
    // Similar from same city in demo
    $similar = array_values(array_filter($_demoPlots, fn($p) => $p['city'] === $plot['city'] && $p['id'] !== $id));
    $similar = array_slice($similar, 0, 3);
}

$enquiryMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enquire'])) {
    if (!validateCSRF($_POST['csrf'] ?? '')) {
        $enquiryMsg = 'error:Security check failed.';
    } else {
        $name  = clean($_POST['name'] ?? '');
        $phone = clean($_POST['phone'] ?? '');
        $msg   = clean($_POST['message'] ?? '');
        if ($name && $phone) {
            try {
                $ins = $pdo->prepare("INSERT INTO enquiries (name,phone,service_interest,message,source,status,created_at) VALUES (?,?,?,?,?,?,NOW())");
                $ins->execute([$name, $phone, "Plot: ".$plot['title'], $msg, 'plot', 'New']);
                $enquiryMsg = 'success:Enquiry submitted! We\'ll call you within 2 hours.';
            } catch(Exception $e) { $enquiryMsg = 'error:Error. Please call us directly.'; }
        } else { $enquiryMsg = 'warning:Please fill name and phone number.'; }
    }
}

$imgs         = is_string($plot['images']) ? json_decode($plot['images'], true) : [];
if (!is_array($imgs)) $imgs = [];
$featArr      = !empty($plot['features']) ? explode(',', $plot['features']) : [];
$pricePerSqft = $plot['size_sqft'] > 0 ? round($plot['price'] / $plot['size_sqft']) : 0;
$pageTitle    = htmlspecialchars($plot['title']) . ' — Plots — ' . SITE_NAME;

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
:root {
  --or:#e8560a; --or-lt:#fff5f0; --or-b:rgba(232,86,10,.18);
  --bl:#1565c0; --bl-lt:#f0f5ff; --bl-b:rgba(21,101,192,.18);
  --gr:#2e7d32; --gr-lt:#f0faf0; --gr-b:rgba(46,125,50,.2);
  --gd:#c9a84c; --gd-lt:#fffbf0; --gd-b:rgba(201,168,76,.25);
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb; --white:#fff;
}

/* ── PAGE WRAPPER ── */
.pd-page { background: var(--bg); padding: 22px 5% 60px; min-height: 80vh; }
.pd-inner {
  max-width: 1200px; margin: 0 auto;
  display: grid; grid-template-columns: 1fr;
  gap: 20px; align-items: start;
}
@media(min-width:992px) { .pd-inner { grid-template-columns: 1fr 340px; } }

/* ── CARD BASE ── */
.pd-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 18px; overflow: hidden;
  box-shadow: 0 2px 12px rgba(26,35,50,.05); margin-bottom: 16px;
}
.pd-card-body { padding: 20px 22px; }
.pd-card-title {
  font-size: .9rem; font-weight: 800; color: var(--txt);
  margin-bottom: 16px; display: flex; align-items: center; gap: 7px;
}
.pd-card-title i { color: var(--gr); }

/* ── GALLERY ── */
.pd-gallery { position: relative; }
.pd-gallery .carousel-item img {
  width: 100%; height: 260px; object-fit: cover; display: block;
}
@media(min-width:576px) { .pd-gallery .carousel-item img { height: 340px; } }
@media(min-width:992px) { .pd-gallery .carousel-item img { height: 420px; } }
.pd-no-img {
  width: 100%; height: 240px;
  background: linear-gradient(135deg, var(--gr-lt), #f5fbf5);
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  color: var(--gr);
}
.pd-no-img i   { font-size: 3.5rem; opacity: .5; margin-bottom: 8px; }
.pd-no-img span { font-size: .8rem; color: var(--hint); font-weight: 600; }
.pd-status-chip {
  position: absolute; top: 14px; right: 14px;
  font-size: .65rem; font-weight: 800; padding: 5px 13px; border-radius: 20px;
}
.chip-avail    { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.chip-sold     { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.chip-reserved { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }

/* ── PLOT INFO ── */
.pd-badges { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
.pd-badge-type {
  font-size: .62rem; font-weight: 800;
  background: var(--gr-lt); color: var(--gr); border: 1px solid var(--gr-b);
  padding: 3px 10px; border-radius: 18px;
}
.pd-badge-facing {
  font-size: .62rem; font-weight: 700;
  background: var(--bl-lt); color: var(--bl); border: 1px solid var(--bl-b);
  padding: 3px 10px; border-radius: 18px;
}
.pd-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(1.2rem, 3vw, 1.7rem);
  font-weight: 900; color: var(--txt); line-height: 1.25; margin-bottom: 7px;
}
.pd-location {
  display: flex; align-items: center; gap: 5px;
  font-size: .82rem; color: var(--light); margin-bottom: 16px;
}
.pd-location i { color: var(--gr); }

/* Price block */
.pd-price-block {
  background: var(--gd-lt); border: 1.5px solid var(--gd-b);
  border-radius: 14px; padding: 16px 18px; margin-bottom: 16px;
}
.pd-price-lbl { font-size: .68rem; color: var(--hint); font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 5px; }
.pd-price-big {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.7rem, 5vw, 2.4rem); font-weight: 900; color: var(--gd); line-height: 1;
}
.pd-price-psf { font-size: .78rem; color: var(--mid); margin-top: 6px; }
.pd-price-psf strong { color: var(--txt); font-weight: 800; }

/* Specs grid */
.pd-specs {
  display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;
}
@media(min-width:480px) { .pd-specs { grid-template-columns: repeat(3, 1fr); } }
.pd-spec {
  background: var(--bg); border: 1.5px solid var(--border);
  border-radius: 10px; padding: 10px 12px;
}
.pd-spec-lbl {
  font-size: .6rem; color: var(--hint); font-weight: 700;
  text-transform: uppercase; letter-spacing: .5px; margin-bottom: 3px;
}
.pd-spec-val { font-size: .88rem; font-weight: 800; color: var(--txt); }

/* Features */
.pd-feat-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 4px; }
@media(min-width:480px) { .pd-feat-grid { grid-template-columns: repeat(3, 1fr); } }
.pd-feat-item {
  display: flex; align-items: center; gap: 7px;
  font-size: .8rem; color: var(--mid); font-weight: 600; padding: 5px 0;
}
.pd-feat-item::before { content: '✓'; color: var(--gr); font-weight: 900; font-size: .75rem; flex-shrink: 0; }

/* Similar plots */
.pd-sim-grid { display: grid; grid-template-columns: 1fr; gap: 12px; }
@media(min-width:480px) { .pd-sim-grid { grid-template-columns: repeat(3, 1fr); } }
.pd-sim-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 14px; overflow: hidden;
  transition: all .2s; text-decoration: none;
  display: block;
}
.pd-sim-card:hover { border-color: var(--gr-b); transform: translateY(-2px); box-shadow: 0 6px 18px rgba(26,35,50,.1); }
.pd-sim-img {
  height: 90px; background: var(--gr-lt);
  display: flex; align-items: center; justify-content: center;
  font-size: 2rem; overflow: hidden;
}
.pd-sim-img img { width: 100%; height: 100%; object-fit: cover; }
.pd-sim-body { padding: 11px; }
.pd-sim-name { font-size: .78rem; font-weight: 700; color: var(--txt); line-height: 1.3; margin-bottom: 3px; }
.pd-sim-loc  { font-size: .7rem; color: var(--hint); margin-bottom: 6px; }
.pd-sim-price { font-size: .88rem; font-weight: 900; color: var(--gr); margin-bottom: 8px; }
.pd-sim-btn {
  display: block; text-align: center; background: var(--gr-lt);
  color: var(--gr); font-size: .72rem; font-weight: 700;
  padding: 6px; border-radius: 8px; text-decoration: none;
  border: 1px solid var(--gr-b); transition: all .15s;
}
.pd-sim-btn:hover { background: var(--gr); color: #fff; }

/* ── SIDEBAR ── */
.pd-sidebar {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 18px; padding: 22px;
  box-shadow: 0 2px 12px rgba(26,35,50,.05);
  position: sticky; top: 80px;
}
.pd-sidebar-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.05rem; font-weight: 900; color: var(--txt);
  margin-bottom: 4px;
}
.pd-sidebar-sub { font-size: .76rem; color: var(--hint); margin-bottom: 16px; }

/* Quick summary */
.pd-qsum { background: var(--bg); border: 1.5px solid var(--border); border-radius: 12px; padding: 12px 14px; margin-bottom: 16px; }
.pd-qsum-lbl { font-size: .6rem; color: var(--hint); font-weight: 700; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; }
.pd-qsum-row { display: flex; justify-content: space-between; font-size: .79rem; margin-bottom: 5px; }
.pd-qsum-row:last-child { margin-bottom: 0; }
.pd-qsum-key { color: var(--mid); }
.pd-qsum-val { font-weight: 800; color: var(--txt); }

/* Enquiry form */
.pd-form-lbl { font-size: .78rem; font-weight: 700; color: var(--txt); display: block; margin-bottom: 5px; }
.pd-form-inp, .pd-form-ta {
  width: 100%; border: 1.5px solid var(--border); border-radius: 10px;
  padding: 9px 13px; font-size: .85rem;
  font-family: 'DM Sans', sans-serif; color: var(--txt);
  background: var(--bg); outline: none; margin-bottom: 10px;
  transition: border-color .18s, box-shadow .18s, background .18s;
}
.pd-form-inp:focus, .pd-form-ta:focus {
  border-color: var(--gr); box-shadow: 0 0 0 3px rgba(46,125,50,.1); background: #fff;
}
.pd-form-ta { resize: vertical; min-height: 70px; }
.btn-pd-submit {
  width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;
  background: linear-gradient(135deg, var(--gr), #1b5e20);
  color: #fff; font-weight: 800; font-size: .9rem;
  padding: 13px; border-radius: 12px; border: none; cursor: pointer;
  box-shadow: 0 4px 14px rgba(46,125,50,.3); transition: all .22s;
  margin-bottom: 9px;
}
.btn-pd-submit:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(46,125,50,.38); }
.btn-pd-wa {
  width: 100%; display: flex; align-items: center; justify-content: center; gap: 7px;
  background: #25d366; color: #fff; font-weight: 700; font-size: .88rem;
  padding: 11px; border-radius: 11px; text-decoration: none;
  margin-bottom: 8px; transition: all .2s; box-shadow: 0 3px 10px rgba(37,211,102,.25);
}
.btn-pd-wa:hover { background: #1db954; color: #fff; transform: translateY(-1px); }
.btn-pd-call {
  width: 100%; display: flex; align-items: center; justify-content: center; gap: 7px;
  background: var(--bg); color: var(--txt); font-weight: 700; font-size: .88rem;
  padding: 11px; border-radius: 11px; text-decoration: none;
  border: 1.5px solid var(--border); transition: all .15s;
}
.btn-pd-call:hover { border-color: var(--gr); color: var(--gr); background: var(--gr-lt); }

/* Alert messages */
.pd-msg { font-size: .82rem; font-weight: 600; padding: 10px 13px; border-radius: 10px; margin-bottom: 12px; }
.pd-msg-success { background: var(--gr-lt); color: var(--gr); border: 1px solid var(--gr-b); }
.pd-msg-error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.pd-msg-warning { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }

/* Mobile sticky CTA */
.pd-mob-cta {
  display: none; position: fixed; bottom: 64px; left: 0; right: 0;
  background: #fff; border-top: 1.5px solid var(--border);
  padding: 10px 14px; z-index: 998;
  grid-template-columns: 1fr 1fr; gap: 8px;
  box-shadow: 0 -4px 16px rgba(26,35,50,.1);
}
@media(max-width:991px) { .pd-mob-cta { display: grid !important; } .pd-page { padding-bottom: 120px; } }
.btn-mob-call {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  background: var(--bg); color: var(--txt); font-weight: 800; font-size: .83rem;
  padding: 12px; border-radius: 10px; text-decoration: none;
  border: 1.5px solid var(--border);
}
.btn-mob-enq {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  background: linear-gradient(135deg, var(--gr), #1b5e20); color: #fff;
  font-weight: 800; font-size: .83rem;
  padding: 12px; border-radius: 10px; border: none; cursor: pointer;
}
</style>

<div class="pd-page">
<div class="pd-inner">

  <!-- ── LEFT COLUMN ── -->
  <div>
    <!-- Gallery -->
    <div class="pd-card">
      <?php if (!empty($imgs)): ?>
      <div id="plotCarousel" class="carousel slide pd-gallery" data-bs-ride="carousel">
        <div class="carousel-inner">
          <?php foreach($imgs as $i => $img): ?>
          <div class="carousel-item <?= $i===0 ? 'active' : '' ?>">
            <img src="/uploads/plots/<?= htmlspecialchars($img) ?>" alt="Plot Image <?= $i+1 ?>">
          </div>
          <?php endforeach; ?>
        </div>
        <?php if(count($imgs) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#plotCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#plotCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon"></span>
        </button>
        <?php endif; ?>
        <?php
        $sc = match($plot['status']) { 'Available'=>'chip-avail', 'Sold'=>'chip-sold', default=>'chip-reserved' };
        $sl = match($plot['status']) { 'Available'=>'✓ Available', 'Sold'=>'✗ Sold', 'Reserved'=>'⏳ Reserved', default=>$plot['status'] };
        ?>
        <span class="pd-status-chip <?= $sc ?>"><?= $sl ?></span>
      </div>
      <?php else: ?>
      <div class="pd-no-img" style="position:relative;">
        <i class="bi bi-geo-alt-fill"></i>
        <span>Plot Image Coming Soon</span>
        <span class="pd-status-chip chip-avail" style="position:absolute;top:14px;right:14px;">✓ <?= htmlspecialchars($plot['status']) ?></span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Info -->
    <div class="pd-card">
      <div class="pd-card-body">
        <div class="pd-badges">
          <span class="pd-badge-type"><?= htmlspecialchars($plot['type']) ?></span>
          <span class="pd-badge-facing"><?= htmlspecialchars($plot['facing']) ?> Facing</span>
        </div>
        <h1 class="pd-title"><?= htmlspecialchars($plot['title']) ?></h1>
        <div class="pd-location">
          <i class="bi bi-geo-alt-fill"></i>
          <?= htmlspecialchars($plot['location']) ?>, <?= htmlspecialchars($plot['city']) ?>
        </div>

        <!-- Price -->
        <div class="pd-price-block">
          <div class="pd-price-lbl">Total Price</div>
          <div class="pd-price-big">₹<?= number_format($plot['price']) ?></div>
          <div class="pd-price-psf">
            <strong>₹<?= number_format($pricePerSqft) ?>/sqft</strong> &nbsp;·&nbsp;
            <?= number_format($plot['size_sqft']) ?> sqft &nbsp;·&nbsp;
            <?= number_format($plot['size_sqyard'], 1) ?> sqyd
          </div>
        </div>

        <!-- Specs -->
        <div class="pd-card-title"><i class="bi bi-grid-3x3-gap-fill"></i> Plot Specifications</div>
        <div class="pd-specs" style="margin-bottom:0;">
          <?php foreach([
            ['Area (sq.ft)',  number_format($plot['size_sqft']).' sqft'],
            ['Area (sq.yd)',  number_format($plot['size_sqyard'],1).' sqyd'],
            ['Rate/sqft',    '₹'.number_format($pricePerSqft)],
            ['Facing',        $plot['facing'].' Facing'],
            ['Type',          $plot['type']],
            ['City',          $plot['city']],
          ] as [$k,$v]): ?>
          <div class="pd-spec">
            <div class="pd-spec-lbl"><?= $k ?></div>
            <div class="pd-spec-val"><?= htmlspecialchars($v) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Description -->
    <?php if(!empty($plot['description'])): ?>
    <div class="pd-card">
      <div class="pd-card-body">
        <div class="pd-card-title"><i class="bi bi-info-circle-fill"></i> About This Plot</div>
        <p style="font-size:.88rem;color:var(--mid);line-height:1.85;margin:0;">
          <?= nl2br(htmlspecialchars($plot['description'])) ?>
        </p>
      </div>
    </div>
    <?php endif; ?>

    <!-- Features -->
    <?php if(!empty($featArr)): ?>
    <div class="pd-card">
      <div class="pd-card-body">
        <div class="pd-card-title"><i class="bi bi-check-circle-fill"></i> Features & Amenities</div>
        <div class="pd-feat-grid">
          <?php foreach($featArr as $f): ?>
            <div class="pd-feat-item"><?= htmlspecialchars(trim($f)) ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Map -->
    <?php if(!empty($plot['map_embed'])): ?>
    <div class="pd-card">
      <div class="pd-card-body">
        <div class="pd-card-title"><i class="bi bi-map-fill"></i> Location on Map</div>
        <div class="ratio ratio-16x9" style="border-radius:12px;overflow:hidden;">
          <?= $plot['map_embed'] ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Similar Plots — full card style matching plots.php -->
    <?php if(!empty($similar)): ?>
    <div class="pd-card">
      <div class="pd-card-body">
        <div class="pd-card-title"><i class="bi bi-map-fill"></i> More Plots in <?= htmlspecialchars($plot['city']) ?></div>
        <div style="display:grid;grid-template-columns:repeat(1,1fr);gap:14px;">
          <?php foreach($similar as $s):
            $sI = is_string($s['images']) ? json_decode($s['images'], true) : [];
            if(!is_array($sI)) $sI = [];
            $sSc = match($s['status']) { 'Available'=>'chip-avail','Sold'=>'chip-sold',default=>'chip-reserved' };
            $sSl = match($s['status']) { 'Available'=>'✓ Available','Sold'=>'✗ Sold',default=>'⏳ '.$s['status'] };
            $sPsf = $s['size_sqft'] > 0 ? round($s['price'] / $s['size_sqft']) : 0;
          ?>
          <div class="plot-c" style="border-radius:14px;">
            <div class="plot-img-area" style="height:120px;">
              <?php if(!empty($sI)): ?>
                <img src="/uploads/plots/<?= htmlspecialchars($sI[0]) ?>" alt="<?= htmlspecialchars($s['title']) ?>">
              <?php else: ?>
                <div class="plot-no-img"><i class="bi bi-geo-alt-fill"></i><span>Plot Image</span></div>
              <?php endif; ?>
              <span class="plot-status-chip <?= $sSc ?>"><?= $sSl ?></span>
            </div>
            <div class="plot-body" style="padding:12px;">
              <div class="plot-badges">
                <span class="plot-type-chip"><?= htmlspecialchars($s['type']) ?></span>
                <span class="plot-facing-chip"><?= htmlspecialchars($s['facing']) ?> Facing</span>
              </div>
              <div class="plot-title" style="font-size:.88rem;"><?= htmlspecialchars($s['title']) ?></div>
              <div class="plot-loc"><i class="bi bi-geo-alt-fill"></i><?= htmlspecialchars($s['location']) ?>, <?= htmlspecialchars($s['city']) ?></div>
              <div class="plot-price" style="font-size:1.1rem;">₹<?= number_format($s['price']) ?></div>
              <div class="plot-psf">₹<?= number_format($sPsf) ?>/sq.ft · <?= number_format($s['size_sqft']) ?> sqft</div>
              <div class="plot-btns">
                <a href="/plot-detail.php?id=<?= $s['id'] ?>" class="btn-plot-view"><i class="bi bi-eye"></i> View Details</a>
                <button class="btn-plot-enq"
                        onclick="openEnquiryPopup('Plot: <?= addslashes(htmlspecialchars($s['title'])) ?>','plot','Our property expert will call you!')">
                  <i class="bi bi-telephone-fill"></i> Enquire Now
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

  <!-- ── RIGHT: SIDEBAR ── -->
  <div>
    <div class="pd-sidebar">
      <div class="pd-sidebar-title">Interested in This Plot?</div>
      <div class="pd-sidebar-sub">Fill the form — we'll call you within 2 hours</div>

      <!-- Quick summary -->
      <div class="pd-qsum">
        <div class="pd-qsum-lbl">Property Summary</div>
        <div class="pd-qsum-row"><span class="pd-qsum-key">📐 Area</span><span class="pd-qsum-val"><?= number_format($plot['size_sqft']) ?> sqft</span></div>
        <div class="pd-qsum-row"><span class="pd-qsum-key">💰 Price</span><span class="pd-qsum-val" style="color:var(--gr);">₹<?= number_format($plot['price']) ?></span></div>
        <div class="pd-qsum-row"><span class="pd-qsum-key">📍 Location</span><span class="pd-qsum-val"><?= htmlspecialchars($plot['city']) ?></span></div>
        <div class="pd-qsum-row"><span class="pd-qsum-key">🧭 Facing</span><span class="pd-qsum-val"><?= htmlspecialchars($plot['facing']) ?></span></div>
      </div>

      <!-- Enquiry form message -->
      <?php if($enquiryMsg): [$type,$text] = explode(':', $enquiryMsg, 2); ?>
        <div class="pd-msg pd-msg-<?= $type ?>"><i class="bi bi-<?= $type==='success'?'check-circle-fill':'exclamation-circle' ?> me-1"></i><?= htmlspecialchars($text) ?></div>
      <?php endif; ?>

      <!-- Enquiry form -->
      <form method="POST">
        <input type="hidden" name="enquire" value="1">
        <input type="hidden" name="csrf" value="<?= generateCSRF() ?>">
        <div>
          <label class="pd-form-lbl">Full Name *</label>
          <input type="text" name="name" class="pd-form-inp" required placeholder="Your name"
                 value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <div>
          <label class="pd-form-lbl">Phone Number *</label>
          <input type="tel" name="phone" class="pd-form-inp" required placeholder="+91 XXXXX XXXXX"
                 value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
        <div>
          <label class="pd-form-lbl">Message</label>
          <textarea name="message" class="pd-form-ta" placeholder="Any questions about this plot..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn-pd-submit">
          <i class="bi bi-telephone-fill"></i> Send Enquiry
        </button>
      </form>

      <a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '' ?>?text=Hi%2C+I%27m+interested+in+the+plot%3A+<?= urlencode($plot['title']) ?>"
         target="_blank" class="btn-pd-wa">
        <i class="bi bi-whatsapp"></i> WhatsApp Now
      </a>
      <a href="tel:<?= defined('SITE_PHONE') ? SITE_PHONE : '' ?>" class="btn-pd-call">
        <i class="bi bi-telephone-fill"></i> <?= defined('SITE_PHONE') ? SITE_PHONE : '+91 9821130198' ?>
      </a>
    </div>
  </div>

</div>
</div>

<!-- Mobile CTA -->
<div class="pd-mob-cta">
  <a href="tel:<?= defined('SITE_PHONE') ? SITE_PHONE : '' ?>" class="btn-mob-call">
    <i class="bi bi-telephone-fill" style="color:var(--gr);"></i> Call Now
  </a>
  <a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '' ?>?text=Hi%2C+interested+in+<?= urlencode($plot['title']) ?>"
     target="_blank" class="btn-mob-enq">
    <i class="bi bi-whatsapp"></i> WhatsApp
  </a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
