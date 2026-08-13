<?php
/**
 * DABA MAGIC - Dedicated Menu & Online Ordering Page (menu.php)
 * Paragon-Inspired Awwwards Architecture
 */

include_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Dedicated Menu Inner Page Hero Banner -->
<section class="menu-hero-banner" style="position: relative; height: 50vh; min-height: 380px; display: flex; align-items: center; justify-content: center; background: url('assets/images/hero_biryani.png') center/cover no-repeat; overflow: hidden; margin-top: 100px;">
  <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(180deg, rgba(14, 11, 10, 0.75) 0%, rgba(14, 11, 10, 0.95) 100%); z-index: 1;"></div>
  
  <div class="container" style="position: relative; z-index: 2; text-align: center;">
    <span class="subheading-caps" style="color: var(--clr-gold); letter-spacing: 0.2em; font-size: 0.9rem;">ORDER ONLINE & DINE-IN</span>
    <h1 class="hollow-outline-text" style="font-size: clamp(3.2rem, 7.5vw, 6rem); -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.85); color: transparent; font-weight: 700; text-transform: uppercase; margin-top: 0.4rem; letter-spacing: 0.05em;">
      OUR GASTRONOMIC MENU
    </h1>
    <div style="width: 100px; height: 2px; background: var(--clr-terracotta); margin: 1.2rem auto;"></div>
    <p style="color: #DDDDDD; font-size: clamp(1rem, 1.8vw, 1.25rem); max-width: 680px; margin: 0 auto; font-family: var(--font-body); font-weight: 300;">
      Filter by Pure Veg 🟢 and Non-Veg 🔴 options, live search dishes, and order your favorites.
    </p>
  </div>
</section>

<!-- 2. Embedded Full Menu Component -->
<?php
include_once __DIR__ . '/components/menu.php';
?>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
