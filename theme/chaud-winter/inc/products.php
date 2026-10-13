<?php
/**
 * Produits : catégories de la boutique, puissance, marque, carte produit.
 *
 * Organisation attendue des produits (voir produits.csv, étape 7) :
 * - catégories : poeles-a-granules, poeles-a-bois, chaudieres ;
 * - marque : taxonomie « Marques » de WooCommerce ;
 * - puissance : attribut global « Puissance » (pa_puissance), par exemple « 11,6 kW ».
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Les trois univers de la boutique, dans l'ordre d'affichage.
 * La clé est l'identifiant (slug) de la catégorie WooCommerce.
 *
 * @return array
 */
function cw_universes() {
	return array(
		'poeles-a-granules' => array(
			'label' => __( 'Poêles à granulés', 'chaud-winter' ),
			'text'  => __( 'Programmables et propres, ils gèrent le feu à votre place.', 'chaud-winter' ),
		),
		'poeles-a-bois'     => array(
			'label' => __( 'Poêles à bois', 'chaud-winter' ),
			'text'  => __( 'La flamme vivante, en fonte, en acier ou en céramique.', 'chaud-winter' ),
		),
		'chaudieres'        => array(
			'label' => __( 'Chaudières', 'chaud-winter' ),
			'text'  => __( 'Chauffez toute la maison, radiateurs et eau chaude.', 'chaud-winter' ),
		),
	);
}

/**
 * Illustration SVG d'un univers (poêle à granulés, poêle à bois, chaudière).
 *
 * @param string $slug Identifiant de la catégorie.
 * @return string
 */
function cw_universe_svg( $slug ) {
	$file = CW_DIR . '/assets/svg/' . sanitize_file_name( $slug ) . '.svg';
	if ( ! is_readable( $file ) ) {
		$file = CW_DIR . '/assets/svg/poeles-a-granules.svg';
	}
	$svg = trim( (string) file_get_contents( $file ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions -- fichier local du thème.
	return str_replace( '<svg ', '<svg aria-hidden="true" focusable="false" ', $svg );
}

/**
 * Photo d'un univers sur l'accueil : l'image de la catégorie choisie dans WordPress
 * (Produits > Catégories > Miniature), sinon la photo du premier produit qui en a une.
 *
 * @param string       $slug     Identifiant de la catégorie.
 * @param WC_Product[] $products Produits de la catégorie, dans l'ordre d'affichage.
 * @return int Identifiant de l'image, 0 s'il n'y en a pas.
 */
function cw_universe_image_id( $slug, $products ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		$thumbnail = absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
		if ( $thumbnail ) {
			return $thumbnail;
		}
	}
	foreach ( $products as $product ) {
		if ( $product->get_image_id() ) {
			return (int) $product->get_image_id();
		}
	}
	return 0;
}

/**
 * Adresse de la page d'une catégorie (ou de la boutique si elle n'existe pas encore).
 *
 * @param string $slug Identifiant de la catégorie.
 * @return string
 */
function cw_category_url( $slug ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

/**
 * Puissance d'un produit en kW (nombre), lue dans l'attribut « Puissance ».
 *
 * @param WC_Product $product Produit.
 * @return float 0 si inconnue.
 */
function cw_product_kw( $product ) {
	$raw = $product->get_attribute( 'pa_puissance' );
	if ( '' === $raw ) {
		$raw = $product->get_attribute( 'puissance' );
	}
	if ( preg_match( '/\d+(?:[.,]\d+)?/', (string) $raw, $m ) ) {
		return (float) str_replace( ',', '.', $m[0] );
	}
	return 0.0;
}

/**
 * Puissance mise en forme à la française, par exemple « 11,6 kW ».
 *
 * @param float $kw Puissance.
 * @return string
 */
function cw_format_kw( $kw ) {
	$decimals = floor( $kw ) == $kw ? 0 : 1; // phpcs:ignore Universal.Operators.StrictComparisons
	/* translators: %s: puissance en kilowatts. */
	return sprintf( __( '%s kW', 'chaud-winter' ), number_format_i18n( $kw, $decimals ) );
}

/**
 * Marque du produit (taxonomie Marques de WooCommerce, sinon attribut « Marque »).
 *
 * @param WC_Product $product Produit.
 * @return string
 */
function cw_product_brand( $product ) {
	$id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
	if ( taxonomy_exists( 'product_brand' ) ) {
		$terms = get_the_terms( $id, 'product_brand' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			return $terms[0]->name;
		}
	}
	return (string) $product->get_attribute( 'pa_marque' );
}

/**
 * Univers d'un produit (pour choisir l'illustration provisoire).
 *
 * @param WC_Product $product Produit.
 * @return string
 */
function cw_product_universe( $product ) {
	foreach ( array_keys( cw_universes() ) as $slug ) {
		if ( has_term( $slug, 'product_cat', $product->get_id() ) ) {
			return $slug;
		}
	}
	return 'poeles-a-granules';
}

/**
 * Produits d'une catégorie, dans l'ordre choisi dans WooCommerce (puis par nom).
 *
 * @param string $slug Identifiant de la catégorie.
 * @return WC_Product[]
 */
function cw_category_products( $slug ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}
	return wc_get_products(
		array(
			'status'     => 'publish',
			'visibility' => 'catalog',
			'category'   => array( $slug ),
			'limit'      => -1,
			'orderby'    => 'menu_order title',
			'order'      => 'ASC',
		)
	);
}

/**
 * Affiche une carte produit.
 *
 * @param WC_Product $product Produit.
 * @param array      $args    heading (h2 ou h3), class (classes en plus).
 */
function cw_product_card( $product, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'heading' => 'h3',
			'class'   => '',
		)
	);
	get_template_part( 'template-parts/product-card', null, array_merge( $args, array( 'product' => $product ) ) );
}

/**
 * Bouton « Ajouter au panier » d'une carte : ajout direct pour un produit simple
 * disponible, sinon lien vers la fiche produit.
 *
 * @param WC_Product $product Produit.
 * @return string
 */
function cw_add_to_cart_button( $product ) {
	$name = $product->get_name();

	if ( $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) {
		return sprintf(
			'<a href="%1$s" data-quantity="1" data-product_id="%2$d" data-product_sku="%3$s" class="cw-btn cw-btn-primary cw-btn-block add_to_cart_button ajax_add_to_cart" aria-label="%4$s" rel="nofollow">%5$s</a>',
			esc_url( $product->add_to_cart_url() ),
			(int) $product->get_id(),
			esc_attr( $product->get_sku() ),
			/* translators: %s: nom du produit. */
			esc_attr( sprintf( __( 'Ajouter « %s » au panier', 'chaud-winter' ), $name ) ),
			esc_html__( 'Ajouter au panier', 'chaud-winter' )
		);
	}

	$label = $product->is_in_stock() ? __( 'Voir le produit', 'chaud-winter' ) : __( 'Indisponible, voir le produit', 'chaud-winter' );
	return sprintf(
		'<a href="%1$s" class="cw-btn cw-btn-primary cw-btn-block">%2$s</a>',
		esc_url( $product->get_permalink() ),
		esc_html( $label )
	);
}
