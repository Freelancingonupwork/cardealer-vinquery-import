<?php
/**
 * Initialized
 *
 * @package VIN Vehicle Import
 */

if ( ! function_exists( 'cdvqi_script_style_admin' ) ) {
	/**
	 * Enqueue script
	 */
	function cdvqi_script_style_admin() {
		global $post_type;

		$suffix         = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$current_screen = get_current_screen();

		wp_register_script( 'cdhl-jquery-vinquery-import', trailingslashit( CDVQI_URL ) . 'js/cardealer_vinquery_import' . $suffix . '.js', array(), CDVQI_VERSION, true );

		wp_register_style( 'cd-vinquery-import', trailingslashit( CDVQI_URL ) . 'css/cardealer-vinquery-import' . $suffix . '.css', array(), CDVQI_VERSION );

		if ( 'cars' === $post_type || ( 'cars_page_cars-vinquery-import' === $current_screen->id ) ) {
			wp_enqueue_script( 'cdhl-jquery-vinquery-import' );
			wp_enqueue_style( 'cd-vinquery-import' );
		}
	}
}

add_action( 'admin_enqueue_scripts', 'cdvqi_script_style_admin' );


// Add Theme Options
function car_dealer_options__vinquery_vin_settings( $opt_name ) {
	Redux::setSection(
		$opt_name,
		array(
			'title'            => esc_html__( 'VIN Vehicle Import', 'cdvqi-addon' ),
			'id'               => 'vinquery_vin_settings',
			'icon'             => 'fas fa-chevron-right',
			'subsection'       => true,
			'fields'           => array(
				array(
					'id'     => 'vin_provider_vinquery_warning',
					'type'   => 'info',
					'style'  => 'critical',
					'notice' => false,
					'icon'   => 'fas fa-exclamation-triangle',
					'title'  => esc_html__( 'Important Note for VINquery', 'cdvqi-addon' ),
					'desc'   => '<br>'
					. sprintf(
						wp_kses(
							/* translators: %s: string */
							__( 'We have received reports that <a href="%s" target="_blank">VINquery.com</a> is not adequately responsive to customer queries or messages and not taking new customers. So, the "<strong>VINquery</strong>" service is no longer part of the standard package. The current "VINQuery" integration is for the <strong>OLD USERS</strong> whose account works fine and can use the service.', 'cdvqi-addon' ),
							cdvqi_allowed_html( 'a', 'strong' )
						),
						esc_url( 'https://www.vinquery.com/' )
					)
					. '<br><br>'
					. '<strong>' . esc_html__( 'Therefore, for NEW USERS, we strongly recommend contacting them directly to inquire about and verify their service stability before subscribing to VINquery services.', 'cdvqi-addon' ) . '</strong>'
					. '<br><br>'
					. '<strong><em>' . esc_html__( 'We no longer provide any fix or update to the VINquery integration. And we will not be liable if VINquery.com does not address customer queries.', 'cdvqi-addon' ) . '</em></strong>',
					'required' => array(
						array( 'vin_provider_type', '=', 'vinquery' ),
					),
				),
				array(
					'id'       => 'vin_provider_type',
					'type'     => 'select',
					'title'    => esc_html__( 'VIN Provider', 'cdvqi-addon' ),
					'desc'     => esc_html__( 'Select VIN provider.', 'cdvqi-addon' ),
					'options'  => array(
						'nhtsa'    => esc_html__( 'NHTSA (National Highway Traffic Safety Administration)', 'cdvqi-addon' ),
						'vincario' => esc_html__( 'Vincario', 'cdvqi-addon' ),
						'vinquery' => esc_html__( 'VINquery', 'cdvqi-addon' ),
					),
					'default'  => 'nhtsa',
				),

				// Vincario API Credentials.
				array(
					'id'     => 'vin_provider_vincario_notice',
					'type'       => 'raw',
					'title'      => '',
					'content'    => '<strong>' . esc_html__( 'Vincario Credentials', 'cdvqi-addon' ) . '</strong>',
					'desc'       => sprintf(
						wp_kses(
							/* translators: %s links */
							__( 'You can get Vincario API credentials from <a href="%s" target="_blank">here</a>.', 'cdvqi-addon' ),
							cdvqi_allowed_html( 'a', 'strong' )
						),
						'https://vindecoder.eu/'
					),
					'class'      => 'cardealer-options-notice cardealer-options-notice-warning',
					'full_width' => false,
					'required'   => array(
						array( 'vin_provider_type', '=', 'vincario' ),
					),
				),
				array(
					'id'       => 'vincario_api_key',
					'type'     => 'text',
					'title'    => esc_html__( 'Vincario - API Key', 'cdvqi-addon' ),
					'default'  => '',
					'required' => array(
						array( 'vin_provider_type', '=', 'vincario' ),
					),
				),
				array(
					'id'       => 'vincario_secret_key',
					'type'     => 'text',
					'title'    => esc_html__( 'Vincario - Secret Key', 'cdvqi-addon' ),
					'default'  => '',
					'required' => array(
						array( 'vin_provider_type', '=', 'vincario' ),
					),
				),

				// VINquery API Credentials.
				array(
					'id'     => 'vin_provider_vinquery_notice',
					'type'       => 'raw',
					'title'      => '',
					'content'    => '<strong>' . esc_html__( 'VINquery Credentials', 'cdvqi-addon' ) . '</strong>',
					'desc'       => sprintf(
						wp_kses(
							/* translators: %s links */
							__( 'You can get VINquery API credentials from <a href="%s" target="_blank">here</a>.', 'cdvqi-addon' ),
							cdvqi_allowed_html( 'a', 'strong' )
						),
						'https://vinquery.com/'
					),
					'class'      => 'cardealer-options-notice cardealer-options-notice-warning',
					'full_width' => false,
					'required'   => array(
						array( 'vin_provider_type', '=', 'vinquery' ),
					),
				),
				array(
					'id'       => 'vinquery_api_key',
					'type'     => 'text',
					'title'    => esc_html__( 'VINquery - API Key', 'cdvqi-addon' ),
					'default'  => '',
					'required' => array(
						array( 'vin_provider_type', '=', 'vinquery' ),
					),
				),
				array(
					'id'       => 'vinquery_api_reporttype',
					'type'     => 'text',
					'title'    => esc_html__( 'VINquery - Data Type', 'cdvqi-addon' ),
					'desc'     => sprintf(
						wp_kses(
							/* translators: %s: url */
							__( 'Please add the value for Data Type. Possible values are (0, 1, 2, 3). For more details about Data Type please <a href="%1$s" target="_blank">check here</a>', 'cdvqi-addon' ),
							array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
								),
							)
						),
						esc_url( 'https://vinquery.com/docs-vindecoding' )
					),
					'default'  => '0',
					'required' => array(
						array( 'vin_provider_type', '=', 'vinquery' ),
					),
				),
				array(
					'id'      => 'vinquery_api_partialvin',
					'type'    => 'button_set',
					'title'   => esc_html__( 'VINquery - Partial VIN', 'cdvqi-addon' ),
					'options'  => array(
						'true'  => esc_html__( 'True', 'cdvqi-addon' ),
						'false' => esc_html__( 'False', 'cdvqi-addon' ),
					),
					'default' => 'false',
					'required' => array(
						array( 'vin_provider_type', '=', 'vinquery' ),
					),
				),
			),
		)
	);
}
add_action( 'car_dealer_options_after_vehicle_settings', 'car_dealer_options__vinquery_vin_settings' );

if ( ! function_exists( 'cdvqi_allowed_html' ) ) {
	/**
	 * Check plugin is active or not .
	 *
	 * @param string $allowed_els .
	 */
	function cdvqi_allowed_html( $allowed_els = '' ) {
		/* bail early if parameter is empty */
		if ( empty( $allowed_els ) ) {
			return array();
		}

		if ( is_string( $allowed_els ) ) {
			$allowed_els = explode( ',', $allowed_els );
		}

		$allowed_html = array();
		$allowed_tags = wp_kses_allowed_html( 'post' );
		foreach ( $allowed_els as $el ) {
			$el = trim( $el );
			if ( array_key_exists( $el, $allowed_tags ) ) {
				$allowed_html[ $el ] = $allowed_tags[ $el ];
			}
		}
		return $allowed_html;
	}
}
