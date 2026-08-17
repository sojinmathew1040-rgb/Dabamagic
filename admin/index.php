<?php
/**
 * Daba Magic - Main Admin Dashboard
 */

require_once __DIR__ . '/includes/auth_check.php';

$page_title = "Dashboard - Daba Magic Admin Panel";

// Process Quick Actions (Confirm / Cancel Reservation)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $res_id = intval($_POST['res_id'] ?? 0);
    $action = $_POST['action'];

    if ($res_id > 0) {
        if ($action === 'confirm_res') {
            $stmt = $con->prepare("UPDATE tbl_reservations SET status = 'Confirmed' WHERE id = ?");
            $stmt->bind_param("i", $res_id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'cancel_res') {
            $stmt = $con->prepare("UPDATE tbl_reservations SET status = 'Cancelled' WHERE id = ?");
            $stmt->bind_param("i", $res_id);
            $stmt->execute();
            $stmt->close();
        } elseif ($action === 'create_res') {
            $code = 'DM-' . rand(100000, 999999);
            $name = trim($_POST['guest_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $guests = intval($_POST['guests'] ?? 2);
            $date = $_POST['reservation_date'] ?? date('Y-m-d');
            $time = $_POST['time_slot'] ?? '19:30 (Dinner)';
            $requests = trim($_POST['special_requests'] ?? '');
            $status = 'Confirmed';

            if (!empty($name) && !empty($phone)) {
                $stmt = $con->prepare("INSERT INTO tbl_reservations (booking_code, guest_name, email, phone, guests, reservation_date, time_slot, special_requests, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssissss", $code, $name, $email, $phone, $guests, $date, $time, $requests, $status);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

// Fetch Metrics from Database
$total_reservations = 0;
$today_reservations = 0;
$pending_reservations = 0;
$active_menu_items = 0;
$total_guests = 0;

$r1 = $con->query("SELECT COUNT(*) AS cnt FROM tbl_reservations");
if ($r1) { $total_reservations = $r1->fetch_assoc()['cnt']; }

$r2 = $con->query("SELECT COUNT(*) AS cnt FROM tbl_reservations WHERE reservation_date = CURDATE()");
if ($r2) { $today_reservations = $r2->fetch_assoc()['cnt']; }

$r3 = $con->query("SELECT COUNT(*) AS cnt FROM tbl_reservations WHERE status = 'Pending'");
if ($r3) { $pending_reservations = $r3->fetch_assoc()['cnt']; }

$r4 = $con->query("SELECT COUNT(*) AS cnt FROM tbl_menu_items WHERE status = 'Active'");
if ($r4) { $active_menu_items = $r4->fetch_assoc()['cnt']; }

$r5 = $con->query("SELECT SUM(guests) AS total FROM tbl_reservations WHERE status = 'Confirmed'");
if ($r5) { $total_guests = $r5->fetch_assoc()['total'] ?? 0; }

// Fetch Recent 6 Reservations
$recent_reservations = [];
$res_query = $con->query("SELECT * FROM tbl_reservations ORDER BY id DESC LIMIT 6");
if ($res_query) {
    while ($row = $res_query->fetch_assoc()) {
        $recent_reservations[] = $row;
    }
}

include_once __DIR__ . '/includes/header.php';
?>

<!-- Page Title Header -->
<div class="page-header">
  <div>
    <h1 class="page-title">Welcome back, <?php echo htmlspecialchars($admin_user); ?> 👋</h1>
    <p class="page-subtitle">Here is the real-time operational overview of Daba Magic today.</p>
  </div>
  <div>
    <button type="button" class="btn-admin-sec" onclick="openModal('add-res-modal')">
      <i class="fa-solid fa-calendar-plus"></i>
      <span>New Table Reservation</span>
    </button>
  </div>
</div>

<!-- KPI Stat Cards -->
<div class="stats-grid">
  
  <div class="stat-card">
    <div class="stat-info">
      <span class="stat-label">Total Reservations</span>
      <span class="stat-value"><?php echo number_format($total_reservations); ?></span>
      <span class="stat-trend up"><i class="fa-solid fa-arrow-trend-up"></i> +14.2% this month</span>
    </div>
    <div class="stat-icon-wrap">
      <i class="fa-solid fa-calendar-days"></i>
    </div>
  </div>

  <div class="stat-card gold">
    <div class="stat-info">
      <span class="stat-label">Today's Bookings</span>
      <span class="stat-value"><?php echo number_format($today_reservations); ?></span>
      <span class="stat-trend neutral"><i class="fa-solid fa-user-group"></i> ~<?php echo $total_guests; ?> Total Guests</span>
    </div>
    <div class="stat-icon-wrap">
      <i class="fa-solid fa-chair"></i>
    </div>
  </div>

  <div class="stat-card green">
    <div class="stat-info">
      <span class="stat-label">Pending Requests</span>
      <span class="stat-value"><?php echo number_format($pending_reservations); ?></span>
      <span class="stat-trend up" style="color: var(--clr-gold);"><i class="fa-solid fa-clock"></i> Requires Approval</span>
    </div>
    <div class="stat-icon-wrap">
      <i class="fa-solid fa-clock-rotate-left"></i>
    </div>
  </div>

  <div class="stat-card purple">
    <div class="stat-info">
      <span class="stat-label">Active Menu Items</span>
      <span class="stat-value"><?php echo number_format($active_menu_items); ?></span>
      <span class="stat-trend up" style="color: #BA68C8;"><i class="fa-solid fa-utensils"></i> Live Dishes</span>
    </div>
    <div class="stat-icon-wrap">
      <i class="fa-solid fa-mortar-pestle"></i>
    </div>
  </div>

</div>

<!-- Interactive Analytics Charts Grid -->
<div class="dashboard-grid">
  
  <!-- Weekly Reservation Activity Bar Chart -->
  <div class="content-card" style="margin-bottom: 0;">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-chart-line"></i>
        <span>Weekly Reservation Volume</span>
      </div>
      <span style="font-size: 0.775rem; color: var(--clr-gold-bright); background: rgba(212,160,23,0.1); padding: 0.25rem 0.75rem; border-radius: var(--radius-full);">
        Live Metrics
      </span>
    </div>
    <div style="height: 260px; position: relative;">
      <canvas id="reservationsChart"></canvas>
    </div>
  </div>

  <!-- Menu Category Distribution -->
  <div class="content-card" style="margin-bottom: 0;">
    <div class="card-header">
      <div class="card-title">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Menu Share</span>
      </div>
    </div>
    <div style="height: 260px; position: relative;">
      <canvas id="categoryChart"></canvas>
    </div>
  </div>

</div>

<!-- Recent Reservations Data Table -->
<div class="content-card" style="margin-top: 2rem;">
  <div class="card-header">
    <div class="card-title">
      <i class="fa-solid fa-receipt"></i>
      <span>Recent Dining Reservations</span>
    </div>
    <a href="reservations.php" class="btn-admin-sec" style="font-size: 0.775rem; padding: 0.4rem 0.9rem;">
      <span>View All Bookings</span>
      <i class="fa-solid fa-arrow-right"></i>
    </a>
  </div>

  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Booking ID</th>
          <th>Guest Details</th>
          <th>Date & Time</th>
          <th>Guests</th>
          <th>Special Notes</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($recent_reservations)): ?>
          <tr>
            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
              No table reservations found.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($recent_reservations as $r): ?>
            <tr>
              <td style="font-family: var(--font-heading); font-weight: 700; color: var(--clr-terracotta-bright);">
                <?php echo htmlspecialchars($r['booking_code']); ?>
              </td>
              <td>
                <strong style="color: var(--text-primary);"><?php echo htmlspecialchars($r['guest_name']); ?></strong><br>
                <small style="color: var(--text-muted);"><i class="fa-solid fa-phone" style="font-size: 0.75rem;"></i> <?php echo htmlspecialchars($r['phone']); ?></small>
              </td>
              <td>
                <span style="color: var(--text-primary); font-weight: 500;"><?php echo date('d M Y', strtotime($r['reservation_date'])); ?></span><br>
                <small style="color: var(--clr-gold);"><?php echo htmlspecialchars($r['time_slot']); ?></small>
              </td>
              <td>
                <span style="font-weight: 600; color: var(--text-primary);"><i class="fa-solid fa-users" style="color: var(--clr-gold);"></i> <?php echo $r['guests']; ?></span>
              </td>
              <td style="max-width: 200px; font-size: 0.825rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                <?php echo !empty($r['special_requests']) ? htmlspecialchars($r['special_requests']) : '<em>None</em>'; ?>
              </td>
              <td>
                <?php
                  $st = strtolower($r['status']);
                  echo "<span class='badge-status {$st}'><i class='fa-solid fa-circle' style='font-size: 6px;'></i> " . htmlspecialchars($r['status']) . "</span>";
                ?>
              </td>
              <td>
                <div class="table-actions">
                  <?php if ($r['status'] === 'Pending'): ?>
                    <form action="index.php" method="POST" style="display:inline;">
                      <input type="hidden" name="res_id" value="<?php echo $r['id']; ?>">
                      <input type="hidden" name="action" value="confirm_res">
                      <button type="submit" class="btn-action-icon success" title="Approve Reservation">
                        <i class="fa-solid fa-check"></i>
                      </button>
                    </form>
                    <form action="index.php" method="POST" style="display:inline;">
                      <input type="hidden" name="res_id" value="<?php echo $r['id']; ?>">
                      <input type="hidden" name="action" value="cancel_res">
                      <button type="submit" class="btn-action-icon danger" title="Decline / Cancel">
                        <i class="fa-solid fa-xmark"></i>
                      </button>
                    </form>
                  <?php else: ?>
                    <button type="button" class="btn-action-icon" onclick="alert('Booking <?php echo $r['booking_code']; ?> is <?php echo $r['status']; ?>.')" title="View Info">
                      <i class="fa-solid fa-eye"></i>
                    </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: New Reservation -->
<div class="admin-modal-overlay" id="add-res-modal">
  <div class="admin-modal-box">
    <button class="modal-close-btn" onclick="closeModal('add-res-modal')">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin-bottom: 1.25rem;">
      <i class="fa-solid fa-calendar-plus"></i> New Table Reservation
    </h3>
    <form action="index.php" method="POST">
      <input type="hidden" name="action" value="create_res">
      
      <div class="form-group">
        <label class="form-label">Guest Full Name</label>
        <input type="text" name="guest_name" class="form-control" required placeholder="e.g. Ananya Roy">
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="tel" name="phone" class="form-control" required placeholder="+353 87 123 4567">
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="guest@example.com">
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label class="form-label">Guests</label>
          <input type="number" name="guests" class="form-control" min="1" max="20" value="2" required>
        </div>
        <div class="form-group">
          <label class="form-label">Date</label>
          <input type="date" name="reservation_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Time Slot</label>
          <select name="time_slot" class="form-control">
            <option value="12:30 (Lunch)">12:30 PM</option>
            <option value="13:30 (Lunch)">1:30 PM</option>
            <option value="18:30 (Dinner)">6:30 PM</option>
            <option value="19:30 (Dinner)" selected>7:30 PM</option>
            <option value="20:30 (Dinner)">8:30 PM</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Special Requests / Notes</label>
        <input type="text" name="special_requests" class="form-control" placeholder="e.g. Window table, birthday celebration">
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn-admin-primary">
          <span>Create & Confirm Booking</span>
          <i class="fa-solid fa-check"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openModal(id) {
    document.getElementById(id).classList.add('active');
  }
  function closeModal(id) {
    document.getElementById(id).classList.remove('active');
  }

  // Chart 1: Weekly Reservations
  const ctx1 = document.getElementById('reservationsChart').getContext('2d');
  new Chart(ctx1, {
    type: 'bar',
    data: {
      labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
      datasets: [{
        label: 'Bookings',
        data: [12, 19, 15, 22, 34, 45, 38],
        backgroundColor: 'rgba(217, 115, 70, 0.75)',
        borderColor: '#D97346',
        borderWidth: 1.5,
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9E8E88' } },
        y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9E8E88' } }
      }
    }
  });

  // Chart 2: Menu Categories
  const ctx2 = document.getElementById('categoryChart').getContext('2d');
  new Chart(ctx2, {
    type: 'doughnut',
    data: {
      labels: ['Biryani', 'Tandoor', 'Curries', 'South Indian', 'Desserts'],
      datasets: [{
        data: [35, 25, 20, 12, 8],
        backgroundColor: [
          '#C86338',
          '#D4A017',
          '#5C9433',
          '#0288D1',
          '#9C27B0'
        ],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: '#D9CCC7', font: { size: 11 } }
        }
      }
    }
  });
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
