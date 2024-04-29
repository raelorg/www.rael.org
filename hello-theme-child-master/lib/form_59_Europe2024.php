<?php

// ----------------------------------------------------
// > The gform_pre_render filter is executed before the 
//   form is displayed and can be used to manipulate 
//   the Form Object prior to rendering the form.
// ----------------------------------------------------

add_filter( 'gform_pre_render_59', 'pre_render_59' );
function pre_render_59( $form ) {
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
			case '26': // Country & Province
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

			case 28: // Prefered Language
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
} // pre_render_59

// -----------------------------------------------------------------------
// Fill in the Country field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_59_26_1', 'contact_us_populate_country_59', 10, 7 );
function contact_us_populate_country_59( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	return $GLOBALS['raelorg_countries'];
	
} // contact_us_populate_country_59

// -----------------------------------------------------------------------
// Fill in the Province field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_59_26_2', 'contact_us_populate_province_59', 10, 7 );
function contact_us_populate_province_59( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {
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

} // contact_us_populate_province_59

// -----------------------------------------------------
// Modify a notification object before it is converted into an email and sent
// > Send a notification to the event manager
// > Send a notification to the participant
// > Send the participant in Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_59', 'notification_59', 10, 3 );
function notification_59( $notification, $form, $entry ) {

	$first = 0;

	if ((rgar( $entry, '42' ) == 'Yes') || (rgar( $entry, '42' ) == 'Oui') || (rgar( $entry, '42' ) == 'Sì')) {
		$first = 1;
	}

	$participant = array(
		'email' => rgar( $entry, '22' ), // ok
		'firstname' => rgar( $entry, '21.3' ), // ok
		'lastname' => rgar( $entry, '21.6' ), // ok
		'nickname' => '', // ok
		'fullname_native' => '', // ok
		'country' => rgar( $entry, '26.1' ), // ok
		'state' => rgar( $entry, '26.2' ), // ok
		'suburb' => rgar( $entry, '27' ), // ok
		'prefLanguage' => rgar( $entry, '28' ), // ok
        'username' => rgar( $entry, '21.6' ) . '||' . rgar( $entry, '21.3' ), // ok
		'mobile_phone' => rgar( $entry, '29' ), // ok
		'home_phone' => rgar( $entry, '44' ), // ok
		'work_phone' => '', // ?
		'date_birth' => rgar( $entry, '43' ), // ok
		'gender' => rgar( $entry, '25' ), // ok
		'understand_english' => '', // ok
		'sem_code' => 159, // ok
        'year' => '2024',  // ok
        'season' => 'summer', // ok
		'firstseminar' => $first, // ok
		'student' => 0, // ok
		'present' => 0, // ok
		'status' => '', // ok
		'fee' => 0.0, // ok
		'duration' => 0, // ok
		'duration_days' => '2024-08-03,2024-08-04,2024-08-05,2024-08-06,2024-08-07,2024-08-08,2024-08-09,2024-08-10', // ok
		'hotel' => '', // ok
		'room_no' => '', // ok
		'accom_type' => '', // ok
		'accom_days' => '', // ok
		'fee_accom' => 0.0, // ok
		'parking' => 'No', // ok
		'meal_breakfast' => '', // ok
		'meal_lunch' => '', // ok
		'meal_dinner' => '', // ok
		'meal_count' => '', // See trigger I_seminar ok
        'arr_date' => '0000-00-00 00:00:00', // ok
        'arr_number' => '', // ok
        'arr_location' => '', // ok
        'dep_date' => '', // ok
        'dep_number' => '', // ok
        'dep_location' => '', // ok
		'translation' => '', // ok
		'transmission' => '', // ok
        'donation' => 0,   // ok
        'dinner' => 0, // ok
		'fee_transport' => 0, // ok
		'responsibility' => '', // ok
		'absent_ceremony' => 0, // ok
		'sem_feedback' => '', // ok
		'pay_type' => 'cash', // ok
		'pay_amount' => 0.0, // ok
		'pay_received' => 0.00, // ok
		'pay_currency' => 'EUR', // ok
		'fee_meals' => 0, // ok
        'ip' => GFFormsModel::get_ip(), // ok
		'fee_discount' => 0, // ok
		'fee_cc' => 0.00, // ok
		'paypal_txn' => '',     // ok
		'paypal_status' => '',  // ok
		'paypal_date' => '',    // ok
		'paypal_fee' => 0.00,   // ok
		'paypal_data' => '',    // ok
		'survey' => '',   		// ok
		'updateby' => 0,        // ok
		'formtext' => ''  		// ok
    );

    foreach ($participant as $key => $value) {
		error_log( __METHOD__ . ' ' . $key. ' = ' . $value  . PHP_EOL );
    }

	$language_iso = rgar( $entry, '28' );
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
	$iso_country = rgar( $entry, '26.1' );

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
			'State' => rgar( $entry, '26.2' ),
			'Town' => rgar( $entry, '27' ),
			'Language' => $language,
			'First participation' => rgar( $entry, '42' ),
			'Sex' => (rgar( $entry, '25' ) == 'F' ? 'Female' : 'Male'),
			'Date of birth' => rgar( $entry, '43' ),
			'Mobile phone' => rgar( $entry, '29' ),
			'Home phone' => rgar( $entry, '44' )
			);

		$arrayFields = setNotificationArrayFields($fields); 

		// Check if a notification exist for the current language and use it as replacement
		// > Sometimes it's better to keep notifications in the database than to waste time with WPML.
		$notificationResponsable = SelectNotification(59, 'responsable', $language_iso);

		if ( 'not found' !== $notificationResponsable ) {
			$notification['message'] = $notificationResponsable;
		}

		$notification['bcc'] = 'loukesir@outlook.com';
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
									59, // id form
									'', // $news_event, 
									$participant['ip'],
									$sem_code );

		$fields = array(
			'First name' => $participant['firstname'],
			'Last name' => $participant['lastname'],
			'Email' => $participant['email'],
			'Country' => $country_name,
			'State' => rgar( $entry, '26.2' ),
			'Town' => rgar( $entry, '27' ),
			'Language' => $language,
			'First participation' => rgar( $entry, '42' ),
			'Sex' => (rgar( $entry, '25' ) == 'F' ? 'Female' : 'Male'),
			'Date of birth' => rgar( $entry, '43' ),
			'Mobile phone' => rgar( $entry, '29' ),
			'Home phone' => rgar( $entry, '44' )
			);

		$arrayFields = setNotificationArrayFields($fields); 

		$language_iso = apply_filters( 'wpml_current_language', NULL );
		$person_type = 'person-not-newcomer';
		
		if ((rgar( $entry, '42' ) == 'Yes') || (rgar( $entry, '42' ) == 'Oui') || (rgar( $entry, '42' ) == 'Sì')) 
		{
			$person_type = 'person-newcomer';
		}
		
		GFCommon::log_debug( __METHOD__ . ' ' . rgar( $entry, '42' ) );
		GFCommon::log_debug( __METHOD__ . ' ' . $language_iso );
		GFCommon::log_debug( __METHOD__ . ' ' . $person_type );
		
		//$confirmation = SelectNotification(59, 'person-not-newcomer', 'en');
		// Check if a notification exist for the current language and use it as replacement
		// > Sometimes it's better to keep notifications in the database than to waste time with WPML.
		if (($language_iso == 'en') || ($language_iso == 'fr') || ($language_iso == 'it')) 
		{
			GFCommon::log_debug( __METHOD__ . ' en-fr-it' );
			$confirmation = SelectNotification(59, $person_type, $language_iso);
		}
		else 
		{
			GFCommon::log_debug( __METHOD__ . ' autre' );
			$confirmation = SelectNotification(59, $person_type, 'en');
		}

		$notification['message'] = $confirmation;
		$notification['bcc'] = 'loukesir@outlook.com';
			
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
}