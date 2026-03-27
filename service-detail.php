<?php
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { redirect('/services.php'); }

$service = null; $tags = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT s.*, sc.name AS cat_name FROM services s LEFT JOIN service_categories sc ON s.category_id=sc.id WHERE s.id=? AND s.status=1");
        $stmt->execute([$id]);
        $service = $stmt->fetch();
        if ($service) {
            $tStmt = $pdo->prepare("SELECT tag_name FROM service_tags WHERE service_id=?");
            $tStmt->execute([$id]);
            $tags = array_column($tStmt->fetchAll(), 'tag_name');
        }
    } catch (Exception $e) {}
}

if (!$service) {
    $service = ['id'=>$id,'name'=>'Professional Service','cat_name'=>'Construction','short_desc'=>'Expert service by certified professionals.','description'=>'<p>Our team of certified professionals delivers high-quality workmanship. Contact us for a free site visit and quotation.</p>','price_from'=>0,'price_unit'=>'sqft','icon'=>'🔧','rating'=>4.8,'is_popular'=>1,'image'=>''];
    $tags = ['Quality Work','Free Estimate','Certified Pros'];
}

// Related services
$relServices = [];
$_allDemoServices = [
    ['id'=>1,'name'=>'RCC Structure Work','cat_name'=>'Civil','short_desc'=>'Complete RCC structure — foundation, columns, beams & slabs.','price_from'=>45,'price_unit'=>'sqft','rating'=>4.9,'is_popular'=>1,'icon'=>'🏗️'],
    ['id'=>2,'name'=>'Full Home Painting','cat_name'=>'Painting','short_desc'=>'Interior & exterior painting with premium Asian Paints.','price_from'=>12,'price_unit'=>'sqft','rating'=>4.7,'is_popular'=>1,'icon'=>'🎨'],
    ['id'=>3,'name'=>'False Ceiling + Cove Lighting','cat_name'=>'Interior','short_desc'=>'Gypsum / POP false ceiling with hidden LED cove lighting.','price_from'=>65,'price_unit'=>'sqft','rating'=>4.8,'is_popular'=>1,'icon'=>'🛋️'],
    ['id'=>4,'name'=>'Modular Kitchen','cat_name'=>'Carpentry','short_desc'=>'Custom modular kitchen with premium hardware.','price_from'=>1200,'price_unit'=>'rft','rating'=>4.8,'is_popular'=>0,'icon'=>'🪵'],
    ['id'=>5,'name'=>'Complete Electrical Work','cat_name'=>'Electrical','short_desc'=>'Full wiring, switchboards, MCB panel installation.','price_from'=>18,'price_unit'=>'sqft','rating'=>4.7,'is_popular'=>0,'icon'=>'⚡'],
    ['id'=>6,'name'=>'Plumbing Work','cat_name'=>'Plumbing','short_desc'=>'Complete plumbing — supply, drainage, bathroom fittings.','price_from'=>22,'price_unit'=>'sqft','rating'=>4.6,'is_popular'=>0,'icon'=>'🔧'],
    ['id'=>7,'name'=>'AC Installation & Service','cat_name'=>'AC & HVAC','short_desc'=>'Split AC installation, gas refilling, same-day service.','price_from'=>799,'price_unit'=>'unit','rating'=>4.8,'is_popular'=>0,'icon'=>'❄️'],
    ['id'=>8,'name'=>'Waterproofing','cat_name'=>'Civil','short_desc'=>'Terrace, bathroom & basement waterproofing — 5-year guarantee.','price_from'=>35,'price_unit'=>'sqft','rating'=>4.6,'is_popular'=>0,'icon'=>'💧'],
    ['id'=>9,'name'=>'Flooring Work','cat_name'=>'Flooring','short_desc'=>'Vitrified tiles, marble, granite, wooden flooring.','price_from'=>28,'price_unit'=>'sqft','rating'=>4.7,'is_popular'=>0,'icon'=>'🏠'],
    ['id'=>10,'name'=>'Steel Fabrication','cat_name'=>'Steel','short_desc'=>'MS steel gates, grills, staircase railing, fabrication.','price_from'=>150,'price_unit'=>'kg','rating'=>4.5,'is_popular'=>0,'icon'=>'⚙️'],
    ['id'=>11,'name'=>'Garden & Landscaping','cat_name'=>'Outdoor','short_desc'=>'Complete garden design, lawn, irrigation, outdoor lighting.','price_from'=>500,'price_unit'=>'sqft','rating'=>4.6,'is_popular'=>0,'icon'=>'🌿'],
    ['id'=>12,'name'=>'Home Renovation','cat_name'=>'Renovation','short_desc'=>'Complete renovation — civil, electrical, plumbing, painting.','price_from'=>350,'price_unit'=>'sqft','rating'=>4.9,'is_popular'=>1,'icon'=>'🔨'],
];
try {
    if ($pdo) {
        $rs = $pdo->prepare("SELECT s.*, sc.name AS cat_name FROM services s LEFT JOIN service_categories sc ON s.category_id=sc.id WHERE s.id != ? AND s.status=1 ORDER BY s.is_popular DESC, s.rating DESC LIMIT 4");
        $rs->execute([$id]);
        $relServices = $rs->fetchAll();
    }
} catch(Exception $e) {}
if (empty($relServices)) {
    $relServices = array_values(array_filter($_allDemoServices, fn($s) => $s['id'] !== $id));
    $relServices = array_slice($relServices, 0, 4);
}

$pageTitle = htmlspecialchars($service['name']) . ' — Incredible Heights';
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

/* ── PAGE HEADER BAR ── */
.sd-hdr {
  background: var(--white);
  border-bottom: 1.5px solid var(--border);
  padding: 14px 5%;
  box-shadow: 0 2px 10px rgba(26,35,50,.05);
}
.sd-hdr-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.sd-hdr-icon {
  width: 36px; height: 36px; border-radius: 10px;
  background: var(--or-lt); border: 1.5px solid var(--or-b);
  display: flex; align-items: center; justify-content: center;
  color: var(--or); font-size: .9rem; flex-shrink: 0;
}
.sd-hdr-cat {
  font-size: .68rem; font-weight: 800; color: var(--or);
  text-transform: uppercase; letter-spacing: 1px;
  background: var(--or-lt); border: 1px solid var(--or-b);
  padding: 3px 10px; border-radius: 18px;
}
.sd-hdr-name {
  font-size: .9rem; font-weight: 800; color: var(--txt);
}
.sd-hdr-sep { color: var(--border); }
.sd-hdr-crumb {
  font-size: .76rem; color: var(--hint); margin-left: auto;
}
.sd-hdr-crumb a { color: var(--hint); text-decoration: none; }
.sd-hdr-crumb a:hover { color: var(--or); }

/* ── PAGE AREA ── */
.sd-page { background: var(--bg); padding: 24px 5% 60px; }
.sd-page-inner {
  max-width: 1200px; margin: 0 auto;
  display: grid; grid-template-columns: 1fr;
  gap: 22px; align-items: start;
}
@media(min-width:992px) { .sd-page-inner { grid-template-columns: 1fr 340px; } }

/* ── LEFT COLUMN ── */

/* Hero image */
.sd-hero {
  background: linear-gradient(135deg, var(--or-lt), #fff8f5);
  border-radius: 18px; height: 280px;
  display: flex; align-items: center; justify-content: center;
  position: relative; overflow: hidden;
  border: 1.5px solid var(--or-b);
  margin-bottom: 18px;
  font-size: 6rem;
}
@media(min-width:768px) { .sd-hero { height: 320px; } }
.sd-hero img {
  width: 100%; height: 100%; object-fit: cover;
  position: absolute; inset: 0; border-radius: 16px;
}
.sd-popular-badge {
  position: absolute; top: 16px; right: 16px;
  background: linear-gradient(135deg, #fff5f0, #ffe4d4);
  color: var(--or); font-size: .75rem; font-weight: 800;
  padding: 6px 14px; border-radius: 20px;
  border: 1.5px solid var(--or-b);
  box-shadow: 0 2px 8px rgba(232,86,10,.15);
}

/* Description card */
.sd-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 18px; padding: 24px 22px;
  box-shadow: 0 2px 12px rgba(26,35,50,.05);
  margin-bottom: 18px;
}
.sd-card:last-child { margin-bottom: 0; }
.sd-card-title {
  font-size: .9rem; font-weight: 800; color: var(--txt);
  margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
}
.sd-card-title i { color: var(--or); }

/* Tags */
.sd-tags { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 16px; }
.sd-tag {
  background: var(--bg); border: 1.5px solid var(--border);
  color: var(--mid); font-size: .76rem; font-weight: 600;
  padding: 4px 12px; border-radius: 20px; transition: all .15s;
}
.sd-tag:hover { border-color: var(--or-b); background: var(--or-lt); color: var(--or); }

/* Description text */
.sd-desc {
  font-size: .88rem; color: var(--mid);
  line-height: 1.85; letter-spacing: .1px;
}
.sd-desc p { margin-bottom: 10px; }
.sd-desc p:last-child { margin-bottom: 0; }

/* Why choose us grid */
.sd-reasons {
  display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
}
@media(max-width:575px) { .sd-reasons { grid-template-columns: 1fr; } }
.sd-reason {
  display: flex; align-items: flex-start; gap: 12px;
  background: var(--bg); border: 1.5px solid var(--border);
  border-radius: 12px; padding: 14px;
  transition: all .18s;
}
.sd-reason:hover {
  border-color: rgba(232,86,10,.2); background: var(--or-lt);
}
.sd-reason-ico {
  font-size: 1.5rem; flex-shrink: 0; line-height: 1;
}
.sd-reason-title { font-size: .82rem; font-weight: 800; color: var(--txt); margin-bottom: 3px; }
.sd-reason-desc  { font-size: .74rem; color: var(--light); line-height: 1.5; }

/* ── RIGHT: STICKY SIDEBAR ── */
.sd-sidebar {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 18px; padding: 22px;
  box-shadow: 0 2px 12px rgba(26,35,50,.05);
  position: sticky; top: 80px;
}
.sd-sidebar-cat {
  font-size: .67rem; font-weight: 800; color: var(--or);
  text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;
  display: flex; align-items: center; gap: 6px;
}
.sd-sidebar-name {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.15rem; font-weight: 900; color: var(--txt);
  line-height: 1.3; margin-bottom: 12px;
}
.sd-price-block {
  background: var(--bg); border-radius: 12px; padding: 14px 16px;
  margin-bottom: 16px; border: 1.5px solid var(--border);
}
.sd-price-from { font-size: .7rem; color: var(--hint); font-weight: 600; margin-bottom: 3px; }
.sd-price-val {
  font-family: 'Playfair Display', serif;
  font-size: 1.6rem; font-weight: 900; color: var(--or);
  line-height: 1;
}
.sd-price-unit { font-size: .78rem; color: var(--light); font-weight: 500; margin-left: 4px; }
.sd-price-note {
  font-size: .72rem; color: var(--hint); margin-top: 6px;
  display: flex; align-items: center; gap: 5px;
}

/* CTA Buttons */
.btn-sd-quote {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; background: linear-gradient(135deg, #f0a070, var(--or));
  color: #fff; font-weight: 800; font-size: .88rem;
  padding: 13px; border-radius: 12px; border: none; cursor: pointer;
  box-shadow: 0 4px 14px rgba(232,86,10,.28); transition: all .22s;
  margin-bottom: 9px; text-decoration: none;
}
.btn-sd-quote:hover { color:#fff; transform: translateY(-1px); box-shadow: 0 8px 20px rgba(232,86,10,.38); }
.btn-sd-wa {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; background: #25d366; color: #fff;
  font-weight: 800; font-size: .88rem;
  padding: 13px; border-radius: 12px; text-decoration: none;
  box-shadow: 0 3px 12px rgba(37,211,102,.3); transition: all .22s;
  margin-bottom: 9px;
}
.btn-sd-wa:hover { background: #1db954; color: #fff; transform: translateY(-1px); }
.btn-sd-call {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; background: var(--white); color: var(--txt);
  font-weight: 700; font-size: .88rem;
  padding: 12px; border-radius: 12px; text-decoration: none;
  border: 1.5px solid var(--border); transition: all .18s;
  margin-bottom: 16px;
}
.btn-sd-call:hover { border-color: var(--or); color: var(--or); background: var(--or-lt); }

/* Rating */
.sd-rating {
  text-align: center; padding-top: 14px;
  border-top: 1px solid var(--border);
  font-size: .75rem; color: var(--mid); font-weight: 600;
}
.sd-rating-stars { color: #f59e0b; font-size: .88rem; margin-bottom: 3px; }

/* Trust items */
.sd-trust { display: flex; flex-direction: column; gap: 8px; margin-top: 14px; }
.sd-trust-item {
  display: flex; align-items: center; gap: 8px;
  font-size: .76rem; color: var(--mid); font-weight: 600;
}
.sd-trust-item i { font-size: .85rem; flex-shrink: 0; }

/* ── RELATED SECTION ── */
.rel-section {
  background: var(--bg); padding: 28px 5% 50px;
  border-top: 1.5px solid var(--border);
}
.rel-inner { max-width: 1200px; margin: 0 auto; }
.rel-label {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: .67rem; font-weight: 800; color: var(--or);
  text-transform: uppercase; letter-spacing: 1.2px;
  background: var(--or-lt); border: 1px solid var(--or-b);
  padding: 4px 12px; border-radius: 28px; margin-bottom: 8px;
}
.rel-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.2rem; font-weight: 900; color: var(--txt); margin-bottom: 18px;
}
.rel-title span { color: var(--or); }
.rel-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr); gap: 13px;
}
@media(min-width:576px) { .rel-grid { grid-template-columns: repeat(3,1fr); gap:15px; } }
@media(min-width:992px) { .rel-grid { grid-template-columns: repeat(4,1fr); gap:16px; } }
.rel-svc-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 16px; padding: 16px 14px 14px;
  display: flex; flex-direction: column; gap: 6px;
  position: relative; transition: all .22s;
  box-shadow: 0 2px 10px rgba(26,35,50,.05); text-decoration: none;
}
.rel-svc-card:hover {
  border-color: rgba(232,86,10,.25); transform: translateY(-3px);
  box-shadow: 0 10px 28px rgba(26,35,50,.11);
}
.rel-svc-pop {
  position: absolute; top: 9px; right: 9px;
  background: var(--or-lt); color: var(--or);
  font-size: .56rem; font-weight: 800;
  padding: 3px 8px; border-radius: 18px; border: 1px solid var(--or-b);
}
.rel-svc-icon {
  width: 46px; height: 46px; border-radius: 12px;
  background: var(--or-lt); border: 1.5px solid rgba(232,86,10,.12);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.5rem; flex-shrink: 0;
}
.rel-svc-cat  { font-size: .6rem; font-weight: 800; color: var(--or); text-transform: uppercase; letter-spacing: .8px; }
.rel-svc-name { font-size: .87rem; font-weight: 700; color: var(--txt); line-height: 1.3; flex: 1; }
.rel-svc-meta { display: flex; justify-content: space-between; align-items: center; }
.rel-svc-rating { font-size: .73rem; color: #f59e0b; font-weight: 600; }
.rel-svc-price  { font-size: .78rem; font-weight: 800; color: var(--txt); }
.btn-rel-view {
  display: flex; align-items: center; justify-content: center; gap: 5px;
  width: 100%; background: linear-gradient(135deg, #f0a070, var(--or));
  color: #fff; font-weight: 700; font-size: .76rem;
  padding: 9px; border-radius: 9px;
  margin-top: 4px; transition: all .2s;
  box-shadow: 0 3px 10px rgba(232,86,10,.2);
}
.btn-rel-view:hover { color:#fff; transform:translateY(-1px); }
</style>



<!-- ── MAIN ── -->
<div class="sd-page">
  <div class="sd-page-inner">

    <!-- LEFT -->
    <div>
      <!-- Hero image -->
      <div class="sd-hero">
        <?php if (!empty($service['image'])): ?>
          <img src="/<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['name']) ?>">
        <?php else: ?>
          <?= htmlspecialchars($service['icon'] ?? '🔧') ?>
        <?php endif; ?>
        <?php if (!empty($service['is_popular'])): ?>
          <span class="sd-popular-badge">⭐ Popular</span>
        <?php endif; ?>
      </div>

      <!-- Description card -->
      <div class="sd-card">
        <div class="sd-card-title">
          <i class="bi bi-info-circle-fill"></i>
          About This Service
        </div>
        <?php if ($tags): ?>
        <div class="sd-tags">
          <?php foreach ($tags as $tag): ?>
            <span class="sd-tag"><?= htmlspecialchars($tag) ?></span>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="sd-desc">
          <?= !empty($service['description']) ? $service['description'] : '<p>'.htmlspecialchars($service['short_desc'] ?? '').'</p>' ?>
        </div>
      </div>

      <!-- Why Choose Us -->
      <div class="sd-card">
        <div class="sd-card-title">
          <i class="bi bi-trophy-fill"></i>
          Why Choose Incredible Heights?
        </div>
        <div class="sd-reasons">
          <?php foreach([
            ['🏗️','50 Years Experience','Trusted since 1975 with 5,000+ projects delivered.'],
            ['👷','Certified Professionals','All work by licensed and background-verified experts.'],
            ['🏠','Free Site Visit','We visit, assess, and give a transparent quotation.'],
            ['🛡️','Work Guarantee','All work backed by warranty. Your satisfaction is our promise.'],
          ] as [$ico,$title,$desc]): ?>
          <div class="sd-reason">
            <span class="sd-reason-ico"><?= $ico ?></span>
            <div>
              <div class="sd-reason-title"><?= $title ?></div>
              <div class="sd-reason-desc"><?= $desc ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- RIGHT: Sidebar -->
    <div>
      <div class="sd-sidebar">
        <div class="sd-sidebar-cat">
          <i class="bi bi-tag-fill"></i><?= htmlspecialchars($service['cat_name'] ?? 'SERVICE') ?>
        </div>
        <div class="sd-sidebar-name"><?= htmlspecialchars($service['name']) ?></div>

        <?php if (!empty($service['price_from']) && $service['price_from'] > 0): ?>
        <div class="sd-price-block">
          <div class="sd-price-from">Starting from</div>
          <div>
            <span class="sd-price-val">₹<?= number_format($service['price_from']) ?></span>
            <span class="sd-price-unit">/ <?= htmlspecialchars($service['price_unit'] ?? 'sqft') ?></span>
          </div>
          <div class="sd-price-note">
            <i class="bi bi-info-circle" style="color:var(--or);"></i>
            Final price depends on site measurement
          </div>
        </div>
        <?php else: ?>
        <div class="sd-price-block">
          <div class="sd-price-from">Pricing</div>
          <div style="font-size:.88rem;font-weight:700;color:var(--or);">Custom Quote</div>
          <div class="sd-price-note">
            <i class="bi bi-info-circle" style="color:var(--or);"></i>
            Based on site visit & measurement
          </div>
        </div>
        <?php endif; ?>

        <button class="btn-sd-quote"
                onclick="openEnquiryPopup('<?= addslashes(htmlspecialchars($service['name'])) ?>', 'service-detail', 'Get a free quotation for this service!')">
          <i class="bi bi-clipboard-check-fill"></i> Get Free Quotation
        </button>
        <a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '' ?>?text=Hi%2C+I%27m+interested+in+<?= urlencode($service['name']) ?>"
           target="_blank" class="btn-sd-wa">
          <i class="bi bi-whatsapp"></i> WhatsApp Now
        </a>
        <a href="tel:<?= defined('SITE_PHONE') ? SITE_PHONE : '' ?>" class="btn-sd-call">
          <i class="bi bi-telephone-fill"></i>
          <?= defined('SITE_PHONE') ? SITE_PHONE : '+91 9821130198' ?>
        </a>

        <?php if (!empty($service['rating'])): ?>
        <div class="sd-rating">
          <div class="sd-rating-stars">
            <?php
            $r = (float)$service['rating'];
            for($i=1;$i<=5;$i++) echo $i<=$r ? '★' : '☆';
            ?>
          </div>
          <?= number_format($r,1) ?>/5 Rating
        </div>
        <?php endif; ?>

        <div class="sd-trust">
          <div class="sd-trust-item"><i class="bi bi-shield-fill-check" style="color:var(--gr);"></i> 100% Quality Guarantee</div>
          <div class="sd-trust-item"><i class="bi bi-geo-alt-fill" style="color:var(--or);"></i> Free Site Visit Available</div>
          <div class="sd-trust-item"><i class="bi bi-award-fill" style="color:#d97706;"></i> 50+ Years Experience</div>
          <div class="sd-trust-item"><i class="bi bi-people-fill" style="color:var(--bl);"></i> 5000+ Projects Delivered</div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php if (!empty($relServices)): ?>
<div class="rel-section">
  <div class="rel-inner">
    <div class="rel-label"><i class="bi bi-grid-fill"></i> More Services</div>
    <div class="rel-title">You May Also <span>Need</span></div>
    <div class="rel-grid">
      <?php foreach ($relServices as $rs): ?>
      <a href="/service-detail.php?id=<?= (int)$rs['id'] ?>" class="rel-svc-card">
        <?php if (!empty($rs['is_popular'])): ?>
          <span class="rel-svc-pop">⭐ Popular</span>
        <?php endif; ?>
        <div class="rel-svc-icon"><?= htmlspecialchars($rs['icon'] ?? '🔧') ?></div>
        <div class="rel-svc-cat"><?= htmlspecialchars($rs['cat_name'] ?? '') ?></div>
        <div class="rel-svc-name"><?= htmlspecialchars($rs['name']) ?></div>
        <div class="rel-svc-meta">
          <span class="rel-svc-rating">★ <?= number_format($rs['rating'] ?? 4.5, 1) ?></span>
          <span class="rel-svc-price">₹<?= number_format($rs['price_from'] ?? 0) ?>/<?= htmlspecialchars($rs['price_unit'] ?? 'unit') ?></span>
        </div>
        <div class="btn-rel-view"><i class="bi bi-clipboard-check"></i> View Details</div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
