<?php
/**
 * Remplace l'en-tête et le pied de page de GeneratePress par ceux de la maquette.
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retire les éléments d'origine de GeneratePress.
 * Fait sur « wp » car le thème parent est chargé après le thème enfant.
 */
function cw_remove_parent_layout() {
	remove_action( 'generate_header', 'generate_construct_header' );
	remove_action( 'generate_before_header', 'generate_top_bar', 5 );
	remove_action( 'generate_before_header', 'generate_add_navigation_before_header', 5 );
	remove_action( 'generate_after_header', 'generate_add_navigation_after_header', 5 );
	remove_action( 'generate_after_header', 'generate_add_navigation_float_right', 5 );
	remove_action( 'generate_footer', 'generate_construct_footer' );
	remove_action( 'generate_footer', 'generate_construct_footer_widgets', 5 );
}
add_action( 'wp', 'cw_remove_parent_layout' );

// Pas de menu GeneratePress : le menu est dans notre en-tête.
add_filter( 'generate_navigation_location', '__return_empty_string' );

add_action( 'generate_header', 'cw_header' );
add_action( 'generate_footer', 'cw_footer' );

/**
 * Barre téléphone + en-tête collant.
 */
function cw_header() {
	?>
	<div class="cw-topbar">
		<div class="cw-wrap">
			<span><?php esc_html_e( 'Spécialiste du chauffage au bois et aux granulés', 'chaud-winter' ); ?></span>
			<span>
				<?php
				printf(
					/* translators: 1: numéro de téléphone, 2: horaires. */
					esc_html__( 'Conseil par téléphone : %1$s · %2$s', 'chaud-winter' ),
					cw_phone_html(), // phpcs:ignore WordPress.Security.EscapeOutput -- déjà échappé.
					esc_html( cw_info( 'hours' ) )
				);
				?>
			</span>
		</div>
	</div>

	<header class="cw-header" id="masthead">
		<div class="cw-wrap cw-header-inner">
			<?php cw_logo(); ?>

			<nav class="cw-nav" id="cw-nav" aria-label="<?php esc_attr_e( 'Navigation principale', 'chaud-winter' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'cw-header',
						'container'      => false,
						'menu_class'     => 'cw-nav-list',
						'depth'          => 1,
						'fallback_cb'    => 'cw_menu_fallback',
					)
				);
				?>
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<div class="cw-nav-search"><?php get_product_search_form(); ?></div>
				<?php endif; ?>
			</nav>

			<div class="cw-head-actions">
				<?php if ( class_exists( 'WooCommerce' ) ) : ?>
					<button class="cw-icon-btn cw-search-btn" type="button" aria-label="<?php esc_attr_e( 'Rechercher', 'chaud-winter' ); ?>" aria-expanded="false" aria-controls="cw-search">
						<?php echo cw_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</button>
				<?php endif; ?>
				<?php echo cw_cart_link(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<a href="<?php echo esc_url( cw_callback_url() ); ?>" class="cw-btn cw-btn-primary cw-head-cta"><?php esc_html_e( 'Être rappelé', 'chaud-winter' ); ?></a>
				<button class="cw-icon-btn cw-menu-btn" type="button" aria-label="<?php esc_attr_e( 'Menu', 'chaud-winter' ); ?>" aria-expanded="false" aria-controls="cw-nav">
					<span class="cw-ico-open"><?php echo cw_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="cw-ico-close"><?php echo cw_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
				</button>
			</div>

			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<div class="cw-search" id="cw-search" hidden>
					<?php get_product_search_form(); ?>
				</div>
			<?php endif; ?>
		</div>
	</header>
	<?php
}

/**
 * Pied de page.
 */
function cw_footer() {
	?>
	<footer class="cw-footer" id="contact">
		<div class="cw-wrap">
			<div class="cw-foot">
				<div class="cw-foot-brand">
					<?php cw_logo( false ); ?>
					<p><?php esc_html_e( 'Poêles à granulés, poêles à bois et chaudières, avec un conseil sur mesure pour chaque maison.', 'chaud-winter' ); ?></p>
				</div>

				<div>
					<h2 class="cw-foot-title"><?php esc_html_e( 'Produits', 'chaud-winter' ); ?></h2>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'cw-footer-shop',
							'container'      => false,
							'menu_class'     => 'cw-foot-list',
							'depth'          => 1,
							'fallback_cb'    => 'cw_menu_fallback',
						)
					);
					?>
				</div>

				<div>
					<h2 class="cw-foot-title"><?php esc_html_e( 'Informations', 'chaud-winter' ); ?></h2>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'cw-footer-infos',
							'container'      => false,
							'menu_class'     => 'cw-foot-list',
							'depth'          => 1,
							'fallback_cb'    => 'cw_menu_fallback',
						)
					);
					?>
				</div>

				<div>
					<h2 class="cw-foot-title"><?php esc_html_e( 'Contact', 'chaud-winter' ); ?></h2>
					<ul class="cw-foot-list">
						<li><?php echo cw_phone_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
						<li><?php echo cw_email_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
						<li><?php echo nl2br( esc_html( cw_info( 'address' ) ) ); ?></li>
					</ul>
				</div>
			</div>

			<div class="cw-legal">
				<?php
				printf(
					/* translators: %s: année en cours. */
					esc_html__( '© %s Chaud Winter. Tous droits réservés.', 'chaud-winter' ),
					esc_html( wp_date( 'Y' ) )
				);
				?>
			</div>
		</div>
	</footer>
	<?php
}
