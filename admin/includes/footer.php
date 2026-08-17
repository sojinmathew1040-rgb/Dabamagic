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

  // Sidebar Expand / Collapse Toggle & LocalStorage State Persistence
  const adminWrapper = document.querySelector('.admin-wrapper');
  const collapseBtn = document.getElementById('sidebar-collapse-btn');
  const collapseIcon = document.getElementById('collapse-icon');
  const sidebarToggleBtn = document.getElementById('sidebar-toggle');
  const adminSidebar = document.getElementById('admin-sidebar');

  function setSidebarCollapsed(collapsed) {
    if (!adminWrapper) return;
    if (collapsed) {
      adminWrapper.classList.add('collapsed-sidebar');
      if (collapseIcon) {
        collapseIcon.classList.remove('fa-angles-left');
        collapseIcon.classList.add('fa-angles-right');
      }
      localStorage.setItem('admin_sidebar_collapsed', 'true');
    } else {
      adminWrapper.classList.remove('collapsed-sidebar');
      if (collapseIcon) {
        collapseIcon.classList.remove('fa-angles-right');
        collapseIcon.classList.add('fa-angles-left');
      }
      localStorage.setItem('admin_sidebar_collapsed', 'false');
    }
  }

  // Restore sidebar state from localStorage on load
  if (localStorage.getItem('admin_sidebar_collapsed') === 'true') {
    setSidebarCollapsed(true);
  }

  // Dedicated collapse button click
  if (collapseBtn) {
    collapseBtn.addEventListener('click', () => {
      const isCollapsed = adminWrapper.classList.contains('collapsed-sidebar');
      setSidebarCollapsed(!isCollapsed);
    });
  }

  // Header toggle button click (mobile open vs desktop collapse)
  if (sidebarToggleBtn) {
    sidebarToggleBtn.addEventListener('click', () => {
      if (window.innerWidth <= 768) {
        if (adminSidebar) adminSidebar.classList.toggle('mobile-open');
      } else {
        const isCollapsed = adminWrapper.classList.contains('collapsed-sidebar');
        setSidebarCollapsed(!isCollapsed);
      }
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
