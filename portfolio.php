<?php
require_once __DIR__ . '/includes/functions.php';

$cat      = sanitizeInput($_GET['cat'] ?? '');
$projects = [];

try {
    if ($pdo) {
        $where  = ['status = 1'];
        $params = [];
        if ($cat) { $where[] = 'category = ?'; $params[] = $cat; }
        $stmt = $pdo->prepare("SELECT * FROM portfolio_projects WHERE " . implode(' AND ', $where) . " ORDER BY sort_order DESC, created_at DESC");
        $stmt->execute($params);
        $projects = $stmt->fetchAll();

        // All categories for dropdown
        $cats = $pdo->query("SELECT DISTINCT category FROM portfolio_projects WHERE status=1 AND category IS NOT NULL AND category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
    }
} catch (Exception $e) {}

// Demo data if DB empty
if (empty($projects)) {
    $allDemo = [
        ['id'=>1,'icon'=>'🏠','title'=>'2BHK Complete Renovation','category'=>'Renovation','location'=>'Noida Sector 62','description'=>'Civil + Electrical + Interior','budget'=>'₹8.5L','image'=>''],
        ['id'=>2,'icon'=>'🛋️','title'=>'Modular Kitchen','category'=>'Interior','location'=>'South Delhi','description'=>'Full modular kitchen with chimney','budget'=>'₹1.2L','image'=>''],
        ['id'=>3,'icon'=>'🎨','title'=>'Villa Exterior Painting','category'=>'Painting','location'=>'Gurugram','description'=>'Asian Paints Royale Shyne — full exterior','budget'=>'₹75K','image'=>''],
        ['id'=>4,'icon'=>'🏗️','title'=>'3BHK New Construction','category'=>'Civil','location'=>'Greater Noida','description'=>'Ground up construction — RCC frame','budget'=>'₹22L','image'=>''],
        ['id'=>5,'icon'=>'🪵','title'=>'Custom Wardrobes','category'=>'Carpentry','location'=>'Dwarka','description'=>'6-door sliding wardrobes with mirrors','budget'=>'₹85K','image'=>''],
        ['id'=>6,'icon'=>'⚡','title'=>'Full Home Wiring','category'=>'Electrical','location'=>'Faridabad','description'=>'Complete electrical rewiring — 3BHK','budget'=>'₹45K','image'=>''],
        ['id'=>7,'icon'=>'💧','title'=>'Terrace Waterproofing','category'=>'Civil','location'=>'Laxmi Nagar','description'=>'Dr Fixit waterproofing — 5 yr guarantee','budget'=>'₹28K','image'=>''],
        ['id'=>8,'icon'=>'🏢','title'=>'Office Interior','category'=>'Interior','location'=>'Connaught Place','description'=>'1200 sqft corporate office fit-out','budget'=>'₹14L','image'=>''],
        ['id'=>9,'icon'=>'🪴','title'=>'Garden & Landscaping','category'=>'Outdoor','location'=>'Vasant Kunj','description'=>'Full lawn, irrigation & lighting','budget'=>'₹1.8L','image'=>''],
    ];
    $cats = array_unique(array_column($allDemo, 'category'));
    sort($cats);
    $projects = $cat
        ? array_values(array_filter($allDemo, fn($p) => $p['category'] === $cat))
        : $allDemo;
}

$total     = count($projects);
$pageTitle = 'Our Portfolio — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
:root {
  --or:#e8560a; --or-lt:#fff5f0; --or-b:rgba(232,86,10,.18);
  --bl:#1565c0; --bl-lt:#f0f5ff;
  --gr:#2e7d32; --gr-lt:#f0faf0;
  --gd:#c9a84c; --gd-lt:#fffbf0;
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb; --white:#fff;
}

/* ── FILTER BAR ── */
.pf-filter-bar {
  background: var(--white);
  border-bottom: 1.5px solid var(--border);
  padding: 11px 5%;
  position: sticky; top: 66px; z-index: 200;
  box-shadow: 0 2px 10px rgba(26,35,50,.06);
}
@media(max-width:991px) { .pf-filter-bar { top: 60px; } }
.pf-filter-inner {
  max-width: 1400px; margin: 0 auto;
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.pf-badge {
  display: flex; align-items: center; gap: 8px; flex-shrink: 0;
}
.pf-badge-icon {
  width: 34px; height: 34px; border-radius: 9px;
  background: rgba(232,86,10,.1); border: 1.5px solid rgba(232,86,10,.22);
  display: flex; align-items: center; justify-content: center;
  color: var(--or); font-size: .9rem; flex-shrink: 0;
}
.pf-badge-title { font-size: .82rem; font-weight: 800; color: var(--txt); }
.pf-badge-count { font-size: .7rem; color: var(--hint); font-weight: 600; }
.pf-divider { width:1px; height:28px; background:var(--border); flex-shrink:0; }
@media(max-width:399px){ .pf-divider { display:none; } }

.pf-select-wrap { position:relative; flex:1; min-width:140px; max-width:280px; }
.pf-select-wrap .pf-chev {
  position:absolute; right:12px; top:50%; transform:translateY(-50%);
  pointer-events:none; color:var(--hint); font-size:.7rem; transition:transform .18s;
}
.pf-select {
  width:100%; appearance:none; -webkit-appearance:none;
  border:1.5px solid var(--border); border-radius:10px;
  padding:9px 34px 9px 13px; font-size:.84rem;
  font-family:'DM Sans',sans-serif; font-weight:600;
  color:var(--txt); background:var(--bg); outline:none; cursor:pointer;
  transition:border-color .18s, box-shadow .18s, background .18s;
}
.pf-select:focus { border-color:var(--or); box-shadow:0 0 0 3px rgba(232,86,10,.1); background:#fff; }
.pf-select.active { border-color:var(--or); background:var(--or-lt); color:var(--or); font-weight:700; }
.pf-clear {
  display:flex; align-items:center; gap:5px;
  font-size:.75rem; color:var(--or); font-weight:700;
  background:var(--or-lt); border:1.5px solid var(--or-b);
  padding:6px 13px; border-radius:20px; text-decoration:none;
  transition:opacity .15s; white-space:nowrap; flex-shrink:0;
}
.pf-clear:hover { opacity:.82; color:var(--or); }

/* ── PAGE AREA ── */
.pf-area {
  background: var(--bg);
  padding: 26px 5% 60px;
  min-height: 70vh;
}
.pf-area-inner { max-width: 1400px; margin: 0 auto; }

/* Section header */
.pf-section-hd { margin-bottom: 22px; }
.pf-section-label {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: .68rem; font-weight: 800; color: var(--or);
  text-transform: uppercase; letter-spacing: 1.3px;
  background: var(--or-lt); border: 1px solid var(--or-b);
  padding: 5px 14px; border-radius: 28px; margin-bottom: 10px;
}
.pf-section-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(1.4rem, 2.5vw, 2rem);
  font-weight: 900; color: var(--txt); margin-bottom: 5px;
}
.pf-section-title span { color: var(--or); }
.pf-section-sub { font-size: .88rem; color: var(--light); }

/* ── CARD GRID ── */
.pf-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
}
@media(min-width:576px)  { .pf-grid { grid-template-columns: repeat(3,1fr); gap:16px; } }
@media(min-width:992px)  { .pf-grid { grid-template-columns: repeat(4,1fr); gap:18px; } }
@media(min-width:1400px) { .pf-grid { grid-template-columns: repeat(5,1fr); } }

/* ── PROJECT CARD ── */
.pf-card {
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 16px;
  overflow: hidden;
  display: flex; flex-direction: column;
  transition: all .22s;
  box-shadow: 0 2px 10px rgba(26,35,50,.05);
  cursor: pointer;
  text-decoration: none;
}
.pf-card:hover {
  border-color: rgba(232,86,10,.25);
  box-shadow: 0 10px 30px rgba(26,35,50,.12);
  transform: translateY(-3px);
}
.pf-card-img {
  height: 160px; width: 100%;
  object-fit: cover; display: block;
}
@media(min-width:768px) { .pf-card-img { height: 190px; } }

.pf-card-icon-area {
  height: 160px;
  display: flex; align-items: center; justify-content: center;
  font-size: 3.2rem;
  background: linear-gradient(135deg, #f8fafc, var(--or-lt));
  border-bottom: 1.5px solid rgba(232,86,10,.08);
  position: relative;
}
@media(min-width:768px) { .pf-card-icon-area { height: 190px; font-size: 3.8rem; } }

/* Category dot on icon */
.pf-cat-dot {
  position: absolute; top: 10px; left: 10px;
  font-size: .6rem; font-weight: 800; text-transform: uppercase;
  letter-spacing: .7px;
  background: var(--white); color: var(--or);
  border: 1.5px solid var(--or-b);
  padding: 3px 10px; border-radius: 20px;
}

.pf-card-body { padding: 14px 14px 16px; flex: 1; display: flex; flex-direction: column; gap: 5px; }

.pf-card-loc {
  display: flex; align-items: center; gap: 5px;
  font-size: .68rem; font-weight: 800; color: var(--or);
  text-transform: uppercase; letter-spacing: .7px;
}
.pf-card-name {
  font-size: .88rem; font-weight: 800; color: var(--txt); line-height: 1.3;
}
@media(max-width:575px) { .pf-card-name { font-size: .82rem; } }
.pf-card-desc { font-size: .74rem; color: var(--light); line-height: 1.5; flex: 1; }
.pf-card-budget {
  font-size: .85rem; font-weight: 800; color: var(--or);
  margin-top: 2px;
}

/* ── CTA STRIP ── */
.pf-cta {
  max-width: 1400px; margin: 32px auto 0;
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 16px;
  padding: 24px 28px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 16px;
  box-shadow: 0 2px 10px rgba(26,35,50,.05);
}
@media(max-width:575px) { .pf-cta { padding: 18px 16px; } }
.pf-cta-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem; font-weight: 800; color: var(--txt); margin-bottom: 4px;
}
.pf-cta-sub { font-size: .84rem; color: var(--light); }
.pf-cta-btns { display: flex; gap: 10px; flex-wrap: wrap; }
.btn-pf-cta {
  display: flex; align-items: center; gap: 7px;
  background: linear-gradient(135deg, #f0a070, var(--or));
  color: #fff; font-weight: 700; font-size: .85rem;
  padding: 11px 20px; border-radius: 10px; border: none;
  cursor: pointer; text-decoration: none;
  box-shadow: 0 3px 12px rgba(232,86,10,.28); transition: all .2s;
}
.btn-pf-cta:hover { color:#fff; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(232,86,10,.38); }
.btn-pf-wa {
  display: flex; align-items: center; gap: 7px;
  background: #25d366; color: #fff; font-weight: 700; font-size: .85rem;
  padding: 11px 20px; border-radius: 10px; text-decoration: none;
  transition: all .2s;
}
.btn-pf-wa:hover { background: #1db954; color:#fff; transform: translateY(-1px); }
</style>

<!-- ── FILTER BAR ── -->
<div class="pf-filter-bar">
  <div class="pf-filter-inner">

    <!-- Badge -->
    <div class="pf-badge">
      <div class="pf-badge-icon"><i class="bi bi-images"></i></div>
      <div>
        <div class="pf-badge-title">Our Portfolio</div>
        <div class="pf-badge-count"><?= $total ?> project<?= $total !== 1 ? 's' : '' ?></div>
      </div>
    </div>

    <div class="pf-divider"></div>

    <!-- Category dropdown -->
    <div class="pf-select-wrap">
      <select class="pf-select <?= $cat ? 'active' : '' ?>"
              onchange="window.location.href='/portfolio.php'+(this.value?'?cat='+encodeURIComponent(this.value):'')">
        <option value="">All Categories</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= htmlspecialchars($c) ?>" <?= $cat === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
      </select>
      <i class="bi bi-chevron-down pf-chev"></i>
    </div>

    <!-- Clear -->
    <?php if ($cat): ?>
      <a href="/portfolio.php" class="pf-clear"><i class="bi bi-x-lg"></i> Clear</a>
    <?php endif; ?>

  </div>
</div>

<!-- ── MAIN AREA ── -->
<div class="pf-area">
  <div class="pf-area-inner">

    <!-- Section heading -->
    <div class="pf-section-hd">
      <div class="pf-section-label"><i class="bi bi-trophy-fill"></i> Our Work</div>
      <div class="pf-section-title">5000+ Projects <span>Delivered</span></div>
      <div class="pf-section-sub">A glimpse of our finest work across Delhi NCR</div>
    </div>

    <?php if (empty($projects)): ?>
      <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:3.5rem;margin-bottom:16px;">🔍</div>
        <h5 style="color:var(--mid);margin-bottom:8px;">No projects found in this category</h5>
        <a href="/portfolio.php" style="color:var(--or);font-weight:600;text-decoration:none;">← View all projects</a>
      </div>
    <?php else: ?>
    <div class="pf-grid">
      <?php foreach ($projects as $p):
        $icon   = $p['icon']        ?? '🏗️';
        $name   = $p['title']       ?? $p['name'] ?? '';
        $loc    = $p['location']    ?? '';
        $desc   = $p['description'] ?? '';
        $budget = $p['budget']      ?? ($p['budget_display'] ?? '');
        $catLbl = $p['category']    ?? '';
        $img    = $p['image']       ?? '';
      ?>
      <div class="pf-card">
        <?php if ($img): ?>
          <img src="/<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($name) ?>" class="pf-card-img">
        <?php else: ?>
          <div class="pf-card-icon-area">
            <?php if ($catLbl): ?><span class="pf-cat-dot"><?= htmlspecialchars($catLbl) ?></span><?php endif; ?>
            <?= htmlspecialchars($icon) ?>
          </div>
        <?php endif; ?>
        <div class="pf-card-body">
          <?php if ($loc): ?>
            <div class="pf-card-loc"><i class="bi bi-geo-alt-fill"></i><?= htmlspecialchars($loc) ?></div>
          <?php endif; ?>
          <div class="pf-card-name"><?= htmlspecialchars($name) ?></div>
          <?php if ($desc): ?>
            <div class="pf-card-desc"><?= htmlspecialchars(substr($desc, 0, 80)) ?><?= strlen($desc) > 80 ? '…' : '' ?></div>
          <?php endif; ?>
          <?php if ($budget): ?>
            <div class="pf-card-budget"><?= htmlspecialchars($budget) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- CTA Strip -->
    <div class="pf-cta">
      <div>
        <div class="pf-cta-title">Want to see your project in our portfolio?</div>
        <div class="pf-cta-sub">Tell us your requirement — we'll make it happen.</div>
      </div>
      <div class="pf-cta-btns">
        <a href="/contact.php" class="btn-pf-cta">
          <i class="bi bi-calendar-check-fill"></i> Discuss Your Project
        </a>
        <a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '' ?>?text=Hi%2C+I+want+to+discuss+a+project" target="_blank" class="btn-pf-wa">
          <i class="bi bi-whatsapp"></i> WhatsApp Us
        </a>
      </div>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>