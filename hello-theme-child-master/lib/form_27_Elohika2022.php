<?php

add_filter( 'gform_currencies', 'add_cfa_currency_27' );
function add_cfa_currency_27( $currencies ) {
    $currencies['CFA'] = array(
        'name'               => __( 'Franc CFA', 'gravityforms' ),
        'symbol_left'        => '',
        'symbol_right'       => 'CFA',
        'symbol_padding'     => ' ',
        'thousand_separator' => ',',
        'decimal_separator'  => '.',
        'decimals'           => 2
    );
  
    return $currencies;
}

// ----------------------------------------------------
// Form : kama summer 2022 (26)
// > Pre-populate the form
// ----------------------------------------------------
add_filter( 'gform_pre_render_27', 'pre_render_27' );
function pre_render_27( $form ) {
	$person_service=GetService( 'person' );
	$person_token=GetToken( 'get_person_dev' );

	$found = false;

	$participant;

	// Registration modification
	if ( isset($_GET['selector']) ) {
		$GLOBALS['selector'] = $_GET['selector'];

		$row = SelectContact($GLOBALS['selector']);

		$json_data = GetParticipant($row->email, $row->sem_code);
		$participant = json_decode($json_data);
		$found = true;
	}

	$options_get = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/json\r\n",
			"ignore_errors" => true, // rather read result status that failing
		)
	);

	if ( $found ) {
		$country_iso_from_ip = $participant->country_iso;	
		$language_iso = $participant->language_iso;
	}
	else {
	    $country_iso_from_ip = GetCountryCodeFromIP(GFFormsModel::get_ip());
		$language_iso = apply_filters( 'wpml_current_language', NULL );
	}

	// Fill fields
	foreach ( $form['fields'] as $field )  {

		// TECHNICAL GUIDE
		// In this switch instruction, process all field of the form 
		// Note that the form can be in insert or update
		switch ( $field->id ) {
			case 1: // Name
				if ( $found ) {
					$field->inputs[1]['defaultValue'] = $participant->firstname;
					$field->inputs[3]['defaultValue'] = $participant->lastname;
				}
			break;

			case 3: // Email
				if ( $found ) {
					$field->inputs[0]['defaultValue'] = $participant->email;
					$field->inputs[1]['defaultValue'] = $participant->email;
					?>
    				<script type="text/javascript">
        				jQuery(document).ready(function(){
							jQuery("#input_27_3").attr("readonly", "readonly");
							jQuery("#input_27_3_2").attr("readonly", "readonly");
        				});
    				</script>
    				<?php
				}
			break;

			case 8: // Country
				$url         = $person_service . 'countries&token=' . $person_token;
				$context_get = stream_context_create( $options_get );
				$contents    = file_get_contents( $url, false, $context_get );
				$json_data   = json_decode( $contents );
				$items       = array ();
			
				foreach ( $json_data as $data ) {
					if ( ! is_object( $data ) ) continue;

					$selected = false;
					if (strtolower($data->iso) == strtolower($country_iso_from_ip)) {
						$selected = true;
					}
			
					$items[] = array(
						'text' => $data->nativeName,
						'value' => $data->iso,
						'isSelected' => $selected
					);
				}
			
				array_multisort( $items, SORT_ASC );

				$field->choices = $items;
				break;

		} // switch
	} // foreach

	return $form;
} // pre_render_27

// -------------------------------------------------------------
// Be sure that is no conflict between meals 500 and 1500 CFA
// -------------------------------------------------------------
add_filter( 'gform_validation', 'validation_27' );
function validation_27( $validation_result ) {
	$form  = $validation_result['form'];
	$entry = GFFormsModel::get_current_lead();

	$field_lunch_500 = RGFormsModel::get_field( $form, 35 );
	$meal_lunch_days_500 = str_replace( '(500.00 CFA)', '', is_object( $field_lunch_500 ) ? $field_lunch_500->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field_lunch_1500 = RGFormsModel::get_field( $form, 36 );
	$meal_lunch_days_1500 = str_replace( '(1,500.00 CFA)', '', is_object( $field_lunch_1500 ) ? $field_lunch_1500->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....

	$field_dinner_500 = RGFormsModel::get_field( $form, 37 );
	$meal_dinner_days_500 = str_replace( '(500.00 CFA)', '', is_object( $field_dinner_500 ) ? $field_dinner_500->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field_dinner_1500 = RGFormsModel::get_field( $form, 38 );
	$meal_dinner_days_1500 = str_replace( '(1,500.00 CFA)', '', is_object( $field_dinner_1500 ) ? $field_dinner_1500->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....

	$meal_lunch_days_500 = str_replace( ' ', '', $meal_lunch_days_500);
	$meal_lunch_days_1500 = str_replace( ' ', '', $meal_lunch_days_1500);
	$meal_dinner_days_500 = str_replace( ' ', '', $meal_dinner_days_500);
	$meal_dinner_days_1500 = str_replace( ' ', '', $meal_dinner_days_1500);

	if (   ( strpos($meal_lunch_days_500, '2022-08-14') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-14') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 14 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-15') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-15') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 15 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-16') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-16') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 16 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-17') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-17') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 17 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-18') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-18') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 18 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-19') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-19') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 19 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-20') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-20') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 20 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-21') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-21') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 21 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-22') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-22') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 22 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-23') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-23') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 23 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-24') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-24') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 24 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-25') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-25') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 25 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-26') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-26') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 26 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-27') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-27') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 27 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-28') !== false )
	 	&& ( strpos($meal_lunch_days_1500, '2022-08-28') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '35' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le déjeuner à 500 et 1 500 CFA pour le 28 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}

	if (   ( strpos($meal_dinner_days_500, '2022-08-14') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-14') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 14 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-15') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-15') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 15 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-16') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-16') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 16 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-17') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-17') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 17 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-18') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-18') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 18 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-19') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-19') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 19 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-20') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-20') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 20 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-21') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-21') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 21 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-22') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-22') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 22 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-23') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-23') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 23 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-24') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-24') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 24 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-25') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-25') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 25 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-26') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-26') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 26 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-27') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-27') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 27 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-28') !== false )
	 	&& ( strpos($meal_dinner_days_1500, '2022-08-28') !== false ) ) {
	 		$validation_result['is_valid'] = false;

	 		foreach ( $form['fields'] as &$field ) {
	 			if ( $field->id == '37' ) {
	 			 	$field->failed_validation = true;				
	 			 	$field->validation_message = "Vous avez choisi à la fois le dîmer à 500 et 1 500 CFA pour le 28 août. Veuillez choisir un ou l'autre.";
	 			}
	 		}
	}

	$validation_result['form'] = $form;
	return $validation_result;

}

// -----------------------------------------------------
// Send a notification to the event manager
// Send a notification to the participant
// Send the participant into Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_27', 'notification_27', 10, 3 );
function notification_27( $notification, $form, $entry ) {

	$fee = 0;
	$fee_accom = 0;
	$accommodation = '';
	$attendance = '';

	$newcomer = rgar( $entry, 14 );
	$attendance_newcomer = rgar( $entry, 28 );
	$attendance_returning = rgar( $entry, 30 );

	if ($newcomer == 'Yes') {
		switch ( $attendance_newcomer ) {
			case 'Moins de 18 ans - 1 000 CFA|1000':
				$attendance = 'Moins de 18 ans - 1 000 CFA';
				$fee = 1000;
				break;
			case 'Entre 18 et 24 ans - 3 500 CFA|3500':
				$attendance = 'Entre 18 et 24 ans - 3 500 CFA';
				$fee = 3500;
				break;
			case '25 ans et plus - 6 000 CFA|6000':
				$attendance = '25 ans et plus - 6 000 CFA';
				$fee = 6000;
				break;
		}
	} else {
		switch ( $attendance_returning ) {
			case 'Moins de 18 ans - 3 500 CFA|3500':
				$attendance = 'Moins de 18 ans - 3 500 CFA';
				$fee = 3500;
				break;
			case 'Entre 18 et 24 ans - 8 500 CFA|8500':
				$attendance = 'Entre 18 et 24 ans - 8 500 CFA';
				$fee = 8500;
				break;
			case '25 ans et plus - 16 000 CFA|16000':
				$attendance = '25 ans et plus - 16 000 CFA';
				$fee = 16000;
				break;
		}
	}

	$accom = rgar( $entry, 27 );
	switch ( $accom ) {
		case 'Huttes améliorées - 5000 CFA par personne|5000':
			$accommodation = 'Huttes ameliorees - 5000 CFA par personne';
			$fee_accom = 5000;
			break;
		case 'Résidence dortoir (6 places par chambre) - 6000 CFA par personne|6000':
			$accommodation = 'Residence dortoir (6 places par chambre) - 6000 CFA par personne';
			$fee_accom = 6000;
			break;
		case 'Petit chalet - 40 000 CFA|40000':
			$accommodation = 'Petit chalet - 40 000 CFA';
			$fee_accom = 40000;
			break;
		case 'Grand chalet - 60 000 CFA|60000':
			$accommodation = 'Grand chalet - 60 000 CFA';
			$fee_accom = 60000;
			break;
		case 'Tente 1 place - 2 000 CFA|2000':
			$accommodation = 'Tente 1 place - 2 000 CFA';
			$fee_accom = 2000;
			break;
		case 'Tente 2 places - 4 000 CFA|4000':
			$accommodation = 'Tente 2 places - 4 000 CFA';
			$fee_accom = 4000;
			break;
	}

	$breakfast_price = 500.00;
	$lunch_price_500 = 500.00;
	$lunch_price_1500 = 1500.00;
	$dinner_price_500 = 500.00;
	$dinner_price_1500 = 1500.00;

	$field = RGFormsModel::get_field( $form, 33 );
	$meal_breakfast_days = str_replace( '(500.00 CFA)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field = RGFormsModel::get_field( $form, 35 );
	$meal_lunch_days_500 = str_replace( '(500.00 CFA)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field = RGFormsModel::get_field( $form, 36 );
	$meal_lunch_days_1500 = str_replace( '(1,500.00 CFA)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field = RGFormsModel::get_field( $form, 37 );
	$meal_dinner_days_500 = str_replace( '(500.00 CFA)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field = RGFormsModel::get_field( $form, 38 );
	$meal_dinner_days_1500 = str_replace( '(1,500.00 CFA)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....

	$meal_breakfast_days = str_replace( ' ', '', $meal_breakfast_days);
	$meal_lunch_days_500 = str_replace( ' ', '', $meal_lunch_days_500);
	$meal_lunch_days_1500 = str_replace( ' ', '', $meal_lunch_days_1500);
	$meal_dinner_days_500 = str_replace( ' ', '', $meal_dinner_days_500);
	$meal_dinner_days_1500 = str_replace( ' ', '', $meal_dinner_days_1500);

	$meal_lunch_days = '';
	if (   ( strpos($meal_lunch_days_500, '2022-08-14') !== false )
		|| ( strpos($meal_lunch_days_1500, '2022-08-14') !== false ) ) {
			$meal_lunch_days .= '2022-08-14,';		
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-15') !== false )
		|| ( strpos($meal_lunch_days_1500, '2022-08-15') !== false ) ) {
			$meal_lunch_days .= '2022-08-15,';		
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-16') !== false )
		|| ( strpos($meal_lunch_days_1500, '2022-08-16') !== false ) ) {
			$meal_lunch_days .= '2022-08-16,';		
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-17') !== false )
		|| ( strpos($meal_lunch_days_1500, '2022-08-17') !== false ) ) {
			$meal_lunch_days .= '2022-08-17,';		
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-18') !== false )
		|| ( strpos($meal_lunch_days_1500, '2022-08-18') !== false ) ) {
			$meal_lunch_days .= '2022-08-18,';		
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-19') !== false )
		|| ( strpos($meal_lunch_days_1500, '2022-08-19') !== false ) ) {
			$meal_lunch_days .= '2022-08-19,';
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-20') !== false )
		|| ( strpos($meal_lunch_days_1500, '2022-08-20') !== false ) ) {
			$meal_lunch_days .= '2022-08-20,';
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-21') !== false )
		|| ( strpos($meal_lunch_days_1500, '2022-08-21') !== false ) ) {
			$meal_lunch_days .= '2022-08-21,';
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-22') !== false )
		|| ( strpos($meal_lunch_days_1500, '2022-08-22') !== false ) ) {
			$meal_lunch_days .= '2022-08-22,';
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-23') !== false )
		|| ( strpos($meal_lunch_days_1500, '2022-08-23') !== false ) ) {
			$meal_lunch_days .= '2022-08-23,';
	}
	if (   ( strpos($meal_lunch_days_500, '2022-08-24') !== false )
		|| ( strpos($meal_lunch_days_1500, '2022-08-24') !== false ) ) {
			$meal_lunch_days .= '2022-08-24,';		
	}

	$meal_dinner_days = '';
	if (   ( strpos($meal_dinner_days_500, '2022-08-14') !== false )
		|| ( strpos($meal_dinner_days_1500, '2022-08-14') !== false ) ) {
			$meal_dinner_days .= '2022-08-14,';		
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-15') !== false )
		|| ( strpos($meal_dinner_days_1500, '2022-08-15') !== false ) ) {
			$meal_dinner_days .= '2022-08-15,';		
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-16') !== false )
		|| ( strpos($meal_dinner_days_1500, '2022-08-16') !== false ) ) {
			$meal_dinner_days .= '2022-08-16,';		
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-17') !== false )
		|| ( strpos($meal_dinner_days_1500, '2022-08-17') !== false ) ) {
			$meal_dinner_days .= '2022-08-17,';		
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-18') !== false )
		|| ( strpos($meal_dinner_days_1500, '2022-08-18') !== false ) ) {
			$meal_dinner_days .= '2022-08-18,';		
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-19') !== false )
		|| ( strpos($meal_dinner_days_1500, '2022-08-19') !== false ) ) {
			$meal_dinner_days .= '2022-08-19,';
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-20') !== false )
		|| ( strpos($meal_dinner_days_1500, '2022-08-20') !== false ) ) {
			$meal_dinner_days .= '2022-08-20,';
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-21') !== false )
		|| ( strpos($meal_dinner_days_1500, '2022-08-21') !== false ) ) {
			$meal_dinner_days .= '2022-08-21,';
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-22') !== false )
		|| ( strpos($meal_dinner_days_1500, '2022-08-22') !== false ) ) {
			$meal_dinner_days .= '2022-08-22,';
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-23') !== false )
		|| ( strpos($meal_dinner_days_1500, '2022-08-23') !== false ) ) {
			$meal_dinner_days .= '2022-08-23,';
	}
	if (   ( strpos($meal_dinner_days_500, '2022-08-24') !== false )
		|| ( strpos($meal_dinner_days_1500, '2022-08-24') !== false ) ) {
			$meal_dinner_days .= '2022-08-24,';		
	}

	$nb_breakfast = substr_count($meal_breakfast_days,',')+1;
	$nb_lunch_500 = substr_count($meal_lunch_days_500,',')+1;
	$nb_lunch_1500 = substr_count($meal_lunch_days_1500,',')+1;
	$nb_dinner_500 = substr_count($meal_dinner_days_500,',')+1;
	$nb_dinner_1500 = substr_count($meal_dinner_days_1500,',')+1;

	$sem_fee_breakfast = $nb_breakfast * $breakfast_price;
	$sem_fee_lunch = ($nb_lunch_500 * $lunch_price_500) + ($nb_lunch_1500 * $lunch_price_1500);
	$sem_fee_dinner = ($nb_dinner_500 * $dinner_price_500) + ($nb_dinner_1500 * $dinner_price_1500);

	// -------------------------------------------------------------------------
	// TECHNICAL GUIDE
	// Variables to set depending of the event
	// -----BEGIN--------------------------------------------------------------------
	$form = 21;
	$sem_code = 154;                
	$year = '2022';                 
	$season = 'summer';             

	$participant = array(
		'email' => rgar( $entry, '3' ),
		'firstname' => rgar( $entry, '1.3' ),
		'lastname' => rgar( $entry, '1.6' ),
		'nickname' => rgar( $entry, '4' ),
		'fullname_native' => rgar( $entry, '7' ),
		'country' => rgar( $entry, '8' ),
		'state' => '',
		'suburb' => rgar( $entry, '9' ), // City / Town
		'prefLanguage' => apply_filters( 'wpml_current_language', NULL ),
        'username' => rgar( $entry, '1.6' ) . '||' . rgar( $entry, '1.3' ), 
		'mobile_phone' => rgar( $entry, '12' ),
		'home_phone' => rgar( $entry, '11' ),
		'work_phone' => '',
		'date_birth' => rgar( $entry, '10' ),
		'gender' => rgar( $entry, '5' ),
		'sem_code' => $sem_code,
        'year' => '2022', 
        'season' => 'summer',
		'firstseminar' => (rgar( $entry, '14' ) == 'Yes' ? 1 : 0),
		'student' => 0,         
		'present' => 0,         // In attendance
		'status' => 'regular', 
		'fee' => $fee,          
		'duration' => 15,
		'duration_days' => '2022-08-14,2022-08-15,2022-08-16,2022-08-17,2022-08-18,2022-08-19,2022-08-20,2022-08-21,2022-08-22,2022-08-23,2022-08-24,2022-08-25,2022-08-26,2022-08-27,2022-08-28',
		'hotel' => '',
		'room_no' => '',
		'accom_type' => $accommodation,
		'accom_days' => '2022-08-14,2022-08-15,2022-08-16,2022-08-17,2022-08-18,2022-08-19,2022-08-20,2022-08-21,2022-08-22,2022-08-23,2022-08-24,2022-08-25,2022-08-26,2022-08-27,2022-08-28',
		'fee_accom' => $fee_accom,
		'parking' => '',
		'meal_breakfast' => $meal_breakfast_days,
		'meal_lunch' => $meal_lunch_days,
		'meal_dinner' => $meal_dinner_days,
		'meal_count' => '', // See trigger I_seminar
        'arr_date' =>  '0000-00-00 00:00:00',
        'arr_number' => '',
        'arr_location' => '',
        'dep_date' => '0000-00-00 00:00:00', 
        'dep_number' => '',
        'dep_location' => '', 
		'translation' => '',
		'transmission' => '',
        'donation' => 0.0,
        'dinner' => 0.00,       // Gala
		'fee_transport' => 0.00,
		'responsibility' => '',
		'absent_ceremony' => 0,
		'sem_feedback' => ($newcomer == 'Yes' ? 'Nouveau venu' : 'Pas nouveau venu') . PHP_EOL . $attendance . PHP_EOL . rgar ( $entry, '13' ),
        'ip' => GFFormsModel::get_ip(),
		'pay_type' => 'n/a',
		'pay_amount' => 0.00,
		'pay_received' => 0.00,
		'pay_currency' => 'CFA',
		'fee_meals' =>$sem_fee_breakfast + $sem_fee_lunch + $sem_fee_dinner,
		'fee_cc' => 0.00,
		'fee_discount' => 0.00,
		'paypal_txn' => '',
		'paypal_status' => '',
		'paypal_date' => '',
		'paypal_fee' => 0.00,
		'paypal_data' => '',
		'survey' => '',
		'updateby' => 0,
		'formtext' => ''       // array
    );
	// ---END----------------------------------------------------------------------

	$country = GetCountryDescription($participant['country']);
	$language_iso = apply_filters( 'wpml_current_language', NULL );

	// Alert notification to the event manager
	if ( $notification['toType'] === 'email' ) {

		//$notification['to'] = 'loukesir@hotmail.com'; 

        //------------------------------------------------------------------------------------------------------------------
        // TECHNICAL GUIDE
        // Adjust this ARRAY depending of the information you want to show in the notification to responsable
        //------------------------------------------------------------------------------------------------------------------
        $fields = array(
			'First seminar' => ($participant['firstseminar'] == 1 ? 'Yes' : 'No'),
			'Firstname' => $participant['firstname'],
			'Lastname' => $participant['lastname'],
			'Email' => $participant['email'],
			'Country' => $country,
			'City / Town' => $participant['suburb'],
			'Phone' => $participant['home_phone'],
			'Mobile' => $participant['mobile_phone'],
			'Message' => $participant['sem_feedback']
			);

		$arrayFields = setNotificationArrayFields($fields); 

		// Check if a notification exist for the current language and use it as replacement
		// > Sometimes it's better to keep notifications in the database than to waste time with WPML.
		$notificationResponsable = SelectNotification($form, 'responsable', $language_iso);

		if ( 'not found' !== $notificationResponsable ) {
			$notification['message'] = $notificationResponsable;
		}
		
		$notification['message'] .= $arrayFields; 
    }

	// Notification sent to the person.
	if ( $notification['toType'] === 'field' ) {
		$GLOBALS['raelorg_country_from_ip'] = "";

		while ( $GLOBALS['raelorg_country_from_ip'] == "" )
		{
			$ip_data = @json_decode(wp_remote_retrieve_body(wp_remote_get( "http://ip-api.com/json/".$participant['ip'])));

			if ( $ip_data->status == "success" ) {
				$GLOBALS['raelorg_country_from_ip'] = $ip_data->countryCode;
			}
		}

		$selector = InsertContact( 	$participant['firstname'], 
									$participant['lastname'], 
									$participant['email'], 
									'fr', 
									$participant['country'], 
									'', // area
									'', // $message, 
									$form, // id form
									'', // $news_event, 
									$participant['ip'],
									$sem_code );


		// Check if a notification exist for the current language and use it as replacement
		// > Sometimes it's better to keep notifications in the database than to waste time with WPML.
		$confirmation = SelectNotification($form, 'person', $language_iso);

		if ( 'not found' !== $confirmation ) {
			$notification['message'] = $confirmation;
		}

		switch ($language_iso) {
			case 'en':
				$cancel_permalink = get_permalink(0) . '?selector=' . $selector;;
				$modify_permalink = get_permalink(0) . '?selector=' . $selector;;
				break;
			case 'fr':
				$cancel_permalink = get_permalink(351287) . '?selector=' . $selector;;
				$modify_permalink = get_permalink(351286) . '?selector=' . $selector;;
				break;
		}

		if ( strstr( $notification['message'], '{cancel_registration}' ) ) {
			$notification['message'] = str_replace('{cancel_registration}', $cancel_permalink, $notification['message'] );
		} elseif ( strstr( $notification['message'], '%7Bcancel_registration%7D' ) ) {
			$notification['message'] = str_replace('%7Bcancel_registration%7D', $cancel_permalink, $notification['message'] );
		} else { 
			$notification['message'] = str_replace('%7bcancel_registration%7d', $cancel_permalink, $notification['message'] );
		}

		if ( strstr( $notification['message'], '{modify_registration}' ) ) {
			$notification['message'] = str_replace('{modify_registration}', $modify_permalink, $notification['message'] );
		}

	    send_participant_to_ElohimNet( $participant, $selector );
	}

    return $notification;
    
} // notification_27