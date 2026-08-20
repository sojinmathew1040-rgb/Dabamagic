<?php
// components/menu.php
if (!isset($con) || !$con || $con->connect_error) {
    @include_once __DIR__ . '/../includes/db_connection.php';
}

// Fetch active categories dynamically
$frontend_categories = [];
$cat_slug_map = [];
if (isset($con) && !$con->connect_error) {
    $fc_query = $con->query("SELECT * FROM tbl_categories WHERE status = 'Active' ORDER BY display_order ASC, name ASC");
    if ($fc_query && $fc_query->num_rows > 0) {
        while ($crow = $fc_query->fetch_assoc()) {
            $frontend_categories[] = $crow;
            $cat_slug_map[$crow['name']] = $crow['slug'];
        }
    }
}

// Fetch active menu items dynamically
$db_menu_items = [];
if (isset($con) && !$con->connect_error) {
    $mi_query = $con->query("SELECT * FROM tbl_menu_items WHERE status = 'Active' ORDER BY id DESC");
    if ($mi_query && $mi_query->num_rows > 0) {
        while ($mirow = $mi_query->fetch_assoc()) {
            $db_menu_items[] = $mirow;
        }
    }
}
?>
<section id="menu" class="section section-bg-surface">
  <div class="container">
    
    <div style="text-align: center; margin-bottom: 3rem; display: flex; flex-direction: column; align-items: center; gap: 0.8rem;">
      <span class="subheading-caps" style="color: var(--clr-gold); letter-spacing: 0.15em;">OUR FULL GASTRONOMIC MENU</span>
      <h2 class="section-title" style="margin: 0.3rem 0 0.8rem 0; line-height: 1.2;">
        EXPLORE THE <span style="color: var(--clr-gold);">DABA</span> <span style="color: var(--clr-green);">MAGIC</span> MENU
      </h2>
      <p class="section-desc" style="margin-left: auto; margin-right: auto; max-width: 720px; line-height: 1.7;">
        Every single dish is prepared fresh to order using handpicked ingredients, traditional handi methods, and authentic Indian spices.
      </p>
    </div>

    <!-- Search & Dietary Options Filter Bar -->
    <div class="menu-filter-bar-container" style="max-width: 950px; margin: 0 auto 2.5rem auto; display: flex; flex-direction: column; gap: 1.2rem; align-items: center;">
      
      <!-- Search Input -->
      <div class="menu-search-wrapper" style="width: 100%; max-width: 480px; position: relative;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
        <input type="text" id="menu-search-input" class="form-control-input" placeholder="Search dishes (e.g., Biryani, Butter Chicken, Dosa)..." style="padding-left: 2.8rem; border-radius: var(--radius-full); background: #18100E; border: 1px solid rgba(200, 99, 56, 0.3);">
      </div>

      <!-- Veg / Non-Veg Dietary Selection Chips -->
      <div class="dietary-filter-chips" style="display: flex; gap: 0.8rem; flex-wrap: wrap; justify-content: center;">
        <button class="diet-chip active" data-diet="all">
          <i class="fa-solid fa-utensils"></i> All Options
        </button>
        <button class="diet-chip diet-chip-veg" data-diet="veg">
          <span class="diet-badge diet-veg"><span class="dot"></span></span> Pure Veg
        </button>
        <button class="diet-chip diet-chip-nonveg" data-diet="nonveg">
          <span class="diet-badge diet-nonveg"><span class="dot"></span></span> Non-Veg Specials
        </button>
      </div>

      <!-- Category Filter Tabs Slider with Left & Right Arrow Navigation Buttons -->
      <div class="cat-tabs-slider-container">
        <button class="cat-tabs-arrow-btn cat-tabs-prev-btn" id="cat-tabs-prev-btn" aria-label="Scroll Categories Left">
          <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="category-tabs-scroll-track" id="category-tabs-scroll-track">
          <div class="category-tabs" style="margin-bottom: 0; display: flex; gap: 0.75rem; width: max-content;">
            <button class="cat-tab-btn active" data-category="all">All Categories</button>
            <?php if (!empty($frontend_categories)): ?>
              <?php foreach ($frontend_categories as $fcat): ?>
                <button class="cat-tab-btn" data-category="<?php echo htmlspecialchars($fcat['slug']); ?>">
                  <?php echo htmlspecialchars($fcat['name']); ?>
                </button>
              <?php endforeach; ?>
            <?php else: ?>
              <button class="cat-tab-btn" data-category="biryani">Biryani Specials</button>
              <button class="cat-tab-btn" data-category="tandoori">Tandoori & Kebabs</button>
              <button class="cat-tab-btn" data-category="curries">Royal Curries</button>
              <button class="cat-tab-btn" data-category="south">South Indian</button>
              <button class="cat-tab-btn" data-category="breads">Breads & Sides</button>
              <button class="cat-tab-btn" data-category="desserts">Desserts & Beverages</button>
            <?php endif; ?>
          </div>
        </div>

        <button class="cat-tabs-arrow-btn cat-tabs-next-btn" id="cat-tabs-next-btn" aria-label="Scroll Categories Right">
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>

    </div>

    <!-- Menu Items Grid -->
    <div class="menu-list-grid" id="menu-items-container">
      
      <?php if (!empty($db_menu_items)): ?>
        <?php foreach ($db_menu_items as $item): ?>
          <?php
            $cat_name = $item['category'];
            $cat_slug = isset($cat_slug_map[$cat_name]) 
              ? $cat_slug_map[$cat_name] 
              : strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $cat_name), '-'));

            // Basic diet detection helper
            $desc_lower = strtolower($item['name'] . ' ' . $item['description']);
            $is_veg = (strpos($desc_lower, 'paneer') !== false || strpos($desc_lower, 'veg') !== false || strpos($desc_lower, 'dosa') !== false || strpos($desc_lower, 'palak') !== false || strpos($desc_lower, 'dal') !== false || strpos($desc_lower, 'lassi') !== false || strpos($desc_lower, 'jamun') !== false);
            $diet_type = $is_veg ? 'veg' : 'nonveg';
            $diet_title = $is_veg ? 'Pure Vegetarian' : 'Non-Vegetarian';
            $diet_class = $is_veg ? 'diet-veg' : 'diet-nonveg';
            $image_path = "assets/images/" . (!empty($item['image']) ? htmlspecialchars($item['image']) : "default_dish.png");
          ?>
          <div class="menu-item-row trigger-dish-modal" 
               data-category="<?php echo htmlspecialchars($cat_slug); ?>" 
               data-diet="<?php echo $diet_type; ?>" 
               data-name="<?php echo htmlspecialchars($item['name']); ?>" 
               data-price="€<?php echo number_format($item['price'], 2); ?>" 
               data-desc="<?php echo htmlspecialchars($item['description']); ?>" 
               data-img="<?php echo $image_path; ?>" 
               data-spice="2" 
               style="cursor: pointer;">
            <div class="menu-item-thumb">
              <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'80\' viewBox=\'0 0 80 80\'%3E%3Crect width=\'80\' height=\'80\' fill=\'%23221613\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' fill=\'%23D4A017\' font-size=\'32\' dominant-baseline=\'middle\' text-anchor=\'middle\'%3E🍲%3C/text%3E%3C/svg%3E';">
            </div>
            <div class="menu-item-details">
              <div class="menu-item-header">
                <div style="display: flex; align-items: center; gap: 0.6rem;">
                  <span class="diet-badge <?php echo $diet_class; ?>" title="<?php echo $diet_title; ?>"><span class="dot"></span></span>
                  <h4 class="menu-item-name"><?php echo htmlspecialchars($item['name']); ?></h4>
                </div>
                <div class="menu-item-dots"></div>
                <span class="menu-item-price">€<?php echo number_format($item['price'], 2); ?></span>
              </div>
              <p class="menu-item-desc"><?php echo htmlspecialchars($item['description']); ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <!-- Static Fallback Items if DB is disconnected -->
        <div class="menu-item-row trigger-dish-modal" data-category="biryani" data-diet="nonveg" data-name="Chicken Dum Biryani" data-price="€16.50" data-desc="Marinated chicken slow-cooked with basmati rice, saffron water, mint & fried onions in sealed clay pot." data-img="assets/images/hero_biryani.png" data-spice="2">
          <div class="menu-item-thumb"><img src="assets/images/hero_biryani.png" alt="Chicken Dum Biryani"></div>
          <div class="menu-item-details">
            <div class="menu-item-header">
              <div style="display: flex; align-items: center; gap: 0.6rem;">
                <span class="diet-badge diet-nonveg" title="Non-Vegetarian"><span class="dot"></span></span>
                <h4 class="menu-item-name">Chicken Dum Biryani</h4>
              </div>
              <div class="menu-item-dots"></div>
              <span class="menu-item-price">€16.50</span>
            </div>
            <p class="menu-item-desc">Slow-cooked tender chicken, basmati rice, fresh mint & house biryani masala.</p>
          </div>
        </div>
      <?php endif; ?>

    </div>

  </div>
</section>

<!-- Dish Details Modal -->
<div class="modal-overlay" id="dish-detail-modal">
  <div class="modal-box">
    <button class="modal-close-btn modal-close-trigger"><i class="fa-solid fa-xmark"></i></button>

    <img src="" id="modal-dish-img" style="width: 100%; height: 220px; object-fit: cover; border-radius: var(--radius-md); margin-bottom: 1.25rem;">
    
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.8rem;">
      <h3 id="modal-dish-title" style="font-size: 1.8rem; font-family: var(--font-heading); color: var(--clr-gold);">Dish Title</h3>
      <span id="modal-dish-price" style="font-size: 1.5rem; font-family: var(--font-heading); color: var(--clr-terracotta); font-weight: 600;">€0.00</span>
    </div>

    <div id="modal-dish-spice" style="margin-bottom: 1rem; color: var(--clr-terracotta); font-size: 0.9rem;"></div>

    <p id="modal-dish-desc" style="color: var(--text-grey); font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;"></p>

    <div style="display: flex; gap: 1rem; justify-content: center;">
      <a href="#reservation" class="btn btn-primary modal-close-trigger">
        <span>Order / Book Table</span>
        <i class="fa-solid fa-utensils"></i>
      </a>
    </div>
  </div>
</div>
