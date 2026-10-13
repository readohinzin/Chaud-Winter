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

	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	// Les cartes visibles d'un panneau arrivent en cascade (l'ordre part de 0 à chaque fois).
	function cascade(panel) {
		if (reduce || !panel) { return; }
		var k = 0;
		$$('.cw-card', panel).forEach(function (c) {
			if (!c.hidden && !c.classList.contains('cw-extra')) { c.style.setProperty('--k', Math.min(k++, 9)); }
		});
		panel.classList.remove('cw-pre', 'is-entering');
		void panel.offsetWidth;
		panel.classList.add('is-entering');
	}

	function selectTab(pid, focus) {
		tabs.forEach(function (tab) {
			var on = tab.getAttribute('data-pid') === pid;
			tab.setAttribute('aria-selected', on ? 'true' : 'false');
			tab.tabIndex = on ? 0 : -1;
			if (on && focus) { tab.focus(); }
		});
		panels.forEach(function (p) {
			var show = p.getAttribute('data-pid') === pid;
			var was = !p.hidden;
			p.hidden = !show;
			if (show && !was && started) { cascade(p); }
		});
	}

	var started = false;

	function currentPid() {
		var sel = tabs.filter(function (x) { return x.getAttribute('aria-selected') === 'true'; })[0];
		return sel ? sel.getAttribute('data-pid') : (tabs[0] && tabs[0].getAttribute('data-pid'));
	}

	if (tablist && tabs.length) {
		tablist.hidden = false;
		selectTab(currentPid());
		started = true;

		// Première arrivée : le panneau visible attend d'être à l'écran, puis ses cartes entrent en cascade.
		var firstPanel = panels.filter(function (p) { return !p.hidden; })[0];
		if (firstPanel && !reduce && 'IntersectionObserver' in window) {
			firstPanel.classList.add('cw-pre');
			var seen = new IntersectionObserver(function (entries) {
				if (entries[0].isIntersecting) { cascade(firstPanel); seen.disconnect(); }
			}, { rootMargin: '0px 0px -12% 0px' });
			seen.observe(firstPanel);
		}

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
	var sr = $('#cw-res-sr'), band = $('#cw-meter-band'), dot = $('#cw-meter-dot'), box = $('.cw-result');
	var lo = 11, hi = 17, shown = 14, raf = 0, pulse = 0, MAX_KW = 45;

	// Le chiffre « Environ N kW » défile d'une valeur à l'autre (le texte lu par les lecteurs d'écran, lui, change tout de suite).
	function countTo(target) {
		window.cancelAnimationFrame(raf);
		if (reduce) { shown = target; big.innerHTML = fmt(t.about, target); return; }
		var from = shown, t0 = null, dur = 520;
		var step = function (now) {
			if (t0 === null) { t0 = now; }
			var k = Math.min(1, (now - t0) / dur);
			var e = 1 - Math.pow(1 - k, 3);
			shown = Math.round(from + (target - from) * e);
			big.innerHTML = fmt(t.about, shown);
			if (k < 1) { raf = window.requestAnimationFrame(step); }
		};
		raf = window.requestAnimationFrame(step);
	}

	function light() {
		if (!box || reduce) { return; }
		box.classList.add('is-pulse');
		window.clearTimeout(pulse);
		pulse = window.setTimeout(function () { box.classList.remove('is-pulse'); }, 700);
	}

	function calc() {
		var s = parseInt(m2.value, 10);
		var checked = $('input[name="cw-iso"]:checked');
		var f = parseFloat(checked ? checked.value : '0.115');
		var kw = s * f;
		lo = Math.max(1, Math.round(kw * 0.8));
		hi = Math.round(kw * 1.2);
		m2v.textContent = fmt(t.m2, s);
		countTo(Math.round(kw));
		if (sr) { sr.textContent = fmt(t.about, Math.round(kw)).replace(/<[^>]+>/g, ''); }
		sub.textContent = fmt(t.range, lo, hi);
		m2.style.setProperty('--p', ((s - m2.min) / (m2.max - m2.min) * 100).toFixed(1) + '%');
		if (band && dot) {
			var pct = function (v) { return Math.max(0, Math.min(100, v / MAX_KW * 100)); };
			band.style.left = pct(lo) + '%';
			band.style.width = Math.max(1.5, pct(hi) - pct(lo)) + '%';
			dot.style.left = pct(kw) + '%';
		}
		if (go) { go.textContent = fmt(t.go, lo, hi); }
	}

	m2.addEventListener('input', function () { calc(); light(); });
	$$('input[name="cw-iso"]').forEach(function (r) { r.addEventListener('change', function () { calc(); light(); }); });

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
	shown = Math.round(parseInt(m2.value, 10) * parseFloat(($('input[name="cw-iso"]:checked') || { value: '0.115' }).value));
	big.innerHTML = fmt(t.about, shown);
})();

/* Mouvement : profondeur du haut de page et cartes des univers. Rien ne bouge si
   « réduire les animations » est activé. */
(function () {
	'use strict';

	var mq = function (q) { return window.matchMedia ? window.matchMedia(q).matches : false; };
	if (mq('(prefers-reduced-motion: reduce)')) { return; }
	var fine = mq('(hover: hover) and (pointer: fine)');
	var cards = Array.prototype.slice.call(document.querySelectorAll('.cw-cat'));

	// Sur téléphone, le poêle est sous l'écran au chargement : il s'allume quand on le voit.
	var wrap = document.querySelector('.cw-scene-wrap');
	if (wrap && 'IntersectionObserver' in window && wrap.getBoundingClientRect().top > window.innerHeight * 0.75) {
		wrap.classList.add('cw-wait');
		var lit = new IntersectionObserver(function (entries) {
			if (entries[0].isIntersecting) {
				wrap.classList.remove('cw-wait');
				lit.disconnect();
			}
		}, { threshold: 0.3 });
		lit.observe(wrap);
	}

	// Les cartes deviennent réactives dès qu'elles ont fini de se dévoiler.
	cards.forEach(function (card) {
		card.addEventListener('transitionend', function (e) {
			if (e.target === card && e.propertyName === 'clip-path' && card.classList.contains('is-in')) {
				card.classList.add('is-done');
			}
		});
	});

	if (fine) {
		// Haut de page : les plans de l'illustration suivent la souris, avec un léger retard.
		var hero = document.querySelector('.cw-hero');
		var scene = document.querySelector('.cw-scene-wrap');
		if (hero && scene) {
			var tx = 0, ty = 0, cx = 0, cy = 0, raf = 0;
			var loop = function () {
				cx += (tx - cx) * 0.07;
				cy += (ty - cy) * 0.07;
				scene.style.setProperty('--px', cx.toFixed(3));
				scene.style.setProperty('--py', cy.toFixed(3));
				raf = Math.abs(tx - cx) + Math.abs(ty - cy) > 0.002 ? window.requestAnimationFrame(loop) : 0;
			};
			var kick = function () { if (!raf) { raf = window.requestAnimationFrame(loop); } };
			hero.addEventListener('pointermove', function (e) {
				var r = scene.getBoundingClientRect();
				tx = Math.max(-1, Math.min(1, (e.clientX - r.left - r.width / 2) / (r.width / 2)));
				ty = Math.max(-1, Math.min(1, (e.clientY - r.top - r.height / 2) / (r.height / 2)));
				kick();
			});
			hero.addEventListener('pointerleave', function () { tx = 0; ty = 0; kick(); });
		}

		// Univers : la carte s'incline vers la souris, une lueur suit le curseur.
		cards.forEach(function (card) {
			card.addEventListener('pointermove', function (e) {
				var r = card.getBoundingClientRect();
				var x = (e.clientX - r.left) / r.width, y = (e.clientY - r.top) / r.height;
				card.style.setProperty('--mx', (x * 100).toFixed(1) + '%');
				card.style.setProperty('--my', (y * 100).toFixed(1) + '%');
				card.style.setProperty('--rx', ((0.5 - y) * 5).toFixed(2) + 'deg');
				card.style.setProperty('--ry', ((x - 0.5) * 7).toFixed(2) + 'deg');
			});
			card.addEventListener('pointerleave', function () {
				card.style.setProperty('--rx', '0deg');
				card.style.setProperty('--ry', '0deg');
			});
		});
	} else if ('IntersectionObserver' in window) {
		// Téléphone : la carte au centre de l'écran s'anime (photo, lueur, flèche).
		var io = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) { e.target.classList.toggle('is-active', e.isIntersecting); });
		}, { rootMargin: '-40% 0px -40% 0px' });
		cards.forEach(function (card) { io.observe(card); });
	}
})();


/* Barre de progression de lecture, fine, en haut de l'écran. */
(function () {
	'use strict';
	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
	var bar = document.createElement('div');
	bar.className = 'cw-progress';
	bar.setAttribute('aria-hidden', 'true');
	document.body.appendChild(bar);
	var ticking = false;
	var update = function () {
		var h = document.documentElement.scrollHeight - window.innerHeight;
		bar.style.setProperty('--sp', h > 0 ? Math.min(1, window.scrollY / h).toFixed(4) : 0);
		ticking = false;
	};
	window.addEventListener('scroll', function () {
		if (!ticking) { ticking = true; window.requestAnimationFrame(update); }
	}, { passive: true });
	update();
})();

/* Feu animé de la photo à la une : cale le cadre du feu sur la photo (même recadrage que l'image),
   et met l'animation en pause quand elle n'est pas à l'écran. */
(function () {
	'use strict';

	var fx = document.querySelector('.cw-fx');
	var media = document.querySelector('.cw-hero-media');
	if (!fx || !media) { return; }
	if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }

	var w = parseInt(fx.getAttribute('data-w'), 10), h = parseInt(fx.getAttribute('data-h'), 10);
	if (!w || !h) { return; }
	var svg = fx.querySelector('svg');

	// La photo remplit le cadre en le recouvrant (comme object-fit: cover), centrée.
	function layout() {
		var s = Math.max(media.clientWidth / w, media.clientHeight / h);
		fx.style.transform = 'translate(' + (-w * s / 2) + 'px,' + (-h * s / 2) + 'px) scale(' + s + ')';
	}

	layout();
	if ('ResizeObserver' in window) { new ResizeObserver(layout).observe(media); } else { window.addEventListener('resize', layout); }
	window.requestAnimationFrame(function () { fx.classList.add('is-on'); });

	if ('IntersectionObserver' in window) {
		new IntersectionObserver(function (entries) {
			var on = entries[0].isIntersecting;
			fx.classList.toggle('is-paused', !on);
			if (svg && svg.pauseAnimations) { if (on) { svg.unpauseAnimations(); } else { svg.pauseAnimations(); } }
		}, { threshold: 0.05 }).observe(media);
	}
})();
