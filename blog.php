<?php
// BLOG PAGE — INCREDIBLE HEIGHTS
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Construction Tips & Blog — ' . SITE_NAME;
$pageDesc  = 'Expert construction tips, interior design ideas, real estate advice for Delhi NCR homes by Incredible Heights.';

// Load HTML shell FIRST
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

// FETCH DATA
$blogs       = [];
$categories  = [];
$selectedCat = isset($_GET['cat'])  ? clean($_GET['cat'])  : '';
$search      = isset($_GET['q'])    ? clean($_GET['q'])    : '';
$page        = max(1, (int)($_GET['page'] ?? 1));
$perPage     = 9;
$totalCount  = 0;

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `blog_categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(100) NOT NULL UNIQUE,
        `color` VARCHAR(20) DEFAULT '#e8560a',
        `status` TINYINT DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `blogs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL UNIQUE,
        `excerpt` TEXT,
        `content` LONGTEXT,
        `image` VARCHAR(255),
        `category_id` INT DEFAULT NULL,
        `author_name` VARCHAR(100) DEFAULT 'Admin',
        `read_time` INT DEFAULT 5,
        `meta_title` VARCHAR(255),
        `meta_desc` TEXT,
        `status` TINYINT DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $categories = $pdo->query("SELECT * FROM `blog_categories` WHERE status=1 ORDER BY name ASC")->fetchAll();
    $where  = "b.status = 1";
    $params = [];
    if ($selectedCat) { $where .= " AND bc.slug = ?"; $params[] = $selectedCat; }
    if ($search)      { $where .= " AND (b.title LIKE ? OR b.excerpt LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

    $tcStmt = $pdo->prepare("SELECT COUNT(*) FROM blogs b LEFT JOIN blog_categories bc ON b.category_id=bc.id WHERE $where");
    $tcStmt->execute($params);
    $totalCount = (int)$tcStmt->fetchColumn();
    $offset = ($page - 1) * $perPage;
    $stmt = $pdo->prepare("
        SELECT b.*, bc.name AS cat_name, bc.slug AS cat_slug, bc.color AS cat_color
        FROM blogs b
        LEFT JOIN blog_categories bc ON b.category_id = bc.id
        WHERE $where ORDER BY b.created_at DESC
        LIMIT {$perPage} OFFSET {$offset}
    ");
    $stmt->execute($params);
    $blogs = $stmt->fetchAll();
} catch (Throwable $e) {
    $blogs = []; $categories = [];
}

// DEMO FALLBACK
if (empty($blogs)) {
    $totalCount = 6;
    $blogs = [
        ['id'=>1,'title'=>'How to Choose the Right Flooring for Your Home in Delhi NCR','slug'=>'choose-right-flooring','excerpt'=>'Complete guide to selecting marble, tiles, wooden or vinyl flooring based on your budget and lifestyle.','image'=>'','cat_name'=>'Interior Design','cat_slug'=>'interior','cat_color'=>'#1565c0','created_at'=>date('Y-m-d',strtotime('-2 days')),'author_name'=>'Incredible Heights','read_time'=>5],
        ['id'=>2,'title'=>'RCC vs AAC Blocks: Which is Better for Construction?','slug'=>'rcc-vs-aac-blocks','excerpt'=>'Expert comparison of traditional RCC construction and modern AAC block technology for Delhi NCR homes.','image'=>'','cat_name'=>'Civil Work','cat_slug'=>'civil','cat_color'=>'#d97706','created_at'=>date('Y-m-d',strtotime('-5 days')),'author_name'=>'Incredible Heights','read_time'=>7],
        ['id'=>3,'title'=>'Top 10 Home Renovation Mistakes to Avoid in 2025','slug'=>'renovation-mistakes-2025','excerpt'=>'Common renovation errors that cost homeowners lakhs — and how to avoid them with proper planning.','image'=>'','cat_name'=>'Tips & Tricks','cat_slug'=>'tips','cat_color'=>'#2e7d32','created_at'=>date('Y-m-d',strtotime('-8 days')),'author_name'=>'Incredible Heights','read_time'=>6],
        ['id'=>4,'title'=>'Vastu Shastra Tips for Your New Home in Delhi NCR','slug'=>'vastu-tips-home','excerpt'=>'Essential Vastu guidelines for direction, rooms, entrance and colours in modern Delhi NCR apartments.','image'=>'','cat_name'=>'Real Estate','cat_slug'=>'real-estate','cat_color'=>'#dc2626','created_at'=>date('Y-m-d',strtotime('-12 days')),'author_name'=>'Incredible Heights','read_time'=>8],
        ['id'=>5,'title'=>'Complete Guide to LED Lighting for Your Home','slug'=>'led-lighting-guide','excerpt'=>'Everything about lumens, color temperature and choosing the right LED lights for every room.','image'=>'','cat_name'=>'Electrical','cat_slug'=>'electrical','cat_color'=>'#7c3aed','created_at'=>date('Y-m-d',strtotime('-16 days')),'author_name'=>'Incredible Heights','read_time'=>4],
        ['id'=>6,'title'=>'Plot Investment: Noida vs Gurugram — 2025 Analysis','slug'=>'noida-vs-gurugram-plots','excerpt'=>'Data-driven comparison of plot investment in Noida, Greater Noida and Gurugram for 2025.','image'=>'','cat_name'=>'Real Estate','cat_slug'=>'real-estate','cat_color'=>'#dc2626','created_at'=>date('Y-m-d',strtotime('-20 days')),'author_name'=>'Incredible Heights','read_time'=>10],
    ];
}
if (empty($categories)) {
    $categories = [
        ['name'=>'Civil Work',     'slug'=>'civil',       'color'=>'#d97706'],
        ['name'=>'Interior Design','slug'=>'interior',    'color'=>'#1565c0'],
        ['name'=>'Real Estate',    'slug'=>'real-estate', 'color'=>'#dc2626'],
        ['name'=>'Electrical',     'slug'=>'electrical',  'color'=>'#7c3aed'],
        ['name'=>'Tips & Tricks',  'slug'=>'tips',        'color'=>'#2e7d32'],
    ];
}
$totalPages = max(1, (int)ceil($totalCount / $perPage));
?>

<!-- SEO Schema -->
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"Blog","name":"<?= SITE_NAME ?> Blog","url":"<?= defined('SITE_URL') ? SITE_URL : '' ?>/blog.php","description":"Construction tips and real estate guides for Delhi NCR"}
</script>

<style>
:root {
  --or:#e8560a; --or-lt:#fff5f0; --or-b:rgba(232,86,10,.18);
  --bl:#1565c0; --bl-lt:#f0f5ff;
  --gr:#2e7d32; --gr-lt:#f0faf0;
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb; --white:#fff;
}

/* ── FILTER BAR (sticky) ── */
.blog-filter-bar {
  background: var(--white);
  border-bottom: 1.5px solid var(--border);
  padding: 11px 5%;
  position: sticky; top: 66px; z-index: 200;
  box-shadow: 0 2px 10px rgba(26,35,50,.06);
}
@media(max-width:991px) { .blog-filter-bar { top: 60px; } }
.blog-filter-inner {
  max-width: 1400px; margin: 0 auto;
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}

/* Badge */
.blg-badge {
  display: flex; align-items: center; gap: 8px; flex-shrink: 0;
}
.blg-badge-icon {
  width: 34px; height: 34px; border-radius: 9px;
  background: rgba(232,86,10,.10); border: 1.5px solid rgba(232,86,10,.22);
  display: flex; align-items: center; justify-content: center;
  color: var(--or); font-size: .9rem; flex-shrink: 0;
}
.blg-badge-title { font-size: .82rem; font-weight: 800; color: var(--txt); }
.blg-badge-count { font-size: .7rem; color: var(--hint); font-weight: 600; }
.blg-divider { width:1px; height:28px; background:var(--border); flex-shrink:0; display:none; }
@media(min-width:400px) { .blg-divider { display:block; } }

/* Search box */
.blg-search-wrap {
  display: flex; align-items: center;
  border: 1.5px solid var(--border); border-radius: 10px;
  background: var(--bg); overflow: hidden;
  flex: 1; min-width: 160px; max-width: 280px;
  transition: border-color .18s, box-shadow .18s;
}
.blg-search-wrap:focus-within {
  border-color: var(--or); box-shadow: 0 0 0 3px rgba(232,86,10,.10); background: #fff;
}
.blg-search-input {
  flex: 1; border: none; outline: none; background: transparent;
  padding: 9px 12px; font-size: .84rem;
  font-family: 'DM Sans', sans-serif; color: var(--txt);
}
.blg-search-input::placeholder { color: var(--hint); }
.blg-search-btn {
  background: none; border: none; cursor: pointer;
  padding: 9px 12px; color: var(--hint); font-size: .9rem;
  transition: color .15s;
}
.blg-search-btn:hover { color: var(--or); }

/* Category dropdown */
.blg-sel-wrap { position: relative; min-width: 150px; max-width: 220px; }
.blg-sel-wrap .blg-chev {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  pointer-events: none; color: var(--hint); font-size: .7rem;
}
.blg-select {
  width: 100%; appearance: none; -webkit-appearance: none;
  border: 1.5px solid var(--border); border-radius: 10px;
  padding: 9px 34px 9px 13px; font-size: .84rem;
  font-family: 'DM Sans', sans-serif; font-weight: 600;
  color: var(--txt); background: var(--bg);
  outline: none; cursor: pointer;
  transition: border-color .18s, box-shadow .18s, background .18s;
}
.blg-select:focus { border-color: var(--or); box-shadow: 0 0 0 3px rgba(232,86,10,.10); background: #fff; }
.blg-select.active { border-color: var(--or); background: var(--or-lt); color: var(--or); font-weight: 700; }

/* Clear pill */
.blg-clear {
  display: flex; align-items: center; gap: 5px;
  font-size: .75rem; color: var(--or); font-weight: 700;
  background: var(--or-lt); border: 1.5px solid var(--or-b);
  padding: 6px 13px; border-radius: 20px; text-decoration: none;
  white-space: nowrap; flex-shrink: 0; transition: opacity .15s;
}
.blg-clear:hover { opacity: .82; color: var(--or); }

/* ── GRID AREA ── */
.blog-area { background: var(--bg); padding: 26px 5% 60px; min-height: 70vh; }
.blog-area-inner { max-width: 1400px; margin: 0 auto; }

/* Section heading */
.blg-sec-hd { margin-bottom: 22px; }
.blg-sec-label {
  display: inline-flex; align-items: center; gap: 6px;
  font-size: .67rem; font-weight: 800; color: var(--or);
  text-transform: uppercase; letter-spacing: 1.2px;
  background: var(--or-lt); border: 1px solid var(--or-b);
  padding: 4px 12px; border-radius: 28px; margin-bottom: 10px;
}
.blg-sec-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(1.25rem, 2.2vw, 1.8rem);
  font-weight: 900; color: var(--txt); margin-bottom: 4px;
}
.blg-sec-title span { color: var(--or); }
.blg-sec-sub { font-size: .88rem; color: var(--light); }

/* ── BLOG CARDS GRID ── */
.blog-grid {
  display: grid;
  grid-template-columns: repeat(1, 1fr);
  gap: 16px;
}
@media(min-width:576px) { .blog-grid { grid-template-columns: repeat(2,1fr); gap:16px; } }
@media(min-width:992px) { .blog-grid { grid-template-columns: repeat(3,1fr); gap:18px; } }

/* ── BLOG CARD ── */
.blog-card {
  background: var(--white);
  border: 1.5px solid var(--border);
  border-radius: 18px; overflow: hidden;
  display: flex; flex-direction: column;
  transition: all .22s;
  box-shadow: 0 2px 10px rgba(26,35,50,.05);
}
.blog-card:hover {
  border-color: rgba(232,86,10,.2);
  box-shadow: 0 10px 30px rgba(26,35,50,.12);
  transform: translateY(-3px);
}

/* Card image area */
.blog-card-img {
  height: 180px; overflow: hidden; position: relative;
  background: linear-gradient(135deg, #f8fafc, var(--or-lt));
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: center;
}
.blog-card-img img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform .4s ease;
}
.blog-card:hover .blog-card-img img { transform: scale(1.04); }
.blog-card-no-img { font-size: 3.5rem; opacity: .35; }
.blog-read-badge {
  position: absolute; bottom: 10px; right: 10px;
  background: rgba(26,35,50,.7); color: #fff;
  font-size: .66rem; font-weight: 600;
  padding: 4px 10px; border-radius: 20px;
  backdrop-filter: blur(4px);
}

/* Card body */
.blog-card-body {
  padding: 16px 16px 18px; display: flex; flex-direction: column; flex: 1; gap: 7px;
}
.blog-card-cat {
  display: inline-flex; align-items: center;
  font-size: .63rem; font-weight: 800; text-transform: uppercase; letter-spacing: .7px;
  padding: 3px 10px; border-radius: 18px;
  width: fit-content;
}
.blog-card-title {
  font-size: .92rem; font-weight: 800; color: var(--txt);
  line-height: 1.4; text-decoration: none;
  transition: color .15s;
}
.blog-card-title:hover { color: var(--or); }
.blog-card-excerpt {
  font-size: .78rem; color: var(--light); line-height: 1.65; flex: 1;
}
.blog-card-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding-top: 12px; border-top: 1px solid var(--border);
  margin-top: auto;
}
.blog-card-date { font-size: .7rem; color: var(--hint); font-weight: 600; }
.blog-read-link {
  font-size: .76rem; font-weight: 800; color: var(--or);
  text-decoration: none; display: flex; align-items: center; gap: 4px;
  transition: gap .15s;
}
.blog-read-link:hover { gap: 7px; color: var(--or); }

/* ── CTA STRIP ── */
.blog-cta {
  margin-top: 36px;
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 16px; padding: 24px 28px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 16px;
  box-shadow: 0 2px 10px rgba(26,35,50,.05);
}
@media(max-width:575px) { .blog-cta { padding: 18px 16px; } }
.blog-cta-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem; font-weight: 800; color: var(--txt); margin-bottom: 4px;
}
.blog-cta-sub { font-size: .84rem; color: var(--light); }
.btn-blog-wa {
  display: flex; align-items: center; gap: 7px;
  background: #25d366; color: #fff; font-weight: 700; font-size: .85rem;
  padding: 11px 20px; border-radius: 10px; text-decoration: none;
  box-shadow: 0 3px 12px rgba(37,211,102,.3); transition: all .2s;
}
.btn-blog-wa:hover { background: #1db954; color: #fff; transform: translateY(-1px); }

/* ── PAGINATION ── */
.blog-pagination {
  display: flex; justify-content: center; gap: 6px;
  flex-wrap: wrap; margin-top: 28px;
}
.blog-pg-btn {
  width: 36px; height: 36px; border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  font-size: .82rem; font-weight: 700; cursor: pointer;
  text-decoration: none; transition: all .15s;
  border: 1.5px solid var(--border); color: var(--mid);
  background: var(--white);
}
.blog-pg-btn:hover { border-color: var(--or); color: var(--or); background: var(--or-lt); }
.blog-pg-btn.active { background: var(--or); color: #fff; border-color: var(--or); }
</style>

<!-- ── FILTER BAR ── -->
<div class="blog-filter-bar">
  <form action="/blog.php" method="GET" style="display:contents;">
    <div class="blog-filter-inner">

      <!-- Badge -->
      <div class="blg-badge">
        <div class="blg-badge-icon"><i class="bi bi-journal-richtext"></i></div>
        <div>
          <div class="blg-badge-title">Expert Blog & Tips</div>
          <div class="blg-badge-count"><?= $totalCount ?> article<?= $totalCount !== 1 ? 's' : '' ?></div>
        </div>
      </div>

      <div class="blg-divider"></div>

      <!-- Search -->
      <div class="blg-search-wrap">
        <input type="text" name="q" class="blg-search-input"
               placeholder="Search articles..."
               value="<?= htmlspecialchars($search) ?>">
        <?php if($selectedCat): ?>
          <input type="hidden" name="cat" value="<?= htmlspecialchars($selectedCat) ?>">
        <?php endif; ?>
        <button type="submit" class="blg-search-btn"><i class="bi bi-search"></i></button>
      </div>

      <!-- Category dropdown -->
      <div class="blg-sel-wrap">
        <select class="blg-select <?= $selectedCat ? 'active' : '' ?>"
                onchange="this.form.submit()">
          <option value="">All Categories</option>
          <?php foreach($categories as $c): ?>
            <option name="cat" value="<?= htmlspecialchars($c['slug']) ?>"
              <?= $selectedCat === $c['slug'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <i class="bi bi-chevron-down blg-chev"></i>
      </div>

      <!-- Clear -->
      <?php if ($search || $selectedCat): ?>
        <a href="/blog.php" class="blg-clear"><i class="bi bi-x-lg"></i> Clear</a>
      <?php endif; ?>

    </div>
  </form>
</div>

<!-- ── MAIN AREA ── -->
<div class="blog-area">
  <div class="blog-area-inner">

    <!-- Section heading -->
    <div class="blg-sec-hd">
      <div class="blg-sec-label"><i class="bi bi-pencil-fill"></i> Expert Articles</div>
      <div class="blg-sec-title">
        <?php if($search): ?>
          Results for "<span><?= htmlspecialchars($search) ?></span>"
        <?php elseif($selectedCat): ?>
          <?= htmlspecialchars(array_column($categories,'name','slug')[$selectedCat] ?? ucfirst($selectedCat)) ?> <span>Articles</span>
        <?php else: ?>
          Expert Blog <span>&amp; Tips</span>
        <?php endif; ?>
      </div>
      <div class="blg-sec-sub">Construction, interior design, real estate &amp; home improvement guides for Delhi NCR</div>
    </div>

    <?php if(empty($blogs)): ?>
      <div style="text-align:center;padding:80px 20px;">
        <div style="font-size:3.5rem;margin-bottom:16px;">🔍</div>
        <h5 style="color:var(--mid);margin-bottom:8px;">No articles found</h5>
        <p style="color:var(--light);font-size:.88rem;margin-bottom:16px;">Try a different search or category.</p>
        <a href="/blog.php" style="color:var(--or);font-weight:700;text-decoration:none;">← View all articles</a>
      </div>
    <?php else: ?>

    <div class="blog-grid">
      <?php foreach($blogs as $b):
        $catColor = $b['cat_color'] ?? '#e8560a';
        $slug     = $b['slug']      ?? $b['id'];
        // Convert hex color to light bg for category pill
        $r = hexdec(substr(ltrim($catColor,'#'),0,2));
        $g = hexdec(substr(ltrim($catColor,'#'),2,2));
        $z = hexdec(substr(ltrim($catColor,'#'),4,2));
        $catBg = "rgba($r,$g,$z,.12)";
      ?>
      <article class="blog-card" itemscope itemtype="https://schema.org/BlogPosting">
        <!-- Image -->
        <a href="/blog-post.php?slug=<?= urlencode((string)$slug) ?>" style="display:block;text-decoration:none;">
          <div class="blog-card-img">
            <?php if(!empty($b['image'])): ?>
              <img src="/uploads/blogs/<?= htmlspecialchars($b['image']) ?>"
                   alt="<?= htmlspecialchars($b['title']) ?>" itemprop="image">
            <?php else: ?>
              <div class="blog-card-no-img">📝</div>
            <?php endif; ?>
            <?php if(!empty($b['read_time'])): ?>
              <span class="blog-read-badge">
                <i class="bi bi-clock me-1"></i><?= (int)$b['read_time'] ?> min read
              </span>
            <?php endif; ?>
          </div>
        </a>
        <!-- Body -->
        <div class="blog-card-body">
          <span class="blog-card-cat"
                style="background:<?= $catBg ?>;color:<?= $catColor ?>;border:1px solid <?= $catColor ?>33;">
            <?= htmlspecialchars($b['cat_name'] ?? 'General') ?>
          </span>
          <a href="/blog-post.php?slug=<?= urlencode((string)$slug) ?>"
             class="blog-card-title" itemprop="headline">
            <?= htmlspecialchars($b['title']) ?>
          </a>
          <p class="blog-card-excerpt" itemprop="description">
            <?= htmlspecialchars(mb_substr($b['excerpt'] ?? '', 0, 110)) ?>...
          </p>
          <div class="blog-card-footer">
            <span class="blog-card-date" itemprop="datePublished">
              <i class="bi bi-calendar3 me-1"></i><?= date('d M Y', strtotime($b['created_at'])) ?>
            </span>
            <a href="/blog-post.php?slug=<?= urlencode((string)$slug) ?>" class="blog-read-link">
              Read more <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
    <div class="blog-pagination">
      <?php for($p = 1; $p <= $totalPages; $p++): ?>
      <a href="?page=<?= $p ?><?= $selectedCat ? '&cat='.urlencode($selectedCat) : '' ?><?= $search ? '&q='.urlencode($search) : '' ?>"
         class="blog-pg-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>

    <!-- CTA Strip -->
    <div class="blog-cta">
      <div>
        <div class="blog-cta-title">Have a construction question?</div>
        <div class="blog-cta-sub">Ask our experts on WhatsApp — reply within 30 minutes!</div>
      </div>
      <a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '' ?>?text=Hi%2C+I+have+a+question+about+construction"
         target="_blank" class="btn-blog-wa">
        <i class="bi bi-whatsapp"></i> Chat with Expert Now
      </a>
    </div>

  </div>
</div>

<script>
// Category select — update hidden input then submit
document.querySelector('.blg-select')?.addEventListener('change', function() {
  var form = this.closest('form');
  var hidden = form.querySelector('input[name="cat"]');
  if (!hidden) {
    hidden = document.createElement('input');
    hidden.type = 'hidden'; hidden.name = 'cat';
    form.appendChild(hidden);
  }
  hidden.value = this.value;
  form.submit();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>