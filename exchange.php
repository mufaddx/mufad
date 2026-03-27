<?php
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Product Exchange — ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$exchangeItems = [];
try {
    $exchangeItems = $pdo->query("
        SELECT p.*, pc.name AS cat_name
        FROM products p
        LEFT JOIN product_categories pc ON p.category_id = pc.id
        WHERE p.status = 1
        ORDER BY p.rating DESC LIMIT 8
    ")->fetchAll();
} catch(Exception $e) {}

if (empty($exchangeItems)) {
    $exchangeItems = [
        ['id'=>1,'name'=>'BLDC Energy Saving Ceiling Fan','cat_name'=>'CEILING FAN','price'=>2800,'original_price'=>3500,'icon'=>'🌀','exchange_discount'=>15,'image'=>''],
        ['id'=>2,'name'=>'1.5 Ton 5-Star Inverter AC','cat_name'=>'SPLIT AC','price'=>42000,'original_price'=>52000,'icon'=>'❄️','exchange_discount'=>20,'image'=>''],
        ['id'=>3,'name'=>'18W Round LED Panel Light','cat_name'=>'LED LIGHTING','price'=>320,'original_price'=>450,'icon'=>'💡','exchange_discount'=>10,'image'=>''],
        ['id'=>4,'name'=>'Premium Modular Switch Board','cat_name'=>'SWITCHES','price'=>850,'original_price'=>1200,'icon'=>'🔌','exchange_discount'=>12,'image'=>''],
        ['id'=>5,'name'=>'Solar Water Heater 100L','cat_name'=>'SOLAR','price'=>18000,'original_price'=>22000,'icon'=>'☀️','exchange_discount'=>18,'image'=>''],
        ['id'=>6,'name'=>'Smart LED TV 43 inch','cat_name'=>'ELECTRONICS','price'=>28000,'original_price'=>35000,'icon'=>'📺','exchange_discount'=>25,'image'=>''],
        ['id'=>7,'name'=>'4mm Electrical Wire 90m Roll','cat_name'=>'WIRING','price'=>1850,'original_price'=>2200,'icon'=>'⚡','exchange_discount'=>8,'image'=>''],
        ['id'=>8,'name'=>'Inverter Battery 150Ah Tubular','cat_name'=>'POWER BACKUP','price'=>12500,'original_price'=>15000,'icon'=>'🔋','exchange_discount'=>14,'image'=>''],
    ];
}
?>
<style>
:root {
  --or:#e8560a; --or-lt:#fff5f0; --or-b:rgba(232,86,10,.18);
  --bl:#1565c0; --bl-lt:#f0f5ff;
  --gr:#2e7d32; --gr-lt:#f0faf0;
  --pu:#7c3aed; --pu-lt:#f5f3ff;
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb; --white:#fff;
}
.ex-page { background:var(--bg); padding:0 0 60px; }

/* ── HEADER BAR ── */
.ex-hdr {
  background:var(--white); border-bottom:1.5px solid var(--border);
  padding:20px 5%; box-shadow:0 2px 10px rgba(26,35,50,.05);
}
.ex-hdr-inner {
  max-width:1400px; margin:0 auto;
  display:flex; align-items:center; justify-content:space-between;
  gap:16px; flex-wrap:wrap;
}
.ex-page-label {
  display:inline-flex; align-items:center; gap:6px;
  font-size:.67rem; font-weight:800; color:var(--or);
  text-transform:uppercase; letter-spacing:1.3px;
  background:var(--or-lt); border:1px solid var(--or-b);
  padding:4px 12px; border-radius:28px; margin-bottom:6px; width:fit-content;
}
.ex-page-title {
  font-family:'Playfair Display',Georgia,serif;
  font-size:clamp(1.4rem,3vw,1.9rem); font-weight:900;
  color:var(--txt); margin:0 0 4px;
}
.ex-page-sub { font-size:.88rem; color:var(--light); margin:0; }
.ex-page-sub strong { color:var(--or); font-weight:800; }
.ex-hdr-btns { display:flex; gap:9px; flex-wrap:wrap; }
.btn-ex-wa {
  display:flex; align-items:center; gap:7px;
  background:#25d366; color:#fff; font-weight:700; font-size:.83rem;
  padding:10px 18px; border-radius:11px; text-decoration:none;
  box-shadow:0 3px 12px rgba(37,211,102,.3); transition:all .2s; white-space:nowrap;
}
.btn-ex-wa:hover { background:#1db954; color:#fff; transform:translateY(-1px); }
.btn-ex-call {
  display:flex; align-items:center; gap:7px;
  background:linear-gradient(135deg,#f0a070,var(--or));
  color:#fff; font-weight:700; font-size:.83rem;
  padding:10px 18px; border-radius:11px; border:none; cursor:pointer;
  box-shadow:0 3px 12px rgba(232,86,10,.28); transition:all .2s; white-space:nowrap;
}
.btn-ex-call:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(232,86,10,.38); }

/* ── MAIN ── */
.ex-main { max-width:1400px; margin:0 auto; padding:28px 5% 0; }

/* Section label + title */
.ex-sec-lbl {
  display:inline-flex; align-items:center; gap:6px;
  font-size:.66rem; font-weight:800; color:var(--or);
  text-transform:uppercase; letter-spacing:1.2px;
  background:var(--or-lt); border:1px solid var(--or-b);
  padding:4px 12px; border-radius:28px; margin-bottom:9px;
}
.ex-sec-title {
  font-family:'Playfair Display',serif;
  font-size:1.2rem; font-weight:900; color:var(--txt); margin-bottom:18px;
}
.ex-sec-title span { color:var(--or); }

/* ── STEPS ── */
.ex-steps {
  display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-bottom:36px;
}
@media(min-width:576px) { .ex-steps { grid-template-columns:repeat(4,1fr); gap:14px; } }
.ex-step {
  background:var(--white); border:1.5px solid var(--border);
  border-radius:16px; padding:22px 16px 18px;
  text-align:center; position:relative;
  box-shadow:0 2px 10px rgba(26,35,50,.05);
  transition:all .22s;
}
.ex-step:hover {
  border-color:rgba(232,86,10,.2); transform:translateY(-3px);
  box-shadow:0 8px 24px rgba(26,35,50,.1);
}
.ex-step-n {
  position:absolute; top:-11px; left:50%; transform:translateX(-50%);
  width:22px; height:22px; border-radius:50%;
  background:var(--or); color:#fff; font-size:.6rem; font-weight:900;
  display:flex; align-items:center; justify-content:center;
  box-shadow:0 2px 8px rgba(232,86,10,.35);
}
.ex-step-ico { font-size:2.2rem; display:block; margin-bottom:9px; line-height:1; }
.ex-step-ttl { font-size:.84rem; font-weight:800; color:var(--txt); margin-bottom:4px; }
.ex-step-dsc { font-size:.72rem; color:var(--light); line-height:1.5; }

/* ── PRODUCTS GRID ── */
.ex-grid {
  display:grid; grid-template-columns:repeat(2,1fr); gap:13px; margin-bottom:36px;
}
@media(min-width:576px) { .ex-grid { grid-template-columns:repeat(3,1fr); gap:15px; } }
@media(min-width:992px) { .ex-grid { grid-template-columns:repeat(4,1fr); gap:16px; } }

.ex-card {
  background:var(--white); border:1.5px solid var(--border);
  border-radius:16px; overflow:hidden;
  display:flex; flex-direction:column;
  transition:all .22s; box-shadow:0 2px 10px rgba(26,35,50,.05);
}
.ex-card:hover {
  border-color:rgba(232,86,10,.22); transform:translateY(-3px);
  box-shadow:0 10px 28px rgba(26,35,50,.11);
}
.ex-card-img {
  height:140px;
  background:linear-gradient(135deg,var(--or-lt),#fff8f5);
  display:flex; align-items:center; justify-content:center;
  font-size:3rem; position:relative;
  border-bottom:1px solid var(--border); overflow:hidden;
}
.ex-card-img img { width:100%; height:100%; object-fit:contain; padding:10px; }
.ex-disc { position:absolute; top:9px; left:9px; background:#ef4444; color:#fff; font-size:.58rem; font-weight:800; padding:3px 9px; border-radius:18px; }
.ex-card-body { padding:12px 13px 14px; display:flex; flex-direction:column; flex:1; }
.ex-card-cat  { font-size:.6rem; font-weight:800; text-transform:uppercase; letter-spacing:.9px; color:var(--or); margin-bottom:4px; }
.ex-card-name { font-size:.87rem; font-weight:700; color:var(--txt); line-height:1.3; margin-bottom:8px; flex:1; }
.ex-bonus {
  display:inline-flex; align-items:center; gap:5px;
  background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0;
  font-size:.67rem; font-weight:800;
  padding:4px 10px; border-radius:20px; margin-bottom:11px; width:fit-content;
}
.btn-ex-now {
  display:flex; align-items:center; justify-content:center; gap:6px;
  width:100%; background:linear-gradient(135deg,#f0a070,var(--or));
  color:#fff; font-weight:700; font-size:.77rem;
  padding:10px; border-radius:10px; border:none; cursor:pointer;
  transition:all .2s; box-shadow:0 3px 10px rgba(232,86,10,.22); margin-top:auto;
}
.btn-ex-now:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(232,86,10,.34); }

/* ── WE ACCEPT ── */
.ex-accept {
  background:var(--white); border:1.5px solid var(--border);
  border-radius:18px; padding:28px;
  box-shadow:0 2px 12px rgba(26,35,50,.06); margin-bottom:36px;
}
@media(max-width:575px) { .ex-accept { padding:20px 16px; } }
.ex-accept-ttl {
  font-family:'Playfair Display',serif;
  font-size:1.15rem; font-weight:900; color:var(--txt);
  text-align:center; margin-bottom:4px;
}
.ex-accept-sub { font-size:.84rem; color:var(--light); text-align:center; margin-bottom:18px; }
.ex-accept-tags { display:flex; flex-wrap:wrap; gap:8px; justify-content:center; margin-bottom:22px; }
.ex-tag {
  display:flex; align-items:center; gap:5px;
  background:var(--bg); border:1.5px solid var(--border);
  color:var(--mid); font-size:.78rem; font-weight:600;
  padding:7px 14px; border-radius:22px; transition:all .15s;
}
.ex-tag:hover { border-color:rgba(232,86,10,.25); background:var(--or-lt); color:var(--or); }
.ex-accept-cta { display:flex; justify-content:center; gap:10px; flex-wrap:wrap; }

/* ── TRUST STATS ── */
.ex-trust {
  display:grid; grid-template-columns:repeat(2,1fr); gap:12px;
}
@media(min-width:768px) { .ex-trust { grid-template-columns:repeat(4,1fr); } }
.ex-trust-card {
  background:var(--white); border:1.5px solid var(--border);
  border-radius:14px; padding:18px 14px; text-align:center;
  box-shadow:0 2px 8px rgba(26,35,50,.04);
}
.ex-trust-ico {
  width:40px; height:40px; border-radius:11px;
  display:flex; align-items:center; justify-content:center;
  font-size:1rem; margin:0 auto 10px;
}
.ex-trust-val   { font-size:1.15rem; font-weight:900; margin-bottom:2px; }
.ex-trust-label { font-size:.72rem; color:var(--light); font-weight:600; }
</style>

  <div class="ex-main">

    <!-- HOW IT WORKS -->
    <div class="ex-sec-lbl"><i class="bi bi-info-circle-fill"></i> Simple Process</div>
    <div class="ex-sec-title">How <span>Exchange</span> Works</div>
    <div class="ex-steps">
      <?php foreach([
        ['📸','Share Photos',  'WhatsApp photos of your old product — any brand, any condition'],
        ['💰','Get Valuation', 'We give you the best exchange value within just 1 hour'],
        ['🛒','Choose New',    'Pick any new product from our wide collection'],
        ['🚚','Doorstep Swap', 'We pickup your old product & deliver the new one free!'],
      ] as $i => [$ico,$ttl,$dsc]): ?>
      <div class="ex-step">
        <div class="ex-step-n"><?= $i+1 ?></div>
        <span class="ex-step-ico"><?= $ico ?></span>
        <div class="ex-step-ttl"><?= $ttl ?></div>
        <div class="ex-step-dsc"><?= $dsc ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- PRODUCTS -->
    <div class="ex-sec-lbl"><i class="bi bi-tag-fill"></i> Exchange Eligible</div>
    <div class="ex-sec-title">Available for <span>Exchange</span></div>
    <div class="ex-grid">
      <?php foreach($exchangeItems as $p):
        $disc = (int)($p['exchange_discount'] ?? rand(10,25));
        $img  = $p['image'] ?? '';
      ?>
      <div class="ex-card">
        <div class="ex-card-img">
          <?php if($img): ?>
            <img src="/uploads/products/<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
          <?php else: ?>
            <?= htmlspecialchars($p['icon'] ?? '📦') ?>
          <?php endif; ?>
          <span class="ex-disc">+<?= $disc ?>% OFF</span>
        </div>
        <div class="ex-card-body">
          <div class="ex-card-cat"><?= htmlspecialchars($p['cat_name'] ?? '') ?></div>
          <div class="ex-card-name"><?= htmlspecialchars($p['name']) ?></div>
          <div class="ex-bonus"><i class="bi bi-check-circle-fill"></i> Extra <?= $disc ?>% Exchange Bonus</div>
          <button class="btn-ex-now" onclick="openExchange('<?= addslashes(htmlspecialchars($p['name'])) ?>')">
            <i class="bi bi-arrow-left-right"></i> Exchange Now
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- WE ACCEPT -->
    <div class="ex-accept">
      <div style="display:flex;justify-content:center;margin-bottom:8px;">
        <div class="ex-sec-lbl"><i class="bi bi-recycle"></i> We Accept</div>
      </div>
      <div class="ex-accept-ttl">Old Products We Exchange</div>
      <div class="ex-accept-sub">Any brand, any condition — we accept it all across Delhi NCR</div>
      <div class="ex-accept-tags">
        <?php foreach([
          ['🌀','Old Fans'],['❄️','Old ACs'],['💡','Old Lights'],['🔌','Old Switches'],
          ['📺','Old TVs'],['🚿','Old Geysers'],['☀️','Old Solar Panels'],['🔋','Old Inverters'],
          ['⚡','Old Wiring'],['🏠','Old Appliances'],['🔧','Old Tools'],['📻','Old Gadgets'],
        ] as [$ic,$lbl]): ?>
          <span class="ex-tag"><?= $ic ?> <?= $lbl ?></span>
        <?php endforeach; ?>
      </div>
      <div class="ex-accept-cta">
        <a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '' ?>?text=Hi%2C+I+want+to+exchange+my+old+product.+Please+give+me+the+value."
           target="_blank" class="btn-ex-wa">
          <i class="bi bi-whatsapp"></i> WhatsApp for Exchange Quote
        </a>
        <button class="btn-ex-call"
                onclick="openEnquiryPopup('Exchange Pickup','exchange','Tell us what you have — we handle the rest!')">
          <i class="bi bi-calendar-check-fill"></i> Book Free Pickup
        </button>
      </div>
    </div>

    <!-- TRUST STATS -->
    <div class="ex-trust">
      <?php foreach([
        ['bi-arrow-left-right','5000+','Products Exchanged','#e8560a','rgba(232,86,10,.1)'],
        ['bi-clock-fill',      '1 Hr', 'Valuation Time',    '#1565c0','rgba(21,101,192,.1)'],
        ['bi-truck',           'Free', 'Pickup & Delivery', '#2e7d32','rgba(46,125,50,.1)'],
        ['bi-shield-fill-check','100%','Satisfaction Rate', '#7c3aed','rgba(124,58,237,.1)'],
      ] as [$ic,$val,$lbl,$clr,$bg]): ?>
      <div class="ex-trust-card">
        <div class="ex-trust-ico" style="background:<?= $bg ?>;color:<?= $clr ?>;"><i class="bi <?= $ic ?>"></i></div>
        <div class="ex-trust-val" style="color:<?= $clr ?>;"><?= $val ?></div>
        <div class="ex-trust-label"><?= $lbl ?></div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<script>
function openExchange(productName) {
  var msg = 'Hi%2C+I+want+to+exchange+my+old+product+for%3A+' + encodeURIComponent(productName) + '.+Please+guide+me+on+the+process.';
  window.open('https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '' ?>?text=' + msg, '_blank');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>