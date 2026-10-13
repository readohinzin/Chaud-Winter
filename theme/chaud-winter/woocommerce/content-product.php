<?php
/**
 * Un produit dans une liste (catégories, produits similaires) : la carte du thème.
 *
 * @package chaud-winter
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

// Titre h2 sous le titre d'une catégorie, h3 sous « Dans la même catégorie » d'une fiche produit.
$cw_heading = is_product() ? 'h3' : 'h2';
?>
<li <?php wc_product_class( 'cw-grid-item', $product ); ?>>
	<?php cw_product_card( $product, array( 'heading' => $cw_heading ) ); ?>
</li>
