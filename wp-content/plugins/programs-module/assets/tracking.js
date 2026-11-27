(function () {
  'use strict';
  document.addEventListener('click', function (event) {
    var target = event.target.closest('[data-track]');
    if (!target || !window.upmTracking) return;
    var payload;
    try { payload = JSON.parse(target.getAttribute('data-track')); } catch (error) { return; }
    var body = JSON.stringify(payload);
    if (navigator.sendBeacon) { navigator.sendBeacon(window.upmTracking.endpoint, new Blob([body], { type: 'application/json' })); }
    else { fetch(window.upmTracking.endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: body, keepalive: true }).catch(function () {}); }
  });
})();
