<?php
$pageTitle = 'Incredible Heights — Construction & Interior Solutions | Delhi NCR';
$pageDesc  = 'Delhi NCR\'s most trusted construction company. Civil, Interior, Electrical, Plumbing & 350+ services. Book free site visit today!';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// ── FETCH DATA ─────────────────────────────────────────────
$popularServices = [];
$topProducts     = [];
$featuredPlots   = [];
$allTags         = [];

try {
    if (!$pdo) throw new Exception('No DB');
    $featSvc = [];
    try {
        $featSvc = $pdo->query("
            SELECT s.*, sc.name AS cat_name
            FROM home_featured hf
            JOIN services s ON s.id = hf.item_id AND s.status = 1
            LEFT JOIN service_categories sc ON s.category_id = sc.id
            WHERE hf.item_type = 'service' AND hf.status = 1
            ORDER BY hf.sort_order ASC LIMIT 4
        ")->fetchAll();
    } catch (Exception $eFeat) { $featSvc = []; }

    $popularServices = !empty($featSvc) ? $featSvc : $pdo->query("
        SELECT s.*, sc.name AS cat_name FROM services s
        LEFT JOIN service_categories sc ON s.category_id = sc.id
        WHERE s.status=1 ORDER BY s.is_popular DESC, s.rating DESC LIMIT 4
    ")->fetchAll();

    if (!empty($popularServices)) {
        $ids  = implode(',', array_map('intval', array_column($popularServices, 'id')));
        $tags = $pdo->query("SELECT * FROM service_tags WHERE service_id IN ($ids)")->fetchAll();
        foreach ($tags as $t) $allTags[$t['service_id']][] = $t['tag_name'];
    }

    $featProd = $pdo->query("
        SELECT p.*, pc.name AS cat_name FROM home_featured hf
        JOIN products p ON p.id = hf.item_id AND p.status = 1
        LEFT JOIN product_categories pc ON p.category_id = pc.id
        WHERE hf.item_type = 'product' AND hf.status = 1
        ORDER BY hf.sort_order ASC LIMIT 4
    ")->fetchAll();
    $topProducts = !empty($featProd) ? $featProd : $pdo->query("
        SELECT p.*, pc.name AS cat_name FROM products p
        LEFT JOIN product_categories pc ON p.category_id = pc.id
        WHERE p.status=1 ORDER BY p.rating DESC LIMIT 4
    ")->fetchAll();

    $featPlot = $pdo->query("
        SELECT pl.* FROM home_featured hf
        JOIN plots pl ON pl.id = hf.item_id AND pl.status = 'Available'
        WHERE hf.item_type = 'plot' AND hf.status = 1
        ORDER BY hf.sort_order ASC LIMIT 4
    ")->fetchAll();
    $featuredPlots = !empty($featPlot) ? $featPlot : $pdo->query("
        SELECT * FROM plots WHERE status='Available' ORDER BY created_at DESC LIMIT 4
    ")->fetchAll();

} catch (Exception $e) {}

// ── DEMO FALLBACKS ──────────────────────────────────────────
if (empty($popularServices)) {
    $popularServices = [
        ['id'=>1,'name'=>'RCC Structure Work','cat_name'=>'Civil','short_desc'=>'Complete RCC structure with certified engineers.','price_from'=>45,'price_unit'=>'sqft','rating'=>4.9,'is_popular'=>1,'icon'=>'🏗️'],
        ['id'=>2,'name'=>'False Ceiling + Cove Lighting','cat_name'=>'Interior','short_desc'=>'Gypsum/POP false ceiling with LED cove lighting.','price_from'=>65,'price_unit'=>'sqft','rating'=>4.8,'is_popular'=>1,'icon'=>'🛋️'],
        ['id'=>3,'name'=>'Full Home Painting','cat_name'=>'Painting','short_desc'=>'Premium Asian Paints — putty, primer, 2 coats.','price_from'=>12,'price_unit'=>'sqft','rating'=>4.7,'is_popular'=>0,'icon'=>'🎨'],
        ['id'=>4,'name'=>'Complete Electrical Work','cat_name'=>'Electrical','short_desc'=>'Full wiring, MCB panel by certified electricians.','price_from'=>18,'price_unit'=>'sqft','rating'=>4.7,'is_popular'=>0,'icon'=>'⚡'],
    ];
    $allTags = [1=>['Foundation','Slab'],2=>['Gypsum','LED'],3=>['Asian Paints'],4=>['MCB Panel']];
}
if (empty($topProducts)) {
    $topProducts = [
        ['id'=>1,'name'=>'BLDC Energy Saving Ceiling Fan','cat_name'=>'CEILING FAN','price'=>2800,'original_price'=>3500,'badge'=>'HOT','icon'=>'🌀','rating'=>4.5],
        ['id'=>2,'name'=>'18W Round LED Panel','cat_name'=>'LED LIGHTING','price'=>320,'original_price'=>450,'badge'=>'NEW','icon'=>'💡','rating'=>4],
        ['id'=>3,'name'=>'1.5 Ton 5-Star Inverter AC','cat_name'=>'SPLIT AC','price'=>42000,'original_price'=>52000,'badge'=>'SALE','icon'=>'❄️','rating'=>5],
        ['id'=>4,'name'=>'Premium Switch Board + USB','cat_name'=>'MODULAR SWITCHES','price'=>850,'original_price'=>1200,'badge'=>'','icon'=>'🔌','rating'=>4],
    ];
}
if (empty($featuredPlots)) {
    $featuredPlots = [
        ['id'=>1,'title'=>'Residential Plot — Sector 45, Noida','city'=>'Noida','type'=>'Residential','size_sqft'=>1200,'price'=>4500000,'facing'=>'East','status'=>'Available'],
        ['id'=>2,'title'=>'Corner Plot — Dwarka, Delhi','city'=>'Delhi','type'=>'Residential','size_sqft'=>1800,'price'=>8200000,'facing'=>'Corner','status'=>'Available'],
        ['id'=>3,'title'=>'Commercial Plot — Golf Course Road','city'=>'Gurugram','type'=>'Commercial','size_sqft'=>2400,'price'=>12000000,'facing'=>'North','status'=>'Available'],
        ['id'=>4,'title'=>'Plot — Greater Noida West','city'=>'Greater Noida','type'=>'Residential','size_sqft'=>900,'price'=>2800000,'facing'=>'West','status'=>'Available'],
    ];
}
?>

<!-- ═══════════════════════════════════════════════
     HOMEPAGE STYLES — FULLY WHITE THEME
     All colors match logo: orange, blue, green, gold
     ═══════════════════════════════════════════════ -->
<style>
:root {
  --or: #e8560a; --or2: #c44b08; --or-lt: #fff5f0; --or-b: rgba(232,86,10,.18);
  --bl: #1565c0; --bl-lt: #f0f5ff; --bl-b: rgba(21,101,192,.18);
  --gr: #2e7d32; --gr-lt: #f0faf0; --gr-b: rgba(46,125,50,.18);
  --gd: #c9a84c; --gd-lt: #fffbf0; --gd-b: rgba(201,168,76,.25);
  --txt: #1a2332; --mid: #4a5568; --light: #718096; --hint: #a0aec0;
  --border: #e8edf5; --bg: #f5f7fb; --white: #ffffff;
  --shadow: 0 4px 20px rgba(26,35,50,.07);
  --shadow-h: 0 12px 36px rgba(26,35,50,.13);
  --radius: 16px;
}
@keyframes pulse    { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.75)} }
@keyframes fadeUp   { from{opacity:0;transform:translateY(18px)} to{opacity:1;transform:translateY(0)} }
@keyframes tagSlide { from{opacity:0;transform:translateX(-10px)} to{opacity:1;transform:translateX(0)} }

/* ══ HERO ════════════════════════════════════════ */
.ih-hero {
  background: #ffffff;
  padding: clamp(52px,7vw,88px) 20px 0;
  position: relative; overflow: hidden;
  border-bottom: 1.5px solid var(--border);
}
/* Soft radial accents top-right (orange) and bottom-left (blue) */
.ih-hero::before {
  content:''; position:absolute; top:-140px; right:-100px;
  width:520px; height:520px;
  background:radial-gradient(circle,rgba(232,86,10,.08) 0%,transparent 62%);
  pointer-events:none;
}
.ih-hero::after {
  content:''; position:absolute; bottom:40px; left:-80px;
  width:420px; height:420px;
  background:radial-gradient(circle,rgba(21,101,192,.07) 0%,transparent 62%);
  pointer-events:none;
}
.ih-hero-inner {
  max-width:860px; margin:0 auto; text-align:center;
  position:relative; z-index:2;
  animation: fadeUp .65s ease both;
}
/* Badge */
.ih-hero-badge {
  display:inline-flex; align-items:center; gap:9px;
  background:linear-gradient(135deg,#fff5f0,#fffbf0);
  border:1.5px solid rgba(232,86,10,.22);
  color:var(--or); font-size:.7rem; font-weight:800;
  letter-spacing:1.3px; padding:8px 22px; border-radius:40px;
  margin-bottom:22px; text-transform:uppercase;
  box-shadow:0 2px 10px rgba(232,86,10,.1);
}
.ih-hero-badge .dot {
  width:6px; height:6px; border-radius:50%; background:var(--or);
  animation:pulse 2s infinite; flex-shrink:0;
}
/* Headline */
.ih-hero-h1 {
  font-family:'Playfair Display',Georgia,serif;
  font-size:clamp(2.2rem,5.5vw,4rem);
  font-weight:900; color:var(--txt); line-height:1.1;
  margin:0 auto 16px; letter-spacing:-.5px;
}
.ih-hero-h1 .c-or { color:var(--or); position:relative; }
.ih-hero-h1 .c-or .uline { position:absolute; bottom:-5px; left:0; width:100%; height:8px; }
.ih-hero-h1 .c-bl { color:var(--bl); }
.ih-hero-sub {
  font-size:clamp(.9rem,1.6vw,1.1rem); color:var(--mid);
  line-height:1.8; max-width:580px; margin:0 auto 30px;
}
/* CTAs */
.ih-cta-wrap {
  display:flex; flex-direction:column; align-items:center;
  gap:10px; max-width:500px; margin:0 auto;
}
.btn-hp-main {
  display:flex; align-items:center; justify-content:center; gap:9px;
  background:linear-gradient(135deg,#f0a070,var(--or));
  color:#fff; font-weight:800; font-size:.93rem;
  padding:15px 28px; border-radius:13px; width:100%;
  border:none; cursor:pointer;
  box-shadow:0 5px 22px rgba(232,86,10,.32);
  transition:all .22s;
}
.btn-hp-main:hover { transform:translateY(-2px); box-shadow:0 9px 28px rgba(232,86,10,.42); }
.btn-hp-wa {
  flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
  background:#25d366; color:#fff; font-weight:700; font-size:.87rem;
  padding:13px 14px; border-radius:12px; text-decoration:none; transition:.2s;
}
.btn-hp-wa:hover { background:#20b858; color:#fff; transform:translateY(-1px); }
.btn-hp-call {
  flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
  background:var(--bl-lt); color:var(--bl); font-weight:700; font-size:.87rem;
  padding:13px 14px; border-radius:12px; text-decoration:none;
  border:1.5px solid var(--bl-b); transition:.2s;
}
.btn-hp-call:hover { background:#dbeafe; transform:translateY(-1px); }
.btn-hp-cart {
  flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
  background:var(--bg); color:var(--mid); font-weight:600; font-size:.84rem;
  padding:11px; border-radius:11px; text-decoration:none;
  border:1.5px solid var(--border); transition:.2s;
}
.btn-hp-cart:hover { border-color:var(--or); color:var(--or); }
.btn-hp-chk {
  flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
  background:var(--gd-lt); color:var(--gd); font-weight:700; font-size:.84rem;
  padding:11px; border-radius:11px; text-decoration:none;
  border:1.5px solid var(--gd-b); transition:.2s;
}
.btn-hp-chk:hover { background:#fef3c7; }
/* Stats bar */
.ih-stats-bar { border-top:1.5px solid var(--border); }
.ih-stats-grid {
  display:grid; grid-template-columns:repeat(4,1fr);
  max-width:1400px; margin:0 auto;
}
@media(max-width:575px){ .ih-stats-grid{ grid-template-columns:repeat(2,1fr); } }
.ih-stat {
  padding:22px 12px; text-align:center;
  border-right:1.5px solid var(--border);
  background:#fff; transition:background .18s;
}
.ih-stat:last-child { border-right:none; }
.ih-stat:hover { background:var(--bg); }
.ih-stat.s-or { border-top:3px solid var(--or); }
.ih-stat.s-bl { border-top:3px solid var(--bl); }
.ih-stat.s-gr { border-top:3px solid var(--gr); }
.ih-stat.s-gd { border-top:3px solid var(--gd); }
.ih-stat-n {
  font-family:'Playfair Display',serif;
  font-size:1.9rem; font-weight:900; line-height:1; margin-bottom:5px;
}
.ih-stat.s-or .ih-stat-n { color:var(--or); }
.ih-stat.s-bl .ih-stat-n { color:var(--bl); }
.ih-stat.s-gr .ih-stat-n { color:var(--gr); }
.ih-stat.s-gd .ih-stat-n { color:var(--gd); }
.ih-stat-t { font-size:.68rem; color:var(--hint); font-weight:600; }
.ih-stat-s { font-size:.58rem; color:var(--or); margin-top:2px; font-weight:700; }
/* Tags bar */
.ih-tags-bar {
  background:var(--bg); border-top:1.5px solid var(--border);
  overflow-x:auto; scrollbar-width:none;
}
.ih-tags-bar::-webkit-scrollbar { display:none; }
.ih-tags-inner {
  display:flex; gap:8px; padding:11px 20px;
  white-space:nowrap; width:max-content;
}
.ih-tag {
  display:inline-flex; align-items:center; gap:5px;
  background:#fff; border:1.5px solid var(--border);
  color:var(--mid); font-size:.74rem; font-weight:600;
  padding:7px 14px; border-radius:22px;
  text-decoration:none; flex-shrink:0; transition:all .18s;
  box-shadow:0 1px 3px rgba(26,35,50,.04);
}
.ih-tag:hover {
  background:var(--or-lt); border-color:rgba(232,86,10,.3);
  color:var(--or); transform:translateY(-1px);
}
/* ══ WHY STRIP ═══════════════════════════════════ */
.ih-why {
  background:var(--bg);
  border-bottom:1.5px solid var(--border);
  padding:44px 5%;
}
.ih-why-grid {
  display:grid; grid-template-columns:repeat(4,1fr);
  gap:14px; max-width:1400px; margin:0 auto;
}
@media(max-width:767px){ .ih-why-grid{ grid-template-columns:repeat(2,1fr); } }
.ih-why-card {
  background:#fff; border:1.5px solid var(--border);
  border-radius:var(--radius); padding:22px 18px; text-align:center;
  transition:all .22s; box-shadow:var(--shadow);
}
.ih-why-card:hover { box-shadow:var(--shadow-h); transform:translateY(-3px); }
.ih-why-ico {
  width:50px; height:50px; border-radius:13px;
  display:flex; align-items:center; justify-content:center;
  font-size:1.4rem; margin:0 auto 12px;
}
.ih-why-card:nth-child(1) .ih-why-ico { background:var(--or-lt); }
.ih-why-card:nth-child(2) .ih-why-ico { background:var(--bl-lt); }
.ih-why-card:nth-child(3) .ih-why-ico { background:var(--gr-lt); }
.ih-why-card:nth-child(4) .ih-why-ico { background:var(--gd-lt); }
.ih-why-title { font-weight:700; font-size:.9rem; color:var(--txt); margin-bottom:5px; }
.ih-why-sub   { font-size:.76rem; color:var(--light); line-height:1.55; }
/* ══ SHARED SECTION STYLES ═══════════════════════ */
.ih-section {
  max-width:1400px; margin:0 auto; padding:52px 5% 0;
}
@media(max-width:991px){ .ih-section{ padding:40px 4% 0; } }
.ih-sec-hd {
  display:flex; align-items:flex-end; justify-content:space-between;
  margin-bottom:22px; flex-wrap:wrap; gap:12px;
}
.ih-sec-label {
  display:inline-flex; align-items:center; gap:6px;
  font-size:.67rem; font-weight:800; letter-spacing:1.3px;
  text-transform:uppercase; padding:5px 13px; border-radius:28px;
  margin-bottom:7px;
}
.lbl-or { background:var(--or-lt); color:var(--or); border:1px solid var(--or-b); }
.lbl-bl { background:var(--bl-lt); color:var(--bl); border:1px solid var(--bl-b); }
.lbl-gr { background:var(--gr-lt); color:var(--gr); border:1px solid var(--gr-b); }
.lbl-gd { background:var(--gd-lt); color:var(--gd); border:1px solid var(--gd-b); }
.ih-sec-title {
  font-family:'Playfair Display',Georgia,serif;
  font-size:clamp(1.3rem,2.5vw,1.8rem);
  font-weight:800; color:var(--txt); line-height:1.2; margin:0;
}
.ih-sec-sub { color:var(--light); font-size:.85rem; margin-top:4px; }
.btn-va {
  display:flex; align-items:center; gap:6px;
  font-weight:700; font-size:.82rem; padding:10px 18px;
  border-radius:11px; text-decoration:none; transition:all .2s;
  border:1.5px solid; white-space:nowrap; cursor:pointer;
}
.va-or { background:var(--or-lt); color:var(--or); border-color:var(--or-b); }
.va-or:hover { background:var(--or); color:#fff; box-shadow:0 4px 14px rgba(232,86,10,.3); }
.va-bl { background:var(--bl-lt); color:var(--bl); border-color:var(--bl-b); }
.va-bl:hover { background:var(--bl); color:#fff; }
.va-gr { background:var(--gr-lt); color:var(--gr); border-color:var(--gr-b); }
.va-gr:hover { background:var(--gr); color:#fff; }
.va-gd { background:var(--gd-lt); color:var(--gd); border-color:var(--gd-b); }
.va-gd:hover { background:var(--gd); color:#fff; }
/* Cards grid */
.ih-grid {
  display:grid; grid-template-columns:repeat(2,1fr); gap:14px;
}
@media(min-width:768px)  { .ih-grid{ grid-template-columns:repeat(3,1fr); gap:16px; } }
@media(min-width:1200px) { .ih-grid{ grid-template-columns:repeat(4,1fr); } }
/* ══ SERVICE CARD ════════════════════════════════ */
.svc-card {
  background:#fff; border:1.5px solid var(--border);
  border-radius:var(--radius); overflow:hidden;
  display:flex; flex-direction:column; transition:all .22s;
  box-shadow:var(--shadow);
}
.svc-card:hover { box-shadow:var(--shadow-h); transform:translateY(-4px); border-color:var(--or-b); }
.svc-img {
  height:112px;
  background:linear-gradient(135deg,#fff5f0,#fff8f5);
  display:flex; align-items:center; justify-content:center;
  position:relative; border-bottom:1px solid var(--border);
}
.svc-ico { font-size:2.7rem; filter:drop-shadow(0 2px 6px rgba(232,86,10,.15)); }
.svc-pop {
  position:absolute; top:9px; left:9px;
  background:linear-gradient(135deg,#fff5f0,#ffe4d4);
  color:var(--or); font-size:.56rem; font-weight:800;
  padding:3px 9px; border-radius:18px; border:1px solid rgba(232,86,10,.2);
}
.svc-body { padding:14px; display:flex; flex-direction:column; flex:1; }
.svc-cat { font-size:.6rem; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:var(--or); margin-bottom:4px; }
.svc-name { font-weight:700; font-size:.9rem; color:var(--txt); line-height:1.3; margin-bottom:6px; }
.svc-desc { font-size:.76rem; color:var(--light); line-height:1.5; margin-bottom:10px; }
.svc-price { font-size:.8rem; color:var(--light); margin-bottom:13px; margin-top:auto; }
.svc-price strong { color:var(--txt); font-weight:800; font-size:.97rem; }
.svc-btn {
  display:flex; align-items:center; justify-content:center; gap:5px;
  background:linear-gradient(135deg,#f0a070,var(--or)); color:#fff;
  font-weight:700; font-size:.78rem; padding:10px; border-radius:10px;
  border:none; cursor:pointer; width:100%; transition:all .2s;
  box-shadow:0 3px 10px rgba(232,86,10,.22);
}
.svc-btn:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(232,86,10,.34); }
/* ══ PRODUCT CARD ════════════════════════════════ */
.prd-card {
  background:#fff; border:1.5px solid var(--border);
  border-radius:var(--radius); overflow:hidden;
  display:flex; flex-direction:column; transition:all .22s;
  box-shadow:var(--shadow);
}
.prd-card:hover { box-shadow:var(--shadow-h); transform:translateY(-4px); border-color:var(--bl-b); }
.prd-img {
  height:130px;
  background:linear-gradient(135deg,var(--bl-lt),#f5f8ff);
  display:flex; align-items:center; justify-content:center;
  position:relative; border-bottom:1px solid var(--border); font-size:2.9rem;
}
.prd-img img { width:100%;height:100%;object-fit:contain;padding:10px; }
.prd-bdg {
  position:absolute; top:9px; left:9px;
  font-size:.56rem; font-weight:800; padding:3px 9px; border-radius:18px;
}
.bdg-hot  { background:#fff0f0; color:#dc2626; border:1px solid #fecaca; }
.bdg-new  { background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; }
.bdg-sale { background:#fffbeb; color:#d97706; border:1px solid #fde68a; }
.prd-body { padding:13px 14px 14px; display:flex; flex-direction:column; flex:1; }
.prd-cat  { font-size:.6rem; font-weight:800; letter-spacing:1px; text-transform:uppercase; color:var(--bl); margin-bottom:4px; }
.prd-name { font-weight:700; font-size:.87rem; color:var(--txt); line-height:1.3; margin-bottom:6px; }
.prd-stars{ color:#f59e0b; font-size:.76rem; margin-bottom:7px; }
.prd-price{ font-weight:800; font-size:1rem; color:var(--txt); }
.prd-orig { text-decoration:line-through; color:var(--hint); font-size:.8rem; margin-left:4px; }
.prd-off  { color:#16a34a; font-weight:700; font-size:.73rem; margin-left:4px; }
.prd-btns { display:flex; gap:8px; margin-top:11px; }
.prd-add {
  flex:1; background:var(--bl-lt); color:var(--bl);
  border:1.5px solid var(--bl-b); font-weight:700; font-size:.76rem;
  padding:8px; border-radius:9px; cursor:pointer; transition:.18s;
}
.prd-add:hover { background:var(--bl); color:#fff; }
.prd-buy {
  flex:1; background:linear-gradient(135deg,var(--bl),#0d47a1); color:#fff;
  font-weight:700; font-size:.76rem; padding:8px; border-radius:9px;
  border:none; cursor:pointer; transition:.18s;
  box-shadow:0 3px 10px rgba(21,101,192,.28);
}
.prd-buy:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(21,101,192,.38); }
/* ══ PLOT CARD ═══════════════════════════════════ */
.plt-card {
  background:#fff; border:1.5px solid var(--border);
  border-radius:var(--radius); overflow:hidden;
  display:flex; flex-direction:column; transition:all .22s;
  box-shadow:var(--shadow); text-decoration:none;
}
.plt-card:hover { box-shadow:var(--shadow-h); transform:translateY(-4px); border-color:var(--gr-b); }
.plt-img {
  height:120px;
  background:linear-gradient(135deg,var(--gr-lt),#f5fbf5);
  display:flex; align-items:center; justify-content:center;
  position:relative; border-bottom:1px solid var(--border); overflow:hidden; font-size:2.5rem;
}
.plt-img img { width:100%;height:100%;object-fit:cover; }
.plt-avail {
  position:absolute; bottom:8px; right:8px;
  background:#f0fdf4; color:#16a34a; font-size:.58rem; font-weight:800;
  padding:3px 9px; border-radius:18px; border:1px solid #bbf7d0;
}
.plt-body { padding:13px 14px 14px; display:flex; flex-direction:column; flex:1; }
.plt-bdgs { display:flex; gap:5px; margin-bottom:7px; flex-wrap:wrap; }
.plt-type    { font-size:.6rem;font-weight:800;padding:3px 8px;border-radius:18px;background:var(--gr-lt);color:var(--gr);border:1px solid var(--gr-b); }
.plt-facing  { font-size:.6rem;font-weight:700;padding:3px 8px;border-radius:18px;background:var(--bl-lt);color:var(--bl);border:1px solid var(--bl-b); }
.plt-title   { font-weight:700;font-size:.88rem;color:var(--txt);line-height:1.3;margin-bottom:4px; }
.plt-loc     { font-size:.76rem;color:var(--light);margin-bottom:8px; }
.plt-price   { font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:800;color:var(--gr);margin-bottom:2px; }
.plt-psf     { font-size:.7rem;color:var(--hint);margin-bottom:11px; }
.plt-btn {
  display:flex; align-items:center; justify-content:center; gap:5px;
  background:linear-gradient(135deg,var(--gr),#1b5e20); color:#fff;
  font-weight:700; font-size:.78rem; padding:10px; border-radius:10px;
  border:none; cursor:pointer; width:100%; transition:.2s;
  box-shadow:0 3px 10px rgba(46,125,50,.22);
}
.plt-btn:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(46,125,50,.34); }
/* ══ BLOG CARD ═══════════════════════════════════ */
.blg-card {
  background:#fff; border:1.5px solid var(--border);
  border-radius:var(--radius); overflow:hidden;
  display:flex; flex-direction:column; transition:all .22s;
  box-shadow:var(--shadow); text-decoration:none;
}
.blg-card:hover { box-shadow:var(--shadow-h); transform:translateY(-4px); border-color:var(--gd-b); }
.blg-img {
  height:118px;
  background:linear-gradient(135deg,var(--gd-lt),#fff8e0);
  display:flex; align-items:center; justify-content:center;
  position:relative; border-bottom:1px solid var(--border);
  overflow:hidden; font-size:2.7rem;
}
.blg-img img { width:100%;height:100%;object-fit:cover; }
.blg-rt {
  position:absolute; bottom:8px; right:8px;
  background:rgba(255,255,255,.9); color:var(--mid); font-size:.6rem; font-weight:700;
  padding:3px 9px; border-radius:18px; border:1px solid var(--border);
}
.blg-body { padding:13px 14px 14px; display:flex; flex-direction:column; flex:1; }
.blg-cat  { display:inline-block;font-size:.6rem;font-weight:800;padding:3px 9px;border-radius:18px;text-transform:uppercase;margin-bottom:7px; }
.blg-title   { font-weight:700;font-size:.9rem;color:var(--txt);line-height:1.4;margin-bottom:6px; }
.blg-excerpt { font-size:.76rem;color:var(--light);line-height:1.6;margin-bottom:10px; }
.blg-read    { margin-top:auto;font-size:.8rem;font-weight:700;color:var(--gd);display:flex;align-items:center;gap:4px; }
/* ══ FOOTER DIVIDER LINE ═════════════════════════ */
.ih-footer-divider {
  margin-top:60px;
  border:none;
  border-top:2px solid var(--border);
  position:relative;
}
.ih-footer-divider::before {
  content:'— Footer starts here —';
  position:absolute; top:-11px; left:50%; transform:translateX(-50%);
  background:var(--bg); color:var(--hint);
  font-size:.68rem; font-weight:700; letter-spacing:1px;
  padding:0 16px; white-space:nowrap;
}
</style>

<!-- ════ HERO ════════════════════════════════════════════════ -->
<section class="ih-hero">
  <div class="ih-hero-inner">

    <div class="ih-hero-badge">
      <span class="dot"></span>
      Trusted Since 1975 &nbsp;|&nbsp; 50 Years of Excellence
    </div>

    <h1 class="ih-hero-h1">
      Building <span class="c-or">Dreams
        <svg class="uline" viewBox="0 0 200 8" preserveAspectRatio="none">
          <path d="M0,6 Q50,0 100,5 Q150,10 200,4" stroke="#e8560a" stroke-width="2.5" fill="none" opacity=".45"/>
        </svg>
      </span><br>
      Since <span class="c-bl">1975</span>
    </h1>

    <p class="ih-hero-sub">
      Delhi NCR's most experienced construction company — civil, interior, electrical, plumbing, and
      <strong style="color:var(--txt);">350+ services</strong> delivered with precision.
    </p>

    <div class="ih-cta-wrap">
      <button class="btn-hp-main"
              onclick="openEnquiryPopup('Free Site Visit','homepage','Our expert will visit your site for FREE!')">
        <i class="bi bi-calendar-check-fill"></i>
        Book Free Site Visit
      </button>
      <div style="display:flex;gap:10px;width:100%;">
        <a href="https://wa.me/<?= defined('SITE_WHATSAPP') ? SITE_WHATSAPP : (defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '') ?>?text=Hi, I need a free consultation"
           target="_blank" class="btn-hp-wa">
          <i class="bi bi-whatsapp"></i> WhatsApp Us
        </a>
        <a href="tel:<?= defined('SITE_PHONE') ? SITE_PHONE : '' ?>" class="btn-hp-call">
          <i class="bi bi-telephone-fill"></i> Call Now
        </a>
      </div>
      <div style="display:flex;gap:10px;width:100%;">
        <a href="/cart.php"     class="btn-hp-cart"><i class="bi bi-cart3"></i> My Cart</a>
        <a href="/checkout.php" class="btn-hp-chk"> <i class="bi bi-bag-check-fill"></i> Checkout</a>
      </div>
    </div>
  </div>

  <!-- Stats bar -->
  <div class="ih-stats-bar" style="margin-top:44px;">
    <div class="ih-stats-grid">
      <div class="ih-stat s-or"><div class="ih-stat-n">50</div><div class="ih-stat-t">Years Experience</div><div class="ih-stat-s">Est. 1975</div></div>
      <div class="ih-stat s-bl"><div class="ih-stat-n">5,000+</div><div class="ih-stat-t">Projects Delivered</div></div>
      <div class="ih-stat s-gr"><div class="ih-stat-n">98%</div><div class="ih-stat-t">Client Satisfaction</div></div>
      <div class="ih-stat s-gd"><div class="ih-stat-n">200+</div><div class="ih-stat-t">Expert Professionals</div></div>
    </div>
  </div>

  <!-- Tags scroll -->
  <div class="ih-tags-bar">
    <div class="ih-tags-inner">
      <?php
      $_catLinks = [
        ['🏗','Civil',        '/services.php?cat=civil'],
        ['🛋','Interior',     '/services.php?cat=interior'],
        ['⚡','Electrical',   '/services.php?cat=electrical'],
        ['🔧','Plumbing',     '/services.php?cat=plumbing'],
        ['❄️','AC Services',  '/services.php?cat=ac-hvac'],
        ['🎨','Painting',     '/services.php?cat=painting'],
        ['🏠','Flooring',     '/services.php?cat=flooring'],
        ['🪵','Carpentry',    '/services.php?cat=carpentry'],
        ['🌿','Landscaping',  '/services.php?cat=outdoor'],
        ['📍','Plots',        '/plots.php'],
        ['🔨','Renovation',   '/services.php?cat=renovation'],
        ['🏢','Commercial',   '/services.php?cat=civil'],
        ['🚿','Bathroom',     '/services.php?cat=plumbing'],
        ['🪟','Windows',      '/services.php?cat=carpentry'],
      ];
      foreach($_catLinks as [$icon,$label,$href]): ?>
      <a href="<?= $href ?>" class="ih-tag"><?= $icon ?>&nbsp;<?= $label ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ════ WHY CHOOSE US ═══════════════════════════════════════ -->
<div class="ih-why">
  <div style="max-width:1400px;margin:0 auto;">
    <div style="text-align:center;margin-bottom:28px;">
      <div class="ih-sec-label lbl-or" style="margin:0 auto 10px;">Our Promise</div>
      <div class="ih-sec-title">Why Choose Incredible Heights?</div>
    </div>
    <div class="ih-why-grid">
      <div class="ih-why-card"><div class="ih-why-ico">🏆</div><div class="ih-why-title">50 Years of Trust</div><div class="ih-why-sub">Serving Delhi NCR since 1975 with 5,000+ completed projects</div></div>
      <div class="ih-why-card"><div class="ih-why-ico">🛡️</div><div class="ih-why-title">Quality Guaranteed</div><div class="ih-why-sub">ISO certified work with warranty on every project we deliver</div></div>
      <div class="ih-why-card"><div class="ih-why-ico">💰</div><div class="ih-why-title">Transparent Pricing</div><div class="ih-why-sub">No hidden charges — fixed rates shared before work starts</div></div>
      <div class="ih-why-card"><div class="ih-why-ico">⚡</div><div class="ih-why-title">Fast Turnaround</div><div class="ih-why-sub">Team arrives within 24 hrs, project completed on schedule</div></div>
    </div>
  </div>
</div>

<!-- ════ SERVICES ════════════════════════════════════════════ -->
<div class="ih-section">
  <div class="ih-sec-hd">
    <div>
      <div class="ih-sec-label lbl-or">350+ Services</div>
      <div class="ih-sec-title">Our Services</div>
      <div class="ih-sec-sub">Professional construction & interior services across Delhi NCR</div>
    </div>
    <a href="/services.php" class="btn-va va-or"><i class="bi bi-grid-fill"></i> View All Services</a>
  </div>
  <div class="ih-grid">
    <?php foreach(array_slice($popularServices, 0, 4) as $svc): ?>
    <div class="svc-card">
      <div class="svc-img">
        <div class="svc-ico"><?= htmlspecialchars($svc['icon'] ?? '🔧') ?></div>
        <?php if(!empty($svc['is_popular'])): ?><span class="svc-pop">⭐ Popular</span><?php endif; ?>
      </div>
      <div class="svc-body">
        <div class="svc-cat"><?= htmlspecialchars($svc['cat_name'] ?? '') ?></div>
        <div class="svc-name"><?= htmlspecialchars($svc['name']) ?></div>
        <div class="svc-desc"><?= htmlspecialchars(substr($svc['short_desc'] ?? '', 0, 72)) ?></div>
        <div class="svc-price">From <strong>₹<?= number_format($svc['price_from'] ?? 0) ?></strong>/<?= htmlspecialchars($svc['price_unit'] ?? 'unit') ?></div>
        <a href="/service-detail.php?id=<?= (int)$svc['id'] ?>" class="svc-btn">
          <i class="bi bi-clipboard-check"></i> View Details
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ════ PRODUCTS ════════════════════════════════════════════ -->
<div class="ih-section" style="margin-top:52px;">
  <div class="ih-sec-hd">
    <div>
      <div class="ih-sec-label lbl-bl">Top Brands</div>
      <div class="ih-sec-title">Our Products</div>
      <div class="ih-sec-sub">Quality electrical & construction products at best prices</div>
    </div>
    <a href="/products.php" class="btn-va va-bl"><i class="bi bi-bag-fill"></i> View All Products</a>
  </div>
  <div class="ih-grid">
    <?php foreach(array_slice($topProducts, 0, 4) as $p):
      $badge = strtolower($p['badge'] ?? '');
      $disc  = (!empty($p['original_price']) && $p['original_price'] > $p['price'])
               ? round((1 - $p['price']/$p['original_price']) * 100) : 0;
      $stars = min(5, max(1, round($p['rating'] ?? 4)));
    ?>
    <div class="prd-card">
      <div class="prd-img">
        <?php if(!empty($p['image'])): ?>
          <img src="/uploads/products/<?= htmlspecialchars($p['image']) ?>"
               alt="<?= htmlspecialchars($p['name']) ?>">
        <?php else: ?>
          <?= htmlspecialchars($p['icon'] ?? '📦') ?>
        <?php endif; ?>
        <?php if(!empty($p['badge'])): ?>
          <span class="prd-bdg bdg-<?= $badge ?>"><?= strtoupper($p['badge']) ?></span>
        <?php endif; ?>
      </div>
      <div class="prd-body">
        <div class="prd-cat"><?= htmlspecialchars($p['cat_name'] ?? '') ?></div>
        <div class="prd-name"><?= htmlspecialchars($p['name']) ?></div>
        <div class="prd-stars">
          <?= str_repeat('★', $stars) ?><?= str_repeat('☆', 5-$stars) ?>
          <span style="color:var(--hint);font-size:.7rem;margin-left:3px;">(<?= $p['rating'] ?>)</span>
        </div>
        <div>
          <span class="prd-price">₹<?= number_format($p['price']) ?></span>
          <?php if($disc > 0): ?>
            <span class="prd-orig">₹<?= number_format($p['original_price']) ?></span>
            <span class="prd-off"><?= $disc ?>% OFF</span>
          <?php endif; ?>
        </div>
        <div class="prd-btns">
          <button class="prd-add" onclick="addToCart('product',<?= (int)$p['id'] ?>,1,<?= (float)$p['price'] ?>)">
            <i class="bi bi-cart-plus"></i> Add
          </button>
          <button class="prd-buy" onclick="buyNow('product',<?= (int)$p['id'] ?>,1,<?= (float)$p['price'] ?>)">
            <i class="bi bi-lightning-charge-fill"></i> Buy
          </button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ════ PLOTS ═══════════════════════════════════════════════ -->
<div class="ih-section" style="margin-top:52px;">
  <div class="ih-sec-hd">
    <div>
      <div class="ih-sec-label lbl-gr">Delhi NCR Real Estate</div>
      <div class="ih-sec-title">Plots for Sale</div>
      <div class="ih-sec-sub">Verified residential & commercial plots at prime locations</div>
    </div>
    <a href="/plots.php" class="btn-va va-gr"><i class="bi bi-map-fill"></i> View All Plots</a>
  </div>
  <div class="ih-grid">
    <?php foreach(array_slice($featuredPlots, 0, 4) as $pl): ?>
    <div class="plt-card" onclick="window.location.href='/plot-detail.php?id=<?= (int)$pl['id'] ?>';" style="cursor:pointer;">
      <div class="plt-img">
        <?php
          $plotImgs = !empty($pl['images']) ? json_decode($pl['images'], true) : [];
          if(!empty($plotImgs) && is_array($plotImgs)): ?>
          <img src="/uploads/plots/<?= htmlspecialchars($plotImgs[0]) ?>" alt="Plot">
        <?php else: ?>
          🏞️
        <?php endif; ?>
        <span class="plt-avail">✓ Available</span>
      </div>
      <div class="plt-body">
        <div class="plt-bdgs">
          <span class="plt-type"><?= htmlspecialchars($pl['type']) ?></span>
          <span class="plt-facing"><?= htmlspecialchars($pl['facing']) ?> Facing</span>
        </div>
        <div class="plt-title"><?= htmlspecialchars($pl['title']) ?></div>
        <div class="plt-loc"><i class="bi bi-geo-alt-fill" style="color:var(--gr);font-size:.72rem;"></i> <?= htmlspecialchars($pl['city']) ?></div>
        <div class="plt-price">₹<?= number_format($pl['price']) ?></div>
        <div class="plt-psf"><?= number_format($pl['size_sqft']) ?> sqft &bull; ₹<?= number_format($pl['price']/$pl['size_sqft']) ?>/sqft</div>
        <button class="plt-btn"
                onclick="event.stopPropagation();openEnquiryPopup('Plot: <?= addslashes(htmlspecialchars($pl['title'])) ?>','plot','Our property expert will call you!')">
          <i class="bi bi-telephone-fill"></i> Enquire Now
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ════ BLOG ════════════════════════════════════════════════ -->
<?php
$latestBlogs = [];
try {
    $latestBlogs = $pdo->query("
        SELECT b.*, bc.name AS cat_name, bc.color AS cat_color
        FROM blogs b LEFT JOIN blog_categories bc ON b.category_id = bc.id
        WHERE b.status = 1 ORDER BY b.created_at DESC LIMIT 4
    ")->fetchAll();
} catch(Exception $e) {}
if (empty($latestBlogs)) {
    $latestBlogs = [
        ['id'=>1,'title'=>'How to Choose the Right Flooring','excerpt'=>'Complete guide to marble, tiles, wood or vinyl — based on budget and lifestyle.','cat_name'=>'Interior','cat_color'=>'#1565c0','slug'=>'choose-right-flooring','read_time'=>5],
        ['id'=>2,'title'=>'RCC vs AAC Blocks: Expert Comparison','excerpt'=>'Traditional RCC vs modern AAC blocks — which is better for Delhi NCR homes?','cat_name'=>'Civil','cat_color'=>'#e8560a','slug'=>'rcc-vs-aac-blocks','read_time'=>7],
        ['id'=>3,'title'=>'Top 10 Renovation Mistakes to Avoid','excerpt'=>'Common mistakes that cost homeowners lakhs — and how to avoid them easily.','cat_name'=>'Tips','cat_color'=>'#2e7d32','slug'=>'renovation-mistakes','read_time'=>6],
        ['id'=>4,'title'=>'5 Interior Trends for 2025','excerpt'=>'From japandi to biophilic design — trends transforming Delhi NCR homes this year.','cat_name'=>'Guide','cat_color'=>'#c9a84c','slug'=>'interior-trends-2025','read_time'=>4],
    ];
}
?>
<div class="ih-section" style="margin-top:52px;padding-bottom:60px;">
  <div class="ih-sec-hd">
    <div>
      <div class="ih-sec-label lbl-gd">Tips &amp; Guides</div>
      <div class="ih-sec-title">Expert Blog</div>
      <div class="ih-sec-sub">Construction tips, interior ideas and real estate guides</div>
    </div>
    <a href="/blog.php" class="btn-va va-gd"><i class="bi bi-journal-richtext"></i> All Articles</a>
  </div>
  <div class="ih-grid">
    <?php foreach($latestBlogs as $bl):
      $catColor = $bl['cat_color'] ?? '#c9a84c';
      $slug = $bl['slug'] ?? $bl['id'];
    ?>
    <a href="/blog-post.php?slug=<?= urlencode($slug) ?>" class="blg-card">
      <div class="blg-img">
        <?php if(!empty($bl['image'])): ?>
          <img src="/uploads/blogs/<?= htmlspecialchars($bl['image']) ?>" alt="">
        <?php else: ?>
          📝
        <?php endif; ?>
        <?php if(!empty($bl['read_time'])): ?>
          <span class="blg-rt">⏱ <?= (int)$bl['read_time'] ?> min</span>
        <?php endif; ?>
      </div>
      <div class="blg-body">
        <span class="blg-cat" style="background:<?= $catColor ?>18;color:<?= $catColor ?>;border:1px solid <?= $catColor ?>35;">
          <?= htmlspecialchars($bl['cat_name'] ?? 'Blog') ?>
        </span>
        <div class="blg-title"><?= htmlspecialchars($bl['title']) ?></div>
        <div class="blg-excerpt"><?= htmlspecialchars(substr($bl['excerpt'] ?? '', 0, 88)) ?>...</div>
        <div class="blg-read">Read Article <i class="bi bi-arrow-right"></i></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- ════ FOOTER DIVIDER ══════════════════════════════════════ -->
<hr class="ih-footer-divider">

<script>
function addToCart(type, id, qty, price) {
  fetch('/api/cart-add.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({item_type:type,item_id:id,quantity:qty,price:price,csrf:'<?= generateCSRF() ?>'})
  }).then(r=>r.json()).then(d=>{
    if(d.success){ showToast('Added to cart!','success'); document.querySelectorAll('.cart-badge').forEach(el=>el.textContent=d.cart_count); }
    else showToast(d.message||'Error adding to cart','error');
  }).catch(()=>showToast('Added to cart!','success'));
}
function buyNow(type, id, qty, price) {
  fetch('/api/cart-add.php', {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({item_type:type,item_id:id,quantity:qty,price:price,csrf:'<?= generateCSRF() ?>'})
  }).then(()=>{ window.location.href='/cart.php'; }).catch(()=>{ window.location.href='/cart.php'; });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>