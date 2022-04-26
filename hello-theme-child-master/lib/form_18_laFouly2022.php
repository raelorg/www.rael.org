<?php

// ----------------------------------------------------
// Form : La Fouly May 2022 (18)
// > Pre-populate the form
// ----------------------------------------------------
add_filter( 'gform_pre_render_18', 'pre_render_18' );
function pre_render_18( $form ) {
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
							jQuery("#input_18_2").attr("readonly", "readonly");
							jQuery("#input_18_2_2").attr("readonly", "readonly");
        				});
    				</script>
    				<?php
				}
			break;

			case 4: // Country
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
} // pre_render_18

// -----------------------------------------------------
// Send a notification to the event manager
// Send a notification to the participant
// Send the participant into Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_18', 'notification_18', 10, 3 );
function notification_18( $notification, $form, $entry ) {

	$page_cancel = 'Registration cancellation la Fouly 2022';
	$message = rgar ( $entry, '6' );
	$phone = rgar ( $entry, '5' );
	$forfait = rgar ( $entry, '7' );
    $feedback = $message . 
'
Additional information:
> Phone: ' . $phone . ' 
> Forfait: ' . $forfait;

	// -------------------------------------------------------------------------
	// TECHNICAL GUIDE
	// Variables to set depending of the event
	// -----BEGIN--------------------------------------------------------------------
	$form = 18;                  // id GF
	$sem_code = 152;                // e107_db_seminars_reg.semreg_id
	$year = '2022';                 // Year
	$page_title = 'La Fouly 2022';     // Title of the web page
	$season = 'spring';             // Season
	$meal_count = '';
	$meal_breakfast = '';
	$meal_lunch = '';
	$meal_dinner = '';
	$duration = '';
	$duration_days = '';
	$pay_amount = '';
	$fee = '';
	$accom_days = '';

	// Forfait
	// > Forfait week end comprenant 2 repas, 1 petit déjeuner, une nuitée (prendre TOUT le nécessaire pour dormir: sac couchage ou drap, duvet et oreiller. Il n’y a rien sur place).
	// > Forfait weekend: 70.- fr; Forfait sans nuitée: 50.-fr; Samedi sans nuitée: 25.-fr; Dimanche 25.-fr
	switch ($forfait) {
		case 'Forfait weekend: 70.-fr':
			$meal_count = "array ('breakfast' => '2','lunch' => '2','dinner' => '2')";
			$meal_breakfast = '2022-05-14,2022-05-15';
			$meal_lunch = '2022-05-14,2022-05-15';
			$meal_dinner = '2022-05-14,2022-05-15';
			$duration = '2';
			$duration_days = '2022-05-14,2022-05-15';
			$pay_amount = 70.00;
			$fee = 70.00;
			$accom_days = '2022-05-14,2022-05-15';
			break;
		case 'Forfait sans nuitée: 50.-fr':
			$meal_count = "array ('breakfast' => '0','lunch' => '2','dinner' => '2')";
			$meal_lunch = '2022-05-14,2022-05-15';
			$meal_dinner = '2022-05-14,2022-05-15';
			$duration = '2';
			$duration_days = '2022-05-14,2022-05-15';
			$pay_amount = 50.00;
			$fee = 50.00;
			break;
		case 'Samedi sans nuitée: 25.-fr':
			$meal_count = "array ('breakfast' => '0','lunch' => '1','dinner' => '1')";
			$meal_lunch = '2022-05-14';
			$meal_dinner = '2022-05-14';
			$duration = '1';
			$duration_days = '2022-05-14';
			$pay_amount = 25.00;
			$fee = 25.00;
			break;
		case 'Dimanche 25.-fr':
			$meal_count = "array ('breakfast' => '0','lunch' => '1','dinner' => '1')";
			$meal_lunch = '2022-05-15';
			$meal_dinner = '2022-05-15';
			$duration = '1';
			$duration_days = '2022-05-15';
			$pay_amount = 25.00;
			$fee = 25.00;
			break;
	}

	$participant = array(
		'email' => rgar( $entry, '3' ),
		'firstname' => rgar( $entry, '1.3' ),
		'lastname' => rgar( $entry, '1.6' ),
		'country' => rgar( $entry, '4' ),
		'state' => '',
		'prefLanguage' => 'fr',
        'username' => rgar( $entry, '1.6' ) . '||' . rgar( $entry, '1.3' ), 
        'arr_date' => '0000-00-00 00:00:00',
        'arr_number' => '',     // KLM687
        'arr_location' => '',   // Airport
        'dep_date' => '0000-00-00 00:00:00', 
        'dep_number' => '',     // KLM231
        'dep_location' => '',   // Hotel
        'dinner' => 0.00,       // 
        'donation' => 0.00,     // 
		'sem_code' => $sem_code,// Mandatory
        'year' => $year,        // Mandatory
        'season' => $season,    // Mandatory
        'ip' => GFFormsModel::get_ip(),
		'firstseminar' => 0,    // 1
		'student' => 0,         // 1
		'pay_type' => 'n/a',
		'pay_amount' => $pay_amount,   // 10.12,
		'pay_received' => 0.00, // 1.11,
		'pay_currency' => 'CHF',   // 'CAD',
		'translation' => '',    // 'French',
		'fee' => $fee,          // 1.00
		'fee_accom' => 0.00,    // 1.00
		'fee_meals' => 0.00,    // 1.00
		'fee_cc' => 0.00,       // 1.00
		'fee_discount' => 0.00, // 1.00
		'paypal_txn' => '',     // '1SR7173971509790K',
		'paypal_status' => '',  // 'Completed',
		'paypal_date' => '',    // '13:54:06 Dec 17, 2008 PST',
		'paypal_fee' => 0.00,   // 1.00,
		'paypal_data' => '',    // 'array()',
		'present' => 0,         // 0
		'absent_ceremony' => 0, // 1
		'responsibility' => '', // 'Tech_team',
		'accom_days' => $accom_days,     // '2008-04-29,2008-04-30,2008-05-01,2008-05-02,2008-05-03,2008-05-04,2008-05-05',
		'accom_type' => '',     // 'Single',
		'hotel' => '',          // 'Wyndham', ok
		'room_no' => '',        // '101',
		'meal_count' => '', //$meal_count,     // 'Array(...)',
		'meal_breakfast' => $meal_breakfast, // '2008-04-30,2008-05-01,2008-05-02,2008-05-03,2008-05-04,2008-05-05,2008-05-06',
		'meal_lunch' => $meal_lunch,     // '2008-04-30,2008-05-01,2008-05-02,2008-05-03,2008-05-04,2008-05-05,2008-05-06',
		'meal_dinner' => $meal_dinner,    // '2008-04-30,2008-05-01,2008-05-02,2008-05-03,2008-05-04,2008-05-05,2008-05-06',
		'duration' => $duration,        // 7,
		'duration_days' => $duration_days,  // '2007-07-24,2007-07-25,2007-07-26,2007-07-27,2007-07-28,2007-07-29,2007-07-30',
		'status' => '',         // 'structure',
		'parking' => 0,         // 1
		'survey' => '',   		//' Please give details here',
		'updateby' => 0,
		'transmission' => 0,    // 1
		'formtext' => '',       // array
		'fee_transport' => 0,   // 1
		'sem_feedback' => $feedback
    );
	// ---END----------------------------------------------------------------------

    $language = GetLanguageDescription($participant['prefLanguage']);
	$country = GetCountryDescription($participant['country']);
	$language_iso = apply_filters( 'wpml_current_language', NULL );

	// dorakefi@gmail.com
	// Alert notification to the event manager
	if ( $notification['toType'] === 'email' ) {
        //------------------------------------------------------------------------------------------------------------------
        // TECHNICAL GUIDE
        // Adjust this ARRAY depending of the information you want to show in the notification to responsable
        //------------------------------------------------------------------------------------------------------------------
        $fields = array(
			'Prénom' => $participant['firstname'],
			'Nom' => $participant['lastname'],
			'Email' => $participant['email'],
			'Pays' => $country,
			'Téléphone' => $phone,
			'Forfait' => $forfait,
			'Message' => $message
			);

		$arrayFields = setNotificationArrayFields($fields); 

		// Check if a notification exist for the current language and use it as replacement
		// > Sometimes it's better to keep notifications in the database than to waste time with WPML.
		$notificationResponsable = SelectNotification($form, 'responsable', $language_iso);

		if ( 'not found' !== $notificationResponsable ) {
			$notification['message'] = $notificationResponsable;
		}
		
		//$notification['to'] = 'loukesir@outlook.com';
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

		// Page title must be same for alllanguages
		$cancel_link = 'https://www.rael.org/fr/registration/annulation-la-fouly-2022/'  . '?selector=' . $selector;
		$modify_link = get_permalink( get_page_by_title( $page_title ) ) . '?selector=' . $selector;
	
		if ( strstr( $notification['message'], '{cancel_registration}' ) ) {
			$notification['message'] = str_replace('{cancel_registration}', $cancel_link, $notification['message'] );
		}
		if ( strstr( $notification['message'], '{modify_registration}' ) ) {
			$notification['message'] = str_replace('{modify_registration}', $modify_link, $notification['message'] );
		}

	    send_participant_to_ElohimNet( $participant );
	}

    return $notification;
    
} // notification_18