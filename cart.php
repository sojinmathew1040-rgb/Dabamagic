<?php
/**
 * Daba Magic - Dedicated Cart Page (cart.php)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db_connection.php';
$page_title = "Your Food Cart - Daba Magic";
include_once __DIR__ . '/includes/header.php';
?>

<div class="cart-page-wrapper" style="min-height: 80vh; padding: 120px 0 80px 0; background: #0E0706; position: relative;">
  <div class="container" style="max-width: 1080px; margin: 0 auto; padding: 0 1.5rem;">
    
    <!-- Title Header -->
    <div style="margin-bottom: 2.5rem; text-align: center;">
      <span style="color: var(--clr-gold-bright); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase;">
        Authentic Indian Dining
      </span>
      <h1 style="font-family: var(--font-heading); font-size: 2.75rem; color: #FFFFFF; margin: 0.35rem 0 0.5rem 0;">
        Your Shopping Cart
      </h1>
      <p style="color: #999; font-size: 0.95rem;">Review your freshly prepared dishes before proceeding to secure Razorpay checkout.</p>
    </div>

    <!-- Empty Cart Alert -->
    <div id="full-cart-empty-state" style="display: none; text-align: center; padding: 4rem 2rem; background: #19100E; border: 1px solid rgba(212,160,23,0.25); border-radius: 16px;">
      <i class="fa-solid fa-basket-shopping" style="font-size: 4rem; color: var(--clr-terracotta); margin-bottom: 1.5rem;"></i>
      <h3 style="color: #FFF; font-family: var(--font-heading); font-size: 1.8rem; margin-bottom: 0.5rem;">Your Cart is Empty</h3>
      <p style="color: #888; font-size: 0.95rem; margin-bottom: 2rem;">Add handi dum biryanis, tandoori kebabs, or creamy curries to start your order.</p>
      <a href="menu.php" class="btn btn-primary">
        <span>Explore Menu</span>
        <i class="fa-solid fa-utensils"></i>
      </a>
    </div>

    <!-- Main Full Cart Grid -->
    <div id="full-cart-main-grid" style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 2rem; align-items: start;">
      
      <!-- Left Column: Itemized Table -->
      <div style="background: #19100E; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 2rem; box-shadow: 0 20px 50px rgba(0,0,0,0.6);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 1rem;">
          <h3 style="font-family: var(--font-heading); font-size: 1.35rem; color: var(--clr-gold-bright); margin: 0;">
            <i class="fa-solid fa-bowl-food"></i> Selected Dishes
          </h3>
          <button type="button" onclick="if(confirm('Clear all dishes from your cart?')) DMCart.clearCart();" class="btn-cropper-action" style="font-size: 0.8rem; color: var(--clr-red-bright);">
            <i class="fa-solid fa-trash-can"></i> Clear All
          </button>
        </div>

        <div id="full-cart-items-table" style="display: flex; flex-direction: column; gap: 1rem;">
          <!-- Dynamically populated by JS -->
        </div>

        <div style="margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid rgba(255,255,255,0.06); display: flex; justify-content: space-between; align-items: center;">
          <a href="menu.php" style="color: var(--clr-gold-bright); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.4rem;">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Add More Dishes</span>
          </a>
        </div>
      </div>

      <!-- Right Column: Order Summary & Checkout Action -->
      <div style="background: #19100E; border: 1px solid rgba(212, 160, 23, 0.35); border-radius: 16px; padding: 2rem; box-shadow: 0 20px 50px rgba(0,0,0,0.6); position: sticky; top: 100px;">
        <h3 style="font-family: var(--font-heading); font-size: 1.35rem; color: var(--clr-gold-bright); margin-bottom: 1.25rem;">
          <i class="fa-solid fa-receipt"></i> Order Summary
        </h3>

        <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.9rem; color: #AAA; margin-bottom: 1.5rem;">
          <div style="display: flex; justify-content: space-between;">
            <span>Items Subtotal:</span>
            <strong id="full-cart-subtotal" style="color: #FFF;">€0.00</strong>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Estimated VAT (9%):</span>
            <strong id="full-cart-tax" style="color: #FFF;">€0.00</strong>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>Delivery / Pickup:</span>
            <span style="color: var(--clr-gold-bright);">Calculated at Checkout</span>
          </div>
          <div style="display: flex; justify-content: space-between; border-top: 1px dashed rgba(255,255,255,0.15); padding-top: 0.85rem; margin-top: 0.25rem; font-size: 1.25rem; font-weight: 700; color: #FFF;">
            <span>Estimated Total:</span>
            <strong id="full-cart-total" style="color: var(--clr-terracotta-bright, #E07B52); font-family: var(--font-heading);">€0.00</strong>
          </div>
        </div>

        <a href="checkout.php" class="cart-checkout-btn" style="height: 52px; font-size: 1rem;">
          <span>Proceed to Checkout</span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>

        <!-- Payment Gateway Trust Badge -->
        <div style="background: rgba(212, 160, 23, 0.08); border: 1px solid rgba(212, 160, 23, 0.2); border-radius: 8px; padding: 0.85rem; margin-top: 1.5rem; text-align: center; font-size: 0.8rem; color: #BBB;">
          <i class="fa-solid fa-shield-halved" style="color: var(--clr-gold-bright); margin-right: 0.35rem;"></i>
          Razorpay Payment Gateway with UPI & 256-bit SSL Security
        </div>
      </div>

    </div>

  </div>
</div>

<script>
function renderFullCartPage() {
  const cart = DMCart.getCart();
  const emptyState = document.getElementById('full-cart-empty-state');
  const mainGrid = document.getElementById('full-cart-main-grid');
  const tableContainer = document.getElementById('full-cart-items-table');

  if (!cart || cart.length === 0) {
    if (emptyState) emptyState.style.display = 'block';
    if (mainGrid) mainGrid.style.display = 'none';
    return;
  }

  if (emptyState) emptyState.style.display = 'none';
  if (mainGrid) mainGrid.style.display = 'grid';

  const subtotal = DMCart.getSubtotal();
  const tax = subtotal * 0.09;
  const total = subtotal + tax;

  let html = '';
  cart.forEach(item => {
    const lineTotal = (item.price * item.quantity).toFixed(2);
    const imgSrc = item.image.startsWith('http') || item.image.startsWith('assets/') 
      ? item.image 
      : `assets/images/${item.image}`;

    html += `
      <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 1rem; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 1rem; min-width: 200px; flex: 1;">
          <img src="${imgSrc}" alt="${item.name}" style="width: 60px; height: 60px; border-radius: 8px; object-fit: cover; border: 1px solid rgba(255,255,255,0.1);" onerror="this.onerror=null; this.src='assets/images/default_dish.png';">
          <div>
            <h4 style="margin: 0; color: #FFF; font-family: var(--font-heading); font-size: 1.05rem;">${item.name}</h4>
            <span style="color: #888; font-size: 0.8rem;">€${item.price.toFixed(2)} each</span>
          </div>
        </div>

        <div style="display: flex; align-items: center; gap: 1.5rem;">
          <div class="cart-qty-pill">
            <button type="button" onclick="DMCart.updateQuantity('${item.id}', -1); renderFullCartPage();"><i class="fa-solid fa-minus"></i></button>
            <span>${item.quantity}</span>
            <button type="button" onclick="DMCart.updateQuantity('${item.id}', 1); renderFullCartPage();"><i class="fa-solid fa-plus"></i></button>
          </div>
          <strong style="color: var(--clr-terracotta-bright); font-size: 1.05rem; min-width: 70px; text-align: right;">€${lineTotal}</strong>
          <button type="button" onclick="DMCart.removeItem('${item.id}'); renderFullCartPage();" style="background: none; border: none; color: #888; cursor: pointer;" title="Remove dish">
            <i class="fa-solid fa-trash-can" style="font-size: 1rem;"></i>
          </button>
        </div>
      </div>
    `;
  });

  if (tableContainer) tableContainer.innerHTML = html;
  document.getElementById('full-cart-subtotal').textContent = `€${subtotal.toFixed(2)}`;
  document.getElementById('full-cart-tax').textContent = `€${tax.toFixed(2)}`;
  document.getElementById('full-cart-total').textContent = `€${total.toFixed(2)}`;
}

document.addEventListener('DOMContentLoaded', () => {
  renderFullCartPage();
});
</script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
