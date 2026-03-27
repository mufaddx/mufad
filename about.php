<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About Us — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<style>
/* ── HERO ── */
.ab-hero {
  background: linear-gradient(112deg, #ffffff 0%, #eef4ff 50%, #fffbee 100%);
  padding: 60px 0 56px;
  border-bottom: 1.5px solid #e8edf5;
  position: relative; overflow: hidden;
}
.ab-hero::before {
  content: ''; position: absolute; top: -100px; right: -100px;
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(201,168,76,.09) 0%, transparent 68%);
  pointer-events: none;
}
.ab-hero::after {
  content: ''; position: absolute; bottom: -80px; left: -80px;
  width: 350px; height: 350px;
  background: radial-gradient(circle, rgba(10,22,40,.04) 0%, transparent 68%);
  pointer-events: none;
}
.ab-eyebrow {
  display: inline-flex; align-items: center; gap: 7px;
  background: rgba(201,168,76,.10); border: 1.5px solid rgba(201,168,76,.28);
  color: #b08010; font-size: .69rem; font-weight: 800;
  letter-spacing: 1.6px; text-transform: uppercase;
  padding: 6px 16px; border-radius: 40px; margin-bottom: 18px;
}
.ab-h1 {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(1.9rem, 4vw, 2.9rem);
  font-weight: 900; color: #0c1e35;
  line-height: 1.18; letter-spacing: -.4px; margin-bottom: 14px;
}
.ab-h1 span { color: var(--gold); }
.ab-sub { color: #5a6e85; font-size: .97rem; line-height: 1.7; max-width: 510px; }
.ab-stats { display: flex; flex-wrap: wrap; gap: 11px; margin-top: 28px; }
.ab-stat {
  background: #fff; border: 1.5px solid #e4ebf5;
  border-radius: 13px; padding: 10px 18px;
  box-shadow: 0 2px 10px rgba(10,22,40,.05);
}
.ab-stat-num {
  font-family: 'Playfair Display', serif;
  font-size: 1.3rem; font-weight: 900; color: #0c1e35; line-height: 1.1;
}
.ab-stat-lbl {
  font-size: .68rem; font-weight: 600; color: #8595a9;
  text-transform: uppercase; letter-spacing: .8px;
}
.ab-logo-card {
  background: #fff; border-radius: 22px;
  border: 1.5px solid #e8edf5;
  box-shadow: 0 14px 44px rgba(10,22,40,.09);
  padding: 32px; display: flex; align-items: center;
  justify-content: center; position: relative;
  max-width: 290px; width: 100%; margin: 0 auto;
}
.ab-logo-card img { height: 130px; width: auto; object-fit: contain; }
.ab-logo-pill {
  position: absolute; bottom: -13px; left: 50%; transform: translateX(-50%);
  background: linear-gradient(135deg, #e8c96d, var(--gold));
  color: #0c1e35; font-size: .67rem; font-weight: 800;
  letter-spacing: 1px; text-transform: uppercase;
  padding: 5px 16px; border-radius: 30px; white-space: nowrap;
  box-shadow: 0 4px 14px rgba(201,168,76,.35);
}

/* ── SECTIONS ── */
.ab-sec     { padding: 70px 0; }
.ab-sec-alt { background: #f6f9fe; padding: 70px 0; }
.ab-chip {
  display: inline-flex; align-items: center; gap: 6px;
  background: rgba(201,168,76,.08); border: 1px solid rgba(201,168,76,.22);
  color: #b08010; font-size: .67rem; font-weight: 800;
  letter-spacing: 1.4px; text-transform: uppercase;
  padding: 5px 14px; border-radius: 30px; margin-bottom: 12px;
}
.ab-h2 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.45rem, 2.5vw, 1.95rem);
  font-weight: 900; color: #0c1e35; margin-bottom: 8px;
}
.ab-divider {
  width: 46px; height: 3px;
  background: linear-gradient(90deg, var(--gold), transparent);
  border-radius: 2px; margin-bottom: 18px;
}

/* ── WHY ITEMS — emoji icons, no Bootstrap icons needed ── */
.ab-why {
  display: flex; gap: 15px; align-items: flex-start;
  padding: 16px; border-radius: 13px; transition: background .18s;
}
.ab-why:hover { background: #eef4ff; }
.ab-why-icon {
  width: 46px; height: 46px; flex-shrink: 0;
  background: linear-gradient(135deg, #e8c96d, var(--gold));
  border-radius: 12px; display: flex; align-items: center;
  justify-content: center; font-size: 1.25rem;
  box-shadow: 0 4px 14px rgba(201,168,76,.28);
}
.ab-why-title { font-weight: 700; color: #0c1e35; font-size: .88rem; margin-bottom: 2px; }
.ab-why-desc  { color: #8595a9; font-size: .78rem; line-height: 1.5; margin: 0; }

/* ── SERVICE CARDS ── */
.ab-svc {
  background: #fff; border: 1.5px solid #edf1f8; border-radius: 16px;
  padding: 20px 16px; text-align: center; height: 100%;
  transition: all .22s ease;
}
.ab-svc:hover {
  border-color: rgba(201,168,76,.45);
  box-shadow: 0 10px 32px rgba(10,22,40,.10);
  transform: translateY(-4px);
}
.ab-svc-icon {
  width: 50px; height: 50px; border-radius: 13px; margin: 0 auto 12px;
  background: linear-gradient(135deg, rgba(201,168,76,.13), rgba(201,168,76,.04));
  display: flex; align-items: center; justify-content: center; font-size: 1.35rem;
}
.ab-svc-name { font-size: .84rem; font-weight: 700; color: #0c1e35; margin-bottom: 4px; }
.ab-svc-desc { font-size: .70rem; color: #8595a9; line-height: 1.5; }

/* ── OFFICE CARDS ── */
.ab-office {
  background: #fff; border: 1.5px solid #edf1f8; border-radius: 20px;
  padding: 28px; height: 100%; position: relative; overflow: hidden;
  transition: all .2s ease;
}
.ab-office::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
  background: linear-gradient(90deg, var(--gold), #e8c96d);
}
.ab-office:hover { box-shadow: 0 14px 40px rgba(10,22,40,.10); transform: translateY(-3px); }
.ab-office-icon {
  width: 42px; height: 42px; border-radius: 11px;
  background: rgba(201,168,76,.10); display: flex;
  align-items: center; justify-content: center;
  font-size: 1.2rem; margin-bottom: 14px;
}
.ab-office h5 { font-size: .93rem; font-weight: 800; color: #0c1e35; margin-bottom: 9px; }
.ab-office p  { color: #5a6e85; font-size: .83rem; line-height: 1.6; margin-bottom: 16px; }

/* ── CTA BANNER ── */
.ab-cta {
  background: linear-gradient(110deg, #0c1e35 0%, #1a3460 100%);
  border-radius: 22px; padding: 42px 40px; color: #fff;
  position: relative; overflow: hidden;
}
.ab-cta::before {
  content: ''; position: absolute; inset: 0;
  background-image: radial-gradient(circle, rgba(201,168,76,.07) 1px, transparent 1px);
  background-size: 24px 24px; pointer-events: none;
}
.ab-cta h3 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.35rem, 2.5vw, 1.85rem);
  font-weight: 900; margin-bottom: 9px;
}
.ab-cta p { color: rgba(255,255,255,.58); font-size: .88rem; margin-bottom: 0; }

@media (max-width: 575px) {
  .ab-stats { gap: 8px; }
  .ab-stat  { padding: 8px 13px; }
  .ab-cta   { padding: 28px 20px; }
}
</style>


<!-- ═══════════════════════ HERO ════════════════════════════════════ -->
<section class="ab-hero">
  <div class="container" style="position:relative;z-index:1;">
    <div class="row align-items-center g-5">

      <div class="col-lg-7">
        <div class="ab-eyebrow">⭐ Trusted Since 1975</div>

        <h1 class="ab-h1">
          Building Dreams Across<br>
          <span>Delhi NCR</span> for 50+ Years
        </h1>

        <p class="ab-sub">
          From foundation to finishing — Incredible Heights is Delhi NCR's most trusted
          construction &amp; interior solutions company, with 5000+ projects delivered
          across residential, commercial, and industrial spaces.
        </p>

        <div class="ab-stats">
          <div class="ab-stat">
            <div class="ab-stat-num">5000<span style="color:var(--gold)">+</span></div>
            <div class="ab-stat-lbl">Projects</div>
          </div>
          <div class="ab-stat">
            <div class="ab-stat-num">50<span style="color:var(--gold)">+</span></div>
            <div class="ab-stat-lbl">Yrs Active</div>
          </div>
          <div class="ab-stat">
            <div class="ab-stat-num">350<span style="color:var(--gold)">+</span></div>
            <div class="ab-stat-lbl">Services</div>
          </div>
          <div class="ab-stat">
            <div class="ab-stat-num">4.9<span style="color:var(--gold)">★</span></div>
            <div class="ab-stat-lbl">Rating</div>
          </div>
        </div>
      </div>

      <div class="col-lg-5 d-flex justify-content-center">
        <div class="ab-logo-card">
          <img src="/assets/images/logo.jpg" alt="Incredible Heights">
          <div class="ab-logo-pill">Builder &amp; Developer</div>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ═══════════════════════ WHY CHOOSE US ═══════════════════════════ -->
<section class="ab-sec">
  <div class="container">
    <div class="row g-5 align-items-center">

      <div class="col-lg-5">
        <div class="ab-chip">🏆 Why Choose Us</div>
        <h2 class="ab-h2">Your Complete Construction Partner</h2>
        <div class="ab-divider"></div>
        <p style="color:#5a6e85;font-size:.88rem;line-height:1.75;margin-bottom:26px;">
          We handle every aspect of construction and interiors under one roof — saving
          you time, money, and the hassle of managing multiple vendors.
        </p>
        <a href="/contact.php" class="btn btn-gold px-4 py-2 me-2">
          📅 Free Site Visit
        </a>
        <a href="tel:<?= SITE_PHONE ?>" class="btn btn-outline-secondary px-4 py-2">
          📞 Call Now
        </a>
      </div>

      <div class="col-lg-7">
        <div class="row g-2">
          <?php
          $whyUs = [
            ['✅', 'Licensed & Insured',   'Fully licensed professionals with liability insurance on every project.'],
            ['⏱️', '30-Min Response',      'Our team responds to every inquiry within 30 minutes, guaranteed.'],
            ['📊', 'Transparent Pricing',  'Detailed quotes with no hidden charges — ever.'],
            ['🛡️', '5-Year Warranty',      'Structural & workmanship warranty on all major projects.'],
            ['👷', 'Expert Team',          '200+ certified engineers, architects & interior designers.'],
            ['📍', 'Pan-NCR Coverage',     'Serving Delhi, Noida, Gurugram, Faridabad & Greater Noida.'],
          ];
          foreach ($whyUs as [$icon, $title, $desc]): ?>
          <div class="col-md-6">
            <div class="ab-why">
              <div class="ab-why-icon"><?= $icon ?></div>
              <div>
                <div class="ab-why-title"><?= $title ?></div>
                <p class="ab-why-desc"><?= $desc ?></p>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ═══════════════════════ SERVICES ════════════════════════════════ -->
<section class="ab-sec-alt">
  <div class="container">
    <div class="text-center mb-5">
      <div class="ab-chip mx-auto">🔨 Our Expertise</div>
      <h2 class="ab-h2">350+ Services Under One Roof</h2>
      <div class="ab-divider mx-auto"></div>
      <p style="color:#5a6e85;font-size:.9rem;max-width:520px;margin:0 auto;">
        From civil foundations to premium interiors — every trade skill under one roof.
      </p>
    </div>
    <div class="row g-3">
      <?php
      $services = [
        ['🏗️','Civil Construction','Foundation, structure, RCC work, brick masonry'],
        ['🛋️','Interior Design',   'Modular kitchen, false ceiling, wardrobes, full fit-out'],
        ['⚡', 'Electrical Work',  'Complete wiring, switchboards, panels, outdoor lighting'],
        ['🔧','Plumbing',          'Pipework, bathroom fitting, water tank, drainage'],
        ['🎨','Painting',          'Interior & exterior painting, wall texture, wallpaper'],
        ['🏠','Flooring',          'Tiles, marble, hardwood, vinyl, polishing'],
        ['🪵','Carpentry',         'Custom furniture, doors, windows, woodwork'],
        ['🗺️','Real Estate',       'Residential plots in prime Delhi NCR locations'],
      ];
      foreach ($services as [$icon, $name, $desc]): ?>
      <div class="col-6 col-md-3">
        <div class="ab-svc">
          <div class="ab-svc-icon"><?= $icon ?></div>
          <div class="ab-svc-name"><?= $name ?></div>
          <div class="ab-svc-desc"><?= $desc ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ═══════════════════════ OFFICES ═════════════════════════════════ -->
<section class="ab-sec">
  <div class="container">
    <div class="text-center mb-5">
      <div class="ab-chip mx-auto">📍 Find Us</div>
      <h2 class="ab-h2">Our Offices</h2>
      <div class="ab-divider mx-auto"></div>
    </div>

    <div class="row g-4 mb-5">
      <div class="col-md-6">
        <div class="ab-office">
          <div class="ab-office-icon">🏢</div>
          <h5>Corporate Office</h5>
          <p><?= OFFICE_CORPORATE ?></p>
          <a href="https://wa.me/<?= SITE_WHATSAPP ?>?text=Hi%2C+I+would+like+to+visit+your+corporate+office."
             target="_blank" class="btn btn-gold btn-sm px-3">
            WhatsApp Us
          </a>
        </div>
      </div>
      <div class="col-md-6">
        <div class="ab-office">
          <div class="ab-office-icon">📋</div>
          <h5>Registered Office</h5>
          <p><?= OFFICE_REGISTERED ?></p>
          <a href="tel:<?= SITE_PHONE ?>" class="btn btn-dark-ih btn-sm px-3">
            Call Us
          </a>
        </div>
      </div>
    </div>

    <!-- CTA Banner -->
    <div class="ab-cta">
      <div class="row align-items-center g-4" style="position:relative;z-index:1;">
        <div class="col-lg-8">
          <h3>Ready to Start Your Project?</h3>
          <p>Get a free consultation and site visit from our expert team — no obligation, completely free.</p>
        </div>
        <div class="col-lg-4 text-lg-end">
          <a href="https://wa.me/<?= SITE_WHATSAPP ?>?text=Hi%2C+I+need+a+free+site+visit."
             target="_blank" class="btn btn-gold px-4 py-2 me-2 mb-2">
            WhatsApp
          </a>
          <a href="tel:<?= SITE_PHONE ?>" class="btn btn-outline-light px-4 py-2 mb-2">
            <?= SITE_PHONE ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>