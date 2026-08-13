<?php
// components/menu.php
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
            <button class="cat-tab-btn" data-category="biryani">Biryani Specials</button>
            <button class="cat-tab-btn" data-category="tandoori">Tandoori & Kebabs</button>
            <button class="cat-tab-btn" data-category="curries">Royal Curries</button>
            <button class="cat-tab-btn" data-category="south">South Indian</button>
            <button class="cat-tab-btn" data-category="breads">Breads & Sides</button>
            <button class="cat-tab-btn" data-category="desserts">Desserts & Beverages</button>
          </div>
        </div>

        <button class="cat-tabs-arrow-btn cat-tabs-next-btn" id="cat-tabs-next-btn" aria-label="Scroll Categories Right">
          <i class="fa-solid fa-chevron-right"></i>
        </button>
      </div>

    </div>

    <!-- Menu Items Grid -->
    <div class="menu-list-grid" id="menu-items-container">
      
      <!-- Dish 1: Chicken Dum Biryani (Non-Veg) -->
      <div class="menu-item-row" data-category="biryani" data-diet="nonveg" data-name="Chicken Dum Biryani" data-price="€16.50" data-desc="Marinated chicken slow-cooked with basmati rice, saffron water, mint & fried onions in sealed clay pot." data-img="assets/images/hero_biryani.png" data-spice="2">
        <div class="menu-item-thumb">
          <img src="assets/images/hero_biryani.png" alt="Chicken Dum Biryani">
        </div>
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

      <!-- Dish 2: Hyderabadi Veg Dum Biryani (Veg) -->
      <div class="menu-item-row" data-category="biryani" data-diet="veg" data-name="Hyderabadi Veg Dum Biryani" data-price="€14.50" data-desc="Fragrant rice layered with fresh garden vegetables, cottage cheese, saffron & rose water." data-img="assets/images/hero_biryani.png" data-spice="1">
        <div class="menu-item-thumb">
          <img src="assets/images/hero_biryani.png" alt="Veg Biryani">
        </div>
        <div class="menu-item-details">
          <div class="menu-item-header">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
              <span class="diet-badge diet-veg" title="Pure Vegetarian"><span class="dot"></span></span>
              <h4 class="menu-item-name">Hyderabadi Veg Dum Biryani</h4>
            </div>
            <div class="menu-item-dots"></div>
            <span class="menu-item-price">€14.50</span>
          </div>
          <p class="menu-item-desc">Assorted fresh vegetables, paneer cubes, saffron basmati rice & fried onions.</p>
        </div>
      </div>

      <!-- Dish 3: Charcoal Chicken Tikka (Non-Veg) -->
      <div class="menu-item-row" data-category="tandoori" data-diet="nonveg" data-name="Charcoal Chicken Tikka" data-price="€15.00" data-desc="Boneless chicken chunks marinated in hung yogurt, red chili, kashmiri spices & roasted in tandoor." data-img="assets/images/tandoori_kebab.png" data-spice="2">
        <div class="menu-item-thumb">
          <img src="assets/images/tandoori_kebab.png" alt="Chicken Tikka">
        </div>
        <div class="menu-item-details">
          <div class="menu-item-header">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
              <span class="diet-badge diet-nonveg" title="Non-Vegetarian"><span class="dot"></span></span>
              <h4 class="menu-item-name">Charcoal Chicken Tikka</h4>
            </div>
            <div class="menu-item-dots"></div>
            <span class="menu-item-price">€15.00</span>
          </div>
          <p class="menu-item-desc">Yogurt & spice marinated chicken skewers roasted over hot charcoal coals.</p>
        </div>
      </div>

      <!-- Dish 4: Tandoori Paneer Tikka (Veg) -->
      <div class="menu-item-row" data-category="tandoori" data-diet="veg" data-name="Tandoori Paneer Tikka" data-price="€13.80" data-desc="Fresh cottage cheese, bell peppers & onions marinated in spiced mustard oil marinade." data-img="assets/images/tandoori_kebab.png" data-spice="1">
        <div class="menu-item-thumb">
          <img src="assets/images/tandoori_kebab.png" alt="Paneer Tikka">
        </div>
        <div class="menu-item-details">
          <div class="menu-item-header">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
              <span class="diet-badge diet-veg" title="Pure Vegetarian"><span class="dot"></span></span>
              <h4 class="menu-item-name">Tandoori Paneer Tikka</h4>
            </div>
            <div class="menu-item-dots"></div>
            <span class="menu-item-price">€13.80</span>
          </div>
          <p class="menu-item-desc">Grilled cottage cheese cubes, colorful peppers, lemon mint glaze & ajwain masala.</p>
        </div>
      </div>

      <!-- Dish 5: Classic Butter Chicken (Non-Veg) -->
      <div class="menu-item-row" data-category="curries" data-diet="nonveg" data-name="Classic Butter Chicken" data-price="€16.90" data-desc="Rich creamy tomato butter gravy with succulent roasted tandoori chicken pieces." data-img="assets/images/butter_chicken.png" data-spice="1">
        <div class="menu-item-thumb">
          <img src="assets/images/butter_chicken.png" alt="Butter Chicken">
        </div>
        <div class="menu-item-details">
          <div class="menu-item-header">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
              <span class="diet-badge diet-nonveg" title="Non-Vegetarian"><span class="dot"></span></span>
              <h4 class="menu-item-name">Classic Butter Chicken</h4>
            </div>
            <div class="menu-item-dots"></div>
            <span class="menu-item-price">€16.90</span>
          </div>
          <p class="menu-item-desc">Velvety tomato gravy, farm fresh butter, organic cream & roasted fenugreek leaves.</p>
        </div>
      </div>

      <!-- Dish 6: Palak Paneer Special (Veg) -->
      <div class="menu-item-row" data-category="curries" data-diet="veg" data-name="Palak Paneer Special" data-price="€14.20" data-desc="Fresh spinach puree tempered with garlic, cumin, and soft paneer cubes." data-img="assets/images/butter_chicken.png" data-spice="1">
        <div class="menu-item-thumb">
          <img src="assets/images/butter_chicken.png" alt="Palak Paneer">
        </div>
        <div class="menu-item-details">
          <div class="menu-item-header">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
              <span class="diet-badge diet-veg" title="Pure Vegetarian"><span class="dot"></span></span>
              <h4 class="menu-item-name">Palak Paneer Special</h4>
            </div>
            <div class="menu-item-dots"></div>
            <span class="menu-item-price">€14.20</span>
          </div>
          <p class="menu-item-desc">Organic spinach puree tempered with golden garlic, cumin, cream & paneer.</p>
        </div>
      </div>

      <!-- Dish 7: Royal Lamb Dum Biryani (Non-Veg) -->
      <div class="menu-item-row" data-category="biryani" data-diet="nonveg" data-name="Royal Lamb Dum Biryani" data-price="€18.50" data-desc="Tender lamb shank cooked with aromatic spices and saffron basmati rice." data-img="assets/images/hero_biryani.png" data-spice="3">
        <div class="menu-item-thumb">
          <img src="assets/images/hero_biryani.png" alt="Lamb Biryani">
        </div>
        <div class="menu-item-details">
          <div class="menu-item-header">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
              <span class="diet-badge diet-nonveg" title="Non-Vegetarian"><span class="dot"></span></span>
              <h4 class="menu-item-name">Royal Lamb Dum Biryani</h4>
            </div>
            <div class="menu-item-dots"></div>
            <span class="menu-item-price">€18.50</span>
          </div>
          <p class="menu-item-desc">Slow-cooked succulent lamb, saffron rice, caramelized onions & cardamom aroma.</p>
        </div>
      </div>

      <!-- Dish 8: Crispy Masala Dosa (Veg) -->
      <div class="menu-item-row" data-category="south" data-diet="veg" data-name="Crispy Masala Dosa" data-price="€13.50" data-desc="Golden crisp crepe with potato masala stuffing, served with coconut chutney & sambar." data-img="assets/images/crisp_dosa.png" data-spice="1">
        <div class="menu-item-thumb">
          <img src="assets/images/crisp_dosa.png" alt="Masala Dosa">
        </div>
        <div class="menu-item-details">
          <div class="menu-item-header">
            <div style="display: flex; align-items: center; gap: 0.6rem;">
              <span class="diet-badge diet-veg" title="Pure Vegetarian"><span class="dot"></span></span>
              <h4 class="menu-item-name">Crispy Masala Dosa</h4>
            </div>
            <div class="menu-item-dots"></div>
            <span class="menu-item-price">€13.50</span>
          </div>
          <p class="menu-item-desc">Fermented rice & lentil crepe filled with mustard potato masala & chutneys.</p>
        </div>
      </div>

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
