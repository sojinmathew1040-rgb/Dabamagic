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
    <img src="../assets/images/logo.png" alt="Daba Magic Logo" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\' viewBox=\'0 0 40 40\'%3E%3Crect width=\'40\' height=\'40\' rx=\'8\' fill=\'%23C86338\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' fill=\'%23FFFFFF\' font-size=\'14\' font-weight=\'bold\' font-family=\'sans-serif\' dominant-baseline=\'middle\' text-anchor=\'middle\'%3EDM%3C/text%3E%3C/svg%3E';">
    <div class="sidebar-brand-text">
      DABA <span>MAGIC</span>
    </div>
    <button type="button" class="sidebar-collapse-btn" id="sidebar-collapse-btn" title="Toggle Menu Collapse / Expand">
      <i class="fa-solid fa-angles-left" id="collapse-icon"></i>
    </button>
  </div>

  <!-- Navigation Menu -->
  <div class="sidebar-menu">
    
    <div class="menu-section-label">Main Menu</div>

    <div class="nav-item">
      <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" data-tooltip="Dashboard">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Dashboard</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="reservations.php" class="nav-link <?php echo ($current_page == 'reservations.php') ? 'active' : ''; ?>" data-tooltip="Reservations <?php echo ($pending_count > 0 ? "($pending_count)" : ''); ?>">
        <i class="fa-solid fa-calendar-check"></i>
        <span>Reservations</span>
        <?php if ($pending_count > 0): ?>
          <span class="nav-badge"><?php echo $pending_count; ?></span>
        <?php endif; ?>
      </a>
    </div>

    <div class="nav-item">
      <a href="categories.php" class="nav-link <?php echo ($current_page == 'categories.php') ? 'active' : ''; ?>" data-tooltip="Categories">
        <i class="fa-solid fa-layer-group"></i>
        <span>Categories</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="menu.php" class="nav-link <?php echo ($current_page == 'menu.php') ? 'active' : ''; ?>" data-tooltip="Menu Items">
        <i class="fa-solid fa-utensils"></i>
        <span>Menu Items</span>
      </a>
    </div>

    <div class="menu-section-label">Shortcuts & Site</div>

    <div class="nav-item">
      <a href="../index.php" target="_blank" class="nav-link" data-tooltip="View Website">
        <i class="fa-solid fa-arrow-up-right-from-square"></i>
        <span>View Website</span>
      </a>
    </div>

    <div class="nav-item">
      <a href="logout.php" class="nav-link logout-nav" style="color: var(--clr-red-bright);" data-tooltip="Logout Session">
        <i class="fa-solid fa-right-from-bracket" style="color: var(--clr-red-bright);"></i>
        <span>Logout Session</span>
      </a>
    </div>

  </div>

  <!-- User Card Footer -->
  <div class="sidebar-user" data-tooltip="<?php echo htmlspecialchars($admin_user); ?> (<?php echo htmlspecialchars($admin_role); ?>)">
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
