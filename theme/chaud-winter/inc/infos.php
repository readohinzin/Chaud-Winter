<?php
/**
 * Coordonnées de l'entreprise, modifiables dans Apparence > Personnaliser > Chaud Winter : coordonnées.
 *
 * Tant qu'une information n'est pas fournie, elle s'affiche sous forme de repère
 * entre crochets, par exemple [TÉLÉPHONE]. Aucun repère ne doit rester au lancement.
 *
 * @package chaud-winter
 */

defined( 'ABSPATH' ) || exit;

/**
 * Liste des coordonnées avec leur libellé et leur repère par défaut.
 *
 * @return array
 */
function cw_info_fields() {
	return array(
		'phone'   => array(
			'label'   => __( 'Téléphone', 'chaud-winter' ),
			'default' => '[TÉLÉPHONE]',
		),
		'hours'   => array(
			'label'   => __( 'Horaires du conseil par téléphone', 'chaud-winter' ),
			'default' => '[HORAIRES]',
		),
		'email'   => array(
			'label'   => __( 'Adresse e-mail', 'chaud-winter' ),
			'default' => '[EMAIL]',
		),
		'address' => array(
			'label'   => __( 'Adresse postale', 'chaud-winter' ),
			'default' => '[ADRESSE]',
		),
	);
}

/**
 * Renvoie une coordonnée (ou son repère entre crochets si elle n'est pas encore remplie).
 *
 * @param string $key phone, hours, email ou address.
 * @return string
 */
function cw_info( $key ) {
	$fields = cw_info_fields();
	if ( ! isset( $fields[ $key ] ) ) {
		return '';
	}
	$value = trim( (string) get_theme_mod( 'cw_' . $key, '' ) );
	return '' === $value ? $fields[ $key ]['default'] : $value;
}

/**
 * Vrai si la valeur est encore un repère du type [TÉLÉPHONE].
 *
 * @param string $value Valeur à tester.
 * @return bool
 */
function cw_is_placeholder( $value ) {
	return (bool) preg_match( '/\[[^\]]+\]/u', (string) $value );
}

/**
 * Ajoute la section « Chaud Winter : coordonnées » dans le personnalisateur.
 *
 * @param WP_Customize_Manager $wp_customize Gestionnaire du personnalisateur.
 */
function cw_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'cw_infos',
		array(
			'title'       => __( 'Chaud Winter : coordonnées', 'chaud-winter' ),
			'description' => __( 'Ces informations s\'affichent dans la barre du haut et le pied de page. Laissez vide pour afficher le repère entre crochets.', 'chaud-winter' ),
			'priority'    => 30,
		)
	);

	foreach ( cw_info_fields() as $key => $field ) {
		$wp_customize->add_setting(
			'cw_' . $key,
			array(
				'default'           => '',
				'sanitize_callback' => 'address' === $key ? 'sanitize_textarea_field' : 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'cw_' . $key,
			array(
				'label'       => $field['label'],
				'section'     => 'cw_infos',
				'type'        => 'address' === $key ? 'textarea' : 'text',
				'input_attrs' => array( 'placeholder' => $field['default'] ),
			)
		);
	}

	$wp_customize->add_setting(
		'cw_callback_page',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		'cw_callback_page',
		array(
			'label'       => __( 'Page du bouton « Être rappelé »', 'chaud-winter' ),
			'description' => __( 'En général, la page Contact.', 'chaud-winter' ),
			'section'     => 'cw_infos',
			'type'        => 'dropdown-pages',
		)
	);
}
add_action( 'customize_register', 'cw_customize_register' );

/**
 * Adresse du bouton « Être rappelé » : la page choisie, sinon la page dont l'adresse est /contact/.
 *
 * @return string
 */
function cw_callback_url() {
	$page_id = absint( get_theme_mod( 'cw_callback_page', 0 ) );
	if ( $page_id && 'publish' === get_post_status( $page_id ) ) {
		return get_permalink( $page_id );
	}
	$contact = get_page_by_path( 'contact' );
	return $contact ? get_permalink( $contact ) : home_url( '/contact/' );
}

/**
 * Rappel dans le tableau de bord tant que des coordonnées manquent.
 */
function cw_admin_notice_placeholders() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$missing = array();
	foreach ( cw_info_fields() as $key => $field ) {
		if ( cw_is_placeholder( cw_info( $key ) ) ) {
			$missing[] = $field['label'];
		}
	}
	if ( ! $missing ) {
		return;
	}
	printf(
		'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a></p></div>',
		esc_html__( 'Chaud Winter : informations à compléter avant la mise en ligne.', 'chaud-winter' ),
		esc_html( implode( ', ', $missing ) ),
		esc_url( admin_url( 'customize.php?autofocus[section]=cw_infos' ) ),
		esc_html__( 'Compléter maintenant', 'chaud-winter' )
	);
}
add_action( 'admin_notices', 'cw_admin_notice_placeholders' );
