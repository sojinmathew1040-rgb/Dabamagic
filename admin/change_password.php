<?php
/**
 * Daba Magic - Admin Change Password Page
 */

require_once __DIR__ . '/includes/auth_check.php';

$page_title = "Change Password - Daba Magic Admin Panel";

$msg = "";
$err = "";

$user_id = $_SESSION['admin_id'] ?? 0;
$admin_user = $_SESSION['admin_user'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? 'admin@dabamagic.com';
$admin_role = $_SESSION['admin_role'] ?? 'Super Admin';

// Process Change Password Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $err = "Please fill in all password fields.";
    } elseif (strlen($new_password) < 6) {
        $err = "New password must be at least 6 characters in length.";
    } elseif ($new_password !== $confirm_password) {
        $err = "New password and Confirm password do not match.";
    } elseif ($user_id <= 0) {
        $err = "Invalid session. Please log in again.";
    } else {
        // Fetch current password hash
        $stmt = $con->prepare("SELECT password FROM tbl_admin WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows === 1) {
            $user_row = $res->fetch_assoc();
            if (password_verify($current_password, $user_row['password'])) {
                if (password_verify($new_password, $user_row['password'])) {
                    $err = "New password cannot be the same as your current password.";
                } else {
                    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt = $con->prepare("UPDATE tbl_admin SET password = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $new_hash, $user_id);
                    if ($update_stmt->execute()) {
                        $msg = "Password updated successfully! Your account is now secured with the new password.";
                    } else {
                        $err = "Database error: Could not update password.";
                    }
                    $update_stmt->close();
                }
            } else {
                $err = "Current password is incorrect. Please try again.";
            }
        } else {
            $err = "User account not found.";
        }
        $stmt->close();
    }
}

include_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Change Password & Security</h1>
    <p class="page-subtitle">Update your operator account credentials and safeguard administrative access.</p>
  </div>
  <div>
    <a href="users.php" class="btn-admin-sec" title="Manage Operators">
      <i class="fa-solid fa-users-gear"></i>
      <span>Manage Operators</span>
    </a>
  </div>
</div>

<?php if (!empty($msg)): ?>
  <div style="background: rgba(92,148,51,0.18); border: 1px solid var(--clr-green); color: var(--clr-green-bright); padding: 1rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.75rem; display: flex; align-items: center; gap: 0.75rem;">
    <i class="fa-solid fa-circle-check" style="font-size: 1.25rem;"></i>
    <span><?php echo htmlspecialchars($msg); ?></span>
  </div>
<?php endif; ?>

<?php if (!empty($err)): ?>
  <div class="alert-danger" style="margin-bottom: 1.75rem;">
    <i class="fa-solid fa-triangle-exclamation" style="font-size: 1.1rem;"></i>
    <span><?php echo htmlspecialchars($err); ?></span>
  </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 1.75rem; align-items: start;">

  <!-- Change Password Form Card -->
  <div class="content-card">
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 1rem;">
      <div style="width: 44px; height: 44px; border-radius: var(--radius-sm); background: rgba(212,160,23,0.15); border: 1px solid var(--border-gold); display: flex; align-items: center; justify-content: center; color: var(--clr-gold-bright); font-size: 1.25rem;">
        <i class="fa-solid fa-key"></i>
      </div>
      <div>
        <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); font-size: 1.15rem; margin: 0;">Update Account Password</h3>
        <small style="color: var(--text-muted);">Ensure your new password is at least 6 characters long.</small>
      </div>
    </div>

    <form action="change_password.php" method="POST" autocomplete="off" id="change-pass-form">
      <input type="hidden" name="action" value="change_password">

      <!-- Current Password -->
      <div class="form-group">
        <label class="form-label" for="current_password">Current Password</label>
        <div class="form-control-wrap">
          <i class="fa-solid fa-lock form-icon"></i>
          <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter your current password" required>
          <button type="button" class="password-toggle" onclick="toggleFieldPass('current_password', 'eye-curr')">
            <i class="fa-solid fa-eye" id="eye-curr"></i>
          </button>
        </div>
      </div>

      <!-- New Password -->
      <div class="form-group">
        <label class="form-label" for="new_password">New Password</label>
        <div class="form-control-wrap">
          <i class="fa-solid fa-shield-halved form-icon"></i>
          <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password (min. 6 characters)" required minlength="6" onkeyup="checkPasswordStrength(this.value)">
          <button type="button" class="password-toggle" onclick="toggleFieldPass('new_password', 'eye-new')">
            <i class="fa-solid fa-eye" id="eye-new"></i>
          </button>
        </div>
        <!-- Live Strength Indicator -->
        <div style="margin-top: 0.5rem; display: flex; align-items: center; gap: 0.65rem;">
          <div style="flex: 1; height: 5px; background: var(--bg-admin-surface); border-radius: 3px; overflow: hidden; border: 1px solid var(--border-subtle);">
            <div id="strength-bar" style="width: 0%; height: 100%; transition: width 0.3s ease, background 0.3s ease; background: var(--clr-red);"></div>
          </div>
          <span id="strength-text" style="font-size: 0.75rem; color: var(--text-muted); min-width: 60px;">Too short</span>
        </div>
      </div>

      <!-- Confirm New Password -->
      <div class="form-group">
        <label class="form-label" for="confirm_password">Confirm New Password</label>
        <div class="form-control-wrap">
          <i class="fa-solid fa-circle-check form-icon"></i>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter new password to confirm" required onkeyup="checkPasswordMatch()">
          <button type="button" class="password-toggle" onclick="toggleFieldPass('confirm_password', 'eye-conf')">
            <i class="fa-solid fa-eye" id="eye-conf"></i>
          </button>
        </div>
        <small id="match-status" style="display: block; margin-top: 0.35rem; font-size: 0.775rem; color: var(--text-muted);"></small>
      </div>

      <div style="margin-top: 2rem; display: flex; gap: 1rem;">
        <button type="submit" class="btn-admin-primary" id="btn-submit-pass">
          <i class="fa-solid fa-check"></i>
          <span>Save New Password</span>
        </button>
        <button type="reset" class="btn-cropper-action" onclick="resetStrength()">
          <i class="fa-solid fa-arrow-rotate-left"></i>
          <span>Reset Form</span>
        </button>
      </div>

    </form>
  </div>

  <!-- Account Details & Security Guidelines Card -->
  <div>
    <!-- Current User Info Box -->
    <div class="content-card" style="margin-bottom: 1.5rem;">
      <h4 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin-bottom: 1.25rem; font-size: 1.05rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-id-badge"></i> Active Operator Profile
      </h4>

      <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.25rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle);">
        <div class="user-avatar" style="width: 54px; height: 54px; font-size: 1.4rem;">
          <?php echo strtoupper(substr($admin_user, 0, 1)); ?>
        </div>
        <div>
          <strong style="color: var(--text-primary); font-size: 1.05rem; display: block; font-family: var(--font-heading);">
            <?php echo htmlspecialchars($admin_user); ?>
          </strong>
          <span style="background: rgba(212,160,23,0.15); color: var(--clr-gold-bright); font-size: 0.775rem; padding: 0.15rem 0.55rem; border-radius: var(--radius-full); font-weight: 600;">
            <i class="fa-solid fa-shield"></i> <?php echo htmlspecialchars($admin_role); ?>
          </span>
        </div>
      </div>

      <div style="font-size: 0.85rem; color: var(--text-secondary); display: flex; flex-direction: column; gap: 0.65rem;">
        <div style="display: flex; justify-content: space-between;">
          <span style="color: var(--text-muted);">Email Address:</span>
          <strong><?php echo htmlspecialchars($admin_email); ?></strong>
        </div>
        <div style="display: flex; justify-content: space-between;">
          <span style="color: var(--text-muted);">Operator Status:</span>
          <span class="badge-status active"><i class="fa-solid fa-circle" style="font-size: 6px;"></i> Active</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
          <span style="color: var(--text-muted);">Account ID:</span>
          <code>#<?php echo $user_id; ?></code>
        </div>
      </div>
    </div>

    <!-- Security Tips Box -->
    <div class="content-card" style="background: rgba(26, 17, 15, 0.4); border-color: rgba(200,99,56,0.25);">
      <h4 style="font-family: var(--font-heading); color: var(--clr-terracotta-bright); margin-bottom: 0.75rem; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fa-solid fa-shield-virus"></i> Password Guidelines
      </h4>
      <ul style="color: var(--text-muted); font-size: 0.825rem; padding-left: 1.25rem; line-height: 1.6; margin: 0;">
        <li>Use a minimum of 6 characters (8+ recommended).</li>
        <li>Combine letters, numbers, and special symbols for stronger protection.</li>
        <li>Do not share operator login credentials across multiple staff members.</li>
      </ul>
    </div>
  </div>

</div>

<script>
  function toggleFieldPass(fieldId, eyeId) {
    const input = document.getElementById(fieldId);
    const eye = document.getElementById(eyeId);
    if (input.type === 'password') {
      input.type = 'text';
      eye.classList.remove('fa-eye');
      eye.classList.add('fa-eye-slash');
    } else {
      input.type = 'password';
      eye.classList.remove('fa-eye-slash');
      eye.classList.add('fa-eye');
    }
  }

  function checkPasswordStrength(pass) {
    const bar = document.getElementById('strength-bar');
    const text = document.getElementById('strength-text');
    let strength = 0;

    if (pass.length >= 6) strength += 30;
    if (pass.length >= 10) strength += 20;
    if (/[A-Z]/.test(pass)) strength += 20;
    if (/[0-9]/.test(pass)) strength += 15;
    if (/[^A-Za-z0-9]/.test(pass)) strength += 15;

    bar.style.width = strength + '%';

    if (pass.length < 6) {
      bar.style.background = 'var(--clr-red)';
      text.textContent = 'Too short';
      text.style.color = 'var(--clr-red-bright)';
    } else if (strength < 50) {
      bar.style.background = 'var(--clr-terracotta)';
      text.textContent = 'Weak';
      text.style.color = 'var(--clr-terracotta-bright)';
    } else if (strength < 80) {
      bar.style.background = 'var(--clr-gold)';
      text.textContent = 'Good';
      text.style.color = 'var(--clr-gold-bright)';
    } else {
      bar.style.background = 'var(--clr-green)';
      text.textContent = 'Strong';
      text.style.color = 'var(--clr-green-bright)';
    }

    checkPasswordMatch();
  }

  function checkPasswordMatch() {
    const newPass = document.getElementById('new_password').value;
    const confPass = document.getElementById('confirm_password').value;
    const matchStatus = document.getElementById('match-status');

    if (!confPass) {
      matchStatus.textContent = '';
      return;
    }

    if (newPass === confPass) {
      matchStatus.textContent = '✓ Passwords match';
      matchStatus.style.color = 'var(--clr-green-bright)';
    } else {
      matchStatus.textContent = '✗ Passwords do not match';
      matchStatus.style.color = 'var(--clr-red-bright)';
    }
  }

  function resetStrength() {
    document.getElementById('strength-bar').style.width = '0%';
    document.getElementById('strength-text').textContent = 'Too short';
    document.getElementById('strength-text').style.color = 'var(--text-muted)';
    document.getElementById('match-status').textContent = '';
  }
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
