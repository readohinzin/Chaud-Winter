<?php
/**
 * Page « WooCommerce > Configuration Chaud Winter » : applique en un clic les réglages
 * de la boutique (pays, euros, TVA, virement, livraison, e-mails).
 *
 * Chaque bloc peut être appliqué séparément. Rien n'est supprimé : les zones de livraison,
 * classes d'expédition et taux de TVA déjà présents sont conservés.
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	return;
}

/**
 * Blocs de réglages proposés.
 *
 * @return array
 */
function cw_setup_blocks() {
	return array(
		'general'  => array(
			'title' => __( 'Pays, monnaie et format des prix', 'chaud-winter' ),
			'items' => array(
				__( 'Boutique en France, vente en France, Belgique et Allemagne uniquement', 'chaud-winter' ),
				__( 'Euros, prix écrits « 1 023,20 € »', 'chaud-winter' ),
				__( 'Poids en kg, dimensions en cm', 'chaud-winter' ),
				__( 'Fuseau horaire de Paris, dates écrites « 13 octobre 2026 »', 'chaud-winter' ),
			),
		),
		'tax'      => array(
			'title' => __( 'TVA', 'chaud-winter' ),
			'items' => array(
				__( 'Prix saisis et affichés TTC', 'chaud-winter' ),
				__( 'TVA française à 20 % pour tous les clients (calculée sur l\'adresse de la boutique)', 'chaud-winter' ),
				__( 'Le taux de 20 % est ajouté seulement s\'il n\'existe pas encore', 'chaud-winter' ),
			),
		),
		'payment'  => array(
			'title' => __( 'Paiement par virement uniquement', 'chaud-winter' ),
			'items' => array(
				__( 'Virement bancaire activé, avec des textes clairs pour l\'acheteur', 'chaud-winter' ),
				__( 'Tous les autres moyens de paiement désactivés (carte, chèque, paiement à la livraison…)', 'chaud-winter' ),
				__( 'Coordonnées bancaires créées avec les repères [IBAN] et [BIC] si elles sont vides', 'chaud-winter' ),
			),
		),
		'shipping' => array(
			'title' => __( 'Livraison', 'chaud-winter' ),
			'items' => array(
				__( '3 classes d\'expédition par poids : moins de 100 kg, 100 à 200 kg, plus de 200 kg', 'chaud-winter' ),
				__( 'France métropolitaine (codes postaux 01000 à 95999, Corse comprise) : livraison offerte', 'chaud-winter' ),
				__( 'Belgique et Allemagne : un forfait par classe de poids', 'chaud-winter' ),
				__( 'Tarifs à 0 € et titre « [TARIFS DE LIVRAISON] » pour la Belgique et l\'Allemagne tant que les vrais tarifs ne sont pas saisis', 'chaud-winter' ),
			),
		),
		'checkout' => array(
			'title' => __( 'Panier et commande', 'chaud-winter' ),
			'items' => array(
				__( 'Téléphone obligatoire (le transporteur appelle avant de livrer)', 'chaud-winter' ),
				__( 'Commande possible sans créer de compte', 'chaud-winter' ),
				__( 'Avis et commentaires publics désactivés sur les fiches produits (les vrais avis se saisissent dans Apparence > Personnaliser)', 'chaud-winter' ),
				__( 'Pages Panier et Commande en version classique, compatible avec le thème', 'chaud-winter' ),
			),
		),
		'pages'    => array(
			'title' => __( 'Pages Contact et pages légales', 'chaud-winter' ),
			'items' => array(
				__( 'Crée les pages : Contact, Conditions générales de vente, Mentions légales, Politique de confidentialité, Cookies, Livraison et retours, Paiement par virement, Impressum', 'chaud-winter' ),
				__( 'Textes de base avec des repères [ENTRE CROCHETS] à compléter, à faire relire', 'chaud-winter' ),
				__( 'Formulaire de contact (si Contact Form 7 est installé)', 'chaud-winter' ),
				__( 'Une page qui existe déjà n\'est pas modifiée', 'chaud-winter' ),
			),
		),
		'emails'   => array(
			'title' => __( 'E-mails', 'chaud-winter' ),
			'items' => array(
				__( 'Nom d\'expéditeur « HotHiver »', 'chaud-winter' ),
				__( 'Couleurs des e-mails assorties au site', 'chaud-winter' ),
				__( 'Objet et titre clairs pour chaque étape : virement à effectuer, virement reçu, commande expédiée', 'chaud-winter' ),
			),
		),
	);
}

/**
 * Ajoute la page dans le menu WooCommerce.
 */
function cw_setup_menu() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	add_submenu_page(
		'woocommerce',
		__( 'Configuration Chaud Winter', 'chaud-winter' ),
		__( 'Configuration Chaud Winter', 'chaud-winter' ),
		'manage_woocommerce',
		'cw-setup',
		'cw_setup_page'
	);
}
add_action( 'admin_menu', 'cw_setup_menu', 60 );

/**
 * Affiche la page.
 */
function cw_setup_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	$done = array();
	if ( isset( $_POST['cw_setup_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['cw_setup_nonce'] ), 'cw_setup' ) ) {
		$chosen = isset( $_POST['cw_blocks'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['cw_blocks'] ) ) : array();
		foreach ( array_intersect( $chosen, array_keys( cw_setup_blocks() ) ) as $block ) {
			$done[ $block ] = call_user_func( 'cw_setup_' . $block );
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Configuration Chaud Winter', 'chaud-winter' ); ?></h1>

		<?php if ( $done ) : ?>
			<div class="notice notice-success">
				<p><strong><?php esc_html_e( 'Réglages appliqués :', 'chaud-winter' ); ?></strong></p>
				<ul style="list-style:disc;margin-left:20px;">
					<?php foreach ( $done as $lines ) : ?>
						<?php foreach ( (array) $lines as $line ) : ?>
							<li><?php echo esc_html( $line ); ?></li>
						<?php endforeach; ?>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="notice notice-warning inline">
			<p><strong><?php esc_html_e( 'Avant de cliquer : faites une sauvegarde du site', 'chaud-winter' ); ?></strong> <?php esc_html_e( '(hPanel Hostinger > Sauvegardes, ou UpdraftPlus). Ces réglages remplacent ceux de WooCommerce indiqués ci-dessous.', 'chaud-winter' ); ?></p>
		</div>

		<form method="post">
			<?php wp_nonce_field( 'cw_setup', 'cw_setup_nonce' ); ?>
			<?php foreach ( cw_setup_blocks() as $key => $block ) : ?>
				<fieldset style="margin:18px 0;padding:14px 18px;background:#fff;border:1px solid #dcdcde;border-radius:8px;">
					<label style="font-size:15px;font-weight:600;">
						<input type="checkbox" name="cw_blocks[]" value="<?php echo esc_attr( $key ); ?>" checked>
						<?php echo esc_html( $block['title'] ); ?>
					</label>
					<ul style="list-style:disc;margin:8px 0 0 44px;">
						<?php foreach ( $block['items'] as $item ) : ?>
							<li><?php echo esc_html( $item ); ?></li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
			<?php endforeach; ?>
			<?php submit_button( __( 'Appliquer les réglages cochés', 'chaud-winter' ) ); ?>
		</form>

		<h2><?php esc_html_e( 'Ensuite, à compléter à la main', 'chaud-winter' ); ?></h2>
		<ul style="list-style:disc;margin-left:20px;">
			<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bacs' ) ); ?>"><?php esc_html_e( 'IBAN, BIC et titulaire du compte', 'chaud-winter' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ); ?>"><?php esc_html_e( 'Tarifs de livraison (à saisir HORS TAXES : WooCommerce ajoute la TVA)', 'chaud-winter' ); ?></a></li>
			<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=general' ) ); ?>"><?php esc_html_e( 'Adresse de la boutique', 'chaud-winter' ); ?></a></li>
		</ul>
	</div>
	<?php
}

/**
 * Pays, monnaie, format des prix, unités.
 *
 * @return array Lignes de compte rendu.
 */
function cw_setup_general() {
	$base = (string) get_option( 'woocommerce_default_country', '' );
	if ( 0 !== strpos( $base, 'FR' ) ) {
		update_option( 'woocommerce_default_country', 'FR' );
	}
	update_option( 'woocommerce_allowed_countries', 'specific' );
	update_option( 'woocommerce_specific_allowed_countries', array( 'FR', 'BE', 'DE' ) );
	update_option( 'woocommerce_ship_to_countries', '' ); // Livrer dans tous les pays de vente.
	update_option( 'woocommerce_currency', 'EUR' );
	update_option( 'woocommerce_currency_pos', 'right_space' );
	update_option( 'woocommerce_price_thousand_sep', "\u{00A0}" ); // Espace insécable : « 1 023,20 € » ne se coupe pas.
	update_option( 'woocommerce_price_decimal_sep', ',' );
	update_option( 'woocommerce_price_num_decimals', 2 );
	update_option( 'woocommerce_weight_unit', 'kg' );
	update_option( 'woocommerce_dimension_unit', 'cm' );
	update_option( 'timezone_string', 'Europe/Paris' );
	update_option( 'date_format', 'j F Y' );
	update_option( 'time_format', 'H:i' );
	update_option( 'start_of_week', 1 );

	// Nom du site : seulement s'il est vide ou celui d'une installation neuve.
	if ( in_array( (string) get_option( 'blogname', '' ), array( '', 'WordPress', 'Chaud Winter' ), true ) ) {
		update_option( 'blogname', 'HotHiver' );
	}

	// Adresse de la boutique (utilisée par WooCommerce pour la TVA et les factures) : seulement si elle est vide.
	if ( '' === (string) get_option( 'woocommerce_store_address', '' ) ) {
		update_option( 'woocommerce_store_address', '1650 route de Bouverat' );
		update_option( 'woocommerce_store_postcode', '74130' );
		update_option( 'woocommerce_store_city', 'Bonneville' );
		update_option( 'woocommerce_default_country', 'FR' );
	}

	return array( __( 'Pays, euros et format des prix réglés.', 'chaud-winter' ) );
}

/**
 * TVA : prix TTC, 20 % pour tous (adresse de la boutique).
 *
 * @return array
 */
function cw_setup_tax() {
	update_option( 'woocommerce_calc_taxes', 'yes' );
	update_option( 'woocommerce_prices_include_tax', 'yes' );
	update_option( 'woocommerce_tax_based_on', 'base' );
	update_option( 'woocommerce_tax_display_shop', 'incl' );
	update_option( 'woocommerce_tax_display_cart', 'incl' );
	update_option( 'woocommerce_tax_total_display', 'single' );
	update_option( 'woocommerce_tax_round_at_subtotal', 'no' );

	$lines = array( __( 'Prix TTC et TVA activée.', 'chaud-winter' ) );

	$has_fr = false;
	foreach ( WC_Tax::get_rates_for_tax_class( '' ) as $rate ) {
		if ( 'FR' === $rate->tax_rate_country || '' === $rate->tax_rate_country ) {
			$has_fr = true;
		}
	}
	if ( ! $has_fr ) {
		WC_Tax::_insert_tax_rate(
			array(
				'tax_rate_country'  => 'FR',
				'tax_rate_state'    => '',
				'tax_rate'          => '20.0000',
				'tax_rate_name'     => __( 'TVA', 'chaud-winter' ),
				'tax_rate_priority' => 1,
				'tax_rate_compound' => 0,
				'tax_rate_shipping' => 1,
				'tax_rate_order'    => 0,
				'tax_rate_class'    => '',
			)
		);
		$lines[] = __( 'Taux de TVA France 20 % ajouté.', 'chaud-winter' );
	} else {
		$lines[] = __( 'Un taux de TVA France existait déjà : conservé tel quel.', 'chaud-winter' );
	}
	return $lines;
}

/**
 * Virement bancaire seul moyen de paiement.
 *
 * @return array
 */
function cw_setup_payment() {
	$lines = array();

	// Désactive tous les autres moyens de paiement.
	foreach ( WC()->payment_gateways()->payment_gateways() as $id => $gateway ) {
		if ( 'bacs' === $id ) {
			continue;
		}
		$key      = $gateway->get_option_key();
		$settings = (array) get_option( $key, array() );
		if ( isset( $settings['enabled'] ) && 'no' === $settings['enabled'] ) {
			continue;
		}
		$settings['enabled'] = 'no';
		update_option( $key, $settings );
		/* translators: %s: nom du moyen de paiement. */
		$lines[] = sprintf( __( 'Désactivé : %s', 'chaud-winter' ), wp_strip_all_tags( $gateway->get_method_title() ) );
	}

	$settings = array_merge(
		(array) get_option( 'woocommerce_bacs_settings', array() ),
		array(
			'enabled'      => 'yes',
			'title'        => __( 'Virement bancaire', 'chaud-winter' ),
			'description'  => __( 'Après la commande, nos coordonnées bancaires (IBAN) s\'affichent à l\'écran et vous sont envoyées par e-mail. Votre commande est préparée dès réception du virement.', 'chaud-winter' ),
			'instructions' => __( 'Faites le virement du montant exact, en indiquant la référence de la commande. Votre commande reste en attente jusqu\'à la réception du virement, puis nous la préparons et vous prévenons par e-mail.', 'chaud-winter' ),
		)
	);
	update_option( 'woocommerce_bacs_settings', $settings );
	update_option( 'woocommerce_gateway_order', array( 'bacs' => 0 ) );
	$lines[] = __( 'Virement bancaire activé, textes pour l\'acheteur écrits.', 'chaud-winter' );

	$accounts = get_option( 'woocommerce_bacs_accounts', array() );
	if ( empty( $accounts ) ) {
		update_option(
			'woocommerce_bacs_accounts',
			array(
				array(
					'account_name'   => '[TITULAIRE DU COMPTE]',
					'account_number' => '',
					'bank_name'      => '',
					'sort_code'      => '',
					'iban'           => '[IBAN]',
					'bic'            => '[BIC]',
				),
			)
		);
		$lines[] = __( 'Coordonnées bancaires créées avec des repères : à compléter.', 'chaud-winter' );
	}
	return $lines;
}

/**
 * Zone « France métropolitaine » : livraison offerte.
 *
 * La zone ne couvre que les codes postaux 01000 à 95999 (Corse comprise). Une adresse
 * d'outre-mer (97xxx, 98xxx) ne trouve aucune livraison : le client est invité à nous contacter.
 *
 * @return array Lignes de compte rendu.
 */
function cw_setup_france_zone() {
	$title = __( 'Livraison offerte en France métropolitaine', 'chaud-winter' );
	$zone  = null;
	foreach ( WC_Shipping_Zones::get_zones() as $data ) {
		foreach ( $data['zone_locations'] as $location ) {
			if ( 'country' === $location->type && 'FR' === $location->code ) {
				$zone = new WC_Shipping_Zone( $data['id'] );
			}
		}
	}

	$created = false;
	if ( ! $zone ) {
		$zone = new WC_Shipping_Zone();
		$zone->set_zone_name( __( 'France métropolitaine', 'chaud-winter' ) );
		$zone->set_zone_order( 1 );
		$zone->add_location( 'FR', 'country' );
		$zone->add_location( '01000...95999', 'postcode' );
		$zone->save();
		$created = true;
	}

	$has_free    = false;
	$placeholder = array();
	$other       = false;
	foreach ( $zone->get_shipping_methods() as $method ) {
		if ( 'free_shipping' === $method->id ) {
			$has_free = true;
		} elseif ( 'flat_rate' === $method->id && cw_is_placeholder( $method->get_title() ) ) {
			$placeholder[] = $method->get_instance_id();
		} else {
			$other = true;
		}
	}

	if ( $has_free ) {
		return array( __( 'Zone France : livraison offerte déjà en place, conservée telle quelle.', 'chaud-winter' ) );
	}
	if ( $other ) {
		return array( __( 'Zone France : des tarifs personnalisés existent déjà, conservés tels quels (la livraison offerte n\'a pas été ajoutée).', 'chaud-winter' ) );
	}

	foreach ( $placeholder as $instance_id ) {
		$zone->delete_shipping_method( $instance_id );
	}
	$instance_id = $zone->add_shipping_method( 'free_shipping' );
	update_option( 'woocommerce_free_shipping_' . $instance_id . '_settings', array( 'title' => $title, 'requires' => '' ) );

	// Zone déjà créée sans limite de codes postaux : on la limite à la métropole.
	if ( ! $created ) {
		$has_postcode = false;
		foreach ( $zone->get_zone_locations() as $location ) {
			if ( 'postcode' === $location->type ) {
				$has_postcode = true;
			}
		}
		if ( ! $has_postcode ) {
			$zone->add_location( '01000...95999', 'postcode' );
			$zone->save();
		}
	}
	return array( $created ? __( 'Zone France métropolitaine créée : livraison offerte.', 'chaud-winter' ) : __( 'Zone France : livraison offerte en France métropolitaine.', 'chaud-winter' ) );
}

/**
 * Classes d'expédition par poids et zones France, Belgique, Allemagne.
 *
 * @return array
 */
function cw_setup_shipping() {
	$lines   = array();
	$classes = array(
		'moins-de-100-kg' => __( 'Moins de 100 kg', 'chaud-winter' ),
		'100-a-200-kg'    => __( '100 à 200 kg', 'chaud-winter' ),
		'plus-de-200-kg'  => __( 'Plus de 200 kg', 'chaud-winter' ),
	);
	$class_ids = array();
	foreach ( $classes as $slug => $name ) {
		$term = get_term_by( 'slug', $slug, 'product_shipping_class' );
		if ( ! $term ) {
			$new = wp_insert_term( $name, 'product_shipping_class', array( 'slug' => $slug ) );
			if ( ! is_wp_error( $new ) ) {
				$class_ids[] = (int) $new['term_id'];
				/* translators: %s: nom de la classe d'expédition. */
				$lines[] = sprintf( __( 'Classe d\'expédition créée : %s', 'chaud-winter' ), $name );
			}
		} else {
			$class_ids[] = (int) $term->term_id;
		}
	}

	$lines = array_merge( $lines, cw_setup_france_zone() );

	$existing = array();
	foreach ( WC_Shipping_Zones::get_zones() as $zone ) {
		foreach ( $zone['zone_locations'] as $location ) {
			if ( 'country' === $location->type ) {
				$existing[] = $location->code;
			}
		}
	}

	// Belgique et Allemagne : un forfait par classe de poids, tarifs à saisir.
	$zones = array(
		'BE' => __( 'Belgique', 'chaud-winter' ),
		'DE' => __( 'Allemagne', 'chaud-winter' ),
	);
	$order = 2;
	foreach ( $zones as $code => $name ) {
		if ( in_array( $code, $existing, true ) ) {
			/* translators: %s: pays. */
			$lines[] = sprintf( __( 'Zone %s déjà présente : conservée telle quelle.', 'chaud-winter' ), $name );
			++$order;
			continue;
		}
		$zone = new WC_Shipping_Zone();
		$zone->set_zone_name( $name );
		$zone->set_zone_order( $order++ );
		$zone->add_location( $code, 'country' );
		$zone->save();

		$instance_id = $zone->add_shipping_method( 'flat_rate' );
		$settings    = array(
			'title'         => __( 'Livraison à domicile [TARIFS DE LIVRAISON]', 'chaud-winter' ),
			'tax_status'    => 'taxable',
			'cost'          => '0',
			'type'          => 'class',
			'no_class_cost' => '0',
		);
		foreach ( $class_ids as $id ) {
			$settings[ 'class_cost_' . $id ] = '0';
		}
		update_option( 'woocommerce_flat_rate_' . $instance_id . '_settings', $settings );
		/* translators: %s: pays. */
		$lines[] = sprintf( __( 'Zone de livraison créée : %s (tarifs à saisir).', 'chaud-winter' ), $name );
	}
	return $lines;
}

/**
 * Champs de commande et pages Panier / Commande classiques.
 *
 * @return array
 */
function cw_setup_checkout() {
	update_option( 'woocommerce_checkout_phone_field', 'required' );
	update_option( 'woocommerce_checkout_company_field', 'optional' );
	update_option( 'woocommerce_checkout_address_2_field', 'optional' );
	// Pas d'avis publics sur les fiches (ils demandent modération et mentions légales) ni de commentaires.
	update_option( 'woocommerce_enable_reviews', 'no' );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );
	update_option( 'woocommerce_enable_guest_checkout', 'yes' );
	update_option( 'woocommerce_enable_checkout_login_reminder', 'yes' );
	update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );
	$lines = array( __( 'Téléphone obligatoire, commande sans compte possible.', 'chaud-winter' ) );

	// Les pages en « blocs » ignorent les réglages du thème : on passe à la version classique.
	$pages = array(
		'cart'     => array( 'woocommerce/cart', '[woocommerce_cart]' ),
		'checkout' => array( 'woocommerce/checkout', '[woocommerce_checkout]' ),
	);
	foreach ( $pages as $page => $data ) {
		$id   = wc_get_page_id( $page );
		$post = $id > 0 ? get_post( $id ) : null;
		if ( $post && has_block( $data[0], $post ) ) {
			wp_update_post(
				array(
					'ID'           => $id,
					'post_content' => '<!-- wp:shortcode -->' . $data[1] . '<!-- /wp:shortcode -->',
				)
			);
			/* translators: %s: titre de la page. */
			$lines[] = sprintf( __( 'Page « %s » passée en version classique.', 'chaud-winter' ), get_the_title( $id ) );
		}
	}
	return $lines;
}

/**
 * Expéditeur et couleurs des e-mails.
 *
 * @return array
 */
function cw_setup_emails() {
	update_option( 'woocommerce_email_from_name', 'HotHiver' );
	update_option( 'woocommerce_email_base_color', '#101216' );
	update_option( 'woocommerce_email_background_color', '#F6F2EC' );
	update_option( 'woocommerce_email_body_background_color', '#FFFFFF' );
	update_option( 'woocommerce_email_text_color', '#14171C' );

	// Un objet et un titre différents à chaque étape, pour que l'acheteur s'y retrouve.
	$emails = array(
		'customer_on_hold_order'    => array(
			'subject'            => __( 'Commande n°{order_number} : virement à effectuer', 'chaud-winter' ),
			'heading'            => __( 'Dernière étape : le virement', 'chaud-winter' ),
			'additional_content' => __( 'Votre commande est réservée en attendant votre virement.', 'chaud-winter' ),
		),
		'customer_processing_order' => array(
			'subject'            => __( 'Virement reçu : nous préparons votre commande n°{order_number}', 'chaud-winter' ),
			'heading'            => __( 'Nous avons bien reçu votre virement', 'chaud-winter' ),
			'additional_content' => __( 'Nous préparons votre commande et vous prévenons dès son expédition.', 'chaud-winter' ),
		),
		'customer_completed_order'  => array(
			'subject'            => __( 'Votre commande n°{order_number} est expédiée', 'chaud-winter' ),
			'heading'            => __( 'Votre commande est expédiée', 'chaud-winter' ),
			'additional_content' => __( 'Merci pour votre confiance. Pour toute question, répondez simplement à cet e-mail.', 'chaud-winter' ),
		),
	);
	foreach ( $emails as $id => $texts ) {
		$key = 'woocommerce_' . $id . '_settings';
		update_option( $key, array_merge( (array) get_option( $key, array() ), $texts ) );
	}
	return array( __( 'E-mails : expéditeur, couleurs et objets de chaque étape réglés.', 'chaud-winter' ) );
}

/**
 * Convertit le HTML simple des textes du thème (titres, paragraphes, listes)
 * en blocs WordPress, pour qu'ils soient faciles à modifier, même sur téléphone.
 *
 * @param string $html HTML.
 * @return string
 */
function cw_html_to_blocks( $html ) {
	$html = cw_fill_known_infos( $html );

	// Liens internes : on ajoute l'adresse du site (utile s'il est installé dans un sous-dossier).
	$html = preg_replace_callback(
		'#href="/([a-z0-9\-/]*)"#',
		function ( $m ) {
			return 'href="' . esc_url( home_url( '/' . $m[1] ) ) . '"';
		},
		$html
	);

	preg_match_all( '#<(h2|h3|p|ul|ol)>(.*?)</\1>#s', $html, $parts, PREG_SET_ORDER );
	$out = array();
	foreach ( $parts as $part ) {
		list( , $tag, $inner ) = $part;
		if ( 'h2' === $tag || 'h3' === $tag ) {
			$level = 'h3' === $tag ? ' {"level":3}' : '';
			$out[] = "<!-- wp:heading{$level} -->\n<{$tag} class=\"wp-block-heading\">{$inner}</{$tag}>\n<!-- /wp:heading -->";
		} elseif ( 'p' === $tag ) {
			$out[] = "<!-- wp:paragraph -->\n<p>{$inner}</p>\n<!-- /wp:paragraph -->";
		} else {
			preg_match_all( '#<li>(.*?)</li>#s', $inner, $items );
			$lis   = array_map(
				function ( $li ) {
					return "<!-- wp:list-item -->\n<li>{$li}</li>\n<!-- /wp:list-item -->";
				},
				$items[1]
			);
			$attrs = 'ol' === $tag ? ' {"ordered":true}' : '';
			$out[] = "<!-- wp:list{$attrs} -->\n<{$tag} class=\"wp-block-list\">" . implode( "\n", $lis ) . "</{$tag}>\n<!-- /wp:list -->";
		}
	}
	return implode( "\n\n", $out );
}

/**
 * Crée le formulaire de contact avec Contact Form 7 (s'il est installé).
 *
 * @return string Code court du formulaire, ou chaîne vide.
 */
function cw_create_contact_form() {
	if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
		return '';
	}
	$privacy = home_url( '/politique-de-confidentialite/' );
	$form    = WPCF7_ContactForm::get_template( array( 'title' => __( 'Contact HotHiver', 'chaud-winter' ) ) );
	$form->set_properties(
		array(
			'form' => implode(
				"\n",
				array(
					'<label>' . __( 'Votre nom', 'chaud-winter' ) . ' *' . "\n" . '[text* your-name autocomplete:name]</label>',
					'<label>' . __( 'Votre e-mail', 'chaud-winter' ) . ' *' . "\n" . '[email* your-email autocomplete:email]</label>',
					'<label>' . __( 'Téléphone (pour être rappelé)', 'chaud-winter' ) . "\n" . '[tel your-phone autocomplete:tel]</label>',
					'<label>' . __( 'Votre demande', 'chaud-winter' ) . "\n" . '[select your-subject "' . __( 'Conseil sur un appareil', 'chaud-winter' ) . '" "' . __( 'Être rappelé', 'chaud-winter' ) . '" "' . __( 'Question sur une commande', 'chaud-winter' ) . '" "' . __( 'Autre', 'chaud-winter' ) . '"]</label>',
					'<label>' . __( 'Surface à chauffer et type de logement (facultatif)', 'chaud-winter' ) . "\n" . '[text your-surface]</label>',
					'<label>' . __( 'Votre message', 'chaud-winter' ) . ' *' . "\n" . '[textarea* your-message]</label>',
					'<p class="cw-form-privacy">' . sprintf(
						/* translators: %s: adresse de la politique de confidentialité. */
						__( 'Vos informations servent uniquement à répondre à votre demande. En savoir plus : <a href="%s">politique de confidentialité</a>.', 'chaud-winter' ),
						esc_url( $privacy )
					) . '</p>',
					'[submit "' . __( 'Envoyer', 'chaud-winter' ) . '"]',
				)
			),
			'mail' => array_merge(
				(array) $form->prop( 'mail' ),
				array(
					/* translators: [your-subject] et [your-name] sont remplis par Contact Form 7. */
					'subject'            => __( 'Site HotHiver : [your-subject] de [your-name]', 'chaud-winter' ),
					'body'               => __( "De : [your-name] <[your-email]>\nTéléphone : [your-phone]\nDemande : [your-subject]\nSurface et logement : [your-surface]\n\nMessage :\n[your-message]", 'chaud-winter' ),
					'additional_headers' => 'Reply-To: [your-email]',
				)
			),
		)
	);
	$form->save();
	return $form->id() ? $form->shortcode() : '';
}

/**
 * Pages Contact et pages légales.
 *
 * @return array
 */
function cw_setup_pages() {
	$lines = array();
	$ids   = array();

	foreach ( cw_page_definitions() as $slug => $title ) {
		$existing = get_page_by_path( $slug );
		$file     = CW_DIR . '/contenus/' . $slug . '.html';
		$content  = is_readable( $file ) ? cw_html_to_blocks( (string) file_get_contents( $file ) ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions

		// Formulaire de contact : ajouté aussi à une page Contact déjà publiée qui n'en a pas.
		if ( 'contact' === $slug && ( ! $existing || ! has_shortcode( $existing->post_content, 'contact-form-7' ) ) ) {
			$shortcode = cw_create_contact_form();
			if ( $shortcode ) {
				$block = "\n\n<!-- wp:shortcode -->\n" . $shortcode . "\n<!-- /wp:shortcode -->";
				if ( $existing && 'publish' === $existing->post_status ) {
					wp_update_post(
						array(
							'ID'           => $existing->ID,
							'post_content' => wp_slash( $existing->post_content . $block ),
						)
					);
				} else {
					$content .= $block;
				}
				$lines[] = __( 'Formulaire de contact créé et ajouté à la page Contact.', 'chaud-winter' );
			} else {
				$lines[] = __( 'Contact Form 7 n\'est pas installé : le formulaire sera ajouté si vous relancez ce bloc après l\'avoir installé.', 'chaud-winter' );
			}
		}

		// Une page déjà publiée n'est pas modifiée. Un brouillon (par exemple la « Politique de
		// confidentialité » créée par WordPress) est remplacé par notre texte et publié.
		if ( $existing && 'publish' === $existing->post_status ) {
			$ids[ $slug ] = $existing->ID;
			/* translators: %s: titre de la page. */
			$lines[] = sprintf( __( 'Page « %s » déjà publiée : texte non modifié.', 'chaud-winter' ), $title );
			continue;
		}

		$postarr = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => wp_slash( $content ),
		);
		if ( $existing ) {
			$postarr['ID'] = $existing->ID;
			$id            = wp_update_post( $postarr );
		} else {
			$id = wp_insert_post( $postarr );
		}
		if ( $id && ! is_wp_error( $id ) ) {
			$ids[ $slug ] = (int) $id;
			/* translators: %s: titre de la page. */
			$lines[] = sprintf( __( 'Page créée : %s', 'chaud-winter' ), $title );
		}
	}

	if ( isset( $ids['politique-de-confidentialite'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $ids['politique-de-confidentialite'] );
	}
	if ( isset( $ids['conditions-generales-de-vente'] ) ) {
		// Ajoute la case « J'ai lu et j'accepte les conditions générales » à la commande.
		update_option( 'woocommerce_terms_page_id', $ids['conditions-generales-de-vente'] );
	}
	if ( isset( $ids['contact'] ) && ! get_theme_mod( 'cw_callback_page' ) ) {
		set_theme_mod( 'cw_callback_page', $ids['contact'] );
	}
	return $lines;
}
