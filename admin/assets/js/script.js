const hamburger = document.getElementById('hamburger');
const sidebar = document.getElementById('sidebar');
const navItems = document.querySelectorAll('.nav-item');
const path = window.location.pathname;

hamburger.addEventListener('click', () => {
  if (window.innerWidth <= 768) {
    sidebar.classList.toggle('hide-on-mobile');
  } else {
    sidebar.classList.toggle('collapsed');
  }
});

// Highlight current page
navItems.forEach(link => {
  if (link.getAttribute('href') === path) {
    link.classList.add('active');
  }
});

document.getElementById("hamburger").addEventListener("click", function () {
  const sidebar = document.getElementById("sidebar");
  sidebar.classList.toggle("show");
});


// Product row click
document.querySelectorAll('.product-row').forEach(row => {
  row.addEventListener('click', function (e) {
    // Prevent click if it's inside a button/link
    if (e.target.tagName.toLowerCase() === 'a') return;

    const id = this.getAttribute('data-id');
    window.location.href = `view_product.php?id=${id}`;
  });
});



