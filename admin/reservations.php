<?php
/**
 * Daba Magic - Admin Reservations Management Page
 */

require_once __DIR__ . '/includes/auth_check.php';

$page_title = "Reservations Management - Daba Magic Admin Panel";

// Process Status Updates & Deletions
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $res_id = intval($_POST['res_id'] ?? 0);
    $action = $_POST['action'];

    if ($action === 'update_status' && $res_id > 0) {
        $new_status = $_POST['status'] ?? 'Confirmed';
        $stmt = $con->prepare("UPDATE tbl_reservations SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $res_id);
        if ($stmt->execute()) {
            $msg = "Reservation status updated to '{$new_status}'.";
        }
        $stmt->close();
    } elseif ($action === 'delete_res' && $res_id > 0) {
        $stmt = $con->prepare("DELETE FROM tbl_reservations WHERE id = ?");
        $stmt->bind_param("i", $res_id);
        if ($stmt->execute()) {
            $msg = "Reservation deleted successfully.";
        }
        $stmt->close();
    } elseif ($action === 'add_res') {
        $code = 'DM-' . rand(100000, 999999);
        $name = trim($_POST['guest_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $guests = intval($_POST['guests'] ?? 2);
        $date = $_POST['reservation_date'] ?? date('Y-m-d');
        $time = $_POST['time_slot'] ?? '19:30 (Dinner)';
        $requests = trim($_POST['special_requests'] ?? '');
        $status = $_POST['status'] ?? 'Confirmed';

        if (!empty($name) && !empty($phone)) {
            $stmt = $con->prepare("INSERT INTO tbl_reservations (booking_code, guest_name, email, phone, guests, reservation_date, time_slot, special_requests, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssissss", $code, $name, $email, $phone, $guests, $date, $time, $requests, $status);
            if ($stmt->execute()) {
                $msg = "New reservation #{$code} added successfully!";
            }
            $stmt->close();
        }
    }
}

// Filter Tab Selection
$filter_status = $_GET['status'] ?? 'all';
$where_clause = "";
if ($filter_status !== 'all') {
    $safe_status = $con->real_escape_string($filter_status);
    $where_clause = "WHERE LOWER(status) = LOWER('$safe_status')";
}

// Fetch Reservations
$all_reservations = [];
$res_query = $con->query("SELECT * FROM tbl_reservations $where_clause ORDER BY id DESC");
if ($res_query) {
    while ($row = $res_query->fetch_assoc()) {
        $all_reservations[] = $row;
    }
}

include_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Table Reservations Management</h1>
    <p class="page-subtitle">Manage guest dining bookings, table assignments, and reservation statuses.</p>
  </div>
  <div>
    <button type="button" class="btn-admin-primary" onclick="openModal('add-res-modal')">
      <i class="fa-solid fa-plus"></i>
      <span>Add New Booking</span>
    </button>
  </div>
</div>

<?php if (!empty($msg)): ?>
  <div style="background: rgba(92,148,51,0.18); border: 1px solid var(--clr-green); color: var(--clr-green-bright); padding: 0.85rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.65rem;">
    <i class="fa-solid fa-circle-check"></i>
    <span><?php echo htmlspecialchars($msg); ?></span>
  </div>
<?php endif; ?>

<!-- Filter & Search Controls -->
<div class="filter-bar">
  <div class="filter-tabs">
    <a href="reservations.php?status=all" class="filter-tab <?php echo ($filter_status == 'all') ? 'active' : ''; ?>">All Bookings</a>
    <a href="reservations.php?status=Pending" class="filter-tab <?php echo ($filter_status == 'Pending') ? 'active' : ''; ?>">Pending</a>
    <a href="reservations.php?status=Confirmed" class="filter-tab <?php echo ($filter_status == 'Confirmed') ? 'active' : ''; ?>">Confirmed</a>
    <a href="reservations.php?status=Cancelled" class="filter-tab <?php echo ($filter_status == 'Cancelled') ? 'active' : ''; ?>">Cancelled</a>
  </div>

  <div style="color: var(--text-muted); font-size: 0.85rem;">
    Total Records: <strong style="color: var(--clr-gold-bright);"><?php echo count($all_reservations); ?></strong>
  </div>
</div>

<!-- Main Reservations Data Table -->
<div class="content-card">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Booking Code</th>
          <th>Guest Info</th>
          <th>Date & Time</th>
          <th>Party Size</th>
          <th>Special Notes</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($all_reservations)): ?>
          <tr>
            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">
              <i class="fa-solid fa-calendar-xmark" style="font-size: 2rem; margin-bottom: 0.5rem; color: var(--clr-terracotta);"></i><br>
              No table bookings found matching this filter criteria.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($all_reservations as $r): ?>
            <tr>
              <td style="font-family: var(--font-heading); font-weight: 700; color: var(--clr-terracotta-bright);">
                <?php echo htmlspecialchars($r['booking_code']); ?>
              </td>
              <td>
                <strong style="color: var(--text-primary); font-size: 0.95rem;"><?php echo htmlspecialchars($r['guest_name']); ?></strong><br>
                <small style="color: var(--text-muted);"><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($r['phone']); ?></small><br>
                <small style="color: var(--clr-gold);"><i class="fa-solid fa-envelope"></i> <?php echo htmlspecialchars($r['email']); ?></small>
              </td>
              <td>
                <span style="color: var(--text-primary); font-weight: 600;"><?php echo date('D, d M Y', strtotime($r['reservation_date'])); ?></span><br>
                <small style="color: var(--clr-gold-bright); font-weight: 500;"><?php echo htmlspecialchars($r['time_slot']); ?></small>
              </td>
              <td>
                <span style="background: var(--bg-admin-surface); border: 1px solid var(--border-subtle); padding: 0.3rem 0.75rem; border-radius: var(--radius-full); font-weight: 600; color: var(--text-primary);">
                  <i class="fa-solid fa-user-group" style="color: var(--clr-terracotta); font-size: 0.8rem;"></i> <?php echo $r['guests']; ?> Guests
                </span>
              </td>
              <td style="max-width: 220px; font-size: 0.825rem; color: var(--text-secondary);">
                <?php echo !empty($r['special_requests']) ? htmlspecialchars($r['special_requests']) : '<em style="color: var(--text-muted)">None</em>'; ?>
              </td>
              <td>
                <?php
                  $st = strtolower($r['status']);
                  echo "<span class='badge-status {$st}'><i class='fa-solid fa-circle' style='font-size: 6px;'></i> " . htmlspecialchars($r['status']) . "</span>";
                ?>
              </td>
              <td>
                <div class="table-actions">
                  
                  <!-- Quick Status Dropdown Form -->
                  <form action="reservations.php?status=<?php echo urlencode($filter_status); ?>" method="POST" style="display: flex; gap: 0.35rem;">
                    <input type="hidden" name="res_id" value="<?php echo $r['id']; ?>">
                    <input type="hidden" name="action" value="update_status">
                    
                    <?php if ($r['status'] !== 'Confirmed'): ?>
                      <button type="submit" name="status" value="Confirmed" class="btn-action-icon success" title="Mark Confirmed">
                        <i class="fa-solid fa-check"></i>
                      </button>
                    <?php endif; ?>

                    <?php if ($r['status'] !== 'Cancelled'): ?>
                      <button type="submit" name="status" value="Cancelled" class="btn-action-icon danger" title="Mark Cancelled">
                        <i class="fa-solid fa-ban"></i>
                      </button>
                    <?php endif; ?>
                  </form>

                  <!-- Delete Reservation Form -->
                  <form action="reservations.php?status=<?php echo urlencode($filter_status); ?>" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete reservation <?php echo $r['booking_code']; ?>?');">
                    <input type="hidden" name="res_id" value="<?php echo $r['id']; ?>">
                    <input type="hidden" name="action" value="delete_res">
                    <button type="submit" class="btn-action-icon danger" title="Delete Booking">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </form>

                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Add Reservation -->
<div class="admin-modal-overlay" id="add-res-modal">
  <div class="admin-modal-box">
    <button class="modal-close-btn" onclick="closeModal('add-res-modal')">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin-bottom: 1.25rem;">
      <i class="fa-solid fa-calendar-plus"></i> Add Table Booking
    </h3>
    <form action="reservations.php?status=<?php echo urlencode($filter_status); ?>" method="POST">
      <input type="hidden" name="action" value="add_res">
      
      <div class="form-group">
        <label class="form-label">Guest Full Name</label>
        <input type="text" name="guest_name" class="form-control" required placeholder="e.g. Ramesh Kumar">
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
          <input type="number" name="guests" class="form-control" min="1" max="25" value="2" required>
        </div>
        <div class="form-group">
          <label class="form-label">Date</label>
          <input type="date" name="reservation_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Time Slot</label>
          <select name="time_slot" class="form-control">
            <option value="12:30 (Lunch)">12:30 PM (Lunch)</option>
            <option value="13:30 (Lunch)">1:30 PM (Lunch)</option>
            <option value="18:30 (Dinner)">6:30 PM (Dinner)</option>
            <option value="19:30 (Dinner)" selected>7:30 PM (Dinner)</option>
            <option value="20:30 (Dinner)">8:30 PM (Dinner)</option>
            <option value="21:15 (Late)">9:15 PM (Late)</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Initial Status</label>
        <select name="status" class="form-control">
          <option value="Confirmed" selected>Confirmed</option>
          <option value="Pending">Pending</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Special Requests / Notes</label>
        <input type="text" name="special_requests" class="form-control" placeholder="e.g. High chair needed, quiet table">
      </div>

      <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
        <button type="submit" class="btn-admin-primary">
          <span>Save Reservation</span>
          <i class="fa-solid fa-check"></i>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function openModal(id) { document.getElementById(id).classList.add('active'); }
  function closeModal(id) { document.getElementById(id).classList.remove('active'); }
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
