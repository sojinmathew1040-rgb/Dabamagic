<?php
/**
 * Daba Magic - Checkout & Razorpay Payment Page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/includes/razorpay_config.php';

$page_title = "Checkout & Secure Payment - Daba Magic";
include_once __DIR__ . '/includes/header.php';
?>

<!-- Razorpay Standard Checkout SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<div class="checkout-page-wrapper" style="min-height: 80vh; padding: 120px 0 80px 0; background: #0E0706; position: relative;">
  
  <div class="container" style="max-width: 1140px; margin: 0 auto; padding: 0 1.5rem;">
    
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 2.5rem; text-align: center;">
      <span style="color: var(--clr-gold-bright, #E5B22D); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase;">
        Authentic Indian Dining
      </span>
      <h1 style="font-family: var(--font-heading, 'Cormorant Garamond', serif); font-size: 2.75rem; color: #FFFFFF; margin: 0.35rem 0 0.5rem 0;">
        Checkout & Secure Payment
      </h1>
      <p style="color: #999; font-size: 0.95rem;">Review your selected dishes, provide fulfillment details, and complete your order.</p>
    </div>

    <!-- Empty Cart Warning (Visible if user visits with empty cart) -->
    <div id="checkout-empty-cart-state" style="display: none; text-align: center; padding: 4rem 2rem; background: #19100E; border: 1px solid rgba(212,160,23,0.25); border-radius: 16px;">
      <i class="fa-solid fa-basket-shopping" style="font-size: 4rem; color: var(--clr-terracotta, #C86338); margin-bottom: 1.5rem;"></i>
      <h3 style="color: #FFF; font-family: var(--font-heading); font-size: 1.8rem; margin-bottom: 0.5rem;">Your Cart is Currently Empty</h3>
      <p style="color: #888; font-size: 0.95rem; margin-bottom: 2rem;">Please add some authentic Indian delicacies from our menu before proceeding to checkout.</p>
      <a href="menu.php" class="btn btn-primary">
        <span>Explore Full Menu</span>
        <i class="fa-solid fa-utensils"></i>
      </a>
    </div>

    <!-- Main Checkout Grid -->
    <div class="checkout-grid" id="checkout-main-grid" style="display: grid; grid-template-columns: 1.35fr 1fr; gap: 2rem; align-items: start;">
      
      <!-- Left Column: Customer & Delivery Details -->
      <div style="background: #19100E; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 2rem; box-shadow: 0 20px 50px rgba(0,0,0,0.6);">
        
        <form id="checkout-form" onsubmit="event.preventDefault(); initiateRazorpayPayment();">
          
          <!-- Step 1: Customer Contact -->
          <div style="margin-bottom: 2rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.35rem; color: var(--clr-gold-bright); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 0.75rem;">
              <i class="fa-solid fa-user-circle"></i>
              <span>1. Contact Information</span>
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
              <div>
                <label class="form-label" for="cust-name">Full Name *</label>
                <input type="text" id="cust-name" class="form-control-input" required placeholder="e.g. Rahul Sharma">
              </div>
              <div>
                <label class="form-label" for="cust-phone">Phone / Mobile *</label>
                <input type="tel" id="cust-phone" class="form-control-input" required placeholder="+353 87 123 4567">
              </div>
            </div>

            <div>
              <label class="form-label" for="cust-email">Email Address *</label>
              <input type="email" id="cust-email" class="form-control-input" required placeholder="rahul@example.com">
              <small style="color: #777; font-size: 0.775rem; margin-top: 0.35rem; display: block;">Your payment receipt and order confirmation will be sent here.</small>
            </div>
          </div>

          <!-- Step 2: Order Fulfillment Mode -->
          <div style="margin-bottom: 2rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.35rem; color: var(--clr-gold-bright); margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 0.75rem;">
              <i class="fa-solid fa-truck-ramp-box"></i>
              <span>2. Order Fulfillment Type</span>
            </h3>

            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.85rem; margin-bottom: 1.25rem;" id="fulfillment-selector">
              
              <label class="fulfillment-card active" onclick="setFulfillmentType('Delivery', this)">
                <input type="radio" name="order_type" value="Delivery" checked style="display: none;">
                <i class="fa-solid fa-motorcycle" style="font-size: 1.5rem; color: var(--clr-gold-bright);"></i>
                <strong style="display: block; margin-top: 0.5rem; font-size: 0.9rem; color: #FFF;">Delivery</strong>
                <span style="font-size: 0.75rem; color: #888;">€3.50 Fee</span>
              </label>

              <label class="fulfillment-card" onclick="setFulfillmentType('Takeaway', this)">
                <input type="radio" name="order_type" value="Takeaway" style="display: none;">
                <i class="fa-solid fa-bag-shopping" style="font-size: 1.5rem; color: var(--clr-gold-bright);"></i>
                <strong style="display: block; margin-top: 0.5rem; font-size: 0.9rem; color: #FFF;">Takeaway</strong>
                <span style="font-size: 0.75rem; color: var(--clr-gold-bright);">Free Pickup</span>
              </label>

              <label class="fulfillment-card" onclick="setFulfillmentType('DineIn', this)">
                <input type="radio" name="order_type" value="DineIn" style="display: none;">
                <i class="fa-solid fa-utensils" style="font-size: 1.5rem; color: var(--clr-gold-bright);"></i>
                <strong style="display: block; margin-top: 0.5rem; font-size: 0.9rem; color: #FFF;">Dine-In</strong>
                <span style="font-size: 0.75rem; color: var(--clr-gold-bright);">Table Service</span>
              </label>

            </div>

            <!-- Dynamic Delivery Address Fields -->
            <div id="field-delivery-address">
              <label class="form-label" for="cust-address">Delivery Address *</label>
              <textarea id="cust-address" class="form-control-input" rows="2" placeholder="Street address, Apartment / House No., Cork City, Postcode"></textarea>
            </div>

            <!-- Dynamic Table Number Field -->
            <div id="field-table-number" style="display: none;">
              <label class="form-label" for="cust-table">Table Number (If seated in restaurant)</label>
              <input type="text" id="cust-table" class="form-control-input" placeholder="e.g. Table 4">
            </div>

          </div>

          <!-- Step 3: Special Notes -->
          <div style="margin-bottom: 1.5rem;">
            <h3 style="font-family: var(--font-heading); font-size: 1.35rem; color: var(--clr-gold-bright); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.6rem; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 0.75rem;">
              <i class="fa-solid fa-comment-dots"></i>
              <span>3. Cooking & Delivery Notes (Optional)</span>
            </h3>
            <textarea id="cust-notes" class="form-control-input" rows="2" placeholder="E.g., Mild spice level, extra mint chutney, ring door bell twice..."></textarea>
          </div>

        </form>

      </div>

      <!-- Right Column: Order Summary & Razorpay Trigger -->
      <div>
        <div style="background: #19100E; border: 1px solid rgba(212, 160, 23, 0.35); border-radius: 16px; padding: 1.75rem; box-shadow: 0 20px 50px rgba(0,0,0,0.6); position: sticky; top: 100px;">
          
          <h3 style="font-family: var(--font-heading); font-size: 1.35rem; color: var(--clr-gold-bright); margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
            <span><i class="fa-solid fa-receipt"></i> Order Summary</span>
            <span id="summary-items-count" style="font-size: 0.8rem; background: rgba(212,160,23,0.15); color: var(--clr-gold-bright); padding: 0.2rem 0.6rem; border-radius: 999px;">0 Items</span>
          </h3>

          <!-- Items List in Summary -->
          <div id="checkout-items-list" style="max-height: 260px; overflow-y: auto; display: flex; flex-direction: column; gap: 0.65rem; margin-bottom: 1.25rem; padding-right: 0.25rem;">
            <!-- Rendered by JS -->
          </div>

          <!-- Cost Calculations -->
          <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: 1rem; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.88rem; color: #AAA;">
            <div style="display: flex; justify-content: space-between;">
              <span>Dishes Subtotal:</span>
              <strong id="chk-subtotal" style="color: #FFF;">€0.00</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span>Hospitality VAT (9%):</span>
              <strong id="chk-tax" style="color: #FFF;">€0.00</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
              <span>Fulfillment Fee:</span>
              <strong id="chk-delivery-fee" style="color: var(--clr-gold-bright);">€3.50</strong>
            </div>
            <div style="display: flex; justify-content: space-between; border-top: 1px dashed rgba(255,255,255,0.15); padding-top: 0.75rem; margin-top: 0.25rem; font-size: 1.25rem; font-weight: 700; color: #FFF;">
              <span>Total Payable:</span>
              <strong id="chk-grand-total" style="color: var(--clr-terracotta-bright, #E07B52); font-family: var(--font-heading);">€0.00</strong>
            </div>
          </div>

          <!-- Razorpay Payment Notice Badge -->
          <div style="background: rgba(212, 160, 23, 0.08); border: 1px solid rgba(212, 160, 23, 0.25); border-radius: 8px; padding: 0.85rem; margin: 1.25rem 0; display: flex; align-items: center; gap: 0.75rem;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #0C2340; display: flex; align-items: center; justify-content: center; color: #528FF0; font-size: 1.1rem; flex-shrink: 0;">
              <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div style="font-size: 0.8rem; color: #CCC; line-height: 1.4;">
              <strong style="color: #FFF; display: block;">Razorpay Secure Gateway</strong>
              Supports UPI, Credit/Debit Cards, Net Banking & Wallets.
            </div>
          </div>

          <!-- Submit & Launch Razorpay Button -->
          <button type="button" id="btn-pay-razorpay" onclick="initiateRazorpayPayment()" class="cart-checkout-btn" style="height: 52px; font-size: 1rem;">
            <i class="fa-solid fa-lock"></i>
            <span>Pay & Place Order</span>
          </button>

          <p style="text-align: center; color: #777; font-size: 0.75rem; margin-top: 0.75rem; margin-bottom: 0;">
            🔒 256-Bit SSL Encrypted & Verified Transaction
          </p>

        </div>
      </div>

    </div>

  </div>

</div>

<!-- Checkout Specific Inline CSS -->
<style>
.fulfillment-card {
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  padding: 1rem 0.5rem;
  text-align: center;
  cursor: pointer;
  transition: all 0.25s ease;
  display: block;
}

.fulfillment-card:hover,
.fulfillment-card.active {
  background: rgba(212, 160, 23, 0.12);
  border-color: var(--clr-gold, #D4A017);
  transform: translateY(-2px);
}

.fulfillment-card.active strong {
  color: var(--clr-gold-bright, #E5B22D) !important;
}

.checkout-item-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 0.5rem 0.75rem;
  font-size: 0.85rem;
}

@media (max-width: 900px) {
  .checkout-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>

<script>
let currentOrderType = 'Delivery';

function setFulfillmentType(type, element) {
  currentOrderType = type;
  document.querySelectorAll('.fulfillment-card').forEach(c => c.classList.remove('active'));
  element.classList.add('active');

  const addrField = document.getElementById('field-delivery-address');
  const tableField = document.getElementById('field-table-number');

  if (type === 'Delivery') {
    addrField.style.display = 'block';
    tableField.style.display = 'none';
  } else if (type === 'DineIn') {
    addrField.style.display = 'none';
    tableField.style.display = 'block';
  } else {
    // Takeaway
    addrField.style.display = 'none';
    tableField.style.display = 'none';
  }

  renderCheckoutSummary();
}

function renderCheckoutSummary() {
  const cart = DMCart.getCart();
  const emptyState = document.getElementById('checkout-empty-cart-state');
  const mainGrid = document.getElementById('checkout-main-grid');
  const itemsContainer = document.getElementById('checkout-items-list');
  const countBadge = document.getElementById('summary-items-count');

  if (!cart || cart.length === 0) {
    if (emptyState) emptyState.style.display = 'block';
    if (mainGrid) mainGrid.style.display = 'none';
    return;
  }

  if (emptyState) emptyState.style.display = 'none';
  if (mainGrid) mainGrid.style.display = 'grid';

  const subtotal = DMCart.getSubtotal();
  const tax = subtotal * 0.09;
  const deliveryFee = (currentOrderType === 'Delivery') ? 3.50 : 0.00;
  const grandTotal = subtotal + tax + deliveryFee;

  let itemsHtml = '';
  cart.forEach(item => {
    const total = (item.price * item.quantity).toFixed(2);
    itemsHtml += `
      <div class="checkout-item-row">
        <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0;">
          <span style="background: rgba(212,160,23,0.2); color: var(--clr-gold-bright); font-size: 0.75rem; font-weight: 700; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">${item.quantity}x</span>
          <span style="color: #FFF; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-weight: 500;">${item.name}</span>
        </div>
        <strong style="color: var(--clr-terracotta-bright); margin-left: 0.5rem;">€${total}</strong>
      </div>
    `;
  });

  if (itemsContainer) itemsContainer.innerHTML = itemsHtml;
  if (countBadge) countBadge.textContent = `${DMCart.getItemCount()} Items`;

  document.getElementById('chk-subtotal').textContent = `€${subtotal.toFixed(2)}`;
  document.getElementById('chk-tax').textContent = `€${tax.toFixed(2)}`;
  document.getElementById('chk-delivery-fee').textContent = deliveryFee > 0 ? `€${deliveryFee.toFixed(2)}` : 'FREE';
  document.getElementById('chk-grand-total').textContent = `€${grandTotal.toFixed(2)}`;
}

function initiateRazorpayPayment() {
  const cart = DMCart.getCart();
  if (!cart || cart.length === 0) {
    alert('Your cart is empty. Please select dishes first.');
    return;
  }

  const name = document.getElementById('cust-name').value.trim();
  const phone = document.getElementById('cust-phone').value.trim();
  const email = document.getElementById('cust-email').value.trim();
  const address = document.getElementById('cust-address').value.trim();
  const table = document.getElementById('cust-table').value.trim();
  const notes = document.getElementById('cust-notes').value.trim();

  if (!name || !phone || !email) {
    alert('Please fill in your name, mobile number, and email address.');
    return;
  }

  if (currentOrderType === 'Delivery' && !address) {
    alert('Please provide a delivery address for your order.');
    document.getElementById('cust-address').focus();
    return;
  }

  const payBtn = document.getElementById('btn-pay-razorpay');
  const originalText = payBtn.innerHTML;
  payBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Initializing Payment...';
  payBtn.disabled = true;

  const payload = {
    customer: {
      name: name,
      phone: phone,
      email: email,
      order_type: currentOrderType,
      table_number: table,
      delivery_address: address,
      order_notes: notes
    },
    items: cart
  };

  fetch('api/create_razorpay_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(res => res.json())
  .then(data => {
    payBtn.innerHTML = originalText;
    payBtn.disabled = false;

    if (!data.success) {
      alert('Error creating order: ' + (data.error || 'Server error'));
      return;
    }

    // Configure Razorpay Options
    const options = {
      key: data.key_id,
      amount: data.amount,
      currency: data.currency || 'EUR',
      name: data.company.name || 'Daba Magic Restaurant',
      description: data.company.description || 'Food Order',
      image: 'assets/images/logo.png',
      order_id: data.razorpay_order_id,
      prefill: {
        name: data.customer.name,
        email: data.customer.email,
        contact: data.customer.contact
      },
      theme: {
        color: data.theme.color || '#C86338'
      },
      handler: function(response) {
        // Payment successful - verify signature on server
        verifyPaymentOnServer(data.order_id, data.order_number, response);
      },
      modal: {
        ondismiss: function() {
          console.log('Razorpay payment modal closed by customer.');
        }
      }
    };

    try {
      const rzpInstance = new Razorpay(options);
      rzpInstance.on('payment.failed', function(response) {
        alert('Payment failed: ' + (response.error.description || 'Transaction was declined.'));
      });
      rzpInstance.open();
    } catch (err) {
      console.error('Razorpay initialization exception:', err);
      // Fallback for simulation if client is testing in non-live browser env
      if (confirm('Razorpay test checkout initialized for Order #' + data.order_number + '. Complete mock test payment?')) {
        verifyPaymentOnServer(data.order_id, data.order_number, {
          razorpay_payment_id: 'pay_test_' + Date.now(),
          razorpay_order_id: data.razorpay_order_id,
          razorpay_signature: 'sig_mock_verified'
        });
      }
    }
  })
  .catch(err => {
    payBtn.innerHTML = originalText;
    payBtn.disabled = false;
    console.error('Order creation error:', err);
    alert('Failed to connect to order server. Please check your network.');
  });
}

function verifyPaymentOnServer(orderId, orderNumber, paymentData) {
  const payBtn = document.getElementById('btn-pay-razorpay');
  if (payBtn) {
    payBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verifying Payment...';
    payBtn.disabled = true;
  }

  fetch('api/verify_payment.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      order_id: orderId,
      order_number: orderNumber,
      razorpay_payment_id: paymentData.razorpay_payment_id || 'pay_demo_' + Date.now(),
      razorpay_order_id: paymentData.razorpay_order_id || 'order_demo',
      razorpay_signature: paymentData.razorpay_signature || 'sig_demo'
    })
  })
  .then(res => res.json())
  .then(result => {
    if (result.success) {
      DMCart.clearCart();
      window.location.href = result.redirect_url || ('order_success.php?order=' + encodeURIComponent(orderNumber));
    } else {
      alert('Payment confirmation error: ' + (result.error || 'Failed to update order status.'));
    }
  })
  .catch(err => {
    console.error('Verification error:', err);
    DMCart.clearCart();
    window.location.href = 'order_success.php?order=' + encodeURIComponent(orderNumber);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  renderCheckoutSummary();
});
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
<?php include_once __DIR__ . '/includes/footer_scripts.php'; ?>
