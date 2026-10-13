<?php
/**
 * Pages de contenu : Contact, pages légales, articles.
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pages créées par la configuration, dans l'ordre du pied de page.
 * La clé est l'adresse de la page (slug), le texte de base est dans contenus/{slug}.html.
 *
 * @return array
 */
function cw_page_definitions() {
	return array(
		'contact'                       => __( 'Contact', 'chaud-winter' ),
		'livraison-et-retours'          => __( 'Livraison et retours', 'chaud-winter' ),
		'faq'                           => __( 'Questions fréquentes', 'chaud-winter' ),
		'paiement-par-virement'         => __( 'Paiement par virement', 'chaud-winter' ),
		'conditions-generales-de-vente' => __( 'Conditions générales de vente', 'chaud-winter' ),
		'mentions-legales'              => __( 'Mentions légales', 'chaud-winter' ),
		'politique-de-confidentialite'  => __( 'Politique de confidentialité', 'chaud-winter' ),
		'cookies'                       => __( 'Cookies', 'chaud-winter' ),
		'impressum'                     => 'Impressum',
	);
}

/**
 * Vrai sur une page ou un article affiché sur fond clair (hors accueil et boutique).
 *
 * @return bool
 */
function cw_is_light_page() {
	if ( is_front_page() || ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) ) {
		return false;
	}
	return is_singular() || is_home() || is_archive() || is_search() || is_404();
}

/**
 * Classe sur le body et styles des pages claires.
 *
 * @param array $classes Classes du body.
 * @return array
 */
function cw_pages_body_class( $classes ) {
	if ( cw_is_light_page() ) {
		$classes[] = 'cw-light-page';
	}
	return $classes;
}
add_filter( 'body_class', 'cw_pages_body_class' );

/**
 * Pas de barre latérale sur les pages claires.
 *
 * @param string $layout Mise en page.
 * @return string
 */
function cw_pages_sidebar_layout( $layout ) {
	return cw_is_light_page() ? 'no-sidebar' : $layout;
}
add_filter( 'generate_sidebar_layout', 'cw_pages_sidebar_layout', 30 );

/**
 * Styles des pages claires. Le formulaire de contact ne charge ses fichiers que sur la page Contact.
 */
function cw_pages_assets() {
	if ( cw_is_light_page() ) {
		wp_enqueue_style( 'cw-pages', CW_URI . '/assets/css/pages.css', array( 'cw-theme' ), CW_VERSION );
	}
	if ( is_page( 'contact' ) ) {
		if ( function_exists( 'wpcf7_enqueue_scripts' ) ) {
			wpcf7_enqueue_scripts();
		}
		if ( function_exists( 'wpcf7_enqueue_styles' ) ) {
			wpcf7_enqueue_styles();
		}
	}
}
add_action( 'wp_enqueue_scripts', 'cw_pages_assets', 30 );

// Contact Form 7 : pas de fichiers chargés sur les autres pages (site plus rapide).
add_filter( 'wpcf7_load_js', '__return_false' );
add_filter( 'wpcf7_load_css', '__return_false' );

/**
 * Rappel dans le tableau de bord : repères [ENTRE CROCHETS] restant dans les pages.
 *
 * @param array $missing Informations manquantes.
 * @return array
 */
function cw_missing_page_infos( $missing ) {
	$pages = get_posts(
		array(
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'numberposts'   => -1,
			'post_name__in' => array_keys( cw_page_definitions() ),
		)
	);
	$with = array();
	foreach ( $pages as $page ) {
		// Repères commençant par des majuscules ([SIRET], [À VÉRIFIER : …]), pas les codes courts [contact-form-7 …].
		if ( preg_match( '/\[[A-ZÀ-ÖØ-Ý][A-ZÀ-ÖØ-Ý0-9°\'’ \-][^\]]*\]/u', $page->post_content ) ) {
			$with[] = $page->post_title;
		}
	}
	if ( $with ) {
		/* translators: %s: liste de pages. */
		$missing[] = sprintf( __( 'Repères à remplacer dans les pages : %s', 'chaud-winter' ), implode( ', ', $with ) );
	}
	return $missing;
}
add_filter( 'cw_missing_infos', 'cw_missing_page_infos' );
