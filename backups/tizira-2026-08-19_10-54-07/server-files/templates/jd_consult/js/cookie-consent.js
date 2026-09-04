(function () {
  var COOKIE_NAME = 'tiza_cookie_consent';
  var LEGACY_NAME = 'spcookie_status';
  var DAYS = 365;

  function getCookie(name) {
    var match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : '';
  }

  function setCookie(name, value, days) {
    var date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = name + '=' + encodeURIComponent(value)
      + '; expires=' + date.toUTCString()
      + '; path=/; SameSite=Lax';
  }

  function hasConsent() {
    return getCookie(COOKIE_NAME) === '1' || getCookie(LEGACY_NAME) === 'ok';
  }

  function loadMetrika() {
    if (window.__tizaMetrikaLoaded) {
      return;
    }
    window.__tizaMetrikaLoaded = true;

    (function (d, w, c) {
      (w[c] = w[c] || []).push(function () {
        try {
          w.yaCounter50276662 = new Ya.Metrika({
            id: 50276662,
            clickmap: true,
            trackLinks: true,
            accurateTrackBounce: true,
            webvisor: true
          });
        } catch (e) {}
      });
      var n = d.getElementsByTagName('script')[0];
      var s = d.createElement('script');
      s.type = 'text/javascript';
      s.async = true;
      s.src = (d.location.protocol === 'https:' ? 'https:' : 'http:') + '//mc.yandex.ru/metrika/watch.js';
      n.parentNode.insertBefore(s, n);
    })(document, window, 'yandex_metrika_callbacks');
  }

  function hideBanner(banner) {
    banner.classList.remove('is-visible');
    window.setTimeout(function () {
      banner.setAttribute('hidden', 'hidden');
    }, 350);
  }

  function showBanner(banner) {
    banner.removeAttribute('hidden');
    window.requestAnimationFrame(function () {
      banner.classList.add('is-visible');
    });
  }

  function accept(banner) {
    setCookie(COOKIE_NAME, '1', DAYS);
    setCookie(LEGACY_NAME, 'ok', DAYS);
    loadMetrika();
    hideBanner(banner);
  }

  function init() {
    var banner = document.getElementById('cookie-consent');
    if (!banner) {
      if (hasConsent()) {
        loadMetrika();
      }
      return;
    }

    var acceptBtn = banner.querySelector('.cookie-consent__btn--accept');
    if (acceptBtn) {
      acceptBtn.addEventListener('click', function () {
        accept(banner);
      });
    }

    if (hasConsent()) {
      loadMetrika();
      banner.setAttribute('hidden', 'hidden');
      return;
    }

    showBanner(banner);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
