/**
 * ECOZONE — Shared Navbar Controller
 * Handles: scroll compression, hamburger menu, theme toggle, live clock, active link detection
 * Import this on every page: <script src="assets/js/navbar.js"></script>
 */
(function () {
  'use strict';

  // ─── Scroll Compression ──────────────────────
  var navbar = document.getElementById('mainNav');
  if (navbar) {
    var SCROLL_THRESHOLD = 50;
    var ticking = false;
    function onScroll() {
      if (!ticking) {
        window.requestAnimationFrame(function () {
          if (window.scrollY > SCROLL_THRESHOLD) {
            navbar.classList.add('scrolled');
          } else {
            navbar.classList.remove('scrolled');
          }
          ticking = false;
        });
        ticking = true;
      }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); // check initial state
  }

  // ─── Hamburger Menu ──────────────────────────
  var hamburger = document.getElementById('navHamburger');
  var navLinks = navbar ? navbar.querySelector('.nav-links') : null;
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', function () {
      hamburger.classList.toggle('open');
      navLinks.classList.toggle('mobile-open');
    });
    // Close menu when a link is clicked
    var links = navLinks.querySelectorAll('.nav-link-item, .btn-nav-back');
    for (var i = 0; i < links.length; i++) {
      links[i].addEventListener('click', function () {
        hamburger.classList.remove('open');
        navLinks.classList.remove('mobile-open');
      });
    }
  }

  // ─── Theme Toggle ────────────────────────────
  var html = document.documentElement;
  var themeBtn = document.getElementById('themeToggle');
  var themeIcon = document.getElementById('themeIcon');

  // Apply saved theme on load
  if (localStorage.getItem('eco-theme') === 'dark') {
    html.setAttribute('data-theme', 'dark');
    if (themeIcon) themeIcon.textContent = '☀️';
    var dashThemeIcon = document.getElementById('themeIconDash');
    if (dashThemeIcon) dashThemeIcon.textContent = '☀️';
  }

  if (themeBtn) {
    themeBtn.addEventListener('click', function () {
      var isDark = html.getAttribute('data-theme') === 'dark';

      // Trigger spin animation
      themeBtn.classList.add('animating');
      setTimeout(function () { themeBtn.classList.remove('animating'); }, 420);

      var dashThemeIcon = document.getElementById('themeIconDash');

      if (isDark) {
        html.removeAttribute('data-theme');
        localStorage.setItem('eco-theme', 'light');
        if (themeIcon) themeIcon.textContent = '🌙';
        if (dashThemeIcon) dashThemeIcon.textContent = '🌙';
      } else {
        html.setAttribute('data-theme', 'dark');
        localStorage.setItem('eco-theme', 'dark');
        if (themeIcon) themeIcon.textContent = '☀️';
        if (dashThemeIcon) dashThemeIcon.textContent = '☀️';
      }
    });
  }

  // ─── Live Clock ──────────────────────────────
  var clockEl = document.getElementById('liveClock');
  if (clockEl) {
    function tick() {
      var n = new Date();
      clockEl.textContent =
        n.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) +
        '  •  ' +
        n.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
    tick();
    setInterval(tick, 1000);
  }

  // ─── Active Link Detection ───────────────────
  // Automatically highlights the correct nav link based on the current page
  var currentPath = window.location.pathname.split('/').pop() || 'index.html';
  var allNavLinks = document.querySelectorAll('.nav-link-item');
  for (var j = 0; j < allNavLinks.length; j++) {
    var href = allNavLinks[j].getAttribute('href');
    if (!href) continue;
    var linkPage = href.split('/').pop().split('#')[0];
    // Remove existing active classes first
    allNavLinks[j].classList.remove('active');
    if (linkPage === currentPath) {
      allNavLinks[j].classList.add('active');
    }
  }

})();
