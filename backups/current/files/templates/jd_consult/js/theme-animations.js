(function () {
  'use strict';

  var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  function initScrollReveal() {
    if (prefersReduced || !('IntersectionObserver' in window)) {
      document.querySelectorAll('.tz-reveal').forEach(function (el) {
        el.classList.add('is-visible');
      });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.05, rootMargin: '0px 0px -20px 0px' });

    document.querySelectorAll('.tz-reveal').forEach(function (el, i) {
      el.style.setProperty('--tz-delay', (i % 6) * 0.08 + 's');
      observer.observe(el);
      // Show elements already in viewport immediately
      var rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight && rect.bottom > 0) {
        el.classList.add('is-visible');
      }
    });
  }

  function markRevealTargets() {
    var selectors = [
      '#sp-logo .tz-contact-card',
      '#sp-search',
      '#sp-main-body .sp-column > h2',
      '#sp-main-body .sp-column > p',
      '#sp-main-body .sp-column > ul',
      '#sp-full-width',
      '#sp-bottom .joboffer',
      '#sp-bottom .sp-module-title',
      '#sp-footer1',
      '#sp-footer2',
      '.itemlayer',
      '.category.table tr'
    ];

    var skip = '.items, .items_list, #system-message-container, #jobokay_search_container, #jobokSearchForm, form';

    selectors.forEach(function (sel) {
      document.querySelectorAll(sel).forEach(function (el) {
        if (el.matches(skip) || el.closest(skip)) return;
        if (!el.classList.contains('tz-reveal')) {
          el.classList.add('tz-reveal');
        }
      });
    });
  }

  function initStickyHeader() {
    /* header is not sticky — matches reference layout */
  }

  function initSmoothAnchors() {
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function (e) {
        var id = a.getAttribute('href');
        if (!id || id === '#') return;
        var target = document.querySelector(id);
        if (!target) return;
        e.preventDefault();
        target.scrollIntoView({ behavior: prefersReduced ? 'auto' : 'smooth', block: 'start' });
      });
    });
  }

  function initSearchFocus() {
    var bar = document.getElementById('filter-bar');
    if (!bar) return;
    bar.querySelectorAll('select, input').forEach(function (el) {
      el.addEventListener('focus', function () {
        bar.classList.add('is-focused');
      });
      el.addEventListener('blur', function () {
        setTimeout(function () {
          if (!bar.querySelector(':focus')) bar.classList.remove('is-focused');
        }, 80);
      });
    });
  }

  function initHeroSlider() {
    var hero = document.getElementById('sp-page-title');
    if (!hero || !hero.querySelector('.fotorama108, .fotorama')) return;
    hero.classList.add('tz-hero');
  }

  function initMapShield() {
    document.querySelectorAll('.tz-map').forEach(function (map) {
      var shield = map.querySelector('.tz-map__shield');
      var iframe = map.querySelector('iframe');
      if (!shield || !iframe) return;

      shield.addEventListener('click', function () {
        map.classList.add('is-active');
      });

      map.addEventListener('wheel', function (e) {
        if (!map.classList.contains('is-active')) return;
        e.stopPropagation();
      }, { passive: true });
    });
  }

  onReady(function () {
    markRevealTargets();
    initHeroSlider();
    initStickyHeader();
    initScrollReveal();
    initSmoothAnchors();
    initSearchFocus();
    initMapShield();
    document.body.classList.add('tz-ready');
  });
})();
