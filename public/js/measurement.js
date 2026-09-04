(function () {
  'use strict';

  var CONSENT_VERSION = 1;
  var COOKIE_MAX_AGE = 34128000; // 13 months
  var googleBootstrapped = false;
  var marketingLoaded = false;
  var clarityLoaded = false;
  var plausibleLoaded = false;
  var pendingFired = false;

  function readJson(id) {
    var node = document.getElementById(id);
    if (!node) return null;
    try {
      return JSON.parse(node.textContent || '');
    } catch (e) {
      return null;
    }
  }

  function escapeHtml(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  /**
   * Normalize legacy schemas (shoow flat tools.*, webnu nested tools.gtag.id)
   * into the unified config shape.
   */
  function normalizeConfig(raw) {
    if (!raw || typeof raw !== 'object') return null;

    var brand = raw.brand || 'app';
    var tools = raw.tools || {};
    var consented = raw.consented || {};
    var exempt = raw.exempt || {};

    var gtmId = consented.gtmId || tools.gtmId || (tools.gtm && tools.gtm.id) || null;
    var gtagId = consented.gtagId || tools.gtagId || (tools.gtag && tools.gtag.id) || null;
    if (gtmId) gtagId = null;

    var clarityId = consented.clarityId || tools.clarityId || (tools.clarity && tools.clarity.id) || null;
    var metaPixelId = consented.metaPixelId || tools.metaPixelId || null;
    var linkedinPartnerId = consented.linkedinPartnerId || tools.linkedinPartnerId || null;

    var plausibleDomain = exempt.plausibleDomain || tools.plausibleDomain || null;
    var plausibleScriptUrl = exempt.plausibleScriptUrl || '/stats/js/script.js';
    var plausibleApiUrl = exempt.plausibleApiUrl || '/stats/api/event';

    return {
      enabled: Boolean(raw.enabled),
      brand: brand,
      cookieBanner: raw.cookieBanner !== false,
      loadGoogleBeforeConsent: raw.loadGoogleBeforeConsent !== false,
      exempt: {
        plausibleDomain: plausibleDomain,
        plausibleScriptUrl: plausibleScriptUrl,
        plausibleApiUrl: plausibleApiUrl,
      },
      consented: {
        gtmId: gtmId,
        gtagId: gtagId,
        clarityId: clarityId,
        metaPixelId: metaPixelId,
        linkedinPartnerId: linkedinPartnerId,
      },
      pendingEvent: raw.pendingEvent || null,
    };
  }

  function storageKey(brand) {
    return brand + '_cookie_consent';
  }

  function cookieName(brand) {
    return brand + '_cookie_consent';
  }

  function readCookie(name) {
    try {
      var parts = (';cookie || '').split(';');
      for (var i = 0; i < parts.length; i++) {
        var part = parts[i].trim();
        if (part.indexOf(name + '=') === 0) {
          return decodeURIComponent(part.substring(name.length + 1));
        }
      }
    } catch (e) {
      /* ignore */
    }
    return null;
  }

  function writeCookie(name, value) {
    try {
      var secure = typeof location !== 'undefined' && location.protocol === 'https:' ? '; Secure' : '';
      document.cookie =
        name +
        '=' +
        encodeURIComponent(value) +
        '; Path=/; Max-Age=' +
        COOKIE_MAX_AGE +
        '; SameSite=Lax' +
        secure;
    } catch (e) {
      /* ignore */
    }
  }

  function parseConsentPayload(raw, brand) {
    if (!raw) return null;

    // Legacy webnu: plain 'accepted' / 'rejected'
    if (raw === 'accepted') {
      return {
        v: CONSENT_VERSION,
        necessary: true,
        analytics: true,
        marketing: false,
        updatedAt: new Date().toISOString(),
      };
    }
    if (raw === 'rejected') {
      return {
        v: CONSENT_VERSION,
        necessary: true,
        analytics: false,
        marketing: false,
        updatedAt: new Date().toISOString(),
      };
    }

    try {
      var data = typeof raw === 'string' ? JSON.parse(raw) : raw;
      if (!data || typeof data !== 'object') return null;
      if (data.v !== CONSENT_VERSION) return null;
      return {
        v: CONSENT_VERSION,
        necessary: true,
        analytics: Boolean(data.analytics),
        marketing: Boolean(data.marketing),
        updatedAt: data.updatedAt || new Date().toISOString(),
      };
    } catch (e) {
      return null;
    }
  }

  function readConsent(brand) {
    var key = storageKey(brand);
    var fromCookie = parseConsentPayload(readCookie(cookieName(brand)), brand);
    if (fromCookie) return fromCookie;

    try {
      var raw = localStorage.getItem(key);
      var fromLs = parseConsentPayload(raw, brand);
      if (fromLs) {
        // Promote legacy LS → cookie
        writeConsent(brand, fromLs);
        return fromLs;
      }
    } catch (e) {
      /* ignore */
    }

    return null;
  }

  function writeConsent(brand, consent) {
    var payload = JSON.stringify({
      v: CONSENT_VERSION,
      necessary: true,
      analytics: Boolean(consent.analytics),
      marketing: Boolean(consent.marketing),
      updatedAt: consent.updatedAt || new Date().toISOString(),
    });
    writeCookie(cookieName(brand), payload);
    try {
      localStorage.setItem(storageKey(brand), payload);
    } catch (e) {
      /* ignore */
    }
  }

  function hasGoogleTools(consented) {
    return Boolean(consented.gtmId || consented.gtagId);
  }

  function hasConsentableTools(consented) {
    return Boolean(
      consented.gtmId ||
        consented.gtagId ||
        consented.clarityId ||
        consented.metaPixelId ||
        consented.linkedinPartnerId
    );
  }

  function hasMarketingTools(consented) {
    return Boolean(consented.metaPixelId || consented.linkedinPartnerId);
  }

  function hasAnalyticsConsentTools(consented) {
    return Boolean(consented.gtmId || consented.gtagId || consented.clarityId);
  }

  function loadScript(src, attrs) {
    return new Promise(function (resolve, reject) {
      var script = document.createElement('script');
      script.src = src;
      script.async = true;
      if (attrs) {
        Object.keys(attrs).forEach(function (key) {
          script.setAttribute(key, attrs[key]);
        });
      }
      script.onload = function () {
        resolve();
      };
      script.onerror = function () {
        reject(new Error('Failed to load ' + src));
      };
      document.head.appendChild(script);
    });
  }

  function ensureDataLayer() {
    window.dataLayer = window.dataLayer || [];
  }

  function ensureGtag() {
    ensureDataLayer();
    if (typeof window.gtag !== 'function') {
      window.gtag = function gtag() {
        window.dataLayer.push(arguments);
      };
    }
  }

  function trackEvent(name, params) {
    ensureDataLayer();
    window.dataLayer.push(Object.assign({ event: name }, params || {}));
    if (typeof window.gtag === 'function') {
      window.gtag('event', name, params || {});
    }
    if (typeof window.fbq === 'function') {
      window.fbq('trackCustom', name, params || {});
    }
  }

  function setConsentDefault() {
    ensureGtag();
    window.gtag('consent', 'default', {
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied',
      analytics_storage: 'denied',
      functionality_storage: 'granted',
      security_storage: 'granted',
      wait_for_update: 500,
    });
    window.gtag('set', 'url_passthrough', true);
    window.gtag('set', 'ads_data_redaction', true);
  }

  function updateConsent(consent) {
    ensureGtag();
    window.gtag('consent', 'update', {
      analytics_storage: consent.analytics ? 'granted' : 'denied',
      ad_storage: consent.marketing ? 'granted' : 'denied',
      ad_user_data: consent.marketing ? 'granted' : 'denied',
      ad_personalization: consent.marketing ? 'granted' : 'denied',
    });
    if (consent.marketing) {
      window.gtag('set', 'ads_data_redaction', false);
    } else {
      window.gtag('set', 'ads_data_redaction', true);
    }
  }

  function loadGtm(id, brand) {
    ensureDataLayer();
    var scriptId = brand + '-gtm-script';
    if (document.getElementById(scriptId)) {
      return Promise.resolve();
    }

    var script = document.createElement('script');
    script.id = scriptId;
    script.text =
      "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':" +
      "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0]," +
      "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=" +
      "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);" +
      "})(window,document,'script','dataLayer','" +
      id +
      "');";

    var firstHeadChild = document.head.firstChild;
    if (firstHeadChild) {
      document.head.insertBefore(script, firstHeadChild);
    } else {
      document.head.appendChild(script);
    }

    injectGtmNoscript(id, brand);
    return Promise.resolve();
  }

  function injectGtmNoscript(id, brand) {
    var noscriptId = brand + '-gtm-noscript';
    if (document.getElementById(noscriptId) || !document.body) return;

    var noscript = document.createElement('noscript');
    noscript.id = noscriptId;
    noscript.innerHTML =
      '<iframe src="https://www.googletagmanager.com/ns.html?id=' +
      encodeURIComponent(id) +
      '" height="0" width="0" style="display:none;visibility:hidden"></iframe>';

    var firstBodyChild = document.body.firstChild;
    if (firstBodyChild) {
      document.body.insertBefore(noscript, firstBodyChild);
    } else {
      document.body.appendChild(noscript);
    }
  }

  function loadGtag(id) {
    ensureGtag();
    window.gtag('js', new Date());
    window.gtag('config', id, { anonymize_ip: true });
    return loadScript(
      'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(id)
    ).catch(function () {
      /* non-fatal */
    });
  }

  function loadClarity(id) {
    if (clarityLoaded) return Promise.resolve();
    clarityLoaded = true;
    window.clarity =
      window.clarity ||
      function clarity() {
        (window.clarity.q = window.clarity.q || []).push(arguments);
      };
    return loadScript('https://www.clarity.ms/tag/' + encodeURIComponent(id)).catch(function () {
      /* non-fatal */
    });
  }

  function loadPlausible(exempt) {
    if (plausibleLoaded || !exempt.plausibleDomain) return Promise.resolve();
    plausibleLoaded = true;
    return loadScript(exempt.plausibleScriptUrl || '/stats/js/script.js', {
      'data-domain': exempt.plausibleDomain,
      'data-api': exempt.plausibleApiUrl || '/stats/api/event',
      defer: '',
    }).catch(function () {
      /* non-fatal */
    });
  }

  function loadMetaPixel(id) {
    window.fbq =
      window.fbq ||
      function fbq() {
        window.fbq.callMethod
          ? window.fbq.callMethod.apply(window.fbq, arguments)
          : window.fbq.queue.push(arguments);
      };
    if (!window._fbq) window._fbq = window.fbq;
    window.fbq.push = window.fbq;
    window.fbq.loaded = true;
    window.fbq.version = '2.0';
    window.fbq.queue = window.fbq.queue || [];
    window.fbq('init', id);
    window.fbq('track', 'PageView');
    return loadScript('https://connect.facebook.net/en_US/fbevents.js').catch(function () {
      /* non-fatal */
    });
  }

  function loadLinkedIn(partnerId) {
    window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || [];
    window._linkedin_data_partner_ids.push(partnerId);
    return loadScript('https://snap.licdn.com/li.lms-analytics/insight.min.js').catch(function () {
      /* non-fatal */
    });
  }

  function bootstrapGoogle(config) {
    var consented = config.consented;
    if (!hasGoogleTools(consented) || googleBootstrapped) {
      return Promise.resolve();
    }
    googleBootstrapped = true;
    setConsentDefault();
    if (consented.gtmId) {
      return loadGtm(consented.gtmId, config.brand);
    }
    return loadGtag(consented.gtagId);
  }

  function firePendingEvent(config) {
    if (!config.pendingEvent || pendingFired) return;
    pendingFired = true;
    trackEvent(config.pendingEvent);
  }

  function applyConsent(config, consent) {
    var consented = config.consented;

    function afterGoogleReady() {
      updateConsent(consent);
      // Tras grant, dispara page_view explícito: el config inicial pudo ir en denied
      // y muchos paneles de GA4 no muestran esas hits cookieless.
      if (consent.analytics && (consented.gtagId || consented.gtmId)) {
        try {
          window.gtag('event', 'page_view', {
            page_title: document.title,
            page_location: location.href,
            page_path: location.pathname + location.search,
          });
        } catch (e) {
          /* ignore */
        }
      }
    }

    if (hasGoogleTools(consented)) {
      if (config.loadGoogleBeforeConsent) {
        bootstrapGoogle(config).then(afterGoogleReady);
      } else if (consent.analytics || consent.marketing) {
        bootstrapGoogle(config).then(afterGoogleReady);
      }
    }

    if (consent.analytics && consented.clarityId) {
      loadClarity(consented.clarityId);
    }

    if (consent.marketing && !marketingLoaded) {
      marketingLoaded = true;
      if (consented.metaPixelId) loadMetaPixel(consented.metaPixelId);
      if (consented.linkedinPartnerId) loadLinkedIn(consented.linkedinPartnerId);
    }

    if (consent.analytics || consent.marketing) {
      firePendingEvent(config);
    }
  }

  function createBanner(config, labels, onSave) {
    var brand = config.brand;
    var bannerId = brand + '-cookie-banner';
    var existing = document.getElementById(bannerId);
    if (existing) existing.remove();

    var consented = config.consented;
    var showAnalytics = hasAnalyticsConsentTools(consented);
    var showMarketing = hasMarketingTools(consented);

    var banner = document.createElement('div');
    banner.id = bannerId;
    banner.className = 'cookie-banner';
    banner.setAttribute('role', 'dialog');
    banner.setAttribute('aria-live', 'polite');
    banner.setAttribute('aria-label', labels.title || 'Cookies');

    var analyticsId = brand + '-consent-analytics';
    var marketingId = brand + '-consent-marketing';

    banner.innerHTML =
      '<div class="cookie-banner__panel">' +
      '<p class="cookie-banner__title">' +
      escapeHtml(labels.title || '') +
      '</p>' +
      '<p class="cookie-banner__text">' +
      escapeHtml(labels.description || '') +
      '</p>' +
      (labels.exemptNote
        ? '<p class="cookie-banner__exempt">' + escapeHtml(labels.exemptNote) + '</p>'
        : '') +
      '<div class="cookie-banner__options">' +
      '<label class="cookie-banner__option">' +
      '<input type="checkbox" checked disabled />' +
      '<span>' +
      escapeHtml(labels.necessary || 'Necesarias') +
      '</span></label>' +
      (showAnalytics
        ? '<label class="cookie-banner__option">' +
          '<input type="checkbox" id="' +
          analyticsId +
          '" />' +
          '<span>' +
          escapeHtml(labels.analytics || 'Analítica') +
          '</span></label>'
        : '') +
      (showMarketing
        ? '<label class="cookie-banner__option">' +
          '<input type="checkbox" id="' +
          marketingId +
          '" />' +
          '<span>' +
          escapeHtml(labels.marketing || 'Marketing') +
          '</span></label>'
        : '') +
      '</div>' +
      '<div class="cookie-banner__actions">' +
      '<button type="button" class="cookie-banner__btn cookie-banner__btn--ghost" data-action="reject">' +
      escapeHtml(labels.reject || 'Rechazar') +
      '</button>' +
      '<button type="button" class="cookie-banner__btn cookie-banner__btn--ghost" data-action="save">' +
      escapeHtml(labels.save || 'Guardar selección') +
      '</button>' +
      '<button type="button" class="cookie-banner__btn cookie-banner__btn--primary" data-action="accept">' +
      escapeHtml(labels.acceptAll || labels.accept || 'Aceptar todas') +
      '</button>' +
      '</div>' +
      '<p class="cookie-banner__legal">' +
      '<a href="' +
      escapeHtml(labels.privacyUrl || '/legal/privacidad') +
      '">' +
      escapeHtml(labels.privacyLink || 'Privacidad') +
      '</a></p></div>';

    document.body.appendChild(banner);

    var analyticsInput = banner.querySelector('#' + analyticsId);
    var marketingInput = banner.querySelector('#' + marketingId);

    function persistAndClose(analytics, marketing) {
      var consent = {
        v: CONSENT_VERSION,
        necessary: true,
        analytics: Boolean(analytics),
        marketing: Boolean(marketing),
        updatedAt: new Date().toISOString(),
      };
      writeConsent(brand, consent);
      banner.remove();
      onSave(consent);
    }

    banner.querySelector('[data-action="accept"]').addEventListener('click', function () {
      persistAndClose(showAnalytics, showMarketing);
    });

    banner.querySelector('[data-action="reject"]').addEventListener('click', function () {
      persistAndClose(false, false);
    });

    banner.querySelector('[data-action="save"]').addEventListener('click', function () {
      var analytics = analyticsInput ? analyticsInput.checked : false;
      var marketing = marketingInput ? marketingInput.checked : false;
      persistAndClose(analytics, marketing);
    });

    if (analyticsInput && marketingInput) {
      analyticsInput.addEventListener('change', function () {
        if (!analyticsInput.checked) marketingInput.checked = false;
      });
      marketingInput.addEventListener('change', function () {
        if (marketingInput.checked) analyticsInput.checked = true;
      });
    }

    requestAnimationFrame(function () {
      banner.classList.add('is-visible');
    });
  }

  function initManageLinks(config, labels) {
    document.querySelectorAll('[data-manage-cookies]').forEach(function (link) {
      link.addEventListener('click', function (event) {
        event.preventDefault();
        createBanner(config, labels, function (consent) {
          applyConsent(config, consent);
        });
      });
    });
  }

  function exposeTrackApis(config) {
    function guardedTrack(name, params) {
      var consent = readConsent(config.brand);
      if (!consent) return;
      if (!consent.analytics && !consent.marketing) return;
      trackEvent(name, params);
    }

    window.trackEvent = guardedTrack;
    window.shoowTrackEvent = guardedTrack;
    window.webnuTrackEvent = guardedTrack;
  }

  function init() {
    // Discover config: try brand-prefixed ids, fall back to known legacy ids
    var raw =
      readJson('shoow-measurement-config') ||
      readJson('webnu-measurement-config') ||
      null;

    // Also try generic scan for *-measurement-config if brand unknown
    if (!raw) {
      var nodes = document.querySelectorAll('script[type="application/json"][id$="-measurement-config"]');
      if (nodes.length) {
        try {
          raw = JSON.parse(nodes[0].textContent || '');
        } catch (e) {
          raw = null;
        }
      }
    }

    var config = normalizeConfig(raw);
    if (!config || !config.enabled) return;

    var labels =
      readJson(config.brand + '-measurement-labels') ||
      readJson('shoow-measurement-labels') ||
      readJson('webnu-measurement-labels') ||
      {};

    exposeTrackApis(config);

    // Layer 1 — always-on exempt analytics
    loadPlausible(config.exempt);

    var consented = config.consented;
    var anyConsentable = hasConsentableTools(consented);

    if (!anyConsentable) {
      firePendingEvent(config);
      return;
    }

    initManageLinks(config, labels);

    // Layer 2 — Consent Mode bootstrap (denied) before any user choice
    if (config.loadGoogleBeforeConsent && hasGoogleTools(consented)) {
      bootstrapGoogle(config);
    }

    var stored = readConsent(config.brand);
    if (stored) {
      applyConsent(config, stored);
      return;
    }

    if (!config.cookieBanner) {
      applyConsent(config, {
        v: CONSENT_VERSION,
        necessary: true,
        analytics: true,
        marketing: true,
        updatedAt: new Date().toISOString(),
      });
      return;
    }

    createBanner(config, labels, function (consent) {
      applyConsent(config, consent);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
