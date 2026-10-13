/* Chaud Winter : accueil (onglets de la boutique, « voir plus », assistant de puissance). */
(function () {
	'use strict';

	var $ = function (s, r) { return (r || document).querySelector(s); };
	var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
	var t = window.cwHome || {};

	// Remplace %d, %1$d, %2$d… dans un texte traduit.
	function fmt(str) {
		var args = Array.prototype.slice.call(arguments, 1), i = 0;
		return String(str || '').replace(/%(?:(\d+)\$)?d/g, function (m, n) {
			return n ? args[n - 1] : args[i++];
		});
	}

	/* ---------------- Onglets de la boutique ---------------- */
	var tablist = $('.cw-tabs');
	var tabs = $$('.cw-tab');
	var panels = $$('.cw-panel');

	function selectTab(pid, focus) {
		tabs.forEach(function (tab) {
			var on = tab.getAttribute('data-pid') === pid;
			tab.setAttribute('aria-selected', on ? 'true' : 'false');
			tab.tabIndex = on ? 0 : -1;
			if (on && focus) { tab.focus(); }
		});
		panels.forEach(function (p) { p.hidden = p.getAttribute('data-pid') !== pid; });
	}

	function currentPid() {
		var sel = tabs.filter(function (x) { return x.getAttribute('aria-selected') === 'true'; })[0];
		return sel ? sel.getAttribute('data-pid') : (tabs[0] && tabs[0].getAttribute('data-pid'));
	}

	if (tablist && tabs.length) {
		tablist.hidden = false;
		selectTab(currentPid());

		tabs.forEach(function (tab, i) {
			tab.addEventListener('click', function () { selectTab(tab.getAttribute('data-pid')); });
			tab.addEventListener('keydown', function (e) {
				var n = null;
				if (e.key === 'ArrowRight') { n = tabs[(i + 1) % tabs.length]; }
				if (e.key === 'ArrowLeft') { n = tabs[(i + tabs.length - 1) % tabs.length]; }
				if (e.key === 'Home') { n = tabs[0]; }
				if (e.key === 'End') { n = tabs[tabs.length - 1]; }
				if (n) {
					e.preventDefault();
					selectTab(n.getAttribute('data-pid'), true);
				}
			});
		});
	}

	/* ---------------- « Voir les autres modèles » ---------------- */
	$$('.cw-expand').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var panel = btn.closest('.cw-panel');
			var first = $('.cw-extra', panel);
			panel.classList.remove('is-collapsed');
			// Le focus va sur le premier modèle ajouté, pour les claviers et lecteurs d'écran.
			if (first) {
				var link = $('.cw-name a', first);
				if (link) { link.focus(); }
			}
		});
	});

	/* ---------------- Filtre par puissance ---------------- */
	var note = $('#cw-filter-note');
	var noteTxt = $('#cw-filter-txt');

	function setCount(pid, n) {
		var tab = tabs.filter(function (x) { return x.getAttribute('data-pid') === pid; })[0];
		if (tab) { $('.cw-cnt', tab).textContent = n; }
	}

	function applyFilter(lo, hi) {
		var counts = {}, total = 0;
		panels.forEach(function (p) {
			var n = 0;
			$$('.cw-card', p).forEach(function (c) {
				var kw = parseFloat(c.getAttribute('data-kw'));
				var ok = !isNaN(kw) && kw >= lo && kw <= hi;
				c.hidden = !ok;
				if (ok) { n++; }
			});
			p.classList.remove('is-collapsed');
			counts[p.getAttribute('data-pid')] = n;
			setCount(p.getAttribute('data-pid'), n);
			total += n;
		});

		if (!counts[currentPid()]) {
			var first = Object.keys(counts).filter(function (k) { return counts[k] > 0; })[0];
			if (first) { selectTab(first); }
		}

		noteTxt.textContent = total ? fmt(total > 1 ? t.foundN : t.found1, total, lo, hi) : fmt(t.foundNone, lo, hi);
		note.hidden = false;
	}

	function resetFilter() {
		panels.forEach(function (p) {
			var cards = $$('.cw-card', p);
			cards.forEach(function (c) { c.hidden = false; });
			if ($('.cw-more-wrap', p)) { p.classList.add('is-collapsed'); }
			setCount(p.getAttribute('data-pid'), cards.length);
		});
		note.hidden = true;
	}

	var reset = $('#cw-reset');
	if (reset) { reset.addEventListener('click', resetFilter); }

	/* ---------------- Assistant de choix de puissance ---------------- */
	// Calcul indicatif : surface × coefficient d'isolation, fourchette de ±20 %.
	// Coefficients à faire valider par un professionnel du chauffage.
	var m2 = $('#cw-m2');
	if (!m2) { return; }
	var m2v = $('#cw-m2v'), big = $('#cw-res-big'), sub = $('#cw-res-sub'), go = $('#cw-go');
	var lo = 11, hi = 17;

	function calc() {
		var s = parseInt(m2.value, 10);
		var checked = $('input[name="cw-iso"]:checked');
		var f = parseFloat(checked ? checked.value : '0.115');
		var kw = s * f;
		lo = Math.max(1, Math.round(kw * 0.8));
		hi = Math.round(kw * 1.2);
		m2v.textContent = fmt(t.m2, s);
		big.innerHTML = fmt(t.about, Math.round(kw));
		sub.textContent = fmt(t.range, lo, hi);
		if (go) { go.textContent = fmt(t.go, lo, hi); }
	}

	m2.addEventListener('input', calc);
	$$('input[name="cw-iso"]').forEach(function (r) { r.addEventListener('change', calc); });

	if (go) {
		go.hidden = false;
		go.addEventListener('click', function () {
			applyFilter(lo, hi);
			var shop = $('#boutique');
			var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			shop.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth' });
			note.setAttribute('tabindex', '-1');
			note.focus({ preventScroll: true });
		});
	}

	calc();
})();
