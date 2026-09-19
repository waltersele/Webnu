<?php
/**
 * First-party analytics beacon (no third-party cookies).
 * Counts pageviews / CTA clicks for the platform analytics panel.
 */
(function () {
  'use strict';

  var ENDPOINT = '/analytics/beacon';
  var sentPageview = false;

  function post(payload) {
    try {
      var body = JSON.stringify(payload);
      if (navigator.sendBeacon) {
        var blob = new Blob([body], { type: 'application/json' });
        navigator.sendBeacon(ENDPOINT, blob);
        return;
      }
    } catch (e) {
      /* fall through */
    }
    try {
      fetch(ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(payload),
        keepalive: true,
        credentials: 'same-origin',
      }).catch(function () {});
    } catch (err) {
      /* ignore */
    }
  }

  function trackPageview() {
    if (sentPageview) return;
    sentPageview = true;
    post({
      event: 'pageview',
      path: location.pathname + location.search,
      referrer: document.referrer || '',
    });
  }

  function trackCta(cta) {
    if (!cta) return;
    post({
      event: 'cta',
      path: location.pathname,
      referrer: document.referrer || '',
      cta: String(cta).slice(0, 64),
    });
  }

  window.webnuAnalytics = {
    pageview: trackPageview,
    cta: trackCta,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', trackPageview);
  } else {
    trackPageview();
  }

  document.addEventListener(
    'click',
    function (e) {
      var el = e.target && e.target.closest ? e.target.closest('[data-wn-analytics-cta]') : null;
      if (!el) return;
      trackCta(el.getAttribute('data-wn-analytics-cta') || el.getAttribute('href') || 'cta');
    },
    true
  );
})();
