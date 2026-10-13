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

	// Le panier rebondit quand le nombre d'articles augmente (ajout sans rechargement de page).
	var header = $('.cw-header') || $('header');
	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	if (header && 'MutationObserver' in window && !reduceMotion) {
		var count = function () { var c = $('.cw-cart-count', header); return c ? parseInt(c.textContent, 10) || 0 : 0; };
		var last = count();
		new MutationObserver(function () {
			var n = count();
			if (n > last) {
				var link = $('.cw-cart-link', header);
				if (link) {
					link.classList.remove('cw-bump');
					void link.offsetWidth;
					link.classList.add('cw-bump');
				}
			}
			last = n;
		}).observe(header, { childList: true, subtree: true, characterData: true });
	}

	// En-tête : fond plus dense et filet braise dès qu'on a quitté le haut de la page.
	if (header) {
		var scrolled = false;
		var onScroll = function () {
			var s = window.scrollY > 24;
			if (s !== scrolled) {
				scrolled = s;
				header.classList.toggle('is-scrolled', s);
			}
		};
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	// Apparition au défilement
	var items = $$('.cw-rv');
	if (!items.length) { return; }
	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (e.isIntersecting) {
					e.target.classList.add('is-in');
					io.unobserve(e.target);
					// Une fois la cascade terminée, le bloc répond au survol sans délai.
					window.setTimeout(function () { e.target.classList.add('is-done'); }, 2200);
				}
			});
		}, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
		items.forEach(function (el) { io.observe(el); });
	} else {
		items.forEach(function (el) { el.classList.add('is-in'); });
	}
})();
