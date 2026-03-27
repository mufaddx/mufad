<?php
require_once __DIR__ . '/includes/functions.php';

$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf'] ?? '')) {
        $errors[] = 'Security error. Please refresh.';
    } else {
        $name    = sanitizeInput($_POST['name']    ?? '');
        $phone   = sanitizeInput($_POST['phone']   ?? '');
        $email   = sanitizeInput($_POST['email']   ?? '');
        $subject = sanitizeInput($_POST['subject'] ?? 'General Enquiry');
        $msg     = sanitizeInput($_POST['message'] ?? '');

        if (!$name)  $errors[] = 'Name is required.';
        if (!$phone) $errors[] = 'Phone is required.';
        if (!$msg)   $errors[] = 'Message is required.';

        if (empty($errors) && $pdo) {
            try {
                $pdo->prepare("INSERT INTO enquiries (name, phone, email, service_interest, message, source, lead_status, created_at) VALUES (?,?,?,?,?,'website','New',NOW())")
                    ->execute([$name, $phone, $email, $subject, $msg]);
                $success = true;
            } catch (Exception $e) {
                $errors[] = 'Could not send message. Please try calling us directly.';
            }
        }
    }
}

$pageTitle = 'Contact Us — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
:root {
  --or:#e8560a; --or-lt:#fff5f0; --or-b:rgba(232,86,10,.18);
  --bl:#1565c0; --bl-lt:#f0f5ff; --bl-b:rgba(21,101,192,.18);
  --gr:#2e7d32; --gr-lt:#f0faf0; --gr-b:rgba(46,125,50,.18);
  --gd:#c9a84c; --gd-lt:#fffbf0;
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb; --white:#fff;
}

/* ── PAGE WRAPPER ── */
.contact-page { background: var(--bg); padding: 44px 0 60px; }
.contact-inner { max-width: 1100px; margin: 0 auto; padding: 0 5%; }

/* ── SECTION HEADER ── */
.contact-hd { margin-bottom: 36px; }
.contact-label {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: .68rem; font-weight: 800; color: var(--or);
  text-transform: uppercase; letter-spacing: 1.3px;
  background: var(--or-lt); border: 1px solid var(--or-b);
  padding: 5px 14px; border-radius: 28px; margin-bottom: 12px;
}
.contact-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(1.8rem, 3.5vw, 2.6rem);
  font-weight: 900; color: var(--txt); margin-bottom: 8px;
}
.contact-title span { color: var(--or); }
.contact-sub { font-size: .92rem; color: var(--light); max-width: 480px; line-height: 1.6; }

/* ── LAYOUT ── */
.contact-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 24px;
}
@media(min-width:768px) { .contact-layout { grid-template-columns: 340px 1fr; } }

/* ── LEFT: CONTACT INFO CARDS ── */
.contact-info-card {
  display: flex; gap: 14px; align-items: flex-start;
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 14px; padding: 16px 18px;
  margin-bottom: 12px; text-decoration: none;
  transition: all .2s; box-shadow: 0 2px 8px rgba(26,35,50,.05);
}
.contact-info-card:hover {
  border-color: var(--or-b);
  box-shadow: 0 6px 20px rgba(26,35,50,.1);
  transform: translateX(3px);
}
.ci-icon {
  width: 42px; height: 42px; border-radius: 11px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; font-size: .95rem;
}
.ci-label { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .8px; margin-bottom: 3px; }
.ci-value { font-size: .85rem; color: var(--mid); line-height: 1.5; font-weight: 500; }

/* ── RIGHT: FORM CARD ── */
.contact-form-card {
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 18px;
  padding: 30px 28px;
  box-shadow: 0 3px 14px rgba(26,35,50,.07);
}
@media(max-width:575px) { .contact-form-card { padding: 20px 16px; } }

.form-card-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.35rem; font-weight: 800; color: var(--txt);
  margin-bottom: 5px;
}
.form-card-sub { font-size: .84rem; color: var(--light); margin-bottom: 24px; }

/* Form inputs */
.lf-label { display: block; font-size: .82rem; font-weight: 700; color: var(--txt); margin-bottom: 6px; }
.lf-input, .lf-select, .lf-textarea {
  width: 100%; border: 1.5px solid var(--border); border-radius: 11px;
  padding: 11px 14px; font-size: .88rem; font-family: 'DM Sans', sans-serif;
  color: var(--txt); background: var(--bg); outline: none;
  transition: border-color .18s, box-shadow .18s; margin-bottom: 16px;
}
.lf-input:focus, .lf-select:focus, .lf-textarea:focus {
  border-color: var(--or); box-shadow: 0 0 0 3px rgba(232,86,10,.12); background: #fff;
}
.lf-textarea { resize: vertical; min-height: 130px; }
.lf-input::placeholder, .lf-textarea::placeholder { color: #c4cdd6; }

/* Submit btn */
.btn-contact-submit {
  display: flex; align-items: center; justify-content: center; gap: 9px;
  width: 100%; padding: 14px; border: none; border-radius: 12px; cursor: pointer;
  font-weight: 800; font-size: .95rem;
  background: linear-gradient(135deg, #f0a070, var(--or));
  color: #fff; box-shadow: 0 5px 20px rgba(232,86,10,.3);
  transition: all .22s;
}
.btn-contact-submit:hover { transform: translateY(-2px); box-shadow: 0 9px 26px rgba(232,86,10,.4); }

/* Alert */
.alert-ok { background: #f0fdf4; color: #166534; border: 1.5px solid #bbf7d0; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; font-weight: 600; font-size: .88rem; }
.alert-err { background: #fef2f2; color: #991b1b; border: 1.5px solid #fecaca; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px; font-size: .88rem; }

/* Input rows */
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
@media(max-width:575px) { .form-row-2 { grid-template-columns: 1fr; } }
</style>

<div class="contact-page">
  <div class="contact-inner">

    <!-- Header -->
    <div class="contact-hd">
      <div class="contact-label"><i class="bi bi-telephone-fill"></i> Get In Touch</div>
      <h1 class="contact-title">Contact <span>Us</span></h1>
      <p class="contact-sub">Call, WhatsApp or fill the form — we reply within 30 minutes guaranteed!</p>
    </div>

    <div class="contact-layout">

      <!-- LEFT: Contact Info -->
      <div>
        <!-- Call -->
        <a href="tel:<?= defined('SITE_PHONE') ? SITE_PHONE : '' ?>" class="contact-info-card">
          <div class="ci-icon" style="background:rgba(46,125,50,.1);border:1.5px solid rgba(46,125,50,.2);">
            <i class="bi bi-telephone-fill" style="color:#2e7d32;"></i>
          </div>
          <div>
            <div class="ci-label" style="color:#2e7d32;">Call Us</div>
            <div class="ci-value"><?= defined('SITE_PHONE') ? SITE_PHONE : '+91 9821130198' ?></div>
            <div style="font-size:.72rem;color:var(--hint);margin-top:2px;">Mon–Sat, 9am – 7pm</div>
          </div>
        </a>

        <!-- WhatsApp -->
        <a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '' ?>?text=Hi, I need help"
           target="_blank" class="contact-info-card">
          <div class="ci-icon" style="background:#f0fdf4;border:1.5px solid #bbf7d0;">
            <i class="bi bi-whatsapp" style="color:#25d366;"></i>
          </div>
          <div>
            <div class="ci-label" style="color:#16a34a;">WhatsApp</div>
            <div class="ci-value"><?= defined('SITE_PHONE') ? SITE_PHONE : '+91 9821130198' ?></div>
            <div style="font-size:.72rem;color:var(--hint);margin-top:2px;">Quick reply within minutes</div>
          </div>
        </a>

        <!-- Email -->
        <a href="mailto:<?= defined('SITE_EMAIL') ? SITE_EMAIL : 'info@ihindia.in' ?>"
           class="contact-info-card">
          <div class="ci-icon" style="background:var(--bl-lt);border:1px solid var(--bl-b);">
            <i class="bi bi-envelope-fill" style="color:var(--bl);"></i>
          </div>
          <div>
            <div class="ci-label" style="color:var(--bl);">Email Us</div>
            <div class="ci-value"><?= defined('SITE_EMAIL') ? SITE_EMAIL : 'info@ihindia.in' ?></div>
            <div style="font-size:.72rem;color:var(--hint);margin-top:2px;">Reply within 24 hours</div>
          </div>
        </a>

        <!-- Registered Office -->
        <div class="contact-info-card" style="cursor:default;">
          <div class="ci-icon" style="background:var(--or-lt);border:1px solid var(--or-b);">
            <i class="bi bi-building" style="color:var(--or);"></i>
          </div>
          <div>
            <div class="ci-label" style="color:var(--or);">Registered Office</div>
            <div class="ci-value">3rd Floor, R-217, Flat No-303, Lane 4,<br>Joga Bai Extn, Jamia Nagar,<br><strong>New Delhi – 110025</strong></div>
          </div>
        </div>

        <!-- Corporate Office -->
        <div class="contact-info-card" style="cursor:default;margin-bottom:0;">
          <div class="ci-icon" style="background:var(--bl-lt);border:1px solid var(--bl-b);">
            <i class="bi bi-geo-alt-fill" style="color:var(--bl);"></i>
          </div>
          <div>
            <div class="ci-label" style="color:var(--bl);">Corporate Office</div>
            <div class="ci-value">1st Floor, Abul Fazal G-57,<br>Kalindi Kunj Road Part II,<br><strong>New Delhi – 110025</strong></div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Form -->
      <div class="contact-form-card">
        <div class="form-card-title">Send Us a Message</div>
        <div class="form-card-sub">Fill the form below — our team will call you back within 30 minutes.</div>

        <?php if ($success): ?>
        <div class="alert-ok">
          <i class="bi bi-check-circle-fill me-2"></i>
          Thank you! We'll call you back within 30 minutes. 🎉
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="alert-err">
          <?php foreach ($errors as $e): ?>
            <div><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($e) ?></div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST">
          <input type="hidden" name="csrf" value="<?= generateCSRF() ?>">

          <div class="form-row-2">
            <div>
              <label class="lf-label">Full Name *</label>
              <input type="text" name="name" class="lf-input" required
                     placeholder="Your full name"
                     value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>
            <div>
              <label class="lf-label">Phone Number *</label>
              <input type="tel" name="phone" class="lf-input" required
                     placeholder="+91 XXXXX XXXXX"
                     value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            </div>
          </div>

          <div class="form-row-2">
            <div>
              <label class="lf-label">Email Address</label>
              <input type="email" name="email" class="lf-input"
                     placeholder="your@email.com"
                     value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div>
              <label class="lf-label">Subject</label>
              <select name="subject" class="lf-select">
                <?php foreach (['General Enquiry','Get a Quote','Civil Construction','Interior Design','Electrical Work','Plumbing','Painting','Site Visit Request','Other'] as $sub): ?>
                  <option value="<?= $sub ?>" <?= ($_POST['subject'] ?? '') === $sub ? 'selected' : '' ?>>
                    <?= $sub ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div>
            <label class="lf-label">Message *</label>
            <textarea name="message" class="lf-textarea" required
                      placeholder="Tell us about your project or requirement..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn-contact-submit">
            <i class="bi bi-send-fill"></i> Send Message
          </button>

          <div style="text-align:center;margin-top:12px;font-size:.74rem;color:var(--hint);">
            🔒 Your information is safe with us. No spam ever.
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>