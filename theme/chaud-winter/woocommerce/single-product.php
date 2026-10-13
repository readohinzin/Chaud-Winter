<?php
/**
 * Fiche produit (remplace le modèle de WooCommerce, sans barre latérale).
 *
 * @package chaud-winter
 * @version 1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>

<main id="main" class="cw-shop cw-single">
	<?php
	while ( have_posts() ) {
		the_post();
		wc_get_template_part( 'content', 'single-product' );
	}
	?>
</main>

<?php
get_footer( 'shop' );
