<?php
/**
 * Petits morceaux d'affichage réutilisés dans tout le thème.
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Symbole du logo (flamme noire dans un carré orange, cœur bleu glacier).
 *
 * @param int $size Taille en pixels.
 * @return string
 */
function cw_logo_symbol( $size = 44 ) {
	return sprintf(
		'<svg class="cw-logo-symbol" width="%1$d" height="%1$d" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect width="24" height="24" rx="6" fill="#F26B2A"/><path d="M12 3c1 3.4 4.8 5.4 4.8 9.6a4.8 4.8 0 1 1-9.6 0c0-1.8.8-3.2 1.9-4.3.3 1.4 1 2.1 1.9 2.4C10.6 8.1 10.4 5.3 12 3z" fill="#101216"/><ellipse cx="12" cy="16.4" rx="2.1" ry="2.7" fill="#9CCBE0"/></svg>',
		(int) $size
	);
}

/**
 * Logo complet : symbole + « Chaud » en romain + « Winter » en italique bleu glacier.
 *
 * @param bool $link Faire du logo un lien vers l'accueil.
 */
function cw_logo( $link = true ) {
	$inner = cw_logo_symbol() . '<span class="cw-logo-word">Hot<em>Hiver</em></span>';
	if ( $link ) {
		printf(
			'<a href="%1$s" class="cw-logo" rel="home" aria-label="%2$s">%3$s</a>',
			esc_url( home_url( '/' ) ),
			esc_attr__( 'HotHiver, accueil', 'chaud-winter' ),
			$inner // phpcs:ignore WordPress.Security.EscapeOutput -- SVG et texte fixes.
		);
	} else {
		echo '<div class="cw-logo">' . $inner . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/**
 * Icônes en SVG.
 *
 * @param string $name search, cart, menu ou close.
 * @return string
 */
function cw_icon( $name ) {
	$paths = array(
		'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>',
		'cart'   => '<path d="M3 4h2l2.4 11h9.7l2-8H6.2"/><circle cx="9" cy="19" r="1.4"/><circle cx="17" cy="19" r="1.4"/>',
		'menu'   => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'  => '<path d="M6 6l12 12M18 6L6 18"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[ $name ] . '</svg>';
}

/**
 * Lien vers le panier avec le nombre d'articles.
 *
 * @return string
 */
function cw_cart_link() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		$count = 0;
		$url   = home_url( '/panier/' );
	} else {
		$count = (int) WC()->cart->get_cart_contents_count();
		$url   = wc_get_cart_url();
	}
	/* translators: %d: nombre d'articles dans le panier. */
	$label = sprintf( _n( 'Panier, %d article', 'Panier, %d articles', $count, 'chaud-winter' ), $count );

	return sprintf(
		'<a href="%1$s" class="cw-icon-btn cw-cart-link" aria-label="%2$s">%3$s<span class="cw-cart-count">%4$d</span></a>',
		esc_url( $url ),
		esc_attr( $label ),
		cw_icon( 'cart' ),
		$count
	);
}

/**
 * Met à jour le lien du panier après un ajout, sans recharger la page.
 *
 * @param array $fragments Morceaux de page remplacés par WooCommerce.
 * @return array
 */
function cw_cart_fragment( $fragments ) {
	$fragments['a.cw-cart-link'] = cw_cart_link();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'cw_cart_fragment' );

/**
 * Numéro de téléphone cliquable (ou le repère tant qu'il n'est pas fourni).
 *
 * @return string
 */
function cw_phone_html() {
	$phone = cw_info( 'phone' );
	if ( cw_is_placeholder( $phone ) ) {
		return esc_html( $phone );
	}
	return sprintf( '<a href="tel:%1$s">%2$s</a>', esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ), esc_html( $phone ) );
}

/**
 * Adresse e-mail cliquable (ou le repère tant qu'elle n'est pas fournie).
 *
 * @return string
 */
function cw_email_html() {
	$email = cw_info( 'email' );
	if ( cw_is_placeholder( $email ) || ! is_email( $email ) ) {
		return esc_html( $email );
	}
	return sprintf( '<a href="mailto:%1$s">%1$s</a>', esc_html( antispambot( $email ) ) );
}

/**
 * Menu de secours tant qu'aucun menu n'est choisi dans Apparence > Menus :
 * les catégories de produits (puis Contact dans l'en-tête), ou les pages
 * d'information si elles existent déjà (pied de page).
 *
 * @param array $args Arguments de wp_nav_menu().
 * @return string|void HTML du menu si $args['echo'] vaut false.
 */
function cw_menu_fallback( $args ) {
	$items = array();

	if ( 'cw-footer-infos' === $args['theme_location'] ) {
		$slugs = array_diff( array_keys( cw_page_definitions() ), array( 'contact' ) );
		foreach ( $slugs as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page && 'publish' === $page->post_status ) {
				$items[] = sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_permalink( $page ) ), esc_html( get_the_title( $page ) ) );
			}
		}
	} elseif ( taxonomy_exists( 'product_cat' ) ) {
		$cats = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => false,
				'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
			)
		);
		if ( ! is_wp_error( $cats ) ) {
			// Ordre de la maquette (granulés, bois, chaudières), puis les éventuelles autres catégories.
			$order = array_flip( array_keys( cw_universes() ) );
			usort(
				$cats,
				function ( $a, $b ) use ( $order ) {
					$pa = isset( $order[ $a->slug ] ) ? $order[ $a->slug ] : 99;
					$pb = isset( $order[ $b->slug ] ) ? $order[ $b->slug ] : 99;
					return $pa <=> $pb ? $pa <=> $pb : strcmp( $a->name, $b->name );
				}
			);
			foreach ( $cats as $cat ) {
				$items[] = sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_term_link( $cat ) ), esc_html( $cat->name ) );
			}
		}
	}

	if ( 'cw-header' === $args['theme_location'] ) {
		$items[] = sprintf( '<li><a href="%1$s">%2$s</a></li>', esc_url( cw_callback_url() ), esc_html__( 'Contact', 'chaud-winter' ) );
	}

	if ( ! $items ) {
		return '';
	}
	$html = sprintf( '<ul class="%1$s">%2$s</ul>', esc_attr( $args['menu_class'] ), implode( '', $items ) );
	if ( isset( $args['echo'] ) && ! $args['echo'] ) {
		return $html;
	}
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput -- échappé plus haut.
}
