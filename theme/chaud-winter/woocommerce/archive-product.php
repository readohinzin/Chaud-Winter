<?php
/**
 * Pages catégories, marques et boutique (remplace le modèle de WooCommerce).
 *
 * Bandeau sombre (titre, description), puis section ivoire avec filtres et grille.
 *
 * @package chaud-winter
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$cw_term     = is_product_taxonomy() ? get_queried_object() : null;
$cw_universe = ( $cw_term && 'product_cat' === $cw_term->taxonomy && array_key_exists( $cw_term->slug, cw_universes() ) ) ? $cw_term->slug : '';
$cw_active   = cw_active_filters_count();
?>

<main id="main" class="cw-shop">

	<header class="cw-archive-head">
		<div class="cw-wrap cw-archive-head-inner">
			<div>
				<?php woocommerce_breadcrumb(); ?>
				<h1 class="cw-archive-title"><?php woocommerce_page_title(); ?></h1>
				<?php
				/**
				 * Description de la catégorie (modifiable dans Produits > Catégories).
				 */
				do_action( 'woocommerce_archive_description' );
				?>
			</div>
			<?php if ( $cw_universe ) : ?>
				<div class="cw-archive-art" aria-hidden="true"><span class="cw-halo"></span><?php echo cw_universe_svg( $cw_universe ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>
		</div>
	</header>

	<section class="cw-light cw-archive-body">
		<div class="cw-wrap">

			<div class="cw-toolbar">
				<button type="button" class="cw-btn cw-btn-ghost cw-filter-toggle" aria-expanded="<?php echo $cw_active ? 'true' : 'false'; ?>" aria-controls="cw-filters" hidden>
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
					<?php esc_html_e( 'Filtrer', 'chaud-winter' ); ?>
					<?php if ( $cw_active ) : ?>
						<span class="cw-cart-count"><?php echo (int) $cw_active; ?></span>
					<?php endif; ?>
				</button>
				<?php
				if ( woocommerce_product_loop() ) {
					woocommerce_result_count();
					woocommerce_catalog_ordering();
				}
				?>
			</div>

			<?php cw_filters_form(); ?>

			<?php
			// Messages de WooCommerce (par exemple « produit ajouté au panier »).
			woocommerce_output_all_notices();

			if ( woocommerce_product_loop() ) {
				woocommerce_product_loop_start();

				if ( wc_get_loop_prop( 'total' ) ) {
					while ( have_posts() ) {
						the_post();
						do_action( 'woocommerce_shop_loop' );
						wc_get_template_part( 'content', 'product' );
					}
				}

				woocommerce_product_loop_end();
				woocommerce_pagination();
			} else {
				?>
				<div class="cw-no-results">
					<p>
						<?php
						echo $cw_active
							? esc_html__( 'Aucun appareil ne correspond à ces filtres.', 'chaud-winter' )
							: esc_html__( 'Aucun produit dans cette catégorie pour le moment.', 'chaud-winter' );
						?>
					</p>
					<p>
						<?php if ( $cw_active ) : ?>
							<a class="cw-btn cw-btn-ghost" href="<?php echo esc_url( cw_archive_base_url() ); ?>"><?php esc_html_e( 'Effacer les filtres', 'chaud-winter' ); ?></a>
						<?php endif; ?>
						<a class="cw-btn cw-btn-primary" href="<?php echo esc_url( cw_callback_url() ); ?>"><?php esc_html_e( 'Être rappelé par un conseiller', 'chaud-winter' ); ?></a>
					</p>
				</div>
				<?php
			}
			?>
		</div>
	</section>

</main>

<?php
get_footer( 'shop' );
