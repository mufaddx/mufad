<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) redirect('/user/dashboard.php');

$step    = $_GET['step'] ?? 'request'; // request | reset
$msg     = '';
$msgType = 'success';
$token   = sanitizeInput($_GET['token'] ?? '');

// ── STEP 1: Request reset ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_reset'])) {
    $identifier = sanitizeInput($_POST['identifier'] ?? '');
    if (!$identifier) {
        $msg = 'Please enter your email or phone number.';
        $msgType = 'danger';
    } elseif ($pdo) {
        try {
            $s = $pdo->prepare("SELECT * FROM users WHERE (email=? OR phone=?) AND status=1 LIMIT 1");
            $s->execute([$identifier, $identifier]);
            $user = $s->fetch();
            if ($user) {
                // Generate a reset token
                $resetToken  = bin2hex(random_bytes(32));
                $expiry      = date('Y-m-d H:i:s', time() + 3600); // 1 hour
                // Store token (use a simple column or dedicated table)
                try {
                    $pdo->prepare("UPDATE users SET reset_token=?, reset_token_expiry=? WHERE id=?")
                        ->execute([$resetToken, $expiry, $user['id']]);
                } catch (Exception $e) {
                    // If columns don't exist, store in session as fallback
                    $_SESSION['reset_token']   = $resetToken;
                    $_SESSION['reset_user_id'] = $user['id'];
                    $_SESSION['reset_expiry']  = time() + 3600;
                }
                // In production: send email/SMS with reset link
                // For now: show the link directly (for development/demo)
                $resetLink = '/forgot-password.php?step=reset&token=' . $resetToken;
                $msg = 'A password reset link has been generated. <strong><a href="' . $resetLink . '">Click here to reset your password</a></strong>.<br><small class="text-muted">In production, this link would be sent to your email/phone.</small>';
                $msgType = 'success';
            } else {
                $msg = 'No account found with that email or phone number.';
                $msgType = 'danger';
            }
        } catch (Exception $e) {
            $msg = 'An error occurred. Please try again.';
            $msgType = 'danger';
        }
    }
}

// ── STEP 2: Reset password ────────────────────────────────────
$resetUser = null;
if ($step === 'reset' && $token) {
    // Try DB token first
    try {
        $s = $pdo->prepare("SELECT * FROM users WHERE reset_token=? AND reset_token_expiry > NOW() LIMIT 1");
        $s->execute([$token]);
        $resetUser = $s->fetch();
    } catch (Exception $e) {}

    // Fallback to session token
    if (!$resetUser && !empty($_SESSION['reset_token']) && hash_equals($_SESSION['reset_token'], $token)) {
        if (time() < ($_SESSION['reset_expiry'] ?? 0)) {
            try {
                $s = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
                $s->execute([$_SESSION['reset_user_id']]);
                $resetUser = $s->fetch();
            } catch (Exception $e) {}
        }
    }

    if (!$resetUser) {
        $msg = 'This reset link is invalid or has expired. Please request a new one.';
        $msgType = 'danger';
        $step = 'request';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_reset'])) {
    $newpw   = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $uid     = (int)($_POST['user_id'] ?? 0);

    if (!$newpw || !$confirm) {
        $msg = 'Please fill in both password fields.'; $msgType = 'danger';
    } elseif ($newpw !== $confirm) {
        $msg = 'Passwords do not match.'; $msgType = 'danger';
    } elseif (strlen($newpw) < 6) {
        $msg = 'Password must be at least 6 characters.'; $msgType = 'danger';
    } elseif ($uid && $pdo) {
        try {
            $pdo->prepare("UPDATE users SET password=?, reset_token=NULL, reset_token_expiry=NULL WHERE id=?")
                ->execute([password_hash($newpw, PASSWORD_DEFAULT), $uid]);
            // Clear session token
            unset($_SESSION['reset_token'], $_SESSION['reset_user_id'], $_SESSION['reset_expiry']);
            $msg = 'Password updated successfully! <a href="/login.php" class="alert-link">Click here to login</a>.';
            $msgType = 'success';
            $step = 'request'; // Back to default view
        } catch (Exception $e) {
            $msg = 'Could not reset password. Please try again.'; $msgType = 'danger';
        }
    }
}

$pageTitle = 'Forgot Password — Incredible Heights';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<style>
:root {
  --or:#e8560a; --or2:#c44b08;
  --gd:#c9a84c; --gd2:#a8893a; --gd-lt:#fffbf0;
  --txt:#1a2332; --mid:#4a5568; --light:#718096; --hint:#a0aec0;
  --border:#e8edf5; --bg:#f5f7fb;
}

.fp-page {
  background: #f5f7fb;
  min-height: calc(100vh - 130px);
  padding: 48px 0 80px;
  display: flex; align-items: flex-start; justify-content: center;
}

.fp-wrap { width: 100%; max-width: 480px; padding: 0 16px; }

.fp-card {
  background: #fff;
  border: 1.5px solid var(--border);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 24px rgba(26,35,50,.07);
  padding: 40px 36px 32px;
}
@media(max-width:575px){ .fp-card{ padding: 28px 18px 24px; } }

.fp-icon {
  width: 64px; height: 64px; border-radius: 50%;
  background: var(--gd-lt); border: 2px solid rgba(201,168,76,.25);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 20px; font-size: 2rem;
}

.fp-title {
  font-family: 'Playfair Display', Georgia, serif;
  font-size: 1.5rem; font-weight: 800;
  color: var(--txt); text-align: center; margin-bottom: 6px;
}
.fp-sub {
  font-size: .86rem; color: var(--light);
  text-align: center; margin-bottom: 28px; line-height: 1.5;
}

.lf-label {
  display: block; font-size: .82rem; font-weight: 700;
  color: var(--txt); margin-bottom: 7px;
}
.lf-input {
  width: 100%; border: 1.5px solid var(--border); border-radius: 11px;
  padding: 12px 16px; font-size: .9rem; font-family: 'DM Sans', sans-serif;
  color: var(--txt); background: #fff; outline: none;
  transition: border-color .18s, box-shadow .18s; margin-bottom: 20px;
  box-sizing: border-box;
}
.lf-input:focus { border-color: var(--gd); box-shadow: 0 0 0 3px rgba(201,168,76,.14); }
.lf-input::placeholder { color: #c4cdd6; }

.pw-wrap { position: relative; margin-bottom: 20px; }
.pw-wrap .lf-input { margin-bottom: 0; padding-right: 46px; }
.pw-toggle {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer; color: var(--hint);
  font-size: 1rem; padding: 4px; transition: color .15s;
}
.pw-toggle:hover { color: var(--gd); }

.btn-fp-submit {
  width: 100%; padding: 14px; border: none; border-radius: 12px; cursor: pointer;
  font-weight: 800; font-size: .95rem; display: flex; align-items: center;
  justify-content: center; gap: 8px; transition: all .22s;
  background: linear-gradient(135deg, #d4b060, var(--gd));
  color: #fff;
  box-shadow: 0 5px 20px rgba(201,168,76,.35);
  margin-top: 4px;
}
.btn-fp-submit:hover { transform: translateY(-2px); box-shadow: 0 9px 26px rgba(201,168,76,.45); }

.fp-back {
  display: block; text-align: center; margin-top: 20px;
  font-size: .83rem; color: var(--hint); text-decoration: none;
  padding-top: 16px; border-top: 1px solid var(--border);
  transition: color .15s;
}
.fp-back:hover { color: var(--or); }

.fp-alert {
  border-radius: 12px; border: none; font-size: .85rem;
  padding: 12px 16px; margin-bottom: 20px; line-height: 1.5;
}
.fp-alert-danger  { background: #fef2f2; color: #991b1b; }
.fp-alert-success { background: #f0fdf4; color: #166534; }
.fp-alert a { font-weight: 700; }
.fp-alert a.alert-link-gold { color: var(--gd2); }
.fp-alert a.alert-link-green { color: #166534; }
</style>

<div class="fp-page">
  <div class="fp-wrap">
    <div class="fp-card">

      <div class="fp-icon">🔑</div>
      <div class="fp-title">Forgot Password</div>
      <div class="fp-sub">
        <?= $step === 'reset'
            ? 'Enter your new password below.'
            : 'Enter your email or phone to reset your password.' ?>
      </div>

      <?php if ($msg): ?>
      <div class="fp-alert fp-alert-<?= $msgType ?>">
        <i class="bi bi-<?= $msgType === 'danger' ? 'exclamation-circle' : 'check-circle' ?> me-2"></i><?= $msg ?>
      </div>
      <?php endif; ?>

      <?php if ($step === 'reset' && $resetUser): ?>
      <!-- Step 2: Set new password -->
      <form method="POST">
        <input type="hidden" name="do_reset" value="1">
        <input type="hidden" name="user_id" value="<?= $resetUser['id'] ?>">

        <label class="lf-label">New Password</label>
        <div class="pw-wrap">
          <input type="password" name="new_password" id="fpPw1" class="lf-input"
                 required minlength="6" placeholder="Min 6 characters">
          <button type="button" class="pw-toggle" onclick="toggleFpPw('fpPw1','fpPwIco1')">
            <i class="bi bi-eye" id="fpPwIco1"></i>
          </button>
        </div>

        <label class="lf-label">Confirm New Password</label>
        <div class="pw-wrap">
          <input type="password" name="confirm_password" id="fpPw2" class="lf-input"
                 required placeholder="Repeat your new password">
          <button type="button" class="pw-toggle" onclick="toggleFpPw('fpPw2','fpPwIco2')">
            <i class="bi bi-eye" id="fpPwIco2"></i>
          </button>
        </div>

        <button type="submit" class="btn-fp-submit">
          <i class="bi bi-lock-fill"></i> Reset Password
        </button>
      </form>

      <?php else: ?>
      <!-- Step 1: Request reset -->
      <form method="POST">
        <input type="hidden" name="request_reset" value="1">

        <label class="lf-label">Email or Phone Number</label>
        <input type="text" name="identifier" class="lf-input" required
               placeholder="Enter your registered email or phone" autofocus>

        <button type="submit" class="btn-fp-submit">
          <i class="bi bi-send-fill"></i> Send Reset Link
        </button>
      </form>
      <?php endif; ?>

      <a href="/login.php" class="fp-back">← Back to Login</a>

    </div>
  </div>
</div>

<script>
function toggleFpPw(fieldId, iconId) {
  var f = document.getElementById(fieldId);
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