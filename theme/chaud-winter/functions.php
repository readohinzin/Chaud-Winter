<?php
/**
 * Chaud Winter : thème enfant de GeneratePress.
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

define( 'CW_VERSION', '0.1.0' );
define( 'CW_DIR', get_stylesheet_directory() );
define( 'CW_URI', get_stylesheet_directory_uri() );

require CW_DIR . '/inc/infos.php';
require CW_DIR . '/inc/template-tags.php';
require CW_DIR . '/inc/layout.php';

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
	wp_enqueue_style( 'cw-fonts', CW_URI . '/assets/css/fonts.css', array(), CW_VERSION );
	// Chargé après les styles de GeneratePress pour pouvoir les remplacer.
	$deps = array( 'cw-fonts' );
	if ( wp_style_is( 'generate-style', 'registered' ) ) {
		$deps[] = 'generate-style';
	}
	wp_enqueue_style( 'cw-theme', CW_URI . '/assets/css/theme.css', $deps, CW_VERSION );

	wp_enqueue_script( 'cw-theme', CW_URI . '/assets/js/theme.js', array(), CW_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );

	// Met à jour le compteur du panier sans recharger la page.
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_script( 'wc-cart-fragments' );
	}
}
add_action( 'wp_enqueue_scripts', 'cw_assets', 20 );

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
