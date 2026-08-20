<?php
/**
 * Daba Magic - Order Success & Live Tracking Page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/includes/razorpay_config.php';

$order_num = $_GET['order'] ?? ($_SESSION['last_order_number'] ?? '');
$order_data = null;
$order_items = [];

if (!empty($order_num)) {
    $stmt = $con->prepare("SELECT * FROM tbl_orders WHERE order_number = ?");
    $stmt->bind_param("s", $order_num);
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
    }
}

$page_title = "Order Confirmed - Daba Magic";
include_once __DIR__ . '/includes/header.php';
?>

<div class="order-success-wrapper" style="min-height: 80vh; padding: 120px 0 80px 0; background: #0E0706; position: relative;">
  <div class="container" style="max-width: 850px; margin: 0 auto; padding: 0 1.5rem;">
    
    <?php if (!$order_data): ?>
      <!-- Order Not Found State -->
      <div style="text-align: center; padding: 4rem 2rem; background: #19100E; border: 1px solid rgba(212,160,23,0.25); border-radius: 16px;">
        <i class="fa-solid fa-circle-exclamation" style="font-size: 3.5rem; color: var(--clr-terracotta); margin-bottom: 1.5rem;"></i>
        <h2 style="color: #FFF; font-family: var(--font-heading); font-size: 2rem; margin-bottom: 0.5rem;">Order Not Found</h2>
        <p style="color: #888; margin-bottom: 2rem;">We could not locate that order number. Please check your confirmation email or order history.</p>
        <a href="menu.php" class="btn btn-primary">
          <span>Explore Menu</span>
          <i class="fa-solid fa-utensils"></i>
        </a>
      </div>
    <?php else: ?>

      <!-- Success Header Card -->
      <div style="background: #19100E; border: 1px solid rgba(212,160,23,0.35); border-radius: 16px; padding: 2.5rem 2rem; text-align: center; margin-bottom: 2rem; box-shadow: 0 20px 60px rgba(0,0,0,0.7);">
        
        <div style="width: 76px; height: 76px; border-radius: 50%; background: rgba(92,148,51,0.18); border: 2px solid var(--clr-green-bright, #70B83E); color: var(--clr-green-bright, #70B83E); font-size: 2.5rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem auto;">
          <i class="fa-solid fa-check"></i>
        </div>

        <span style="background: rgba(212,160,23,0.15); color: var(--clr-gold-bright); font-size: 0.825rem; font-weight: 700; padding: 0.35rem 0.9rem; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.1em;">
          Payment Verified & Received
        </span>

        <h1 style="font-family: var(--font-heading); font-size: 2.4rem; color: #FFFFFF; margin: 0.75rem 0 0.35rem 0;">
          Thank You, <?php echo htmlspecialchars($order_data['customer_name']); ?>!
        </h1>
        <p style="color: #AAA; font-size: 1rem; margin-bottom: 1.5rem;">
          Your order has been received by our kitchen. We are preparing authentic flavors for you!
        </p>

        <div style="display: inline-flex; align-items: center; gap: 0.6rem; background: rgba(255,255,255,0.04); border: 1px dashed rgba(212,160,23,0.4); padding: 0.6rem 1.25rem; border-radius: 8px; font-size: 0.95rem;">
          <span style="color: #888;">Order Number:</span>
          <strong style="color: var(--clr-gold-bright); font-family: monospace; font-size: 1.05rem; letter-spacing: 0.05em;">
            <?php echo htmlspecialchars($order_data['order_number']); ?>
          </strong>
        </div>

      </div>

      <!-- Live Order Status Tracker Card -->
      <div style="background: #19100E; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 2rem; margin-bottom: 2rem;">
        <h3 style="font-family: var(--font-heading); font-size: 1.3rem; color: var(--clr-gold-bright); margin-bottom: 1.75rem; display: flex; align-items: center; gap: 0.5rem;">
          <i class="fa-solid fa-clock-rotate-left"></i>
          <span>Live Kitchen & Fulfillment Status</span>
        </h3>

        <?php
          $st = strtolower($order_data['order_status']);
          $step1_active = true;
          $step2_active = in_array($st, ['preparing', 'ready', 'outfordelivery', 'delivered']);
          $step3_active = in_array($st, ['outfordelivery', 'ready', 'delivered']);
          $step4_active = ($st === 'delivered');
        ?>

        <div class="order-tracker-timeline">
          
          <div class="tracker-step <?php echo $step1_active ? 'completed' : ''; ?>">
            <div class="tracker-icon"><i class="fa-solid fa-receipt"></i></div>
            <strong class="tracker-label">Order Received</strong>
            <small class="tracker-time"><?php echo date('H:i', strtotime($order_data['created_at'])); ?></small>
          </div>

          <div class="tracker-step <?php echo $step2_active ? 'completed' : ($st === 'received' ? 'active' : ''); ?>">
            <div class="tracker-icon"><i class="fa-solid fa-fire-burner"></i></div>
            <strong class="tracker-label">Kitchen Preparing</strong>
            <small class="tracker-time">Fresh & Hot</small>
          </div>

          <div class="tracker-step <?php echo $step3_active ? 'completed' : ($st === 'preparing' ? 'active' : ''); ?>">
            <div class="tracker-icon"><i class="fa-solid <?php echo ($order_data['order_type'] === 'Delivery') ? 'fa-motorcycle' : 'fa-bell-concierge'; ?>"></i></div>
            <strong class="tracker-label"><?php echo ($order_data['order_type'] === 'Delivery') ? 'Out for Delivery' : 'Ready for Pickup'; ?></strong>
            <small class="tracker-time">En Route</small>
          </div>

          <div class="tracker-step <?php echo $step4_active ? 'completed' : ''; ?>">
            <div class="tracker-icon"><i class="fa-solid fa-circle-check"></i></div>
            <strong class="tracker-label">Delivered</strong>
            <small class="tracker-time">Bon Appétit</small>
          </div>

        </div>
      </div>

      <!-- Itemized Order Receipt Card -->
      <div style="background: #19100E; border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 2rem; margin-bottom: 2rem;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 1rem;">
          <h3 style="font-family: var(--font-heading); font-size: 1.3rem; color: var(--clr-gold-bright); margin: 0; display: flex; align-items: center; gap: 0.5rem;">
            <i class="fa-solid fa-file-invoice"></i>
            <span>Order Summary & Invoice</span>
          </h3>
          <button type="button" onclick="window.print()" class="btn-cropper-action" style="font-size: 0.8rem; padding: 0.35rem 0.85rem;">
            <i class="fa-solid fa-print"></i>
            <span>Print Receipt</span>
          </button>
        </div>

        <!-- Ordered Dishes Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 0.9rem;">
          <thead>
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); color: #888; text-align: left;">
              <th style="padding: 0.6rem 0;">Dish Name</th>
              <th style="padding: 0.6rem 0; text-align: center;">Qty</th>
              <th style="padding: 0.6rem 0; text-align: right;">Unit Price</th>
              <th style="padding: 0.6rem 0; text-align: right;">Total</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($order_items as $item): ?>
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.04); color: #EEE;">
                <td style="padding: 0.75rem 0; font-weight: 500;">
                  <?php echo htmlspecialchars($item['dish_name']); ?>
                </td>
                <td style="padding: 0.75rem 0; text-align: center; color: var(--clr-gold-bright);">
                  <?php echo $item['quantity']; ?>
                </td>
                <td style="padding: 0.75rem 0; text-align: right; color: #AAA;">
                  €<?php echo number_format($item['dish_price'], 2); ?>
                </td>
                <td style="padding: 0.75rem 0; text-align: right; font-weight: 700; color: #FFF;">
                  €<?php echo number_format($item['item_total'], 2); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

        <!-- Receipt Financial Summary -->
        <div style="display: flex; flex-direction: column; gap: 0.45rem; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1rem; font-size: 0.88rem; color: #AAA; max-width: 320px; margin-left: auto;">
          <div style="display: flex; justify-content: space-between;">
            <span>Subtotal:</span>
            <strong style="color: #FFF;">€<?php echo number_format($order_data['subtotal'], 2); ?></strong>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Hospitality VAT (9%):</span>
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

        <!-- Payment & Delivery Meta Info -->
        <div style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid rgba(255,255,255,0.06); display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.85rem; color: #888;">
          <div>
            <strong style="color: #FFF; display: block; margin-bottom: 0.25rem;">Payment Reference:</strong>
            <span><i class="fa-solid fa-credit-card" style="color: var(--clr-gold);"></i> Razorpay Gateway (<?php echo htmlspecialchars($order_data['payment_status']); ?>)</span><br>
            <code style="color: #AAA; font-size: 0.75rem;"><?php echo htmlspecialchars($order_data['razorpay_payment_id'] ?? 'pay_verified'); ?></code>
          </div>
          <div>
            <strong style="color: #FFF; display: block; margin-bottom: 0.25rem;">Fulfillment & Destination:</strong>
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

    <?php endif; ?>

  </div>
</div>

<style>
/* Tracker Timeline */
.order-tracker-timeline {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  margin: 1rem 0;
}

.order-tracker-timeline::before {
  content: '';
  position: absolute;
  top: 24px;
  left: 30px;
  right: 30px;
  height: 3px;
  background: rgba(255, 255, 255, 0.1);
  z-index: 1;
}

.tracker-step {
  position: relative;
  z-index: 2;
  text-align: center;
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.tracker-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #140C0B;
  border: 2px solid rgba(255, 255, 255, 0.15);
  color: #666;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
  transition: all 0.3s ease;
}

.tracker-label {
  font-size: 0.85rem;
  color: #888;
  display: block;
}

.tracker-time {
  font-size: 0.75rem;
  color: #666;
}

.tracker-step.completed .tracker-icon {
  background: var(--clr-green-bright, #70B83E);
  border-color: var(--clr-green-bright, #70B83E);
  color: #110B0A;
  box-shadow: 0 0 15px rgba(112, 184, 62, 0.5);
}

.tracker-step.completed .tracker-label {
  color: #FFF;
}

.tracker-step.active .tracker-icon {
  background: var(--clr-gold, #D4A017);
  border-color: var(--clr-gold, #D4A017);
  color: #110B0A;
  animation: pulseActive 1.5s infinite;
}

.tracker-step.active .tracker-label {
  color: var(--clr-gold-bright, #E5B22D);
  font-weight: 700;
}

@keyframes pulseActive {
  0% { transform: scale(1); box-shadow: 0 0 0 rgba(212, 160, 23, 0.6); }
  50% { transform: scale(1.1); box-shadow: 0 0 18px rgba(212, 160, 23, 0.8); }
  100% { transform: scale(1); box-shadow: 0 0 0 rgba(212, 160, 23, 0.6); }
}

@media (max-width: 600px) {
  .order-tracker-timeline {
    flex-direction: column;
    align-items: flex-start;
    gap: 1.5rem;
  }
  .order-tracker-timeline::before {
    left: 24px;
    top: 20px;
    bottom: 20px;
    width: 3px;
    height: auto;
  }
  .tracker-step {
    flex-direction: row;
    gap: 1rem;
    text-align: left;
    align-items: center;
  }
}

@media print {
  body { background: #FFF !important; color: #000 !important; }
  .site-header, .site-footer, .btn, .btn-cropper-action, .order-tracker-timeline, .dm-cart-drawer, .dm-cart-backdrop { display: none !important; }
  .order-success-wrapper { padding: 0 !important; background: #FFF !important; }
  div { box-shadow: none !important; border: 1px solid #CCC !important; color: #000 !important; }
  h1, h2, h3, strong, td, th { color: #000 !important; }
}
</style>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
<?php include_once __DIR__ . '/includes/footer_scripts.php'; ?>
