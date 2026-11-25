/* Event tracking: Meta Pixel (browser) + Conversions API (server), de-duplicated by event_id.
 *
 * Declarative usage in templates:
 *   <a href="..." data-track="Lead" data-track-params='{"content_name":"x"}'>
 *   <form data-track-submit="Subscribe">
 * Programmatic usage: Tracker.track('Search', {search_string: 'abc'});
 */
(function () {
	var cfg = window.TRACKING;
	if (!cfg || !window.fbq) return;

	function uid() {
		return (window.crypto && crypto.randomUUID) ? crypto.randomUUID()
			: 'e' + Date.now() + Math.random().toString(16).slice(2);
	}

	function send(event, id, params) {
		if (!cfg.server) return;
		var tokenEl = document.querySelector('meta[name="csrf-token"]');
		try {
			fetch(cfg.endpoint, {
				method: 'POST', keepalive: true, credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					'X-CSRF-TOKEN': tokenEl ? tokenEl.content : ''
				},
				body: JSON.stringify({ event: event, event_id: id, url: location.href, params: params || {} })
			}).catch(function () {});
		} catch (e) {}
	}

	var Tracker = window.Tracker = {
		track: function (event, params) {
			var id = uid();
			fbq('track', event, params || {}, { eventID: id });
			send(event, id, params);
		}
	};

	// Page-level events
	Tracker.track('PageView');
	if (cfg.page === 'home') {
		Tracker.track('ViewContent', { content_name: 'Home', content_category: 'home' });
	} else if (cfg.page === 'post') {
		Tracker.track('ViewContent', cfg.params);
	}

	function parse(s) { try { return s ? JSON.parse(s) : {}; } catch (e) { return {}; } }

	document.addEventListener('click', function (e) {
		var el = e.target.closest && e.target.closest('[data-track]');
		if (el) Tracker.track(el.getAttribute('data-track'), parse(el.getAttribute('data-track-params')));
	});

	document.addEventListener('submit', function (e) {
		var f = e.target;
		var ev = f.getAttribute && f.getAttribute('data-track-submit');
		if (!ev) return;
		var p = parse(f.getAttribute('data-track-params'));
		var q = f.querySelector('input[name="search"], input[type="search"]');
		if (ev === 'Search' && q) p.search_string = q.value;
		Tracker.track(ev, p);
	});
})();
