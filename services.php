<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Our Services — " . SITE_NAME;
$cat = isset($_GET['cat']) ? clean($_GET['cat']) : '';

$where  = ["s.status = 1"];
$params = [];
if ($cat) {
    $where[] = "(sc.slug = ? OR LOWER(sc.name) = ?)";
    $params[] = $cat;
    $params[] = str_replace('-', ' ', $cat);
}
$whereStr = implode(' AND ', $where);

try {
    $stmt = $pdo->prepare("
        SELECT s.*, sc.name AS cat_name, sc.slug AS cat_slug,
               GROUP_CONCAT(st.tag_name SEPARATOR ',') AS tags
        FROM services s
        LEFT JOIN service_categories sc ON s.category_id = sc.id
        LEFT JOIN service_tags st ON st.service_id = s.id
        WHERE $whereStr
        GROUP BY s.id
        ORDER BY s.is_popular DESC, s.rating DESC
    ");
    $stmt->execute($params);
    $services = $stmt->fetchAll();
} catch (Exception $e) {
    $services = [];
}

// Categories for dropdown
$cats = [];
try {
    $cats = $pdo->query("SELECT DISTINCT sc.name, sc.slug FROM services s LEFT JOIN service_categories sc ON s.category_id = sc.id WHERE s.status=1 AND sc.name IS NOT NULL ORDER BY sc.name")->fetchAll();
} catch (Exception $e) {}

if (empty($services)) {
    $allDemo = [
        ['id'=>1,'name'=>'RCC Structure Work','cat_name'=>'Civil','short_desc'=>'Complete reinforced concrete structure — foundation, columns, beams & slabs with certified engineers.','price_from'=>45,'price_unit'=>'sqft','rating'=>4.9,'is_popular'=>1,'icon'=>'🏗️','tags'=>'Foundation,Column,Beam'],
        ['id'=>2,'name'=>'Full Home Painting','cat_name'=>'Painting','short_desc'=>'Interior & exterior painting with premium Asian Paints. Putty, primer, 2 coats finish — all included.','price_from'=>12,'price_unit'=>'sqft','rating'=>4.7,'is_popular'=>1,'icon'=>'🎨','tags'=>'Asian Paints,Putty,Primer'],
        ['id'=>3,'name'=>'False Ceiling + Cove Lighting','cat_name'=>'Interior','short_desc'=>'Gypsum / POP false ceiling with hidden LED cove lighting. Complete design, material & installation.','price_from'=>65,'price_unit'=>'sqft','rating'=>4.8,'is_popular'=>1,'icon'=>'🛋️','tags'=>'Gypsum,POP,LED Cove'],
        ['id'=>4,'name'=>'Modular Kitchen','cat_name'=>'Carpentry','short_desc'=>'Custom modular kitchen with premium hardware — upper & lower cabinets, shutters, countertop & chimney.','price_from'=>1200,'price_unit'=>'rft','rating'=>4.8,'is_popular'=>0,'icon'=>'🪵','tags'=>'Cabinets,Countertop,Hardware'],
        ['id'=>5,'name'=>'Complete Electrical Work','cat_name'=>'Electrical','short_desc'=>'Full wiring, switchboards, MCB panel installation. Concealed & open wiring with certified electricians.','price_from'=>18,'price_unit'=>'sqft','rating'=>4.7,'is_popular'=>0,'icon'=>'⚡','tags'=>'Wiring,MCB Panel,Concealed'],
        ['id'=>6,'name'=>'Plumbing Work','cat_name'=>'Plumbing','short_desc'=>'Complete plumbing — water supply, drainage, bathroom fitting installation by certified plumbers.','price_from'=>22,'price_unit'=>'sqft','rating'=>4.6,'is_popular'=>0,'icon'=>'🔧','tags'=>'Water Supply,Drainage,Fitting'],
        ['id'=>7,'name'=>'AC Installation & Service','cat_name'=>'AC & HVAC','short_desc'=>'Split AC installation, gas refilling, servicing. Same-day service available across Delhi NCR.','price_from'=>799,'price_unit'=>'unit','rating'=>4.8,'is_popular'=>0,'icon'=>'❄️','tags'=>'Split AC,Installation,Service'],
        ['id'=>8,'name'=>'Waterproofing','cat_name'=>'Civil','short_desc'=>'Terrace, bathroom & basement waterproofing with 5-year guarantee using Sika / Dr. Fixit materials.','price_from'=>35,'price_unit'=>'sqft','rating'=>4.6,'is_popular'=>0,'icon'=>'💧','tags'=>'Terrace,Bathroom,Guarantee'],
        ['id'=>9,'name'=>'Flooring Work','cat_name'=>'Flooring','short_desc'=>'Vitrified tiles, marble, granite, wooden flooring. Supply + installation with skilled professionals.','price_from'=>28,'price_unit'=>'sqft','rating'=>4.7,'is_popular'=>0,'icon'=>'🏠','tags'=>'Tiles,Marble,Granite'],
        ['id'=>10,'name'=>'Steel Fabrication','cat_name'=>'Steel','short_desc'=>'MS steel gates, grills, staircase railing, warehouse structure fabrication and installation.','price_from'=>150,'price_unit'=>'kg','rating'=>4.5,'is_popular'=>0,'icon'=>'⚙️','tags'=>'Gates,Grills,Railing'],
        ['id'=>11,'name'=>'Garden & Landscaping','cat_name'=>'Outdoor','short_desc'=>'Complete garden design, lawn installation, irrigation system, outdoor lighting and maintenance.','price_from'=>500,'price_unit'=>'sqft','rating'=>4.6,'is_popular'=>0,'icon'=>'🌿','tags'=>'Garden,Lawn,Irrigation'],
        ['id'=>12,'name'=>'Home Renovation','cat_name'=>'Renovation','short_desc'=>'Complete home renovation — civil, electrical, plumbing, painting all in one project.','price_from'=>350,'price_unit'=>'sqft','rating'=>4.9,'is_popular'=>1,'icon'=>'🔨','tags'=>'Civil,Electrical,Painting'],
    ];
    $services = $cat ? array_values(array_filter($allDemo, fn($s) => strtolower($s['cat_name']) === str_replace('-',' ',strtolower($cat)))) : $allDemo;
    $cats = array_unique(array_column($allDemo, 'cat_name'));
    $cats = array_map(fn($c) => ['name' => $c, 'slug' => strtolower(str_replace(' ', '-', $c))], $cats);
}

$total = count($services);
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
:root {
  --or:#e8560a; --or-lt:#fff5f0; --or-b:rgba(232,86,10,.18);
  --bl:#1565c0; --bl-lt:#f0f5ff;
  --gr:#2e7d32; --gr-lt:#f0faf0;
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb; --white:#ffffff;
}

/* ══ FILTER BAR ══ */
.svc-filter-bar {
  background: var(--white);
  border-bottom: 1.5px solid var(--border);
  padding: 11px 5%;
  position: sticky; top: 66px; z-index: 200;
  box-shadow: 0 2px 10px rgba(26,35,50,.06);
}
@media(max-width:991px) { .svc-filter-bar { top: 60px; } }
.svc-filter-inner {
  max-width: 1400px; margin: 0 auto;
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.svc-fb-badge {
  display: flex; align-items: center; gap: 8px; flex-shrink: 0;
}
.svc-fb-icon {
  width: 34px; height: 34px; border-radius: 9px;
  background: rgba(232,86,10,.10); border: 1.5px solid rgba(232,86,10,.22);
  display: flex; align-items: center; justify-content: center;
  color: var(--or); font-size: .9rem; flex-shrink: 0;
}
.svc-fb-title  { font-size: .82rem; font-weight: 800; color: var(--txt); }
.svc-fb-count  { font-size: .7rem; color: var(--hint); font-weight: 600; }
.svc-fb-divider { width:1px; height:28px; background:var(--border); flex-shrink:0; display:none; }
@media(min-width:400px) { .svc-fb-divider { display:block; } }

.svc-sel-wrap {
  position: relative; flex: 1; min-width: 160px; max-width: 300px;
}
.svc-sel-wrap .svc-chev {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  pointer-events: none; color: var(--hint); font-size: .7rem;
  transition: transform .18s;
}
.svc-select {
  width: 100%; appearance: none; -webkit-appearance: none;
  border: 1.5px solid var(--border); border-radius: 10px;
  padding: 9px 34px 9px 13px; font-size: .84rem;
  font-family: 'DM Sans', sans-serif; font-weight: 600;
  color: var(--txt); background: var(--bg);
  outline: none; cursor: pointer;
  transition: border-color .18s, box-shadow .18s, background .18s;
}
.svc-select:focus {
  border-color: var(--or); box-shadow: 0 0 0 3px rgba(232,86,10,.10); background: #fff;
}
.svc-select.active {
  border-color: var(--or); background: var(--or-lt); color: var(--or); font-weight: 700;
}
.svc-fb-clear {
  display: flex; align-items: center; gap: 5px;
  font-size: .75rem; color: var(--or); font-weight: 700;
  background: var(--or-lt); border: 1.5px solid var(--or-b);
  padding: 6px 13px; border-radius: 20px; text-decoration: none;
  white-space: nowrap; flex-shrink: 0; transition: opacity .15s;
}
.svc-fb-clear:hover { opacity: .82; color: var(--or); }

/* ══ GRID WRAP ══ */
.svc-grid-wrap {
  background: var(--bg);
  padding: 28px 5% 60px;
}
.svc-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
  max-width: 1400px; margin: 0 auto;
}
@media(min-width:576px)  { .svc-grid { grid-template-columns: repeat(3,1fr); gap:16px; } }
@media(min-width:992px)  { .svc-grid { grid-template-columns: repeat(4,1fr); gap:18px; } }
@media(min-width:1400px) { .svc-grid { grid-template-columns: repeat(5,1fr); } }

/* ══ CARD ══ */
.svc-card {
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 16px;
  padding: 18px 16px 14px;
  display: flex; flex-direction: column;
  gap: 7px; position: relative;
  transition: all .22s;
  box-shadow: 0 2px 10px rgba(26,35,50,.05);
}
.svc-card:hover {
  border-color: rgba(232,86,10,.25);
  box-shadow: 0 10px 30px rgba(26,35,50,.12);
  transform: translateY(-3px);
}
.svc-pop-badge {
  position: absolute; top: 10px; right: 10px;
  background: linear-gradient(135deg, #fff5f0, #ffe4d4);
  color: var(--or); font-size: .58rem; font-weight: 800;
  padding: 3px 9px; border-radius: 20px;
  border: 1px solid rgba(232,86,10,.2);
}
.svc-icon-wrap {
  width: 52px; height: 52px; border-radius: 14px;
  background: linear-gradient(135deg, var(--or-lt), #fff8f5);
  border: 1.5px solid rgba(232,86,10,.12);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.7rem; flex-shrink: 0;
}
.svc-cat-lbl {
  font-size: .62rem; font-weight: 800; color: var(--or);
  text-transform: uppercase; letter-spacing: .9px;
}
.svc-card-name {
  font-size: .9rem; font-weight: 700; color: var(--txt); line-height: 1.3;
}
@media(max-width:575px) { .svc-card-name { font-size: .82rem; } }
.svc-card-desc {
  font-size: .76rem; color: var(--light); line-height: 1.5; display: none;
}
@media(min-width:768px) { .svc-card-desc { display: block; } }
.svc-tags-row { display: flex; flex-wrap: wrap; gap: 4px; }
.svc-tag {
  font-size: .6rem; background: var(--bg);
  border: 1px solid var(--border);
  color: var(--mid); padding: 2px 7px; border-radius: 9px; font-weight: 600;
}
.svc-meta {
  display: flex; align-items: center; justify-content: space-between; margin-top: 2px;
}
.svc-rating { font-size: .75rem; color: #f59e0b; font-weight: 600; }
.svc-price  { font-size: .8rem; font-weight: 800; color: var(--txt); }
.svc-card-btn {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  width: 100%; background: linear-gradient(135deg, #f0a070, var(--or));
  color: #fff; font-weight: 700; font-size: .78rem;
  padding: 10px; border-radius: 10px; border: none;
  cursor: pointer; text-decoration: none; margin-top: auto;
  transition: all .2s; box-shadow: 0 3px 10px rgba(232,86,10,.22);
}
.svc-card-btn:hover { color:#fff; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(232,86,10,.34); }

/* ══ CTA STRIP ══ */
.svc-cta {
  max-width: 1400px; margin: 32px auto 0;
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 16px; padding: 24px 28px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 16px;
  box-shadow: 0 2px 10px rgba(26,35,50,.05);
}
@media(max-width:575px) { .svc-cta { padding: 18px 16px; } }
.svc-cta-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem; font-weight: 800; color: var(--txt); margin-bottom: 4px;
}
.svc-cta-sub { font-size: .84rem; color: var(--light); }
.svc-cta-btns { display: flex; gap: 10px; flex-wrap: wrap; }
.btn-svc-quote {
  display: flex; align-items: center; gap: 7px;
  background: linear-gradient(135deg, #f0a070, var(--or));
  color: #fff; font-weight: 700; font-size: .85rem;
  padding: 11px 20px; border-radius: 10px; border: none;
  cursor: pointer; box-shadow: 0 3px 12px rgba(232,86,10,.28);
  transition: all .2s;
}
.btn-svc-quote:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(232,86,10,.38); }
.btn-svc-wa {
  display: flex; align-items: center; gap: 7px;
  background: #25d366; color: #fff; font-weight: 700; font-size: .85rem;
  padding: 11px 20px; border-radius: 10px; text-decoration: none; transition: all .2s;
}
.btn-svc-wa:hover { background: #1db954; color: #fff; transform: translateY(-1px); }

/* Empty state */
.svc-empty { text-align: center; padding: 80px 20px; max-width: 1400px; margin: 0 auto; }
</style>

<!-- ══ FILTER BAR ══ -->
<div class="svc-filter-bar">
  <div class="svc-filter-inner">

    <div class="svc-fb-badge">
      <div class="svc-fb-icon"><i class="bi bi-tools"></i></div>
      <div>
        <div class="svc-fb-title">Our Services</div>
        <div class="svc-fb-count"><?= $total ?> service<?= $total !== 1 ? 's' : '' ?></div>
      </div>
    </div>

    <div class="svc-fb-divider"></div>

    <div class="svc-sel-wrap">
      <select class="svc-select <?= $cat ? 'active' : '' ?>"
              onchange="window.location.href='/services.php'+(this.value?'?cat='+encodeURIComponent(this.value):'')">
        <option value="">All Services</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= htmlspecialchars($c['slug']) ?>"
            <?= $cat === $c['slug'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <i class="bi bi-chevron-down svc-chev"></i>
    </div>

    <?php if ($cat): ?>
      <a href="/services.php" class="svc-fb-clear"><i class="bi bi-x-lg"></i> Clear</a>
    <?php endif; ?>

  </div>
</div>

<!-- ══ GRID ══ -->
<div class="svc-grid-wrap">
  <?php if (empty($services)): ?>
    <div class="svc-empty">
      <div style="font-size:3.5rem;margin-bottom:16px;">🔍</div>
      <h5 style="color:var(--mid);margin-bottom:8px;">No services found in this category</h5>
      <a href="/services.php" style="color:var(--or);font-weight:600;text-decoration:none;">← View all services</a>
    </div>
  <?php else: ?>
  <div class="svc-grid">
    <?php foreach ($services as $svc): ?>
    <div class="svc-card">
      <?php if (!empty($svc['is_popular'])): ?>
        <span class="svc-pop-badge">⭐ Popular</span>
      <?php endif; ?>
      <div class="svc-icon-wrap"><?= htmlspecialchars($svc['icon'] ?? '🔧') ?></div>
      <div class="svc-cat-lbl"><?= htmlspecialchars($svc['cat_name'] ?? '') ?></div>
      <div class="svc-card-name"><?= htmlspecialchars($svc['name']) ?></div>
      <p class="svc-card-desc"><?= htmlspecialchars($svc['short_desc'] ?? '') ?></p>
      <?php if (!empty($svc['tags'])): ?>
      <div class="svc-tags-row">
        <?php foreach (array_slice(explode(',', $svc['tags']), 0, 3) as $tag): ?>
          <span class="svc-tag"><?= htmlspecialchars(trim($tag)) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="svc-meta">
        <div class="svc-rating">★ <?= number_format($svc['rating'] ?? 4.5, 1) ?></div>
        <div class="svc-price">₹<?= number_format($svc['price_from'] ?? 0) ?>/<?= htmlspecialchars($svc['price_unit'] ?? 'unit') ?></div>
      </div>
      <a href="/service-detail.php?id=<?= (int)$svc['id'] ?>" class="svc-card-btn">
        <i class="bi bi-clipboard-check"></i> View Details
      </a>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- CTA Strip -->
  <div class="svc-cta">
    <div>
      <div class="svc-cta-title">Can't find what you need?</div>
      <div class="svc-cta-sub">Tell us your requirement — we'll find the right service for you.</div>
    </div>
    <div class="svc-cta-btns">
      <button class="btn-svc-quote"
              onclick="openEnquiryPopup('Custom Service Request','services','Our expert will call you back within 2 hours!')">
        <i class="bi bi-calendar-check-fill"></i> Get Free Quote
      </button>
      <a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '' ?>?text=Hi%2C+I+need+a+service+quote"
         target="_blank" class="btn-svc-wa">
        <i class="bi bi-whatsapp"></i> WhatsApp Us
      </a>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>