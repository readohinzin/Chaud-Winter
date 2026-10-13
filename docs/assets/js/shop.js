/* Chaud Winter : boutique (bouton « Filtrer » sur téléphone). */
(function () {
	'use strict';

	var btn = document.querySelector('.cw-filter-toggle');
	var form = document.getElementById('cw-filters');
	if (!btn || !form) { return; }

	// Sans JavaScript, le formulaire reste toujours visible.
	btn.hidden = false;
	form.classList.add('is-collapsible');
	form.classList.toggle('is-open', btn.getAttribute('aria-expanded') === 'true');

	btn.addEventListener('click', function () {
		var open = btn.getAttribute('aria-expanded') !== 'true';
		btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		form.classList.toggle('is-open', open);
		if (open) {
			var first = form.querySelector('input');
			if (first) { first.focus(); }
		}
	});
})();
