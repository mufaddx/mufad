<?php
require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
$pageTitle = '404 — Page Not Found | Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
/* ═══════════════════════════════════════════
   404 PAGE — INCREDIBLE HEIGHTS
   Professional Light Design with Animations
   ═══════════════════════════════════════════ */

@keyframes floatUp {
  0%   { opacity:0; transform: translateY(40px); }
  100% { opacity:1; transform: translateY(0); }
}
@keyframes floatBob {
  0%, 100% { transform: translateY(0px) rotate(-1deg); }
  50%       { transform: translateY(-18px) rotate(1deg); }
}
@keyframes pulseGlow {
  0%, 100% { box-shadow: 0 0 0 0 rgba(201,168,76,.0); }
  50%       { box-shadow: 0 0 0 22px rgba(201,168,76,.08); }
}
@keyframes spinSlow {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
@keyframes shimmer {
  0%   { background-position: -400px 0; }
  100% { background-position: 400px 0; }
}
@keyframes countUp {
  from { opacity:0; transform:scale(.6); }
  to   { opacity:1; transform:scale(1); }
}

/* ── WRAPPER ── */
.err-wrap {
  min-height: 88vh;
  background: linear-gradient(140deg, #ffffff 0%, #f0f6ff 45%, #fff9ee 100%);
  display: flex; align-items: center; justify-content: center;
  padding: 60px 20px;
  position: relative; overflow: hidden;
}

/* decorative blobs */
.err-blob {
  position: absolute; border-radius: 50%;
  pointer-events: none; filter: blur(60px);
}
.err-blob-1 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(201,168,76,.12) 0%, transparent 70%);
  top: -120px; right: -120px;
}
.err-blob-2 {
  width: 380px; height: 380px;
  background: radial-gradient(circle, rgba(12,30,53,.06) 0%, transparent 70%);
  bottom: -80px; left: -80px;
}
.err-blob-3 {
  width: 220px; height: 220px;
  background: radial-gradient(circle, rgba(201,168,76,.08) 0%, transparent 70%);
  top: 40%; left: 10%;
}

/* floating dots background */
.err-dots {
  position: absolute; inset: 0;
  background-image: radial-gradient(circle, rgba(10,22,40,.045) 1.5px, transparent 1.5px);
  background-size: 32px 32px;
  pointer-events: none;
}

/* ── MAIN CONTENT ── */
.err-inner {
  position: relative; z-index: 2;
  text-align: center; max-width: 680px; width: 100%;
}

/* big 404 graphic */
.err-graphic {
  position: relative; display: inline-block;
  margin-bottom: 40px;
  animation: floatUp .7s cubic-bezier(.16,1,.3,1) both;
}

.err-num {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(7rem, 20vw, 13rem);
  font-weight: 900;
  line-height: .95;
  letter-spacing: -4px;
  background: linear-gradient(135deg, #0c1e35 0%, #1a3460 40%, #c9a84c 70%, #e8c96d 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  position: relative; display: block;
  animation: countUp .6s cubic-bezier(.34,1.56,.64,1) .2s both;
}

/* shimmer over the 404 */
.err-num::after {
  content: '404';
  position: absolute; inset: 0;
  background: linear-gradient(90deg, transparent 30%, rgba(255,255,255,.55) 50%, transparent 70%);
  background-size: 400px 100%;
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: shimmer 3s ease-in-out 1.2s infinite;
}

/* no floating icon */

/* ── TEXT ── */
.err-badge {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(201,168,76,.10);
  border: 1.5px solid rgba(201,168,76,.28);
  color: #b08010; font-size: .70rem; font-weight: 800;
  letter-spacing: 1.5px; text-transform: uppercase;
  padding: 6px 18px; border-radius: 40px; margin-bottom: 18px;
  animation: floatUp .6s cubic-bezier(.16,1,.3,1) .4s both;
}

.err-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(1.5rem, 3.5vw, 2.2rem);
  font-weight: 900; color: #0c1e35;
  line-height: 1.25; margin-bottom: 14px;
  animation: floatUp .6s cubic-bezier(.16,1,.3,1) .5s both;
}

.err-desc {
  color: #5a6e85; font-size: .97rem; line-height: 1.7;
  max-width: 420px; margin: 0 auto 32px;
  animation: floatUp .6s cubic-bezier(.16,1,.3,1) .6s both;
}

/* ── SEARCH ── */
.err-search {
  max-width: 420px; margin: 0 auto 36px;
  position: relative;
  animation: floatUp .6s cubic-bezier(.16,1,.3,1) .65s both;
}
.err-search input {
  width: 100%; padding: 14px 52px 14px 20px;
  border: 1.5px solid #dde5f0;
  border-radius: 14px; font-size: .9rem;
  font-family: 'DM Sans', sans-serif;
  color: #0c1e35; outline: none;
  background: #fff;
  box-shadow: 0 4px 16px rgba(10,22,40,.07);
  transition: border-color .18s, box-shadow .18s;
}
.err-search input:focus {
  border-color: var(--gold);
  box-shadow: 0 4px 16px rgba(10,22,40,.07), 0 0 0 3px rgba(201,168,76,.15);
}
.err-search input::placeholder { color: #aab4c2; }
.err-search-btn {
  position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
  width: 38px; height: 38px; border-radius: 10px;
  background: linear-gradient(135deg, #e8c96d, var(--gold));
  border: none; cursor: pointer; display: flex; align-items: center;
  justify-content: center; color: #0c1e35;
  font-size: .9rem; transition: transform .18s, box-shadow .18s;
}
.err-search-btn:hover {
  transform: translateY(-50%) scale(1.08);
  box-shadow: 0 4px 14px rgba(201,168,76,.4);
}

/* ── ACTION BUTTONS ── */
.err-actions {
  display: flex; flex-wrap: wrap; gap: 12px;
  justify-content: center;
  animation: floatUp .6s cubic-bezier(.16,1,.3,1) .75s both;
}
.err-btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 12px 24px; border-radius: 13px;
  font-weight: 700; font-size: .87rem;
  text-decoration: none; transition: all .2s ease;
  white-space: nowrap;
}
.err-btn-gold {
  background: linear-gradient(135deg, #e8c96d, var(--gold));
  color: #0c1e35;
  box-shadow: 0 6px 20px rgba(201,168,76,.35);
}
.err-btn-gold:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(201,168,76,.45);
  color: #0c1e35;
}
.err-btn-dark {
  background: #0c1e35; color: #fff;
  box-shadow: 0 6px 20px rgba(10,22,40,.18);
}
.err-btn-dark:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 28px rgba(10,22,40,.28);
  background: #1a3460; color: #fff;
}
.err-btn-outline {
  background: #fff; color: #5a6e85;
  border: 1.5px solid #dde5f0;
  box-shadow: 0 3px 12px rgba(10,22,40,.07);
}
.err-btn-outline:hover {
  transform: translateY(-2px);
  border-color: var(--gold); color: #b08010;
  box-shadow: 0 6px 18px rgba(10,22,40,.1);
}
.err-btn-green {
  background: #25d366; color: #fff;
  box-shadow: 0 6px 20px rgba(37,211,102,.3);
}
.err-btn-green:hover {
  transform: translateY(-2px);
  background: #20c15e; color: #fff;
  box-shadow: 0 10px 28px rgba(37,211,102,.4);
}

/* ── QUICK LINKS ── */
.err-quick {
  margin-top: 48px; padding-top: 36px;
  border-top: 1.5px solid #e8edf5;
  animation: floatUp .6s cubic-bezier(.16,1,.3,1) .85s both;
}
.err-quick-label {
  font-size: .70rem; font-weight: 800; color: #aab4c2;
  text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 18px;
}
.err-quick-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
  gap: 10px;
}
.err-qlink {
  background: #fff; border: 1.5px solid #e8edf5;
  border-radius: 13px; padding: 16px 14px;
  text-align: center; text-decoration: none;
  transition: all .2s ease;
}
.err-qlink:hover {
  border-color: rgba(201,168,76,.45);
  box-shadow: 0 8px 24px rgba(10,22,40,.10);
  transform: translateY(-3px);
}
.err-qlink .ql-icon { font-size: 1.5rem; margin-bottom: 7px; }
.err-qlink .ql-name {
  font-size: .76rem; font-weight: 700; color: #0c1e35;
}

@media (max-width: 480px) {
  .err-num { letter-spacing: -2px; }
  .err-icon-wrap { width: 66px; height: 66px; font-size: 1.8rem; }
  .err-orbit { width: 100px; height: 100px; }
  .err-actions { gap: 9px; }
  .err-btn { padding: 11px 18px; font-size: .82rem; }
}
</style>

<!-- ══════════════════════════════════════════════════════════ -->
<!--  404 CONTENT                                              -->
<!-- ══════════════════════════════════════════════════════════ -->
<div class="err-wrap">
  <!-- background -->
  <div class="err-dots"></div>
  <div class="err-blob err-blob-1"></div>
  <div class="err-blob err-blob-2"></div>
  <div class="err-blob err-blob-3"></div>

  <div class="err-inner">

    <!-- animated 404 graphic -->
    <div class="err-graphic">
      <span class="err-num">404</span>

    </div>

    <!-- badge -->
    <div class="err-badge">⚠️ Page Under Construction</div>

    <!-- headline -->
    <h1 class="err-title">Oops! This page took a<br>wrong turn somewhere</h1>

    <!-- description -->
    <p class="err-desc">
      The page you're looking for may have been moved, renamed, or is still
      under construction. Let us help you find what you need.
    </p>

    <!-- search -->
    <div class="err-search">
      <input
        type="text"
        id="err404Search"
        placeholder="Search services, plots, products..."
        onkeydown="if(event.key==='Enter') window.location.href='/search.php?q='+encodeURIComponent(this.value)"
      >
      <button class="err-search-btn" onclick="doSearch()">
        <i class="bi bi-search"></i>
      </button>
    </div>

    <!-- action buttons -->
    <div class="err-actions">
      <a href="/" class="err-btn err-btn-gold">
        <i class="bi bi-house-fill"></i> Go Home
      </a>
      <a href="/services.php" class="err-btn err-btn-dark">
        <i class="bi bi-tools"></i> Browse Services
      </a>
      <a href="https://wa.me/<?= defined('SITE_WHATSAPP') ? SITE_WHATSAPP : '919821130198' ?>?text=Hi%2C+I+was+on+a+page+that+returned+404.+Can+you+help%3F"
         target="_blank" class="err-btn err-btn-green">
        <i class="bi bi-whatsapp"></i> WhatsApp Us
      </a>
      <a href="javascript:history.back()" class="err-btn err-btn-outline">
        <i class="bi bi-arrow-left"></i> Go Back
      </a>
    </div>

    <!-- quick links -->
    <div class="err-quick">
      <div class="err-quick-label">Popular Pages</div>
      <div class="err-quick-grid">
        <?php
        $quickPages = [
          ['/', '🏠', 'Home'],
          ['/services.php', '🔧', 'Services'],
          ['/plots.php', '🗺️', 'Plots'],
          ['/products.php', '🛍️', 'Products'],
          ['/packages.php', '📦', 'Packages'],
          ['/portfolio.php', '🏛️', 'Portfolio'],
          ['/blog.php', '📝', 'Blog'],
          ['/contact.php', '📞', 'Contact'],
        ];
        foreach ($quickPages as [$href, $icon, $name]): ?>
        <a href="<?= $href ?>" class="err-qlink">
          <div class="ql-icon"><?= $icon ?></div>
          <div class="ql-name"><?= $name ?></div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<script>
function doSearch() {
  var q = document.getElementById('err404Search').value.trim();
  if (q) window.location.href = '/search.php?q=' + encodeURIComponent(q);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>