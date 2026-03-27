<?php
require_once __DIR__ . '/includes/functions.php';

$slug = sanitizeInput($_GET['slug'] ?? '');
$id   = (int)($_GET['id'] ?? 0);

$post = null;
try {
    if ($slug) {
        $s = $pdo->prepare("SELECT * FROM blogs WHERE slug=? AND status='published' LIMIT 1");
        $s->execute([$slug]); $post = $s->fetch();
    } elseif ($id) {
        $s = $pdo->prepare("SELECT * FROM blogs WHERE id=? AND status='published' LIMIT 1");
        $s->execute([$id]); $post = $s->fetch();
    }
} catch (Exception $e) {}

if (!$post) {
    redirect('/blog.php');
}

// Related posts
$related = [];
try {
    $s = $pdo->prepare(
        "SELECT id, title, slug, excerpt, image, created_at, author_name, read_time
         FROM blogs WHERE status='published' AND id != ? ORDER BY created_at DESC LIMIT 3"
    );
    $s->execute([$post['id']]);
    $related = $s->fetchAll();
} catch (Exception $e) {}

$pageTitle = htmlspecialchars($post['meta_title'] ?: $post['title']) . ' — Incredible Heights';
$pageDesc  = htmlspecialchars($post['meta_desc'] ?: $post['excerpt'] ?: '');
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
.blog-content { line-height: 1.8; font-size: 1.05rem; color: #2d2d2d; }
.blog-content h2,.blog-content h3 { color: var(--dark); font-weight: 700; margin-top: 2rem; margin-bottom: 1rem; }
.blog-content p { margin-bottom: 1.2rem; }
.blog-content ul,.blog-content ol { padding-left: 1.5rem; margin-bottom: 1.2rem; }
.blog-content li { margin-bottom: .4rem; }
.blog-content blockquote { border-left: 4px solid var(--gold); padding: 12px 20px; background: rgba(201,168,76,.07); margin: 1.5rem 0; border-radius: 0 8px 8px 0; }
.blog-content img { max-width: 100%; border-radius: 12px; margin: 1rem 0; }
</style>

<!-- Breadcrumb -->
<div style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:12px 0;">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Home</a></li>
        <li class="breadcrumb-item"><a href="/blog.php" class="text-decoration-none">Blog</a></li>
        <li class="breadcrumb-item active text-truncate" style="max-width:300px;"><?= htmlspecialchars($post['title']) ?></li>
      </ol>
    </nav>
  </div>
</div>

<div class="container py-5">
  <div class="row g-5">
    <!-- Main Article -->
    <div class="col-lg-8">
      <article>
        <!-- Post Header -->
        <div class="mb-4">
          <?php if ($post['category_id']): ?>
          <span class="badge mb-2" style="background:var(--gold);color:var(--dark);font-size:.75rem;">Blog</span>
          <?php endif; ?>
          <h1 class="fw-800 mb-3" style="font-size:2rem;line-height:1.3;color:var(--dark);">
            <?= htmlspecialchars($post['title']) ?>
          </h1>
          <div class="d-flex align-items-center gap-3 text-muted small flex-wrap">
            <span><i class="bi bi-person me-1"></i><?= htmlspecialchars($post['author_name'] ?? 'IH Team') ?></span>
            <span><i class="bi bi-calendar me-1"></i><?= date('d F Y', strtotime($post['created_at'])) ?></span>
            <?php if ($post['read_time']): ?>
            <span><i class="bi bi-clock me-1"></i><?= htmlspecialchars($post['read_time']) ?> min read</span>
            <?php endif; ?>
          </div>
        </div>

        <!-- Featured Image -->
        <?php if (!empty($post['image'])): ?>
        <div class="mb-4" style="border-radius:16px;overflow:hidden;max-height:420px;">
          <img src="/<?= htmlspecialchars($post['image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>"
               style="width:100%;height:420px;object-fit:cover;">
        </div>
        <?php endif; ?>

        <!-- Excerpt -->
        <?php if ($post['excerpt']): ?>
        <div class="mb-4 p-4" style="background:var(--gold-lt);border-radius:12px;border-left:4px solid var(--gold);">
          <p class="mb-0 fw-600" style="color:var(--dark);"><?= htmlspecialchars($post['excerpt']) ?></p>
        </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="blog-content">
          <?= $post['content'] /* Content is stored as HTML from the rich editor */ ?>
        </div>

        <!-- Share Buttons -->
        <div class="mt-5 pt-4 border-top">
          <div class="d-flex align-items-center gap-3 flex-wrap">
            <span class="fw-700 small">Share this article:</span>
            <?php
            $shareUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'ihindia.in') . '/blog-detail.php?slug=' . urlencode($post['slug'] ?? '');
            $shareTitle = urlencode($post['title']);
            ?>
            <a href="https://wa.me/?text=<?= $shareTitle ?>%20<?= urlencode($shareUrl) ?>" target="_blank"
               class="btn btn-sm" style="background:#25d366;color:#fff;border:none;">
              <i class="bi bi-whatsapp me-1"></i>WhatsApp
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($shareUrl) ?>" target="_blank"
               class="btn btn-sm" style="background:#1877f2;color:#fff;border:none;">
              <i class="bi bi-facebook me-1"></i>Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?text=<?= $shareTitle ?>&url=<?= urlencode($shareUrl) ?>" target="_blank"
               class="btn btn-sm" style="background:#1da1f2;color:#fff;border:none;">
              <i class="bi bi-twitter me-1"></i>Twitter
            </a>
          </div>
        </div>
      </article>

      <!-- Related Posts -->
      <?php if ($related): ?>
      <div class="mt-5">
        <h4 class="fw-800 mb-4">📖 Related Articles</h4>
        <div class="row g-3">
        <?php foreach ($related as $rel): ?>
        <div class="col-md-4">
          <div class="ih-card h-100 overflow-hidden" style="transition:transform .2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
            <?php if ($rel['image']): ?>
            <img src="/<?= htmlspecialchars($rel['image']) ?>" alt=""
                 style="width:100%;height:150px;object-fit:cover;">
            <?php else: ?>
            <div style="width:100%;height:150px;background:var(--dark);display:flex;align-items:center;justify-content:center;font-size:2rem;">📝</div>
            <?php endif; ?>
            <div class="p-3">
              <h6 class="fw-700 mb-1" style="font-size:.875rem;line-height:1.4;">
                <a href="/blog-detail.php?slug=<?= urlencode($rel['slug'] ?? '') ?>&id=<?= $rel['id'] ?>"
                   class="text-decoration-none" style="color:var(--dark);">
                  <?= htmlspecialchars($rel['title']) ?>
                </a>
              </h6>
              <div class="text-muted small"><?= date('d M Y', strtotime($rel['created_at'])) ?></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <!-- CTA Box -->
      <div class="ih-card p-4 mb-4 text-center" style="background:var(--dark);">
        <div style="font-size:2.5rem;">🏗️</div>
        <h5 class="fw-800 mt-2" style="color:#fff;">Need Help with Your Project?</h5>
        <p style="color:rgba(255,255,255,.6);font-size:.875rem;" class="mt-2">
          Get a free site visit and estimate for any construction or interior project.
        </p>
        <a href="/contact.php" class="btn btn-gold w-100 mt-2 fw-700">
          <i class="bi bi-telephone me-2"></i>Book Free Consultation
        </a>
        <a href="/services.php" class="btn btn-outline-light w-100 mt-2 fw-700 small">
          View All Services
        </a>
      </div>

      <!-- Quick Links -->
      <div class="ih-card p-4">
        <h6 class="fw-700 mb-3">🔗 Explore More</h6>
        <?php
        $links = [
          ['/services.php','bi-tools','All Services','350+ construction services'],
          ['/products.php','bi-box-seam','Products','Quality construction materials'],
          ['/packages.php','bi-archive','Packages','Complete home packages'],
          ['/plots.php','bi-map','Plots','Available plots in Delhi NCR'],
          ['/portfolio.php','bi-images','Portfolio','Our completed projects'],
        ];
        foreach ($links as [$href,$icon,$title,$desc]):
        ?>
        <a href="<?= $href ?>" class="d-flex align-items-center gap-3 p-2 rounded text-decoration-none mb-2"
           style="color:var(--dark);transition:background .15s;" onmouseover="this.style.background='rgba(201,168,76,.08)'" onmouseout="this.style.background=''">
          <div style="width:36px;height:36px;border-radius:8px;background:var(--gold-lt);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="bi <?= $icon ?> text-gold"></i>
          </div>
          <div>
            <div class="fw-600 small"><?= $title ?></div>
            <div class="text-muted" style="font-size:.72rem;"><?= $desc ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
