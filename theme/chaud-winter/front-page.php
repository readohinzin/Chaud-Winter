<?php
/**
 * Page d'accueil (d'après la maquette).
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

get_header();

$cw_universes = cw_universes();
$cw_check     = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9CCBE0" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
$cw_arrow     = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
$cw_shop_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );

// Produit mis en avant dans le hero : le premier produit « mis en avant » (étoile) dans WooCommerce.
$cw_featured = null;
if ( function_exists( 'wc_get_products' ) ) {
	$cw_found = wc_get_products(
		array(
			'status'   => 'publish',
			'featured' => true,
			'limit'    => 1,
		)
	);
	$cw_featured = $cw_found ? $cw_found[0] : null;
}

// Produits de la boutique, par univers.
$cw_shop = array();
foreach ( $cw_universes as $cw_slug => $cw_universe ) {
	$cw_shop[ $cw_slug ] = cw_category_products( $cw_slug );
}
$cw_shop       = array_filter( $cw_shop );
$cw_first_slug = $cw_shop ? array_key_first( $cw_shop ) : '';
$cw_per_panel  = 8;

// Marques présentes dans la boutique.
$cw_brands = array();
if ( taxonomy_exists( 'product_brand' ) ) {
	$cw_brands = get_terms(
		array(
			'taxonomy'   => 'product_brand',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);
	$cw_brands = is_wp_error( $cw_brands ) ? array() : $cw_brands;
}

// Guides : les 3 derniers articles de la catégorie « guides ».
$cw_guides_cat = get_category_by_slug( 'guides' );
$cw_guides     = $cw_guides_cat ? get_posts(
	array(
		'category'    => $cw_guides_cat->term_id,
		'numberposts' => 3,
	)
) : array();

$cw_reviews  = cw_reviews();
$cw_whatsapp = cw_whatsapp_url();
?>

<main id="main" class="cw-home">

	<!-- Hero -->
	<section class="cw-hero">
		<div class="cw-wrap cw-hero-inner">
			<div>
				<div class="cw-eyebrow"><?php esc_html_e( 'Poêles · Chaudières · Granulés', 'chaud-winter' ); ?></div>
				<h1><?php esc_html_e( 'Une chaleur qui', 'chaud-winter' ); ?> <span class="cw-accent cw-it"><?php esc_html_e( 'tient tout l\'hiver.', 'chaud-winter' ); ?></span></h1>
				<p class="cw-lead"><?php esc_html_e( 'Poêles à granulés, poêles à bois et chaudières : des appareils choisis avec soin, livrés en France, en Belgique et en Allemagne.', 'chaud-winter' ); ?></p>
				<div class="cw-cta-row">
					<a href="#boutique" class="cw-btn cw-btn-primary"><?php esc_html_e( 'Découvrir la boutique', 'chaud-winter' ); ?></a>
					<a href="#conseil" class="cw-btn cw-btn-ghost"><?php esc_html_e( 'Trouver ma puissance', 'chaud-winter' ); ?></a>
				</div>
				<ul class="cw-trust">
					<li><?php echo $cw_check; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Prix TTC en euros', 'chaud-winter' ); ?></li>
					<li><?php echo $cw_check; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Paiement par virement', 'chaud-winter' ); ?></li>
					<li><?php echo $cw_check; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Conseil avant achat', 'chaud-winter' ); ?></li>
				</ul>
			</div>

			<div class="cw-scene-wrap">
				<?php
				// Photo du produit mis en avant ; sinon l'illustration animée de la maquette.
				if ( $cw_featured && $cw_featured->get_image_id() ) {
					echo wp_get_attachment_image(
						$cw_featured->get_image_id(),
						'large',
						false,
						array(
							'class'         => 'cw-hero-photo',
							'alt'           => '',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'sizes'         => '(min-width: 981px) 560px, 100vw',
						)
					);
				} else {
					echo file_get_contents( CW_DIR . '/assets/svg/hero.svg' ); // phpcs:ignore WordPress.Security.EscapeOutput, WordPress.WP.AlternativeFunctions -- illustration du thème.
				}
				?>
				<?php if ( $cw_featured ) : ?>
					<?php
					$cw_f_kw   = cw_product_kw( $cw_featured );
					$cw_f_desc = wp_strip_all_tags( $cw_featured->get_short_description() );
					?>
					<div class="cw-chip"><?php esc_html_e( 'À la une', 'chaud-winter' ); ?></div>
					<a class="cw-float-card" href="<?php echo esc_url( $cw_featured->get_permalink() ); ?>">
						<span>
							<?php if ( $cw_f_desc ) : ?>
								<small><?php echo esc_html( $cw_f_desc ); ?></small>
							<?php endif; ?>
							<strong><?php echo esc_html( $cw_featured->get_name() . ( $cw_f_kw ? ' · ' . cw_format_kw( $cw_f_kw ) : '' ) ); ?></strong>
						</span>
						<span class="cw-float-price"><?php echo wp_kses_post( $cw_featured->get_price_html() ); ?> <small><?php esc_html_e( 'TTC', 'chaud-winter' ); ?></small></span>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<!-- Réassurance -->
	<section class="cw-assure" aria-label="<?php esc_attr_e( 'Nos engagements', 'chaud-winter' ); ?>">
		<div class="cw-wrap">
			<div class="cw-as">
				<div class="cw-as-ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#9CCBE0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M2 6h12v10H2zM14 9h4l4 3v4h-8"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/></svg></div>
				<div><b><?php esc_html_e( 'Livraison offerte en France métropolitaine', 'chaud-winter' ); ?></b><span><?php esc_html_e( 'Aussi en Belgique et en Allemagne', 'chaud-winter' ); ?></span></div>
			</div>
			<div class="cw-as">
				<div class="cw-as-ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#9CCBE0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10h20"/></svg></div>
				<div><b><?php esc_html_e( 'Paiement par virement bancaire', 'chaud-winter' ); ?></b><span><?php esc_html_e( 'Virement SEPA, en euros', 'chaud-winter' ); ?></span></div>
			</div>
			<div class="cw-as">
				<div class="cw-as-ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#9CCBE0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M12 3l8 3v6c0 4.5-3.3 7.6-8 9-4.7-1.4-8-4.5-8-9V6z"/><path d="M8.5 12l2.5 2.5L15.5 10"/></svg></div>
				<div><b><?php esc_html_e( 'Garantie constructeur', 'chaud-winter' ); ?></b><span><?php echo esc_html( cw_info( 'warranty' ) ); ?></span></div>
			</div>
			<div class="cw-as">
				<div class="cw-as-ico"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#9CCBE0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg></div>
				<div><b><?php esc_html_e( 'Conseil par téléphone', 'chaud-winter' ); ?></b><span><?php echo cw_phone_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- déjà échappé. ?></span></div>
			</div>
		</div>
	</section>

	<!-- Les 3 univers -->
	<section class="cw-sec cw-light">
		<div class="cw-wrap">
			<div class="cw-rv">
				<div class="cw-eyebrow"><?php esc_html_e( 'Nos univers', 'chaud-winter' ); ?></div>
				<h2 class="cw-h2 cw-narrow"><?php esc_html_e( 'Trois façons de chauffer votre maison', 'chaud-winter' ); ?></h2>
			</div>
			<div class="cw-cats">
				<?php
				$cw_n = 0;
				foreach ( $cw_universes as $cw_slug => $cw_universe ) :
					++$cw_n;
					?>
					<a href="<?php echo esc_url( cw_category_url( $cw_slug ) ); ?>" class="cw-cat cw-rv">
						<span class="cw-cat-num"><?php echo esc_html( sprintf( '%02d', $cw_n ) ); ?></span>
						<span class="cw-cat-art">
							<?php
							$cw_photo = cw_universe_image_id( $cw_slug, isset( $cw_shop[ $cw_slug ] ) ? $cw_shop[ $cw_slug ] : array() );
							if ( $cw_photo ) {
								echo wp_get_attachment_image(
									$cw_photo,
									'large',
									false,
									array(
										'class' => 'cw-cat-photo',
										'alt'   => '',
										'sizes' => '(min-width: 761px) 400px, 100vw',
									)
								);
							} else {
								echo '<span class="cw-halo"></span>' . cw_universe_svg( $cw_slug ); // phpcs:ignore WordPress.Security.EscapeOutput -- SVG du thème.
							}
							?>
						</span>
						<span class="cw-cat-body">
							<span>
								<span class="cw-cat-title"><?php echo esc_html( $cw_universe['label'] ); ?></span>
								<span class="cw-cat-text"><?php echo esc_html( $cw_universe['text'] ); ?></span>
							</span>
							<span class="cw-arrow"><?php echo $cw_arrow; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Assistant de choix de puissance -->
	<section class="cw-sec" id="conseil">
		<div class="cw-wrap cw-finder">
			<div class="cw-rv">
				<div class="cw-eyebrow"><?php esc_html_e( 'Assistant de choix', 'chaud-winter' ); ?></div>
				<h2 class="cw-h2"><?php esc_html_e( 'Quelle puissance pour votre maison ?', 'chaud-winter' ); ?></h2>
				<p class="cw-lead cw-mt"><?php esc_html_e( 'Indiquez la surface et l\'isolation : on vous montre les appareils adaptés. Un conseiller peut ensuite confirmer votre choix.', 'chaud-winter' ); ?></p>
			</div>
			<div class="cw-finder-card cw-rv">
				<div class="cw-f-row">
					<label for="cw-m2"><?php esc_html_e( 'Surface à chauffer', 'chaud-winter' ); ?></label>
					<output class="cw-f-val" id="cw-m2v" for="cw-m2">120 m²</output>
				</div>
				<input type="range" id="cw-m2" min="30" max="300" step="5" value="120">
				<fieldset class="cw-pills">
					<legend class="cw-f-lbl"><?php esc_html_e( 'Isolation du logement', 'chaud-winter' ); ?></legend>
					<?php
					// Coefficients indicatifs (kW par m²) : à faire valider par un professionnel du chauffage.
					$cw_iso = array(
						'0.09'  => __( 'Bien isolée', 'chaud-winter' ),
						'0.115' => __( 'Isolation moyenne', 'chaud-winter' ),
						'0.14'  => __( 'Peu isolée', 'chaud-winter' ),
					);
					foreach ( $cw_iso as $cw_value => $cw_label ) :
						?>
						<label class="cw-pill"><input type="radio" name="cw-iso" value="<?php echo esc_attr( $cw_value ); ?>" <?php checked( '0.115', $cw_value ); ?>><span><?php echo esc_html( $cw_label ); ?></span></label>
					<?php endforeach; ?>
				</fieldset>
				<div class="cw-result" aria-live="polite">
					<div class="cw-big" id="cw-res-big"><?php echo wp_kses( __( 'Environ <b>14 kW</b>', 'chaud-winter' ), array( 'b' => array() ) ); ?></div>
					<p id="cw-res-sub"><?php esc_html_e( 'Gamme conseillée : de 11 à 17 kW.', 'chaud-winter' ); ?></p>
					<?php if ( $cw_shop ) : ?>
						<button type="button" class="cw-btn cw-btn-primary cw-btn-block" id="cw-go" hidden><?php esc_html_e( 'Voir les appareils de 11 à 17 kW', 'chaud-winter' ); ?></button>
					<?php endif; ?>
				</div>
				<p class="cw-note"><?php esc_html_e( 'Estimation indicative, à confirmer par un conseiller selon votre logement.', 'chaud-winter' ); ?></p>
			</div>
		</div>
	</section>

	<!-- Boutique -->
	<section class="cw-sec cw-light" id="boutique">
		<div class="cw-wrap">
			<div class="cw-shop-head cw-rv">
				<div>
					<div class="cw-eyebrow"><?php esc_html_e( 'La boutique', 'chaud-winter' ); ?></div>
					<h2 class="cw-h2"><?php esc_html_e( 'Nos appareils', 'chaud-winter' ); ?></h2>
				</div>
				<a class="cw-link" href="<?php echo esc_url( $cw_shop_url ); ?>"><?php esc_html_e( 'Voir tous les produits', 'chaud-winter' ); ?></a>
			</div>

			<?php if ( ! $cw_shop ) : ?>
				<p class="cw-empty"><?php esc_html_e( 'Les produits arrivent bientôt.', 'chaud-winter' ); ?></p>
			<?php else : ?>
				<div class="cw-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Catégories de produits', 'chaud-winter' ); ?>" hidden>
					<?php foreach ( $cw_shop as $cw_slug => $cw_products ) : ?>
						<button type="button" role="tab" class="cw-tab" id="cw-tab-<?php echo esc_attr( $cw_slug ); ?>" aria-controls="cw-panel-<?php echo esc_attr( $cw_slug ); ?>" aria-selected="<?php echo $cw_slug === $cw_first_slug ? 'true' : 'false'; ?>" tabindex="<?php echo $cw_slug === $cw_first_slug ? '0' : '-1'; ?>" data-pid="<?php echo esc_attr( $cw_slug ); ?>">
							<?php echo esc_html( $cw_universes[ $cw_slug ]['label'] ); ?> <span class="cw-cnt"><?php echo (int) count( $cw_products ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="cw-filter-note" id="cw-filter-note" hidden>
					<span id="cw-filter-txt"></span>
					<button type="button" class="cw-btn cw-btn-ghost cw-btn-small" id="cw-reset"><?php esc_html_e( 'Voir tous les appareils', 'chaud-winter' ); ?></button>
				</div>

				<?php foreach ( $cw_shop as $cw_slug => $cw_products ) : ?>
					<div class="cw-panel is-collapsed" id="cw-panel-<?php echo esc_attr( $cw_slug ); ?>" role="tabpanel" aria-labelledby="cw-tab-<?php echo esc_attr( $cw_slug ); ?>" data-pid="<?php echo esc_attr( $cw_slug ); ?>">
						<h3 class="cw-panel-title"><?php echo esc_html( $cw_universes[ $cw_slug ]['label'] ); ?></h3>
						<div class="cw-grid">
							<?php
							foreach ( array_values( $cw_products ) as $cw_i => $cw_product ) {
								cw_product_card( $cw_product, array( 'class' => $cw_i >= $cw_per_panel ? 'cw-extra' : '' ) );
							}
							?>
						</div>
						<?php
						$cw_more = count( $cw_products ) - $cw_per_panel;
						if ( $cw_more > 0 ) :
							?>
							<div class="cw-more-wrap">
								<button type="button" class="cw-btn cw-btn-ghost cw-expand">
									<?php
									/* translators: %d: nombre de modèles masqués. */
									echo esc_html( sprintf( _n( 'Voir l\'autre modèle', 'Voir les %d autres modèles', $cw_more, 'chaud-winter' ), $cw_more ) );
									?>
								</button>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</section>

	<!-- Comment commander -->
	<section class="cw-sec">
		<div class="cw-wrap">
			<div class="cw-rv">
				<div class="cw-eyebrow"><?php esc_html_e( 'Comment commander', 'chaud-winter' ); ?></div>
				<h2 class="cw-h2 cw-narrow"><?php esc_html_e( 'Simple, de l\'écran à votre salon', 'chaud-winter' ); ?></h2>
			</div>
			<ol class="cw-steps">
				<li class="cw-step cw-rv"><div class="cw-n" aria-hidden="true">01</div><h3><?php esc_html_e( 'Choisissez votre appareil', 'chaud-winter' ); ?></h3><p><?php esc_html_e( 'Filtrez par puissance, comparez les modèles ou demandez à être rappelé par un conseiller.', 'chaud-winter' ); ?></p></li>
				<li class="cw-step cw-rv"><div class="cw-n" aria-hidden="true">02</div><h3><?php esc_html_e( 'Passez commande', 'chaud-winter' ); ?></h3><p><?php esc_html_e( 'Vous recevez le récapitulatif et les coordonnées bancaires à l\'écran et par e-mail.', 'chaud-winter' ); ?></p></li>
				<li class="cw-step cw-rv"><div class="cw-n" aria-hidden="true">03</div><h3><?php esc_html_e( 'Réglez par virement', 'chaud-winter' ); ?></h3><p><?php esc_html_e( 'Dès réception du virement, la commande est préparée et expédiée en France, en Belgique ou en Allemagne.', 'chaud-winter' ); ?></p><p><?php esc_html_e( 'Envoyez-nous la preuve de virement par e-mail pour accélérer le traitement.', 'chaud-winter' ); ?></p><p class="cw-step-tip"><?php echo esc_html( cw_instant_transfer_tip() ); ?></p></li>
			</ol>
		</div>
	</section>

	<?php if ( $cw_brands ) : ?>
		<!-- Marques -->
		<section class="cw-brands-sec">
			<div class="cw-wrap">
				<h2 class="cw-eyebrow cw-center"><?php esc_html_e( 'Les marques de la boutique', 'chaud-winter' ); ?></h2>
				<ul class="cw-brands">
					<?php foreach ( $cw_brands as $cw_brand ) : ?>
						<li><a href="<?php echo esc_url( get_term_link( $cw_brand ) ); ?>"><?php echo esc_html( $cw_brand->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
	<?php endif; ?>

	<!-- Bandeau de conseil -->
	<section class="cw-cta-band">
		<div class="cw-wrap">
			<div>
				<h2 class="cw-it"><?php esc_html_e( 'Un doute sur le bon modèle ?', 'chaud-winter' ); ?></h2>
				<p><?php esc_html_e( 'Dites-nous la surface à chauffer et le type de logement. On vous oriente vers l\'appareil adapté.', 'chaud-winter' ); ?></p>
			</div>
			<div class="cw-cta-actions">
				<a href="<?php echo esc_url( cw_callback_url() ); ?>" class="cw-btn cw-btn-dark"><?php esc_html_e( 'Être rappelé', 'chaud-winter' ); ?></a>
				<?php if ( $cw_whatsapp ) : ?>
					<a href="<?php echo esc_url( $cw_whatsapp ); ?>" class="cw-btn cw-btn-line" target="_blank" rel="noopener"><?php esc_html_e( 'Écrire sur WhatsApp', 'chaud-winter' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php if ( $cw_reviews ) : ?>
		<!-- Avis -->
		<section class="cw-sec cw-light">
			<div class="cw-wrap">
				<div class="cw-rv">
					<div class="cw-eyebrow"><?php esc_html_e( 'Avis clients', 'chaud-winter' ); ?></div>
					<h2 class="cw-h2"><?php esc_html_e( 'Ils nous font confiance', 'chaud-winter' ); ?></h2>
					<?php
					$cw_rating = trim( (string) get_theme_mod( 'cw_reviews_rating', '' ) );
					$cw_count  = absint( get_theme_mod( 'cw_reviews_count', 0 ) );
					if ( '' !== $cw_rating && $cw_count ) :
						?>
						<p class="cw-lead cw-mt-s">
							<?php
							/* translators: 1: note sur 10, 2: nombre d'avis. */
							echo esc_html( sprintf( _n( '%1$s/10 · %2$d avis', '%1$s/10 · %2$d avis', $cw_count, 'chaud-winter' ), $cw_rating, $cw_count ) );
							?>
						</p>
					<?php endif; ?>
				</div>
				<div class="cw-rev-grid">
					<?php foreach ( $cw_reviews as $cw_review ) : ?>
						<figure class="cw-rev cw-rv">
							<div class="cw-q" aria-hidden="true">“</div>
							<blockquote><p><?php echo esc_html( $cw_review['text'] ); ?></p></blockquote>
							<?php if ( $cw_review['author'] ) : ?>
								<figcaption class="cw-who"><?php echo esc_html( $cw_review['author'] ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $cw_guides ) : ?>
		<!-- Guides -->
		<section class="cw-sec cw-light<?php echo $cw_reviews ? ' cw-pt0' : ''; ?>" id="guides">
			<div class="cw-wrap">
				<div class="cw-shop-head cw-rv">
					<div>
						<div class="cw-eyebrow"><?php esc_html_e( 'Guides', 'chaud-winter' ); ?></div>
						<h2 class="cw-h2"><?php esc_html_e( 'Conseils pour bien choisir', 'chaud-winter' ); ?></h2>
					</div>
					<a class="cw-link" href="<?php echo esc_url( get_category_link( $cw_guides_cat ) ); ?>"><?php esc_html_e( 'Tous les guides', 'chaud-winter' ); ?></a>
				</div>
				<div class="cw-guides">
					<?php foreach ( $cw_guides as $cw_guide ) : ?>
						<a href="<?php echo esc_url( get_permalink( $cw_guide ) ); ?>" class="cw-guide cw-rv">
							<span class="cw-art" aria-hidden="true">
								<?php
								if ( has_post_thumbnail( $cw_guide ) ) {
									echo get_the_post_thumbnail( $cw_guide, 'medium_large', array( 'alt' => '' ) );
								} else {
									echo cw_logo_symbol( 56 ); // phpcs:ignore WordPress.Security.EscapeOutput
								}
								?>
							</span>
							<span class="cw-tx">
								<span class="cw-tag"><?php esc_html_e( 'Guide', 'chaud-winter' ); ?></span>
								<span class="cw-guide-title"><?php echo esc_html( get_the_title( $cw_guide ) ); ?></span>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

</main>

<?php
get_footer();
