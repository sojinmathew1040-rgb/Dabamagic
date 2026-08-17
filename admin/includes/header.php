<?php
/**
 * Daba Magic - Admin Header Include
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$admin_user = $_SESSION['admin_user'] ?? 'Admin';
$admin_email = $_SESSION['admin_email'] ?? 'admin@dabamagic.com';
$admin_role = $_SESSION['admin_role'] ?? 'Super Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title ?? 'Daba Magic Admin Panel'; ?></title>
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="../assets/images/logo.png">

  <!-- Google Fonts: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Admin Custom Stylesheet -->
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>

<div class="admin-wrapper">

  <!-- Sidebar Navigation -->
  <?php include_once __DIR__ . '/sidebar.php'; ?>

  <!-- Main Content Wrapper -->
  <div class="admin-main">

    <!-- Top Sticky Header -->
    <header class="admin-header">
      <div style="display: flex; align-items: center; gap: 1rem;">
        <button type="button" class="mobile-toggle-btn" id="sidebar-toggle">
          <i class="fa-solid fa-bars"></i>
        </button>
        
        <div class="header-search">
          <i class="fa-solid fa-magnifying-glass"></i>
          <input type="text" placeholder="Search bookings, menu items..." id="global-admin-search">
        </div>
      </div>

      <div class="header-actions">
        <!-- Live Clock -->
        <div class="live-clock">
          <i class="fa-solid fa-clock"></i>
          <span id="live-time-display"><?php echo date('d M Y | H:i:s'); ?></span>
        </div>

        <!-- System Notification Bell -->
        <button class="btn-header-action" title="Notifications">
          <i class="fa-solid fa-bell"></i>
          <span class="action-dot"></span>
        </button>

        <!-- Quick View Site -->
        <a href="../index.php" target="_blank" class="btn-header-action" title="View Public Website">
          <i class="fa-solid fa-globe"></i>
        </a>
      </div>
    </header>

    <main class="admin-content">
