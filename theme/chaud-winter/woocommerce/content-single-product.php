<?php
/**
 * Contenu de la fiche produit : partie sombre (photos et achat), puis partie ivoire
 * (description, caractéristiques, produits de la même catégorie).
 *
 * Les crochets (hooks) de WooCommerce sont conservés pour rester compatible.
 *
 * @package chaud-winter
 * @version 3.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'cw-product', $product ); ?>>

	<section class="cw-product-top">
		<div class="cw-wrap">
			<?php
			/**
			 * Messages de WooCommerce (par exemple « produit ajouté au panier »), dans la largeur du contenu.
			 */
			do_action( 'woocommerce_before_single_product' );

			woocommerce_breadcrumb();
			?>
			<div class="cw-product-grid">
				<div class="cw-product-media">
					<?php
					/**
					 * Pastille promo et galerie de photos.
					 */
					do_action( 'woocommerce_before_single_product_summary' );
					?>
				</div>

				<div class="summary entry-summary cw-product-summary">
					<?php
					/**
					 * Marque, nom, puissance, description courte, prix TTC, panier, réassurance.
					 */
					do_action( 'woocommerce_single_product_summary' );
					?>
				</div>
			</div>
		</div>
	</section>

	<section class="cw-light cw-product-more">
		<div class="cw-wrap">
			<?php
			/**
			 * Onglets (description, caractéristiques), produits liés et similaires.
			 */
			do_action( 'woocommerce_after_single_product_summary' );
			?>
		</div>
	</section>
</div>

<?php do_action( 'woocommerce_after_single_product' ); ?>
