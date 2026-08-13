<?php
// components/reservation.php
?>
<section id="reservation" class="section reservation-section">
  <div class="container">
    
    <div style="text-align: center; margin-bottom: 3.5rem;">
      <span class="section-tag">ONLINE TABLE BOOKING</span>
      <h2 class="section-title kinetic-title">
        RESERVE YOUR <span class="highlight-terracotta">DINING EXPERIENCE</span>
      </h2>
      <p class="section-desc" style="margin-left: auto; margin-right: auto;">
        Join us for an unforgettable feast of authentic Indian flavors. Book your table online in seconds and receive instant confirmation.
      </p>
    </div>

    <!-- Reservation Card Wrapper -->
    <div class="reservation-card-wrapper">
      <form id="reservation-form">
        
        <input type="hidden" id="res-guests-hidden" name="guests" value="2">
        <input type="hidden" id="res-time-hidden" name="time" value="19:30">

        <div class="res-form-grid">
          
          <!-- Guest Counter Field -->
          <div>
            <label class="form-label"><i class="fa-solid fa-users"></i> Number of Guests</label>
            <div class="guest-selector-counter">
              <button type="button" class="guest-btn" id="guest-minus"><i class="fa-solid fa-minus"></i></button>
              <span class="guest-count-num" id="guest-count">2</span>
              <button type="button" class="guest-btn" id="guest-plus"><i class="fa-solid fa-plus"></i></button>
            </div>
          </div>

          <!-- Date Picker Field -->
          <div>
            <label class="form-label"><i class="fa-solid fa-calendar-days"></i> Select Date</label>
            <input type="date" id="res-date" class="form-control-input" required>
          </div>

          <!-- Time Chips Selector -->
          <div class="res-field-full">
            <label class="form-label"><i class="fa-solid fa-clock"></i> Select Dining Time Slot</label>
            <div class="time-chip-group">
              <span class="time-chip" data-time="12:30">12:30 PM (Lunch)</span>
              <span class="time-chip" data-time="13:30">1:30 PM (Lunch)</span>
              <span class="time-chip" data-time="18:30">6:30 PM (Dinner)</span>
              <span class="time-chip selected" data-time="19:30">7:30 PM (Dinner)</span>
              <span class="time-chip" data-time="20:30">8:30 PM (Dinner)</span>
              <span class="time-chip" data-time="21:15">9:15 PM (Late Dinner)</span>
            </div>
          </div>

          <!-- Name Input -->
          <div>
            <label class="form-label"><i class="fa-solid fa-user"></i> Full Name</label>
            <input type="text" id="res-name" class="form-control-input" placeholder="Enter your full name" required>
          </div>

          <!-- Phone Input -->
          <div>
            <label class="form-label"><i class="fa-solid fa-phone"></i> Phone Number</label>
            <input type="tel" id="res-phone" class="form-control-input" placeholder="+353 87 123 4567" required>
          </div>

          <!-- Email Input -->
          <div class="res-field-full">
            <label class="form-label"><i class="fa-solid fa-envelope"></i> Email Address</label>
            <input type="email" id="res-email" class="form-control-input" placeholder="name@example.com" required>
          </div>

          <!-- Special Requests -->
          <div class="res-field-full">
            <label class="form-label"><i class="fa-solid fa-comment-dots"></i> Special Requests / Dietary Needs (Optional)</label>
            <input type="text" id="res-notes" class="form-control-input" placeholder="e.g. Birthday celebration, High chair needed, Extra mild spice...">
          </div>

          <!-- Submit CTA -->
          <div class="res-field-full" style="text-align: center; margin-top: 1rem;">
            <button type="submit" class="btn btn-primary" style="width: 100%; max-width: 380px;">
              <span>Confirm Table Reservation</span>
              <i class="fa-solid fa-check"></i>
            </button>
          </div>

        </div>

      </form>
    </div>

  </div>
</section>

<!-- Confirmation Modal -->
<div class="modal-overlay" id="reservation-confirm-modal">
  <div class="modal-box">
    <button class="modal-close-btn" id="res-modal-close"><i class="fa-solid fa-xmark"></i></button>

    <div class="modal-icon-success">
      <i class="fa-solid fa-circle-check"></i>
    </div>

    <h3 style="font-family: var(--font-heading); font-size: 2rem; color: var(--clr-gold); margin-bottom: 0.5rem;">
      RESERVATION CONFIRMED!
    </h3>

    <p style="color: var(--text-secondary); font-size: 0.95rem; margin-bottom: 1.5rem;">
      Thank you, <strong id="confirm-name" style="color: var(--text-primary);">Guest</strong>! Your table reservation at Daba Magic has been successfully registered.
    </p>

    <div style="background: var(--bg-dark-surface); border: 1px dashed var(--glass-border-gold); padding: 1.25rem; border-radius: var(--radius-md); margin-bottom: 2rem; text-align: center;">
      <span style="font-family: var(--font-accent); font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase;">Booking Code:</span>
      <h4 id="confirm-code" style="font-family: var(--font-heading); font-size: 2.2rem; color: var(--clr-terracotta-bright); letter-spacing: 0.1em; margin: 0.3rem 0;">DM-849201</h4>
      <span id="confirm-details" style="font-size: 0.9rem; color: var(--clr-gold);">2 Guests • 2026-08-11 at 19:30</span>
    </div>

    <p style="font-size: 0.825rem; color: var(--text-muted); margin-bottom: 1.5rem;">
      A confirmation SMS and email summary has been sent to your contact details. We look forward to hosting you!
    </p>

    <button type="button" class="btn btn-primary" onclick="document.getElementById('reservation-confirm-modal').classList.remove('active');">
      <span>Done</span>
    </button>
  </div>
</div>
