<?php
require_once __DIR__ . '/includes/functions.php';

$packages = [];
if ($pdo) {
    try {
        $packages = $pdo->query("SELECT * FROM packages WHERE status=1 ORDER BY price ASC")->fetchAll();
    } catch (Exception $e) {}
}

if (empty($packages)) {
    $packages = [
        ['id'=>1,'name'=>'Basic Renovation','description'=>'Perfect for single-room renovation or minor repairs.','price'=>50000,'price_unit'=>'project','is_popular'=>0,'features'=>json_encode(['Painting (1 room)','Minor plumbing fixes','Electrical safety check','Dedicated site supervisor','Free site visit']),'color'=>'#e8560a','bg'=>'#fff5f0','icon'=>'🔧'],
        ['id'=>2,'name'=>'Complete Interior','description'=>'Full interior design & execution for 1BHK or 2BHK homes.','price'=>150000,'price_unit'=>'project','is_popular'=>1,'features'=>json_encode(['False ceiling with LED','Modular kitchen','Wardrobes & storage','Flooring & tiles','Full painting','Site supervisor + 1yr support']),'color'=>'#1565c0','bg'=>'#f0f5ff','icon'=>'🛋️'],
        ['id'=>3,'name'=>'Premium Construction','description'=>'End-to-end construction — from foundation to finishing.','price'=>450000,'price_unit'=>'project','is_popular'=>0,'features'=>json_encode(['RCC structure','Complete plumbing & electrical','Interior design included','Premium finishing','5-year structural warranty','Dedicated project manager']),'color'=>'#2e7d32','bg'=>'#f0faf0','icon'=>'🏗️'],
    ];
}

$pageTitle = 'Construction Packages — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
:root {
  --or:#e8560a; --bl:#1565c0; --gr:#2e7d32; --gd:#c9a84c;
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb; --white:#fff;
}

/* ── PAGE WRAPPER ── */
.pkg-page { background: var(--bg); padding: 44px 5% 60px; }
.pkg-page-inner { max-width: 1100px; margin: 0 auto; }

/* ── SECTION HEADER ── */
.pkg-header { text-align: center; margin-bottom: 40px; }
.pkg-label {
  display: inline-block; font-size: .68rem; font-weight: 800;
  color: var(--gd); text-transform: uppercase; letter-spacing: 1.5px;
  background: #fffbf0; border: 1px solid rgba(201,168,76,.25);
  padding: 5px 14px; border-radius: 28px; margin-bottom: 12px;
}
.pkg-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(1.8rem,3.5vw,2.6rem); font-weight: 900;
  color: var(--txt); margin-bottom: 10px;
}
.pkg-title span { color: var(--or); }
.pkg-sub { font-size: .92rem; color: var(--light); max-width: 480px; margin: 0 auto; }

/* ── PRICING CARDS ── */
.pkg-grid {
  display: grid;
  grid-template-columns: repeat(1,1fr);
  gap: 20px;
}
@media(min-width:640px) { .pkg-grid { grid-template-columns: repeat(2,1fr); } }
@media(min-width:992px) { .pkg-grid { grid-template-columns: repeat(3,1fr); } }

.pkg-card {
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 20px;
  overflow: hidden;
  display: flex; flex-direction: column;
  transition: all .22s;
  box-shadow: 0 3px 14px rgba(26,35,50,.07);
  position: relative;
}
.pkg-card:hover {
  box-shadow: 0 12px 36px rgba(26,35,50,.14);
  transform: translateY(-4px);
}
.pkg-card.popular {
  border-width: 2px;
}

/* Popular ribbon */
.pkg-ribbon {
  position: absolute; top: 18px; right: -28px;
  background: var(--gd); color: var(--txt);
  font-size: .62rem; font-weight: 800; letter-spacing: .8px;
  padding: 5px 40px; transform: rotate(45deg);
  text-transform: uppercase;
}

/* Card top accent bar */
.pkg-accent-bar {
  height: 5px;
  border-radius: 0;
}

/* Icon + name area */
.pkg-top { padding: 24px 24px 0; }
.pkg-icon-wrap {
  width: 54px; height: 54px; border-radius: 15px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.7rem; margin-bottom: 14px;
}
.pkg-name {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem; font-weight: 800; color: var(--txt); margin-bottom: 6px;
}
.pkg-desc { font-size: .84rem; color: var(--light); line-height: 1.55; margin-bottom: 18px; }

/* Price */
.pkg-price-block {
  background: var(--bg); border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  padding: 16px 24px;
}
.pkg-price {
  font-family: 'Playfair Display', serif;
  font-size: 2rem; font-weight: 900; color: var(--txt); line-height: 1;
}
.pkg-price-sub { font-size: .78rem; color: var(--hint); margin-top: 4px; }
.pkg-price-note { font-size: .72rem; color: var(--hint); margin-top: 6px; font-style: italic; }

/* Features */
.pkg-features { padding: 20px 24px; flex: 1; }
.pkg-feature {
  display: flex; align-items: flex-start; gap: 10px;
  margin-bottom: 10px; font-size: .85rem; color: var(--mid);
}
.pkg-check {
  width: 20px; height: 20px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; font-size: .7rem; font-weight: 800;
  color: #fff; margin-top: 1px;
}

/* CTA button */
.pkg-cta { padding: 0 24px 24px; }
.btn-pkg-cta {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; padding: 13px; border-radius: 12px;
  font-weight: 700; font-size: .9rem; border: none; cursor: pointer;
  transition: all .22s; color: #fff;
  box-shadow: 0 4px 14px rgba(0,0,0,.18);
}
.btn-pkg-cta:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(0,0,0,.22); }

/* ── CUSTOM CTA STRIP ── */
.pkg-custom {
  margin-top: 36px;
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 18px;
  padding: 32px 36px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 20px;
  box-shadow: 0 3px 14px rgba(26,35,50,.06);
}
.pkg-custom-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.3rem; font-weight: 800; color: var(--txt); margin-bottom: 5px;
}
.pkg-custom-sub { font-size: .87rem; color: var(--light); }
.btn-custom {
  display: flex; align-items: center; gap: 8px;
  background: linear-gradient(135deg, #f0a070, var(--or));
  color: #fff; font-weight: 700; font-size: .9rem;
  padding: 13px 26px; border-radius: 12px; border: none;
  cursor: pointer; transition: all .22s; white-space: nowrap;
  box-shadow: 0 4px 14px rgba(232,86,10,.3);
}
.btn-custom:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(232,86,10,.4); }
</style>

<div class="pkg-page">
  <div class="pkg-page-inner">

    <!-- Section Header -->
    <div class="pkg-header">
      <div class="pkg-label">Transparent Pricing</div>
      <h2 class="pkg-title">Choose Your <span>Package</span></h2>
      <p class="pkg-sub">All packages include a free site visit and detailed quotation — no hidden charges.</p>
    </div>

    <!-- Package Cards -->
    <div class="pkg-grid">
      <?php
      $colors = ['#e8560a','#1565c0','#2e7d32'];
      $bgs    = ['#fff5f0','#f0f5ff','#f0faf0'];
      $icons  = ['🔧','🛋️','🏗️'];
      foreach ($packages as $i => $pkg):
        $features = is_string($pkg['features']) ? json_decode($pkg['features'], true) : ($pkg['features'] ?? []);
        $color = $pkg['color'] ?? $colors[$i % 3];
        $bg    = $pkg['bg']    ?? $bgs[$i % 3];
        $icon  = $pkg['icon']  ?? $icons[$i % 3];
        $pop   = !empty($pkg['is_popular']);
      ?>
      <div class="pkg-card <?= $pop ? 'popular' : '' ?>"
           style="<?= $pop ? "border-color:{$color}55;" : '' ?>">

        <?php if ($pop): ?>
          <div class="pkg-ribbon" style="background:var(--gd);">POPULAR</div>
        <?php endif; ?>

        <!-- Top accent bar -->
        <div class="pkg-accent-bar" style="background:<?= $color ?>;"></div>

        <!-- Top content -->
        <div class="pkg-top">
          <div class="pkg-icon-wrap" style="background:<?= $bg ?>;border:1.5px solid <?= $color ?>25;">
            <?= $icon ?>
          </div>
          <div class="pkg-name"><?= htmlspecialchars($pkg['name']) ?></div>
          <div class="pkg-desc"><?= htmlspecialchars($pkg['description'] ?? '') ?></div>
        </div>

        <!-- Price block -->
        <div class="pkg-price-block">
          <div class="pkg-price" style="color:<?= $color ?>;">
            ₹<?= number_format($pkg['price']) ?>
          </div>
          <div class="pkg-price-sub">onwards / <?= htmlspecialchars($pkg['price_unit'] ?? 'project') ?></div>
          <div class="pkg-price-note">*Final price depends on measurements & materials</div>
        </div>

        <!-- Features -->
        <div class="pkg-features">
          <?php if (!empty($features)): ?>
            <?php foreach ($features as $f): ?>
            <div class="pkg-feature">
              <div class="pkg-check" style="background:<?= $color ?>;">✓</div>
              <span><?= htmlspecialchars($f) ?></span>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- CTA -->
        <div class="pkg-cta">
          <button class="btn-pkg-cta"
                  style="background:linear-gradient(135deg, <?= $color ?>dd, <?= $color ?>);"
                  onclick="openEnquiryPopup('Package: <?= addslashes(htmlspecialchars($pkg['name'])) ?>','packages','Our expert will call you with a detailed quote!')">
            <i class="bi bi-calendar-check-fill"></i> Get Free Quote
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Custom Work Strip -->
    <div class="pkg-custom">
      <div>
        <div class="pkg-custom-title">Need Something Custom?</div>
        <div class="pkg-custom-sub">Tell us exactly what you need — we'll create a tailored package just for you.</div>
      </div>
      <button class="btn-custom"
              onclick="openEnquiryPopup('Custom Package Enquiry','packages','Tell us your requirements!')">
        <i class="bi bi-chat-dots-fill"></i> Talk to Our Expert
      </button>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>