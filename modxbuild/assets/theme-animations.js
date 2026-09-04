(function () {
  'use strict';
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function ready(fn) {
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn);
    else fn();
  }

  function initCursor() {
    return;
  }

  function initAmbient() {
    return;
  }

  function initProgress() {
    if (reduced) return;
    var bar = document.createElement('div');
    bar.className = 'tz-progress';
    document.body.appendChild(bar);
    var tick = false;
    function upd() {
      var st = window.pageYOffset || document.documentElement.scrollTop;
      var dh = document.documentElement.scrollHeight - window.innerHeight;
      bar.style.width = (dh > 0 ? (st / dh) * 100 : 0) + '%';
      tick = false;
    }
    window.addEventListener('scroll', function () {
      if (!tick) { requestAnimationFrame(upd); tick = true; }
    }, { passive: true });
  }

  function initHeader() {
    var header = document.getElementById('tz-header');
    if (!header) return;
    var last = 0;
    var tick = false;

    function syncHeaderOffset() {
      var landing = document.querySelector('.tz-hero--landing');
      var h = header.offsetHeight;
      if (h > 0) {
        if (landing) {
          document.documentElement.style.setProperty('--tz-landing-header', h + 'px');
          document.documentElement.style.setProperty('--tz-header-offset', h + 'px');
        } else if (window.innerWidth <= 991) {
          document.documentElement.style.setProperty('--tz-header-offset', h + 'px');
          document.documentElement.style.removeProperty('--tz-landing-header');
        } else {
          document.documentElement.style.removeProperty('--tz-header-offset');
          document.documentElement.style.removeProperty('--tz-landing-header');
        }
      }
    }

    syncHeaderOffset();
    window.addEventListener('resize', syncHeaderOffset, { passive: true });
    if (typeof ResizeObserver !== 'undefined') {
      var ro = new ResizeObserver(syncHeaderOffset);
      ro.observe(header);
    }

    function upd() {
      var y = window.pageYOffset || document.documentElement.scrollTop;
      header.classList.toggle('tz-header--scrolled', y > 30);
      if (!reduced && y > 140 && window.innerWidth > 991) header.classList.toggle('tz-header--hidden', y > last && y > 220);
      else header.classList.remove('tz-header--hidden');
      last = y;
      tick = false;
    }
    window.addEventListener('scroll', function () {
      if (!tick) { requestAnimationFrame(upd); tick = true; }
    }, { passive: true });
  }

  function initDrawer() {
    var burger = document.getElementById('tz-burger');
    var drawer = document.getElementById('tz-drawer');
    var backdrop = document.getElementById('tz-drawer-backdrop');
    var closeBtn = document.getElementById('tz-drawer-close');
    if (!burger || !drawer) return;
    function open() {
      drawer.classList.add('is-open');
      drawer.setAttribute('aria-hidden', 'false');
      burger.setAttribute('aria-expanded', 'true');
      document.body.classList.add('tz-no-scroll');
    }
    function close() {
      drawer.classList.remove('is-open');
      drawer.setAttribute('aria-hidden', 'true');
      burger.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('tz-no-scroll');
    }
    burger.addEventListener('click', function () { drawer.classList.contains('is-open') ? close() : open(); });
    if (backdrop) backdrop.addEventListener('click', close);
    if (closeBtn) closeBtn.addEventListener('click', close);
    drawer.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', close); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
  }

  function initReveal() {
    var els = document.querySelectorAll('.tz-reveal');
    if (reduced || !('IntersectionObserver' in window)) {
      els.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        en.target.classList.add('is-visible');
        io.unobserve(en.target);
      });
    }, { threshold: 0.06, rootMargin: '0px 0px -24px 0px' });
    els.forEach(function (el) { io.observe(el); });
  }

  function initMap() {
    var maps = document.querySelectorAll('.tz-map');
    if (!maps.length) return;

    maps.forEach(function (map) {
      var iframe = map.querySelector('iframe[data-src]');
      var cover = map.querySelector('.tz-map__cover');
      var closeBtn = map.querySelector('.tz-map__close');
      if (!iframe || !cover) return;

      var src = iframe.getAttribute('data-src');

      function closeMap() {
        map.classList.remove('is-open');
        map.classList.add('is-covered');
        iframe.removeAttribute('src');
        if (closeBtn) closeBtn.hidden = true;
      }

      function openMap() {
        if (!iframe.getAttribute('src')) iframe.setAttribute('src', src);
        map.classList.remove('is-covered');
        map.classList.add('is-open');
        if (closeBtn) closeBtn.hidden = false;
      }

      closeMap();

      cover.addEventListener('click', function (e) {
        e.preventDefault();
        openMap();
      });

      if (closeBtn) {
        closeBtn.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();
          closeMap();
        });
      }

      if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
          entries.forEach(function (en) {
            if (!map.classList.contains('is-open')) return;
            if (!en.isIntersecting || en.intersectionRatio < 0.12) closeMap();
          });
        }, { threshold: [0, 0.12, 0.35] });
        io.observe(map);
      }
    });
  }

  function initSearchFocus() {
    var bar = document.getElementById('filter-bar');
    if (!bar) return;
    bar.querySelectorAll('select, input').forEach(function (el) {
      el.addEventListener('focus', function () { bar.classList.add('is-focused'); });
      el.addEventListener('blur', function () {
        setTimeout(function () { if (!bar.querySelector(':focus')) bar.classList.remove('is-focused'); }, 80);
      });
    });
  }

  function initTotop() {
    var btn = document.getElementById('tz-totop');
    if (!btn) return;
    function upd() {
      btn.classList.toggle('is-visible', (window.pageYOffset || document.documentElement.scrollTop) > 400);
    }
    window.addEventListener('scroll', upd, { passive: true });
    upd();
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'auto' });
    });
  }

  function initStaffingForm() {
    var forms = document.querySelectorAll('.tz-staff-form__form');
    if (!forms.length) return;

    forms.forEach(function (form) {
      var siteKey = form.getAttribute('data-recaptcha-site-key');
      if (!siteKey) return;

      form.addEventListener('submit', function (e) {
        if (form.dataset.tzRecaptchaReady === '1') {
          form.dataset.tzRecaptchaReady = '';
          return;
        }
        e.preventDefault();
        var tokenField = form.querySelector('input[name="g-recaptcha-response"]');
        if (!tokenField) {
          form.submit();
          return;
        }

        function run() {
          window.grecaptcha.ready(function () {
            window.grecaptcha.execute(siteKey, { action: 'staffing_form' }).then(function (token) {
              tokenField.value = token;
              form.dataset.tzRecaptchaReady = '1';
              if (typeof form.requestSubmit === 'function') form.requestSubmit();
              else form.submit();
            }).catch(function () {
              form.dataset.tzRecaptchaReady = '1';
              if (typeof form.requestSubmit === 'function') form.requestSubmit();
              else form.submit();
            });
          });
        }

        if (window.grecaptcha && window.grecaptcha.execute) run();
        else {
          var tries = 0;
          var timer = setInterval(function () {
            tries += 1;
            if (window.grecaptcha && window.grecaptcha.execute) {
              clearInterval(timer);
              run();
            } else if (tries > 40) {
              clearInterval(timer);
              form.dataset.tzRecaptchaReady = '1';
              if (typeof form.requestSubmit === 'function') form.requestSubmit();
              else form.submit();
            }
          }, 150);
        }
      });
    });
  }

  function initLanding() {
    var hero = document.getElementById('tz-hero');
    if (!hero || !hero.classList.contains('tz-hero--landing')) return;

    document.documentElement.classList.add('tz-home');
    document.body.classList.add('tz-home');

    var hint = document.getElementById('tz-scroll-hint');
    var target = document.getElementById('tz-main-content');

    if (hint && target) {
      hint.addEventListener('click', function (e) {
        e.preventDefault();
        var top = target.getBoundingClientRect().top + window.pageYOffset;
        window.scrollTo({ top: top, behavior: 'auto' });
      });
    }

    var tick = false;
    window.addEventListener('scroll', function () {
      if (!tick) {
        requestAnimationFrame(function () {
          var y = window.pageYOffset || document.documentElement.scrollTop;
          if (hint) hint.classList.toggle('is-hidden', y > 80);
          tick = false;
        });
        tick = true;
      }
    }, { passive: true });
  }

  ready(function () {
    initAmbient();
    initCursor();
    initProgress();
    initHeader();
    initDrawer();
    initLanding();
    initReveal();
    initMap();
    initSearchFocus();
    initTotop();
    initStaffingForm();
    document.body.classList.add('tz-ready');
  });
})();
