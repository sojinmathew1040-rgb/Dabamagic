/**
 * Daba Magic - Client-Side Cart & Ordering System
 */

const DMCart = (function() {
  const STORAGE_KEY = 'dm_cart';

  function getCart() {
    try {
      const data = localStorage.getItem(STORAGE_KEY);
      return data ? JSON.parse(data) : [];
    } catch (e) {
      console.error('Error reading cart from localStorage', e);
      return [];
    }
  }

  function saveCart(cart) {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
      updateUI();
    } catch (e) {
      console.error('Error saving cart to localStorage', e);
    }
  }

  function addItem(item) {
    const cart = getCart();
    const existingIndex = cart.findIndex(i => i.id == item.id || (i.name === item.name && !item.id));

    if (existingIndex > -1) {
      cart[existingIndex].quantity += (item.quantity || 1);
    } else {
      cart.push({
        id: item.id || Date.now(),
        name: item.name,
        price: parseFloat(item.price),
        image: item.image || 'default_dish.png',
        category: item.category || 'Curries',
        quantity: item.quantity || 1
      });
    }

    saveCart(cart);
    showToast(`Added "${item.name}" to cart!`);
    openCartDrawer();
  }

  function removeItem(id) {
    let cart = getCart();
    cart = cart.filter(i => i.id != id);
    saveCart(cart);
  }

  function updateQuantity(id, delta) {
    let cart = getCart();
    const item = cart.find(i => i.id == id);
    if (item) {
      item.quantity += delta;
      if (item.quantity <= 0) {
        cart = cart.filter(i => i.id != id);
      }
      saveCart(cart);
    }
  }

  function clearCart() {
    localStorage.removeItem(STORAGE_KEY);
    updateUI();
  }

  function getSubtotal() {
    const cart = getCart();
    return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  }

  function getItemCount() {
    const cart = getCart();
    return cart.reduce((sum, item) => sum + item.quantity, 0);
  }

  function updateUI() {
    const cart = getCart();
    const count = getItemCount();
    const subtotal = getSubtotal();

    // Update Header Badges
    document.querySelectorAll('.cart-count-badge').forEach(badge => {
      badge.textContent = count;
      badge.style.display = count > 0 ? 'flex' : 'none';
    });

    // Update Cart Drawer Items List
    const drawerItemsContainer = document.getElementById('dm-cart-items-list');
    const drawerSubtotal = document.getElementById('dm-cart-subtotal');
    const drawerTotal = document.getElementById('dm-cart-total');
    const checkoutBtn = document.getElementById('dm-cart-checkout-btn');

    if (drawerItemsContainer) {
      if (cart.length === 0) {
        drawerItemsContainer.innerHTML = `
          <div class="cart-empty-state">
            <i class="fa-solid fa-basket-shopping"></i>
            <h4>Your cart is empty</h4>
            <p>Browse our authentic dishes and add delicious Indian specials to your order.</p>
            <a href="menu.php" class="btn-dm-primary" onclick="DMCart.closeCartDrawer()">
              <span>Explore Menu</span>
              <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>
        `;
        if (checkoutBtn) {
          checkoutBtn.classList.add('disabled');
          checkoutBtn.setAttribute('disabled', 'true');
        }
      } else {
        let html = '';
        cart.forEach(item => {
          const itemTotal = (item.price * item.quantity).toFixed(2);
          const imgSrc = item.image.startsWith('http') || item.image.startsWith('assets/') 
            ? item.image 
            : `assets/images/${item.image}`;

          html += `
            <div class="cart-drawer-item" data-id="${item.id}">
              <div class="cart-item-thumb">
                <img src="${imgSrc}" alt="${item.name}" onerror="this.onerror=null; this.src='assets/images/default_dish.png';">
              </div>
              <div class="cart-item-details">
                <h5 class="cart-item-title">${item.name}</h5>
                <span class="cart-item-unit-price">€${item.price.toFixed(2)} each</span>
                <div class="cart-item-qty-row">
                  <div class="cart-qty-pill">
                    <button type="button" onclick="DMCart.updateQuantity('${item.id}', -1)" aria-label="Decrease quantity">
                      <i class="fa-solid fa-minus"></i>
                    </button>
                    <span>${item.quantity}</span>
                    <button type="button" onclick="DMCart.updateQuantity('${item.id}', 1)" aria-label="Increase quantity">
                      <i class="fa-solid fa-plus"></i>
                    </button>
                  </div>
                  <strong class="cart-item-total">€${itemTotal}</strong>
                </div>
              </div>
              <button type="button" class="cart-item-remove-btn" onclick="DMCart.removeItem('${item.id}')" title="Remove item">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </div>
          `;
        });
        drawerItemsContainer.innerHTML = html;

        if (checkoutBtn) {
          checkoutBtn.classList.remove('disabled');
          checkoutBtn.removeAttribute('disabled');
        }
      }
    }

    if (drawerSubtotal) {
      drawerSubtotal.textContent = `€${subtotal.toFixed(2)}`;
    }
    if (drawerTotal) {
      drawerTotal.textContent = `€${subtotal.toFixed(2)}`;
    }
  }

  function openCartDrawer() {
    const drawer = document.getElementById('dm-cart-drawer');
    const backdrop = document.getElementById('dm-cart-backdrop');
    if (drawer) drawer.classList.add('active');
    if (backdrop) backdrop.classList.add('active');
    document.body.classList.add('cart-open');
  }

  function closeCartDrawer() {
    const drawer = document.getElementById('dm-cart-drawer');
    const backdrop = document.getElementById('dm-cart-backdrop');
    if (drawer) drawer.classList.remove('active');
    if (backdrop) backdrop.classList.remove('active');
    document.body.classList.remove('cart-open');
  }

  function showToast(message) {
    let toast = document.getElementById('dm-cart-toast');
    if (!toast) {
      toast = document.createElement('div');
      toast.id = 'dm-cart-toast';
      toast.className = 'dm-cart-toast';
      document.body.appendChild(toast);
    }
    toast.innerHTML = `<i class="fa-solid fa-circle-check"></i> <span>${message}</span>`;
    toast.classList.add('show');
    setTimeout(() => {
      toast.classList.remove('show');
    }, 2800);
  }

  // Initialize on DOMContentLoaded
  document.addEventListener('DOMContentLoaded', () => {
    updateUI();

    // Attach click listener to Add to Cart buttons
    document.addEventListener('click', e => {
      const btn = e.target.closest('.btn-add-to-cart');
      if (btn) {
        e.preventDefault();
        e.stopPropagation();

        const dishData = {
          id: btn.getAttribute('data-id') || Date.now(),
          name: btn.getAttribute('data-name') || 'Authentic Dish',
          price: parseFloat(btn.getAttribute('data-price') || 0),
          image: btn.getAttribute('data-img') || 'default_dish.png',
          category: btn.getAttribute('data-category') || 'Curries',
          quantity: 1
        };

        addItem(dishData);
      }
    });

    // Cart trigger buttons
    document.querySelectorAll('.trigger-cart-drawer').forEach(btn => {
      btn.addEventListener('click', e => {
        e.preventDefault();
        openCartDrawer();
      });
    });
  });

  return {
    getCart,
    addItem,
    removeItem,
    updateQuantity,
    clearCart,
    getSubtotal,
    getItemCount,
    openCartDrawer,
    closeCartDrawer,
    updateUI
  };
})();
