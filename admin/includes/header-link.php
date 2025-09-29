  <link rel="stylesheet" href="./assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="./assets/vendor/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="../fonts/remixicon.css">

  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="./assets/css/sidebar.css">
  <link rel="stylesheet" href="./assets/css/header.css">

  <script>
    (function() {
      const saved = localStorage.getItem('pczoneTheme');
      if (saved === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
      }

      const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
      if (window.innerWidth > 768 && sidebarCollapsed) {
        // We apply a class to the body, which is available immediately.
        document.body.classList.add('sidebar-collapsed');
      }
    })();
  </script>