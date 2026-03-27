<?php
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Terms of Service — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="page-banner"><div class="container"><h1 class="mb-0" style="font-family:'Playfair Display',Georgia,serif;">Terms of Service</h1></div></div>
<div class="container py-5">
  <div class="row justify-content-center"><div class="col-lg-8">
    <div class="ih-card p-5">
      <p class="text-muted small">Last updated: <?= date('d F Y') ?></p>
      <h4 class="fw-800 mt-3 mb-3">1. Agreement to Terms</h4>
      <p class="text-muted">By accessing or using Incredible Heights services, you agree to be bound by these Terms of Service. If you disagree with any part of these terms, you may not access our services.</p>
      <h4 class="fw-800 mt-4 mb-3">2. Services</h4>
      <p class="text-muted">Incredible Heights provides construction, renovation, and interior design services in Delhi NCR. All services are subject to availability and our team's assessment of the project scope.</p>
      <h4 class="fw-800 mt-4 mb-3">3. Orders & Payments</h4>
      <p class="text-muted">Orders placed through our platform are subject to confirmation by our team. Prices are indicative and final quotes are provided after site inspection. Payment terms are agreed upon at the time of booking.</p>
      <h4 class="fw-800 mt-4 mb-3">4. Cancellation Policy</h4>
      <p class="text-muted">Bookings can be cancelled 24 hours before the scheduled service date for a full refund. Cancellations within 24 hours may attract a cancellation fee of 10% of the booking value.</p>
      <h4 class="fw-800 mt-4 mb-3">5. Warranty</h4>
      <p class="text-muted">Incredible Heights provides a 1-year workmanship warranty on all completed projects. This covers defects in workmanship but excludes damage caused by misuse, negligence, or natural disasters.</p>
      <h4 class="fw-800 mt-4 mb-3">6. Contact</h4>
      <p class="text-muted">For any queries regarding these terms, contact us at <a href="mailto:<?= SUPPORT_EMAIL ?>"><?= SUPPORT_EMAIL ?></a> or call <?= SITE_PHONE ?>.</p>
    </div>
  </div></div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
