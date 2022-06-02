<?php

// ----------------------------------------------------
// Form : kama summer 2022 (21)
// > Pre-populate the form
// ----------------------------------------------------
add_filter( 'gform_pre_render_21', 'pre_render_21' );
function pre_render_21( $form ) {
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
							jQuery("#input_21_3").attr("readonly", "readonly");
							jQuery("#input_21_3_2").attr("readonly", "readonly");
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
} // pre_render_21

// -----------------------------------------------------
// Send a notification to the event manager
// Send a notification to the participant
// Send the participant into Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_21', 'notification_21', 10, 3 );
function notification_21( $notification, $form, $entry ) {

	$page_cancel = 'Registration cancellation Kama Summer 2022';

	// -------------------------------------------------------------------------
	// TECHNICAL GUIDE
	// Variables to set depending of the event
	// -----BEGIN--------------------------------------------------------------------
	$form = 21;
	$sem_code = 154;                
	$year = '2022';                 
	$page_title = 'Kama Summer 2022'; // Title of the registration page
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
		'prefLanguage' => '',
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
		'student' => (rgar( $entry, '15' ) == 'Yes' ? 1 : 0),         
		'present' => 0,         // In attendance
		'status' => 'regular', 
		'fee' => '',          
		'duration' => 0,       // Calculated automatiquely
		'duration_days' => '2022-08-14,2022-08-15,2022-08-16,2022-08-17,2022-08-18,2022-08-19,2022-08-20,2022-08-21,2022-08-22,2022-08-23,2022-08-24,2022-08-25,2022-08-26,2022-08-27,2022-08-28',
		'hotel' => '',
		'room_no' => '',
		'accom_type' => '',
		'accom_days' => '',
		'fee_accom' => '',
		'parking' => '',
		'meal_breakfast' => '',
		'meal_lunch' => '',
		'meal_dinner' => '',
		'meal_count' => '', // See trigger I_seminar
        'arr_date' =>  '0000-00-00 00:00:00',
        'arr_number' => '',
        'arr_location' => '',
        'dep_date' => '0000-00-00 00:00:00', 
        'dep_number' => '',
        'dep_location' => '', 
		'translation' => (rgar( $entry, '16' ) == 'Yes' ? 'English' : ''),
		'transmission' => '',
        'donation' => 0.0,
        'dinner' => 0.00,       // Gala
		'fee_transport' => 0.00,
		'responsibility' => '',
		'absent_ceremony' => 0,
		'sem_feedback' => rgar ( $entry, '13' ),
        'ip' => GFFormsModel::get_ip(),
		'pay_type' => 'n/a',
		'pay_amount' => 0.00,
		'pay_received' => 0.00,
		'pay_currency' => 'JPY',
		'fee_meals' => 0.00,
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
        //------------------------------------------------------------------------------------------------------------------
        // TECHNICAL GUIDE
        // Adjust this ARRAY depending of the information you want to show in the notification to responsable
        //------------------------------------------------------------------------------------------------------------------
        $fields = array(
			'First seminar' => ($participant['firstseminar'] == 1 ? 'Yes' : 'No'),
			'Student' => ($participant['student'] == 1 ? 'Yes' : 'No'),
			'Translation' => $participant['translation'],
			'Firstname' => $participant['firstname'],
			'Lastname' => $participant['lastname'],
			'Email' => $participant['email'],
			'Nickname' => $participant['nickname'],
			'Name in native language' => $participant['fullname_native'],
			'Country' => $country,
			'City / Town' => $participant['suburb'],
			'Gender' => ($participant['gender'] == 'M' ? 'Male' : 'Female'),
			'Date of birth' => $participant['date_birth'],
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
				$cancel_permalink = get_permalink(351283) . '?selector=' . $selector;;
				$modify_permalink = get_permalink(351281) . '?selector=' . $selector;;
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

	    send_participant_to_ElohimNet( $participant );
	}

    return $notification;
    
} // notification_21