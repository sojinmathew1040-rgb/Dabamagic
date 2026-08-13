<?php
// components/specials.php
?>
<!-- "Discover Our Menu" Section (Matching Reference Screenshot 100%) -->
<section id="specials" class="section" style="background-color: #0E0E0E; position: relative;">
  <div class="container">
    
    <!-- Section Title Header (Matching Reference Screenshot 100%) -->
    <div style="text-align: center; margin-bottom: 4.2rem; position: relative;">
      <div style="font-family: var(--font-cursive); font-size: clamp(3.2rem, 6.5vw, 5rem); color: var(--clr-terracotta-bright); line-height: 1; margin-bottom: -1.3rem; position: relative; z-index: 2; font-style: italic;">
        Discover
      </div>
      <h2 class="hollow-outline-text" style="font-size: clamp(3.5rem, 8.5vw, 7rem); font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; line-height: 1; -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.85); color: transparent;">
        OUR MENU
      </h2>
    </div>

    <!-- Horizontal Menu Cards Carousel Track Container -->
    <div class="menu-carousel-wrapper" id="menu-carousel-wrapper">
      <div class="menu-cards-carousel-track" id="menu-cards-carousel-track">
        
        <!-- Card 1: Poultry & Meat -->
        <div class="dm-menu-card">
          <div class="dm-card-photo-wrap">
            <img src="assets/images/butter_chicken.png" alt="Poultry & Meat">
          </div>
          <div class="dm-card-info">
            <h3>Poultry & Meat</h3>
            <ul>
              <li>Aleppey Chicken Curry</li>
              <li>Chicken Malabari</li>
              <li>Daba Magic Chilli Chicken</li>
              <li>Kanthari Chicken Curry</li>
            </ul>
            <a href="menu.php" class="circle-arrow-btn"><i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <!-- Card 2: Meals & Biriyani -->
        <div class="dm-menu-card">
          <div class="dm-card-photo-wrap">
            <img src="assets/images/hero_biryani.png" alt="Meals & Biriyani">
          </div>
          <div class="dm-card-info">
            <h3>Meals & Biriyani</h3>
            <ul>
              <li>Daba Magic Special Meals</li>
              <li>Chicken Biriyani</li>
              <li>Hammour Biriyani</li>
              <li>Mutton Biriyani</li>
            </ul>
            <a href="menu.php" class="circle-arrow-btn"><i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <!-- Card 3: Breakfast & Breads -->
        <div class="dm-menu-card">
          <div class="dm-card-photo-wrap">
            <img src="assets/images/crisp_dosa.png" alt="Breakfast Menu">
          </div>
          <div class="dm-card-info">
            <h3>Breakfast Menu</h3>
            <ul>
              <li>Chapati</li>
              <li>Wheat Paratha</li>
              <li>Puttu</li>
              <li>Idili (3 pcs)</li>
            </ul>
            <a href="menu.php" class="circle-arrow-btn"><i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <!-- Card 4: Tandoori & Kebabs -->
        <div class="dm-menu-card">
          <div class="dm-card-photo-wrap">
            <img src="assets/images/tandoori_kebab.png" alt="Tandoori & Kebabs">
          </div>
          <div class="dm-card-info">
            <h3>Tandoori & Kebabs</h3>
            <ul>
              <li>Charcoal Chicken Tikka</li>
              <li>Lamb Seekh Kebab</li>
              <li>Paneer Tikka</li>
              <li>Garlic Butter Naan</li>
            </ul>
            <a href="menu.php" class="circle-arrow-btn"><i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <!-- Card 5: South Indian Delights -->
        <div class="dm-menu-card">
          <div class="dm-card-photo-wrap">
            <img src="assets/images/crisp_dosa.png" alt="South Indian Delights">
          </div>
          <div class="dm-card-info">
            <h3>South Indian</h3>
            <ul>
              <li>Crispy Masala Dosa</li>
              <li>Ghee Roast Dosa</li>
              <li>Medu Vada Platter</li>
              <li>Coconut Chutney</li>
            </ul>
            <a href="menu.php" class="circle-arrow-btn"><i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <!-- Card 6: Desserts & Drinks -->
        <div class="dm-menu-card">
          <div class="dm-card-photo-wrap">
            <img src="assets/images/ambiance.png" alt="Desserts & Drinks">
          </div>
          <div class="dm-card-info">
            <h3>Desserts & Drinks</h3>
            <ul>
              <li>Gulab Jamun with Ice Cream</li>
              <li>Artisan Mango Kulfi</li>
              <li>Royal Cardamom Rasmalai</li>
              <li>Masala Chai & Lassi</li>
            </ul>
            <a href="menu.php" class="circle-arrow-btn"><i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

      </div>
    </div>

    <!-- Bottom Center Left & Right Arrow Controls (Matching Reference Screenshot 100%) -->
    <div class="specials-bottom-nav-controls" style="display: flex; gap: 3.5rem; justify-content: center; align-items: center; margin-top: 3.2rem;">
      <button class="specials-bottom-arrow-btn" id="specials-prev-btn" aria-label="Explore Left">
        <i class="fa-solid fa-arrow-left-long"></i>
      </button>
      <button class="specials-bottom-arrow-btn" id="specials-next-btn" aria-label="Explore Right">
        <i class="fa-solid fa-arrow-right-long"></i>
      </button>
    </div>

  </div>
</section>

<!-- Full Viewport Height (100vh) Specials Dynamic Full-Screen Column Switcher (Exact Paragon Design 100%) -->
<section class="specials-banner-section" id="specials-hover-banner">
  
  <!-- Background Layers for Smooth Image Crossfade -->
  <div class="specials-bg-layer specials-bg-layer-active" id="specials-bg-active" style="background-image: url('assets/images/tandoori_kebab.png');"></div>
  <div class="specials-bg-layer" id="specials-bg-next" style="opacity: 0;"></div>

  <!-- Dark Backdrop Overlay -->
  <div class="specials-banner-overlay"></div>
  
  <!-- Center Giant Hollow Outline Title -->
  <div class="specials-title-center-wrap">
    <h2 class="hollow-outline-text" style="font-size: clamp(3.2rem, 8vw, 7.5rem); -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.7); color: transparent; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; line-height: 1;">
      DABA MAGIC SPECIALS
    </h2>
  </div>

  <!-- 4 Full-Height Vertical Columns Grid (Taking up 100% Height of Section) -->
  <div class="specials-vertical-columns-grid">
    <div class="special-vert-col active" data-bg="assets/images/tandoori_kebab.png">
      <h4 class="special-col-title">Charcoal Kozhi Porichath</h4>
    </div>
    <div class="special-vert-col" data-bg="assets/images/hero_biryani.png">
      <h4 class="special-col-title">Royal Lamb Dum Biryani</h4>
    </div>
    <div class="special-vert-col" data-bg="assets/images/butter_chicken.png">
      <h4 class="special-col-title">Velvet Butter Chicken</h4>
    </div>
    <div class="special-vert-col" data-bg="assets/images/crisp_dosa.png">
      <h4 class="special-col-title">Crispy Masala Dosa</h4>
    </div>
  </div>
</section>
