<?php
/**
 * Chaud Winter : thème enfant de GeneratePress.
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

define( 'CW_VERSION', '0.9.5' );
define( 'CW_DIR', get_stylesheet_directory() );
define( 'CW_URI', get_stylesheet_directory_uri() );

require CW_DIR . '/inc/infos.php';
require CW_DIR . '/inc/template-tags.php';
require CW_DIR . '/inc/layout.php';
require CW_DIR . '/inc/products.php';
require CW_DIR . '/inc/pages.php';
require CW_DIR . '/inc/performance.php';
require CW_DIR . '/inc/woocommerce.php';
require CW_DIR . '/inc/checkout.php';
require CW_DIR . '/inc/setup.php';

/**
 * Traductions, menus et supports du thème.
 */
function cw_setup() {
	load_child_theme_textdomain( 'chaud-winter', CW_DIR . '/languages' );

	register_nav_menus(
		array(
			'cw-header'       => __( 'En-tête : menu principal', 'chaud-winter' ),
			'cw-footer-shop'  => __( 'Pied de page : produits', 'chaud-winter' ),
			'cw-footer-infos' => __( 'Pied de page : informations', 'chaud-winter' ),
		)
	);

	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'cw_setup', 20 );

/**
 * Feuilles de style et scripts.
 */
function cw_assets() {
	// Chargé après les styles de GeneratePress pour pouvoir les remplacer.
	$deps = array();
	if ( wp_style_is( 'generate-style', 'registered' ) ) {
		$deps[] = 'generate-style';
	}
	wp_enqueue_style( 'cw-theme', CW_URI . '/assets/css/theme.css', $deps, CW_VERSION );

	wp_enqueue_script( 'cw-theme', CW_URI . '/assets/js/theme.js', array(), CW_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );

	// Met à jour le compteur du panier sans recharger la page.
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_script( 'wc-cart-fragments' );
	}

	if ( is_front_page() ) {
		wp_enqueue_style( 'cw-home', CW_URI . '/assets/css/home.css', array( 'cw-theme' ), CW_VERSION );
		wp_enqueue_script( 'cw-home', CW_URI . '/assets/js/home.js', array(), CW_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
		wp_localize_script(
			'cw-home',
			'cwHome',
			array(
				/* translators: %d: surface en m². */
				'm2'        => __( '%d m²', 'chaud-winter' ),
				/* translators: %d: puissance estimée en kW. */
				'about'     => __( 'Environ <b>%d kW</b>', 'chaud-winter' ),
				/* translators: 1: puissance basse, 2: puissance haute. */
				'range'     => __( 'Gamme conseillée : de %1$d à %2$d kW.', 'chaud-winter' ),
				/* translators: 1: puissance basse, 2: puissance haute. */
				'go'        => __( 'Voir les appareils de %1$d à %2$d kW', 'chaud-winter' ),
				/* translators: 1: nombre d'appareils, 2: puissance basse, 3: puissance haute. */
				'found1'    => __( '%1$d appareil entre %2$d et %3$d kW', 'chaud-winter' ),
				/* translators: 1: nombre d'appareils, 2: puissance basse, 3: puissance haute. */
				'foundN'    => __( '%1$d appareils entre %2$d et %3$d kW', 'chaud-winter' ),
				/* translators: 1: puissance basse, 2: puissance haute. */
				'foundNone' => __( 'Aucun appareil entre %1$d et %2$d kW : un conseiller peut vous orienter.', 'chaud-winter' ),
			)
		);
		if ( class_exists( 'WooCommerce' ) ) {
			wp_enqueue_script( 'wc-add-to-cart' );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'cw_assets', 20 );

/**
 * Accueil : pleine largeur, sans barre latérale.
 *
 * @param array $classes Classes du body.
 * @return array
 */
function cw_body_class( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'cw-full-width';
	}
	return $classes;
}
add_filter( 'body_class', 'cw_body_class' );

/**
 * Pas de barre latérale GeneratePress sur l'accueil.
 *
 * @param string $layout Mise en page choisie.
 * @return string
 */
function cw_sidebar_layout( $layout ) {
	return is_front_page() ? 'no-sidebar' : $layout;
}
add_filter( 'generate_sidebar_layout', 'cw_sidebar_layout' );

/**
 * Précharge les deux polices les plus utilisées pour éviter le clignotement du texte.
 */
function cw_preload_fonts() {
	foreach ( array( 'poppins-latin-400-normal', 'playfair-display-latin-700-normal' ) as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( CW_URI . '/assets/fonts/' . $font . '.woff2' )
		);
	}
}
add_action( 'wp_head', 'cw_preload_fonts', 1 );

/**
 * Ajoute la classe « js » au plus tôt : les animations d'apparition ne masquent
 * le contenu que si le JavaScript fonctionne.
 */
function cw_js_class() {
	echo "<script>document.documentElement.classList.add('js')</script>\n";
}
add_action( 'wp_head', 'cw_js_class', 0 );

/**
 * Couleur de la barre du navigateur sur téléphone.
 */
function cw_theme_color() {
	echo '<meta name="theme-color" content="#101216">' . "\n";
}
add_action( 'wp_head', 'cw_theme_color', 2 );
