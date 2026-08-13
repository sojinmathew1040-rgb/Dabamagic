/* ==========================================
   DABA MAGIC - LENIS SMOOTH SCROLL SETUP
   Smooth Inertial Scrolling & GSAP Sync
   ========================================== */

document.addEventListener('DOMContentLoaded', () => {
  // Initialize Lenis Smooth Scroll
  if (typeof Lenis !== 'undefined') {
    window.lenis = new Lenis({
      duration: 1.2,
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      direction: 'vertical',
      gestureDirection: 'vertical',
      smooth: true,
      mouseMultiplier: 1.0,
      smoothTouch: false,
      touchMultiplier: 2,
      infinite: false
    });

    // Synchronize Lenis with GSAP ScrollTrigger
    if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
      window.lenis.on('scroll', ScrollTrigger.update);

      gsap.ticker.add((time) => {
        window.lenis.raf(time * 1000);
      });

      gsap.ticker.lagSmoothing(0);
    } else {
      function raf(time) {
        window.lenis.raf(time);
        requestAnimationFrame(raf);
      }
      requestAnimationFrame(raf);
    }

    console.log('✨ Lenis smooth scrolling initialized successfully.');
  } else {
    console.warn('⚠️ Lenis library not loaded. Falling back to native scrolling.');
  }
});
