/* Aperçu de démonstration : panier simulé dans le navigateur (localStorage).
   Dans le vrai site, c'est WooCommerce qui gère le panier. */
(function () {
	var CAT = window.HH_CATALOGUE || {};
	var KEY = 'hh_apercu_panier', ORDER = 'hh_apercu_commande', SEQ = 'hh_apercu_numero';

	function load(key, def) {
		try { var v = JSON.parse(localStorage.getItem(key)); return v === null ? def : v; } catch (e) { return def; }
	}
	function save(key, v) { try { localStorage.setItem(key, JSON.stringify(v)); } catch (e) {} }
	function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

	function money(n) {
		return n.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' €';
	}
	function priceHtml(n) {
		var s = n.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
		return '<span class="woocommerce-Price-amount amount"><bdi>' + s + '&nbsp;<span class="woocommerce-Price-currencySymbol">€</span></bdi></span>';
	}

	var cart = load(KEY, {});
	// Ne garde que les produits connus, avec une quantité valide.
	Object.keys(cart).forEach(function (id) {
		if (!CAT[id] || !(cart[id] > 0)) { delete cart[id]; }
	});

	function count() { return Object.keys(cart).reduce(function (n, id) { return n + cart[id]; }, 0); }
	function subtotal() { return Object.keys(cart).reduce(function (n, id) { return n + cart[id] * CAT[id].price; }, 0); }
	function totalCell(total) {
		var tva = total - total / 1.2;
		return '<strong>' + priceHtml(total) + '</strong> <small class="includes_tax">(TVA incluse ' + priceHtml(tva) + ')</small>';
	}

	function refreshCount() {
		var n = count();
		document.querySelectorAll('.cw-cart-count').forEach(function (e) { e.textContent = n; });
		document.querySelectorAll('.cw-cart-link').forEach(function (e) {
			e.setAttribute('aria-label', 'Panier, ' + n + (n > 1 ? ' articles' : ' article'));
		});
	}

	function add(id, qty) {
		if (!CAT[id]) { return; }
		cart[id] = (cart[id] || 0) + qty;
		save(KEY, cart);
		refreshCount();
	}

	// Boutons « Ajouter au panier » des cartes produit (accueil et catégories) : on reste sur la page.
	document.addEventListener('click', function (e) {
		var a = e.target.closest ? e.target.closest('a.add_to_cart_button') : null;
		if (!a) { return; }
		e.preventDefault();
		add(a.getAttribute('data-product_id'), 1);
		a.textContent = 'Ajouté ✓';
		a.classList.add('added');
		var old = a.getAttribute('data-hh-timer');
		if (!old) {
			var t = setTimeout(function () { a.textContent = 'Ajouter au panier'; a.classList.remove('added'); a.removeAttribute('data-hh-timer'); }, 1800);
			a.setAttribute('data-hh-timer', String(t));
		}
	});

	// Fiche produit.
	var form = document.querySelector('form.cart[data-id]');
	if (form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var q = parseInt((form.querySelector('input.qty') || {}).value, 10);
			add(form.getAttribute('data-id'), q > 0 ? q : 1);
			location.href = 'panier.html';
		});
	}

	// Panier.
	var cartForm = document.getElementById('hh-cartform');
	if (cartForm) {
		var rows = document.getElementById('hh-rows');
		var btn = cartForm.querySelector('button[name=update_cart]');
		var draw = function () {
			var ids = Object.keys(cart);
			document.getElementById('hh-empty').hidden = ids.length > 0;
			cartForm.hidden = ids.length === 0;
			document.getElementById('hh-collat').hidden = ids.length === 0;
			var html = ids.map(function (id) {
				var p = CAT[id], q = cart[id];
				return '<tr class="woocommerce-cart-form__cart-item cart_item" data-id="' + id + '">' +
					'<td class="product-remove"><a href="#" class="remove" aria-label="Retirer ' + esc(p.name) + ' du panier">×</a></td>' +
					'<td class="product-thumbnail"><a href="' + p.url + '"><img src="' + p.img + '" width="96" height="96" alt="" loading="lazy"></a></td>' +
					'<td class="product-name" data-title="Produit"><a href="' + p.url + '">' + esc(p.name) + '</a></td>' +
					'<td class="product-price" data-title="Prix">' + priceHtml(p.price) + '</td>' +
					'<td class="product-quantity" data-title="Quantité"><div class="quantity"><input type="number" class="input-text qty text" min="1" value="' + q + '" aria-label="Quantité : ' + esc(p.name) + '"></div></td>' +
					'<td class="product-subtotal" data-title="Sous-total">' + priceHtml(p.price * q) + '</td></tr>';
			}).join('');
			rows.innerHTML = html + '<tr><td colspan="6" class="actions"><button type="submit" class="button" name="update_cart" disabled>Mettre à jour le panier</button></td></tr>';
			btn = cartForm.querySelector('button[name=update_cart]');
			var s = subtotal();
			document.querySelector('[data-hh=subtotal]').innerHTML = priceHtml(s);
			document.querySelector('[data-hh=totalcell]').innerHTML = totalCell(s);
			refreshCount();
		};
		cartForm.addEventListener('click', function (e) {
			var r = e.target.closest ? e.target.closest('a.remove') : null;
			if (!r) { return; }
			e.preventDefault();
			delete cart[r.closest('tr').getAttribute('data-id')];
			save(KEY, cart);
			draw();
		});
		cartForm.addEventListener('input', function (e) {
			if (btn && e.target.classList.contains('qty')) { btn.disabled = false; }
		});
		cartForm.addEventListener('submit', function (e) {
			e.preventDefault();
			cartForm.querySelectorAll('tr.cart_item').forEach(function (tr) {
				var q = parseInt(tr.querySelector('input.qty').value, 10);
				var id = tr.getAttribute('data-id');
				if (q > 0) { cart[id] = q; } else { delete cart[id]; }
			});
			save(KEY, cart);
			draw();
		});
		draw();
	}

	// Commande : récapitulatif du vrai panier, puis confirmation.
	var checkout = document.querySelector('form.checkout');
	if (checkout) {
		if (!count()) { location.replace('panier.html'); return; }
		var crow = document.getElementById('hh-rows');
		crow.innerHTML = Object.keys(cart).map(function (id) {
			return '<tr class="cart_item"><td class="product-name">' + esc(CAT[id].name) + ' <strong class="product-quantity">× ' + cart[id] + '</strong></td><td class="product-total">' + priceHtml(CAT[id].price * cart[id]) + '</td></tr>';
		}).join('');
		var cs = subtotal();
		document.querySelector('[data-hh=subtotal]').innerHTML = priceHtml(cs);
		document.querySelector('[data-hh=totalcell]').innerHTML = totalCell(cs);
		checkout.addEventListener('submit', function (e) {
			e.preventDefault();
			var seq = load(SEQ, 1041) + 1;
			save(SEQ, seq);
			save(ORDER, {
				num: seq,
				items: Object.keys(cart).map(function (id) { return { id: id, q: cart[id] }; }),
				email: (document.getElementById('billing_email') || {}).value || ''
			});
			cart = {};
			save(KEY, cart);
			location.href = 'confirmation.html';
		});
	}

	// Confirmation : la commande qui vient d'être passée.
	var over = document.querySelector('.woocommerce-order-overview');
	if (over) {
		var o = load(ORDER, null);
		if (!o || !o.items || !o.items.length) { location.replace('panier.html'); return; }
		var total = o.items.reduce(function (n, i) { return n + (CAT[i.id] ? CAT[i.id].price * i.q : 0); }, 0);
		var set = function (sel, html) { document.querySelectorAll(sel).forEach(function (e) { e.innerHTML = html; }); };
		set('[data-hh=num]', o.num);
		set('[data-hh=date]', new Date().toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }));
		set('[data-hh=email]', o.email ? esc(o.email) : '—');
		set('[data-hh=total]', priceHtml(total));
		set('[data-hh=totalcell2]', priceHtml(total) + ' <small class="includes_tax">(TVA incluse ' + priceHtml(total - total / 1.2) + ')</small>');
		set('#hh-rows', o.items.filter(function (i) { return CAT[i.id]; }).map(function (i) {
			return '<tr><td>' + esc(CAT[i.id].name) + ' × ' + i.q + '</td><td>' + priceHtml(CAT[i.id].price * i.q) + '</td></tr>';
		}).join(''));
		// Encadré « Dernière étape : le virement ».
		document.querySelectorAll('.cw-transfer-facts dd').forEach(function (dd, k) {
			dd.textContent = k === 0 ? money(total) : 'Commande ' + o.num;
		});
	}

	refreshCount();
})();
