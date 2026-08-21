<?php
/**
 * Daba Magic - Admin Live Orders & Kitchen Display System (KDS)
 */

require_once __DIR__ . '/includes/auth_check.php';

// Handle AJAX Live Poll Requests
if (isset($_GET['ajax']) && $_GET['ajax'] === 'live_orders') {
    header('Content-Type: application/json');

    $filter_st = $_GET['status'] ?? 'all';
    $search = trim($_GET['q'] ?? '');

    $where = [];
    if ($filter_st !== 'all') {
        $safe_st = $con->real_escape_string($filter_st);
        $where[] = "order_status = '$safe_st'";
    }
    if (!empty($search)) {
        $safe_q = $con->real_escape_string($search);
        $where[] = "(order_number LIKE '%$safe_q%' OR customer_name LIKE '%$safe_q%' OR customer_phone LIKE '%$safe_q%')";
    }
    $w_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

    $ajax_orders = [];
    $res = $con->query("SELECT * FROM tbl_orders $w_sql ORDER BY id DESC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $o_id = $row['id'];
            $i_res = $con->query("SELECT * FROM tbl_order_items WHERE order_id = $o_id");
            $items = [];
            if ($i_res) {
                while ($it = $i_res->fetch_assoc()) {
                    $items[] = $it;
                }
            }
            $row['items'] = $items;
            $ajax_orders[] = $row;
        }
    }

    // Live Metrics
    $total_orders = 0;
    $total_rev = 0.00;
    $active_k = 0;
    $today_cnt = 0;
    $today_str = date('Y-m-d');

    $k_res = $con->query("SELECT order_status, payment_status, total_amount, created_at FROM tbl_orders");
    if ($k_res) {
        while ($kr = $k_res->fetch_assoc()) {
            $total_orders++;
            if ($kr['payment_status'] === 'Paid') $total_rev += floatval($kr['total_amount']);
            if (in_array($kr['order_status'], ['Received', 'Preparing', 'Ready', 'OutForDelivery'])) $active_k++;
            if (substr($kr['created_at'], 0, 10) === $today_str) $today_cnt++;
        }
    }

    echo json_encode([
        'success' => true,
        'orders' => $ajax_orders,
        'metrics' => [
            'total_orders' => $total_orders,
            'total_revenue' => number_format($total_rev, 2),
            'active_kitchen' => $active_k,
            'today_orders' => $today_cnt
        ],
        'latest_id' => !empty($ajax_orders) ? intval($ajax_orders[0]['id']) : 0
    ]);
    exit;
}

// Handle AJAX Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'update_status') {
    header('Content-Type: application/json');
    $order_id = intval($_POST['order_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? 'Received';

    if ($order_id > 0) {
        $stmt = $con->prepare("UPDATE tbl_orders SET order_status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $order_id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'order_id' => $order_id, 'new_status' => $new_status]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid order ID']);
    }
    exit;
}

// Standard Form POST Actions
$msg = "";
$err = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'delete_order') {
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

$page_title = "Live Customer Orders & Kitchen Monitor - Daba Magic Admin";
$filter_status = $_GET['status'] ?? 'all';
$search_q = trim($_GET['q'] ?? '');

$where_parts = [];
if ($filter_status !== 'all') {
    $safe_st = $con->real_escape_string($filter_status);
    $where_parts[] = "order_status = '$safe_st'";
}
if (!empty($search_q)) {
    $safe_q = $con->real_escape_string($search_q);
    $where_parts[] = "(order_number LIKE '%$safe_q%' OR customer_name LIKE '%$safe_q%' OR customer_phone LIKE '%$safe_q%')";
}
$where_sql = !empty($where_parts) ? "WHERE " . implode(" AND ", $where_parts) : "";

// Fetch initial orders
$orders = [];
$ord_query = $con->query("SELECT * FROM tbl_orders $where_sql ORDER BY id DESC");
if ($ord_query) {
    while ($row = $ord_query->fetch_assoc()) {
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

// Initial Metrics
$total_orders_cnt = 0;
$total_revenue = 0.00;
$active_kitchen_cnt = 0;
$today_orders_cnt = 0;
$today_str = date('Y-m-d');

$kpi_query = $con->query("SELECT order_status, payment_status, total_amount, created_at FROM tbl_orders");
if ($kpi_query) {
    while ($k_row = $kpi_query->fetch_assoc()) {
        $total_orders_cnt++;
        if ($k_row['payment_status'] === 'Paid') $total_revenue += floatval($k_row['total_amount']);
        if (in_array($k_row['order_status'], ['Received', 'Preparing', 'Ready', 'OutForDelivery'])) $active_kitchen_cnt++;
        if (substr($k_row['created_at'], 0, 10) === $today_str) $today_orders_cnt++;
    }
}

include_once __DIR__ . '/includes/header.php';
?>

<!-- Header Toolbar with Live Sync & View Mode Switcher -->
<div class="page-header">
  <div>
    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.35rem;">
      <h1 class="page-title" style="margin: 0;">Live Orders & Kitchen Monitor</h1>
      <span class="live-pulse-indicator" id="live-indicator">
        <span class="live-radar-dot"></span>
        <span>Live Sync (5s)</span>
      </span>
    </div>
    <p class="page-subtitle">Real-time live view of ordered items, instant kitchen alerts, and status dispatching.</p>
  </div>

  <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
    <!-- Sound Alert Toggle -->
    <button type="button" class="btn-cropper-action" id="btn-sound-toggle" onclick="toggleAudioChime()" title="Toggle Audio Chime Alert on New Order">
      <i class="fa-solid fa-bell" id="sound-icon"></i>
      <span id="sound-text">Sound: ON</span>
    </button>

    <!-- View Mode Switcher -->
    <div style="display: flex; background: var(--bg-admin-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-sm); padding: 0.2rem;">
      <button type="button" class="btn-cropper-action" id="btn-view-table" onclick="setViewMode('table')" style="background: var(--clr-gold); color: #110B0A; font-weight: 700;">
        <i class="fa-solid fa-table-list"></i> Table View
      </button>
      <button type="button" class="btn-cropper-action" id="btn-view-kds" onclick="setViewMode('kds')">
        <i class="fa-solid fa-kitchen-set"></i> Kitchen KDS Board
      </button>
    </div>

    <!-- Manual Refresh -->
    <button type="button" class="btn-admin-sec" onclick="fetchLiveOrders(true)" title="Force Refresh">
      <i class="fa-solid fa-rotate" id="refresh-spinner"></i>
    </button>
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
      <h3 class="kpi-value" id="kpi-total-orders"><?php echo $total_orders_cnt; ?></h3>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="background: rgba(92, 148, 51, 0.15); color: var(--clr-green-bright); border: 1px solid rgba(92, 148, 51, 0.3);">
      <i class="fa-solid fa-circle-dollar-to-slot"></i>
    </div>
    <div class="kpi-info">
      <span class="kpi-title">Paid Revenue</span>
      <h3 class="kpi-value" id="kpi-total-revenue">€<?php echo number_format($total_revenue, 2); ?></h3>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="background: rgba(200, 99, 56, 0.15); color: var(--clr-terracotta-bright); border: 1px solid rgba(200, 99, 56, 0.3);">
      <i class="fa-solid fa-fire-burner"></i>
    </div>
    <div class="kpi-info">
      <span class="kpi-title">Active Kitchen Orders</span>
      <h3 class="kpi-value" id="kpi-active-kitchen" style="color: var(--clr-terracotta-bright);"><?php echo $active_kitchen_cnt; ?></h3>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-icon" style="background: rgba(82, 143, 240, 0.15); color: #528FF0; border: 1px solid rgba(82, 143, 240, 0.3);">
      <i class="fa-solid fa-calendar-day"></i>
    </div>
    <div class="kpi-info">
      <span class="kpi-title">Today's Orders</span>
      <h3 class="kpi-value" id="kpi-today-orders"><?php echo $today_orders_cnt; ?></h3>
    </div>
  </div>

</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <div class="filter-tabs">
    <button type="button" class="filter-tab <?php echo ($filter_status === 'all') ? 'active' : ''; ?>" onclick="setFilterStatus('all', this)">All Orders</button>
    <button type="button" class="filter-tab <?php echo ($filter_status === 'Received') ? 'active' : ''; ?>" onclick="setFilterStatus('Received', this)">Received</button>
    <button type="button" class="filter-tab <?php echo ($filter_status === 'Preparing') ? 'active' : ''; ?>" onclick="setFilterStatus('Preparing', this)">Preparing</button>
    <button type="button" class="filter-tab <?php echo ($filter_status === 'Ready') ? 'active' : ''; ?>" onclick="setFilterStatus('Ready', this)">Ready</button>
    <button type="button" class="filter-tab <?php echo ($filter_status === 'OutForDelivery') ? 'active' : ''; ?>" onclick="setFilterStatus('OutForDelivery', this)">Out for Delivery</button>
    <button type="button" class="filter-tab <?php echo ($filter_status === 'Delivered') ? 'active' : ''; ?>" onclick="setFilterStatus('Delivered', this)">Delivered</button>
  </div>

  <div style="display: flex; gap: 0.5rem; align-items: center;">
    <div class="header-search" style="width: 260px;">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" id="live-search-input" value="<?php echo htmlspecialchars($search_q); ?>" placeholder="Live search orders..." onkeyup="handleLiveSearch(this.value)">
    </div>
  </div>
</div>

<!-- ==========================================================================
     VIEW MODE 1: DATA TABLE VIEW WITH LIVE DISHES COLUMN
     ========================================================================== -->
<div id="orders-table-view-container" class="content-card">
  <div class="table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Order Info</th>
          <th>Customer</th>
          <th>Ordered Dishes (Live Preview)</th>
          <th>Fulfillment</th>
          <th>Payment</th>
          <th>Kitchen Status</th>
          <th>Amount</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody id="orders-tbody">
        <!-- Rendered dynamically by renderOrdersTable() -->
      </tbody>
    </table>
  </div>
</div>

<!-- ==========================================================================
     VIEW MODE 2: KITCHEN DISPLAY SYSTEM (KDS) BOARD
     ========================================================================== -->
<div id="orders-kds-view-container" style="display: none;">
  <div class="kds-columns-grid">
    
    <!-- Column 1: New Received Orders -->
    <div class="kds-column" style="border-top: 3px solid var(--clr-gold);">
      <div class="kds-column-header">
        <h4 style="margin: 0; color: var(--clr-gold-bright); font-family: var(--font-heading); font-size: 1.15rem; display: flex; align-items: center; gap: 0.5rem;">
          <i class="fa-solid fa-inbox"></i> 1. New Orders
        </h4>
        <span class="badge-status active" id="kds-count-received">0</span>
      </div>
      <div id="kds-cards-received" style="display: flex; flex-direction: column; gap: 1rem;"></div>
    </div>

    <!-- Column 2: In Kitchen / Preparing -->
    <div class="kds-column" style="border-top: 3px solid var(--clr-terracotta);">
      <div class="kds-column-header">
        <h4 style="margin: 0; color: var(--clr-terracotta-bright); font-family: var(--font-heading); font-size: 1.15rem; display: flex; align-items: center; gap: 0.5rem;">
          <i class="fa-solid fa-fire-burner"></i> 2. Cooking / In Kitchen
        </h4>
        <span class="badge-status pending" id="kds-count-preparing">0</span>
      </div>
      <div id="kds-cards-preparing" style="display: flex; flex-direction: column; gap: 1rem;"></div>
    </div>

    <!-- Column 3: Ready / Out for Delivery -->
    <div class="kds-column" style="border-top: 3px solid #528FF0;">
      <div class="kds-column-header">
        <h4 style="margin: 0; color: #528FF0; font-family: var(--font-heading); font-size: 1.15rem; display: flex; align-items: center; gap: 0.5rem;">
          <i class="fa-solid fa-motorcycle"></i> 3. Ready / Out for Delivery
        </h4>
        <span class="badge-status pending" style="background: rgba(82,143,240,0.15); color: #528FF0;" id="kds-count-ready">0</span>
      </div>
      <div id="kds-cards-ready" style="display: flex; flex-direction: column; gap: 1rem;"></div>
    </div>

    <!-- Column 4: Delivered / Completed -->
    <div class="kds-column" style="border-top: 3px solid var(--clr-green);">
      <div class="kds-column-header">
        <h4 style="margin: 0; color: var(--clr-green-bright); font-family: var(--font-heading); font-size: 1.15rem; display: flex; align-items: center; gap: 0.5rem;">
          <i class="fa-solid fa-circle-check"></i> 4. Completed
        </h4>
        <span class="badge-status active" id="kds-count-delivered">0</span>
      </div>
      <div id="kds-cards-delivered" style="display: flex; flex-direction: column; gap: 1rem;"></div>
    </div>

  </div>
</div>

<!-- Order Detail Modal -->
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

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; background: var(--bg-admin-surface); padding: 1rem; border-radius: var(--radius-sm); font-size: 0.85rem;">
      <div>
        <strong style="color: var(--text-primary); display: block; margin-bottom: 0.25rem;">Customer Information:</strong>
        <span id="mod-cust-name" style="color: var(--clr-gold-bright); font-weight: 600;"></span><br>
        <span id="mod-cust-phone" style="color: var(--text-secondary);"></span><br>
        <span id="mod-cust-email" style="color: var(--text-muted); font-size: 0.8rem;"></span>
      </div>
      <div>
        <strong style="color: var(--text-primary); display: block; margin-bottom: 0.25rem;">Fulfillment & Destination:</strong>
        <span id="mod-ord-type" style="color: var(--clr-terracotta-bright); font-weight: 600;"></span><br>
        <span id="mod-ord-addr" style="color: var(--text-secondary);"></span>
      </div>
    </div>

    <div id="mod-notes-box" style="display: none; background: rgba(200,99,56,0.1); border-left: 3px solid var(--clr-terracotta); padding: 0.6rem 0.85rem; margin-bottom: 1.25rem; font-size: 0.85rem; color: var(--text-secondary);">
      <strong style="color: var(--clr-terracotta-bright); display: block;">Special Cooking Notes:</strong>
      <span id="mod-ord-notes"></span>
    </div>

    <h4 style="font-family: var(--font-heading); color: var(--text-primary); margin-bottom: 0.5rem; font-size: 1rem;">Ordered Dishes:</h4>
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
        <tbody id="mod-items-tbody"></tbody>
      </table>
    </div>

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

<!-- ==========================================================================
     LIVE CLIENT-SIDE ENGINE (POLLING, AUDIO CHIME & KDS)
     ========================================================================== -->
<script>
let currentFilter = '<?php echo htmlspecialchars($filter_status); ?>';
let currentSearch = '<?php echo htmlspecialchars($search_q); ?>';
let currentViewMode = 'table';
let soundEnabled = true;
let highestOrderId = <?php echo !empty($orders) ? intval($orders[0]['id']) : 0; ?>;
let liveOrdersData = <?php echo json_encode($orders); ?>;
let isFirstLoad = true;

// Synthesize pleasant double-tone audio chime (No external mp3 required)
function playNewOrderChime() {
  if (!soundEnabled) return;
  try {
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    const now = audioCtx.currentTime;

    // Tone 1 (High bell)
    const osc1 = audioCtx.createOscillator();
    const gain1 = audioCtx.createGain();
    osc1.type = 'sine';
    osc1.frequency.setValueAtTime(880, now); // A5
    gain1.gain.setValueAtTime(0.3, now);
    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
    osc1.connect(gain1);
    gain1.connect(audioCtx.destination);
    osc1.start(now);
    osc1.stop(now + 0.6);

    // Tone 2 (Harmonic bell)
    const osc2 = audioCtx.createOscillator();
    const gain2 = audioCtx.createGain();
    osc2.type = 'sine';
    osc2.frequency.setValueAtTime(1174.66, now + 0.18); // D6
    gain2.gain.setValueAtTime(0.35, now + 0.18);
    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.9);
    osc2.connect(gain2);
    gain2.connect(audioCtx.destination);
    osc2.start(now + 0.18);
    osc2.stop(now + 0.9);
  } catch (e) {
    console.log('Audio chime not supported or blocked by user gesture policy');
  }
}

function toggleAudioChime() {
  soundEnabled = !soundEnabled;
  const icon = document.getElementById('sound-icon');
  const text = document.getElementById('sound-text');
  if (soundEnabled) {
    icon.className = 'fa-solid fa-bell';
    text.textContent = 'Sound: ON';
    playNewOrderChime();
  } else {
    icon.className = 'fa-solid fa-bell-slash';
    text.textContent = 'Sound: OFF';
  }
}

function setViewMode(mode) {
  currentViewMode = mode;
  const tableCont = document.getElementById('orders-table-view-container');
  const kdsCont = document.getElementById('orders-kds-view-container');
  const btnTable = document.getElementById('btn-view-table');
  const btnKds = document.getElementById('btn-view-kds');

  if (mode === 'table') {
    tableCont.style.display = 'block';
    kdsCont.style.display = 'none';
    btnTable.style.background = 'var(--clr-gold)';
    btnTable.style.color = '#110B0A';
    btnTable.style.fontWeight = '700';
    btnKds.style.background = '';
    btnKds.style.color = '';
    btnKds.style.fontWeight = '';
  } else {
    tableCont.style.display = 'none';
    kdsCont.style.display = 'block';
    btnKds.style.background = 'var(--clr-gold)';
    btnKds.style.color = '#110B0A';
    btnKds.style.fontWeight = '700';
    btnTable.style.background = '';
    btnTable.style.color = '';
    btnTable.style.fontWeight = '';
  }
  renderAllViews();
}

function setFilterStatus(status, element) {
  currentFilter = status;
  document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
  if (element) element.classList.add('active');
  fetchLiveOrders();
}

function handleLiveSearch(val) {
  currentSearch = val.trim();
  fetchLiveOrders();
}

// Fetch live orders via AJAX
function fetchLiveOrders(manual = false) {
  const spinner = document.getElementById('refresh-spinner');
  if (spinner && manual) spinner.classList.add('fa-spin');

  const url = `orders.php?ajax=live_orders&status=${encodeURIComponent(currentFilter)}&q=${encodeURIComponent(currentSearch)}`;
  fetch(url)
    .then(res => res.json())
    .then(data => {
      if (spinner && manual) spinner.classList.remove('fa-spin');
      if (data.success) {
        // Check for new orders
        if (!isFirstLoad && data.latest_id > highestOrderId) {
          playNewOrderChime();
          const newCount = data.orders.filter(o => o.id > highestOrderId).length;
          showLiveToast(`🔔 ${newCount} New Customer Order(s) Received!`);
        }

        if (data.latest_id > highestOrderId) {
          highestOrderId = data.latest_id;
        }
        isFirstLoad = false;

        liveOrdersData = data.orders;
        updateKpiDisplay(data.metrics);
        renderAllViews();
      }
    })
    .catch(err => {
      if (spinner && manual) spinner.classList.remove('fa-spin');
      console.error('Live polling error:', err);
    });
}

function updateKpiDisplay(metrics) {
  if (!metrics) return;
  document.getElementById('kpi-total-orders').textContent = metrics.total_orders;
  document.getElementById('kpi-total-revenue').textContent = '€' + metrics.total_revenue;
  document.getElementById('kpi-active-kitchen').textContent = metrics.active_kitchen;
  document.getElementById('kpi-today-orders').textContent = metrics.today_orders;
}

function renderAllViews() {
  renderOrdersTable();
  renderKdsBoard();
}

// Render Table View with Live Dishes Items
function renderOrdersTable() {
  const tbody = document.getElementById('orders-tbody');
  if (!tbody) return;

  if (liveOrdersData.length === 0) {
    tbody.innerHTML = `
      <tr>
        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3rem;">
          <i class="fa-solid fa-receipt" style="font-size: 2.5rem; margin-bottom: 0.5rem; color: var(--clr-terracotta);"></i><br>
          No orders found matching the selected filter.
        </td>
      </tr>
    `;
    return;
  }

  let html = '';
  liveOrdersData.forEach(ord => {
    // Build live items preview HTML
    let itemsHtml = '<div class="live-dishes-wrap">';
    if (ord.items && ord.items.length > 0) {
      ord.items.forEach(it => {
        itemsHtml += `
          <div class="live-dish-pill">
            <span class="live-dish-qty">${it.quantity}x</span>
            <span>${it.dish_name}</span>
          </div>
        `;
      });
    } else {
      itemsHtml += '<span style="color: var(--text-muted); font-size: 0.775rem;">No items listed</span>';
    }

    if (ord.order_notes) {
      itemsHtml += `
        <span style="display: block; margin-top: 0.25rem; font-size: 0.75rem; color: var(--clr-terracotta-bright);">
          <i class="fa-solid fa-note-sticky"></i> Note: ${ord.order_notes}
        </span>
      `;
    }
    itemsHtml += '</div>';

    // Type Badge
    let typeBadge = '';
    if (ord.order_type === 'Delivery') {
      typeBadge = `<span style="background: rgba(200,99,56,0.15); color: var(--clr-terracotta-bright); padding: 0.2rem 0.6rem; border-radius: var(--radius-full); font-size: 0.775rem; font-weight: 600;"><i class="fa-solid fa-motorcycle"></i> Delivery</span>`;
    } else if (ord.order_type === 'DineIn') {
      typeBadge = `<span style="background: rgba(212,160,23,0.15); color: var(--clr-gold-bright); padding: 0.2rem 0.6rem; border-radius: var(--radius-full); font-size: 0.775rem; font-weight: 600;"><i class="fa-solid fa-utensils"></i> Dine-In ${ord.table_number ? '(' + ord.table_number + ')' : ''}</span>`;
    } else {
      typeBadge = `<span style="background: rgba(92,148,51,0.15); color: var(--clr-green-bright); padding: 0.2rem 0.6rem; border-radius: var(--radius-full); font-size: 0.775rem; font-weight: 600;"><i class="fa-solid fa-bag-shopping"></i> Takeaway</span>`;
    }

    // Payment Badge
    let payBadge = '';
    if (ord.payment_status === 'Paid') {
      payBadge = `<span class="badge-status active" title="Verified via Razorpay"><i class="fa-solid fa-circle-check" style="font-size: 8px;"></i> Paid</span>`;
    } else {
      payBadge = `<span class="badge-status cancelled"><i class="fa-solid fa-clock" style="font-size: 8px;"></i> Pending</span>`;
    }

    // Status Select Options
    const statuses = ['Received', 'Preparing', 'Ready', 'OutForDelivery', 'Delivered', 'Cancelled'];
    const statusLabels = {
      'Received': '📥 Received',
      'Preparing': '🍳 Preparing',
      'Ready': '🍽️ Ready',
      'OutForDelivery': '🚚 Out for Delivery',
      'Delivered': '✅ Delivered',
      'Cancelled': '❌ Cancelled'
    };

    let selectOptions = '';
    statuses.forEach(s => {
      selectOptions += `<option value="${s}" ${ord.order_status === s ? 'selected' : ''}>${statusLabels[s]}</option>`;
    });

    html += `
      <tr>
        <td>
          <a href="javascript:void(0)" onclick="openOrderModal(${JSON.stringify(ord).replace(/"/g, '&quot;')})" style="text-decoration: none; color: var(--clr-gold-bright); font-weight: 700; font-family: monospace; font-size: 0.95rem;">
            ${ord.order_number}
          </a><br>
          <small style="color: var(--text-muted); font-size: 0.775rem;">
            <i class="fa-regular fa-clock"></i> ${ord.created_at}
          </small>
        </td>
        <td>
          <strong style="color: var(--text-primary); font-size: 0.9rem;">${ord.customer_name}</strong><br>
          <a href="tel:${ord.customer_phone}" style="color: var(--text-secondary); text-decoration: none; font-size: 0.8rem;">
            <i class="fa-solid fa-phone" style="font-size: 0.7rem; color: var(--clr-gold);"></i> ${ord.customer_phone}
          </a>
        </td>
        <td>${itemsHtml}</td>
        <td>${typeBadge}</td>
        <td>${payBadge}</td>
        <td>
          <select onchange="updateOrderStatusAjax(${ord.id}, this.value)" class="form-control" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; height: auto; border-radius: var(--radius-sm); width: 140px; background: #110B0A;">
            ${selectOptions}
          </select>
        </td>
        <td>
          <strong style="color: var(--clr-gold-bright); font-size: 0.95rem;">€${parseFloat(ord.total_amount).toFixed(2)}</strong>
        </td>
        <td>
          <div class="table-actions">
            <button type="button" class="btn-action-icon success" title="View Full Order Details" onclick="openOrderModal(${JSON.stringify(ord).replace(/"/g, '&quot;')})">
              <i class="fa-solid fa-eye"></i>
            </button>
            <a href="../order_success.php?order=${encodeURIComponent(ord.order_number)}" target="_blank" class="btn-action-icon warning" title="Print Kitchen Invoice">
              <i class="fa-solid fa-print"></i>
            </a>
          </div>
        </td>
      </tr>
    `;
  });

  tbody.innerHTML = html;
}

// Render Kitchen KDS Board
function renderKdsBoard() {
  const colRec = document.getElementById('kds-cards-received');
  const colPrep = document.getElementById('kds-cards-preparing');
  const colReady = document.getElementById('kds-cards-ready');
  const colDel = document.getElementById('kds-cards-delivered');

  if (!colRec) return;

  colRec.innerHTML = '';
  colPrep.innerHTML = '';
  colReady.innerHTML = '';
  colDel.innerHTML = '';

  let cntRec = 0, cntPrep = 0, cntReady = 0, cntDel = 0;

  liveOrdersData.forEach(ord => {
    const isNew = (ord.order_status === 'Received');
    let itemsList = '';
    if (ord.items && ord.items.length > 0) {
      ord.items.forEach(it => {
        itemsList += `
          <div class="kds-dish-row">
            <span><strong style="color: var(--clr-gold-bright);">${it.quantity}x</strong> ${it.dish_name}</span>
            <span style="color: var(--text-muted); font-size: 0.775rem;">€${parseFloat(it.item_total).toFixed(2)}</span>
          </div>
        `;
      });
    }

    let actionBtn = '';
    if (ord.order_status === 'Received') {
      cntRec++;
      actionBtn = `<button type="button" class="btn-kds-advance prep" onclick="updateOrderStatusAjax(${ord.id}, 'Preparing')"><i class="fa-solid fa-fire-burner"></i> Start Cooking ➔</button>`;
    } else if (ord.order_status === 'Preparing') {
      cntPrep++;
      actionBtn = `<button type="button" class="btn-kds-advance ready" onclick="updateOrderStatusAjax(${ord.id}, 'Ready')"><i class="fa-solid fa-bell-concierge"></i> Mark Ready ➔</button>`;
    } else if (ord.order_status === 'Ready' || ord.order_status === 'OutForDelivery') {
      cntReady++;
      actionBtn = `<button type="button" class="btn-kds-advance deliver" onclick="updateOrderStatusAjax(${ord.id}, 'Delivered')"><i class="fa-solid fa-check"></i> Complete & Deliver ✅</button>`;
    } else if (ord.order_status === 'Delivered') {
      cntDel++;
      actionBtn = `<span style="color: var(--clr-green-bright); font-size: 0.8rem; font-weight: 600;"><i class="fa-solid fa-circle-check"></i> Order Completed</span>`;
    }

    const cardHtml = `
      <div class="kds-ticket-card ${isNew ? 'new-order' : ''}" id="kds-card-${ord.id}">
        <div class="kds-ticket-top">
          <div>
            <strong style="color: var(--clr-gold-bright); font-family: monospace; font-size: 1rem;">#${ord.order_number}</strong><br>
            <span style="color: #FFF; font-weight: 600; font-size: 0.85rem;">${ord.customer_name}</span>
          </div>
          <span style="background: rgba(255,255,255,0.06); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; color: #AAA;">
            ${ord.order_type}
          </span>
        </div>

        <div class="kds-ticket-dishes">
          ${itemsList}
        </div>

        ${ord.order_notes ? `<div style="background: rgba(200,99,56,0.15); border-left: 2px solid var(--clr-terracotta); padding: 0.4rem 0.6rem; font-size: 0.775rem; color: var(--clr-terracotta-bright);"><i class="fa-solid fa-comment-dots"></i> ${ord.order_notes}</div>` : ''}

        <div class="kds-ticket-actions">
          ${actionBtn}
          <button type="button" class="btn-cropper-action" onclick="openOrderModal(${JSON.stringify(ord).replace(/"/g, '&quot;')})" title="Inspect Details">
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
      </div>
    `;

    if (ord.order_status === 'Received') colRec.innerHTML += cardHtml;
    else if (ord.order_status === 'Preparing') colPrep.innerHTML += cardHtml;
    else if (ord.order_status === 'Ready' || ord.order_status === 'OutForDelivery') colReady.innerHTML += cardHtml;
    else if (ord.order_status === 'Delivered') colDel.innerHTML += cardHtml;
  });

  document.getElementById('kds-count-received').textContent = cntRec;
  document.getElementById('kds-count-preparing').textContent = cntPrep;
  document.getElementById('kds-count-ready').textContent = cntReady;
  document.getElementById('kds-count-delivered').textContent = cntDel;
}

// 1-Click Fast AJAX Status Update
function updateOrderStatusAjax(orderId, newStatus) {
  const formData = new FormData();
  formData.append('ajax_action', 'update_status');
  formData.append('order_id', orderId);
  formData.append('new_status', newStatus);

  fetch('orders.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // Update local state instantly
      const target = liveOrdersData.find(o => o.id == orderId);
      if (target) target.order_status = newStatus;
      renderAllViews();
      showLiveToast(`Order status changed to "${newStatus}"`);
    } else {
      alert('Error updating status: ' + (data.error || 'Server error'));
    }
  })
  .catch(err => {
    console.error('AJAX status error:', err);
  });
}

function showLiveToast(msg) {
  let toast = document.getElementById('admin-live-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'admin-live-toast';
    toast.style.position = 'fixed';
    toast.style.bottom = '25px';
    toast.style.right = '25px';
    toast.style.background = 'rgba(26, 17, 15, 0.95)';
    toast.style.border = '1px solid var(--clr-gold)';
    toast.style.color = '#FFF';
    toast.style.padding = '0.85rem 1.4rem';
    toast.style.borderRadius = '8px';
    toast.style.fontSize = '0.9rem';
    toast.style.boxShadow = '0 10px 30px rgba(0,0,0,0.8)';
    toast.style.zIndex = '999999';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '0.6rem';
    toast.style.transition = 'all 0.3s ease';
    document.body.appendChild(toast);
  }
  toast.innerHTML = `<i class="fa-solid fa-bell" style="color: var(--clr-gold-bright);"></i> <span>${msg}</span>`;
  toast.style.opacity = '1';
  toast.style.transform = 'translateY(0)';
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(20px)';
  }, 3500);
}

// Modal Helpers
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

// Start Live Engine
document.addEventListener('DOMContentLoaded', () => {
  renderAllViews();
  // Poll every 5 seconds for new orders
  setInterval(() => {
    fetchLiveOrders();
  }, 5000);
});
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
