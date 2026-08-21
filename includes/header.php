<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Daba Magic | Authentic Indian Cuisine & Fine Dining</title>
  <meta name="description" content="Welcome to Daba Magic. Experience traditional handi dum biryani, sizzling tandoori, and royal curries.">

  <!-- Favicon -->
  <link rel="icon" type="image/png" href="assets/images/logo.png">

  <!-- Google Fonts: Poppins, Great Vibes, Cormorant Garamond -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400;1,600&family=Great+Vibes&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- FontAwesome 6 Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!-- CSS Stylesheets -->
  <link rel="stylesheet" href="assets/css/variables.css">
  <link rel="stylesheet" href="assets/css/main.css">
  <link rel="stylesheet" href="assets/css/loader.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/responsive.css">

  <!-- External Animation Libraries (CDN) -->
  <!-- GSAP & ScrollTrigger -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

  <!-- Lenis Smooth Scroll -->
  <script src="https://cdn.jsdelivr.net/npm/@studio-freight/lenis@1.0.42/dist/lenis.min.js"></script>

  <!-- SplitType (Kinetic Typography) -->
  <script src="https://unpkg.com/split-type@0.3.4/dist/index.js"></script>
</head>
<body>

  <!-- Top Header Navigation -->
  <header class="site-header">
    <div class="container nav-wrapper">
      
      <!-- Big Logo in Top Left Corner -->
      <a href="index.php" class="brand-logo-left">
        <img src="assets/images/logo.png" alt="Daba Magic Logo" class="main-header-logo">
      </a>

      <!-- Right Header Controls & Navigation Toggle -->
      <div class="header-right-controls">
        <div class="dm-top-links-list">
          <a href="menu.php">ORDER ONLINE</a>
          <a href="track_order.php">TRACK ORDER</a>
          <a href="index.php#reservation">FEEDBACK</a>
          <a href="index.php#contact">CONTACT US</a>
        </div>

        <!-- Shopping Cart Trigger Button -->
        <button type="button" class="dm-header-cart-btn trigger-cart-drawer" aria-label="View Shopping Cart" title="View Cart">
          <i class="fa-solid fa-basket-shopping"></i>
          <span class="cart-count-badge" id="header-cart-badge">0</span>
        </button>
        
        <button class="dm-menu-circle-btn mobile-menu-toggle" id="dm-menu-toggle-btn" aria-label="Toggle Menu">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>

    </div>
  </header>

  <!-- Fullscreen Navigation Overlay Stack -->
  <div class="dm-nav-overlay" id="dm-nav-overlay">
    <div class="dm-nav-card">
      
      <!-- Top Bar inside Overlay: Logo & Round Close Button -->
      <div class="dm-nav-card-header">
        <a href="index.php" class="dm-nav-logo">
          <img src="assets/images/logo.png" alt="Daba Magic Logo" style="height: 70px;">
        </a>
        <button class="dm-nav-close-btn" id="dm-nav-close-btn" aria-label="Close Navigation">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <!-- 3 Column Content Layout inside Navigation Overlay Stack -->
      <div class="dm-nav-card-body">
        
        <!-- Column 1: Contact Info & Address -->
        <div class="dm-nav-col">
          <h4 class="dm-nav-col-title">Contact</h4>
          <div class="dm-nav-address-block">
            <strong>Daba Magic Restaurant & Catering</strong>
            <p>Grand Parade & St. Patrick Street,<br>Cork City Centre, T12 X482</p>
            <div class="dm-nav-phone-links">
              <a href="tel:+353214279900"><i class="fa-solid fa-phone"></i> P: +353 21 427 9900</a>
              <a href="tel:+353871234567"><i class="fa-solid fa-mobile-screen"></i> M: +353 87 123 4567</a>
            </div>
          </div>
        </div>

        <!-- Column 2: Quick Links -->
        <div class="dm-nav-col">
          <h4 class="dm-nav-col-title">Quick Links</h4>
          <ul class="dm-nav-link-stack">
            <li><a href="index.php" class="overlay-nav-link">Home</a></li>
            <li><a href="about.php" class="overlay-nav-link">About Us</a></li>
            <li><a href="menu.php" class="overlay-nav-link">Explore Full Menu</a></li>
            <li><a href="track_order.php" class="overlay-nav-link" style="color: var(--clr-gold-bright);">Track Order Live</a></li>
            <li><a href="index.php#specials" class="overlay-nav-link">Daba Magic Specials</a></li>
            <li><a href="index.php#experience" class="overlay-nav-link">Culinary Craft</a></li>
            <li><a href="index.php#gallery" class="overlay-nav-link">Visual Gallery</a></li>
            <li><a href="index.php#contact" class="overlay-nav-link">Contact & Booking</a></li>
          </ul>
        </div>

        <!-- Column 3: Discover Our Menu (Split Sub-Columns) -->
        <div class="dm-nav-col">
          <h4 class="dm-nav-col-title">Discover Our Menu</h4>
          <div class="dm-menu-subcols">
            <ul class="dm-nav-link-stack">
              <li><a href="menu.php" class="overlay-nav-link">Hot Starters</a></li>
              <li><a href="menu.php" class="overlay-nav-link">Soups & Salads</a></li>
              <li><a href="menu.php" class="overlay-nav-link">Royal Dum Biryani</a></li>
              <li><a href="menu.php" class="overlay-nav-link">Tandoori & Kebabs</a></li>
              <li><a href="menu.php" class="overlay-nav-link">Accompaniments</a></li>
            </ul>
            <ul class="dm-nav-link-stack">
              <li><a href="menu.php" class="overlay-nav-link">Velvet Butter Chicken</a></li>
              <li><a href="menu.php" class="overlay-nav-link">Royal Curries</a></li>
              <li><a href="menu.php" class="overlay-nav-link">Crispy Masala Dosa</a></li>
              <li><a href="menu.php" class="overlay-nav-link">Desserts & Sweets</a></li>
              <li><a href="menu.php" class="overlay-nav-link">Royal Beverages</a></li>
            </ul>
          </div>
        </div>

      </div>

      <!-- Bottom Bar: Social Icons & Copyright -->
      <div class="dm-nav-card-footer">
        <div class="dm-nav-socials">
          <a href="#"><i class="fa-brands fa-instagram"></i></a>
          <a href="#"><i class="fa-brands fa-facebook"></i></a>
          <a href="#"><i class="fa-brands fa-tripadvisor"></i></a>
        </div>
        <div style="font-size: 0.8rem; color: var(--text-muted);">
          &copy; <?php echo date('Y'); ?> Daba Magic Culinary Group. All Rights Reserved.
        </div>
      </div>

    </div>
  </div>

  <!-- Cart Drawer Backdrop Overlay -->
  <div class="dm-cart-backdrop" id="dm-cart-backdrop" onclick="DMCart.closeCartDrawer()"></div>

  <!-- Slide-In Cart Drawer -->
  <aside class="dm-cart-drawer" id="dm-cart-drawer" aria-label="Shopping Cart Drawer">
    <div class="cart-drawer-header">
      <h3 class="cart-drawer-title">
        <i class="fa-solid fa-basket-shopping"></i>
        <span>Your Order Cart</span>
      </h3>
      <button type="button" class="cart-drawer-close-btn" onclick="DMCart.closeCartDrawer()" aria-label="Close Cart">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>

    <div class="cart-drawer-body" id="dm-cart-items-list">
      <!-- Populated dynamically by DMCart.updateUI() -->
    </div>

    <div class="cart-drawer-footer">
      <div class="cart-summary-row">
        <span>Subtotal</span>
        <strong id="dm-cart-subtotal">€0.00</strong>
      </div>
      <div class="cart-summary-row">
        <span>Estimated Tax & Delivery</span>
        <small style="color: var(--clr-gold-bright);">Calculated at Checkout</small>
      </div>
      <div class="cart-summary-row total-row">
        <span>Total</span>
        <strong id="dm-cart-total" style="color: var(--clr-gold-bright);">€0.00</strong>
      </div>

      <a href="checkout.php" class="cart-checkout-btn disabled" id="dm-cart-checkout-btn">
        <span>Proceed to Checkout</span>
        <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
  </aside>
