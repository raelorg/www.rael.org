<?php

// ----------------------------------------------------
// > The gform_pre_render filter is executed before the 
//   form is displayed and can be used to manipulate 
//   the Form Object prior to rendering the form.
// ----------------------------------------------------

add_filter( 'gform_pre_render_64', 'pre_render_64' );
function pre_render_64( $form ) {
	$GLOBALS['raelorg_session_ID'] = GetUniqueIdSession();
	$GLOBALS['raelorg_ip_address'] = GFFormsModel::get_ip();
	$GLOBALS['raelorg_country_from_ip'] = "";
	$GLOBALS['raelorg_countries'] = array();

	GFCommon::log_debug( __METHOD__ . 'pre_render DEBUG' );

	while ( $GLOBALS['raelorg_country_from_ip'] == "" )
		{
			$ip_data = @json_decode(wp_remote_retrieve_body(wp_remote_get( "http://ip-api.com/json/".$GLOBALS['raelorg_ip_address'])));

			if ( $ip_data->status == "success" ) {
				$GLOBALS['raelorg_country_from_ip'] = $ip_data->countryCode;

				if ($ip_data->countryCode == 'HK') {
					$GLOBALS['raelorg_country_from_ip'] = 'cn';
				}
			}
		}

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

		switch ( $field->id ) {
			case 78: // Name
				if ( $found ) {
					$field->inputs[1]['defaultValue'] = $participant->firstname;
					$field->inputs[3]['defaultValue'] = $participant->lastname;
				}
			break;

			case 79: // Email
				if ( $found ) {
					$field->inputs[0]['defaultValue'] = $participant->email;
					$field->inputs[1]['defaultValue'] = $participant->email;
					?>
    				<script type="text/javascript">
        				jQuery(document).ready(function(){
							jQuery("#input_64_79").attr("readonly", "readonly");
							jQuery("#input_64_79_2").attr("readonly", "readonly");
        				});
    				</script>
    				<?php
				}
			break;

			case '82': // Country & Province
				$country = GetParentLabelCountry( apply_filters( 'wpml_current_language', NULL ) );
				$province = GetChildrenLabelProvince( apply_filters( 'wpml_current_language', NULL ) );

				$field->inputs = array(
					array(
					'id' => "{$field->id}.1",
					'label' => '*' . $country
					),
					array(
					'id' => "{$field->id}.2",
					'label' => '*' . $province
					),
				);
				
				// Bug into Chained Selects List:
				// > Loading countries into a global variable to avoid twice http request 
				$url         = $person_service . 'countries&token=' . $person_token;
				$context_get = stream_context_create( $options_get );
				$contents    = file_get_contents( $url, false, $context_get );
				$json_data   = json_decode( $contents );

				foreach ( $json_data as $data ) {
					if ( ! is_object( $data ) ) continue;

					$selected = false;
					if (strtolower($data->iso) == strtolower($GLOBALS['raelorg_country_from_ip'])) {
						$selected = true;
					}
					
					$GLOBALS['raelorg_countries'][] = array(
						'text' => $data->nativeName,
						'value' => $data->iso,
						'isSelected' => $selected
					);
				}
			break;

			case 84: // Prefered Language
				$url         = $person_service . 'prefPublicLanguages&token=' . $person_token;
				$context_get = stream_context_create( $options_get );
				$contents    = file_get_contents( $url, false, $context_get );
				$json_data   = json_decode( $contents );
				$items       = array ();

				foreach ( $json_data as $data ) {
					if ( ! is_object( $data ) ) continue;
			
					$selected = false;
					if (strtolower($data->iso) == strtolower($language_iso)) {
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

			case '160':
				if (date("Y-m-d") < "2024-10-26") {
					$field->defaultValue = "show";
				}
			break;
				
			case '201':
				$field->defaultValue = "japan"; // luc proposition
			break;

		} // switch
	} // foreach

	return $form;
} // pre_render_64

// -----------------------------------------------------------------------
// Fill in the Country field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_64_82_1', 'contact_us_populate_country_64', 10, 7 );
function contact_us_populate_country_64( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	//InsertFormsLog( $GLOBALS['raelorg_session_ID'], '41', 'Country', $GLOBALS['raelorg_country_from_ip'], $GLOBALS['raelorg_ip_address'], 'N/A' );

	return $GLOBALS['raelorg_countries'];
	
} // contact_us_populate_country_64

// -----------------------------------------------------------------------
// Fill in the Province field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_64_82_2', 'contact_us_populate_province_64', 10, 7 );
function contact_us_populate_province_64( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {
	global $wpdb;

	$selected_iso_country = $chain_value[ "{$field->id}.1" ];

	//InsertFormsLog( $GLOBALS['raelorg_session_ID'], '41', 'Province', $GLOBALS['raelorg_country_from_ip'], $GLOBALS['raelorg_ip_address'], 'N/A' );
	
	$choices = array ();
	$query   = "select province from raelorg_country_province where code_country = '" . $selected_iso_country . "' and active = 1 order by province";
	$result  = $wpdb->get_results ( $query );

	foreach ( $result as $data )
	{
		$choices[] = array(
			'text' => $data->province,
			'value' => $data->province
		);
	}

	return $choices;

} // contact_us_populate_province_64

add_action( 'gform_after_submission_64', 'after_submission_64', 10, 2 );
function after_submission_64 ( $entry, $form ) {
	// Get the post
    $post = get_post( $entry['post_id'] );
 
    // Log the original post
    GFCommon::log_debug( 'gform_after_submission: Original post => ' . print_r( $post, true ) );
}

// -----------------------------------------------------
// Modify a notification object before it is converted into an email and sent
// > Send a notification to the event manager
// > Send a notification to the participant
// > Send the participant in Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_64', 'notification_64', 10, 3 );
function notification_64( $notification, $form, $entry ) {

	error_log( __METHOD__ . ' LOUKESIR: Ceci est un test DEBUG '  . PHP_EOL );
	GFCommon::log_debug( __METHOD__ . ' LOUKESIR: Ceci est un test DEBUG ' );

	$notification['to'] = 'loukesir@hotmail.com';
	
	$status = '';
	$attendance = '';

	$status = rgar( $entry, '2' ); // Please choose your status
	GFCommon::log_debug( __METHOD__ . ' LOUKESIR: $status: ' . $status  . PHP_EOL );
	return $notification;
	$attendance = rgar( $entry, '3' ); // Which date will you participate?  Full, Partial
	return $notification;

	$sem_code = 160;
	$discount = 0;
	$discount_applied = False;
	$fee = 0.0;
	$hotel = '';
	$hotel_checkin = '';
	$hotel_checkout = '';
	$box_meals = '';
    $fee_transport = 0;
	$fee_accom = 0.0;
	$duration = 0;
	$duration_days = '';
	$sem_fee_lunch = 0;
	$sem_fee_dinner = 0;
	$bus_service = '';
	$fee_gala_dinner = 0;
	$meal_lunch_days = '';
	$meal_dinner_days = '';
	$JRM_Transport_Service_on_December_18 = '';
	$JRM_Transport_Service_on_December_18_destination = '';
	$JRM_Transport_Service_on_December_18_departure_time = '';
	$JRM_Transport_Service_on_December_18_departure_flight_number = '';
	$JRM_Transport_Service_on_December_18_departure_flight_type = '';
	$JRM_Circular_bus = '';
	$JRM_Circular_bus_hotel = '';
	$register_as_proxy = '';
	$last_name_proxy = '';
	$first_name_proxy = '';
	$email_proxy = '';
	$relationship_proxy = '';
	$fullname_native_language = '';
	$VISA_to_enter_japan = '';
	$nationality_to_enter_japan = '';
	$additional_information = '';
	
	// IMPORTANT
	// Pour comprendre la programmation, il faut tenir compte que le formulaire contient une
	// paire de champs avec discount et sans discount pour chaque ststus. Des conditions 
	// logiques contrôles l'affichage de ces champs
	
	// Pourquoi ne pas mettre un code dans "value" au lieu du texte en anglais ?
	// Réponse : Parce que lorsqu'on utilse rgar pour obtenir la valeur choisie, il faut
	// de toute façon reconvertir en texte pour envoyer le texte en anglais dans Elohim.net
	// et dans la notification envoyée au responsable. De plus, en mettant un code, on doit
	// utilsier une nomenclature et cela rend plus complexe le lien entre code, programmation 
	// et reonversion en texte.
	
	// Les conditions logiques du formulaire sont effectuées sur l'attribut "value" . Il faut
	// donc s'assurer que tous les attributs "value" associés aux conditions logiques n'ont
	// pas été traduites par WPML.

	/*
	$proxy_japan = rgar( $entry, '201' ); // proxy japan
	$bProxyJapan = true;
	if ($proxy_japan != 'japan') {
		$bProxyJapan = false;
	}
	*/

	$status = rgar( $entry, '2' ); // Please choose your status
	$attendance = rgar( $entry, '3' ); // Which date will you participate?  Full, Partial
	GFCommon::log_debug( __METHOD__ . ' LOUKESIR: $status: ' . $status  . PHP_EOL );
	$hotel = rgar( $entry, '146' ); // The hotel you reserve
	$first_participation = rgar( $entry, '145' ); // Have you participated in any of the official Raelian Academies which are held in 4 continents of Europe, Kama, America and Asia?
    $box_meals = rgar( $entry, '54' ); // Will you order BOX meals (BENTO) for lunches and dinners during the Event?
	$JRM_Transport_Service_on_December_18 = rgar( $entry, '130' ); // Will you use the Bus service, please select \u201cYes\u201d and enter details.
	$JRM_Circular_bus = rgar( $entry, '156' ); // If you use JRM Circular Bus service, please choose “Yes”
	$fullname_native_language = rgar( $entry, '166' ); // Fullname in native language
	$VISA_to_enter_japan =  rgar( $entry, '149' ); // Do you need VISA to enter Japan?
	$message = rgar ( $entry, '92' );
    $additional_information = 'Additional information:' . PHP_EOL;
	return $notification;

	return $notification;
	GFCommon::log_debug( __METHOD__ . ' LOUKESIR: $attendance: ' . $attendance  . PHP_EOL );
	
	if (date("Y-m-d") < "2024-10-26") {
		$discount_applied = True;
	}

	GFCommon::log_debug( __METHOD__ . ' LOUKESIR: step 1 ' );
	return $notification;

	if ($attendance == 'Full') {
		GFCommon::log_debug( __METHOD__ . ' LOUKESIR: $status: ' . $status  . PHP_EOL );

		$duration = 4;
		$duration_days = '2024-12-15,2024-12-16,2024-12-17,2024-12-18';

		switch ($status) {
			case 'Structure member':
				$fee = ($discount_applied ? 15200 : 17200);
				break;
			case 'Supporting Structure member (Japanese Only)':
				$fee = ($discount_applied ? 15200 : 17200);
				break;
			case 'Simple member':
				$fee = ($discount_applied ? 15200 : 17200);
				break;
			case 'Non member':
				$fee = 8000;
				break;
			case 'All under 19 (18 or younger)':
				$fee = 0;
				break;
		}
	}

	GFCommon::log_debug( __METHOD__ . ' LOUKESIR: step 2'  . PHP_EOL );
	return $notification;
	
	if ($attendance == 'Partial') {
		switch ($status) {
			case 'Structure member':
				if ($discount_applied) {
					$field = RGFormsModel::get_field( $form, 18 );
					$duration_days = str_replace( '(¥ 3,800)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
					$duration = substr_count($duration_days,',')+1;
					$fee = $duration * 4300; // discount est calculé plus loin
				}
				else {
					$field = RGFormsModel::get_field( $form, 177 );
					$duration_days = str_replace( '(¥ 4,300)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
					$duration = substr_count($duration_days,',')+1;
					$fee = $duration * 4300;
				}
				break;
			case 'Supporting Structure member (Japanese Only)':
				if ($discount_applied) {
					$field = RGFormsModel::get_field( $form, 20 );
					$duration_days = str_replace( '(¥ 3,800)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
					$duration = substr_count($duration_days,',')+1;
					$fee = $duration * 4300; // discount est calculé plus loin
				}
				else {
					$field = RGFormsModel::get_field( $form, 178 );
					$duration_days = str_replace( '(¥ 4,300)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
					$duration = substr_count($duration_days,',')+1;
					$fee = $duration * 4300;
				}
				break;
			case 'Simple member':
				if ($discount_applied) {
					$field = RGFormsModel::get_field( $form, 187 );
					$duration_days = str_replace( '(¥ 3,800)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
					$duration = substr_count($duration_days,',')+1;
					$fee = $duration * 4300; // discount est calculé plus loin
				}
				else {
					$field = RGFormsModel::get_field( $form, 23 );
					$duration_days = str_replace( '(¥ 4,300)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
					$duration = substr_count($duration_days,',')+1;
					$fee = $duration * 4300;
				}
				break;
			case 'Non member':
				$field = RGFormsModel::get_field( $form, 27 );
				$duration_days = str_replace( '(¥ 2,000)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$duration = substr_count($duration_days,',')+1;
				$fee = $duration * 2000;
				break;
            case 'Student under 26 years old or person under 18 years old':
                $fee = 0;
                break;
        }
	}

	GFCommon::log_debug( __METHOD__ . ' step 3'  . PHP_EOL );
	
	if ($hotel != 'Other hotels (Those who do not need JRM bus support)') {
		$hotel_checkin = rgar( $entry, '147' ); // Check-in date
		$hotel_checkout = rgar( $entry, '148' ); // Check-out date
	}

	if ($box_meals == 'Yes') {
		$field = RGFormsModel::get_field( $form, 51 );
		$meal_lunch_days = str_replace( '(¥ 700)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');      // Get the value selected like 2022-07-17, 2022-07-18, ....
		$field = RGFormsModel::get_field( $form, 52 );
		$meal_dinner_days = str_replace( '(¥ 1,200)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');     // Get the value selected like 2022-07-17, 2022-07-18, ....
	
		$nb_lunch = 0;
		$nb_dinner = 0;

		if ($meal_lunch_days != '') {
			$nb_lunch = substr_count($meal_lunch_days,',')+1;
		}
		if ($meal_dinner_days != '') {
			$nb_dinner = substr_count($meal_dinner_days,',')+1;
		}

		$sem_fee_lunch = $nb_lunch * 700;
		$sem_fee_dinner = $nb_dinner * 1200;
	}
	
	GFCommon::log_debug( __METHOD__ . ' step 4'  . PHP_EOL );

	if ($JRM_Transport_Service_on_December_18 == 'Yes') {
		$field = RGFormsModel::get_field( $form, 60 );
		$JRM_Transport_Service_on_December_18_destination = is_object( $field ) ? $field->get_value_export( $entry ) : ''; 

		if (str_contains($JRM_Transport_Service_on_December_18_destination, 'Nanjo City')) {
			$fee_transport += 3000;
		} elseif (str_contains($JRM_Transport_Service_on_December_18_destination, 'Naha City')) {
			$fee_transport += 2000;
		} elseif (str_contains($JRM_Transport_Service_on_December_18_destination, 'Naha Airport ')) {
			$fee_transport += 2000;
			$JRM_Transport_Service_on_December_18_departure_time = (rgar( $entry, '70' ) == '' ? '' : '2023-12-18' . ' ' .  rgar( $entry, '70' ));
			$JRM_Transport_Service_on_December_18_departure_flight_number = rgar( $entry, '71' );
			$JRM_Transport_Service_on_December_18_departure_flight_type = rgar( $entry, '140' );
		}
	}
	
    if ($JRM_Circular_bus == 'Yes') {
        $fee_transport += 4000;
		$JRM_Circular_bus_hotel = rgar( $entry, '199' ); // At which hotel will you use the JRM Circular Buses?
    }

	GFCommon::log_debug( __METHOD__ . ' step 5'  . PHP_EOL );
	
	if (!$bProxyJapan) {
		$register_as_proxy = rgar( $entry, '191' );

		if ($register_as_proxy == 'Yes') {
			$first_name_proxy = rgar( $entry, '194.3' ); 
			$last_name_proxy = rgar( $entry, '194.3' );
			$email_proxy = rgar( $entry, '195' );
			$relationship_proxy = rgar( $entry, '196' );

	        $additional_information .= '   > Submited by: ' . $first_name_proxy . ' ' . $last_name_proxy . ' (' . $email_proxy . ')' . PHP_EOL;
		}
	}
	
	if ($VISA_to_enter_japan == 'Yes') {
		$nationality_to_enter_japan =  rgar( $entry, '150' ); // ??
	}

	GFCommon::log_debug( __METHOD__ . ' step 6'  . PHP_EOL );
	
	$additional_information .= 
		'FULL NATIVE NAME' . PHP_EOL .
		'   > ' . $fullname_native_language . PHP_EOL .
		'MEALS' . PHP_EOL .
		'   > Will you order BOX meals (BENTO) for lunches and dinners during the Event? ' . $box_meals . PHP_EOL .
		'TRANSPORT' . PHP_EOL .
		'   > Circular bus service? ' . $JRM_Circular_bus . PHP_EOL .
		'   > JRM Transport Service on December 18? ' . $JRM_Transport_Service_on_December_18 . PHP_EOL;
	
	if ($JRM_Transport_Service_on_December_18 == 'Yes') {
		$additional_information .= 	'   > Destination: ' . $JRM_Transport_Service_on_December_18_destination . PHP_EOL . 
    								'   > Flight type: ' . $JRM_Transport_Service_on_December_18_departure_flight_type . PHP_EOL;
	}

	$additional_information .= 
		'ACCOMMODATION' . PHP_EOL .
		'   > Hotel: ' . $hotel . PHP_EOL;
	
	if ($hotel != 'Other hotels (Those who do not need JRM bus support)') {
		$additional_information .= 
		'   > Hotel Check-in date: ' . $hotel_checkin . PHP_EOL .
		'   > Hotel Check-out date: ' . $hotel_checkout . PHP_EOL;
	}

	$additional_information .= 
		'VISA' . PHP_EOL .
		'   > Do you need VISA to enter Japan?' . ' ' . $VISA_to_enter_japan . PHP_EOL;

	if ($VISA_to_enter_japan == 'Yes') {
		$additional_information .= '   > Nationality: ' . $nationality_to_enter_japan . PHP_EOL; // ??
	}
	
    if ($message != '') {
        $additional_information .= 'MESSAGE' . PHP_EOL . $message;
    }

    $feedback = $additional_information;

	GFCommon::log_debug( __METHOD__ . ' step 7'  . PHP_EOL );
	
	$local_timestamp = GFCommon::get_local_timestamp( time() );
	$current_date = date_i18n( 'Y-m-d', $local_timestamp, true );

	if ($discount_applied) {
        if ($attendance_prog == 'fulltime') {
            $discount = 2000;
        }
        else {
            $discount = 500 * $duration;
        }
    }
	
	$participant = array(
		'email' => rgar( $entry, '79' ), // ok2
		'firstname' => rgar( $entry, '78.3' ), // ok2
		'lastname' => rgar( $entry, '78.6' ), // ok2
		'nickname' => rgar( $entry, '80' ), // ok2
		'fullname_native' => $fullname_native_language, // ok2
		'gender' => rgar( $entry, '81' ), // ok2
		'country' => rgar( $entry, '82.1' ), // ok2
		'state' => rgar( $entry, '82.2' ), // ok2
		'suburb' => rgar( $entry, '83' ), // ok2
		'prefLanguage' => rgar( $entry, '84' ), // ok2
        'username' => rgar( $entry, '78.6' ) . '||' . rgar( $entry, '78.3' ), // ok2
		'date_birth' => rgar( $entry, '85' ), // ok2
		'understand_english' => rgar( $entry, '144' ), // ok2
		'mobile_phone' => rgar( $entry, '87' ), // ok2
		'home_phone' => '', // ok2
		'work_phone' => '', // ok2
		'sem_code' => $sem_code, // ok2
        'year' => '2024',  // ok2
        'season' => 'winter', // ok2
		'firstseminar' => ($first_participation != 'Yes, I have participated in a continental academy once or more.' ? 1 : 0), // ok2
		'student' => 0, // ok2
		'present' => 0,         // ok2
		'status' => $status, // ok2
		'fee' => $fee, // ok2
		'duration' => $duration, // ok2
		'duration_days' => str_replace(' ', '', $duration_days), // ok2
		'hotel' => $hotel, // ok2
		'room_no' => '', // ok2
		'accom_type' => $hotel, // ok2
		'accom_days' => '', // ok2
		'fee_accom' => 0, // ok2
		'parking' => 'No', // ok2
		'meal_breakfast' => '', // ok2
		'meal_lunch' => str_replace(' ', '', $meal_lunch_days), // ok2
		'meal_dinner' => str_replace(' ', '', $meal_dinner_days), // ok2
		'meal_count' => '', // See trigger I_seminar ok2
        'arr_date' => '0000-00-00 00:00:00', // ok2
        'arr_number' => '', // ok2
        'arr_location' => '', // ok2
        'dep_date' => (rgar( $entry, '70' ) == '' ? '' : '2024-12-18' . ' ' .  rgar( $entry, '70' )), // ok2
        'dep_number' => rgar( $entry, '71' ), // ok2
        'dep_location' => '', // ok2
		'translation' => rgar( $entry, '93' ), // ok2
		'transmission' => '', // ok2
        'donation' => 0,   // ok2
        'dinner' => 0, // ok2
		'fee_transport' => $fee_transport, // ok2
		'responsibility' => '', // ok
		'absent_ceremony' => 0, // ok
		'sem_feedback' => $feedback, // ok2
		'pay_type' => 'cash', // ok2
		'pay_amount' => 0.00, // ok2
		'pay_received' => 0.00, // ok2
		'pay_currency' => 'JPY', // ok2
		'fee_meals' => $sem_fee_lunch + $sem_fee_dinner, // ok2
        'ip' => GFFormsModel::get_ip(), // ok2
		'fee_discount' => $discount, // ok2
		'fee_cc' => 0.00, // ok2
		'paypal_txn' => '',     // ok2
		'paypal_status' => '',  // ok2
		'paypal_date' => '',    // ok2
		'paypal_fee' => 0.00,   // ok2
		'paypal_data' => '',    // ok2
		'survey' => '',   		// ok2
		'updateby' => 0,        // ok2
		'formtext' => ''  // ok2
    );

    foreach ($participant as $key => $value) {
		error_log( __METHOD__ . ' ' . $key. ' = ' . $value  . PHP_EOL );
		GFCommon::log_debug( __METHOD__ . ' ' . $key. ' = ' . $value  . PHP_EOL );
    }

	$language_iso = rgar( $entry, '84' );
	$language = GetLanguageDescription($language_iso);

	// Obtain list of countries and the e-mails of the respondents
	$person_service=GetService( 'person' );
	$person_token=GetToken( 'get_person_dev' );
	$options_get = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/json\r\n",
					"ignore_errors" => true, // rather read result status that failing
				)
		);
	
	$url         = $person_service . 'countries&token=' . $person_token;
	$context_get = stream_context_create( $options_get );
	$contents    = file_get_contents( $url, false, $context_get );
	$json_data   = json_decode( $contents );
	$country_name = '';
	$iso_country = rgar( $entry, '82.1' );

	// Find the email of the country concerned in the list received from Elohim.net
	foreach ( $json_data as $data ) {
		if ( $data->iso == $iso_country ) {
			$country_name = $data->nativeName;
			break;
		}
	}

	// Alert notification to the responsable
	if ( $notification['toType'] === 'email' ) {
        $fields = array(
			'First name' => $participant['firstname'],
			'Last name' => $participant['lastname'],
			'Native name' => $participant['fullname_native'],
			'Email' => $participant['email'],
			'Country' => $country_name,
			'State' => rgar( $entry, '82.2' ),
			'Language' => $language,
			'Message' => $message
			);

		$arrayFields = setNotificationArrayFields($fields); 

		// Check if a notification exist for the current language and use it as replacement
		// > Sometimes it's better to keep notifications in the database than to waste time with WPML.
		$notificationResponsable = SelectNotification(62, 'responsable', $language_iso);

		if ( 'not found' !== $notificationResponsable ) {
			$notification['message'] = $notificationResponsable;
		}

		if ($iso_country == 'jp') {
			$notification['to'] = 'asia-sem@rael.org';
		}
		else {
			$notification['to'] = 'asia-ha@rael.org';
		}

		$notification['to'] = 'loukesir@hotmail.com';
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
									$participant['prefLanguage'], 
									$participant['country'], 
									'', // area
									'', // $message, 
									62, // id form
									'', // $news_event, 
									$participant['ip'],
									$sem_code );


		$fields = array(
		 	'First name' => $participant['firstname'],
		 	'Last name' => $participant['lastname'],
			'Native name' => $participant['fullname_native'],
		 	'Nickname' => $participant['nickname'],
		 	'Birthdate' => $participant['date_birth'],
		 	'Gender' => $participant['gender'],
		 	'Email' => $participant['email'],
		 	'Country' => $country_name,
		 	'State' => rgar( $entry, '82.2' ),
			'City' => $participant['suburb'],
		 	'Language' => $language,
		 	'Understand English?' => $participant['understand_english'],
			'Mobile phone' => $participant['mobile_phone']
		 	);

		if ($participant['firstseminar'] == 1) {
			 $fields['First seminar'] = 'Yes';
		}
		else {
			 $fields['First seminar'] = 'No';
		}

		$fields['Hotel'] = $hotel;
		$fields['Fee registration without discount'] = $fee;
		$fields['Discount'] = $participant['fee_discount'];
		$fields['Fee registration with discount'] = $fee - $discount;
		$fields['Duration days'] = $duration_days;
		$fields['Lunch'] = $meal_lunch_days;
		$fields['Dinner'] = $meal_dinner_days;
		$fields['Translation'] = $participant['translation'];
		$fields['Meals fee'] = $participant['fee_meals'];
		$fields['Transport fee'] = $participant['fee_transport'];

		if (str_contains($JRM_Transport_Service_on_December_18_destination, 'Naha Airport ')) {
			$fields['Departure date'] = $participant['dep_date'];
			$fields['Departure flight number'] = $participant['dep_number'];
		}

		$arrayFields = setNotificationArrayFields($fields); 
							
		// Check if a notification exist for the current language and use it as replacement
		// > Sometimes it's better to keep notifications in the database than to waste time with WPML.
		$confirmation = SelectNotification(62, 'person', $language_iso);

		if ( 'not found' !== $confirmation ) {
			$notification['message'] = $confirmation;
		}

		$notification['bcc'] = 'loukesir@outlook.com';

		$montant_to_pay = number_format($fee + $fee_accom + $fee_transport + $sem_fee_lunch + $sem_fee_dinner - $discount, 0, ".", ",");

		if ( strstr( $notification['message'], '{montant_to_pay}' ) ) {
			$notification['message'] = str_replace('{montant_to_pay}', $montant_to_pay, $notification['message'] );
		} elseif ( strstr( $notification['message'], '%7Bmontant_to_pay%7D' ) ) {
			$notification['message'] = str_replace('%7Bmontant_to_pay%7D', $montant_to_pay, $notification['message'] );
		} else { 
			$notification['message'] = str_replace('%7bmontant_to_pay%7d', $montant_to_pay, $notification['message'] );
		}

		if ( strstr( $notification['message'], '{field_list}' ) ) {
			$notification['message'] = str_replace('{field_list}', $arrayFields, $notification['message'] );
		} elseif ( strstr( $notification['message'], '%field_list%7D' ) ) {
			$notification['message'] = str_replace('%field_list%7D', $arrayFields, $notification['message'] );
		} else { 
			$notification['message'] = str_replace('%field_list%7d', $arrayFields, $notification['message'] );
		}

	    send_participant_to_ElohimNet( $participant, $selector );
	}

    return $notification;
    
} // notification_64

add_filter( 'gform_validation', 'custom_validation_64' );
function custom_validation_64( $validation_result ) {

	$form = $validation_result['form'];
	$current_page_number = rgpost('gform_source_page_number_' . $form['id']) ? rgpost('gform_source_page_number_' . $form['id']) : 1;

	if ($current_page_number == 3) {
		$entry = GFFormsModel::get_current_lead();
		$box_meals = rgar( $entry, '54' );
		 
		if ($box_meals == 'Yes' ) {
			$field = RGFormsModel::get_field( $form, 51 );
			$meal_lunch_days = str_replace( '(¥ 700)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');      // Get the value selected like 2022-07-17, 2022-07-18, ....
			$field = RGFormsModel::get_field( $form, 52 );
			$meal_dinner_days = str_replace( '(¥ 1,200)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');     // Get the value selected like 2022-07-17, 2022-07-18, ....
		
			$nb_lunch = substr_count($meal_lunch_days,',');
			$nb_dinner = substr_count($meal_dinner_days,',');

			if (($meal_lunch_days == '') && ($meal_dinner_days == '')) {
				// set the form validation to false
				$validation_result['is_valid'] = false;

				//finding Field with ID of 1 and marking it as failed validation
				foreach( $form['fields'] as &$field ) {
		
					//NOTE: replace 1 with the field you would like to validate
					if (( $field->id == '51' ) && ($nb_lunch == 0) && ($nb_dinner == 0)) {
						$field->failed_validation = true;
						$field->validation_message = 'Choose at least 1 lunch or 1 dinner.';
						break;
					}
				}
			}
		}
	}

	//Assign modified $form object back to the validation result
    $validation_result['form'] = $form;
    return $validation_result;
}