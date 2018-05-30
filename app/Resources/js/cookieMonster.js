/* eslint-disable */
// not flow cause of global Cookies

var cookieMonster = function() {
  if (!!(typeof window !== 'undefined' && window.document && window.document.createElement)) {
    var document = window.document;
  } else {
    return;
  }
  const cookieBanner = document.getElementById('cookie-banner');
  const cookieConsent = document.getElementById('cookie-consent');
  const GA_COOKIE_NAMES = [
    '__utma',
    '__utmb',
    '__utmc',
    '__utmz',
    '_ga',
    '_gat',
    '_gid',
    '_pk_ref',
    '_pk_cvar',
    '_pk_id',
    '_pk_ses',
    '_pk_hsr',
  ];
  const scrollValueToConsent = 400;

  var isDoNotTrackActive = function() {
    const doNotTrack = navigator.doNotTrack || navigator.msDoNotTrack;
    return doNotTrack === 'yes' || doNotTrack === '1';
  };

  var processCookieConsent = function() {
    const consentCookie = Cookies.getJSON('hasFullConsent');
    const analyticConsent = Cookies.getJSON('analyticConsentValue');
    const adsConsent = Cookies.getJSON('adCookieConsentValue');

    if (consentCookie === true) {
      executeAnalyticScript();
      return;
    }

    if (consentCookie === false) {
      if (analyticConsent === true) {
        executeAnalyticScript();
        return;
      }
      // so do Not Track Is Activated
      hideBanner();
      return;
    }

    // we dont have a consent cookie so, we show the banner
    if (isDoNotTrackActive()) {
      if (typeof analyticConsent === 'undefined') {
        Cookies.set('analyticConsentValue', false, { expires: 395 });
        Cookies.set('adCookieConsentValue', false, { expires: 395 });
      }
      if (consentCookie) {
        Cookies.set('hasFullConsent', false, { expires: 395 });
      }
    }

    cookieBanner.classList.add('active');
    cookieConsent.addEventListener('click', removeCookieConsent, false);
    document.addEventListener('click', onDocumentClick, false);
    document.addEventListener('scroll', onDocumentScroll, false);
  };

  function onDocumentScroll(event) {
    if (window.location.pathname === '/confidentialite') {
      return;
    }

    if (
      document.body.scrollTop > scrollValueToConsent ||
      document.documentElement.scrollTop > scrollValueToConsent
    ) {
      if (isDoNotTrackActive()) {
        hideBanner();
        Cookies.set('hasFullConsent', false, { expires: 395 });

        return;
      }
      if (!Cookies.getJSON('hasFullConsent')) {
        considerFullConsent();
      }
    }
  }

  function hideBanner() {
    cookieBanner.className = cookieBanner.className.replace('active', '').trim();
    document.removeEventListener('click', onDocumentClick, false);
    document.removeEventListener('scroll', onDocumentClick, false);
  }

  function onDocumentClick(event) {
    const target = event.target;
    if (
      target.id === 'cookie-banner' ||
      target.parentNode.id === 'cookie-banner' ||
      target.parentNode.parentNode.id === 'cookie-banner' ||
      target.id === 'cookie-more-button'
    ) {
      return;
    }
    if (isDoNotTrackActive()) {
      Cookies.set('hasFullConsent', false, { expires: 395 });
      hideBanner();
      return;
    }

    // user clicked on close cookie banner
    if (target.id === 'cookie-consent') {
      considerFullConsent();
      return;
    }
    if (window.location.pathname === '/confidentialite' && target.id !== 'main-navbar') {
      return;
    }

    considerFullConsent();
  }

  function considerFullConsent() {
    Cookies.set('hasFullConsent', true, { expires: 395 });
    executeAnalyticScript();
    hideBanner();
  }

  var toggleCookie = function(value, type) {
    Cookies.set('hasFullConsent', false, { expires: 395 });
    hideBanner();
    Cookies.set(type, value, { expires: 395 });
    if (type === 'analyticConsentValue') {
      if (value) {
        executeAnalyticScript();
      } else {
        GA_COOKIE_NAMES.forEach(name => {
          if (typeof Cookies.get(name) !== 'undefined') {
            document.cookie =
              name + '=; expires=' + +new Date() + '; domain=.' + window.location.host + '; path=/';
          }
        });
      }
    } else if (type === 'adCookieConsentValue') {
      if (value) {
        // do something; waiting for instruction
      }
    }
  };

  var analyticCookieValue = function() {
    return Cookies.getJSON('analyticConsentValue');
  };

  var adCookieConsentValue = function() {
    return Cookies.getJSON('adCookieConsentValue');
  };

  function removeCookieConsent(event) {
    var cookieChoiceElement = document.getElementById(cookieConsent);
    if (cookieChoiceElement != null) {
      cookieChoiceElement.parentNode.removeChild(cookieChoiceElement);
    }
  }

  function executeAnalyticScript() {
    window.executeAnalyticScript();
  }

  return {
    processCookieConsent,
    toggleCookie,
    analyticCookieValue,
    adCookieConsentValue,
    isDoNotTrackActive,
  };
};

export default new cookieMonster();
