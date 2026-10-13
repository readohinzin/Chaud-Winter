<?php
/**
 * WooCommerce : pages catégories, filtres puissance et prix, fiche produit.
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Vrai sur les pages de la boutique (catégories, marques, boutique) et les fiches produits.
 *
 * @return bool
 */
function cw_is_shop_page() {
	return function_exists( 'is_woocommerce' ) && is_woocommerce();
}

/**
 * Styles de la boutique.
 */
function cw_shop_assets() {
	if ( cw_is_shop_page() ) {
		wp_enqueue_style( 'cw-shop', CW_URI . '/assets/css/shop.css', array( 'cw-theme' ), CW_VERSION );
		wp_enqueue_script( 'cw-shop', CW_URI . '/assets/js/shop.js', array(), CW_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
}
add_action( 'wp_enqueue_scripts', 'cw_shop_assets', 30 );

/**
 * Les pages de la boutique sont en pleine largeur, comme l'accueil.
 *
 * @param array $classes Classes du body.
 * @return array
 */
function cw_shop_body_class( $classes ) {
	if ( cw_is_shop_page() ) {
		$classes[] = 'cw-full-width';
	}
	return $classes;
}
add_filter( 'body_class', 'cw_shop_body_class' );

/**
 * 24 produits par page.
 *
 * @return int
 */
function cw_products_per_page() {
	return 24;
}
add_filter( 'loop_shop_per_page', 'cw_products_per_page' );

/* -------------------------------------------------------------------------- */
/* Filtres puissance (kW) et prix                                             */
/* -------------------------------------------------------------------------- */

/**
 * Valeurs des filtres lues dans l'adresse de la page.
 * Le prix utilise min_price et max_price, compris nativement par WooCommerce.
 *
 * @return array kw_min, kw_max, min_price, max_price (nombre ou null).
 */
function cw_filter_values() {
	$values = array();
	foreach ( array( 'kw_min', 'kw_max', 'min_price', 'max_price' ) as $key ) {
		$raw = isset( $_GET[ $key ] ) ? wc_clean( wp_unslash( $_GET[ $key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification -- simple filtre d'affichage.
		$raw = str_replace( array( ',', ' ' ), array( '.', '' ), (string) $raw );
		$values[ $key ] = is_numeric( $raw ) && (float) $raw >= 0 ? (float) $raw : null;
	}
	return $values;
}

/**
 * Nombre de filtres actifs.
 *
 * @return int
 */
function cw_active_filters_count() {
	$values = cw_filter_values();
	$count  = 0;
	if ( null !== $values['kw_min'] || null !== $values['kw_max'] ) {
		++$count;
	}
	if ( null !== $values['min_price'] || null !== $values['max_price'] ) {
		++$count;
	}
	return $count;
}

/**
 * Filtre par puissance : on garde les produits dont l'attribut « Puissance »
 * (par exemple « 11,6 kW ») est dans la fourchette demandée.
 *
 * @param WP_Query $query Requête.
 */
function cw_filter_by_power( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! ( $query->is_post_type_archive( 'product' ) || $query->is_tax( get_object_taxonomies( 'product' ) ) ) ) {
		return;
	}

	$values = cw_filter_values();
	$min    = $values['kw_min'];
	$max    = $values['kw_max'];
	if ( null === $min && null === $max ) {
		return;
	}
	if ( null !== $min && null !== $max && $min > $max ) {
		list( $min, $max ) = array( $max, $min );
	}

	$ids = array();
	if ( taxonomy_exists( 'pa_puissance' ) ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'pa_puissance',
				'hide_empty' => false,
			)
		);
		foreach ( is_wp_error( $terms ) ? array() : $terms as $term ) {
			if ( ! preg_match( '/\d+(?:[.,]\d+)?/', $term->name, $m ) ) {
				continue;
			}
			$kw = (float) str_replace( ',', '.', $m[0] );
			if ( ( null === $min || $kw >= $min ) && ( null === $max || $kw <= $max ) ) {
				$ids[] = (int) $term->term_id;
			}
		}
	}

	$tax_query   = (array) $query->get( 'tax_query' );
	$tax_query[] = array(
		'taxonomy' => 'pa_puissance',
		'field'    => 'term_id',
		'terms'    => $ids ? $ids : array( 0 ), // Aucune puissance dans la fourchette : aucun résultat.
		'operator' => 'IN',
	);
	$query->set( 'tax_query', $tax_query );
}
add_action( 'pre_get_posts', 'cw_filter_by_power', 20 );

/**
 * Adresse de la page actuelle de la boutique, sans filtre ni pagination.
 *
 * @return string
 */
function cw_archive_base_url() {
	if ( is_product_taxonomy() ) {
		$link = get_term_link( get_queried_object() );
		if ( ! is_wp_error( $link ) ) {
			return $link;
		}
	}
	return wc_get_page_permalink( 'shop' );
}

/**
 * Formulaire de filtres (puissance et prix). Fonctionne sans JavaScript.
 */
function cw_filters_form() {
	$values = cw_filter_values();
	$fmt    = function ( $v ) {
		return null === $v ? '' : wc_format_localized_decimal( $v );
	};
	$order  = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$active = cw_active_filters_count();
	?>
	<form class="cw-filters" id="cw-filters" method="get" action="<?php echo esc_url( cw_archive_base_url() ); ?>">
		<fieldset class="cw-filter-group">
			<legend><?php esc_html_e( 'Puissance (kW)', 'chaud-winter' ); ?></legend>
			<div class="cw-range">
				<label><span><?php esc_html_e( 'De', 'chaud-winter' ); ?></span><input type="text" inputmode="decimal" name="kw_min" value="<?php echo esc_attr( $fmt( $values['kw_min'] ) ); ?>" placeholder="<?php esc_attr_e( 'min.', 'chaud-winter' ); ?>" autocomplete="off"></label>
				<label><span><?php esc_html_e( 'à', 'chaud-winter' ); ?></span><input type="text" inputmode="decimal" name="kw_max" value="<?php echo esc_attr( $fmt( $values['kw_max'] ) ); ?>" placeholder="<?php esc_attr_e( 'max.', 'chaud-winter' ); ?>" autocomplete="off"></label>
			</div>
		</fieldset>
		<fieldset class="cw-filter-group">
			<legend><?php esc_html_e( 'Prix TTC (€)', 'chaud-winter' ); ?></legend>
			<div class="cw-range">
				<label><span><?php esc_html_e( 'De', 'chaud-winter' ); ?></span><input type="text" inputmode="numeric" name="min_price" value="<?php echo esc_attr( $fmt( $values['min_price'] ) ); ?>" placeholder="<?php esc_attr_e( 'min.', 'chaud-winter' ); ?>" autocomplete="off"></label>
				<label><span><?php esc_html_e( 'à', 'chaud-winter' ); ?></span><input type="text" inputmode="numeric" name="max_price" value="<?php echo esc_attr( $fmt( $values['max_price'] ) ); ?>" placeholder="<?php esc_attr_e( 'max.', 'chaud-winter' ); ?>" autocomplete="off"></label>
			</div>
		</fieldset>
		<?php if ( $order ) : ?>
			<input type="hidden" name="orderby" value="<?php echo esc_attr( $order ); ?>">
		<?php endif; ?>
		<div class="cw-filter-actions">
			<button type="submit" class="cw-btn cw-btn-primary"><?php esc_html_e( 'Filtrer', 'chaud-winter' ); ?></button>
			<?php if ( $active ) : ?>
				<a class="cw-btn cw-btn-ghost" href="<?php echo esc_url( $order ? add_query_arg( 'orderby', $order, cw_archive_base_url() ) : cw_archive_base_url() ); ?>"><?php esc_html_e( 'Effacer les filtres', 'chaud-winter' ); ?></a>
			<?php endif; ?>
		</div>
	</form>
	<?php
}

/* -------------------------------------------------------------------------- */
/* Image provisoire                                                           */
/* -------------------------------------------------------------------------- */

/**
 * Tant qu'un produit n'a pas de photo : illustration du thème + « Photo à venir ».
 *
 * @param string $html Image provisoire de WooCommerce.
 * @return string
 */
function cw_placeholder_img( $html ) {
	global $product;
	$slug = $product instanceof WC_Product ? cw_product_universe( $product ) : 'poeles-a-granules';
	return '<span class="cw-card-placeholder">' . cw_universe_svg( $slug ) . '<span class="cw-photo-tag">' . esc_html__( 'Photo à venir', 'chaud-winter' ) . '</span></span>';
}
add_filter( 'woocommerce_placeholder_img', 'cw_placeholder_img' );

/**
 * Même image provisoire dans la galerie de la fiche produit.
 *
 * @param string $html         Image de la galerie.
 * @param int    $thumbnail_id Identifiant de l'image (0 s'il n'y en a pas).
 * @return string
 */
function cw_gallery_placeholder( $html, $thumbnail_id ) {
	if ( $thumbnail_id ) {
		return $html;
	}
	return '<div class="woocommerce-product-gallery__image--placeholder">' . cw_placeholder_img( '' ) . '</div>';
}
add_filter( 'woocommerce_single_product_image_thumbnail_html', 'cw_gallery_placeholder', 10, 2 );

/* -------------------------------------------------------------------------- */
/* Fiche produit                                                              */
/* -------------------------------------------------------------------------- */

/**
 * Réorganise le résumé de la fiche produit :
 * marque, nom, puissance et catégorie, description courte, prix TTC, panier, réassurance.
 */
function cw_single_product_layout() {
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

	add_action( 'woocommerce_single_product_summary', 'cw_single_brand', 4 );
	add_action( 'woocommerce_single_product_summary', 'cw_single_badges', 6 );
	add_action( 'woocommerce_single_product_summary', 'cw_single_price', 25 );
	add_action( 'woocommerce_single_product_summary', 'cw_single_reassurance', 35 );
}
add_action( 'wp', 'cw_single_product_layout' );

/**
 * Marque au-dessus du nom.
 */
function cw_single_brand() {
	global $product;
	$brand = cw_product_brand( $product );
	if ( $brand ) {
		echo '<div class="cw-eyebrow cw-single-brand">' . esc_html( $brand ) . '</div>';
	}
}

/**
 * Pastilles puissance et catégorie sous le nom.
 */
function cw_single_badges() {
	global $product;
	$kw       = cw_product_kw( $product );
	$universe = cw_product_universe( $product );
	$labels   = cw_universes();
	echo '<div class="cw-badges">';
	if ( $kw ) {
		echo '<span class="cw-badge cw-badge-kw">' . esc_html( cw_format_kw( $kw ) ) . '</span>';
	}
	if ( has_term( $universe, 'product_cat', $product->get_id() ) ) {
		printf( '<a class="cw-badge" href="%1$s">%2$s</a>', esc_url( cw_category_url( $universe ) ), esc_html( $labels[ $universe ]['label'] ) );
	}
	echo '</div>';
}

/**
 * Prix TTC.
 */
function cw_single_price() {
	global $product;
	?>
	<div class="cw-single-price">
		<span class="cw-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
		<span class="cw-ttc"><?php esc_html_e( 'TTC', 'chaud-winter' ); ?></span>
	</div>
	<?php
}

/**
 * Rappels utiles sous le bouton : livraison, paiement, conseil.
 */
function cw_single_reassurance() {
	?>
	<ul class="cw-single-assure">
		<li><?php esc_html_e( 'Livraison en France, Belgique et Allemagne', 'chaud-winter' ); ?></li>
		<li><?php esc_html_e( 'Paiement par virement bancaire : les coordonnées s\'affichent après la commande', 'chaud-winter' ); ?></li>
		<li>
			<?php
			printf(
				/* translators: %s: lien « être rappelé ». */
				esc_html__( 'Un doute sur la puissance ? %s', 'chaud-winter' ),
				'<a href="' . esc_url( cw_callback_url() ) . '">' . esc_html__( 'Être rappelé par un conseiller', 'chaud-winter' ) . '</a>'
			);
			?>
		</li>
	</ul>
	<?php
}

/**
 * Onglets de la fiche : « Informations complémentaires » devient « Caractéristiques ».
 *
 * @param array $tabs Onglets.
 * @return array
 */
function cw_product_tabs( $tabs ) {
	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = __( 'Caractéristiques', 'chaud-winter' );
	}
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'cw_product_tabs', 20 );

/**
 * Produits similaires : 4, de la même catégorie.
 *
 * @param array $args Arguments.
 * @return array
 */
function cw_related_args( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'cw_related_args' );

/**
 * Titres des sections « produits similaires » et « vous aimerez aussi ».
 *
 * @return string
 */
function cw_related_heading() {
	return __( 'Dans la même catégorie', 'chaud-winter' );
}
add_filter( 'woocommerce_product_related_products_heading', 'cw_related_heading' );

/**
 * Fil d'Ariane : séparateur discret et lien d'accueil en français.
 *
 * @param array $defaults Réglages.
 * @return array
 */
function cw_breadcrumb_defaults( $defaults ) {
	$defaults['delimiter']   = '<span class="cw-bc-sep" aria-hidden="true">/</span>';
	$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb cw-breadcrumb" aria-label="' . esc_attr__( 'Fil d\'Ariane', 'chaud-winter' ) . '">';
	$defaults['home']        = __( 'Accueil', 'chaud-winter' );
	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'cw_breadcrumb_defaults' );

/* -------------------------------------------------------------------------- */
/* Import CSV : colonne « Brands » (marques)                                  */
/* -------------------------------------------------------------------------- */

/**
 * L'importateur de WooCommerce ne lit pas encore la colonne des marques : on l'ajoute.
 *
 * @param array $options Colonnes proposées à l'association.
 * @return array
 */
function cw_import_brand_option( $options ) {
	$options['cw_brands'] = __( 'Marques', 'chaud-winter' );
	return $options;
}
add_filter( 'woocommerce_csv_product_import_mapping_options', 'cw_import_brand_option' );

/**
 * Association automatique des colonnes « Brands » et « Marques ».
 *
 * @param array $columns Nom de colonne => champ.
 * @return array
 */
function cw_import_brand_column( $columns ) {
	$columns['Brands']                          = 'cw_brands';
	$columns[ __( 'Marques', 'chaud-winter' ) ] = 'cw_brands';
	return $columns;
}
add_filter( 'woocommerce_csv_product_import_mapping_default_columns', 'cw_import_brand_column' );

/**
 * Rattache le produit importé à sa ou ses marques (créées si besoin).
 *
 * @param WC_Product $product Produit importé.
 * @param array      $data    Données de la ligne.
 */
function cw_import_brands( $product, $data ) {
	if ( empty( $data['cw_brands'] ) || ! taxonomy_exists( 'product_brand' ) ) {
		return;
	}
	$ids = array();
	foreach ( array_filter( array_map( 'trim', explode( ',', $data['cw_brands'] ) ) ) as $name ) {
		$term = term_exists( $name, 'product_brand' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'product_brand' );
		}
		if ( ! is_wp_error( $term ) ) {
			$ids[] = (int) $term['term_id'];
		}
	}
	if ( $ids ) {
		wp_set_object_terms( $product->get_id(), $ids, 'product_brand' );
	}
}
add_action( 'woocommerce_product_import_inserted_product_object', 'cw_import_brands', 10, 2 );
