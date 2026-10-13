<?php
/**
 * Carte produit : photo, puissance, marque, nom, description courte, prix TTC, bouton.
 *
 * @package chaud-winter
 *
 * @var array $args product (WC_Product), heading (h2 ou h3), class.
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'];
$heading = in_array( $args['heading'], array( 'h2', 'h3' ), true ) ? $args['heading'] : 'h3';
$kw      = cw_product_kw( $product );
$brand   = cw_product_brand( $product );
$link    = $product->get_permalink();
$desc    = wp_strip_all_tags( $product->get_short_description() );
?>
<article class="cw-card <?php echo esc_attr( $args['class'] ); ?>" data-kw="<?php echo esc_attr( $kw ? $kw : '' ); ?>">
	<a class="cw-card-img" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
		<?php if ( $product->get_image_id() ) : ?>
			<?php echo wp_get_attachment_image( $product->get_image_id(), 'woocommerce_thumbnail', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
		<?php else : ?>
			<span class="cw-card-placeholder">
				<?php echo cw_universe_svg( cw_product_universe( $product ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- SVG du thème. ?>
				<span class="cw-photo-tag"><?php esc_html_e( 'Photo à venir', 'chaud-winter' ); ?></span>
			</span>
		<?php endif; ?>
		<?php if ( $kw ) : ?>
			<span class="cw-kw"><?php echo esc_html( cw_format_kw( $kw ) ); ?></span>
		<?php endif; ?>
	</a>
	<div class="cw-card-body">
		<?php if ( $brand ) : ?>
			<div class="cw-brand"><?php echo esc_html( $brand ); ?></div>
		<?php endif; ?>
		<<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput -- h2 ou h3 vérifié plus haut. ?> class="cw-name"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></<?php echo $heading; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<?php if ( $desc ) : ?>
			<p class="cw-desc"><?php echo esc_html( $desc ); ?></p>
		<?php endif; ?>
		<div class="cw-price-row">
			<span class="cw-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			<span class="cw-ttc"><?php esc_html_e( 'TTC', 'chaud-winter' ); ?></span>
		</div>
		<?php echo cw_add_to_cart_button( $product ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans la fonction. ?>
		<a href="<?php echo esc_url( $link ); ?>" class="cw-more-link"><?php esc_html_e( 'Voir le détail', 'chaud-winter' ); ?><span class="screen-reader-text"> : <?php echo esc_html( $product->get_name() ); ?></span></a>
	</div>
</article>
