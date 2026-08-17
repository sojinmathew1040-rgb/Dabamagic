<?php
/**
 * Daba Magic - Admin Footer Include
 */
?>
    </main> <!-- /.admin-content -->

    <!-- Admin Footer -->
    <footer class="admin-footer">
      <div>
        &copy; <?php echo date('Y'); ?> <strong>Daba Magic Panel</strong>. Authentic Indian Cuisine Management.
      </div>
      <div>
        System Status: <span style="color: var(--clr-green-bright); font-weight: 600;">● Online (Local Host)</span>
      </div>
    </footer>

  </div> <!-- /.admin-main -->
</div> <!-- /.admin-wrapper -->

<!-- JavaScript Scripts -->
<script>
  // Real-time Clock Updater
  function updateLiveClock() {
    const clockEl = document.getElementById('live-time-display');
    if (!clockEl) return;
    const now = new Date();
    const options = { 
      day: '2-digit', 
      month: 'short', 
      year: 'numeric', 
      hour: '2-digit', 
      minute: '2-digit', 
      second: '2-digit',
      hour12: false 
    };
    clockEl.innerText = now.toLocaleDateString('en-GB', options).replace(',', ' |');
  }
  setInterval(updateLiveClock, 1000);
  updateLiveClock();

  // Mobile Sidebar Toggle
  const sidebarToggleBtn = document.getElementById('sidebar-toggle');
  const adminSidebar = document.getElementById('admin-sidebar');
  if (sidebarToggleBtn && adminSidebar) {
    sidebarToggleBtn.addEventListener('click', () => {
      adminSidebar.classList.toggle('mobile-open');
    });
  }

  // Global Search Filter (Tables)
  const globalSearchInput = document.getElementById('global-admin-search');
  if (globalSearchInput) {
    globalSearchInput.addEventListener('keyup', function() {
      const filter = this.value.toLowerCase();
      const rows = document.querySelectorAll('.admin-table tbody tr');
      rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
      });
    });
  }
</script>
</body>
</html>
