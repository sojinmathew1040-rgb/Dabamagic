<?php
/**
 * DABA MAGIC - About Us Dedicated Page (about.php)
 * Paragon-Inspired Awwwards Architecture
 */

include_once __DIR__ . '/includes/header.php';
?>

<!-- 1. Inner Page Hero Banner -->
<section class="about-hero-banner" style="position: relative; height: 60vh; min-height: 420px; display: flex; align-items: center; justify-content: center; background: url('assets/images/ambiance.png') center/cover no-repeat; overflow: hidden; margin-top: 100px;">
  <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(180deg, rgba(14, 11, 10, 0.75) 0%, rgba(14, 11, 10, 0.92) 100%); z-index: 1;"></div>
  
  <div class="container" style="position: relative; z-index: 2; text-align: center;">
    <span class="subheading-caps" style="color: var(--clr-gold); letter-spacing: 0.2em; font-size: 0.9rem;">HERITAGE & PASSION</span>
    <h1 class="hollow-outline-text" style="font-size: clamp(3.2rem, 7.5vw, 6rem); -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.85); color: transparent; font-weight: 700; text-transform: uppercase; margin-top: 0.4rem; letter-spacing: 0.05em;">
      ABOUT DABA MAGIC
    </h1>
    <div style="width: 100px; height: 2px; background: var(--clr-terracotta); margin: 1.2rem auto;"></div>
    <p style="color: #DDDDDD; font-size: clamp(1rem, 1.8vw, 1.25rem); max-width: 680px; margin: 0 auto; font-family: var(--font-body); font-weight: 300;">
      Decades of delivering delights, hand-ground spices, and unforgettable culinary craftsmanship.
    </p>
  </div>
</section>

<!-- 2. Kinetic Ticker Marquee Strip -->
<div style="background: var(--clr-terracotta); padding: 0.9rem 0; overflow: hidden; white-space: nowrap; font-family: var(--font-heading); color: #FFFFFF; font-weight: 700; letter-spacing: 0.15em; font-size: 0.95rem; text-transform: uppercase;">
  <div style="display: inline-block; animation: marqueeScroll 25s linear infinite;">
    <span>CHOSEN AMONG TOP LEGENDARY RESTAURANTS</span> &nbsp;•&nbsp;
    <span>80+ YEARS OF CULINARY EXCELLENCE</span> &nbsp;•&nbsp;
    <span>TRADITIONAL DUM METHOD</span> &nbsp;•&nbsp;
    <span>HOUSE-GROUND KERALA SPICES</span> &nbsp;•&nbsp;
    <span>ROYAL FINE DINING EXPERIENCE</span> &nbsp;•&nbsp;
  </div>
  <div style="display: inline-block; animation: marqueeScroll 25s linear infinite;">
    <span>CHOSEN AMONG TOP LEGENDARY RESTAURANTS</span> &nbsp;•&nbsp;
    <span>80+ YEARS OF CULINARY EXCELLENCE</span> &nbsp;•&nbsp;
    <span>TRADITIONAL DUM METHOD</span> &nbsp;•&nbsp;
    <span>HOUSE-GROUND KERALA SPICES</span> &nbsp;•&nbsp;
    <span>ROYAL FINE DINING EXPERIENCE</span> &nbsp;•&nbsp;
  </div>
</div>

<style>
@keyframes marqueeScroll {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
</style>

<!-- 3. Decades of Delivering Delights Section -->
<section class="section" style="background: var(--bg-dark-body); position: relative; padding: 6rem 0;">
  <div class="container">
    <div class="about-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 4rem; align-items: center;">
      
      <!-- Left Photo Composition -->
      <div class="about-image-wrapper reveal-on-scroll" style="position: relative;">
        <img src="assets/images/tandoori_kebab.png" alt="Culinary Craft" style="width: 100%; border-radius: 12px; transform: rotate(-3deg); box-shadow: -15px 20px 35px rgba(0,0,0,0.8); position: relative; z-index: 2;">
        <div style="position: absolute; top: -20px; left: -20px; width: 140px; height: 140px; border-radius: 50%; border: 3px solid rgba(200, 99, 56, 0.4); z-index: 1;"></div>
        <div style="position: absolute; bottom: -20px; right: -20px; width: 160px; height: 160px; border-radius: 50%; background: rgba(92, 148, 51, 0.15); z-index: 1;"></div>
      </div>

      <!-- Right Detailed Story Content -->
      <div class="about-content reveal-on-scroll">
        <span style="color: var(--clr-gold); font-family: var(--font-cursive); font-size: 2rem; display: block; margin-bottom: 0.3rem;">About Daba Magic Group</span>
        <h2 style="font-family: var(--font-heading); font-size: clamp(2.2rem, 4vw, 3.4rem); color: #FFFFFF; font-weight: 700; line-height: 1.2; margin-bottom: 1.2rem; text-transform: uppercase;">
          Decades of<br><span style="color: var(--clr-gold);">Delivering Delights</span>
        </h2>
        <div style="width: 100px; height: 2px; background: var(--clr-terracotta); margin-bottom: 2rem;"></div>

        <p style="color: #CCCCCC; font-size: 1.05rem; line-height: 1.8; margin-bottom: 1.2rem;">
          Established with a passion for authentic Indian gastronomy, Daba Magic boasts an unmatched heritage in culinary excellence. From classic delicacies of coastal India to the rich aromatic handi biryanis of royal court traditions, Daba Magic has been a delectable part of happy memories for food enthusiasts.
        </p>
        <p style="color: #CCCCCC; font-size: 1.05rem; line-height: 1.8; margin-bottom: 1.2rem;">
          Acclaimed for coastal and tandoori specialties, our master chefs stone-grind fresh spice pods daily, sourcing whole cardamoms, star anise, mace, and saffron directly from spice estates to preserve essential fragrant oils.
        </p>
        <p style="color: #CCCCCC; font-size: 1.05rem; line-height: 1.8;">
          We invite you to experience a culinary journey filled with unmatched taste, royal hospitality, and cherished memories. Bon Appétit!
        </p>
      </div>

    </div>
  </div>
</section>

<!-- 4. Interactive Statistics Counter Grid -->
<section style="background: var(--bg-dark-card); border-top: 1px solid var(--border-dark); border-bottom: 1px solid var(--border-dark); padding: 4rem 0;">
  <div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2.5rem; text-align: center;">
      
      <div class="reveal-on-scroll">
        <div style="font-family: var(--font-heading); font-size: 3.5rem; font-weight: 700; color: var(--clr-gold); line-height: 1;">80+</div>
        <div style="color: #AAAAAA; font-size: 0.95rem; margin-top: 0.5rem; letter-spacing: 0.08em; text-transform: uppercase;">Years of Heritage</div>
      </div>

      <div class="reveal-on-scroll">
        <div style="font-family: var(--font-heading); font-size: 3.5rem; font-weight: 700; color: var(--clr-terracotta); line-height: 1;">10M+</div>
        <div style="color: #AAAAAA; font-size: 0.95rem; margin-top: 0.5rem; letter-spacing: 0.08em; text-transform: uppercase;">Happy Guests Served</div>
      </div>

      <div class="reveal-on-scroll">
        <div style="font-family: var(--font-heading); font-size: 3.5rem; font-weight: 700; color: var(--clr-green); line-height: 1;">50+</div>
        <div style="color: #AAAAAA; font-size: 0.95rem; margin-top: 0.5rem; letter-spacing: 0.08em; text-transform: uppercase;">Signature Dishes</div>
      </div>

      <div class="reveal-on-scroll">
        <div style="font-family: var(--font-heading); font-size: 3.5rem; font-weight: 700; color: var(--clr-gold); line-height: 1;">4</div>
        <div style="color: #AAAAAA; font-size: 0.95rem; margin-top: 0.5rem; letter-spacing: 0.08em; text-transform: uppercase;">Grand Outlets</div>
      </div>

    </div>
  </div>
</section>

<!-- 5. Culinary Composer / Founder Legacy Section (Matching Paragon Reference) -->
<section class="section" style="background: var(--bg-dark-body); padding: 6rem 0;">
  <div class="container">
    
    <div style="text-align: center; margin-bottom: 4rem;">
      <span style="color: var(--clr-gold); font-family: var(--font-cursive); font-size: 2.2rem;">Our Culinary Composer</span>
      <h2 style="font-family: var(--font-heading); font-size: clamp(2.2rem, 4.5vw, 3.8rem); color: #FFFFFF; font-weight: 700; text-transform: uppercase; margin-top: 0.2rem;">
        PASSION IN EVERY RECIPE
      </h2>
      <div style="width: 120px; height: 2px; background: var(--clr-terracotta); margin: 1rem auto;"></div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 4rem; align-items: flex-start;">
      
      <!-- Founder Photo -->
      <div class="reveal-on-scroll" style="text-align: center;">
        <div style="position: relative; display: inline-block;">
          <img src="assets/images/butter_chicken.png" alt="Our Master Culinary Composer" style="width: 100%; max-width: 420px; border-radius: 12px; border: 2px solid var(--clr-gold); box-shadow: 0 20px 40px rgba(0,0,0,0.85);">
          <div style="position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); background: var(--clr-terracotta); color: #FFFFFF; padding: 0.6rem 2rem; border-radius: 20px; font-family: var(--font-heading); font-weight: 700; font-size: 0.95rem; letter-spacing: 0.1em; white-space: nowrap;">
            MASTER CHEF & FOUNDER
          </div>
        </div>
      </div>

      <!-- Founder Personal Letter -->
      <div class="reveal-on-scroll" style="background: var(--bg-dark-card); border: 1px solid var(--border-dark); border-radius: 12px; padding: 3rem 2.5rem;">
        <p style="color: #DDDDDD; font-size: 1.08rem; line-height: 1.85; margin-bottom: 1.2rem; font-style: italic;">
          "Hello there, let me start by saying how glad I am to see you here. I inherited the passion and legacy of my ancestors, deeply rooted in traditional hospitality and the abundant flavors of our spice region."
        </p>
        <p style="color: #CCCCCC; font-size: 1rem; line-height: 1.8; margin-bottom: 1.2rem;">
          For me, Daba Magic isn't just a restaurant; it's about honoring the traditions that have shaped us, embarking on a continuous journey to bring you the finest culinary creations. Every time we introduce a new dish, we refine it endlessly with our esteemed team of chefs.
        </p>
        <p style="color: #CCCCCC; font-size: 1rem; line-height: 1.8; margin-bottom: 1.5rem;">
          At the end of the day, what gives me assurance that our legacy lives on is the smile on our guests' faces. Each smile is a testament to the love, care, and tradition we pour into every plate.
        </p>

        <div style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
          <div>
            <div style="font-family: var(--font-heading); font-size: 1.2rem; color: #FFFFFF; font-weight: 700;">Executive Master Chef</div>
            <div style="color: var(--clr-gold); font-size: 0.88rem;">Daba Magic Culinary Group</div>
          </div>
          <div style="font-family: var(--font-cursive); font-size: 2.2rem; color: var(--clr-terracotta);">Daba Magic</div>
        </div>
      </div>

    </div>

  </div>
</section>

<!-- 6. Awards & Recognition Section -->
<section style="background: var(--bg-dark-card); border-top: 1px solid var(--border-dark); padding: 5rem 0;">
  <div class="container" style="text-align: center;">
    <span class="subheading-caps" style="color: var(--clr-gold); letter-spacing: 0.18em;">ACCOLADES & PRESS</span>
    <h2 style="font-family: var(--font-heading); font-size: clamp(2rem, 4vw, 3rem); color: #FFFFFF; margin-top: 0.3rem; margin-bottom: 3rem; text-transform: uppercase;">
      OUR AWARDS & RECOGNITIONS
    </h2>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; align-items: center;">
      <div class="glass-card" style="padding: 2rem 1.5rem; text-align: center;">
        <i class="fa-solid fa-trophy" style="font-size: 2.5rem; color: var(--clr-gold); margin-bottom: 1rem;"></i>
        <h4 style="color: #FFFFFF; font-size: 1.1rem; margin-bottom: 0.4rem;">Times Foodie Award</h4>
        <p style="color: #AAAAAA; font-size: 0.85rem;">Best Coastal & Tandoori Cuisine</p>
      </div>

      <div class="glass-card" style="padding: 2rem 1.5rem; text-align: center;">
        <i class="fa-solid fa-award" style="font-size: 2.5rem; color: var(--clr-terracotta); margin-bottom: 1rem;"></i>
        <h4 style="color: #FFFFFF; font-size: 1.1rem; margin-bottom: 0.4rem;">TimeOut Dining Award</h4>
        <p style="color: #AAAAAA; font-size: 0.85rem;">Consistently Top Ranked</p>
      </div>

      <div class="glass-card" style="padding: 2rem 1.5rem; text-align: center;">
        <i class="fa-solid fa-star" style="font-size: 2.5rem; color: var(--clr-gold); margin-bottom: 1rem;"></i>
        <h4 style="color: #FFFFFF; font-size: 1.1rem; margin-bottom: 0.4rem;">TripAdvisor Choice</h4>
        <p style="color: #AAAAAA; font-size: 0.85rem;">Travelers' Choice Winner</p>
      </div>

      <div class="glass-card" style="padding: 2rem 1.5rem; text-align: center;">
        <i class="fa-solid fa-crown" style="font-size: 2.5rem; color: var(--clr-green); margin-bottom: 1rem;"></i>
        <h4 style="color: #FFFFFF; font-size: 1.1rem; margin-bottom: 0.4rem;">TasteAtlas Award</h4>
        <p style="color: #AAAAAA; font-size: 0.85rem;">Top 100 Legendary Outlets</p>
      </div>
    </div>

  </div>
</section>

<?php
include_once __DIR__ . '/includes/footer.php';
?>
