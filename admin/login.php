<?php
/**
 * Daba Magic - Admin Login Page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/db_init.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error_msg = "Please fill in all login credentials.";
    } else {
        $stmt = $con->prepare("SELECT id, username, password, full_name, email, role FROM tbl_admin WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Update last login
                    $update_stmt = $con->prepare("UPDATE tbl_admin SET last_login = NOW() WHERE id = ?");
                    $update_stmt->bind_param("i", $user['id']);
                    $update_stmt->execute();

                    // Set Session
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_user'] = $user['username'];
                    $_SESSION['admin_name'] = $user['full_name'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_role'] = $user['role'];

                    header("Location: index.php");
                    exit;
                } else {
                    $error_msg = "Invalid password. Please check your credentials.";
                }
            } else {
                $error_msg = "Admin user not found.";
            }
            $stmt->close();
        } else {
            $error_msg = "Database query error: " . $con->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login - Daba Magic</title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="../assets/images/logo.png">

  <!-- Fonts & Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Custom Admin CSS -->
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-login-body">

  <div class="login-bg-glow login-bg-glow-1"></div>
  <div class="login-bg-glow login-bg-glow-2"></div>

  <div class="login-card">
    
    <div class="login-header">
      <img src="../assets/images/logo.png" alt="Daba Magic Logo" class="login-logo" onerror="this.style.display='none'">
      <h1 class="login-title">DABA <span>MAGIC</span> ADMIN</h1>
      <p class="login-subtitle">Authentic Indian Cuisine Control Panel</p>
    </div>

    <?php if (!empty($error_msg)): ?>
      <div class="alert-danger">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span><?php echo htmlspecialchars($error_msg); ?></span>
      </div>
    <?php endif; ?>

    <form action="login.php" method="POST" autocomplete="off">
      
      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <div class="form-control-wrap">
          <i class="fa-solid fa-user-gear form-icon"></i>
          <input type="text" id="username" name="username" class="form-control" placeholder="Enter admin username" required value="<?php echo htmlspecialchars($_POST['username'] ?? 'admin'); ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="form-control-wrap">
          <i class="fa-solid fa-lock form-icon"></i>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter admin password" required>
          <button type="button" class="password-toggle" id="toggle-pass-btn">
            <i class="fa-solid fa-eye" id="pass-eye-icon"></i>
          </button>
        </div>
      </div>

      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.75rem; font-size: 0.825rem;">
        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; color: var(--text-muted);">
          <input type="checkbox" name="remember" style="accent-color: var(--clr-terracotta);"> Remember me
        </label>
        <span style="color: var(--clr-gold); font-size: 0.775rem;">Default: admin / admin123</span>
      </div>

      <button type="submit" class="btn-admin-primary">
        <span>Access Dashboard</span>
        <i class="fa-solid fa-arrow-right-to-bracket"></i>
      </button>

    </form>

    <div style="text-align: center; margin-top: 2rem; border-top: 1px solid var(--border-subtle); padding-top: 1.25rem;">
      <a href="../index.php" style="color: var(--text-muted); font-size: 0.825rem; transition: var(--transition-fast);" onmouseover="this.style.color='var(--clr-gold)'" onmouseout="this.style.color='var(--text-muted)'">
        <i class="fa-solid fa-house"></i> Return to Daba Magic Main Website
      </a>
    </div>

  </div>

<script>
  // Password toggle
  const togglePassBtn = document.getElementById('toggle-pass-btn');
  const passInput = document.getElementById('password');
  const passEyeIcon = document.getElementById('pass-eye-icon');

  if (togglePassBtn && passInput) {
    togglePassBtn.addEventListener('click', () => {
      if (passInput.type === 'password') {
        passInput.type = 'text';
        passEyeIcon.classList.remove('fa-eye');
        passEyeIcon.classList.add('fa-eye-slash');
      } else {
        passInput.type = 'password';
        passEyeIcon.classList.remove('fa-eye-slash');
        passEyeIcon.classList.add('fa-eye');
      }
    });
  }
</script>
</body>
</html>
