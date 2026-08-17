<?php
/**
 * Daba Magic - Admin Sidebar Component
 */
$current_page = basename($_SERVER['PHP_SELF']);

// Count pending reservations for navigation badge
$pending_count = 0;
if (isset($con) && !$con->connect_error) {
    $res = $con->query("SELECT COUNT(*) AS cnt FROM tbl_reservations WHERE status = 'Pending'");
    if ($res) {
        $row = $res->fetch_assoc();
        $pending_count = $row['cnt'];
    }
}
?>
<aside class="admin-sidebar" id="admin-sidebar">
  
  <!-- Brand Logo Bar -->
  <div class="sidebar-brand">
    <img src="../assets/images/logo.png" alt="Daba Magic Logo" onerror="this.src='https://via.placeholder.com/40x40?text=DM'">
    <div class="sidebar-brand-text">
      DABA <span>MAGIC</span>
    </div>
  </div>

  <!-- Navigation Menu -->
  <div class="sidebar-menu">
    
    <div class="menu-section-label">Main Menu</div>

    <div class="nav-item">
      <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Dashboard</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="reservations.php" class="nav-link <?php echo ($current_page == 'reservations.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-calendar-check"></i>
        <span>Reservations</span>
        <?php if ($pending_count > 0): ?>
          <span class="nav-badge"><?php echo $pending_count; ?></span>
        <?php endif; ?>
      </a>
    </div>

    <div class="nav-item">
      <a href="menu.php" class="nav-link <?php echo ($current_page == 'menu.php') ? 'active' : ''; ?>">
        <i class="fa-solid fa-utensils"></i>
        <span>Menu Items</span>
      </a>
    </div>

    <div class="menu-section-label">Shortcuts & Site</div>

    <div class="nav-item">
      <a href="../index.php" target="_blank" class="nav-link">
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
        <span>View Website</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="logout.php" class="nav-link" style="color: var(--clr-red-bright);">
        <i class="fa-solid fa-right-from-bracket" style="color: var(--clr-red-bright);"></i>
        <span>Logout Session</span>
      </a>
    </div>

  </div>

  <!-- User Card Footer -->
  <div class="sidebar-user">
    <div class="user-avatar">
      <?php echo strtoupper(substr($admin_user, 0, 1)); ?>
    </div>
    <div class="user-info">
      <div class="user-name"><?php echo htmlspecialchars($admin_user); ?></div>
      <div class="user-role"><?php echo htmlspecialchars($admin_role); ?></div>
    </div>
    <a href="logout.php" class="logout-btn" title="Logout">
      <i class="fa-solid fa-power-off"></i>
    </a>
  </div>

</aside>
