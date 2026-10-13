<?php
/**
 * Vitesse, cookies et référencement.
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------- */
/* Cookies : seulement ceux qui sont nécessaires                              */
/* -------------------------------------------------------------------------- */

/**
 * Désactive le suivi de l'origine des commandes de WooCommerce.
 *
 * Il dépose des cookies de traçage (sbjs_*) dès la première visite : en France, ils exigent
 * l'accord du visiteur. La page « Cookies » du site annonce que seuls les cookies nécessaires
 * sont utilisés : on garde cette promesse vraie par défaut. Pour le réactiver, il faudra
 * d'abord installer un bandeau de consentement (Complianz) et mettre la page à jour.
 *
 * @return string
 */
function cw_disable_order_attribution() {
	return 'no';
}
add_filter( 'pre_option_woocommerce_feature_order_attribution_enabled', 'cw_disable_order_attribution' );

/* -------------------------------------------------------------------------- */
/* Vitesse : moins de fichiers à charger                                      */
/* -------------------------------------------------------------------------- */

/**
 * Retire ce dont le site n'a pas besoin.
 */
function cw_trim_assets() {
	// Emojis : un script de 22 Ko inutile (les emojis s'affichent déjà sans lui).
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'cw_trim_assets' );

/**
 * jQuery Migrate (13 Ko) ne sert qu'aux anciens plugins : WooCommerce n'en a pas besoin.
 * jQuery lui-même est chargé en différé.
 *
 * @param WP_Scripts $scripts Scripts enregistrés.
 */
function cw_remove_jquery_migrate( $scripts ) {
	if ( is_admin() ) {
		return;
	}
	if ( isset( $scripts->registered['jquery'] ) ) {
		$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
	}
	// jQuery chargé en différé : il ne bloque plus l'affichage (les scripts de WooCommerce le sont déjà).
	if ( isset( $scripts->registered['jquery-core'] ) ) {
		$scripts->add_data( 'jquery-core', 'strategy', 'defer' );
	}
}
add_action( 'wp_default_scripts', 'cw_remove_jquery_migrate' );

/**
 * Fichiers de style inutiles : la feuille du thème enfant (vide, seulement un en-tête)
 * et celle des commentaires (le site n'en affiche pas).
 */
function cw_dequeue_unused_styles() {
	wp_dequeue_style( 'generate-child' );
	wp_dequeue_style( 'generate-comments' );
	wp_dequeue_script( 'comment-reply' );
}
add_action( 'wp_enqueue_scripts', 'cw_dequeue_unused_styles', 100 );

/**
 * Les feuilles de style de WooCommerce (120 Ko) ne servent que sur ses propres pages
 * (boutique, produit, panier, commande, compte). Le thème habille tout le reste lui-même.
 *
 * @param array $styles Feuilles de style de WooCommerce.
 * @return array
 */
function cw_woocommerce_styles( $styles ) {
	if ( is_admin() ) {
		return $styles;
	}
	$on_shop = ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) || is_cart() || is_checkout() || is_account_page();
	return $on_shop ? $styles : array();
}
add_filter( 'woocommerce_enqueue_styles', 'cw_woocommerce_styles' );

/* -------------------------------------------------------------------------- */
/* Référencement : description affichée dans Google                           */
/* -------------------------------------------------------------------------- */

/**
 * Texte court (155 caractères au plus) pour la description de la page dans les résultats de recherche.
 *
 * @return string
 */
function cw_meta_description() {
	$free = __( 'Livraison offerte en France métropolitaine.', 'chaud-winter' );
	$desc = '';

	if ( is_front_page() ) {
		$desc = __( 'Poêles à granulés, poêles à bois et chaudières. Conseil d\'un spécialiste, paiement par virement, livraison offerte en France métropolitaine.', 'chaud-winter' );
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_the_ID() );
		if ( $product ) {
			$kw    = function_exists( 'cw_product_kw' ) ? cw_product_kw( $product ) : 0;
			$brand = function_exists( 'cw_product_brand' ) ? cw_product_brand( $product ) : '';
			$parts = array_filter(
				array(
					trim( $brand . ' ' . $product->get_name() ),
					wp_strip_all_tags( $product->get_short_description() ),
					$kw ? cw_format_kw( $kw ) : '',
				)
			);
			$desc  = implode( ', ', $parts ) . '. ' . $free;
		}
	} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$term = get_queried_object();
		$desc = $term && term_description( $term->term_id, 'product_cat' )
			? wp_strip_all_tags( term_description( $term->term_id, 'product_cat' ) )
			/* translators: %s: nom de la catégorie. */
			: sprintf( __( '%s : filtrez par puissance et par prix. Paiement par virement.', 'chaud-winter' ), $term ? $term->name : '' ) . ' ' . $free;
	} elseif ( is_singular() ) {
		$post = get_post();
		$desc = $post ? ( has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) ) : '';
	}

	$desc = trim( preg_replace( '/\s+/u', ' ', (string) $desc ) );
	if ( mb_strlen( $desc ) > 155 ) {
		$desc = rtrim( mb_substr( $desc, 0, 152 ), " ,.;:-" ) . '…';
	}
	return $desc;
}

/**
 * Ajoute la balise de description, sauf sur le panier, la commande et le compte (non indexés).
 */
function cw_print_meta_description() {
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) {
		return;
	}
	$desc = cw_meta_description();
	if ( '' !== $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'cw_print_meta_description', 3 );
