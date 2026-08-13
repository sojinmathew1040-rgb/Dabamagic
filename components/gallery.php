<?php
// components/gallery.php
?>
<section id="gallery" class="section gallery-coverflow-section">
  <div class="container">
    
    <!-- Gallery Header matching exact reference -->
    <div class="gallery-header-block">
      <div class="gallery-subheading-line">
        <span class="line-left"></span>
        <span class="subheading-text">OUR GALLERY</span>
        <span class="line-right"></span>
      </div>
      <h2 class="gallery-main-title">
        WELCOME TO OUR RESTAURANT
      </h2>
      <p class="gallery-main-desc">
        Our menu features a wide range of dishes from different regions of authentic Indian cuisine.
      </p>
    </div>

    <!-- 3D Cover Flow Slider Container -->
    <div class="coverflow-carousel-wrapper" id="coverflow-carousel-wrapper">
      
      <button class="coverflow-nav-btn prev-btn" id="coverflow-prev" aria-label="Previous Slide">
        <i class="fa-solid fa-chevron-left"></i>
      </button>

      <div class="coverflow-track-container">
        <div class="coverflow-track" id="coverflow-track">
          
          <div class="coverflow-slide" data-index="0">
            <div class="coverflow-card gallery-item">
              <img src="assets/images/crisp_dosa.png" alt="Crispy Masala Dosa">
              <div class="coverflow-caption">Crispy Masala Dosa</div>
            </div>
          </div>

          <div class="coverflow-slide" data-index="1">
            <div class="coverflow-card gallery-item">
              <img src="assets/images/tandoori_kebab.png" alt="Garlic Butter Naan & Kebabs">
              <div class="coverflow-caption">Garlic Butter Naan & Kebabs</div>
            </div>
          </div>

          <div class="coverflow-slide active" data-index="2">
            <div class="coverflow-card gallery-item">
              <img src="assets/images/hero_biryani.png" alt="Royal Handi Dum Biryani">
              <div class="coverflow-caption">Royal Dum Biryani</div>
            </div>
          </div>

          <div class="coverflow-slide" data-index="3">
            <div class="coverflow-card gallery-item">
              <img src="assets/images/butter_chicken.png" alt="Velvet Butter Chicken">
              <div class="coverflow-caption">Velvet Butter Chicken</div>
            </div>
          </div>

          <div class="coverflow-slide" data-index="4">
            <div class="coverflow-card gallery-item">
              <img src="assets/images/ambiance.png" alt="Fine Dining Ambiance">
              <div class="coverflow-caption">Fine Dining Ambiance</div>
            </div>
          </div>

        </div>
      </div>

      <button class="coverflow-nav-btn next-btn" id="coverflow-next" aria-label="Next Slide">
        <i class="fa-solid fa-chevron-right"></i>
      </button>

    </div>

    <!-- Pagination Dots -->
    <div class="coverflow-pagination" id="coverflow-pagination">
      <span class="dot" data-index="0"></span>
      <span class="dot" data-index="1"></span>
      <span class="dot active" data-index="2"></span>
      <span class="dot" data-index="3"></span>
      <span class="dot" data-index="4"></span>
    </div>

  </div>
</section>
