/* ==========================================
   DABA MAGIC - GSAP ANIMATIONS & SCROLLTRIGGER
   Clean, Smooth Paragon-Inspired Animation Architecture (Zero Text Overlaps)
   ========================================== */

document.addEventListener('DOMContentLoaded', () => {
  if (typeof gsap === 'undefined') {
    console.warn('⚠️ GSAP library not found.');
    return;
  }

  gsap.registerPlugin(ScrollTrigger);

  // 1. Preloader Loading Screen Timeline
  const preloader = document.getElementById('preloader');
  const counterEl = document.querySelector('.loader-counter');
  const progressBarFill = document.querySelector('.loader-progress-bar-fill');
  const loaderContent = document.querySelector('.loader-content');
  const curtainLeft = document.querySelector('.loader-curtain-left');
  const curtainRight = document.querySelector('.loader-curtain-right');

  let countObj = { val: 0 };
  
  if (preloader && counterEl) {
    const preloaderTL = gsap.timeline({
      onComplete: () => {
        gsap.to(loaderContent, {
          opacity: 0,
          scale: 0.9,
          duration: 0.5,
          ease: 'power2.in',
          onComplete: () => {
            if (curtainLeft && curtainRight) {
              gsap.to(curtainLeft, { xPercent: -100, duration: 0.9, ease: 'power4.inOut' });
              gsap.to(curtainRight, {
                xPercent: 100,
                duration: 0.9,
                ease: 'power4.inOut',
                onComplete: () => {
                  preloader.style.display = 'none';
                  initHeroAnimations();
                  initScrollReveals();
                }
              });
            } else {
              gsap.to(preloader, {
                opacity: 0,
                duration: 0.8,
                onComplete: () => {
                  preloader.style.display = 'none';
                  initHeroAnimations();
                  initScrollReveals();
                }
              });
            }
          }
        });
      }
    });

    preloaderTL.to(countObj, {
      val: 100,
      duration: 2.2,
      ease: 'power2.inOut',
      onUpdate: () => {
        const rounded = Math.floor(countObj.val);
        counterEl.textContent = rounded;
        if (progressBarFill) {
          progressBarFill.style.width = rounded + '%';
        }
      }
    });
  } else {
    initHeroAnimations();
    initScrollReveals();
  }

  // 2. Hero Animations Trigger
  function initHeroAnimations() {
    const heroTitle = document.querySelector('.hero-title-main');
    if (heroTitle && typeof SplitType !== 'undefined') {
      const splitText = new SplitType(heroTitle, { types: 'words, chars' });

      gsap.from(splitText.chars, {
        y: 50,
        opacity: 0,
        rotateX: -90,
        stagger: 0.02,
        duration: 1.1,
        ease: 'power4.out',
        clearProps: 'transform,opacity'
      });
    }

    gsap.from('.hero-quote-desc', { y: 30, opacity: 1, duration: 0.9, delay: 0.3, ease: 'power3.out' });
    gsap.from('.hero-content .btn', { y: 30, opacity: 1, stagger: 0.15, duration: 0.9, delay: 0.5, ease: 'power3.out' });
    gsap.from('.hero-dish-img', { scale: 0.85, opacity: 1, duration: 1.3, delay: 0.3, ease: 'power4.out' });
    gsap.from('.hero-love-ribbon', { y: 40, opacity: 1, duration: 0.9, delay: 0.7, ease: 'back.out(1.4)' });
  }

  // 3. ScrollTrigger Reveals (Zero Overlaps, Pristine Alignment)
  function initScrollReveals() {
    
    // A. Character-by-Character Kinetic Reveal on Main Headings
    const allTitles = document.querySelectorAll('h1:not(.hero-title-main), h2, h3, .section-title, .about-content-heading, .gallery-main-title');
    allTitles.forEach((title) => {
      if (typeof SplitType !== 'undefined' && !title.classList.contains('split-done')) {
        title.classList.add('split-done');
        const split = new SplitType(title, { types: 'words, chars' });
        
        gsap.from(split.chars, {
          scrollTrigger: {
            trigger: title,
            start: 'top 88%',
            toggleActions: 'play none none reverse'
          },
          y: 40,
          opacity: 0,
          rotateX: -60,
          stagger: 0.015,
          duration: 0.9,
          ease: 'power3.out',
          clearProps: 'transform,opacity'
        });
      } else {
        gsap.from(title, {
          scrollTrigger: {
            trigger: title,
            start: 'top 88%',
            toggleActions: 'play none none reverse'
          },
          y: 40,
          opacity: 0,
          duration: 0.9,
          ease: 'power3.out',
          clearProps: 'transform,opacity'
        });
      }
    });

    // B. Paragon Horizontal Text Parallax Effect on Giant Hollow Titles (NO Sibling Overlaps)
    const hollowTitles = document.querySelectorAll('.hollow-outline-text');
    hollowTitles.forEach((hollow, i) => {
      const direction = i % 2 === 0 ? -10 : 10;
      gsap.to(hollow, {
        scrollTrigger: {
          trigger: hollow,
          start: 'top bottom',
          end: 'bottom top',
          scrub: 1.2
        },
        xPercent: direction,
        ease: 'none'
      });
    });

    // C. Section Parallax Layers (Container Wrappers ONLY)
    const parallaxContainers = document.querySelectorAll('.about-image-wrapper, .hero-dish-container');
    parallaxContainers.forEach((el, index) => {
      const speed = (index % 2 === 0) ? -20 : 20;
      gsap.to(el, {
        scrollTrigger: {
          trigger: el,
          start: 'top bottom',
          end: 'bottom top',
          scrub: 1.5
        },
        y: speed,
        ease: 'none'
      });
    });

    // D. Smooth Section Image Scale-Up on Scroll Entrance
    const foodPhotos = document.querySelectorAll('.about-food-photo, .dm-card-photo-wrap img, .coverflow-card img');
    foodPhotos.forEach((img) => {
      gsap.from(img, {
        scrollTrigger: {
          trigger: img,
          start: 'top 90%',
          toggleActions: 'play none none reverse'
        },
        scale: 0.9,
        opacity: 0.5,
        duration: 1.1,
        ease: 'power3.out',
        clearProps: 'transform,opacity'
      });
    });

    // E. Text Paragraphs & Subtitles Clean Entrance Reveal (Zero Overlapping)
    const textElements = document.querySelectorAll('.section-desc, p, .subheading-caps, .about-content-subtitle, h4, h5');
    textElements.forEach((el) => {
      gsap.from(el, {
        scrollTrigger: {
          trigger: el,
          start: 'top 92%',
          toggleActions: 'play none none reverse'
        },
        y: 30,
        opacity: 0,
        duration: 0.8,
        ease: 'power3.out',
        clearProps: 'transform,opacity'
      });
    });

    // F. Cards & Grid Items Staggered Entrance
    const cards = document.querySelectorAll('.dm-menu-card, .dm-craft-card, .menu-item-row, .glass-card, .special-vert-col');
    cards.forEach((card, index) => {
      gsap.from(card, {
        scrollTrigger: {
          trigger: card,
          start: 'top 88%',
          toggleActions: 'play none none reverse'
        },
        y: 45,
        opacity: 0,
        duration: 0.95,
        delay: (index % 3) * 0.1,
        ease: 'power3.out',
        clearProps: 'transform,opacity'
      });
    });

  }

  console.log('✨ Fixed GSAP ScrollTrigger Animations Active (Zero Text Overlaps).');
});
