<?php
/**
 * Page Contact (utilisée automatiquement pour la page dont l'adresse est /contact/).
 *
 * Coordonnées à gauche (sur fond sombre), texte de la page et formulaire à droite.
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="cw-page-main">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'cw-contact' ); ?>>
			<header class="cw-page-header">
				<h1 class="cw-page-title"><?php the_title(); ?></h1>
			</header>

			<div class="cw-contact-grid">
				<aside class="cw-contact-card" aria-label="<?php esc_attr_e( 'Nos coordonnées', 'chaud-winter' ); ?>">
					<h2><?php esc_html_e( 'Nous joindre', 'chaud-winter' ); ?></h2>
					<dl>
						<div>
							<dt><?php esc_html_e( 'Téléphone', 'chaud-winter' ); ?></dt>
							<dd class="cw-contact-big"><?php echo cw_phone_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans la fonction. ?></dd>
							<dd><?php echo esc_html( cw_info( 'hours' ) ); ?></dd>
						</div>
						<div>
							<dt><?php esc_html_e( 'E-mail', 'chaud-winter' ); ?></dt>
							<dd><?php echo cw_email_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans la fonction. ?></dd>
						</div>
						<div>
							<dt><?php esc_html_e( 'Adresse', 'chaud-winter' ); ?></dt>
							<dd><?php echo nl2br( esc_html( cw_info( 'address' ) ) ); ?></dd>
						</div>
					</dl>
					<?php if ( cw_whatsapp_url() ) : ?>
						<a class="cw-btn cw-btn-ghost cw-btn-block" href="<?php echo esc_url( cw_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Écrire sur WhatsApp', 'chaud-winter' ); ?></a>
					<?php endif; ?>
				</aside>

				<div class="cw-contact-form entry-content">
					<?php the_content(); ?>
					<?php if ( ! has_shortcode( get_the_content(), 'contact-form-7' ) && current_user_can( 'edit_pages' ) ) : ?>
						<p class="cw-admin-note"><?php esc_html_e( 'Visible seulement par vous : le formulaire de contact n\'est pas encore en place. Installez Contact Form 7, puis relancez le bloc « Pages » dans WooCommerce > Configuration Chaud Winter.', 'chaud-winter' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</article>
	<?php endwhile; ?>
</main>

<?php
get_footer();
