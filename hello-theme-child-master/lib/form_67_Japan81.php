<?php

add_filter('gform_entry_is_spam_67', 'entry_is_spam_67', 10, 3);
function entry_is_spam_67($is_spam, $form, $entry) {

    // ID du champ email
    $email = rgar( $entry, '65' );

    if ($email === 'loukesir@hotmail.com') {
        return false; // jamais spam
    }

    return $is_spam; // comportement normal pour les autres
}

// ----------------------------------------------------
// > The gform_pre_render filter is executed before the 
//   form is displayed and can be used to manipulate 
//   the Form Object prior to rendering the form.
// ----------------------------------------------------

add_filter( 'gform_pre_render_67', 'pre_render_67' );
function pre_render_67( $form ) {
	$GLOBALS['raelorg_session_ID'] = GetUniqueIdSession();
	$GLOBALS['raelorg_ip_address'] = GFFormsModel::get_ip();
	$GLOBALS['raelorg_country_from_ip'] = "";
	$GLOBALS['raelorg_countries'] = array();

	GFCommon::log_debug( __METHOD__ . '->pre_render DEBUG ' );
	
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
	
	$country_iso_from_ip = GetCountryCodeFromIP(GFFormsModel::get_ip());
	$language_iso = apply_filters( 'wpml_current_language', NULL );

	$options_get = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/json\r\n",
			"ignore_errors" => true, // rather read result status that failing
		)
	);

    $country_iso_from_ip = GetCountryCodeFromIP(GFFormsModel::get_ip());
	$language_iso = apply_filters( 'wpml_current_language', NULL );
	
	// Fill fields
	foreach ( $form['fields'] as $field )  {

		switch ( $field->id ) {
			case '69': // Country & Province
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

			case 72: // Prefered Language
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

			case '113': // BENTO
				if (date("Y-m-d") > "2026-07-31") {
					$field->defaultValue = "false";
				}
			break;
		} // switch
	} // foreach

	error_log( __METHOD__ . '->pre_render return DEBUG' . PHP_EOL );
	
	return $form;
} // pre_render_67

// -----------------------------------------------------------------------
// Fill in the Country field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_67_69_1', 'contact_us_populate_country_67', 10, 7 );
function contact_us_populate_country_67( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	return $GLOBALS['raelorg_countries'];
	
} // contact_us_populate_country_67

// -----------------------------------------------------------------------
// Fill in the Province field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_67_69_2', 'contact_us_populate_province_67', 10, 7 );
function contact_us_populate_province_67( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {
	global $wpdb;

	$selected_iso_country = $chain_value[ "{$field->id}.1" ];

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

} // contact_us_populate_province_67

add_action( 'gform_after_submission_67', 'after_submission_67', 10, 2 );
function after_submission_67 ( $entry, $form ) {
	// Get the post
    $post = get_post( $entry['post_id'] );
}

// -----------------------------------------------------
// Modify a notification object before it is converted into an email and sent
// > Send a notification to the event manager
// > Send a notification to the participant
// > Send the participant in Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_67', 'notification_67', 10, 3 );
function notification_67( $notification, $form, $entry ) {
	// IMPORTANT - Pourquoi ne pas utiliser des "codes" au lieu de la description des valeurs ?

	// Réponse : Parce-que lorsqu'on utilise rgar pour obtenir la valeur choisie, il faut
	// de toute façon reconvertir en texte pour envoyer le texte en anglais dans Elohim.net
	// et dans la notification envoyée au responsable. De plus, en mettant un code, on doit
	// utiliser une nomenclature et cela rend plus complexe le lien entre code, programmation 
	// et reconversion en texte.
	
	// Les conditions logiques du formulaire sont effectuées sur l'attribut "value" . Il faut
	// donc s'assurer que tous les attributs "value" associés aux conditions logiques n'ont
	// pas été traduites par WPML.

	GFCommon::log_debug( __METHOD__ . '**************** LOUKESIR DEBUG ******************' );

	$status = '';
	$elohimNet_registered = '';
	$send_portrait = 'No';
	$age = 0;
	$attendance = '';
	$sem_code = 163;
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
	$JRM_Circular_bus = '';
	$register_as_proxy = '';
	$name_proxy = '';
	$email_proxy = '';
	$fullname_native_language = '';
	$VISA_to_enter_japan = '';
	$nationality_to_enter_japan = '';
	$additional_information = '';
    $roomates = '';

	// NOTE : Le traitement est fait en suivant l'ordre des pages et des champs dans le formulaire.
	// 

	// Page 1
	$status = rgar( $entry, '1' ); // Please choose your status
	$first_participation = rgar( $entry, '3' ); // Have you participated in any of the official Raelian Academies which are held in 4 continents of Europe, Kama, America and Asia?
	$elohimNet_registered = rgar( $entry, '115' ); // Are you registered in the “Elohim.NET” together with your portrait photo?
	//$send_portrait = rgar( $entry, '116' ); // Will you please send your portrait photo (in full colors\/above shoulder) // rgar pas nécessaire
	$attendance = rgar( $entry, '4' ); // Which date will you participate?  Full, Partial

	if ($attendance == 'Full') {
        error_log( __METHOD__ . ' LOUKESIR: attendance == full ' . PHP_EOL );

		$duration = 7;
		$duration_days = '2026-09-26,2026-09-27,2026-09-28,2026-09-29,2026-09-30,2026-10-01,2026-10-02';

		switch ($status) {
			case 'Structure member':
				$fee = 24000;
				break;
			case 'Supporting Structure member (Japanese Only)':
				$fee = 24000;
				break;
			case 'Simple member':
				$fee = 24000;
				break;
			case 'Non member':
				$fee = 13800;
				break;
			case 'All under 19 (18 or younger)':
				$fee = 0;
				break;
		}
	}

	if ($attendance == 'Partial') {
        switch ($status) {
			case 'Structure member':
                $field = RGFormsModel::get_field( $form, 16 );
				$raw_value = is_object( $field ) ? $field->get_value_export( $entry ) : ''; // $raw_value = 2026-09-26, 2026-09-27 (¥ 4,000), 2026-09-28 (¥ 4,000), 2026-09-29 (¥ 4,000)
				$duration_days = str_replace( '(¥ 4,000)', '', $raw_value );
                $duration = substr_count($duration_days,',')+1;
                $fee = $duration * 4000;
				if (strpos($raw_value, '2026-09-26') !== false) {
 				   $fee = $fee - 4000; // Ne pas compter le 26 septembre
				}
			break;
			case 'Supporting Structure member (Japanese Only)':
                $field = RGFormsModel::get_field( $form, 19 );
				$raw_value = is_object( $field ) ? $field->get_value_export( $entry ) : ''; // $raw_value = 2026-09-26, 2026-09-27 (¥ 4,000), 2026-09-28 (¥ 4,000), 2026-09-29 (¥ 4,000)
				$duration_days = str_replace( '(¥ 4,000)', '', $raw_value );
                $duration = substr_count($duration_days,',')+1;
                $fee = $duration * 4000;
				if (strpos($raw_value, '2026-09-26') !== false) {
 				   $fee = $fee - 4000; // Ne pas compter le 26 septembre
				}
			break;
			case 'Simple member':
                $field = RGFormsModel::get_field( $form, 21 );
				$raw_value = is_object( $field ) ? $field->get_value_export( $entry ) : ''; // $raw_value = 2026-09-26, 2026-09-27 (¥ 4,000), 2026-09-28 (¥ 4,000), 2026-09-29 (¥ 4,000)
				$duration_days = str_replace( '(¥ 4,000)', '', $raw_value );
                $duration = substr_count($duration_days,',')+1;
                $fee = $duration * 4000;
				if (strpos($raw_value, '2026-09-26') !== false) {
 				   $fee = $fee - 4000; // Ne pas compter le 26 septembre
				}
			break;
			case 'Non member': 
                $field = RGFormsModel::get_field( $form, 23 );
				$raw_value = is_object( $field ) ? $field->get_value_export( $entry ) : ''; // $raw_value = 2026-09-26, 2026-09-27 (¥ 2,300), 2026-09-28 (¥ 2,300), 2026-09-29 (¥ 2,300)
				$duration_days = str_replace( '(¥ 2,300)', '', $raw_value );
				$duration = substr_count($duration_days,',')+1;
				$fee = $duration * 2300;				
				if (strpos($raw_value, '2026-09-26') !== false) {
 				   $fee = $fee - 2300; // Ne pas compter le 26 septembre
				}
			break;
            case 'All under 19 (18 or younger)':
				$field = RGFormsModel::get_field( $form, 24 );
				$raw_value = is_object( $field ) ? $field->get_value_export( $entry ) : ''; // $raw_value = 2026-09-26, 2026-09-27, 2026-09-28, 2026-09-29
				$duration_days = str_replace( ' ', '', $raw_value );
				$duration = substr_count($duration_days,',')+1;
                $fee = 0;
            break;
        }
	}

	// Page 2
	$hotel = rgar( $entry, '29' ); // The hotel you reserve

	if ($hotel != 'Other hotels (Those who do not need JRM bus support)') {
		$hotel_checkin = rgar( $entry, '30' ); // Check-in date
		$hotel_checkout = rgar( $entry, '31' ); // Check-out date
	    $roomates = rgar( $entry, '106' );
	}
	
	// Page 3
	$box_meals = rgar( $entry, '34' ); // Will you order BOX meals (BENTO) for lunches and dinners during the Event?

	if ($box_meals == 'Yes') {
		$field = RGFormsModel::get_field( $form, 37 );
		$meal_lunch_days = str_replace( '(¥ 850)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');      // Get the value selected like 2022-07-17, 2022-07-18, ....
		$field = RGFormsModel::get_field( $form, 38 );
		$meal_dinner_days = str_replace( '(¥ 1,200)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');     // Get the value selected like 2022-07-17, 2022-07-18, ....
	
		$nb_lunch = 0;
		$nb_dinner = 0;

		if ($meal_lunch_days != '') {
			$nb_lunch = substr_count($meal_lunch_days,',')+1;
		}
		if ($meal_dinner_days != '') {
			$nb_dinner = substr_count($meal_dinner_days,',')+1;
		}

		$sem_fee_lunch = $nb_lunch * 850;
		$sem_fee_dinner = $nb_dinner * 1200;
	}
	
	// Page 4
	$JRM_Circular_bus = rgar( $entry, '111' ); // If you use JRM Circular Bus service, please choose “Yes”

	if (str_contains($JRM_Circular_bus, 'Yes')) {
        $fee_transport += 7000;
        $JRM_Circular_bus = 'Yes'; // Remplacer "Yes (¥ 7,000)|7000" par "Yes"
    }
	else {
		$JRM_Circular_bus = 'No';
	}
	
	// Page 5: Identification
    $fullname_native_language = rgar( $entry, '63' ); // Fullname in native language
	$age = rgar( $entry, '125' );
    $register_as_proxy = rgar( $entry, '99' );
	
	// Page 6 & 7
    $VISA_to_enter_japan =  rgar( $entry, '84' ); // Do you need VISA to enter Japan?
    $message = rgar ( $entry, '88' );
	
	// Additional information
    $additional_information = 'Additional information:' . PHP_EOL;

    if ($register_as_proxy == 'No') {
        $name_proxy = rgar( $entry, '102' ); 
        $email_proxy = rgar( $entry, '100' );

        $additional_information .= '   > Submited by: ' . $name_proxy . ' (' . $email_proxy . ')' . PHP_EOL;
    }
	
	if ($VISA_to_enter_japan == 'Yes') {
		$nationality_to_enter_japan =  rgar( $entry, '86' );
	}
	
	if ($elohimNet_registered != 'Yes') { // No or not sure
		$send_portrait = 'Yes'; // Forcer Yes 
	}

	$additional_information .= 
		'ELOHIM.NET STATUS' . PHP_EOL .
		'   > Are you registered in the Elohim.NET together with your portrait photo? ' . $elohimNet_registered . PHP_EOL .
		'   > Will you please send your portrait? ' . $send_portrait . PHP_EOL .
		'IDENTIFICATION' . PHP_EOL .
		'   > Full native name: ' . $fullname_native_language . PHP_EOL .
		'   > Age: ' . $age . PHP_EOL .
		'MEALS' . PHP_EOL .
		'   > Will you order BOX meals (BENTO) for lunches and dinners during the Event? ' . $box_meals . PHP_EOL .
		'TRANSPORT' . PHP_EOL .
		'   > Circular bus service? ' . $JRM_Circular_bus . PHP_EOL;
	
	$additional_information .= 
		'ACCOMMODATION' . PHP_EOL .
		'   > Hotel: ' . $hotel . PHP_EOL;
	
	if ($hotel != 'Other hotels (Those who do not need JRM Circular Bus service)') {
		$additional_information .= 
        '   > Roomates: ' . $roomates . PHP_EOL .
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

	$participant = array(
		'email' => rgar( $entry, '65' ), // ok2
		'firstname' => rgar( $entry, '62.3' ), // ok2
		'lastname' => rgar( $entry, '62.6' ), // ok2
		'nickname' => rgar( $entry, '66' ), // ok2
		'fullname_native' => $fullname_native_language, // ok2
		'gender' => rgar( $entry, '67' ), // ok2
		'country' => rgar( $entry, '69.1' ), // ok2
		'state' => rgar( $entry, '69.2' ), // ok2
		'suburb' => rgar( $entry, '71' ), // ok2
		'prefLanguage' => rgar( $entry, '72' ), // ok2
        'username' => rgar( $entry, '62.6' ) . '||' . rgar( $entry, '62.3' ), // ok2
		'date_birth' => '', // ok2
		'understand_english' => rgar( $entry, '75' ), // ok2
		'mobile_phone' => rgar( $entry, '77' ), // ok2
		'home_phone' => '', // ok2
		'work_phone' => '', // ok2
		'sem_code' => $sem_code, // ok2
        'year' => '2026',  // ok2
        'season' => 'automn', // ok2
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
        'dep_date' => $JRM_Transport_Service_on_departure_time, // ok2
        'dep_number' => $JRM_Transport_Service_on_departure_flight_number, // ok2
        'dep_location' => '', // ok2
		'translation' => rgar( $entry, '83' ), // ok2
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

//    foreach ($participant as $key => $value) {
//		error_log( __METHOD__ . ' ' . $key. ' = ' . $value  . PHP_EOL );
//    }
    
//	$notification['to'] = 'loukesir@hotmail.com'; // debug
//	return $notification; // debug

	$language_iso = rgar( $entry, '72' );
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
	$iso_country = rgar( $entry, '69.1' );

	// Find the email of the country concerned in the list received from Elohim.net
	foreach ( $json_data as $data ) {
		if ( $data->iso == $iso_country ) {
			$country_name = $data->nativeName;
			break;
		}
	}

	// Alert notification to the responsable
	// Ne pas envoyé cette notification dans une langue étrangère du responsable
	if ( $notification['toType'] === 'email' ) {
        $fields = array(
			'First name' => $participant['firstname'],
			'Last name' => $participant['lastname'],
			'Native name' => $participant['fullname_native'],
			'Email' => $participant['email'],
			'Country' => $country_name,
			'State' => rgar( $entry, '69.2' ),
			'Language' => $language,
			'Message' => $message
			);

		$arrayFields = setNotificationArrayFields($fields); 

		// Check if a notification exist for the current language and use it as replacement
		// > Sometimes it's better to keep notifications in the database than to waste time with WPML.
		$language_iso = apply_filters( 'wpml_current_language', NULL );
		$notificationResponsable = SelectNotification(67, 'responsable', $language_iso);

		if ( 'not found' !== $notificationResponsable ) {
			$notification['message'] = $notificationResponsable;
		}

		if ($iso_country == 'jp') {
			$notification['to'] = 'asia-sem@rael.org';
		}
		else {
			$notification['to'] = 'asia-ha@rael.org';
		}

		// Si c'est moi qui fait un test, ne pas envoyer le courriel au responsable mais plutôt à moi-même
		if ($participant['email'] == 'loukesir@hotmail.com') {
			$notification['to'] = 'loukesir@hotmail.com'; // debug
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
									$participant['prefLanguage'], 
									$participant['country'], 
									'', // area
									'', // $message, 
									67, // id form
									'', // $news_event, 
									$participant['ip'],
									$sem_code );


		$fields = array(
		 	'First name' => $participant['firstname'],
		 	'Last name' => $participant['lastname'],
			'Native name' => $participant['fullname_native'],
		 	'Nickname' => $participant['nickname'],
		 	'Gender' => $participant['gender'],
		 	'Email' => $participant['email'],
		 	'Country' => $country_name,
		 	'State' => rgar( $entry, '69.2' ),
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
        $fields['Roomates'] = $roomates;
		$fields['Fee registration'] = $fee;
		$fields['Duration days'] = $duration_days;
		$fields['Lunch'] = $meal_lunch_days;
		$fields['Dinner'] = $meal_dinner_days;
		$fields['Translation'] = $participant['translation'];
		$fields['Meals fee'] = $participant['fee_meals'];
		$fields['Transport fee'] = $participant['fee_transport'];

		$arrayFields = setNotificationArrayFields($fields); 
							
		// Check if a notification exist for the current language and use it as replacement
		// > Sometimes it's better to keep notifications in the database than to waste time with WPML.
		$language_iso = apply_filters( 'wpml_current_language', NULL );
		$confirmation = SelectNotification(67, 'person', $language_iso);

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
    
} // notification_67

add_filter( 'gform_validation_67', 'custom_validation_67' );
function custom_validation_67( $validation_result ) {

	$form = $validation_result['form'];
	$current_page_number = rgpost('gform_source_page_number_' . $form['id']) ? rgpost('gform_source_page_number_' . $form['id']) : 1;

	if ($current_page_number == 5) {
		$entry = GFFormsModel::get_current_lead();
		$age = rgar( $entry, '125' );

		foreach ( $form['fields'] as &$field ) {

			// Age
			if ( $field->id == 125 ) {
				$valeur = rgpost( "input_{$field->id}" );

				// Validation : doit être numérique ET entre 0 et 100
				if ( !is_numeric( $valeur ) || $valeur < 0 || $valeur > 100 ) {
					$field->failed_validation = true;
					$field->validation_message = 'Please enter a number between 0 and 100.';
					$validation_result['is_valid'] = false;
				}
			}
        }
	}

	if ($current_page_number == 3) {
		$entry = GFFormsModel::get_current_lead();
		$box_meals = rgar( $entry, '34' );
		 
		if ($box_meals == 'Yes' ) {
			$field = RGFormsModel::get_field( $form, 37 );
			$meal_lunch_days = str_replace( '(¥ 850)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');      // Get the value selected like 2022-07-17, 2022-07-18, ....
			$field = RGFormsModel::get_field( $form, 38 );
			$meal_dinner_days = str_replace( '(¥ 1,200)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');     // Get the value selected like 2022-07-17, 2022-07-18, ....
		
			$nb_lunch = substr_count($meal_lunch_days,',');
			$nb_dinner = substr_count($meal_dinner_days,',');

			if (($meal_lunch_days == '') && ($meal_dinner_days == '')) {
				// set the form validation to false
				$validation_result['is_valid'] = false;

				//finding Field with ID of 1 and marking it as failed validation
				foreach( $form['fields'] as &$field ) {
					if (( $field->id == '37' ) && ($nb_lunch == 0) && ($nb_dinner == 0)) {
						$field->failed_validation = true;
						$field->validation_message = 'Choose at least 1 lunch or 1 dinner.';
						break;
					}
					if (( $field->id == '38' ) && ($nb_lunch == 0) && ($nb_dinner == 0)) {
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