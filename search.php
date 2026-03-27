<?php
require_once __DIR__ . '/includes/functions.php';

$q = sanitizeInput($_GET['q'] ?? '');
$type = sanitizeInput($_GET['type'] ?? 'all'); // all | products | services | blogs | plots

$results = ['products'=>[],'services'=>[],'blogs'=>[],'plots'=>[]];
$total   = 0;

if (strlen($q) >= 2) {
    $like = "%$q%";
    try {
        if ($type === 'all' || $type === 'products') {
            $s = $pdo->prepare("SELECT id, name, slug, price, sale_price, image, short_desc FROM products WHERE status=1 AND (name LIKE ? OR short_desc LIKE ? OR description LIKE ?) LIMIT 12");
            $s->execute([$like,$like,$like]);
            $results['products'] = $s->fetchAll();
        }
        if ($type === 'all' || $type === 'services') {
            $s = $pdo->prepare("SELECT id, name, slug, price, image, short_desc FROM services WHERE status=1 AND (name LIKE ? OR short_desc LIKE ? OR description LIKE ?) LIMIT 12");
            $s->execute([$like,$like,$like]);
            $results['services'] = $s->fetchAll();
        }
        if ($type === 'all' || $type === 'blogs') {
            $s = $pdo->prepare("SELECT id, title, slug, excerpt, image, created_at, author_name FROM blogs WHERE status='published' AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?) LIMIT 6");
            $s->execute([$like,$like,$like]);
            $results['blogs'] = $s->fetchAll();
        }
        if ($type === 'all' || $type === 'plots') {
            $s = $pdo->prepare("SELECT id, title, type, city, locality, price, size_sqft FROM plots WHERE status='Available' AND (title LIKE ? OR city LIKE ? OR locality LIKE ?) LIMIT 6");
            $s->execute([$like,$like,$like]);
            $results['plots'] = $s->fetchAll();
        }
        $total = count($results['products']) + count($results['services']) + count($results['blogs']) + count($results['plots']);
    } catch (Exception $e) {}
}

$pageTitle = $q ? 'Search: ' . htmlspecialchars($q) . ' — Incredible Heights' : 'Search — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
.result-card { transition: transform .2s, box-shadow .2s; }
.result-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.1); }
</style>

<!-- Search Bar -->
<div style="background:var(--dark);padding:40px 0;">
  <div class="container">
    <h1 class="fw-800 text-center mb-4" style="color:#fff;font-size:1.8rem;">🔍 Search Incredible Heights</h1>
    <div class="row justify-content-center">
      <div class="col-md-8">
        <form method="GET" action="/search.php">
          <div class="d-flex gap-2">
            <input type="text" name="q" class="form-control form-control-lg" value="<?= htmlspecialchars($q) ?>"
                   placeholder="Search products, services, blogs..." autofocus>
            <button type="submit" class="btn btn-gold btn-lg px-4 fw-700">Search</button>
          </div>
          <?php if ($q): ?>
          <div class="d-flex gap-2 mt-3 justify-content-center flex-wrap">
            <?php foreach(['all'=>'All','products'=>'Products','services'=>'Services','blogs'=>'Blogs','plots'=>'Plots'] as $t => $lbl): ?>
            <a href="?q=<?= urlencode($q) ?>&type=<?= $t ?>"
               class="btn btn-sm <?= $type===$t ? 'btn-gold' : 'btn-outline-light' ?>"><?= $lbl ?></a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="container py-5">

  <?php if (!$q): ?>
  <!-- No search yet — show suggestions -->
  <div class="text-center py-4">
    <div style="font-size:4rem;">🔍</div>
    <h4 class="fw-700 mt-3">What are you looking for?</h4>
    <p class="text-muted">Search for products, services, blog articles, or plots.</p>
    <div class="d-flex gap-2 justify-content-center flex-wrap mt-3">
      <?php foreach(['ceiling fan','interior design','house construction','electrical','plumbing','modular kitchen'] as $suggestion): ?>
      <a href="?q=<?= urlencode($suggestion) ?>" class="btn btn-outline-secondary btn-sm"><?= htmlspecialchars($suggestion) ?></a>
      <?php endforeach; ?>
    </div>
  </div>

  <?php elseif ($total === 0): ?>
  <!-- No results -->
  <div class="text-center py-5">
    <div style="font-size:4rem;">😕</div>
    <h4 class="fw-700 mt-3">No results for "<?= htmlspecialchars($q) ?>"</h4>
    <p class="text-muted">Try different keywords or browse our categories below.</p>
    <div class="row g-3 justify-content-center mt-3" style="max-width:600px;margin:auto;">
      <div class="col-6 col-md-3"><a href="/products.php" class="btn btn-outline-dark w-100 small">📦 Products</a></div>
      <div class="col-6 col-md-3"><a href="/services.php" class="btn btn-outline-dark w-100 small">🔧 Services</a></div>
      <div class="col-6 col-md-3"><a href="/blog.php" class="btn btn-outline-dark w-100 small">📝 Blog</a></div>
      <div class="col-6 col-md-3"><a href="/contact.php" class="btn btn-outline-dark w-100 small">📞 Contact</a></div>
    </div>
  </div>

  <?php else: ?>
  <div class="mb-4">
    <p class="text-muted"><strong><?= $total ?></strong> result<?= $total>1?'s':'' ?> found for "<strong><?= htmlspecialchars($q) ?></strong>"</p>
  </div>

  <!-- Products -->
  <?php if ($results['products']): ?>
  <div class="mb-5">
    <h4 class="fw-800 mb-4">📦 Products <span class="badge bg-secondary small"><?= count($results['products']) ?></span></h4>
    <div class="row g-3">
    <?php foreach ($results['products'] as $p): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <div class="ih-card result-card h-100 overflow-hidden">
        <a href="/product-detail.php?id=<?= $p['id'] ?>" class="text-decoration-none text-dark">
          <?php if ($p['image']): ?>
          <img src="/<?= htmlspecialchars($p['image']) ?>" alt="" style="width:100%;height:160px;object-fit:cover;">
          <?php else: ?>
          <div style="width:100%;height:160px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:2.5rem;">📦</div>
          <?php endif; ?>
          <div class="p-3">
            <h6 class="fw-700 mb-1 small" style="line-height:1.4;"><?= htmlspecialchars($p['name']) ?></h6>
            <?php if ($p['short_desc']): ?><p class="text-muted" style="font-size:.75rem;margin-bottom:8px;"><?= htmlspecialchars(substr($p['short_desc'],0,60)) ?>...</p><?php endif; ?>
            <div class="fw-800" style="color:var(--gold);">
              <?php if ($p['sale_price'] && $p['sale_price'] < $p['price']): ?>
              ₹<?= number_format($p['sale_price']) ?> <del class="text-muted fw-400 small">₹<?= number_format($p['price']) ?></del>
              <?php else: ?>
              ₹<?= number_format($p['price']) ?>
              <?php endif; ?>
            </div>
          </div>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Services -->
  <?php if ($results['services']): ?>
  <div class="mb-5">
    <h4 class="fw-800 mb-4">🔧 Services <span class="badge bg-secondary small"><?= count($results['services']) ?></span></h4>
    <div class="row g-3">
    <?php foreach ($results['services'] as $sv): ?>
    <div class="col-md-6 col-lg-4">
      <div class="ih-card result-card p-3 h-100 d-flex gap-3">
        <?php if ($sv['image']): ?>
        <img src="/<?= htmlspecialchars($sv['image']) ?>" alt="" style="width:60px;height:60px;border-radius:10px;object-fit:cover;flex-shrink:0;">
        <?php else: ?>
        <div style="width:60px;height:60px;border-radius:10px;background:var(--gold-lt);display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">🔧</div>
        <?php endif; ?>
        <div class="min-w-0">
          <h6 class="fw-700 mb-1 small"><?= htmlspecialchars($sv['name']) ?></h6>
          <?php if ($sv['short_desc']): ?><p class="text-muted mb-1" style="font-size:.75rem;"><?= htmlspecialchars(substr($sv['short_desc'],0,70)) ?></p><?php endif; ?>
          <?php if ($sv['price']): ?><div class="fw-700 small" style="color:var(--gold);">From ₹<?= number_format($sv['price']) ?></div><?php endif; ?>
          <a href="/service-detail.php?id=<?= $sv['id'] ?>" class="btn btn-sm btn-gold mt-2" style="font-size:.72rem;">View Service</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Blogs -->
  <?php if ($results['blogs']): ?>
  <div class="mb-5">
    <h4 class="fw-800 mb-4">📝 Blog Posts <span class="badge bg-secondary small"><?= count($results['blogs']) ?></span></h4>
    <div class="row g-3">
    <?php foreach ($results['blogs'] as $bl): ?>
    <div class="col-md-6">
      <div class="ih-card result-card p-3 d-flex gap-3">
        <?php if ($bl['image']): ?>
        <img src="/<?= htmlspecialchars($bl['image']) ?>" alt="" style="width:80px;height:80px;border-radius:10px;object-fit:cover;flex-shrink:0;">
        <?php else: ?>
        <div style="width:80px;height:80px;border-radius:10px;background:var(--dark);display:flex;align-items:center;justify-content:center;font-size:1.8rem;flex-shrink:0;">📝</div>
        <?php endif; ?>
        <div class="min-w-0">
          <a href="/blog-detail.php?slug=<?= urlencode($bl['slug'] ?? '') ?>&id=<?= $bl['id'] ?>"
             class="fw-700 small d-block text-decoration-none" style="color:var(--dark);">
            <?= htmlspecialchars($bl['title']) ?>
          </a>
          <?php if ($bl['excerpt']): ?><p class="text-muted mb-1" style="font-size:.75rem;"><?= htmlspecialchars(substr($bl['excerpt'],0,80)) ?>...</p><?php endif; ?>
          <div class="text-muted" style="font-size:.7rem;"><?= date('d M Y',strtotime($bl['created_at'])) ?> • <?= htmlspecialchars($bl['author_name'] ?? 'IH Team') ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- Plots -->
  <?php if ($results['plots']): ?>
  <div class="mb-5">
    <h4 class="fw-800 mb-4">🗺️ Plots <span class="badge bg-secondary small"><?= count($results['plots']) ?></span></h4>
    <div class="row g-3">
    <?php foreach ($results['plots'] as $pl): ?>
    <div class="col-md-6 col-lg-4">
      <div class="ih-card result-card p-3">
        <div class="fw-700"><?= htmlspecialchars($pl['title']) ?></div>
        <div class="text-muted small"><?= htmlspecialchars($pl['locality'].', '.$pl['city']) ?></div>
        <div class="d-flex gap-3 mt-2 small">
          <span>📐 <?= number_format($pl['size_sqft']) ?> sq.ft</span>
          <span class="fw-700 text-gold">₹<?= number_format($pl['price']) ?></span>
        </div>
        <a href="/plot-detail.php?id=<?= $pl['id'] ?>" class="btn btn-sm btn-outline-dark mt-2 small">View Plot</a>
      </div>
    </div>
    <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
