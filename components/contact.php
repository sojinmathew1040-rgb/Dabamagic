<?php
// components/contact.php
?>
<section id="contact" class="section">
  <div class="container">
    
    <div style="text-align: center; margin-bottom: 3.5rem;">
      <span class="section-tag">FIND & CONNECT</span>
      <h2 class="section-title kinetic-title">
        VISIT <span class="highlight-gold">DABA MAGIC</span>
      </h2>
    </div>

    <!-- Contact Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 3rem;">
      
      <!-- Info Cards Column -->
      <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <div class="glass-card" style="padding: 1.8rem; display: flex; align-items: flex-start; gap: 1.2rem;">
          <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(211, 97, 53, 0.2); color: var(--clr-terracotta-bright); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
            <i class="fa-solid fa-location-dot"></i>
          </div>
          <div>
            <h4 style="font-family: var(--font-heading); font-size: 1.3rem; color: var(--clr-gold); margin-bottom: 0.3rem;">Restaurant Address</h4>
            <p style="color: var(--text-secondary); font-size: 0.92rem; line-height: 1.5;">
              38 Grand Parade & St. Patrick Street,<br>
              Cork City Centre, T12 X482, Ireland
            </p>
          </div>
        </div>

        <div class="glass-card" style="padding: 1.8rem; display: flex; align-items: flex-start; gap: 1.2rem;">
          <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(229, 178, 37, 0.2); color: var(--clr-gold-bright); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
            <i class="fa-solid fa-phone"></i>
          </div>
          <div>
            <h4 style="font-family: var(--font-heading); font-size: 1.3rem; color: var(--clr-gold); margin-bottom: 0.3rem;">Phone & Table Inquiry</h4>
            <p style="color: var(--text-secondary); font-size: 0.92rem; line-height: 1.5;">
              Reservations: +353 (0)21 427 9900<br>
              Takeaway & Delivery: +353 (0)21 427 9901
            </p>
          </div>
        </div>

        <div class="glass-card" style="padding: 1.8rem; display: flex; align-items: flex-start; gap: 1.2rem;">
          <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(110, 147, 52, 0.2); color: var(--clr-olive-bright); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
            <i class="fa-solid fa-envelope"></i>
          </div>
          <div>
            <h4 style="font-family: var(--font-heading); font-size: 1.3rem; color: var(--clr-gold); margin-bottom: 0.3rem;">Email & Events</h4>
            <p style="color: var(--text-secondary); font-size: 0.92rem; line-height: 1.5;">
              Inquiries: info@dabamagic.ie<br>
              Private Catering: events@dabamagic.ie
            </p>
          </div>
        </div>

      </div>

      <!-- Map & Message Form Column -->
      <div class="glass-card" style="padding: 2.5rem;">
        <h3 style="font-family: var(--font-heading); font-size: 1.8rem; color: var(--clr-gold); margin-bottom: 1.5rem;">Send Us a Message</h3>
        
        <form onsubmit="event.preventDefault(); alert('Thank you for contacting Daba Magic! We will respond shortly.'); this.reset();" style="display: flex; flex-direction: column; gap: 1.2rem;">
          <div>
            <label class="form-label">Your Name</label>
            <input type="text" class="form-control-input" placeholder="Full Name" required>
          </div>

          <div>
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control-input" placeholder="name@example.com" required>
          </div>

          <div>
            <label class="form-label">Message</label>
            <textarea class="form-control-input" rows="4" placeholder="How can we assist you?" required style="resize: vertical;"></textarea>
          </div>

          <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
            <span>Send Message</span>
            <i class="fa-solid fa-paper-plane"></i>
          </button>
        </form>
      </div>

    </div>

  </div>
</section>
