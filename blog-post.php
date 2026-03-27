<?php
// blog-post.php — Blog detail page for Incredible Heights
require_once __DIR__ . '/includes/functions.php';

$slug = sanitizeInput($_GET['slug'] ?? '');
$id   = (int)($_GET['id'] ?? 0);
$post = null; $related = [];

// Demo blog posts matching blog.php fallback data
$_demoPosts = [
    'choose-right-flooring' => [
        'id'=>1,'title'=>'How to Choose the Right Flooring for Your Home in Delhi NCR',
        'slug'=>'choose-right-flooring','cat_name'=>'Interior Design','cat_color'=>'#1565c0',
        'excerpt'=>'Complete guide to selecting marble, tiles, wooden or vinyl flooring based on your budget and lifestyle.',
        'content'=>'<h2>Why Flooring Matters</h2><p>Flooring is one of the most important decisions in any home. It affects the look, feel, durability, and maintenance of your space. In Delhi NCR, where temperatures vary from extreme heat in summer to cold winters, choosing the right flooring is even more critical.</p><h2>Popular Flooring Options</h2><h3>1. Vitrified Tiles</h3><p>The most popular choice in Delhi NCR homes. Vitrified tiles are durable, easy to clean, and available in hundreds of designs. Cost: ₹40–₹150 per sq ft installed.</p><h3>2. Marble Flooring</h3><p>Classic and luxurious. Marble looks stunning but requires regular maintenance and polishing. Best for living rooms and bedrooms. Cost: ₹80–₹300 per sq ft installed.</p><h3>3. Wooden / Laminate Flooring</h3><p>Warm, elegant, and comfortable. Laminate is an affordable alternative to solid wood. Great for bedrooms. Cost: ₹60–₹200 per sq ft installed.</p><h3>4. Vinyl / SPC Flooring</h3><p>Waterproof, durable, and easy to install. Best for kitchens, bathrooms, and areas with moisture. Cost: ₹45–₹120 per sq ft installed.</p><h2>Our Recommendation</h2><p>For most Delhi NCR homes, we recommend vitrified tiles for common areas (living, dining, kitchen) and wooden laminate for bedrooms. This gives you the best combination of durability and comfort within a reasonable budget.</p><p>Contact Incredible Heights for a free site visit — our experts will help you choose the perfect flooring for your home.</p>',
        'image'=>'','author_name'=>'IH Team','read_time'=>5,
        'created_at'=>date('Y-m-d',strtotime('-2 days')),'meta_title'=>'','meta_desc'=>'',
    ],
    'rcc-vs-aac-blocks' => [
        'id'=>2,'title'=>'RCC vs AAC Blocks: Which is Better for Construction?',
        'slug'=>'rcc-vs-aac-blocks','cat_name'=>'Civil Work','cat_color'=>'#d97706',
        'excerpt'=>'Expert comparison of traditional RCC construction and modern AAC block technology for Delhi NCR homes.',
        'content'=>'<h2>What is RCC Construction?</h2><p>RCC (Reinforced Cement Concrete) is the traditional method of construction using cement, sand, aggregate, and steel reinforcement. It has been the standard in India for decades and is known for its strength and durability.</p><h2>What are AAC Blocks?</h2><p>AAC (Autoclaved Aerated Concrete) blocks are a modern building material made from fly ash, cement, lime, and aluminum powder. They are lightweight, thermally efficient, and eco-friendly.</p><h2>Key Differences</h2><h3>Weight</h3><p>AAC blocks are 3-4x lighter than traditional bricks, reducing the load on the structure and foundation. This can save up to 10-15% on structural costs.</p><h3>Thermal Insulation</h3><p>AAC blocks provide excellent thermal insulation, keeping your home cooler in summer and warmer in winter — reducing your AC and heating bills by up to 30%.</p><h3>Construction Speed</h3><p>AAC blocks are larger in size, so walls go up faster. Construction time can be reduced by 20-30%.</p><h3>Cost</h3><p>AAC blocks typically cost 15-20% more than traditional bricks, but the savings on structural work, plastering, and energy bills often offset this.</p><h2>Which Should You Choose?</h2><p>For most residential projects in Delhi NCR, we recommend AAC blocks for their thermal efficiency and speed. However, for high-rise buildings and areas with specific structural requirements, consult with our engineers.</p>',
        'image'=>'','author_name'=>'IH Team','read_time'=>7,
        'created_at'=>date('Y-m-d',strtotime('-5 days')),'meta_title'=>'','meta_desc'=>'',
    ],
    'renovation-mistakes-2025' => [
        'id'=>3,'title'=>'Top 10 Home Renovation Mistakes to Avoid in 2025',
        'slug'=>'renovation-mistakes-2025','cat_name'=>'Tips & Tricks','cat_color'=>'#2e7d32',
        'excerpt'=>'Common renovation errors that cost homeowners lakhs — and how to avoid them with proper planning.',
        'content'=>'<h2>Introduction</h2><p>Home renovation is exciting but can quickly become a nightmare if you make common mistakes. After completing 5,000+ projects in Delhi NCR, our team has seen it all. Here are the top 10 mistakes to avoid.</p><h3>1. Not Having a Detailed Plan</h3><p>Starting without drawings and specifications leads to constant changes, delays, and budget overruns. Always get detailed drawings before starting.</p><h3>2. Choosing the Cheapest Contractor</h3><p>The cheapest quote almost always means cutting corners. Look for value, not just price. Check references and past work.</p><h3>3. Ignoring Permits</h3><p>Many renovations require municipal approvals. Skipping permits can lead to demolition orders and legal issues.</p><h3>4. Underestimating the Budget</h3><p>Always add 15-20% buffer to your budget for unexpected expenses. Renovation almost always costs more than initially planned.</p><h3>5. Poor Waterproofing</h3><p>Skimping on waterproofing leads to seepage, dampness, and expensive repairs later. Always use quality materials from brands like Sika or Dr. Fixit.</p><h3>6. Not Checking Material Quality</h3><p>Verify all materials before they arrive on site. Substandard materials are a common way contractors cut costs.</p><h3>7. Changing Design Mid-Construction</h3><p>Changes during construction are the biggest cost escalator. Finalize everything before work begins.</p><h3>8. Ignoring Ventilation</h3><p>Good ventilation is essential for kitchen, bathrooms, and bedrooms. Plan for proper airflow from the start.</p><h3>9. Cheap Electrical Work</h3><p>Electrical shortcuts cause fires. Always use ISI-marked wires and certified electricians.</p><h3>10. Not Getting Everything in Writing</h3><p>Always have a written contract with timeline, specifications, and payment schedule. This protects both parties.</p>',
        'image'=>'','author_name'=>'IH Team','read_time'=>6,
        'created_at'=>date('Y-m-d',strtotime('-8 days')),'meta_title'=>'','meta_desc'=>'',
    ],
    'vastu-tips-home' => [
        'id'=>4,'title'=>'Vastu Shastra Tips for Your New Home in Delhi NCR',
        'slug'=>'vastu-tips-home','cat_name'=>'Real Estate','cat_color'=>'#dc2626',
        'excerpt'=>'Essential Vastu guidelines for direction, rooms, entrance and colours in modern Delhi NCR apartments.',
        'content'=>'<h2>Why Vastu Matters</h2><p>Vastu Shastra is an ancient Indian science of architecture that promotes harmony, prosperity, and well-being in your home. Many Delhi NCR homeowners incorporate Vastu principles in their construction and interior design.</p><h2>Key Vastu Tips</h2><h3>Main Entrance</h3><p>The main entrance should ideally face North, East, or North-East. This brings positive energy and prosperity into the home.</p><h3>Master Bedroom</h3><p>The master bedroom should be in the South-West corner of the house. Sleep with your head pointing South or East.</p><h3>Kitchen</h3><p>The kitchen should be in the South-East corner (direction of fire/Agni). The cook should face East while cooking.</p><h3>Living Room</h3><p>The living room is best placed in the North or East. Keep it clutter-free and well-lit.</p><h3>Colors</h3><p>Use light, positive colors — white, cream, light yellow for living areas. Avoid dark colors in the bedroom.</p><h3>Water Elements</h3><p>Place water bodies, aquariums, or fountains in the North or North-East direction.</p>',
        'image'=>'','author_name'=>'IH Team','read_time'=>8,
        'created_at'=>date('Y-m-d',strtotime('-12 days')),'meta_title'=>'','meta_desc'=>'',
    ],
    'led-lighting-guide' => [
        'id'=>5,'title'=>'Complete Guide to LED Lighting for Your Home',
        'slug'=>'led-lighting-guide','cat_name'=>'Electrical','cat_color'=>'#7c3aed',
        'excerpt'=>'Everything about lumens, color temperature and choosing the right LED lights for every room.',
        'content'=>'<h2>Why Switch to LED?</h2><p>LED lights consume 80% less energy than traditional incandescent bulbs and last 25x longer. For a typical Delhi NCR home, switching to LED can save ₹3,000–₹8,000 per year on electricity bills.</p><h2>Understanding Lumens</h2><p>Lumens measure brightness. More lumens = brighter light. A 9W LED produces about 800 lumens — equivalent to a 60W incandescent bulb.</p><h2>Color Temperature</h2><p>Color temperature is measured in Kelvin (K):</p><ul><li><strong>2700K–3000K (Warm White)</strong>: Cozy, relaxing — best for bedrooms and living rooms</li><li><strong>4000K (Cool White)</strong>: Clean, productive — best for kitchens and offices</li><li><strong>6500K (Daylight)</strong>: Bright, energizing — best for study rooms and workspaces</li></ul><h2>Room-by-Room Guide</h2><h3>Living Room</h3><p>Use 3000K warm white downlights (9W each) for ambient lighting, with accent lights for artwork.</p><h3>Kitchen</h3><p>4000K cool white for the main area, with under-cabinet LED strips for work surfaces.</p><h3>Bedroom</h3><p>2700K warm white for a relaxing atmosphere. Add a bedside lamp for reading.</p>',
        'image'=>'','author_name'=>'IH Team','read_time'=>4,
        'created_at'=>date('Y-m-d',strtotime('-16 days')),'meta_title'=>'','meta_desc'=>'',
    ],
    'noida-vs-gurugram-plots' => [
        'id'=>6,'title'=>'Plot Investment: Noida vs Gurugram — 2025 Analysis',
        'slug'=>'noida-vs-gurugram-plots','cat_name'=>'Real Estate','cat_color'=>'#dc2626',
        'excerpt'=>'Data-driven comparison of plot investment in Noida, Greater Noida and Gurugram for 2025.',
        'content'=>'<h2>Introduction</h2><p>Delhi NCR offers multiple options for plot investment. Noida, Greater Noida, and Gurugram are the three major destinations. Let\'s analyze which offers the best returns in 2025.</p><h2>Noida</h2><p>Noida continues to grow with excellent metro connectivity, IT sector expansion, and new residential developments. Plot prices range from ₹4,500–₹8,000 per sq ft in prime sectors. Expected appreciation: 12–15% per year.</p><h2>Greater Noida</h2><p>More affordable than Noida with rapid infrastructure development. The upcoming metro extension and Jewar Airport are major growth drivers. Plot prices: ₹2,000–₹4,500 per sq ft. Expected appreciation: 15–20% per year.</p><h2>Gurugram</h2><p>Premium market with strong corporate presence. Plots near Dwarka Expressway and Southern Peripheral Road offer good returns. Plot prices: ₹5,000–₹15,000 per sq ft. Expected appreciation: 10–12% per year.</p><h2>Our Recommendation</h2><p>For investors looking for high growth potential at reasonable prices, Greater Noida West (GNIDA sectors) offers the best value in 2025. For premium investment, Dwarka Expressway in Gurugram remains a strong choice.</p>',
        'image'=>'','author_name'=>'IH Team','read_time'=>10,
        'created_at'=>date('Y-m-d',strtotime('-20 days')),'meta_title'=>'','meta_desc'=>'',
    ],
];

// Try DB first
try {
    if ($pdo) {
        if ($slug) {
            $s = $pdo->prepare("SELECT b.*, bc.name AS cat_name, bc.color AS cat_color FROM blogs b LEFT JOIN blog_categories bc ON b.category_id=bc.id WHERE b.slug=? AND b.status=1 LIMIT 1");
            $s->execute([$slug]); $post = $s->fetch();
        } elseif ($id) {
            $s = $pdo->prepare("SELECT b.*, bc.name AS cat_name, bc.color AS cat_color FROM blogs b LEFT JOIN blog_categories bc ON b.category_id=bc.id WHERE b.id=? AND b.status=1 LIMIT 1");
            $s->execute([$id]); $post = $s->fetch();
        }
        if ($post) {
            $rs = $pdo->prepare("SELECT b.*, bc.name AS cat_name FROM blogs b LEFT JOIN blog_categories bc ON b.category_id=bc.id WHERE b.status=1 AND b.id != ? ORDER BY b.created_at DESC LIMIT 3");
            $rs->execute([$post['id']]);
            $related = $rs->fetchAll();
        }
    }
} catch (Exception $e) {}

// Fallback to demo data
if (!$post) {
    // Try by slug
    if ($slug && isset($_demoPosts[$slug])) {
        $post = $_demoPosts[$slug];
    }
    // Try by id
    if (!$post && $id) {
        foreach ($_demoPosts as $dp) {
            if ($dp['id'] === $id) { $post = $dp; break; }
        }
    }
    if (!$post) redirect('/blog.php');
    // Related: 3 other demo posts
    $related = array_values(array_filter($_demoPosts, fn($p) => $p['id'] !== $post['id']));
    $related = array_slice($related, 0, 3);
}

$pageTitle = htmlspecialchars(($post['meta_title'] ?? '') ?: $post['title']) . ' — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';

$shareUrl   = (defined('SITE_URL') ? SITE_URL : 'https://shop.ihindia.in') . '/blog-post.php?slug=' . urlencode($post['slug'] ?? '');
$catColor   = $post['cat_color'] ?? '#e8560a';
$r = hexdec(substr(ltrim($catColor,'#'),0,2));
$g = hexdec(substr(ltrim($catColor,'#'),2,2));
$z = hexdec(substr(ltrim($catColor,'#'),4,2));
$catBg = "rgba($r,$g,$z,.12)";
?>

<style>
:root {
  --or:#e8560a; --or-lt:#fff5f0; --or-b:rgba(232,86,10,.18);
  --bl:#1565c0; --bl-lt:#f0f5ff;
  --gr:#2e7d32; --gr-lt:#f0faf0; --gr-b:rgba(46,125,50,.2);
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb; --white:#fff;
}

/* ── BREADCRUMB ── */
.bp-crumb {
  background: var(--white); border-bottom: 1.5px solid var(--border);
  padding: 11px 5%;
}
.bp-crumb-inner {
  max-width: 1200px; margin: 0 auto;
  display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
  font-size: .76rem; color: var(--hint);
}
.bp-crumb a { color: var(--hint); text-decoration: none; }
.bp-crumb a:hover { color: var(--or); }
.bp-crumb-sep { color: var(--border); }
.bp-crumb-active { color: var(--mid); font-weight: 600; }

/* ── PAGE ── */
.bp-page { background: var(--white); padding: 22px 5% 60px; }
.bp-inner {
  max-width: 1200px; margin: 0 auto;
  display: grid; grid-template-columns: 1fr;
  gap: 32px; align-items: start;
}
@media(min-width:992px) { .bp-inner { grid-template-columns: 1fr 300px; } }

/* ── ARTICLE — no border, no shadow, full width ── */
.bp-article { background: var(--white); border: none; box-shadow: none; }
.bp-article-body { padding: 0; }
@media(max-width:575px) { .bp-article-body { padding: 0; } }

/* Post header */
.bp-cat-pill {
  display: inline-flex; align-items: center;
  font-size: .67rem; font-weight: 800; text-transform: uppercase; letter-spacing: .8px;
  padding: 4px 12px; border-radius: 20px; margin-bottom: 14px;
  width: fit-content;
}
.bp-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: clamp(1.3rem, 3.5vw, 2rem);
  font-weight: 900; color: var(--txt); line-height: 1.3;
  margin-bottom: 14px;
}
.bp-meta {
  display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
  font-size: .76rem; color: var(--hint); font-weight: 600; margin-bottom: 22px;
  padding-bottom: 18px; border-bottom: 1.5px solid var(--border);
}
.bp-meta i { margin-right: 4px; color: var(--or); }

/* Featured image */
.bp-feat-img {
  width: 100%; max-height: 400px; object-fit: cover;
  border-radius: 14px; margin-bottom: 22px; display: block;
}
.bp-feat-placeholder {
  background: linear-gradient(135deg, var(--or-lt), #fff8f5);
  border-radius: 14px; height: 220px; margin-bottom: 22px;
  display: flex; align-items: center; justify-content: center;
  font-size: 4rem; opacity: .6;
}

/* Excerpt highlight */
.bp-excerpt {
  background: var(--or-lt); border-left: 4px solid var(--or);
  border-radius: 0 12px 12px 0; padding: 14px 18px;
  margin-bottom: 24px;
}
.bp-excerpt p { font-size: .9rem; font-weight: 600; color: var(--txt); margin: 0; line-height: 1.6; }

/* Article content */
.bp-content {
  font-size: .95rem; color: var(--mid); line-height: 1.85; letter-spacing: .1px;
}
.bp-content h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem; font-weight: 800; color: var(--txt);
  margin-top: 2rem; margin-bottom: .8rem;
  padding-bottom: 8px; border-bottom: 2px solid var(--or-lt);
}
.bp-content h3 { font-size: 1.05rem; font-weight: 800; color: var(--txt); margin-top: 1.5rem; margin-bottom: .6rem; }
.bp-content p  { margin-bottom: 1.1rem; }
.bp-content ul, .bp-content ol { padding-left: 1.4rem; margin-bottom: 1.2rem; }
.bp-content li { margin-bottom: .4rem; }
.bp-content blockquote {
  border-left: 4px solid var(--or); padding: 12px 18px;
  background: var(--or-lt); margin: 1.5rem 0; border-radius: 0 10px 10px 0;
}
.bp-content img { max-width: 100%; border-radius: 12px; margin: 1rem 0; }
.bp-content strong { color: var(--txt); }

/* Share buttons */
.bp-share {
  margin-top: 28px; padding-top: 18px; border-top: 1.5px solid var(--border);
  display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.bp-share-label { font-size: .82rem; font-weight: 700; color: var(--mid); }
.btn-share {
  display: flex; align-items: center; gap: 6px;
  font-size: .78rem; font-weight: 700; padding: 7px 14px; border-radius: 8px;
  text-decoration: none; border: none; cursor: pointer; transition: all .15s;
}
.btn-share-wa   { background: #25d366; color: #fff; }
.btn-share-wa:hover { background: #1db954; color: #fff; }
.btn-share-fb   { background: #1877f2; color: #fff; }
.btn-share-fb:hover { background: #0d6ee0; color: #fff; }
.btn-share-copy { background: var(--bg); color: var(--mid); border: 1.5px solid var(--border); }
.btn-share-copy:hover { background: var(--or-lt); color: var(--or); border-color: var(--or-b); }

/* ── RELATED POSTS ── */
.bp-related { margin-top: 22px; }
.bp-related-title {
  font-size: .9rem; font-weight: 800; color: var(--txt); margin-bottom: 14px;
  display: flex; align-items: center; gap: 7px;
}
.bp-related-title i { color: var(--or); }
.bp-related-grid { display: grid; grid-template-columns: 1fr; gap: 10px; }
@media(min-width:576px) { .bp-related-grid { grid-template-columns: repeat(3,1fr); } }
.bp-rel-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 14px; overflow: hidden; text-decoration: none;
  display: flex; flex-direction: column; transition: all .2s;
  box-shadow: 0 2px 8px rgba(26,35,50,.04);
}
.bp-rel-card:hover { border-color: rgba(232,86,10,.2); transform: translateY(-2px); box-shadow: 0 6px 18px rgba(26,35,50,.1); }
.bp-rel-img {
  height: 90px; background: var(--or-lt);
  display: flex; align-items: center; justify-content: center;
  font-size: 2rem; overflow: hidden;
}
.bp-rel-img img { width:100%; height:100%; object-fit:cover; }
.bp-rel-body { padding: 11px; }
.bp-rel-name  { font-size: .78rem; font-weight: 700; color: var(--txt); line-height: 1.3; margin-bottom: 5px; }
.bp-rel-date  { font-size: .68rem; color: var(--hint); }
.bp-rel-read  { font-size: .72rem; font-weight: 700; color: var(--or); display: inline-block; margin-top: 6px; }

/* ── SIDEBAR ── */
.bp-sidebar { display: flex; flex-direction: column; gap: 16px; }
.bp-sidebar-card {
  background: var(--white); border: 1.5px solid var(--border);
  border-radius: 16px; padding: 20px;
  box-shadow: 0 2px 10px rgba(26,35,50,.05);
}
/* Sticky only on desktop - prevents mobile scroll overlap */
@media(min-width:992px) {
  .bp-sidebar-card:first-child { position: sticky; top: 80px; }
}
.bp-sidebar-title {
  font-size: .87rem; font-weight: 800; color: var(--txt);
  margin-bottom: 14px; display: flex; align-items: center; gap: 7px;
}
.bp-sidebar-title i { color: var(--or); }
.btn-bp-consult {
  display: flex; align-items: center; justify-content: center; gap: 7px;
  width: 100%; background: linear-gradient(135deg, #f0a070, var(--or));
  color: #fff; font-weight: 800; font-size: .87rem;
  padding: 12px; border-radius: 11px; text-decoration: none;
  box-shadow: 0 3px 12px rgba(232,86,10,.28); transition: all .2s; margin-bottom: 8px;
}
.btn-bp-consult:hover { color:#fff; transform:translateY(-1px); }
.btn-bp-all {
  display: flex; align-items: center; justify-content: center; gap: 7px;
  width: 100%; background: var(--bg); color: var(--mid);
  font-weight: 700; font-size: .85rem;
  padding: 10px; border-radius: 10px; text-decoration: none;
  border: 1.5px solid var(--border); transition: all .15s;
}
.btn-bp-all:hover { border-color: var(--or); color: var(--or); background: var(--or-lt); }
.bp-quick-link {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 10px; border-radius: 10px; text-decoration: none;
  transition: background .15s; margin-bottom: 3px;
}
.bp-quick-link:hover { background: var(--or-lt); }
.bp-quick-icon {
  width: 34px; height: 34px; border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  font-size: .85rem; flex-shrink: 0;
}
.bp-quick-name { font-size: .8rem; font-weight: 700; color: var(--txt); line-height: 1.2; }
.bp-quick-desc { font-size: .68rem; color: var(--hint); }
</style>

<!-- Breadcrumb -->
<div class="bp-crumb">
  <div class="bp-crumb-inner">
    <a href="/">Home</a>
    <span class="bp-crumb-sep">›</span>
    <a href="/blog.php">Blog</a>
    <span class="bp-crumb-sep">›</span>
    <span class="bp-crumb-active"><?= htmlspecialchars(mb_substr($post['title'], 0, 50)) ?>...</span>
  </div>
</div>

<div class="bp-page">
<div class="bp-inner">

  <!-- MAIN ARTICLE -->
  <div>
    <div class="bp-article">
      <div class="bp-article-body">

        <!-- Category + Title + Meta -->
        <span class="bp-cat-pill"
              style="background:<?= $catBg ?>;color:<?= $catColor ?>;border:1px solid <?= $catColor ?>33;">
          <?= htmlspecialchars($post['cat_name'] ?? 'General') ?>
        </span>
        <h1 class="bp-title"><?= htmlspecialchars($post['title']) ?></h1>
        <div class="bp-meta">
          <span><i class="bi bi-person-fill"></i><?= htmlspecialchars($post['author_name'] ?? 'IH Team') ?></span>
          <span><i class="bi bi-calendar3"></i><?= date('d F Y', strtotime($post['created_at'])) ?></span>
          <?php if (!empty($post['read_time'])): ?>
          <span><i class="bi bi-clock"></i><?= (int)$post['read_time'] ?> min read</span>
          <?php endif; ?>
        </div>

        <!-- Featured Image -->
        <?php if (!empty($post['image'])): ?>
          <img src="/<?= htmlspecialchars($post['image']) ?>"
               alt="<?= htmlspecialchars($post['title']) ?>" class="bp-feat-img">
        <?php else: ?>
          <div class="bp-feat-placeholder">📝</div>
        <?php endif; ?>

        <!-- Excerpt -->
        <?php if (!empty($post['excerpt'])): ?>
        <div class="bp-excerpt">
          <p><?= htmlspecialchars($post['excerpt']) ?></p>
        </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="bp-content">
          <?= $post['content'] ?? '<p>Content coming soon.</p>' ?>
        </div>

        <!-- Share -->
        <div class="bp-share">
          <span class="bp-share-label">Share this article:</span>
          <a href="https://wa.me/?text=<?= urlencode($post['title']) ?>%20<?= urlencode($shareUrl) ?>"
             target="_blank" class="btn-share btn-share-wa">
            <i class="bi bi-whatsapp"></i> WhatsApp
          </a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>"
             target="_blank" class="btn-share btn-share-fb">
            <i class="bi bi-facebook"></i> Facebook
          </a>
          <button class="btn-share btn-share-copy"
                  onclick="navigator.clipboard.writeText('<?= $shareUrl ?>');this.innerHTML='<i class=\'bi bi-check-lg\'></i> Copied!'">
            <i class="bi bi-link-45deg"></i> Copy Link
          </button>
        </div>
      </div>
    </div>

    <!-- Related Posts -->
    <?php if (!empty($related)): ?>
    <div class="bp-related">
      <div style="border-top:1.5px solid var(--border);padding-top:22px;margin-top:22px;">
        <div class="bp-related-title"><i class="bi bi-journal-richtext"></i> Related Articles</div>
        <div class="bp-related-grid">
          <?php foreach ($related as $rel): ?>
          <a href="/blog-post.php?slug=<?= urlencode($rel['slug'] ?? '') ?>&id=<?= $rel['id'] ?>"
             class="bp-rel-card">
            <div class="bp-rel-img">
              <?php if (!empty($rel['image'])): ?>
                <img src="/<?= htmlspecialchars($rel['image']) ?>" alt="">
              <?php else: ?>
                📝
              <?php endif; ?>
            </div>
            <div class="bp-rel-body">
              <div class="bp-rel-name"><?= htmlspecialchars($rel['title']) ?></div>
              <div class="bp-rel-date"><?= date('d M Y', strtotime($rel['created_at'])) ?></div>
              <span class="bp-rel-read">Read more →</span>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- SIDEBAR -->
  <div class="bp-sidebar">
    <!-- CTA -->
    <div class="bp-sidebar-card">
      <div style="font-size:2.5rem;text-align:center;margin-bottom:10px;">🏗️</div>
      <div class="bp-sidebar-title" style="justify-content:center;">Need Help With Your Project?</div>
      <p style="font-size:.8rem;color:var(--light);text-align:center;margin-bottom:16px;line-height:1.5;">
        Get a free site visit and estimate for any construction or interior project.
      </p>
      <a href="/contact.php" class="btn-bp-consult">
        <i class="bi bi-telephone-fill"></i> Book Free Consultation
      </a>
      <a href="/services.php" class="btn-bp-all">
        <i class="bi bi-tools"></i> View All Services
      </a>
    </div>

  </div>

</div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>