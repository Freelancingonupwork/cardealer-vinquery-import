<?php
/**
 * Sub menu class
 *
 * @author Potenza
 * @package VIN Vehicle Import
 */

if ( ! class_exists( 'Cardealer_Vinquery_Import' ) ) {
	/**
	 * VIN Vehicle Import
	 */
	class Cardealer_Vinquery_Import extends CDVQI {

		/**
		 * Autoload method.
		 *
		 * @return void
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'cdvi_admin_vinquery_menu' ) );
			add_action( 'admin_notices', array( $this, 'cdvi_all_vinquery_errors' ) );
		}

		/**
		 * Register submenu.
		 *
		 * @return void
		 */
		public function cdvi_admin_vinquery_menu() {
			add_submenu_page(
				'edit.php?post_type=cars',
				esc_html__( 'VIN Vehicle Import', 'cdvqi-addon' ),
				esc_html__( 'VIN Vehicle Import', 'cdvqi-addon' ),
				'manage_options', 'cars-vinquery-import',
				array( &$this, 'cdvqi_cars_vinquery_import' )
			);
		}

		/**
		 * Get saved mapping for current VIN type.
		 *
		 * @return array
		 */
		public function cdvqi_get_import_mapping() {
			global $car_dealer_options;

			$status            = 'error';
			$message           = esc_html__( 'Something went wrong!', 'cdvqi-addon' );
			$vin_provider_type = isset( $car_dealer_options['vin_provider_type'] ) ? $car_dealer_options['vin_provider_type'] : 'vinquery';

			if ( 'nhtsa' === $vin_provider_type ) {
				$vin_import_var = get_option( 'vin_nhtsa_import_mapping' );
			} elseif ( 'vincario' === $vin_provider_type ) {
				$vin_import_var = get_option( 'vin_vincario_import_mapping' );
			} else {
				$vin_import_var = get_option( 'vin_query_import_mapping' );
			}

			if ( ! empty( $vin_import_var ) ) {
				$vin_import_mapping = ! empty( $vin_import_var ) ? $vin_import_var : '';
				$vin_import_mapping = isset( $vin_import_mapping['vin_import'] ) ? $vin_import_mapping['vin_import'] : $vin_import_mapping;
			} else {
				$vin_import_mapping = array();
			}

			return $vin_import_mapping;
		}

		/**
		 * Render submenu.
		 *
		 * @return void
		 */
		public function cdvqi_cars_vinquery_import() {
			global $car_dealer_options, $cars;

			$vin               = ( isset( $_GET['vin'] ) && ! empty( $_GET['vin'] ) ? sanitize_text_field( wp_unslash( $_GET['vin'] ) ) : '' );
			$vin_provider_type = isset( $car_dealer_options['vin_provider_type'] ) ? $car_dealer_options['vin_provider_type'] : 'vinquery';

			?>
			<div class="wrap cdhl_car_vin_import">
				<h1 class="wp-heading-inline"><?php esc_html_e( 'VIN Import', 'cdvqi-addon' ); ?></h1>
				<hr class="wp-header-end">
				<?php
				if ( ! empty( $vin ) ) {
					$responce_body = $this->cdvi_get_vinquery_data( $vin );

					if ( isset( $responce_body['Status'] ) && 'FAILED' === $responce_body['Status'] ) {
						?>
						<div class="notice notice-error cars-vin-error">
							<p class="cars_text_error">
								<?php echo wp_kses_post( $responce_body['Message'] ); ?>
							</p>
							<p>
								<?php
								printf(
									wp_kses(
										/* translators: 1: Vehicle VIN Import page link. */
										__( 'Return to <a href="%1$s">VIN Import</a> page.', 'cdvqi-addon' ),
										array(
											'a' => array(
												'href' => true,
											),
										)
									),
									esc_url( admin_url( 'edit.php?post_type=cars&page=cars-vinquery-import' ) )
								);
								?>
							</p>
						</div>
						<?php
						die;
					}
				}

				/**
				 * When post mapped data for save
				 */
				if ( isset( $_POST['vin_import'] ) && ! empty( $_POST['vin_import'] ) ) {
					$msg = $this->cdvi_insert_vehicle_data( $responce_body );
					echo wp_kses_post( $msg );
				} else {
					/**
					 * Get VIN details and mapping area
					 */
					unset( $_GET['error'] );
					if ( ! empty( $vin ) && ! isset( $_GET['error'] ) ) {
						$vin_import_mapping = $this->cdvqi_get_import_mapping();
						?>
						<div class="cdhl-import-area-left">
							<div class="cdhl-area-title">
								<p><?php echo esc_html__( 'To import vehicles, drag and drop fields from the right column into the left column attributes and meta boxes. You can save current field mapping for future use. You can use saved field mapping to import vehicles again.', 'cdvqi-addon' ); ?></p><br>
								<h3 class="res-msg"></h3>
								<div class="cdhl-button-group">
									<button class="cdhl_save_current_mapping current_vin_mapping button cdhl_button-primary"><?php esc_html_e( 'Save current mapping', 'cdvqi-addon' ); ?></button>
									<button class="cdhl_submit_vin button button-primary" style="vertical-align: super;"><?php esc_html_e( 'Import Vehicles', 'cdvqi-addon' ); ?></button>
									<span class="cdhl-loader-img"></span>
								</div>
								<div class="clr"></div>
							</div>

							<form method="post" action="" name="cars_vin_import_form" id="cars_vin_import_form">
								<?php wp_nonce_field( 'cdvi_cars_vin_import', 'cdvqi_vinquery_import_nonce' ); ?>
								<div id="tabs">
									<ul>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-vehicle-title"><a href="#tabs-1"><?php esc_html_e( 'Vehicle Title', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-attributes"><a href="#tabs-2"><?php esc_html_e( 'Attributes', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-car-images"><a href="#tabs-3"><?php esc_html_e( 'Vehicle Images', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-regular-price"><a href="#tabs-4"><?php esc_html_e( 'Regular price', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-tax-label"><a href="#tabs-5"><?php esc_html_e( 'Tax Label', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-fuel-efficiency"><a href="#tabs-6"><?php esc_html_e( 'Fuel Efficiency', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-pdf-file"><a href="#tabs-7"><?php esc_html_e( 'PDF Brochure', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-video-link"><a href="#tabs-8"><?php esc_html_e( 'Video', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-car-status"><a href="#tabs-9"><?php esc_html_e( 'Vehicle Status', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-vehicle-overview"><a href="#tabs-10"><?php esc_html_e( 'Vehicle Overview', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-features-options"><a href="#tabs-11"><?php esc_html_e( 'Features & Options', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-technical-specifications"><a href="#tabs-12"><?php esc_html_e( 'Technical Specifications', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-general-information"><a href="#tabs-13"><?php esc_html_e( 'General Information', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-vehicle-location"><a href="#tabs-14"><?php esc_html_e( 'Vehicle Location', 'cdvqi-addon' ); ?></a></li>
										<li class="cdvqi-field-map-tab cdvqi-field-map-tab-excerpt"><a href="#tabs-15"><?php esc_html_e( 'Excerpt(Short content)', 'cdvqi-addon' ); ?></a></li>
									</ul>
									<div class="cdhl-form-group cdvqi-field-map-tab-content cdvqi-field-map-tab-content-vehicle-title" id="tabs-1">
										<div class="cdhl_attributes">
											<label><?php esc_html_e( 'Vehicle Titles', 'cdvqi-addon' ); ?></label>
											<div class="cars_attributes">
												<ul class="cdhl_cars_attributes cdhl_form_data" data-limit="0" data-name="cars_title">
													<?php $this->cdvi_cars_vinquery_import_item( 'cars_title', $vin_import_mapping, $responce_body, 0 ); ?>
												</ul>
											</div>
										</div>
									</div>
									<div class="cdhl-form-group cdvqi-field-map-tab-content cdvqi-field-map-tab-content-attributes" id="tabs-2">
										<?php
										$cars_attributes = cardealer_get_all_taxonomy_with_terms();
										foreach ( $cars_attributes as $key => $value ) {
											$attr_safe_name = $value['slug'];
											if ( 'features-options' !== $key ) {
												?>
												<div class="cdhl_attributes">
													<label><?php echo esc_html( $value['label'] ); ?></label>
													<div class="cars_attributes_area">
														<ul class="cdhl_cars_attributes cdhl_form_data" data-limit="1" data-name="<?php echo esc_attr( $attr_safe_name ); ?>">
															<?php
															$this->cdvi_cars_vinquery_import_item( $key, $vin_import_mapping, $responce_body );
															?>
														</ul>
													</div>
												</div>
												<?php
											}
										}
										?>
									</div>

									<?php
									// extra spots.
									$extra_spots = array(
										'car_images'               => array(
											'label' => esc_html__( 'Vehicles Images', 'cdvqi-addon' ),
											'limit' => 0,
										),
										'regular_price'            => array(
											'label' => esc_html__( 'Regular price', 'cdvqi-addon' ),
											'limit' => 1,
										),
										'tax_label'                => array(
											'label' => esc_html__( 'Tax Label', 'cdvqi-addon' ),
											'limit' => 1,
										),
										'fuel_efficiency'          => array(
											'label' => esc_html__( 'Fuel Efficiency', 'cdvqi-addon' ),
											'limit' => 1,
										),
										'pdf_file'                 => array(
											'label' => esc_html__( 'PDF Brochure', 'cdvqi-addon' ),
											'limit' => 1,
										),
										'video_link'               => array(
											'label' => esc_html__( 'Video Link', 'cdvqi-addon' ),
											'limit' => 1,
										),
										'car_status'               => array(
											'label' => esc_html__( 'Vehicle Status( sold/unsold )', 'cdvqi-addon' ),
											'limit' => 1,
										),
										'vehicle_overview'         => array(
											'label' => esc_html__( 'Vehicle Overview', 'cdvqi-addon' ),
											'limit' => 0,
										),
										'features_options'         => array(
											'label' => esc_html__( 'Features Options', 'cdvqi-addon' ),
											'limit' => 0,
										),
										'technical_specifications' => array(
											'label' => esc_html__( 'Technical Specifications', 'cdvqi-addon' ),
											'limit' => 0,
										),
										'general_information'      => array(
											'label' => esc_html__( 'General Information', 'cdvqi-addon' ),
											'limit' => 0,
										),
										'vehicle_location'         => array(
											'label' => esc_html__( 'Vehicle Location', 'cdvqi-addon' ),
											'limit' => 1,
										),
										'excerpt'                  => array(
											'label' => esc_html__( 'Excerpt(Short content)', 'cdvqi-addon' ),
											'limit' => 1,
										),
									);

									$ef = 3;
									foreach ( $extra_spots as $key => $option ) {
										$class = 'cdvqi-field-map-tab-content cdvqi-field-map-tab-content-' . str_replace( '_', '-', $key );

										if ( 'features_options' === $key ) {
											$taxonomy_name         = get_taxonomy( 'car_features_options' );
											$slug                  = $taxonomy_name->rewrite['slug'];
											$label                 = $taxonomy_name->labels->menu_name;
											$fno_import_type_value = ( isset( $vin_import_mapping['features_options_import_type'] ) && ! empty( $vin_import_mapping['features_options_import_type'] ) && in_array( $vin_import_mapping['features_options_import_type'], array(), true ) ) ? $vin_import_mapping['features_options_import_type'] : 'value';
											?>
											<div class="<?php echo esc_attr( $class ); ?>" id="tabs-<?php echo esc_attr( $ef ); ?>">
												<div class="cdhl_attributes">
													<label><?php echo esc_html( $label ); ?></label>
													<div class="features-options-import-type-list">
														<div class="features-options-import-type-list-label"><?php esc_html_e( 'Import type:', 'cdvqi-addon' ); ?></div>
														<div class="features-options-import-type-list-options">
															<label><input type="radio" name="vin_import[features_options_import_type]" value="value" <?php checked( $fno_import_type_value, 'value' ); ?>><?php esc_html_e( 'Value', 'cdvqi-addon' ); ?></label>
															<label><input type="radio" name="vin_import[features_options_import_type]" value="key_value" <?php checked( $fno_import_type_value, 'key_value' ); ?>><?php esc_html_e( 'Key: Value', 'cdvqi-addon' ); ?></label>
														</div>
													</div>
													<div class="cars_attributes_area">
														<ul class="cdhl_cars_attributes cdhl_form_data" data-limit="<?php echo esc_attr( $option['limit'] ); ?>" data-name="<?php echo esc_attr( $key ); ?>">
															<?php $this->cdvi_cars_vinquery_import_item( $key, $vin_import_mapping, $responce_body, $option['limit'] ); ?>
														</ul>
													</div>
												</div>
											</div>
											<?php
										}

										if ( 'regular_price' === $key ) {
											?>
											<div class="<?php echo esc_attr( $class ); ?>" id="tabs-<?php echo esc_attr( $ef ); ?>">
												<div class="cdhl_attributes">
													<label><?php esc_html_e( 'Regular Price', 'cdvqi-addon' ); ?></label>
													<div class="cars_attributes_area">
														<ul class="cdhl_cars_attributes cdhl_form_data" data-limit="<?php echo esc_attr( $option['limit'] ); ?>" data-name="regular_price">
															<?php $this->cdvi_cars_vinquery_import_item( 'regular_price', $vin_import_mapping, $responce_body, $option['limit'] ); ?>
														</ul>
													</div>
												</div>


												<div class="cdhl_attributes">
													<label><?php esc_html_e( 'Sale Price', 'cdvqi-addon' ); ?></label>
													<div class="cars_attributes_area">
														<ul class="cdhl_cars_attributes cdhl_form_data" data-limit="<?php echo esc_attr( $option['limit'] ); ?>" data-name="sale_price">
															<?php $this->cdvi_cars_vinquery_import_item( 'sale_price', $vin_import_mapping, $responce_body, $option['limit'] ); ?>
														</ul>
													</div>
												</div>

											</div>
											<?php
										}

										if ( 'fuel_efficiency' === $key ) {
											?>
											<div class="<?php echo esc_attr( $class ); ?>" id="tabs-<?php echo esc_attr( $ef ); ?>">

												<div class="cdhl_attributes">
													<label><?php esc_html_e( 'City MPG', 'cdvqi-addon' ); ?></label>
													<div class="cars_attributes_area">
														<ul class="cdhl_cars_attributes cdhl_form_data" data-limit="<?php echo esc_attr( $option['limit'] ); ?>" data-name="city_mpg">
															<?php $this->cdvi_cars_vinquery_import_item( 'city_mpg', $vin_import_mapping, $responce_body, $option['limit'] ); ?>
														</ul>
													</div>
												</div>

												<div class="cdhl_attributes">
													<label><?php esc_html_e( 'Highway MPG', 'cdvqi-addon' ); ?></label>
													<div class="cars_attributes_area">
														<ul class="cdhl_cars_attributes cdhl_form_data" data-limit="<?php echo esc_attr( $option['limit'] ); ?>" data-name="highway_mpg">
															<?php $this->cdvi_cars_vinquery_import_item( 'highway_mpg', $vin_import_mapping, $responce_body, $option['limit'] ); ?>
														</ul>
													</div>
												</div>
											</div>
											<?php
										}

										if ( 'features_options' !== $key && 'regular_price' !== $key && 'fuel_efficiency' !== $key ) {
											?>
											<div class="<?php echo esc_attr( $class ); ?>" id="tabs-<?php echo esc_attr( $ef ); ?>">
												<div class="cdhl_attributes">
													<label><?php echo esc_html( $option['label'] ); ?></label>
													<?php
													if ( 'technical_specifications' === $key ) {
														?>
														<div class="cdvqi-field-map-tab-content-notice"><?php echo esc_html__( 'Important Note: If multiple items are added in this field, the system will generate content as a list (each item in each list item).', 'cdvqi-addon' ); ?></div>
														<?php
													}
													?>
													<div class="cars_attributes_area clearfix">
														<ul class="cdhl_cars_attributes cdhl_form_data" data-limit="<?php echo esc_attr( $option['limit'] ); ?>" data-name="<?php echo esc_attr( $key ); ?>">
															<?php $this->cdvi_cars_vinquery_import_item( $key, $vin_import_mapping, $responce_body, $option['limit'] ); ?>
														</ul>
													</div>
												</div>
											</div>
											<?php
										}
										$ef++;
									}
									?>
								</div>
							</form>

							<div class="cdhl-area-title cdvi-footer">
								<div class="cdhl-button-group">
									<button class="cdhl_save_current_mapping current_vin_mapping button cdhl_button-primary"><?php esc_html_e( 'Save current mapping', 'cdvqi-addon' ); ?></button>
									<button class="cdhl_submit_vin button button-primary" style="vertical-align: super;"><?php esc_html_e( 'Import Vehicles', 'cdvqi-addon' ); ?></button>
									<span class="cdhl-loader-img"></span>
								</div>
								<div class="clr"></div>
							</div>
						</div>
						<div class="cdhl-import-area-right">
							<h3><?php esc_html_e( 'API Result', 'cdvqi-addon' ); ?></h3>
							<ul id="cdhl_vin_items" class="cdhl_form_data ui-sortable">
								<?php
								if ( 'SUCCESS' === $responce_body['Status'] ) {
									foreach ( $responce_body as $key => $value ) {
										if ( 'Status' !== $key && 'Message' !== $key ) {
											?>
											<li class='ui-state-default'>
												<?php echo esc_html( $key . ': ' . $value ); ?>
												<input type="hidden" name="" value="<?php echo esc_attr( $key ); ?>" />
											</li>
											<?php
										}
									}
								}
								?>
							</ul>
						</div>
						<?php
					} else {
						/**
						 * Pass VIN number here ( Enter vin number here )
						 */
						$cars_api_key        = isset( $car_dealer_options['vinquery_api_key'] ) ? $car_dealer_options['vinquery_api_key'] : '';
						$vincario_api_key    = isset( $car_dealer_options['vincario_api_key'] ) ? $car_dealer_options['vincario_api_key'] : '';
						$vincario_secret_key = isset( $car_dealer_options['vincario_secret_key'] ) ? $car_dealer_options['vincario_secret_key'] : '';

						if ( ( ! empty( $vincario_secret_key ) && ! empty( $vincario_api_key ) && 'vincario' === $vin_provider_type ) || ( ! empty( $cars_api_key ) && 'vinquery' === $vin_provider_type ) || 'nhtsa' === $vin_provider_type ) {
							?>
							<div class="upload-plugin-pgs">
								<form method="GET" class="wp-upload-form" action="" name="import_url">
									<input type="hidden" name="post_type" value="cars">
									<input type="hidden" name="page" value="cars-vinquery-import">

									<label class="screen-reader-text" for="pluginzip"><?php esc_html_e( 'vehicle file', 'cdvqi-addon' ); ?></label>
									<div class="vin-search-input-container">
										<input class="vin-field" type="text" name="vin" placeholder="<?php esc_html_e( 'VIN #', 'cdvqi-addon' ); ?>" style="width: 60%;" maxlength="17">
										<div class="vin-search-input-count">
											<span class="vin-search-input-count-value">0</span><span><?php esc_html_e( '/17', 'cdvqi-addon' ); ?></span>
										</div>
									</div>
									<button onclick="jQuery(this).closest('form').submit()" class="button"><?php esc_html_e( 'Get vehicle details', 'cdvqi-addon' ); ?></button>
								</form>
							</div>
							<?php
						} else {
							$option_tab_url = car_dealer_get_options_tab_url( 'vin_provider_type' );
							printf(
								wp_kses(
									__( 'The API key is not set. Click <a href="%s" target="_blank">here</a> to set the API key.', 'cdvqi-addon' ),
									array(
										'a' => array(
											'href'   => true,
											'target' => true,
										),
									)
								),
								esc_url( $option_tab_url )
							);
						}
					}
				}
				?>
			</div>
			<?php
		}

	}

}
new Cardealer_Vinquery_Import();
