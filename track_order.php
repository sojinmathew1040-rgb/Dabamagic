<?php
/**
 * Daba Magic - User Side Live Order Status & Tracking Page (track_order.php)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/includes/razorpay_config.php';

// Handle Real-Time AJAX Status Polling for Customer
if (isset($_GET['ajax']) && $_GET['ajax'] === 'order_status') {
    header('Content-Type: application/json');
    $order_num = trim($_GET['order'] ?? '');

    if (empty($order_num)) {
        echo json_encode(['success' => false, 'error' => 'Order number is required.']);
        exit;
    }

    $stmt = $con->prepare("SELECT id, order_number, order_status, payment_status, customer_name, order_type, table_number, delivery_address, total_amount, created_at FROM tbl_orders WHERE order_number = ?");
    $stmt->bind_param("s", $order_num);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $ord = $res->fetch_assoc();
        echo json_encode([
            'success' => true,
            'order_number' => $ord['order_number'],
            'order_status' => $ord['order_status'],
            'payment_status' => $ord['payment_status'],
            'created_at' => $ord['created_at'],
            'time_formatted' => date('d M Y, H:i', strtotime($ord['created_at']))
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Order not found.']);
    }
    $stmt->close();
    exit;
}

$search_order = trim($_GET['order'] ?? ($_POST['order_number'] ?? ($_SESSION['last_order_number'] ?? '')));
$order_data = null;
$order_items = [];
$error_msg = "";

if (!empty($search_order)) {
    $stmt = $con->prepare("SELECT * FROM tbl_orders WHERE order_number = ?");
    $stmt->bind_param("s", $search_order);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $order_data = $res->fetch_assoc();
        $stmt->close();

        // Fetch ordered items
        $i_stmt = $con->prepare("SELECT * FROM tbl_order_items WHERE order_id = ?");
        $i_stmt->bind_param("i", $order_data['id']);
        $i_stmt->execute();
        $i_res = $i_stmt->get_result();
        while ($row = $i_res->fetch_assoc()) {
            $order_items[] = $row;
        }
        $i_stmt->close();
    } else {
        $error_msg = "No order found matching order number '{$search_order}'. Please check and try again.";
        $stmt->close();
    }
}

$page_title = "Track Your Order Live - Daba Magic";
include_once __DIR__ . '/includes/header.php';
?>

<div class="track-order-wrapper" style="min-height: 85vh; padding: 120px 0 80px 0; background: #0E0706; position: relative;">
  <div class="container" style="max-width: 900px; margin: 0 auto; padding: 0 1.5rem;">
    
    <!-- Title Header -->
    <div style="margin-bottom: 2.5rem; text-align: center;">
      <span style="color: var(--clr-gold-bright, #E5B22D); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase;">
        Live Kitchen Dispatch & Tracking
      </span>
      <h1 style="font-family: var(--font-heading, 'Cormorant Garamond', serif); font-size: 2.75rem; color: #FFFFFF; margin: 0.35rem 0 0.5rem 0;">
        Track Your Food Order
      </h1>
      <p style="color: #999; font-size: 0.95rem;">Monitor real-time kitchen preparation, delivery progress, and dish invoice.</p>
    </div>

    <!-- Order Search Form Box -->
    <div style="background: #19100E; border: 1px solid rgba(212,160,23,0.3); border-radius: 16px; padding: 1.75rem 2rem; margin-bottom: 2.5rem; box-shadow: 0 20px 50px rgba(0,0,0,0.6);">
      <form action="track_order.php" method="GET" style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 250px;">
          <label class="form-label" for="search-order-input">Enter Order Number</label>
          <div style="position: relative;">
            <i class="fa-solid fa-receipt" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--clr-gold-bright);"></i>
            <input type="text" name="order" id="search-order-input" class="form-control-input" style="padding-left: 2.75rem; font-family: monospace; font-size: 1.05rem;" placeholder="e.g. DM-ORD-260820-A1B299" value="<?php echo htmlspecialchars($search_order); ?>" required>
          </div>
        </div>
        <div style="padding-top: 1.5rem;">
          <button type="submit" class="btn btn-primary" style="height: 48px;">
            <i class="fa-solid fa-satellite-dish"></i>
            <span>Track Order</span>
          </button>
        </div>
      </form>
    </div>

    <?php if (!empty($error_msg)): ?>
      <div style="background: rgba(235, 87, 87, 0.15); border: 1px solid var(--clr-red); color: var(--clr-red-bright); padding: 1rem 1.5rem; border-radius: 12px; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
        <i class="fa-solid fa-circle-exclamation" style="font-size: 1.2rem;"></i>
        <span><?php echo htmlspecialchars($error_msg); ?></span>
      </div>
    <?php endif; ?>

    <?php if ($order_data): ?>
      <?php
        $st = strtolower($order_data['order_status']);
        $step1_active = true;
        $step2_active = in_array($st, ['preparing', 'ready', 'outfordelivery', 'delivered']);
        $step3_active = in_array($st, ['outfordelivery', 'ready', 'delivered']);
        $step4_active = ($st === 'delivered');
        $is_cancelled = ($st === 'cancelled');
      ?>

      <!-- Live Order Status Card -->
      <div style="background: #19100E; border: 1px solid rgba(212,160,23,0.35); border-radius: 16px; padding: 2.25rem; margin-bottom: 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.7); position: relative;">
        
        <!-- Live Sync Header Strip -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 1.25rem; margin-bottom: 2rem;">
          <div>
            <span style="color: #888; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.08em; display: block;">Active Order</span>
            <strong style="color: var(--clr-gold-bright); font-family: monospace; font-size: 1.35rem;">
              #<?php echo htmlspecialchars($order_data['order_number']); ?>
            </strong>
          </div>

          <div style="display: flex; align-items: center; gap: 1rem;">
            <div class="live-pulse-indicator" id="user-live-indicator">
              <span class="live-radar-dot"></span>
              <span id="user-live-text">Live Sync Active</span>
            </div>
            
            <?php if ($order_data['payment_status'] === 'Paid'): ?>
              <span class="badge-status active">
                <i class="fa-solid fa-circle-check" style="font-size: 8px;"></i> Paid via Razorpay
              </span>
            <?php else: ?>
              <span class="badge-status cancelled">
                <i class="fa-solid fa-clock" style="font-size: 8px;"></i> Payment Pending
              </span>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($is_cancelled): ?>
          <div style="text-align: center; padding: 2rem; background: rgba(235,87,87,0.1); border: 1px solid rgba(235,87,87,0.3); border-radius: 12px; margin-bottom: 1.5rem;">
            <i class="fa-solid fa-ban" style="font-size: 3rem; color: var(--clr-red-bright); margin-bottom: 0.75rem;"></i>
            <h3 style="color: #FFF; font-family: var(--font-heading); margin-bottom: 0.25rem;">This Order Was Cancelled</h3>
            <p style="color: #AAA; font-size: 0.9rem; margin: 0;">Please contact our support if you require a refund or assistance.</p>
          </div>
        <?php else: ?>

          <!-- 4-Stage Visual Order Timeline Tracker -->
          <div class="order-tracker-timeline" id="live-tracker-bar">
            
            <div class="tracker-step <?php echo $step1_active ? 'completed' : ''; ?>" id="track-step-1">
              <div class="tracker-icon"><i class="fa-solid fa-receipt"></i></div>
              <strong class="tracker-label">Order Received</strong>
              <small class="tracker-time"><?php echo date('H:i', strtotime($order_data['created_at'])); ?></small>
            </div>

            <div class="tracker-step <?php echo $step2_active ? 'completed' : ($st === 'received' ? 'active' : ''); ?>" id="track-step-2">
              <div class="tracker-icon"><i class="fa-solid fa-fire-burner"></i></div>
              <strong class="tracker-label">Kitchen Preparing</strong>
              <small class="tracker-time">Fresh & Hot</small>
            </div>

            <div class="tracker-step <?php echo $step3_active ? 'completed' : ($st === 'preparing' ? 'active' : ''); ?>" id="track-step-3">
              <div class="tracker-icon"><i class="fa-solid <?php echo ($order_data['order_type'] === 'Delivery') ? 'fa-motorcycle' : 'fa-bell-concierge'; ?>"></i></div>
              <strong class="tracker-label"><?php echo ($order_data['order_type'] === 'Delivery') ? 'Out for Delivery' : 'Ready for Pickup'; ?></strong>
              <small class="tracker-time">En Route</small>
            </div>

            <div class="tracker-step <?php echo $step4_active ? 'completed' : ''; ?>" id="track-step-4">
              <div class="tracker-icon"><i class="fa-solid fa-circle-check"></i></div>
              <strong class="tracker-label">Delivered</strong>
              <small class="tracker-time">Enjoy Your Meal</small>
            </div>

          </div>

          <!-- Status Highlight Banner -->
          <div id="live-status-message" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 1rem 1.25rem; margin-top: 1.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
              <i class="fa-solid fa-circle-info" style="color: var(--clr-gold-bright); font-size: 1.25rem;"></i>
              <div>
                <strong style="color: #FFF; display: block; font-size: 0.95rem;" id="live-status-title">
                  <?php
                    if ($st === 'received') echo "Order Received - Awaiting Kitchen Dispatch";
                    elseif ($st === 'preparing') echo "Chefs are actively preparing your authentic dishes";
                    elseif ($st === 'ready' || $st === 'outfordelivery') echo ($order_data['order_type'] === 'Delivery') ? "Driver is on the way with your food" : "Your food is freshly packed and ready for pickup";
                    elseif ($st === 'delivered') echo "Order has been delivered. Thank you!";
                    else echo "Current Status: " . ucfirst($order_data['order_status']);
                  ?>
                </strong>
                <small style="color: #888;">Live updates sync automatically every 5 seconds.</small>
              </div>
            </div>

            <a href="tel:+353214279900" class="btn btn-outline" style="padding: 0.45rem 0.95rem; font-size: 0.825rem;">
              <i class="fa-solid fa-phone"></i>
              <span>Call Kitchen</span>
            </a>
          </div>

        <?php endif; ?>

      </div>

      <!-- Itemized Receipt & Delivery Info Card -->
      <div style="background: #19100E; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 2rem; margin-bottom: 2rem;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 1rem;">
          <h3 style="font-family: var(--font-heading); font-size: 1.3rem; color: var(--clr-gold-bright); margin: 0;">
            <i class="fa-solid fa-bowl-food"></i> Ordered Dishes
          </h3>
          <button type="button" onclick="window.print()" class="btn-cropper-action" style="font-size: 0.8rem;">
            <i class="fa-solid fa-print"></i> Print Receipt
          </button>
        </div>

        <!-- Dishes Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 0.9rem;">
          <thead>
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: #888; text-align: left;">
              <th style="padding: 0.6rem 0;">Dish</th>
              <th style="padding: 0.6rem 0; text-align: center;">Quantity</th>
              <th style="padding: 0.6rem 0; text-align: right;">Unit Price</th>
              <th style="padding: 0.6rem 0; text-align: right;">Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($order_items as $it): ?>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.04); color: #EEE;">
                <td style="padding: 0.75rem 0; font-weight: 500;">
                  <?php echo htmlspecialchars($it['dish_name']); ?>
                </td>
                <td style="padding: 0.75rem 0; text-align: center; color: var(--clr-gold-bright); font-weight: 700;">
                  <?php echo $it['quantity']; ?>x
                </td>
                <td style="padding: 0.75rem 0; text-align: right; color: #AAA;">
                  €<?php echo number_format($it['dish_price'], 2); ?>
                </td>
                <td style="padding: 0.75rem 0; text-align: right; font-weight: 700; color: #FFF;">
                  €<?php echo number_format($it['item_total'], 2); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Receipt Financials -->
        <div style="display: flex; flex-direction: column; gap: 0.45rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; font-size: 0.88rem; color: #AAA; max-width: 320px; margin-left: auto;">
          <div style="display: flex; justify-content: space-between;">
            <span>Subtotal:</span>
            <strong style="color: #FFF;">€<?php echo number_format($order_data['subtotal'], 2); ?></strong>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>VAT (9%):</span>
            <strong style="color: #FFF;">€<?php echo number_format($order_data['tax'], 2); ?></strong>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Fulfillment Fee (<?php echo htmlspecialchars($order_data['order_type']); ?>):</span>
            <strong style="color: var(--clr-gold-bright);">€<?php echo number_format($order_data['delivery_fee'], 2); ?></strong>
          </div>
          <div style="display: flex; justify-content: space-between; border-top: 1px dashed rgba(255,255,255,0.2); padding-top: 0.6rem; margin-top: 0.35rem; font-size: 1.2rem; font-weight: 700; color: #FFF;">
            <span>Total Paid:</span>
            <strong style="color: var(--clr-terracotta-bright, #E07B52); font-family: var(--font-heading);">
              €<?php echo number_format($order_data['total_amount'], 2); ?>
            </strong>
          </div>
        </div>

        <!-- Destination and Contact Meta -->
        <div style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid rgba(255,255,255,0.06); display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.85rem; color: #888;">
          <div>
            <strong style="color: #FFF; display: block; margin-bottom: 0.25rem;">Recipient:</strong>
            <span><?php echo htmlspecialchars($order_data['customer_name']); ?></span><br>
            <span><i class="fa-solid fa-phone" style="color: var(--clr-gold);"></i> <?php echo htmlspecialchars($order_data['customer_phone']); ?></span>
          </div>
          <div>
            <strong style="color: #FFF; display: block; margin-bottom: 0.25rem;">Fulfillment & Address:</strong>
            <span><strong><?php echo htmlspecialchars($order_data['order_type']); ?></strong></span>
            <?php if (!empty($order_data['delivery_address'])): ?>
              <p style="margin: 0.2rem 0 0 0; color: #CCC;"><?php echo nl2br(htmlspecialchars($order_data['delivery_address'])); ?></p>
            <?php elseif (!empty($order_data['table_number'])): ?>
              <p style="margin: 0.2rem 0 0 0; color: #CCC;">Table: <?php echo htmlspecialchars($order_data['table_number']); ?></p>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- Action Buttons -->
      <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <a href="menu.php" class="btn btn-primary">
          <span>Order More Dishes</span>
          <i class="fa-solid fa-utensils"></i>
        </a>
        <a href="index.php" class="btn btn-outline">
          <span>Return to Homepage</span>
          <i class="fa-solid fa-house"></i>
        </a>
      </div>

      <!-- Real-Time Auto-Polling Script -->
      <script>
      let currentOrderNumber = '<?php echo addslashes($order_data['order_number']); ?>';
      let currentOrderStatus = '<?php echo addslashes($order_data['order_status']); ?>';
      let orderType = '<?php echo addslashes($order_data['order_type']); ?>';

      function pollLiveOrderStatus() {
        if (!currentOrderNumber) return;

        fetch(`track_order.php?ajax=order_status&order=${encodeURIComponent(currentOrderNumber)}`)
          .then(res => res.json())
          .then(data => {
            if (data.success && data.order_status !== currentOrderStatus) {
              currentOrderStatus = data.order_status;
              updateTrackerUI(data.order_status);
            }
          })
          .catch(err => {
            console.log('Tracking poll heartbeat', err);
          });
      }

      function updateTrackerUI(status) {
        const st = status.toLowerCase();
        const step1 = document.getElementById('track-step-1');
        const step2 = document.getElementById('track-step-2');
        const step3 = document.getElementById('track-step-3');
        const step4 = document.getElementById('track-step-4');
        const msgTitle = document.getElementById('live-status-title');

        // Reset
        [step1, step2, step3, step4].forEach(s => {
          if (s) {
            s.classList.remove('active');
            s.classList.remove('completed');
          }
        });

        if (st === 'received') {
          if (step1) step1.classList.add('completed');
          if (step2) step2.classList.add('active');
          if (msgTitle) msgTitle.textContent = "Order Received - Awaiting Kitchen Dispatch";
        } else if (st === 'preparing') {
          if (step1) step1.classList.add('completed');
          if (step2) step2.classList.add('completed');
          if (step3) step3.classList.add('active');
          if (msgTitle) msgTitle.textContent = "Chefs are actively preparing your authentic dishes";
        } else if (st === 'ready' || st === 'outfordelivery') {
          if (step1) step1.classList.add('completed');
          if (step2) step2.classList.add('completed');
          if (step3) step3.classList.add('completed');
          if (step4) step4.classList.add('active');
          if (msgTitle) msgTitle.textContent = (orderType === 'Delivery') ? "Driver is on the way with your food" : "Your food is freshly packed and ready for pickup";
        } else if (st === 'delivered') {
          if (step1) step1.classList.add('completed');
          if (step2) step2.classList.add('completed');
          if (step3) step3.classList.add('completed');
          if (step4) step4.classList.add('completed');
          if (msgTitle) msgTitle.textContent = "Order has been delivered. Enjoy your meal!";
        }
      }

      // Auto poll every 5 seconds
      setInterval(pollLiveOrderStatus, 5000);
      </script>

    <?php endif; ?>

  </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
