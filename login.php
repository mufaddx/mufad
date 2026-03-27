<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect($_GET['redirect'] ?? '/user/account.php');
}

$error  = '';
$tab    = $_GET['tab'] ?? 'login';

// ── HANDLE LOGIN ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email    = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
        $tab   = 'login';
    } elseif ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE (email = ? OR phone = ?) AND status = 1 LIMIT 1");
            $stmt->execute([$email, $email]);
            $user = $stmt->fetch();
            if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
                loginUser($user);
                $redirect = sanitizeInput($_GET['redirect'] ?? '/user/account.php');
                redirect($redirect);
            } else {
                $error = 'Invalid email/phone or password.';
                $tab   = 'login';
            }
        } catch (Exception $e) {
            $error = 'Login error. Please try again.';
            $tab   = 'login';
        }
    } else {
        $error = 'Service unavailable. Please try again later.';
        $tab   = 'login';
    }
}

// ── HANDLE REGISTER ───────────────────────────────────────────────────
$regError   = '';
$regSuccess = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $name     = sanitizeInput($_POST['reg_name'] ?? '');
    $email    = sanitizeInput($_POST['reg_email'] ?? '');
    $phone    = sanitizeInput($_POST['reg_phone'] ?? '');
    $password = $_POST['reg_password'] ?? '';
    $confirm  = $_POST['reg_confirm'] ?? '';
    $tab      = 'register';

    if (!$name)                         $regError = 'Full name is required.';
    elseif (!$phone && !$email)         $regError = 'Email or phone is required.';
    elseif (strlen($password) < 6)      $regError = 'Password must be at least 6 characters.';
    elseif ($password !== $confirm)     $regError = 'Passwords do not match.';
    elseif ($pdo) {
        try {
            $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ? LIMIT 1");
            $check->execute([$email, $phone]);
            if ($check->fetch()) {
                $regError = 'An account with this email or phone already exists. Please login.';
            } else {
                $hashedPw = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT INTO users (name, email, phone, password, status) VALUES (?,?,?,?,1)")
                    ->execute([$name, $email ?: null, $phone ?: null, $hashedPw]);
                $newId = $pdo->lastInsertId();
                $newUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $newUser->execute([$newId]);
                $newUser = $newUser->fetch();
                loginUser($newUser);
                setFlash('success', 'Welcome to Incredible Heights, ' . $name . '! 🎉');
                redirect('/user/account.php');
            }
        } catch (Exception $e) {
            $regError = 'Registration error. Please try again.';
        }
    } else {
        $regError = 'Service unavailable. Please try again later.';
    }
}

$pageTitle = 'Login / Register — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<!-- ═══════════════════════════════════════
     LOGIN PAGE STYLES — FULLY WHITE THEME
     ═══════════════════════════════════════ -->
<style>
:root {
  --or:#e8560a; --or2:#c44b08; --or-lt:#fff5f0; --or-b:rgba(232,86,10,.18);
  --bl:#1565c0; --bl-lt:#f0f5ff; --bl-b:rgba(21,101,192,.18);
  --gr:#2e7d32; --gr-lt:#f0faf0;
  --gd:#c9a84c; --gd-lt:#fffbf0;
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb; --white:#ffffff;
}

/* ── PAGE WRAPPER ── */
.login-page {
  background: #f5f7fb;
  min-height: calc(100vh - 130px);
  padding: 32px 0 64px;
}


/* ── MAIN CARD ── */
.login-wrap {
  max-width: 680px; margin: 0 auto; padding: 0 16px;
}


/* ── LEFT FORM CARD ── */
.login-form-card {
  background: #fff;
  border: 1.5px solid var(--border);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(26,35,50,.07);
}

/* Tabs */
.login-tabs {
  display: flex;
  border-bottom: 1.5px solid var(--border);
  background: var(--bg);
}
.login-tab {
  flex: 1; display: flex; align-items: center; justify-content: center; gap: 7px;
  padding: 16px 20px; font-size: .88rem; font-weight: 700;
  text-decoration: none; color: var(--hint);
  border-bottom: 2.5px solid transparent;
  transition: all .18s; background: transparent;
  border-right: 1.5px solid var(--border);
}
.login-tab:last-child { border-right: none; }
.login-tab.active { color: var(--or); border-bottom-color: var(--or); background: #fff; }
.login-tab:hover:not(.active) { color: var(--mid); background: #fff; }

/* Form body */
.login-form-body { padding: 32px; }
@media(max-width:575px){ .login-form-body{ padding: 20px 16px; } }

.form-title { font-family: 'Playfair Display', Georgia, serif; font-size: 1.5rem; font-weight: 800; color: var(--txt); margin-bottom: 5px; }
.form-sub   { font-size: .88rem; color: var(--light); margin-bottom: 24px; }

/* Form controls */
.lf-label {
  display: block; font-size: .82rem; font-weight: 700;
  color: var(--txt); margin-bottom: 7px;
}
.lf-input {
  width: 100%; border: 1.5px solid var(--border); border-radius: 11px;
  padding: 12px 16px; font-size: .9rem; font-family: 'DM Sans', sans-serif;
  color: var(--txt); background: #fff; outline: none; transition: border-color .18s, box-shadow .18s;
  margin-bottom: 18px;
}
.lf-input:focus { border-color: var(--or); box-shadow: 0 0 0 3px rgba(232,86,10,.12); }
.lf-input::placeholder { color: #c4cdd6; }

/* Password wrapper */
.pw-wrap { position: relative; margin-bottom: 18px; }
.pw-wrap .lf-input { margin-bottom: 0; padding-right: 46px; }
.pw-toggle {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; color: var(--hint);
  font-size: 1rem; padding: 4px; transition: color .15s;
}
.pw-toggle:hover { color: var(--or); }

/* Forgot */
.forgot-link {
  display: block; text-align: right; font-size: .8rem;
  color: var(--hint); text-decoration: none; margin-bottom: 20px;
  transition: color .15s;
}
.forgot-link:hover { color: var(--or); }

/* Submit buttons */
.btn-login-submit {
  width: 100%; padding: 14px; border: none; border-radius: 12px; cursor: pointer;
  font-weight: 800; font-size: .95rem; display: flex; align-items: center;
  justify-content: center; gap: 8px; transition: all .22s;
  background: linear-gradient(135deg, #f0a070, var(--or));
  color: #fff;
  box-shadow: 0 5px 20px rgba(232,86,10,.3);
}
.btn-login-submit:hover { transform: translateY(-2px); box-shadow: 0 9px 26px rgba(232,86,10,.4); }

.terms-note { text-align: center; margin-top: 14px; font-size: .76rem; color: var(--hint); }
.terms-note a { color: var(--or); text-decoration: none; }






/* Benefits body */







/* Security note */



/* Switch link */
.switch-tab-link {
  text-align: center;
  margin-top: 20px;
  font-size: .84rem;
  color: var(--light);
  padding: 14px 0 4px;
  border-top: 1px solid var(--border);
}
.switch-tab-link a {
  color: var(--or);
  font-weight: 700;
  text-decoration: none;
  margin-left: 3px;
}
.switch-tab-link a:hover { text-decoration: underline; }

/* Alert overrides */
.alert { border-radius: 12px !important; border: none !important; font-size: .86rem; margin-bottom: 20px; }
.alert-danger  { background: #fef2f2; color: #991b1b; }
.alert-success { background: #f0fdf4; color: #166534; }
</style>

<div class="login-page">

  <!-- Main grid -->
  <div class="login-wrap">

    <!-- ── LEFT: FORM CARD ── -->
    <div class="login-form-card">

      <!-- Tabs -->
      <div class="login-tabs">
        <a href="?tab=login<?= !empty($_GET['redirect']) ? '&redirect='.urlencode($_GET['redirect']) : '' ?>"
           class="login-tab <?= $tab !== 'register' ? 'active' : '' ?>">
          <i class="bi bi-box-arrow-in-right"></i> Login
        </a>
        <a href="?tab=register" class="login-tab <?= $tab === 'register' ? 'active' : '' ?>">
          <i class="bi bi-person-plus"></i> Create Account
        </a>
      </div>

      <div class="login-form-body">

        <?php if ($tab !== 'register'): ?>
        <!-- ─ LOGIN FORM ─ -->
        <div class="form-title">Welcome back!</div>
        <div class="form-sub">Login to track your orders, bookings and more.</div>

        <?php if ($error): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
          <input type="hidden" name="login" value="1">

          <label class="lf-label">Email or Phone</label>
          <input type="text" name="email" class="lf-input"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                 placeholder="Enter email or phone number" required autofocus>

          <label class="lf-label">Password</label>
          <div class="pw-wrap">
            <input type="password" name="password" class="lf-input" id="loginPw"
                   placeholder="Your password" required>
            <button type="button" class="pw-toggle" onclick="togglePw('loginPw','loginPwIco')">
              <i class="bi bi-eye" id="loginPwIco"></i>
            </button>
          </div>

          <a href="/forgot-password.php" class="forgot-link">Forgot password?</a>

          <button type="submit" class="btn-login-submit">
            <i class="bi bi-box-arrow-in-right"></i> Login to My Account
          </button>
        </form>

        <div class="switch-tab-link">
          Don't have an account? <a href="?tab=register">Create one free →</a>
        </div>

        <?php else: ?>
        <!-- ─ REGISTER FORM ─ -->
        <div class="form-title">Create your account</div>
        <div class="form-sub">Join thousands of happy customers across Delhi NCR.</div>

        <?php if ($regError): ?>
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($regError) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
          <input type="hidden" name="register" value="1">

          <label class="lf-label">Full Name *</label>
          <input type="text" name="reg_name" class="lf-input" required
                 value="<?= htmlspecialchars($_POST['reg_name'] ?? '') ?>"
                 placeholder="Your full name">

          <label class="lf-label">Phone Number</label>
          <input type="tel" name="reg_phone" class="lf-input"
                 value="<?= htmlspecialchars($_POST['reg_phone'] ?? '') ?>"
                 placeholder="+91 XXXXX XXXXX">

          <label class="lf-label">Email Address</label>
          <input type="email" name="reg_email" class="lf-input"
                 value="<?= htmlspecialchars($_POST['reg_email'] ?? '') ?>"
                 placeholder="your@email.com">

          <label class="lf-label">Password *</label>
          <div class="pw-wrap">
            <input type="password" name="reg_password" id="regPw" class="lf-input" required
                   placeholder="At least 6 characters" minlength="6">
            <button type="button" class="pw-toggle" onclick="togglePw('regPw','regPwIco')">
              <i class="bi bi-eye" id="regPwIco"></i>
            </button>
          </div>

          <label class="lf-label">Confirm Password *</label>
          <div class="pw-wrap">
            <input type="password" name="reg_confirm" id="regCf" class="lf-input" required
                   placeholder="Repeat your password">
            <button type="button" class="pw-toggle" onclick="togglePw('regCf','regCfIco')">
              <i class="bi bi-eye" id="regCfIco"></i>
            </button>
          </div>

          <button type="submit" class="btn-login-submit">
            <i class="bi bi-person-check"></i> Create My Account
          </button>

          <div class="terms-note">
            By registering you agree to our <a href="/terms.php">Terms of Service</a>.
          </div>
        </form>

        <div class="switch-tab-link">
          Already have an account? <a href="?tab=login">Login here →</a>
        </div>
        <?php endif; ?>

      </div>
    </div><!-- end form card -->

  </div><!-- end grid -->
</div><!-- end login-page -->

<script>
function togglePw(fieldId, iconId) {
  var f   = document.getElementById(fieldId);
  var ico = document.getElementById(iconId);
  if (f.type === 'password') {
    f.type = 'text';
    if (ico) ico.className = 'bi bi-eye-slash';
  } else {
    f.type = 'password';
    if (ico) ico.className = 'bi bi-eye';
  }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>