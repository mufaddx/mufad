<?php
require_once __DIR__ . '/includes/functions.php';

$serviceId = (int)($_GET['id'] ?? 0);
$service   = null;

if ($serviceId && $pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT s.*, sc.name AS cat_name
            FROM services s
            LEFT JOIN service_categories sc ON s.category_id = sc.id
            WHERE s.id = ? AND s.status = 1
        ");
        $stmt->execute([$serviceId]);
        $service = $stmt->fetch();
    } catch (Exception $e) {}
}
if (!$service) redirect('/services.php');

$success = false;
$errors  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST['csrf'] ?? '')) {
        $errors[] = 'Security error. Please refresh the page.';
    } else {
        $name    = sanitizeInput($_POST['name'] ?? '');
        $phone   = sanitizeInput($_POST['phone'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $date    = sanitizeInput($_POST['preferred_date'] ?? '');
        $time    = sanitizeInput($_POST['preferred_time'] ?? '');
        $notes   = sanitizeInput($_POST['notes'] ?? '');

        if (!$name)  $errors[] = 'Name is required.';
        if (!$phone) $errors[] = 'Phone is required.';
        if (!$date)  $errors[] = 'Preferred date is required.';

        if (empty($errors) && $pdo) {
            try {
                $userId    = isLoggedIn() ? $_SESSION['user_id'] : null;
                $bookingNo = generateBookingNumber();
                $pdo->prepare("
                    INSERT INTO bookings
                      (booking_number, user_id, service_id, name, phone, address,
                       preferred_date, preferred_time, notes, status, created_at)
                    VALUES (?,?,?,?,?,?,?,?,?,'Pending',NOW())
                ")->execute([
                    $bookingNo, $userId, $service['id'],
                    $name, $phone, $address, $date, $time, $notes
                ]);
                $success = true;
            } catch (Exception $e) {
                error_log('Booking error: ' . $e->getMessage());
                $errors[] = 'Could not save booking. Please call us directly.';
            }
        }
    }
}

$pageTitle = 'Book ' . htmlspecialchars($service['name']) . ' — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<div class="page-banner">
  <div class="container">
    <h1 class="mb-1">Book Service</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="/" class="text-muted">Home</a></li>
      <li class="breadcrumb-item"><a href="/services.php" class="text-muted">Services</a></li>
      <li class="breadcrumb-item active"><?= htmlspecialchars($service['name']) ?></li>
    </ol></nav>
  </div>
</div>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-4">
      <div class="ih-card p-4 position-sticky" style="top:80px">
        <div style="font-size:3rem;text-align:center;margin-bottom:12px"><?= $service['icon'] ?? '🔧' ?></div>
        <h4 class="fw-800 text-center mb-1"><?= htmlspecialchars($service['name']) ?></h4>
        <?php if (!empty($service['cat_name'])): ?>
          <div class="text-center mb-3"><span class="badge-gold"><?= htmlspecialchars($service['cat_name']) ?></span></div>
        <?php endif; ?>
        <?php if (!empty($service['short_desc'])): ?>
          <p class="text-muted small"><?= htmlspecialchars($service['short_desc']) ?></p>
        <?php endif; ?>
        <?php if (!empty($service['price_from'])): ?>
          <div class="text-center mt-3">
            <div class="text-muted small">Starting from</div>
            <div class="fw-800 fs-4 text-gold">₹<?= number_format($service['price_from']) ?>/<?= htmlspecialchars($service['price_unit'] ?? 'sqft') ?></div>
          </div>
        <?php endif; ?>
        <hr>
        <div class="small text-muted">
          <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-telephone text-warning"></i>
            <a href="tel:<?= SITE_PHONE ?>"><?= SITE_PHONE ?></a>
          </div>
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-whatsapp text-success"></i>
            <a href="https://wa.me/<?= SITE_WHATSAPP ?>" target="_blank">WhatsApp Us</a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="ih-card p-4">
        <h4 class="fw-800 mb-4">📅 Book Your Service</h4>
        <?php if ($success): ?>
          <div class="text-center py-4">
            <div style="font-size:4rem">✅</div>
            <h4 class="fw-800 text-success mt-3">Booking Confirmed!</h4>
            <p class="text-muted">Our team will call you within 30 minutes.</p>
            <div class="d-flex gap-2 justify-content-center flex-wrap mt-3">
              <?php if (isLoggedIn()): ?><a href="/user/bookings.php" class="btn btn-gold">View My Bookings</a><?php endif; ?>
              <a href="/services.php" class="btn btn-outline-secondary">Browse More Services</a>
            </div>
          </div>
        <?php else: ?>
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
            </div>
          <?php endif; ?>
          <form method="POST">
            <input type="hidden" name="csrf" value="<?= generateCSRF() ?>">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-600">Full Name *</label>
                <input type="text" name="name" class="form-control" required
                       value="<?= htmlspecialchars($_SESSION['user_name'] ?? $_POST['name'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-600">Phone Number *</label>
                <input type="tel" name="phone" class="form-control" required
                       value="<?= htmlspecialchars($_SESSION['user_phone'] ?? $_POST['phone'] ?? '') ?>">
              </div>
              <div class="col-12">
                <label class="form-label fw-600">Service Address *</label>
                <textarea name="address" class="form-control" rows="2" required
                          placeholder="Full address where service is needed..."><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-600">Preferred Date *</label>
                <input type="date" name="preferred_date" class="form-control" required
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                       value="<?= htmlspecialchars($_POST['preferred_date'] ?? '') ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-600">Preferred Time</label>
                <select name="preferred_time" class="form-select">
                  <option value="">Select time slot</option>
                  <?php foreach (['9:00 AM - 11:00 AM','11:00 AM - 1:00 PM','2:00 PM - 4:00 PM','4:00 PM - 6:00 PM','Flexible / Anytime'] as $slot): ?>
                    <option value="<?= $slot ?>" <?= ($_POST['preferred_time'] ?? '') === $slot ? 'selected' : '' ?>><?= $slot ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label fw-600">Additional Requirements</label>
                <textarea name="notes" class="form-control" rows="3"
                          placeholder="Any specific requirements or details..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-gold btn-lg w-100 fw-700">
                  <i class="bi bi-calendar-check me-2"></i>Confirm Booking
                </button>
              </div>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
