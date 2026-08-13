/* ==========================================
   DABA MAGIC - MAIN SITE LOGIC
   Navigation Overlay Stack, 3D Cover Flow Carousel & Modals
   ========================================== */

document.addEventListener('DOMContentLoaded', () => {
  
  // 1. Header Scroll Effect
  const header = document.querySelector('.site-header');

  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      if (header) header.classList.add('scrolled');
    } else {
      if (header) header.classList.remove('scrolled');
    }
  });

  // 2. Fullscreen Navigation Overlay Stack Handler
  const navOverlay = document.getElementById('dm-nav-overlay');
  const overlayNavLinks = document.querySelectorAll('.overlay-nav-link');

  // 3. Specials Menu Cards Carousel Left & Right Exploration Buttons & Auto Horizontal Scroll
  const specialsPrevBtn = document.getElementById('specials-prev-btn');
  const specialsNextBtn = document.getElementById('specials-next-btn');
  const menuCarouselWrapper = document.getElementById('menu-carousel-wrapper');

  if (menuCarouselWrapper) {
    const scrollAmountPerStep = 380;
    const autoScrollDelay = 3500; // 3.5s delay

    const autoScrollMenu = () => {
      const maxScrollLeft = menuCarouselWrapper.scrollWidth - menuCarouselWrapper.clientWidth;
      if (maxScrollLeft <= 0) return;

      if (menuCarouselWrapper.scrollLeft + scrollAmountPerStep >= maxScrollLeft - 20) {
        // Smoothly loop back to start when reaching the end
        menuCarouselWrapper.scrollTo({ left: 0, behavior: 'smooth' });
      } else {
        menuCarouselWrapper.scrollBy({ left: scrollAmountPerStep, behavior: 'smooth' });
      }
    };

    let specialsTimer = setInterval(autoScrollMenu, autoScrollDelay);

    // Pause auto-scroll on hover so user can interact
    menuCarouselWrapper.addEventListener('mouseenter', () => clearInterval(specialsTimer));
    menuCarouselWrapper.addEventListener('mouseleave', () => {
      clearInterval(specialsTimer);
      specialsTimer = setInterval(autoScrollMenu, autoScrollDelay);
    });

    if (specialsPrevBtn) {
      specialsPrevBtn.addEventListener('click', () => {
        menuCarouselWrapper.scrollBy({ left: -scrollAmountPerStep, behavior: 'smooth' });
      });
    }

    if (specialsNextBtn) {
      specialsNextBtn.addEventListener('click', () => {
        menuCarouselWrapper.scrollBy({ left: scrollAmountPerStep, behavior: 'smooth' });
      });
    }
  }

  function openNavOverlay() {
    if (navOverlay) {
      navOverlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeNavOverlay() {
    if (navOverlay) {
      navOverlay.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  // Event Delegation for Menu Button & Close Button
  document.addEventListener('click', (e) => {
    const toggleBtn = e.target.closest('#dm-menu-toggle-btn') || e.target.closest('.mobile-menu-toggle') || e.target.closest('.dm-menu-circle-btn');
    if (toggleBtn) {
      e.preventDefault();
      openNavOverlay();
      return;
    }

    const closeBtn = e.target.closest('#dm-nav-close-btn') || e.target.closest('.dm-nav-close-btn');
    if (closeBtn) {
      e.preventDefault();
      closeNavOverlay();
      return;
    }

    if (navOverlay && e.target === navOverlay) {
      closeNavOverlay();
    }
  });

  overlayNavLinks.forEach((link) => {
    link.addEventListener('click', () => {
      closeNavOverlay();
    });
  });

  // 3. 3D Cover Flow Gallery Carousel Slider Logic
  const coverflowSlides = document.querySelectorAll('.coverflow-slide');
  const prevBtn = document.getElementById('coverflow-prev');
  const nextBtn = document.getElementById('coverflow-next');
  const dots = document.querySelectorAll('.coverflow-pagination .dot');
  let currentIndex = 2; // Center active item

  function updateCoverflow() {
    const total = coverflowSlides.length;
    if (total === 0) return;

    coverflowSlides.forEach((slide, index) => {
      let offset = index - currentIndex;

      // Handle cyclic wrapping
      if (offset > Math.floor(total / 2)) offset -= total;
      if (offset < -Math.floor(total / 2)) offset += total;

      const isMobile = window.innerWidth <= 768;
      const spacing = isMobile ? 170 : 250;

      if (offset === 0) {
        // Active Center Slide
        slide.style.transform = `translateX(0px) translateZ(120px) rotateY(0deg) scale(1.12)`;
        slide.style.zIndex = '10';
        slide.style.opacity = '1';
        slide.style.filter = 'none';
        slide.classList.add('active');
      } else if (offset === -1) {
        // Left Slide 1
        slide.style.transform = `translateX(-${spacing}px) translateZ(0px) rotateY(25deg) scale(0.88)`;
        slide.style.zIndex = '5';
        slide.style.opacity = '0.85';
        slide.style.filter = 'brightness(0.75)';
        slide.classList.remove('active');
      } else if (offset === 1) {
        // Right Slide 1
        slide.style.transform = `translateX(${spacing}px) translateZ(0px) rotateY(-25deg) scale(0.88)`;
        slide.style.zIndex = '5';
        slide.style.opacity = '0.85';
        slide.style.filter = 'brightness(0.75)';
        slide.classList.remove('active');
      } else if (offset < -1) {
        // Far Left Slides
        slide.style.transform = `translateX(-${spacing * 1.65}px) translateZ(-120px) rotateY(45deg) scale(0.72)`;
        slide.style.zIndex = '2';
        slide.style.opacity = '0.45';
        slide.style.filter = 'brightness(0.5)';
        slide.classList.remove('active');
      } else if (offset > 1) {
        // Far Right Slides
        slide.style.transform = `translateX(${spacing * 1.65}px) translateZ(-120px) rotateY(-45deg) scale(0.72)`;
        slide.style.zIndex = '2';
        slide.style.opacity = '0.45';
        slide.style.filter = 'brightness(0.5)';
        slide.classList.remove('active');
      }
    });

    // Update Pagination Dots
    dots.forEach((dot, idx) => {
      if (idx === currentIndex) {
        dot.classList.add('active');
      } else {
        dot.classList.remove('active');
      }
    });
  }

  if (coverflowSlides.length > 0) {
    updateCoverflow();

    if (prevBtn) {
      prevBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        currentIndex = (currentIndex - 1 + coverflowSlides.length) % coverflowSlides.length;
        updateCoverflow();
      });
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        currentIndex = (currentIndex + 1) % coverflowSlides.length;
        updateCoverflow();
      });
    }

    coverflowSlides.forEach((slide, index) => {
      slide.addEventListener('click', () => {
        currentIndex = index;
        updateCoverflow();
      });
    });

    dots.forEach((dot, index) => {
      dot.addEventListener('click', () => {
        currentIndex = index;
        updateCoverflow();
      });
    });

    // Auto Rotation Timer
    let coverflowTimer = setInterval(() => {
      currentIndex = (currentIndex + 1) % coverflowSlides.length;
      updateCoverflow();
    }, 4500);

    const wrapper = document.getElementById('coverflow-carousel-wrapper');
    if (wrapper) {
      wrapper.addEventListener('mouseenter', () => clearInterval(coverflowTimer));
      wrapper.addEventListener('mouseleave', () => {
        coverflowTimer = setInterval(() => {
          currentIndex = (currentIndex + 1) % coverflowSlides.length;
          updateCoverflow();
        }, 4500);
      });
    }
  }

  // 4. Gallery Lightbox Modal
  const galleryItems = document.querySelectorAll('.gallery-item');
  const lightboxModal = document.getElementById('gallery-lightbox-modal');
  const lightboxImg = document.getElementById('lightbox-target-img');
  const lightboxClose = document.getElementById('lightbox-close');

  if (galleryItems.length > 0 && lightboxModal && lightboxImg) {
    galleryItems.forEach((item) => {
      item.addEventListener('click', (e) => {
        // Only open lightbox if active slide is clicked
        const slide = item.closest('.coverflow-slide');
        if (slide && !slide.classList.contains('active')) return;
        
        const imgSrc = item.querySelector('img').src;
        lightboxImg.src = imgSrc;
        lightboxModal.classList.add('active');
      });
    });

    if (lightboxClose) {
      lightboxClose.addEventListener('click', () => {
        lightboxModal.classList.remove('active');
      });
    }

    lightboxModal.addEventListener('click', (e) => {
      if (e.target === lightboxModal) {
        lightboxModal.classList.remove('active');
      }
    });
  }

  // 6. Floating Back to Top Button Handler
  const backToTopBtn = document.getElementById('back-to-top-btn');
  if (backToTopBtn) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 300) {
        backToTopBtn.classList.add('visible');
      } else {
        backToTopBtn.classList.remove('visible');
      }
    });

    backToTopBtn.addEventListener('click', () => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // 7. Specials Banner Dynamic Image Hover Switcher (Matching Paragon Site 100%)
  const specialVertCols = document.querySelectorAll('.special-vert-col');
  const bgActive = document.getElementById('specials-bg-active');
  const bgNext = document.getElementById('specials-bg-next');

  if (specialVertCols.length > 0 && bgActive && bgNext) {
    specialVertCols.forEach((col) => {
      col.addEventListener('mouseenter', () => {
        const targetImg = col.getAttribute('data-bg');
        if (!targetImg) return;

        specialVertCols.forEach(c => c.classList.remove('active'));
        col.classList.add('active');

        bgNext.style.backgroundImage = `url('${targetImg}')`;
        bgNext.style.opacity = '1';

        setTimeout(() => {
          bgActive.style.backgroundImage = `url('${targetImg}')`;
          bgNext.style.opacity = '0';
        }, 400);
      });
    });
  }

  console.log('✨ Site modules & Vertical Column Specials Image Switcher initialized.');
});
