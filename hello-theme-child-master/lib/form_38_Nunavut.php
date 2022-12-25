<?php

// ----------------------------------------------------------------------------
// Give the discount if condition apply
// ----------------------------------------------------------------------------
function compute_discount( $value ) {
	$discount = 0;

	$local_timestamp = GFCommon::get_local_timestamp( time() );
	$current_date = date_i18n( 'Y-d-m', $local_timestamp, true );

	if ($current_date <= '2033-11-30') {
		$discount = 50;
	}

	return $discount;
}

// ----------------------------------------------------
// > The gform_pre_render filter is executed before the 
//   form is displayed and can be used to manipulate 
//   the Form Object prior to rendering the form.
// ----------------------------------------------------
add_filter( 'gform_pre_render_38', 'pre_render_38' );
function pre_render_38( $form ) {
	$GLOBALS['raelorg_session_ID'] = GetUniqueIdSession();
	$GLOBALS['raelorg_ip_address'] = GFFormsModel::get_ip();
	$GLOBALS['raelorg_country_from_ip'] = "";
	$GLOBALS['raelorg_countries'] = array();

	while ( $GLOBALS['raelorg_country_from_ip'] == "" )
		{
			$ip_data = @json_decode(wp_remote_retrieve_body(wp_remote_get( "http://ip-api.com/json/".$GLOBALS['raelorg_ip_address'])));

			if ( $ip_data->status == "success" ) {
				$GLOBALS['raelorg_country_from_ip'] = $ip_data->countryCode;
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
			case 100: // Name
				if ( $found ) {
					$field->inputs[1]['defaultValue'] = $participant->firstname;
					$field->inputs[3]['defaultValue'] = $participant->lastname;
				}
			break;

			case 101: // Email
				if ( $found ) {
					$field->inputs[0]['defaultValue'] = $participant->email;
					$field->inputs[1]['defaultValue'] = $participant->email;
					?>
    				<script type="text/javascript">
        				jQuery(document).ready(function(){
							jQuery("#input_38_101").attr("readonly", "readonly");
							jQuery("#input_38_101_2").attr("readonly", "readonly");
        				});
    				</script>
    				<?php
				}
			break;

			case '114': // Country & Province
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

			case 103: // Prefered Language
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
} // pre_render_38

// -----------------------------------------------------------------------
// Fill in the Country field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_38_114_1', 'contact_us_populate_country_38', 10, 7 );
function contact_us_populate_country_38( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	InsertFormsLog( $GLOBALS['raelorg_session_ID'], 'IPT', 'Country', $GLOBALS['raelorg_country_from_ip'], $GLOBALS['raelorg_ip_address'], 'N/A' );

	return $GLOBALS['raelorg_countries'];
	
} // contact_us_populate_country_38

// -----------------------------------------------------------------------
// Fill in the Province field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_38_114_2', 'contact_us_populate_province_38', 10, 7 );
function contact_us_populate_province_38( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {
	global $wpdb;

	$selected_iso_country = $chain_value[ "{$field->id}.1" ];

	InsertFormsLog( $GLOBALS['raelorg_session_ID'], 'IPT', 'Province', $GLOBALS['raelorg_country_from_ip'], $GLOBALS['raelorg_ip_address'], 'N/A' );
	
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

} // contact_us_populate_province_38

// -----------------------------------------------------
// Modify a notification object before it is converted into an email and sent
// > Send a notification to the event manager
// > Send a notification to the participant
// > Send the participant in Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_38', 'notification_38', 10, 3 );
function notification_38( $notification, $form, $entry ) {

	$sem_code = 157;
	$discount = 0.0;
	$fee = 0.0;
	$fee_accom = 0.0;
	$duration = 0;
	$duration_days = '';
	$attendance_type = (rgar( $entry, '6' ) == 'Yes' ? 'perday' : 'fulltime');
	$attendance = rgar( $entry, '5' );

	$accom_days = ''; 
	$accom_type = rgar( $entry, '32' );

	$not_you = rgar ( $entry, '113' );
	$email_not_you = rgar ( $entry, '96' );
	$name_not_you = rgar ( $entry, '98' );
	$message = rgar ( $entry, '19' );
	$additional_information = '';

	// Put in Feedback/Notes information which cannot be save in the Elohim.net struture
	if ($not_you == 'Yes') {
		$additional_information = 
'
Additional information:
	> Submited by: ' . $name_not_you . ' (' . $email_not_you . ')'; 
	}

    $feedback = $message . $additional_information;

	if ($attendance_type == 'fulltime') {
		$duration = 6;
		$duration_days = '2033-12-11,2033-12-12,2033-12-13,2033-12-14,2033-12-15,2033-12-16';
		$accom_days    = '2033-12-11,2033-12-12,2033-12-13,2033-12-14,2033-12-15,2033-12-16';

		// Attendance fulltime
		switch ($attendance) {
			case 'Student under 26':
				$fee = GFCommon::to_number( rgar( $entry, '7.2' ) );
				break;
			case 'Newcomer under 25':
				$fee = GFCommon::to_number( rgar( $entry, '8.2' ) );
				break;
			case 'Newcomer 25 and more':
				$fee = GFCommon::to_number( rgar( $entry, '9.2' ) );
				break;
			case 'Between 25 and 64':
				$fee = GFCommon::to_number( rgar( $entry, '10.2' ) );
				break;
			case '65 and more':
				$fee = GFCommon::to_number( rgar( $entry, '11.2' ) );
				break;
		}

		// Accomodation fulltime
		switch ($accom_type) {
			case 'Igloo for one person no heating fireplace ($150 per day or $700 all days)':
				$fee_accom = 700.00;
				break;
			case 'Igloo for two people no heating fireplace ($80 per day or $380 all days)':
				$fee_accom = 380.00;
				break;
			case 'Igloo for three people no heating fireplace ($50 per day or $230 all days)':
				$fee_accom = 230.00;
				break;
			case 'Igloo for five people no heating fireplace ($35 per day or $170 all days)':
				$fee_accom = 170.00;
				break;
			case 'Tipi for three people with heating fireplace ($125 per day or $580 all days)':
				$fee_accom = 580.00;
				break;
			case 'Tipi for five people with heating fireplace ($95 per day or $440 all days)':
				$fee_accom = 440.00;
				break;
			case 'Tipi for ten people with heating fireplace ($35 per day or $140 all days)':
				$fee_accom = 140.00;
				break;
		}
	}
	else {
		// Attendance per day
		switch ($attendance) {
			case 'Student under 26':       // Attendance per days (Student under 26)
				$field = RGFormsModel::get_field( $form, 20 );
				$duration_days = str_replace( '($10.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$duration = substr_count($duration_days,',')+1;
				$fee = $duration * 10.00;
				break;
			case 'Newcomer under 25':      // Attendance per days (Newcomer under 25)
				$field = RGFormsModel::get_field( $form, 24 );
				$duration_days = str_replace( '($20.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$duration = substr_count($duration_days,',')+1;
				$fee = $duration * 20.00;
				break;
			case 'Newcomer 25 and more':   // Attendance per days (Newcomer 25 and more)
				$field = RGFormsModel::get_field( $form, 26 );
				$duration_days = str_replace( '($40.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$duration = substr_count($duration_days,',')+1;
				$fee = $duration * 40.00;
				break;
			case 'Between 25 and 64':      // Attendance per days (Newcomer 25 and 64)
				$field = RGFormsModel::get_field( $form, 28 );
				$duration_days = str_replace( '($100.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$duration = substr_count($duration_days,',')+1;
				$fee = $duration * 100.00;
				break;
			case '65 and more':            // Attendance per days (65 and more)
				$field = RGFormsModel::get_field( $form, 30 );
				$duration_days = str_replace( '($60.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$duration = substr_count($duration_days,',')+1;
				$fee = $duration * 60.00;
				break;
		}

		// Accomodation per day
		switch ($accom_type) {
			case 'Igloo for one person no heating fireplace ($150 per day or $700 all days)':
				$field = RGFormsModel::get_field( $form, 49 );
				$accom_days = str_replace( '($150.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$nb_accom_days = substr_count($accom_days,',')+1;
				$fee_accom = $nb_accom_days * 150.00;
				break;
			case 'Igloo for two people no heating fireplace ($80 per day or $380 all days)':
				$field = RGFormsModel::get_field( $form, 51 );
				$accom_days = str_replace( '($80.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$nb_accom_days = substr_count($accom_days,',')+1;
				$fee_accom = $nb_accom_days * 80.00;
				break;
			case 'Igloo for three people no heating fireplace ($50 per day or $230 all days)':
				$field = RGFormsModel::get_field( $form, 53 );
				$accom_days = str_replace( '($50.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$nb_accom_days = substr_count($accom_days,',')+1;
				$fee_accom = $nb_accom_days * 50.00;
				break;
			case 'Igloo for five people no heating fireplace ($35 per day or $170 all days)':
				$field = RGFormsModel::get_field( $form, 55 );
				$accom_days = str_replace( '($35.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$nb_accom_days = substr_count($accom_days,',')+1;
				$fee_accom = $nb_accom_days * 35.00;
				break;
			case 'Tipi for three people with heating fireplace ($125 per day or $580 all days)':
				$field = RGFormsModel::get_field( $form, 58 );
				$accom_days = str_replace( '($125.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$nb_accom_days = substr_count($accom_days,',')+1;
				$fee_accom = $nb_accom_days * 125.00;
				break;
			case 'Tipi for five people with heating fireplace ($95 per day or $440 all days)':
				$field = RGFormsModel::get_field( $form, 60 );
				$accom_days = str_replace( '($95.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$nb_accom_days = substr_count($accom_days,',')+1;
				$fee_accom = $nb_accom_days * 95.00;
				break;
			case 'Tipi for ten people with heating fireplace ($35 per day or $140 all days)':
				$field = RGFormsModel::get_field( $form, 63 );
				$accom_days = str_replace( '($35.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
				$nb_accom_days = substr_count($accom_days,',')+1;
				$fee_accom = $nb_accom_days * 35.00;
				break;
		}
	}

	// Meal
	$field = RGFormsModel::get_field( $form, 67 );
	$meal_breakfast_days = str_replace( '($10.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field = RGFormsModel::get_field( $form, 70 );
	$meal_lunch_days = str_replace( '($25.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');      // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field = RGFormsModel::get_field( $form, 71 );
	$meal_dinner_days = str_replace( '($25.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');     // Get the value selected like 2022-07-17, 2022-07-18, ....

	$nb_breakfast = substr_count($meal_breakfast_days,',')+1;
	$nb_lunch = substr_count($meal_lunch_days,',')+1;
	$nb_dinner = substr_count($meal_dinner_days,',')+1;

	$sem_fee_breakfast = $nb_breakfast * 10;
	$sem_fee_lunch = $nb_lunch * 25;
	$sem_fee_dinner = $nb_dinner * 25;

	$fee_gala_dinner = 0;
	$field = RGFormsModel::get_field( $form, 74 );  // fee is $25 if 2033-12-15 is selected for dinner
	$yes_dinner = is_object( $field ) ? $field->get_value_export( $entry ) : '';
	if (strpos($yes_dinner, 'Yes') !== false) {
		$fee_gala_dinner = 25.00;
	}
	$field = RGFormsModel::get_field( $form, 76 );  // fee is $50 if 2033-12-15 is not selected for dinner
	$yes_dinner = is_object( $field ) ? $field->get_value_export( $entry ) : '';
	if (strpos($yes_dinner, 'Yes') !== false) {
		$fee_gala_dinner = 50.00;
	}

	// Transport
	$fee_transport = 0;
	$field = RGFormsModel::get_field( $form, 83 );
	$yes_transport = is_object( $field ) ? $field->get_value_export( $entry ) : '';
	if (strpos($yes_transport, 'Yes') !== false) {
		$fee_transport = 20.00;
	}

	// Discount
	if ($attendance_type == 'fulltime') {
		$discount = compute_discount( $discount );
	}

	$participant = array(
		'email' => rgar( $entry, '101' ),
		'firstname' => rgar( $entry, '100.3' ),
		'lastname' => rgar( $entry, '100.6' ),
		'nickname' => '',
		'fullname_native' => '',
		'country' => rgar( $entry, '114.1' ),
		'state' => rgar( $entry, '114.2' ),
		'suburb' => '',
		'prefLanguage' => rgar( $entry, '103' ),
        'username' => rgar( $entry, '100.6' ) . '||' . rgar( $entry, '100.3' ), 
		'mobile_phone' => rgar( $entry, '106' ),
		'home_phone' => '',
		'work_phone' => '',
		'date_birth' => '',
		'gender' => rgar( $entry, '102' ),
		'understand_english' => 0,
		'sem_code' => $sem_code,
        'year' => '2033', 
        'season' => 'winter',
		'firstseminar' => (in_array(rgar( $entry, '5' ), array('Newcomer under 25', 'Newcomer 25 and more')) ? 1 : 0),
		'student' => (rgar( $entry, '5' ) == 'Student under 26' ? 1 : 0),         
		'present' => 0,         // In attendance
		'status' => $attendance, 
		'fee' => $fee,          
		'duration' => $duration,
		'duration_days' => str_replace(' ', '', $duration_days),
		'hotel' => '',
		'room_no' => '',
		'accom_type' => $accom_type,
		'accom_days' => str_replace(' ', '', $accom_days),
		'fee_accom' => $fee_accom,
		'parking' => rgar( $entry, '115' ),
		'meal_breakfast' => str_replace(' ', '', $meal_breakfast_days),
		'meal_lunch' => str_replace(' ', '', $meal_lunch_days),
		'meal_dinner' => str_replace(' ', '', $meal_dinner_days),
		'meal_count' => '', // See trigger I_seminar
        'arr_date' =>  rgar( $entry, '85' ) . ' ' .  rgar( $entry, '86' ),  // '0000-00-00 00:00:00',
        'arr_number' =>  rgar( $entry, '87' ),
        'arr_location' =>  rgar( $entry, '88' ),
        'dep_date' => rgar( $entry, '89' ) . ' ' .  rgar( $entry, '90' ), 
        'dep_number' => rgar( $entry, '91' ),
        'dep_location' => rgar( $entry, '92' ), 
		'translation' => rgar( $entry, '108' ),
		'transmission' => '',
        'donation' => 0,   // User price type, no need .2
        'dinner' => $fee_gala_dinner,
		'fee_transport' => $fee_transport,
		'responsibility' => '',
		'absent_ceremony' => 0,
		'sem_feedback' => $feedback,
		'pay_type' => rgar( $entry, '110' ),
		'pay_amount' => 0.00,
		'pay_received' => 0.00,
		'pay_currency' => 'CAD',
		'fee_meals' => $sem_fee_breakfast + $sem_fee_lunch + $sem_fee_dinner,    
        'ip' => GFFormsModel::get_ip(),
		'fee_discount' => $discount,
		'fee_cc' => 0.00,
		'paypal_txn' => '',     // '1SR7173971509790K',
		'paypal_status' => '',  // 'Completed',
		'paypal_date' => '',    // '13:54:06 Dec 17, 2008 PST',
		'paypal_fee' => 0.00,
		'paypal_data' => '',    // 'array()',
		'survey' => '',   		//' Please give details here',
		'updateby' => 0,
		'formtext' => ''       // array
    );

	$language_iso = rgar( $entry, '103' );
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
	$iso_country = rgar( $entry, '114.1' );

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
			'State' => rgar( $entry, '114.2' ),
			'Language' => $language,
			'Message' => $message
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
									$participant['prefLanguage'], 
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

		$cancel_permalink = '';
		$modify_permalink = '';

		switch ($language_iso) {
			case 'en':
				$cancel_permalink = get_permalink(358057) . '?selector=' . $selector;
				$modify_permalink = get_permalink(358054) . '?selector=' . $selector;
				break;
			case 'fr':
				$cancel_permalink = get_permalink(358064) . '?selector=' . $selector;
				$modify_permalink = get_permalink(358063) . '?selector=' . $selector;
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
		} elseif ( strstr( $notification['message'], '%7Bmodify_registration%7D' ) ) {
			$notification['message'] = str_replace('%7Bmodify_registration%7D', $modify_permalink, $notification['message'] );
		} else { 
			$notification['message'] = str_replace('%7bmodify_registration%7d', $modify_permalink, $notification['message'] );
		}

	    send_participant_to_ElohimNet( $participant, $selector );
	}

    return $notification;
    
} // notification_38