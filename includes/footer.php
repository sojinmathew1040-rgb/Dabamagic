<?php
// includes/footer.php
?>
  <!-- Footer Section -->
  <footer class="site-footer">
    <div class="container">
      
      <!-- 4-Column Locations & Phone Numbers Strip -->
      <div class="locations-grid">
        
        <div class="location-col">
          <div class="location-icon"><i class="fa-solid fa-location-dot"></i></div>
          <div class="location-name">Cork City Centre</div>
          <div class="location-phone">+353 21 427 9900</div>
        </div>

        <div class="location-col">
          <div class="location-icon"><i class="fa-solid fa-location-dot"></i></div>
          <div class="location-name">Ballincollig</div>
          <div class="location-phone">+353 21 427 9901</div>
        </div>

        <div class="location-col">
          <div class="location-icon"><i class="fa-solid fa-location-dot"></i></div>
          <div class="location-name">Dublin Grand</div>
          <div class="location-phone">+353 1 456 7890</div>
        </div>

        <div class="location-col">
          <div class="location-icon"><i class="fa-solid fa-location-dot"></i></div>
          <div class="location-name">Galway Harbour</div>
          <div class="location-phone">+353 91 567 8901</div>
        </div>

      </div>

      <!-- Footer Bottom Bar -->
      <div class="footer-bottom">
        <div>
          &copy; <?php echo date('Y'); ?> Daba Magic Group. All Rights Reserved.
        </div>
        <div style="display: flex; gap: 1.5rem;">
          <a href="#" style="color: var(--text-muted); text-decoration: none;">Privacy Policy</a>
          <a href="#" style="color: var(--text-muted); text-decoration: none;">Terms of Service</a>
          <a href="#" style="color: var(--text-muted); text-decoration: none;">Food Hygiene Standards</a>
        </div>
      </div>

    </div>
  </footer>

  <!-- Floating Quick Cart Button -->
  <button id="floating-cart-btn" class="floating-quick-cart trigger-cart-drawer" aria-label="Open Cart" title="View Order Cart">
    <i class="fa-solid fa-basket-shopping"></i>
    <span class="cart-count-badge floating-badge" id="floating-cart-badge">0</span>
  </button>

  <!-- Floating Back to Top Button -->
  <button id="back-to-top-btn" class="floating-back-to-top" aria-label="Back to Top">
    <i class="fa-solid fa-arrow-up"></i>
  </button>

  <!-- JavaScript Libraries & Modules -->
  <script src="assets/js/cart.js"></script>
  <script src="assets/js/menu.js"></script>
  <script src="assets/js/gsap-animations.js"></script>
  <script src="assets/js/main.js"></script>

</body>
</html>
