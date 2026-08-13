/* ==========================================
   DABA MAGIC - RESERVATION ENGINE
   Guest Selector, Time Chips & Confirmation Modal
   ========================================== */

document.addEventListener('DOMContentLoaded', () => {
  const guestMinusBtn = document.getElementById('guest-minus');
  const guestPlusBtn = document.getElementById('guest-plus');
  const guestCountEl = document.getElementById('guest-count');
  const guestInput = document.getElementById('res-guests-hidden');

  let guestCount = 2;

  if (guestMinusBtn && guestPlusBtn && guestCountEl) {
    guestMinusBtn.addEventListener('click', () => {
      if (guestCount > 1) {
        guestCount--;
        guestCountEl.textContent = guestCount;
        if (guestInput) guestInput.value = guestCount;
      }
    });

    guestPlusBtn.addEventListener('click', () => {
      if (guestCount < 16) {
        guestCount++;
        guestCountEl.textContent = guestCount;
        if (guestInput) guestInput.value = guestCount;
      }
    });
  }

  // Time Chip Selector
  const timeChips = document.querySelectorAll('.time-chip');
  const selectedTimeInput = document.getElementById('res-time-hidden');

  timeChips.forEach((chip) => {
    chip.addEventListener('click', () => {
      timeChips.forEach(c => c.classList.remove('selected'));
      chip.classList.add('selected');
      const timeVal = chip.getAttribute('data-time');
      if (selectedTimeInput) selectedTimeInput.value = timeVal;
    });
  });

  // Today Date auto-fill minimum
  const dateInput = document.getElementById('res-date');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
    dateInput.value = today;
  }

  // Reservation Form Submission
  const resForm = document.getElementById('reservation-form');
  const confirmModal = document.getElementById('reservation-confirm-modal');
  const modalCloseBtn = document.getElementById('res-modal-close');

  if (resForm) {
    resForm.addEventListener('submit', (e) => {
      e.preventDefault();

      const name = document.getElementById('res-name').value;
      const phone = document.getElementById('res-phone').value;
      const date = document.getElementById('res-date').value;
      const time = selectedTimeInput ? selectedTimeInput.value : '19:30';

      // Generate random confirmation code
      const bookingCode = 'DM-' + Math.floor(100000 + Math.random() * 900000);

      document.getElementById('confirm-name').textContent = name;
      document.getElementById('confirm-details').textContent = `${guestCount} Guests • ${date} at ${time}`;
      document.getElementById('confirm-code').textContent = bookingCode;

      if (confirmModal) {
        confirmModal.classList.add('active');
      }

      resForm.reset();
      guestCount = 2;
      if (guestCountEl) guestCountEl.textContent = '2';
    });
  }

  if (modalCloseBtn && confirmModal) {
    modalCloseBtn.addEventListener('click', () => {
      confirmModal.classList.remove('active');
    });
  }

  console.log('✨ Reservation Engine initialized.');
});
