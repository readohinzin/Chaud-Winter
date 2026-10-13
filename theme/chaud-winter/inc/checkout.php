<?php
/**
 * Panier, commande et paiement par virement.
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/**
 * Vrai sur le panier, la page de commande et le compte client.
 *
 * @return bool
 */
function cw_is_checkout_page() {
	return is_cart() || is_checkout() || is_account_page();
}

/**
 * Styles du panier et de la commande.
 */
function cw_checkout_assets() {
	if ( cw_is_checkout_page() ) {
		wp_enqueue_style( 'cw-shop', CW_URI . '/assets/css/shop.css', array( 'cw-theme' ), CW_VERSION );
		wp_enqueue_style( 'cw-checkout', CW_URI . '/assets/css/checkout.css', array( 'cw-shop' ), CW_VERSION );
	}
}
add_action( 'wp_enqueue_scripts', 'cw_checkout_assets', 30 );

/**
 * Classe sur le body pour la mise en page claire du panier et de la commande.
 *
 * @param array $classes Classes du body.
 * @return array
 */
function cw_checkout_body_class( $classes ) {
	if ( cw_is_checkout_page() ) {
		$classes[] = 'cw-wc-page';
	}
	return $classes;
}
add_filter( 'body_class', 'cw_checkout_body_class' );

/**
 * Pas de barre latérale sur le panier, la commande et le compte client.
 *
 * @param string $layout Mise en page.
 * @return string
 */
function cw_checkout_sidebar_layout( $layout ) {
	return cw_is_checkout_page() ? 'no-sidebar' : $layout;
}
add_filter( 'generate_sidebar_layout', 'cw_checkout_sidebar_layout', 20 );

/**
 * Libellé du bouton de commande. La loi française (et allemande) impose qu'il indique
 * clairement que la commande oblige à payer. À faire valider.
 *
 * @return string
 */
function cw_order_button_text() {
	return __( 'Commander avec obligation de paiement', 'chaud-winter' );
}
add_filter( 'woocommerce_order_button_text', 'cw_order_button_text' );

/**
 * Libellé du statut « En attente » (on-hold) : c'est le statut que WooCommerce donne aux
 * commandes payées par virement, en attendant que le vendeur confirme la réception.
 *
 * @param array $statuses Statuts de commande.
 * @return array
 */
function cw_order_status_labels( $statuses ) {
	if ( isset( $statuses['wc-on-hold'] ) ) {
		$statuses['wc-on-hold'] = _x( 'En attente du virement', 'Statut de commande', 'chaud-winter' );
	}
	return $statuses;
}
add_filter( 'wc_order_statuses', 'cw_order_status_labels' );

/**
 * Infos du virement pour une commande : montant exact et référence.
 *
 * @param WC_Order $order Commande.
 * @return array amount (texte), reference (texte).
 */
function cw_transfer_details( $order ) {
	return array(
		'amount'    => html_entity_decode( wp_strip_all_tags( wc_price( $order->get_total(), array( 'currency' => $order->get_currency() ) ) ), ENT_QUOTES, 'UTF-8' ),
		/* translators: %s: numéro de commande. */
		'reference' => sprintf( __( 'Commande %s', 'chaud-winter' ), $order->get_order_number() ),
	);
}

/**
 * Encadré « Comment payer » sur la page de confirmation, avant l'IBAN.
 *
 * @param int $order_id Numéro de la commande.
 */
function cw_thankyou_transfer_box( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	$d = cw_transfer_details( $order );
	?>
	<section class="cw-transfer-box" aria-labelledby="cw-transfer-title">
		<h2 id="cw-transfer-title"><?php esc_html_e( 'Dernière étape : le virement', 'chaud-winter' ); ?></h2>
		<dl class="cw-transfer-facts">
			<div>
				<dt><?php esc_html_e( 'Montant exact à virer', 'chaud-winter' ); ?></dt>
				<dd><?php echo esc_html( $d['amount'] ); ?></dd>
			</div>
			<div>
				<dt><?php esc_html_e( 'Référence à indiquer dans le virement', 'chaud-winter' ); ?></dt>
				<dd><?php echo esc_html( $d['reference'] ); ?></dd>
			</div>
		</dl>
		<ol class="cw-transfer-steps">
			<li><?php esc_html_e( 'Faites le virement depuis votre banque, avec les coordonnées bancaires ci-dessous.', 'chaud-winter' ); ?></li>
			<li><?php esc_html_e( 'Indiquez bien la référence : elle nous permet de reconnaître votre paiement.', 'chaud-winter' ); ?></li>
			<li><?php esc_html_e( 'Un virement met en général 1 à 2 jours ouvrés à arriver. Dès sa réception, nous préparons votre commande et vous prévenons par e-mail.', 'chaud-winter' ); ?></li>
		</ol>
		<p class="cw-transfer-note"><?php esc_html_e( 'Ces informations vous ont aussi été envoyées par e-mail.', 'chaud-winter' ); ?></p>
	</section>
	<?php
}
add_action( 'woocommerce_thankyou_bacs', 'cw_thankyou_transfer_box', 5 );

/**
 * Même encadré dans l'e-mail de commande envoyé au client (avant l'IBAN).
 *
 * @param WC_Order $order         Commande.
 * @param bool     $sent_to_admin E-mail destiné au vendeur.
 * @param bool     $plain_text    E-mail en texte brut.
 */
function cw_email_transfer_box( $order, $sent_to_admin, $plain_text = false ) {
	if ( $sent_to_admin || 'bacs' !== $order->get_payment_method() || ! $order->has_status( 'on-hold' ) ) {
		return;
	}
	$d = cw_transfer_details( $order );

	if ( $plain_text ) {
		echo "\n" . esc_html__( 'DERNIÈRE ÉTAPE : LE VIREMENT', 'chaud-winter' ) . "\n";
		echo esc_html__( 'Montant exact à virer', 'chaud-winter' ) . ' : ' . esc_html( $d['amount'] ) . "\n";
		echo esc_html__( 'Référence à indiquer dans le virement', 'chaud-winter' ) . ' : ' . esc_html( $d['reference'] ) . "\n";
		echo esc_html__( 'Un virement met en général 1 à 2 jours ouvrés à arriver. Dès sa réception, nous préparons votre commande et vous prévenons par e-mail.', 'chaud-winter' ) . "\n\n";
		return;
	}
	?>
	<div style="margin:0 0 24px;padding:18px 20px;border:2px solid #F26B2A;border-radius:12px;background:#FFF8F3;">
		<h2 style="margin:0 0 12px;"><?php esc_html_e( 'Dernière étape : le virement', 'chaud-winter' ); ?></h2>
		<p style="margin:0 0 6px;"><?php esc_html_e( 'Montant exact à virer', 'chaud-winter' ); ?> : <strong><?php echo esc_html( $d['amount'] ); ?></strong></p>
		<p style="margin:0 0 12px;"><?php esc_html_e( 'Référence à indiquer dans le virement', 'chaud-winter' ); ?> : <strong><?php echo esc_html( $d['reference'] ); ?></strong></p>
		<p style="margin:0;"><?php esc_html_e( 'Un virement met en général 1 à 2 jours ouvrés à arriver. Dès sa réception, nous préparons votre commande et vous prévenons par e-mail.', 'chaud-winter' ); ?></p>
	</div>
	<?php
}
add_action( 'woocommerce_email_before_order_table', 'cw_email_transfer_box', 5, 3 );

/**
 * Rappel dans le tableau de bord : IBAN et tarifs de livraison encore à compléter.
 *
 * @param array $missing Informations manquantes.
 * @return array
 */
function cw_missing_shop_infos( $missing ) {
	foreach ( (array) get_option( 'woocommerce_bacs_accounts', array() ) as $account ) {
		if ( cw_is_placeholder( implode( ' ', (array) $account ) ) ) {
			$missing[] = __( 'IBAN et BIC (WooCommerce > Réglages > Paiements)', 'chaud-winter' );
			break;
		}
	}
	foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
		foreach ( $zone['shipping_methods'] as $method ) {
			if ( cw_is_placeholder( $method->get_title() ) ) {
				$missing[] = __( 'Tarifs de livraison (WooCommerce > Réglages > Expédition)', 'chaud-winter' );
				break 2;
			}
		}
	}
	return $missing;
}
add_filter( 'cw_missing_infos', 'cw_missing_shop_infos' );
