<?php
/**
 * Daba Magic - Admin Order Management & Live Kitchen Monitor
 */

require_once __DIR__ . '/includes/auth_check.php';

$page_title = "Customer Orders - Daba Magic Admin Panel";

$msg = "";
$err = "";

// Process Status Updates & Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_order_status') {
        $order_id = intval($_POST['order_id'] ?? 0);
        $new_status = $_POST['order_status'] ?? 'Received';

        if ($order_id > 0) {
            $stmt = $con->prepare("UPDATE tbl_orders SET order_status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $order_id);
            if ($stmt->execute()) {
                $msg = "Order status updated to '{$new_status}'.";
            }
            $stmt->close();
        }
    } elseif ($action === 'update_payment_status') {
        $order_id = intval($_POST['order_id'] ?? 0);
        $payment_status = $_POST['payment_status'] ?? 'Paid';

        if ($order_id > 0) {
            $stmt = $con->prepare("UPDATE tbl_orders SET payment_status = ? WHERE id = ?");
            $stmt->bind_param("si", $payment_status, $order_id);
            if ($stmt->execute()) {
                $msg = "Payment status updated to '{$payment_status}'.";
            }
            $stmt->close();
        }
    } elseif ($action === 'delete_order') {
        $order_id = intval($_POST['order_id'] ?? 0);
        if ($order_id > 0) {
            $stmt = $con->prepare("DELETE FROM tbl_orders WHERE id = ?");
            $stmt->bind_param("i", $order_id);
            if ($stmt->execute()) {
                $msg = "Order record deleted successfully.";
            }
            $stmt->close();
        }
    }
}

// Filter and Search
$filter_status = $_POST['filter_status'] ?? ($_GET['status'] ?? 'all');
$search_q = trim($_GET['q'] ?? '');

$where_parts = [];
if ($filter_status !== 'all') {
    $safe_st = $con->real_escape_string($filter_status);
    $where_parts[] = "order_status = '$safe_st'";
}
if (!empty($search_q)) {
    $safe_q = $con->real_escape_string($search_q);
    $where_parts[] = "(order_number LIKE '%$safe_q%' OR customer_name LIKE '%$safe_q%' OR customer_phone LIKE '%$safe_q%' OR customer_email LIKE '%$safe_q%')";
}
$where_sql = !empty($where_parts) ? "WHERE " . implode(" AND ", $where_parts) : "";

// Fetch Orders
$orders = [];
$ord_query = $con->query("SELECT * FROM tbl_orders $where_sql ORDER BY id DESC");
if ($ord_query) {
    while ($row = $ord_query->fetch_assoc()) {
        // Fetch ordered items
        $o_id = $row['id'];
        $item_res = $con->query("SELECT * FROM tbl_order_items WHERE order_id = $o_id");
        $items = [];
        if ($item_res) {
            while ($i_row = $item_res->fetch_assoc()) {
                $items[] = $i_row;
            }
        }
        $row['items'] = $items;
        $orders[] = $row;
    }
}

// KPI Metrics
$total_orders_cnt = 0;
$total_revenue = 0.00;
$active_kitchen_cnt = 0;
$today_orders_cnt = 0;

$kpi_query = $con->query("SELECT order_status, payment_status, total_amount, created_at FROM tbl_orders");
if ($kpi_query) {
    $today_str = date('Y-m-d');
    while ($k_row = $kpi_query->fetch_assoc()) {
        $total_orders_cnt++;
        if ($k_row['payment_status'] === 'Paid') {
            $total_revenue += floatval($k_row['total_amount']);
        }
        if (in_array($k_row['order_status'], ['Received', 'Preparing', 'Ready', 'OutForDelivery'])) {
            $active_kitchen_cnt++;
        }
        if (substr($k_row['created_at'], 0, 10) === $today_str) {
            $today_orders_cnt++;
        }
    }
}

include_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
  <div>
    <h1 class="page-title">Customer Orders & Kitchen Monitor</h1>
    <p class="page-subtitle">Track online customer food orders, monitor Razorpay payments, and manage fulfillment statuses.</p>
  </div>
  <div style="display: flex; gap: 0.75rem;">
    <a href="../menu.php" target="_blank" class="btn-admin-sec" title="Open Customer Menu">
      <i class="fa-solid fa-arrow-up-right-from-square"></i>
      <span>Customer Menu</span>
    </a>
  </div>
</div>

<?php if (!empty($msg)): ?>
  <div style="background: rgba(92,148,51,0.18); border: 1px solid var(--clr-green); color: var(--clr-green-bright); padding: 0.9rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.65rem;">
    <i class="fa-solid fa-circle-check" style="font-size: 1.15rem;"></i>
    <span><?php echo htmlspecialchars($msg); ?></span>
  </div>
<?php endif; ?>

<!-- KPI Metrics Grid -->
<div class="dashboard-grid" style="margin-bottom: 1.75rem;">
  
  <div class="kpi-card">
    <div class="kpi-icon" style="background: rgba(212, 160, 23, 0.15); color: var(--clr-gold-bright); border: 1px solid var(--border-gold);">
      <i class="fa-solid fa-receipt"></i>
    </div>
    <div class="kpi-info">
      <span class="kpi-title">Total Orders</span>
      <h3 class="kpi-value"><?php echo $total_orders_cnt; ?></h3>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="background: rgba(92, 148, 51, 0.15); color: var(--clr-green-bright); border: 1px solid rgba(92, 148, 51, 0.3);">
      <i class="fa-solid fa-circle-dollar-to-slot"></i>
    </div>
    <div class="kpi-info">
      <span class="kpi-title">Paid Revenue</span>
      <h3 class="kpi-value">€<?php echo number_format($total_revenue, 2); ?></h3>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="background: rgba(200, 99, 56, 0.15); color: var(--clr-terracotta-bright); border: 1px solid rgba(200, 99, 56, 0.3);">
      <i class="fa-solid fa-fire-burner"></i>
    </div>
    <div class="kpi-info">
      <span class="kpi-title">Active Kitchen Orders</span>
      <h3 class="kpi-value"><?php echo $active_kitchen_cnt; ?></h3>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="background: rgba(82, 143, 240, 0.15); color: #528FF0; border: 1px solid rgba(82, 143, 240, 0.3);">
      <i class="fa-solid fa-calendar-day"></i>
    </div>
    <div class="kpi-info">
      <span class="kpi-title">Today's Orders</span>
      <h3 class="kpi-value"><?php echo $today_orders_cnt; ?></h3>
    </div>
  </div>

</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <div class="filter-tabs">
    <a href="orders.php?status=all" class="filter-tab <?php echo ($filter_status === 'all') ? 'active' : ''; ?>">All Orders</a>
    <a href="orders.php?status=Received" class="filter-tab <?php echo ($filter_status === 'Received') ? 'active' : ''; ?>">Received</a>
    <a href="orders.php?status=Preparing" class="filter-tab <?php echo ($filter_status === 'Preparing') ? 'active' : ''; ?>">Preparing</a>
    <a href="orders.php?status=OutForDelivery" class="filter-tab <?php echo ($filter_status === 'OutForDelivery') ? 'active' : ''; ?>">Out for Delivery</a>
    <a href="orders.php?status=Delivered" class="filter-tab <?php echo ($filter_status === 'Delivered') ? 'active' : ''; ?>">Delivered</a>
    <a href="orders.php?status=Cancelled" class="filter-tab <?php echo ($filter_status === 'Cancelled') ? 'active' : ''; ?>">Cancelled</a>
  </div>

  <form action="orders.php" method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
    <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
    <div class="header-search" style="width: 260px;">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="q" value="<?php echo htmlspecialchars($search_q); ?>" placeholder="Order #, customer, phone...">
    </div>
    <?php if (!empty($search_q)): ?>
      <a href="orders.php?status=<?php echo urlencode($filter_status); ?>" class="btn-cropper-action" title="Clear Search">
        <i class="fa-solid fa-xmark"></i>
      </a>
    <?php endif; ?>
  </form>
</div>

<!-- Orders Table Card -->
<div class="content-card">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Order Info</th>
          <th>Customer Details</th>
          <th>Type / Location</th>
          <th>Payment</th>
          <th>Kitchen Status</th>
          <th>Amount</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr>
            <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem;">
              <i class="fa-solid fa-receipt" style="font-size: 2.5rem; margin-bottom: 0.5rem; color: var(--clr-terracotta);"></i><br>
              No orders found matching the selected filter.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($orders as $ord): ?>
            <tr>
              <!-- Order Info -->
              <td>
                <a href="javascript:void(0)" onclick="openOrderModal(<?php echo htmlspecialchars(json_encode($ord)); ?>)" style="text-decoration: none; color: var(--clr-gold-bright); font-weight: 700; font-family: monospace; font-size: 0.95rem;">
                  <?php echo htmlspecialchars($ord['order_number']); ?>
                </a><br>
                <small style="color: var(--text-muted); font-size: 0.775rem;">
                  <i class="fa-regular fa-clock"></i> <?php echo date('d M Y, H:i', strtotime($ord['created_at'])); ?>
                </small>
              </td>

              <!-- Customer Details -->
              <td>
                <strong style="color: var(--text-primary); font-size: 0.9rem;">
                  <?php echo htmlspecialchars($ord['customer_name']); ?>
                </strong><br>
                <a href="tel:<?php echo htmlspecialchars($ord['customer_phone']); ?>" style="color: var(--text-secondary); text-decoration: none; font-size: 0.8rem;">
                  <i class="fa-solid fa-phone" style="font-size: 0.7rem; color: var(--clr-gold);"></i> <?php echo htmlspecialchars($ord['customer_phone']); ?>
                </a>
              </td>

              <!-- Type / Table -->
              <td>
                <?php if ($ord['order_type'] === 'Delivery'): ?>
                  <span style="background: rgba(200,99,56,0.15); color: var(--clr-terracotta-bright); padding: 0.2rem 0.6rem; border-radius: var(--radius-full); font-size: 0.775rem; font-weight: 600;">
                    <i class="fa-solid fa-motorcycle"></i> Delivery
                  </span>
                <?php elseif ($ord['order_type'] === 'DineIn'): ?>
                  <span style="background: rgba(212,160,23,0.15); color: var(--clr-gold-bright); padding: 0.2rem 0.6rem; border-radius: var(--radius-full); font-size: 0.775rem; font-weight: 600;">
                    <i class="fa-solid fa-utensils"></i> Dine-In <?php echo !empty($ord['table_number']) ? '(' . htmlspecialchars($ord['table_number']) . ')' : ''; ?>
                  </span>
                <?php else: ?>
                  <span style="background: rgba(92,148,51,0.15); color: var(--clr-green-bright); padding: 0.2rem 0.6rem; border-radius: var(--radius-full); font-size: 0.775rem; font-weight: 600;">
                    <i class="fa-solid fa-bag-shopping"></i> Takeaway
                  </span>
                <?php endif; ?>
              </td>

              <!-- Payment Status -->
              <td>
                <?php if ($ord['payment_status'] === 'Paid'): ?>
                  <span class="badge-status active" title="Verified via Razorpay">
                    <i class="fa-solid fa-circle-check" style="font-size: 8px;"></i> Paid
                  </span>
                <?php elseif ($ord['payment_status'] === 'Refunded'): ?>
                  <span class="badge-status pending" style="background: rgba(82,143,240,0.15); color: #528FF0; border-color: rgba(82,143,240,0.3);">
                    <i class="fa-solid fa-rotate-left" style="font-size: 8px;"></i> Refunded
                  </span>
                <?php else: ?>
                  <span class="badge-status cancelled">
                    <i class="fa-solid fa-clock" style="font-size: 8px;"></i> Pending
                  </span>
                <?php endif; ?>
              </td>

              <!-- Order Status Dropdown Form -->
              <td>
                <form action="orders.php?status=<?php echo urlencode($filter_status); ?>" method="POST" style="display: inline-block;">
                  <input type="hidden" name="action" value="update_order_status">
                  <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                  <select name="order_status" onchange="this.form.submit()" class="form-control" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; height: auto; border-radius: var(--radius-sm); width: 140px; background: #110B0A;">
                    <option value="Received" <?php echo ($ord['order_status'] === 'Received') ? 'selected' : ''; ?>>📥 Received</option>
                    <option value="Preparing" <?php echo ($ord['order_status'] === 'Preparing') ? 'selected' : ''; ?>>🍳 Preparing</option>
                    <option value="Ready" <?php echo ($ord['order_status'] === 'Ready') ? 'selected' : ''; ?>>🍽️ Ready</option>
                    <option value="OutForDelivery" <?php echo ($ord['order_status'] === 'OutForDelivery') ? 'selected' : ''; ?>>🚚 Out for Delivery</option>
                    <option value="Delivered" <?php echo ($ord['order_status'] === 'Delivered') ? 'selected' : ''; ?>>✅ Delivered</option>
                    <option value="Cancelled" <?php echo ($ord['order_status'] === 'Cancelled') ? 'selected' : ''; ?>>❌ Cancelled</option>
                  </select>
                </form>
              </td>

              <!-- Amount -->
              <td>
                <strong style="color: var(--clr-gold-bright); font-size: 0.95rem;">
                  €<?php echo number_format($ord['total_amount'], 2); ?>
                </strong><br>
                <small style="color: var(--text-muted); font-size: 0.75rem;"><?php echo count($ord['items']); ?> dish(es)</small>
              </td>

              <!-- Actions -->
              <td>
                <div class="table-actions">
                  <button type="button" class="btn-action-icon success" title="View Order Details" onclick="openOrderModal(<?php echo htmlspecialchars(json_encode($ord)); ?>)">
                    <i class="fa-solid fa-eye"></i>
                  </button>

                  <a href="../order_success.php?order=<?php echo urlencode($ord['order_number']); ?>" target="_blank" class="btn-action-icon warning" title="Print Invoice / View Receipt">
                    <i class="fa-solid fa-print"></i>
                  </a>

                  <form action="orders.php?status=<?php echo urlencode($filter_status); ?>" method="POST" onsubmit="return confirm('Permanently delete order <?php echo htmlspecialchars($ord['order_number']); ?>?');">
                    <input type="hidden" name="action" value="delete_order">
                    <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                    <button type="submit" class="btn-action-icon danger" title="Delete Order">
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

<!-- Order Details Modal -->
<div class="admin-modal-overlay" id="order-detail-modal">
  <div class="admin-modal-box" style="max-width: 650px;">
    <button class="modal-close-btn" onclick="closeModal('order-detail-modal')">
      <i class="fa-solid fa-xmark"></i>
    </button>
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-subtle); padding-bottom: 0.85rem;">
      <h3 style="font-family: var(--font-heading); color: var(--clr-gold-bright); margin: 0;">
        <i class="fa-solid fa-receipt"></i> Order <span id="mod-ord-number"></span>
      </h3>
      <span id="mod-ord-status" style="font-size: 0.8rem; font-weight: 700;"></span>
    </div>

    <!-- Customer & Destination Info -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; background: var(--bg-admin-surface); padding: 1rem; border-radius: var(--radius-sm); font-size: 0.85rem;">
      <div>
        <strong style="color: var(--text-primary); display: block; margin-bottom: 0.25rem;">Customer Information:</strong>
        <span id="mod-cust-name" style="color: var(--clr-gold-bright); font-weight: 600;"></span><br>
        <span id="mod-cust-phone" style="color: var(--text-secondary);"></span><br>
        <span id="mod-cust-email" style="color: var(--text-muted); font-size: 0.8rem;"></span>
      </div>
      <div>
        <strong style="color: var(--text-primary); display: block; margin-bottom: 0.25rem;">Fulfillment & Address:</strong>
        <span id="mod-ord-type" style="color: var(--clr-terracotta-bright); font-weight: 600;"></span><br>
        <span id="mod-ord-addr" style="color: var(--text-secondary);"></span>
      </div>
    </div>

    <!-- Special Notes if present -->
    <div id="mod-notes-box" style="display: none; background: rgba(200,99,56,0.1); border-left: 3px solid var(--clr-terracotta); padding: 0.6rem 0.85rem; margin-bottom: 1.25rem; font-size: 0.85rem; color: var(--text-secondary);">
      <strong style="color: var(--clr-terracotta-bright); display: block;">Special Cooking Notes:</strong>
      <span id="mod-ord-notes"></span>
    </div>

    <!-- Itemized List Table -->
    <h4 style="font-family: var(--font-heading); color: var(--text-primary); margin-bottom: 0.5rem; font-size: 1rem;">Ordered Items:</h4>
    <div style="max-height: 200px; overflow-y: auto; margin-bottom: 1.25rem; border: 1px solid var(--border-subtle); border-radius: var(--radius-sm);">
      <table class="admin-table" style="font-size: 0.85rem;">
        <thead>
          <tr>
            <th>Dish Name</th>
            <th style="text-align: center;">Qty</th>
            <th style="text-align: right;">Unit Price</th>
            <th style="text-align: right;">Total</th>
          </tr>
        </thead>
        <tbody id="mod-items-tbody">
          <!-- Populated by JS -->
        </tbody>
      </table>
    </div>

    <!-- Summary Total -->
    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-subtle); padding-top: 1rem; font-size: 0.9rem;">
      <div>
        <span style="color: var(--text-muted);">Payment: </span>
        <strong id="mod-pay-status" style="color: var(--clr-green-bright);"></strong>
      </div>
      <div style="text-align: right;">
        <span style="color: var(--text-muted); font-size: 0.85rem;">Grand Total: </span>
        <strong id="mod-grand-total" style="font-size: 1.3rem; color: var(--clr-gold-bright); font-family: var(--font-heading);"></strong>
      </div>
    </div>

  </div>
</div>

<script>
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

function openOrderModal(order) {
  document.getElementById('mod-ord-number').textContent = '#' + order.order_number;
  document.getElementById('mod-cust-name').textContent = order.customer_name;
  document.getElementById('mod-cust-phone').textContent = order.customer_phone;
  document.getElementById('mod-cust-email').textContent = order.customer_email;
  document.getElementById('mod-ord-type').textContent = order.order_type;

  let addr = 'Takeaway Pickup';
  if (order.order_type === 'Delivery') {
    addr = order.delivery_address || 'Address not specified';
  } else if (order.order_type === 'DineIn') {
    addr = 'Dine-In Table: ' + (order.table_number || 'Unspecified');
  }
  document.getElementById('mod-ord-addr').textContent = addr;

  const notesBox = document.getElementById('mod-notes-box');
  if (order.order_notes) {
    document.getElementById('mod-ord-notes').textContent = order.order_notes;
    notesBox.style.display = 'block';
  } else {
    notesBox.style.display = 'none';
  }

  document.getElementById('mod-pay-status').textContent = order.payment_status + ' (' + (order.razorpay_payment_id || 'Razorpay') + ')';
  document.getElementById('mod-grand-total').textContent = '€' + parseFloat(order.total_amount).toFixed(2);

  // Render items
  const tbody = document.getElementById('mod-items-tbody');
  tbody.innerHTML = '';
  if (order.items && order.items.length > 0) {
    order.items.forEach(it => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td><strong>${it.dish_name}</strong></td>
        <td style="text-align: center; color: var(--clr-gold);">${it.quantity}</td>
        <td style="text-align: right; color: var(--text-muted);">€${parseFloat(it.dish_price).toFixed(2)}</td>
        <td style="text-align: right; font-weight: 700; color: var(--text-primary);">€${parseFloat(it.item_total).toFixed(2)}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  openModal('order-detail-modal');
}
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
