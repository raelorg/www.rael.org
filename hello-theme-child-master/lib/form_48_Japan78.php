<?php

// ----------------------------------------------------
// > The gform_pre_render filter is executed before the 
//   form is displayed and can be used to manipulate 
//   the Form Object prior to rendering the form.
// ----------------------------------------------------

add_filter( 'gform_pre_render_48', 'pre_render_48' );
function pre_render_48( $form ) {
	$GLOBALS['raelorg_session_ID'] = GetUniqueIdSession();
	$GLOBALS['raelorg_ip_address'] = GFFormsModel::get_ip();
	$GLOBALS['raelorg_country_from_ip'] = "";
	$GLOBALS['raelorg_countries'] = array();

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

			case 76: // Email
				if ( $found ) {
					$field->inputs[0]['defaultValue'] = $participant->email;
					$field->inputs[1]['defaultValue'] = $participant->email;
					?>
    				<script type="text/javascript">
        				jQuery(document).ready(function(){
							jQuery("#input_48_76").attr("readonly", "readonly");
							jQuery("#input_48_76_2").attr("readonly", "readonly");
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

		} // switch
	} // foreach

	return $form;
} // pre_render_48

// -----------------------------------------------------------------------
// Fill in the Country field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_48_82_1', 'contact_us_populate_country_48', 10, 7 );
function contact_us_populate_country_48( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	//InsertFormsLog( $GLOBALS['raelorg_session_ID'], '41', 'Country', $GLOBALS['raelorg_country_from_ip'], $GLOBALS['raelorg_ip_address'], 'N/A' );

	return $GLOBALS['raelorg_countries'];
	
} // contact_us_populate_country_48

// -----------------------------------------------------------------------
// Fill in the Province field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_48_82_2', 'contact_us_populate_province_48', 10, 7 );
function contact_us_populate_province_48( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {
	global $wpdb;

	$selected_iso_country = $chain_value[ "{$field->id}.1" ];

	// coucou
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

} // contact_us_populate_province_48

// -----------------------------------------------------
// Modify a notification object before it is converted into an email and sent
// > Send a notification to the event manager
// > Send a notification to the participant
// > Send the participant in Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_48', 'notification_48', 10, 3 );
function notification_48( $notification, $form, $entry ) {

	$sem_code = 158;
	$discount = 0;
	$fee = 0.0;
    $fee_transport = 0;
	$fee_accom = 0.0;
	$duration = 0;
	$duration_days = '';
	$attendance_type = (rgar( $entry, '3' ) == 'Yes' ? 'perday' : 'fulltime');
	$attendance = rgar( $entry, '2' );
	$sem_fee_lunch = 0;
	$sem_fee_dinner = 0;
	$fee_gala_dinner = 0;
	$meal_lunch_days = '';
	$meal_dinner_days = '';

	$accom_days = ''; 
	$accom_type = rgar( $entry, '32' );
    $accom_type2 = '';

    $box_meals = rgar( $entry, '54' );
    $circular_bus = rgar( $entry, '112' );
    $flight_type = rgar( $entry, '140' );

	$you = rgar ( $entry, '75' );
	$email_not_you = rgar ( $entry, '76' );
	$name_not_you = rgar ( $entry, '77' );
	$message = rgar ( $entry, '92' );
	$self_checkin = rgar ( $entry, '131' );
	$self_checkout = rgar ( $entry, '132' );
	$roommate = rgar ( $entry, '33' );
	$additional_information = '';

    // Transport
    $circular_bus_yes = rgar ( $entry, '112' );
    $bus_after16_yes = rgar ( $entry, '130' );
    
    if ($circular_bus_yes == 'Yes') {
        $fee_transport += 3000;
    }

    $field = RGFormsModel::get_field( $form, 60 );
    $bus_after16 = is_object( $field ) ? $field->get_value_export( $entry ) : ''; 

    if ($bus_after16_yes == 'Yes') {
        if (str_contains($bus_after16, 'Nanjo City')) {
            $fee_transport += 3000;
        } elseif (str_contains($bus_after16, 'Naha City')) {
            $fee_transport += 2000;
        } elseif (str_contains($bus_after16, 'Naha Airport ')) {
            $fee_transport += 2000;
        }
    }

    $additional_information = 'Additional information:' . PHP_EOL;

	if ($you == 'No') {
        $additional_information .= '   > Submited by: ' . $name_not_you . ' (' . $email_not_you . ')' . PHP_EOL;
    }

	if ($roommate != '') {
        $additional_information .= '   > Roommate: ' . $roommate . PHP_EOL;
    }

	if ($accom_type == 'Self reservation 1 (KARIYUSHI Resort Hotel)') {
        $additional_information .= '   > Check-in (self reservation 1): ' . $self_checkin . PHP_EOL;
        $additional_information .= '   > Check-out (self reservation 1): ' . $self_checkout . PHP_EOL;
	}

	$additional_information .= 
    '   > Will you order BOX meals (BENTO) for lunches and dinners during the Event? ' . $box_meals . PHP_EOL .
    '   > Would you like to take advantage of the circular bus service between the event hall and the hotel? ' . $circular_bus_yes . PHP_EOL .
    '   > JRM will charter buses on Dec 16 after the Event finishes. Would you like to take advantage of transport? ' . $bus_after16_yes . ' ' . $bus_after16 . PHP_EOL . 
    '   > Departure flight type: ' . $flight_type . PHP_EOL . PHP_EOL;

    if ($message != '') {
        $additional_information .= 'MESSAGE:';
    }

    $feedback = $additional_information . PHP_EOL . $message;

	if ($attendance_type == 'fulltime') {
		$duration = 8;
		$duration_days = '2023-12-09,2023-12-10,2023-12-11,2023-12-12,2023-12-13,2023-12-14,2023-12-15,2023-12-16';

		// Attendance fulltime
		switch ($attendance) {
			case 'Structure member':
				$fee = GFCommon::to_number( rgar( $entry, '4.2' ) );
				break;
			case 'Supporting Structure member (Japanese Only)':
				$fee = GFCommon::to_number( rgar( $entry, '6.2' ) );
				break;
			case 'Simple member':
				$fee = GFCommon::to_number( rgar( $entry, '8.2' ) );
				break;
			case 'Newcomer':
				$fee = GFCommon::to_number( rgar( $entry, '11.2' ) );
				break;
			case 'Non member':
				$fee = GFCommon::to_number( rgar( $entry, '13.2' ) );
				break;
            case 'Student under 26 years old or person under 18 years old':
                $fee = 0;
                break;
            }
	}
	else {
		// Attendance per day
		switch ($attendance) {
			case 'Structure member':
				$field = RGFormsModel::get_field( $form, 18 );
				$duration_days = str_replace( '(¥ 3,800)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$duration = substr_count($duration_days,',')+1;
				$fee = $duration * 3800;
				break;
			case 'Supporting Structure member (Japanese Only)':
				$field = RGFormsModel::get_field( $form, 20 );
				$duration_days = str_replace( '(¥ 3,800)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$duration = substr_count($duration_days,',')+1;
				$fee = $duration * 3800;
				break;
			case 'Simple member':
				$field = RGFormsModel::get_field( $form, 23 );
				$duration_days = str_replace( '(¥ 2,300)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$duration = substr_count($duration_days,',')+1;
				$fee = $duration * 2300;
				break;
			case 'Newcomer':
				$field = RGFormsModel::get_field( $form, 25 );
				$duration_days = str_replace( '(¥ 2,300)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$duration = substr_count($duration_days,',')+1;
				$fee = $duration * 2300;
				break;
			case 'Non member':
				$field = RGFormsModel::get_field( $form, 27 );
				$duration_days = str_replace( '(¥ 2,300)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$duration = substr_count($duration_days,',')+1;
				$fee = $duration * 2300;
				break;
            case 'Student under 26 years old or person under 18 years old':
                $fee = 0;
                break;
        }
	}

    // Accommodation
    switch ($accom_type) {
        case 'Room for 1 person (¥ 15,500 per night)':
            $field = RGFormsModel::get_field( $form, 35 );
            $accom_days = str_replace( '(¥ 15,500)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
            $nb_accom_days = substr_count($accom_days,',')+1;
            $fee_accom = $nb_accom_days * 15500;
            $accom_type2 = 'Room for 1 person (JPY 15,500 per night)';
            break;
        case 'Room for 2 persons (¥ 7,300 per night)':
            $field = RGFormsModel::get_field( $form, 36 );
            $accom_days = str_replace( '(¥ 7,300)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
            $nb_accom_days = substr_count($accom_days,',')+1;
            $fee_accom = $nb_accom_days * 7300;
            $accom_type2 = 'Room for 2 persons (JPY 7,300 per night)';
            break;
        case 'Room for 3 persons (¥ 6,800 per night)':
            $field = RGFormsModel::get_field( $form, 39 );
            $accom_days = str_replace( '(¥ 6,800)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
            $nb_accom_days = substr_count($accom_days,',')+1;
            $fee_accom = $nb_accom_days * 6800;
            $accom_type2 = 'Room for 3 persons (JPY 6,800 per night)';
            break;
        case 'Room for 4 persons (¥ 6,700 per night)':
            $field = RGFormsModel::get_field( $form, 41 );
            $accom_days = str_replace( '(¥ 6,700)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
            $nb_accom_days = substr_count($accom_days,',')+1;
            $fee_accom = $nb_accom_days * 6700;
            $accom_type2 = 'Room for 4 persons (JPY 6,700 per night)';
            break;
        default:
            $fee_accom = 0;
            $accom_type2 = $accom_type;
            break;
    }

	// Meal
	if ($box_meals == 'Yes') {
		$field = RGFormsModel::get_field( $form, 51 );
		$meal_lunch_days = str_replace( '(¥ 600)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');      // Get the value selected like 2022-07-17, 2022-07-18, ....
		$field = RGFormsModel::get_field( $form, 52 );
		$meal_dinner_days = str_replace( '(¥ 1,000)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');     // Get the value selected like 2022-07-17, 2022-07-18, ....
	
		$nb_lunch = 0;
		$nb_dinner = 0;

		if ($meal_lunch_days != '') {
			$nb_lunch = substr_count($meal_lunch_days,',')+1;
		}
		if ($meal_dinner_days != '') {
			$nb_dinner = substr_count($meal_dinner_days,',')+1;
		}

		$sem_fee_lunch = $nb_lunch * 600;
		$sem_fee_dinner = $nb_dinner * 1000;
	}

	$local_timestamp = GFCommon::get_local_timestamp( time() );
	$current_date = date_i18n( 'Y-m-d', $local_timestamp, true );

	if ($current_date <= '2023-10-20') {
        if ($attendance_type == 'fulltime') {
            $discount = 4000;
        }
        else {
            $discount = 500 * $duration;
        }
    }
    
    $accom_type = str_replace( '¥ ', '', $accom_type);
	$pay_amount = $fee + $fee_accom + $fee_transport + $sem_fee_lunch + $sem_fee_dinner - $discount;

	$participant = array(
		'email' => rgar( $entry, '79' ), // ok
		'firstname' => rgar( $entry, '78.3' ), // ok
		'lastname' => rgar( $entry, '78.6' ), // ok
		'nickname' => rgar( $entry, '80' ), // ok
		'fullname_native' => '', // ok
		'country' => rgar( $entry, '82.1' ), // ok
		'state' => rgar( $entry, '82.2' ), // ok
		'suburb' => rgar( $entry, '83' ), // ok
		'prefLanguage' => rgar( $entry, '84' ), // ok
        'username' => rgar( $entry, '78.6' ) . '||' . rgar( $entry, '78.3' ), // ok
		'mobile_phone' => rgar( $entry, '87' ), // ok
		'home_phone' => '', // ok
		'work_phone' => '', // ok
		'date_birth' => rgar( $entry, '85' ), // ok
		'gender' => rgar( $entry, '81' ), // ok
		'understand_english' => rgar( $entry, '144' ), // ok
		'sem_code' => $sem_code, // ok
        'year' => '2023',  // ok
        'season' => 'winter', // ok
		'firstseminar' => (rgar( $entry, '2' ) == 'Newcomer' ? 1 : 0), // ok
		'student' => (rgar( $entry, '2' ) == 'Student under 26 years old or person under 18 years old' ? 1 : 0), // ok
		'present' => 0,         // ok
		'status' => $attendance, // ok
		'fee' => $fee, // ok    
		'duration' => $duration, // ok
		'duration_days' => str_replace(' ', '', $duration_days), // ok
		'hotel' => '', // ok
		'room_no' => '', // ok
		'accom_type' => $accom_type2, // ok
		'accom_days' => str_replace(' ', '', $accom_days), // ok
		'fee_accom' => $fee_accom, // ok
		'parking' => 'No', // ok
		'meal_breakfast' => '', // ok
		'meal_lunch' => str_replace(' ', '', $meal_lunch_days), // ok
		'meal_dinner' => str_replace(' ', '', $meal_dinner_days), // ok
		'meal_count' => '', // See trigger I_seminar ok
        'arr_date' => '0000-00-00 00:00:00', // ok
        'arr_number' => '', // ok
        'arr_location' => '', // ok
        'dep_date' => (rgar( $entry, '70' ) == '' ? '' : '2023-12-16' . ' ' .  rgar( $entry, '70' )), // ok
        'dep_number' => rgar( $entry, '71' ), // ok
        'dep_location' => '', // ok
		'translation' => rgar( $entry, '93' ), // ok
		'transmission' => '', // ok
        'donation' => 0,   // ok
        'dinner' => 0, // ok
		'fee_transport' => $fee_transport, // ok
		'responsibility' => '', // ok
		'absent_ceremony' => 0, // ok
		'sem_feedback' => $feedback, // ok
		'pay_type' => 'cash', // ok
		'pay_amount' => $pay_amount, // ok
		'pay_received' => 0.00, // ok
		'pay_currency' => 'JPY', // ok
		'fee_meals' => $sem_fee_lunch + $sem_fee_dinner, // ok
        'ip' => GFFormsModel::get_ip(), // ok
		'fee_discount' => $discount, // ok
		'fee_cc' => 0.00, // ok
		'paypal_txn' => '',     // ok
		'paypal_status' => '',  // ok
		'paypal_date' => '',    // ok
		'paypal_fee' => 0.00,   // ok
		'paypal_data' => '',    // ok
		'survey' => '',   		// ok
		'updateby' => 0,        // ok
		'formtext' => $roommate  // ok
    );

    foreach ($participant as $key => $value) {
		error_log( __METHOD__ . ' ' . $key. ' = ' . $value  . PHP_EOL );
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
			'Email' => $participant['email'],
			'Country' => $country_name,
			'State' => rgar( $entry, '82.2' ),
			'Language' => $language,
			'Message' => $message
			);

		$arrayFields = setNotificationArrayFields($fields); 

		// Check if a notification exist for the current language and use it as replacement
		// > Sometimes it's better to keep notifications in the database than to waste time with WPML.
		$notificationResponsable = SelectNotification(41, 'responsable', $language_iso);

		if ( 'not found' !== $notificationResponsable ) {
			$notification['message'] = $notificationResponsable;
		}

		if ($iso_country == 'jp') {
			$notification['to'] = 'asia-sem@rael.org';
		}
		else {
			$notification['to'] = 'asia-ha@rael.org';
		}

		//$notification['to'] = 'loukesir@hotmail.com';
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
									48, // id form
									'', // $news_event, 
									$participant['ip'],
									$sem_code );


		$fields = array(
		 	'First name' => $participant['firstname'],
		 	'Last name' => $participant['lastname'],
		 	'Nickname' => $participant['nickname'],
		 	'Birthdate' => $participant['date_birth'],
		 	'Gender' => $participant['gender'],
		 	'Email' => $participant['email'],
		 	'Country' => $country_name,
		 	'State' => rgar( $entry, '82.2' ),
			'Suburb' => $participant['suburb'],
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

		if ($participant['student'] == 1) {
			 $fields['Student'] = 'Yes';
		}
		else {
			 $fields['Student'] = 'No';
		}

		$fields['Attendance'] = $attendance;
		$fields['Fee'] = $fee;
		$fields['Duration days'] = $duration_days;
		$fields['Accommodation type'] = $accom_type2;
		$fields['Accommodation nights'] = $participant['accom_days'];
		$fields['Accommodation fee'] = $participant['fee_accom'];
		$fields['Lunch'] = $meal_lunch_days;
		$fields['Dinner'] = $meal_dinner_days;
		$fields['Translation'] = $participant['translation'];
		$fields['Meals fee'] = $participant['fee_meals'];
		$fields['Transport fee'] = $participant['fee_transport'];
		$fields['Discount'] = $participant['fee_discount'];

		if (str_contains($bus_after16, 'Naha Airport ')) {
			$fields['Departure date'] = $participant['dep_date'];
			$fields['Departure flight number'] = $participant['dep_number'];
		}

		$arrayFields = setNotificationArrayFields($fields); 
							
		// Check if a notification exist for the current language and use it as replacement
		// > Sometimes it's better to keep notifications in the database than to waste time with WPML.
		$confirmation = SelectNotification(48, 'person', $language_iso);

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
    
} // notification_48

add_filter( 'gform_validation', 'custom_validation_48' );
function custom_validation_48( $validation_result ) {

	$form = $validation_result['form'];
	$current_page_number = rgpost('gform_source_page_number_' . $form['id']) ? rgpost('gform_source_page_number_' . $form['id']) : 1;

	if ($current_page_number == 3) {
		$entry = GFFormsModel::get_current_lead();
		$box_meals = rgar( $entry, '54' );
		 
		if ($box_meals == 'Yes' ) {
			$field = RGFormsModel::get_field( $form, 51 );
			$meal_lunch_days = str_replace( '(¥ 600)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');      // Get the value selected like 2022-07-17, 2022-07-18, ....
			$field = RGFormsModel::get_field( $form, 52 );
			$meal_dinner_days = str_replace( '(¥ 1,000)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');     // Get the value selected like 2022-07-17, 2022-07-18, ....
		
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