/* Chaud Winter : menu sur téléphone, recherche, apparition au défilement. */
(function () {
	'use strict';

	var $ = function (s, r) { return (r || document).querySelector(s); };
	var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };

	// Ouvre ou ferme un panneau commandé par un bouton (aria-controls).
	function toggle(btn, open) {
		var target = document.getElementById(btn.getAttribute('aria-controls'));
		if (!target) { return; }
		if (typeof open !== 'boolean') { open = btn.getAttribute('aria-expanded') !== 'true'; }
		btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		return { target: target, open: open };
	}

	// Menu sur téléphone
	var menuBtn = $('.cw-menu-btn');
	if (menuBtn) {
		menuBtn.addEventListener('click', function () {
			var r = toggle(menuBtn);
			if (r) { r.target.classList.toggle('is-open', r.open); }
		});
	}

	// Recherche
	var searchBtn = $('.cw-search-btn');
	if (searchBtn) {
		searchBtn.addEventListener('click', function () {
			var r = toggle(searchBtn);
			if (!r) { return; }
			r.target.hidden = !r.open;
			if (r.open) {
				var input = $('input[type="search"]', r.target);
				if (input) { input.focus(); }
			}
		});
	}

	// Échap ferme le menu et la recherche.
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') { return; }
		if (searchBtn && searchBtn.getAttribute('aria-expanded') === 'true') {
			toggle(searchBtn, false).target.hidden = true;
			searchBtn.focus();
		}
		if (menuBtn && menuBtn.getAttribute('aria-expanded') === 'true') {
			toggle(menuBtn, false).target.classList.remove('is-open');
			menuBtn.focus();
		}
	});

	// Apparition au défilement
	var items = $$('.cw-rv');
	if (!items.length) { return; }
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add('is-in');
					io.unobserve(e.target);
				}
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
		items.forEach(function (el) { io.observe(el); });
	} else {
		items.forEach(function (el) { el.classList.add('is-in'); });
	}
})();
