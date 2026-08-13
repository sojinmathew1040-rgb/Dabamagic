/* ==========================================
   DABA MAGIC - INTERACTIVE MENU & SEARCH
   Filtering, Search, Dietary Tags & Dish Modal
   ========================================== */

document.addEventListener('DOMContentLoaded', () => {
  const catTabs = document.querySelectorAll('.cat-tab-btn');
  const menuItems = document.querySelectorAll('.menu-item-card, .menu-item-row');
  const searchInput = document.getElementById('menu-search-input');
  const dietChips = document.querySelectorAll('.diet-chip');

  let activeCategory = 'all';
  let activeDietFilter = 'all';
  let searchQuery = '';

  // 1. Category Tab Switcher
  catTabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      catTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      activeCategory = tab.getAttribute('data-category');
      filterMenu();
    });
  });

  // 2. Search Filter Listener
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      searchQuery = e.target.value.toLowerCase().trim();
      filterMenu();
    });
  }

  // 3. Dietary Filter Listener
  dietChips.forEach((chip) => {
    chip.addEventListener('click', () => {
      dietChips.forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      activeDietFilter = chip.getAttribute('data-diet');
      filterMenu();
    });
  });

  // 3b. Category Tabs Left & Right Navigation Arrows
  const catTabsPrevBtn = document.getElementById('cat-tabs-prev-btn');
  const catTabsNextBtn = document.getElementById('cat-tabs-next-btn');
  const catTabsScrollTrack = document.getElementById('category-tabs-scroll-track');

  if (catTabsPrevBtn && catTabsNextBtn && catTabsScrollTrack) {
    catTabsPrevBtn.addEventListener('click', () => {
      catTabsScrollTrack.scrollBy({ left: -220, behavior: 'smooth' });
    });

    catTabsNextBtn.addEventListener('click', () => {
      catTabsScrollTrack.scrollBy({ left: 220, behavior: 'smooth' });
    });
  }

  // Filter Algorithm
  function filterMenu() {
    menuItems.forEach((item) => {
      const itemCategory = item.getAttribute('data-category');
      const itemDiet = item.getAttribute('data-diet');
      const itemName = item.getAttribute('data-name') ? item.getAttribute('data-name').toLowerCase() : '';
      const itemDesc = item.getAttribute('data-desc') ? item.getAttribute('data-desc').toLowerCase() : '';

      const matchesCat = activeCategory === 'all' || itemCategory === activeCategory;
      const matchesDiet = activeDietFilter === 'all' || itemDiet === activeDietFilter;
      const matchesSearch = searchQuery === '' || itemName.includes(searchQuery) || itemDesc.includes(searchQuery);

      if (matchesCat && matchesDiet && matchesSearch) {
        item.style.display = 'flex';
        gsap.fromTo(item, { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' });
      } else {
        item.style.display = 'none';
      }
    });
  }

  // 4. Dish Detail Modal Integration
  const dishModal = document.getElementById('dish-detail-modal');
  const modalCloseBtns = document.querySelectorAll('.modal-close-trigger');
  const viewDishBtns = document.querySelectorAll('.btn-view-dish, .trigger-dish-modal');

  viewDishBtns.forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const parentCard = btn.closest('[data-name]');
      if (!parentCard || !dishModal) return;

      const name = parentCard.getAttribute('data-name');
      const price = parentCard.getAttribute('data-price');
      const desc = parentCard.getAttribute('data-desc');
      const img = parentCard.getAttribute('data-img');
      const spice = parentCard.getAttribute('data-spice') || '2';

      document.getElementById('modal-dish-title').textContent = name;
      document.getElementById('modal-dish-price').textContent = price;
      document.getElementById('modal-dish-desc').textContent = desc;
      document.getElementById('modal-dish-img').src = img;

      // Spice Chili Rating
      const spiceContainer = document.getElementById('modal-dish-spice');
      if (spiceContainer) {
        let chilis = '';
        for (let i = 0; i < parseInt(spice); i++) {
          chilis += '<i class="fa-solid fa-pepper-hot"></i> ';
        }
        spiceContainer.innerHTML = chilis;
      }

      dishModal.classList.add('active');
    });
  });

  modalCloseBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      if (dishModal) dishModal.classList.remove('active');
    });
  });

  console.log('✨ Menu filtering and dish modal initialized.');
});
