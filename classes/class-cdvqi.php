<?php
/**
 * Sub menu class
 *
 * @author Potenza
 * @package VIN Vehicle Import
 */

if ( ! class_exists( 'CDVQI' ) ) {
	/**
	 * Insert vehicle data
	 */
	class CDVQI {

		/**
		 * Autoload method.
		 *
		 * @param mixed $body   Content retrived from vin API request.
		 */
		public function cdvi_insert_vehicle_data( $body ) {
			$msg            = '';
			$post_title     = '';
			$post_title_raw = array();

			// Values in array are validated below in the foreach loop.
			$vin_import = wp_unslash( $_POST['vin_import'] );

			if ( isset( $vin_import['cars_title'] ) && ! empty( $vin_import['cars_title'] ) ) {
				foreach ( $vin_import['cars_title'] as $cars_title ) {
					$cars_title_trimmed = trim( $cars_title );
					if ( ! empty( $cars_title_trimmed ) ) {
						$cars_title_trimmed = $this->cdvi_get_car_vinquery_value( $cars_title_trimmed, $body );
						$post_title_raw[]   = sanitize_text_field( $cars_title_trimmed );
					}
				}
			}

			if ( ! empty( $post_title_raw ) ) {
				$post_title = implode( ' ', $post_title_raw );
			}

			$post_title = trim( $post_title );

			// insert post, get id.
			$insert_info          = array(
				'post_type'   => 'cars',
				'post_title'  => $post_title,
				'post_status' => 'publish',
			);
			$cdvi_cars_attributes = cardealer_get_all_taxonomy_with_terms();
			$insert_id            = wp_insert_post( $insert_info );

			if ( $insert_id ) {
				$cdvi_cars_attributes = cardealer_get_all_taxonomy_with_terms();

				foreach ( $cdvi_cars_attributes as $key => $attributes ) {

					$value         = '';
					$attr_safe_key = ( isset( $attributes['slug'] ) && ! empty( $attributes['slug'] ) ? $attributes['slug'] : str_replace( ' ', '_', strtolower( $key ) ) );
					if ( isset( $vin_import[ $attr_safe_key ] ) && ! empty( $vin_import[ $attr_safe_key ] ) && is_array( $vin_import[ $attr_safe_key ] ) ) {
						foreach ( $vin_import[ $attr_safe_key ] as $key => $vin_import_value ) {
							$value .= $this->cdvi_get_car_vinquery_value( $vin_import_value, $body ) . ' ';
						}
					} elseif ( isset( $vin_import[ $attr_safe_key ] ) && ! empty( $vin_import[ $attr_safe_key ] ) ) {
						$value = $this->cdvi_get_car_vinquery_value( $vin_import[ $attr_safe_key ], $body );
					}

					if ( 'year' === $key ) {
						$year = (string) $value;
						if ( isset( $year ) && ! empty( $year ) && 'None' !== $year ) {
							wp_set_object_terms( $insert_id, $year, 'car_year', false );
						}
					}

					if ( 'make' === $key ) {
						$make = $value;
						if ( ! empty( $value ) && 'None' !== $value ) {
							wp_set_object_terms( $insert_id, $make, 'car_make', false );
						}
					}

					if ( 'model' === $key ) {
						$model = $value;
						if ( ! empty( $value ) && 'None' !== $value ) {
							wp_set_object_terms( $insert_id, $model, 'car_model', false );
						}
					}

					if ( 'body-style' === $key ) {
						if ( ! empty( $value ) && 'None' !== $value ) {
							$body_style = $value;
							wp_set_object_terms( $insert_id, $body_style, 'car_body_style', false );
						}
					}

					if ( 'mileage' === $key ) {
						if ( ! empty( $value ) && 'None' !== $value ) {
							$mileage = (string) $value;
							wp_set_object_terms( $insert_id, $mileage, 'car_mileage', false );
						}
					}

					if ( 'fuel-economy' === $key ) {
						if ( ! empty( $value ) && 'None' !== $value ) {
							$fuel_economy = $value;
							wp_set_object_terms( $insert_id, $fuel_economy, 'car_fuel_economy', false );
						}
					}

					if ( 'fuel-type' === $key ) {
						if ( ! empty( $value ) && 'None' !== $value ) {
							$fuel_type = $value;
							wp_set_object_terms( $insert_id, $fuel_type, 'car_fuel_type', false );
						}
					}

					if ( 'trim' === $key ) {
						if ( ! empty( $value ) && 'None' !== $value ) {
							$trim = $value;
							wp_set_object_terms( $insert_id, $trim, 'car_trim', false );
						}
					}

					if ( 'transmission' === $key ) {
						$transmission = $value;
						if ( ! empty( $value ) && 'None' !== $value ) {
							wp_set_object_terms( $insert_id, $transmission, 'car_transmission', false );
						}
					}

					if ( 'condition' === $key ) {
						$condition = $value;
						if ( ! empty( $value ) && 'None' !== $value ) {
							if ( preg_match( '(new|New|N|n)', $condition ) == 1 ) {
								wp_set_object_terms( $insert_id, 'New', 'car_condition', false );
							} elseif ( preg_match( '(used|Used|U|u)', $condition ) == 1 ) {
								wp_set_object_terms( $insert_id, 'Used', 'car_condition', false );
							} else {
								wp_set_object_terms( $insert_id, 'Certified', 'car_condition', false );
							}
						}
					}

					if ( 'drivetrain' === $key ) {
						$drivetrain = $value;
						if ( ! empty( $value ) && 'None' !== $value ) {
							wp_set_object_terms( $insert_id, $drivetrain, 'car_drivetrain', false );
						}
					}

					if ( 'engine' === $key ) {
						$engine = (string) $value;
						if ( ! empty( $engine ) && 'None' !== $engine ) {
							wp_set_object_terms( $insert_id, $engine, 'car_engine', false );
						}
					}

					if ( 'exterior-color' === $key ) {
						$exterior_color = (string) $value;
						if ( ! empty( $value ) && 'None' !== $value ) {
							wp_set_object_terms( $insert_id, $exterior_color, 'car_exterior_color', false );
						}
					}

					if ( 'interior-color' === $key ) {
						$interior_color = (string) $value;
						if ( ! empty( $interior_color ) && 'None' !== $interior_color ) {
							wp_set_object_terms( $insert_id, $interior_color, 'car_interior_color', false );
						}
					}

					if ( 'stock-number' === $key ) {
						$stock_number = (string) $value;
						if ( ! empty( $value ) && 'None' !== $value ) {
							wp_set_object_terms( $insert_id, $stock_number, 'car_stock_number', false );
						}
					}

					if ( 'vin-number' === $key ) {
						$vin_number = (string) $value;
						if ( ! empty( $value ) && 'None' !== $value ) {
							wp_set_object_terms( $insert_id, $vin_number, 'car_vin_number' );
						}
					}

					/**
					 * Additional attributes
					 */
					$new_tax_obj = get_taxonomy( $key );
					if ( ! is_wp_error( $new_tax_obj ) && isset($new_tax_obj->include_in_filters) && $new_tax_obj->include_in_filters == true ) {
						$new_tax_val = (string) $value;;
						if ( ! empty( $new_tax_val ) && 'None' !== $new_tax_val ) {
							wp_set_object_terms( $insert_id, $new_tax_val, $key, false );
						}
					}
				}

				if ( isset( $vin_import['features_options'] ) && ! empty( $vin_import['features_options'] ) ) {
					if ( is_array( $vin_import['features_options'] ) ) {
						$car_features_options = $vin_import['features_options'];
						foreach ( $car_features_options as $option ) {
							if ( 'key_value' === $vin_import['features_options_import_type'] ) {
								$value = $this->cdvi_get_car_vinquery_key_value( $option, $body );
							} else {
								$value = $this->cdvi_get_car_vinquery_value( $option, $body );
							}
							wp_set_object_terms( $insert_id, $value, 'car_features_options', true );
						}
					} elseif ( isset( $vin_import['features_options'] ) && ! empty( $vin_import['features_options'] ) ) {
						if ( 'key_value' === $vin_import['features_options_import_type'] ) {
							$value = $this->cdvi_get_car_vinquery_key_value( $vin_import['features_options'], $body );
						} else {
							$value = $this->cdvi_get_car_vinquery_value( $vin_import['features_options'], $body );
						}
						wp_set_object_terms( $insert_id, $value, 'car_features_options', true );
					}
				}

				$regular_price = ( isset( $vin_import['regular_price'] ) && ! empty( $vin_import['regular_price'] ) ? $this->cdvi_get_car_vinquery_value( $vin_import['regular_price'], $body ) : '' );

				if ( isset( $regular_price ) && ! empty( $regular_price ) ) {
					preg_match_all( '!\d+!', $regular_price, $matches_regular_price );
					if ( isset( $matches_regular_price[0] ) && is_array( $matches_regular_price[0] ) ) {
						$regular_price = implode( '', $matches_regular_price[0] );
					}
					update_field( 'regular_price', $regular_price, $insert_id );
					$final_price = $regular_price;
				}

				$sale_price = ( isset( $vin_import['sale_price'] ) && ! empty( $vin_import['sale_price'] ) ? $this->cdvi_get_car_vinquery_value( $vin_import['sale_price'], $body ) : '' );
				if ( isset( $sale_price ) && ! empty( $sale_price ) ) {
					preg_match_all( '!\d+!', $sale_price, $matches_sale_price );
					if ( isset( $matches_sale_price[0] ) && is_array( $matches_sale_price[0] ) ) {
						$sale_price = implode( '', $matches_sale_price[0] );
					}
					update_field( 'sale_price', $sale_price, $insert_id );
					$final_price = $sale_price;
				}
				// set final price.
				if ( isset( $final_price ) ) {
					update_post_meta( $insert_id, 'final_price', $final_price );
				}

				$tax_label = ( isset( $vin_import['tax_label'] ) && ! empty( $vin_import['tax_label'] ) ? $this->cdvi_get_car_vinquery_value( $vin_import['tax_label'], $body ) : '' );
				if ( isset( $tax_label ) && ! empty( $tax_label ) ) {
					update_field( 'tax_label', $tax_label, $insert_id );
				}

				$post_excerpt = ( isset( $vin_import['excerpt'] ) && ! empty( $vin_import['excerpt'] ) ? $this->cdvi_get_car_vinquery_value( $vin_import['excerpt'], $body ) : '' );
				if ( isset( $post_excerpt ) && ! empty( $post_excerpt ) ) {
					$my_post = array(
						'ID'           => $insert_id,
						'post_excerpt' => $post_excerpt,
					);
					wp_update_post( $my_post );
				}

				$city_mpg = ( isset( $vin_import['city_mpg'] ) && ! empty( $vin_import['city_mpg'] ) ? $this->cdvi_get_car_vinquery_value( $vin_import['city_mpg'], $body ) : '' );
				if ( isset( $city_mpg ) && ! empty( $city_mpg ) ) {
					preg_match_all( '!\d+!', $city_mpg, $matches_city_mpg );
					if ( isset( $matches_city_mpg[0] ) && is_array( $matches_city_mpg[0] ) ) {
						$city_mpg = implode( '', $matches_city_mpg[0] );
					}
					update_field( 'city_mpg', $city_mpg, $insert_id );
				}

				$highway_mpg = ( isset( $vin_import['highway_mpg'] ) && ! empty( $vin_import['highway_mpg'] ) ? $this->cdvi_get_car_vinquery_value( $vin_import['highway_mpg'], $body ) : '' );
				if ( isset( $highway_mpg ) && ! empty( $highway_mpg ) ) {
					preg_match_all( '!\d+!', $highway_mpg, $matches_highway_mpg );
					if ( isset( $matches_highway_mpg[0] ) && is_array( $matches_highway_mpg[0] ) ) {
						$highway_mpg = implode( '', $matches_highway_mpg[0] );
					}
					update_field( 'highway_mpg', $highway_mpg, $insert_id );
				}

				$video_link = ( isset( $vin_import['video_link'] ) && ! empty( $vin_import['video_link'] ) ? $this->cdvi_get_car_vinquery_value( $vin_import['video_link'], $body ) : '' );
				if ( isset( $video_link ) && ! empty( $video_link ) ) {
					update_field( 'video_link', $video_link, $insert_id );
				}

				$technicalspecification = '';
				if ( isset( $vin_import['technical_specifications'] ) && ! empty( $vin_import['technical_specifications'] ) ) {
					if ( count( $vin_import['technical_specifications'] ) > 1 ) {
						$technicalspecification .= '<ul class="list-style-1 list-col-3">';
						foreach ( $vin_import['technical_specifications'] as $technical_specification ) {
							$get_tsp = $this->cdvi_get_car_vinquery_key_value( $technical_specification, $body );
							$tsp = sanitize_text_field( $get_tsp ) . ' ';
							$technicalspecification .= '<li><i class="fas fa-check"></i>' . $tsp . '</li>';
						}
						$technicalspecification .= '</ul>';
					} elseif ( isset( $vin_import['technical_specifications'] ) && ! empty( $vin_import['technical_specifications'] ) ) {
						foreach ( $vin_import['technical_specifications'] as $technical_specification ) {
							$technical_specification = $this->cdvi_get_car_vinquery_value( $technical_specification, $body );
							$technicalspecification .= sanitize_text_field( $technical_specification ) . ' ';
						}
					}
				}

				$technicalspecification = trim( $technicalspecification );
				if ( isset( $technicalspecification ) && ! empty( $technicalspecification ) ) {
					update_field( 'technical_specifications', $technicalspecification, $insert_id );
				}

				$generalinformation = '';
				if ( isset( $vin_import['general_information'] ) && ! empty( $vin_import['general_information'] ) ) {
					foreach ( $vin_import['general_information'] as $general_information ) {
						$general_information = $this->cdvi_get_car_vinquery_value( $general_information, $body );
						$generalinformation .= sanitize_text_field( $general_information ) . ' ';
					}
				}
				$generalinformation = trim( $generalinformation );
				if ( isset( $generalinformation ) && ! empty( $generalinformation ) ) {
					update_field( 'general_information', $generalinformation, $insert_id );
				}

				// PDF File.
				$pdf_file = ( isset( $vin_import['pdf_file'] ) && ! empty( $vin_import['pdf_file'] ) ? $vin_import['pdf_file'] : '' );
				if ( isset( $pdf_file ) && ! empty( $pdf_file ) ) {
					$val = $this->cdvi_get_car_vinquery_value( $pdf_file, $body );
					if ( filter_var( $val, FILTER_VALIDATE_URL ) ) {
						$pdf_file = get_upload_pdf_file( $val );
						update_field( 'pdf_file', $pdf_file, $insert_id );
					}
				}

				$post_content = '';
				if ( isset( $vin_import['vehicle_overview'] ) && ! empty( $vin_import['vehicle_overview'] ) ) {
					foreach ( $vin_import['vehicle_overview'] as $overview ) {
						$overview      = $this->cdvi_get_car_vinquery_value( $overview, $body );
						$post_content .= sanitize_text_field( $overview ) . ' ';
					}
				}

				$post_content = trim( $post_content );
				if ( '' !== $post_content ) {
					update_field( 'vehicle_overview', $post_content, $insert_id );
				}

				$location  = ( isset( $vin_import['location_map'] ) && ! empty( $vin_import['location_map'] ) ? $this->cdvi_get_car_vinquery_value( $vin_import['location_map'], $body ) : '' );
				$latitude  = ( isset( $vin_import['latitude'] ) && ! empty( $vin_import['latitude'] ) ? $this->cdvi_get_car_vinquery_value( $vin_import['latitude'], $body ) : '' );
				$longitude = ( isset( $vin_import['longitude'] ) && ! empty( $vin_import['longitude'] ) ? $this->cdvi_get_car_vinquery_value( $vin_import['longitude'], $body ) : '' );
				if ( '' !== $location ) {
					$valuearr     = array(
						'address' => $location,
						'lng'     => $latitude,
						'lat'     => $longitude,
					);
					$location_map = $valuearr;
				}

				if ( isset( $location_map ) && ! empty( $location_map ) ) {
					update_field( 'vehicle_location', $location_map, $insert_id );
					update_field( 'vehicle_location_address', $location, $insert_id );
					update_field( 'vehicle_location_lat', $latitude, $insert_id );
					update_field( 'vehicle_location_lng', $longitude, $insert_id );
				}

				$msg  = esc_html__( 'Congratulations, you successfully imported this vehicle details: ', 'cdvqi-addon' );
				$msg .= "<a href='" . get_permalink( $insert_id ) . "'>" . ( ! empty( $post_title ) ? $post_title : esc_html__( 'Untitled', 'cdvqi-addon' ) ) . '</a>';
			} else {
				$msg = esc_html__( 'Error importing your vehicle', 'cdvqi-addon' );
			}
			return $msg;
		}
		/**
		 * Get vinquery date
		 *
		 * @param string $vin .
		 */
		public function cdvi_get_vinquery_data( $vin ) {
			global $car_dealer_options;

			$vin_provider_type = isset( $car_dealer_options['vin_provider_type'] ) ? $car_dealer_options['vin_provider_type'] : 'vinquery';
			if ( 'vinquery' === $vin_provider_type ) {
				$cars_api_key            = ( isset( $car_dealer_options['vinquery_api_key'] ) && ! empty( $car_dealer_options['vinquery_api_key'] ) ? $car_dealer_options['vinquery_api_key'] : '' );
				$cars_api_reporttype     = ( isset( $car_dealer_options['vinquery_api_reporttype'] ) && ! empty( $car_dealer_options['vinquery_api_reporttype'] ) ? $car_dealer_options['vinquery_api_reporttype'] : '0' );
				$vinquery_api_partialvin = ( isset( $car_dealer_options['vinquery_api_partialvin'] ) && 'true' === $car_dealer_options['vinquery_api_partialvin'] ) ? 'TRUE' : 'FALSE';
				$url                     = add_query_arg(
					array(
						'accesscode' => $cars_api_key,
						'reportType' => $cars_api_reporttype,
						'vin'        => $vin,
						'partialvin' => $vinquery_api_partialvin,
					),
					'http://ws.vinquery.com/restxml.aspx'
				);

				$response_data = array();
				$request       = wp_remote_get( $url );

				if ( is_wp_error( $request ) || ( 'OK' !== wp_remote_retrieve_response_message( $request ) ) || ( 200 !== wp_remote_retrieve_response_code( $request ) ) ) {
					if ( is_wp_error( $request ) ) {
						$response_data['Status']  = 'FAILED';
						$response_data['Message'] = $request->get_error_message();
						return $response_data;
					}
				}

				$response      = wp_remote_retrieve_body( $request );
				$response_xml  = simplexml_load_string( $response );
				$response_json = wp_json_encode( $response_xml );
				$response_json = json_decode( $response_json, true );

				foreach ( $response_json['VIN'] as $attributes_keys => $attributes_value ) {
					if ( isset( $attributes_value['Number'] ) ) {
						$response_data['VIN Number'] = $attributes_value['Number'];
					}
					if ( isset( $attributes_value['Status'] ) ) {
						$response_data['Status'] = $attributes_value['Status'];
					}
				}
				if ( isset( $response_json['VIN']['Message'] ) ) {
					$response_data['Message'] = $response_json['VIN']['Message']['@attributes']['Value'];
				}
				if ( isset( $response_json['VIN']['Vehicle']['Item'] ) || isset( $response_json['VIN']['Vehicle'][0]['Item'] ) ) {
					if ( isset( $response_json['VIN']['Vehicle']['Item'] ) ) {
						$item = $response_json['VIN']['Vehicle']['Item'];
					} elseif ( isset( $response_json['VIN']['Vehicle'][0]['Item'] ) ) {
						$item = $response_json['VIN']['Vehicle'][0]['Item'];
					}
					foreach ( $item as $keys => $values ) {
						foreach ( $values as $key => $value ) {
							$response_data[ $value['Key'] ]  = $value['Value'];
							$response_data[ $value['Key'] ] .= ( isset( $value['Unit'] ) && '' !== $value['Unit'] ) ? ' ' . $value['Unit'] : '';
						}
					}
				}
			} elseif( 'vincario' === $vin_provider_type ) {

				$response_data = array();
				$apiPrefix           = 'https://api.vindecoder.eu/3.2';
				$vincario_api_key    = isset( $car_dealer_options['vincario_api_key'] ) ? $car_dealer_options['vincario_api_key'] : '';
				$vincario_secret_key = isset( $car_dealer_options['vincario_secret_key'] ) ? $car_dealer_options['vincario_secret_key'] : '';
				$id                  = 'decode';
				$vin                 = mb_strtoupper( $vin );
				$controlsum          = substr( sha1("{$vin}|{$id}|{$vincario_api_key}|{$vincario_secret_key}" ), 0, 10);
				$request             = wp_remote_get( "{$apiPrefix}/{$vincario_api_key}/{$controlsum}/decode/{$vin}.json" );

				if ( is_wp_error( $request ) ) {
					$response_data['Status']  = 'FAILED';
					$response_data['Message'] = $request->get_error_message();
					return $response_data;
				} else if ( 'OK' !== wp_remote_retrieve_response_message( $request ) || 200 !== wp_remote_retrieve_response_code( $request ) ) {
					$response_data['Status']  = 'FAILED';
					$response_data['Message'] = wp_remote_retrieve_response_message( $request );
					return $response_data;
				} else {
					$response_data['Status'] = 'SUCCESS';
				}

				$response_json = wp_remote_retrieve_body( $request );
				$response      = json_decode( $response_json, true );

				if ( isset( $response['error'] ) && $response['error'] ) {
					$response_data['Status']  = 'FAILED';
					$response_data['Message'] = isset( $response['message'] ) ? $response['message'] : esc_html__( 'Error in the request, please check API keys or limit is reached for your VIN account.', 'cdvqi-addon' );
					return $response_data;
				}

				if ( isset( $response['decode'] ) && $response['decode'] ) {
					foreach( $response['decode'] as $response_decode ) {
						if ( isset( $response_decode['value'] ) && $response_decode['value'] ) {
							if ( is_array( $response_decode['value'] ) ) {
								$response_data[$response_decode['label']] = implode( ',', $response_decode['value'] );
							} else {
								$response_data[$response_decode['label']] = $response_decode['value'];
							}
						}
					}
				}

				return $response_data;

			} elseif( 'nhtsa' === $vin_provider_type ) {
				$response_data = array();
				$request       = wp_remote_get( 'https://vpic.nhtsa.dot.gov/api/vehicles/decodevin/' . $vin . '?format=json' );
				if ( is_wp_error( $request ) || ( 'OK' !== wp_remote_retrieve_response_message( $request ) ) || ( 200 !== wp_remote_retrieve_response_code( $request ) ) ) {
					if ( is_wp_error( $request ) ) {
						$response_data['Status']  = 'FAILED';
						$response_data['Message'] = $request->get_error_message();
						return $response_data;
					}
				} else {
					$response_data['Status'] = 'SUCCESS';
				}

				$response_json = wp_remote_retrieve_body( $request );
				$response      = json_decode( $response_json, true );

				if ( isset( $response['Results'] ) && $response['Results'] ) {
					foreach ( $response['Results'] as $results ) {
						if ( ! preg_match( "/Error/i", $results['Variable'] ) ) {
							if ( $results['Value'] && 'Not Applicable' !== $results['Value'] && null !== $results['Value'] ) {
								$response_data[$results['Variable']] = $results['Value'];
							}
						} else {
							if ( 'Error Code' === $results['Variable'] && ! empty( $results['Value'] ) ) {
								$response_data['Status']  = 'FAILED';
							} elseif ( 'Error Text' === $results['Variable'] && ! empty( $results['Value'] ) ) {
								$response_data['Message'] = $results['Value'];
							}
						}
					}
				}
			}

			return $response_data;
		}

		/**
		 * Get all errors
		 */
		public function cdvi_all_vinquery_errors() {
			$vin = ( isset( $_GET['vin'] ) && ! empty( $_GET['vin'] ) ? wp_unslash( $_GET['vin'] ) : '' );
			if ( ! empty( $vin ) && isset( $_GET['error'] ) ) {
				$body = $this->cdvi_get_vinquery_data( $vin );

				if ( isset( $body->errorType ) && ! empty( $body->errorType ) ) {
					?>
					<div class='notice notice-error cars-vin-error'>
						<p class="cars_text_error">
							<?php echo esc_html( 'Error: ' . $body->message ); ?>
						</p>
					</div>
					<?php
				}
			}
		}
		/**
		 * Get vinquery value
		 *
		 * @param string $location Location.
		 * @param array  $body     Body.
		 */
		public function cdvi_get_car_vinquery_value( $location, $body ) {
			$return = '';
			if ( $location && isset( $body[$location] ) && $body[$location] ) {
				$return = $body[$location];
			}

			return $return;
		}
		/**
		 * Get vinquery key value
		 *
		 * @param string $location Location.
		 * @param array  $body     Body.
		 */
		public function cdvi_get_car_vinquery_key_value( $location, $body ) {
			$return = '';
			if ( $location && isset( $body[$location] ) && $body[$location] ) {
				$return = $location . ": ". $body[ $location ];
			}

			return $return;
		}
		/**
		 * Vinquery attributte value
		 *
		 * @param string $vin_mapp .
		 * @param string $attr_safe_name .
		 * @param string $body .
		 * @param string $option .
		 */
		public function cdvi_vinquery_atributte_value( $vin_mapp, $attr_safe_name, $body, $option ) {
			if ( isset( $body[$vin_mapp] ) && ! empty( $body[$vin_mapp] ) ) {
				?>
				<li class='ui-state-default'>
					<?php echo esc_html( $vin_mapp . ': ' . $body[$vin_mapp] ); ?>
					<input type="hidden" name="<?php echo esc_attr( 'vin_import[' . $attr_safe_name . ']' . ( 0 === $option ? '[]' : '' ) ); ?>" value="<?php echo esc_attr( $vin_mapp ); ?>">
					<span class='rmv-row'>X</span>
				</li>
				<?php
			}
		}
		/**
		 * VIN Import item
		 *
		 * @param string $attr_safe_name .
		 * @param string $vin_import_mapping .
		 * @param string $body .
		 * @param int    $option .
		 */
		public function cdvi_cars_vinquery_import_item( $attr_safe_name, $vin_import_mapping, $body, $option = 1 ) {
			if ( ! empty( $vin_import_mapping ) && isset( $vin_import_mapping[ $attr_safe_name ] ) && ! empty( $vin_import_mapping[ $attr_safe_name ] ) ) {
				$vin_mapp = $vin_import_mapping[ $attr_safe_name ];
				if ( is_array( $vin_mapp ) ) {
					foreach ( $vin_mapp as $single_vin_mapp ) {
						if ( strpos( $single_vin_mapp, ':' ) === false ) {
							$this->cdvi_vinquery_atributte_value( $single_vin_mapp, $attr_safe_name, $body, $option );
						} else {
							$key_arr = explode( ':', $single_vin_mapp );
							if ( ! empty( $key_arr ) && isset( $key_arr[0] ) && isset( $body[ $key_arr[0] ] ) ) {
								$this->cdvi_vinquery_atributte_value( $key_arr[0], $attr_safe_name, $body, $option );
							}
						}
					}
				} elseif ( strpos( $vin_mapp, ':' ) === false ) {
					$this->cdvi_vinquery_atributte_value( $vin_mapp, $attr_safe_name, $body, $option );
				} else {
					$key_arr = explode( ':', $vin_mapp );
					if ( ! empty( $key_arr ) && isset( $key_arr[0] ) && isset( $body[ $key_arr[0] ] ) ) {
						$this->cdvi_vinquery_atributte_value( $key_arr[0], $attr_safe_name, $body, $option );
					}

				}
			}
		}
	}

}
