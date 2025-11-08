(function(){
  // Sidebar toggle for desktop collapsible and mobile drawer
  const body = document.body;
  const sidebar = document.querySelector('.sidebar');
  const toggleBtn = document.getElementById('sidebarToggle');
  const currentDateTime = document.getElementById('currentDateTime');

  function updateClock(){
    try {
      const now = new Date();
      const dateStr = now.toLocaleDateString(undefined, { weekday:'short', year:'numeric', month:'short', day:'numeric' });
      const timeStr = now.toLocaleTimeString(undefined, { hour:'2-digit', minute:'2-digit', second:'2-digit' });
      if (currentDateTime) currentDateTime.textContent = `${dateStr} • ${timeStr}`;
    } catch (e) {}
  }

  setInterval(updateClock, 1000);
  updateClock();

  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      // On large screens, collapse body; on small, open sidebar drawer
      if (window.innerWidth >= 992) {
        body.classList.toggle('body-collapsed');
      } else if (sidebar) {
        sidebar.classList.toggle('open');
      }
    });
  }

  // Close mobile sidebar when clicking outside
  document.addEventListener('click', (e) => {
    if (window.innerWidth < 992 && sidebar && sidebar.classList.contains('open')) {
      const inSidebar = sidebar.contains(e.target);
      const inToggle = toggleBtn && toggleBtn.contains(e.target);
      if (!inSidebar && !inToggle) sidebar.classList.remove('open');
    }
  });

  // Initialize Bootstrap tooltips
  try {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(el => {
      // eslint-disable-next-line no-undef
      new bootstrap.Tooltip(el, { container: 'body' });
    });
  } catch (e) { /* bootstrap not loaded yet */ }
})();
