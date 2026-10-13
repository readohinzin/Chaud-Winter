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
	<aside class="cw-topbar" aria-label="<?php esc_attr_e( 'Conseil par téléphone', 'chaud-winter' ); ?>">
		<div class="cw-wrap">
			<span><?php echo esc_html( cw_info( 'hours' ) ); ?></span>
			<span>
				<?php
				printf(
					/* translators: %s: numéro de téléphone. */
					esc_html__( 'Conseil par téléphone : %s', 'chaud-winter' ),
					cw_phone_html() // phpcs:ignore WordPress.Security.EscapeOutput -- déjà échappé.
				);
				?>
			</span>
		</div>
	</aside>

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

				<?php
				$cw_foot_menus = array(
					'cw-footer-shop'  => __( 'Produits', 'chaud-winter' ),
					'cw-footer-infos' => __( 'Informations', 'chaud-winter' ),
				);
				foreach ( $cw_foot_menus as $cw_location => $cw_title ) :
					// Colonne masquée tant que son menu est vide (par exemple avant la création des pages légales).
					$cw_menu = wp_nav_menu(
						array(
							'theme_location' => $cw_location,
							'container'      => false,
							'menu_class'     => 'cw-foot-list',
							'depth'          => 1,
							'fallback_cb'    => 'cw_menu_fallback',
							'echo'           => false,
						)
					);
					if ( ! $cw_menu ) {
						continue;
					}
					?>
					<div>
						<h2 class="cw-foot-title"><?php echo esc_html( $cw_title ); ?></h2>
						<?php echo $cw_menu; // phpcs:ignore WordPress.Security.EscapeOutput -- produit par wp_nav_menu(). ?>
					</div>
				<?php endforeach; ?>

				<div>
					<h2 class="cw-foot-title"><?php esc_html_e( 'Contact', 'chaud-winter' ); ?></h2>
					<ul class="cw-foot-list">
						<li><?php echo cw_phone_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
						<li class="cw-foot-hours"><?php echo esc_html( cw_info( 'hours' ) ); ?></li>
						<li><?php echo cw_email_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
						<li><?php echo nl2br( esc_html( cw_info( 'address' ) ) ); ?></li>
					</ul>
				</div>
			</div>

			<div class="cw-legal">
				<?php
				printf(
					/* translators: %s: année en cours. */
					esc_html__( '© %s HotHiver. Tous droits réservés.', 'chaud-winter' ),
					esc_html( wp_date( 'Y' ) )
				);
				printf(
					/* translators: 1: nom de l'entreprise, 2: forme juridique, 3: nom commercial, 4: SIRET, 5: n° de TVA. */
					' <span class="cw-legal-id">' . esc_html__( '%1$s, %2$s, nom commercial %3$s · SIRET %4$s · TVA %5$s', 'chaud-winter' ) . '</span>',
					esc_html( cw_info( 'company' ) ),
					esc_html( cw_info( 'legal_form' ) ),
					esc_html( cw_info( 'trade_name' ) ),
					esc_html( cw_info( 'siret' ) ),
					esc_html( cw_info( 'vat' ) )
				);
				?>
			</div>
		</div>
	</footer>
	<?php
}
