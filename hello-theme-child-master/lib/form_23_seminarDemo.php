<?php

// ------------------------------------------------------------
// Determine the status of a participant
// ------------------------------------------------------------
function DetermineParticipantStatus ( $student, $newcomer, $structure ) {
	$status = 'regular';

	if ( $student == '1' ) {
		$status = 'student';
	}
	elseif ( $newcomer == '1' ) {
		$status = 'newcomer';
	}
	elseif ( $structure == '1' ) {
		$status = 'structure';
	}

	return $status;
}

// ----------------------------------------------------
// Form : Seminar Demo (23)
// > Pre-populate the form
// ----------------------------------------------------
add_filter( 'gform_pre_render_23', 'pre_render_23' );
function pre_render_23( $form ) {
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
			case 62: // Name
				if ( $found ) {
					$field->inputs[1]['defaultValue'] = $participant->firstname;
					$field->inputs[3]['defaultValue'] = $participant->lastname;
				}
			break;

			case 63: // Email
				if ( $found ) {
					$field->inputs[0]['defaultValue'] = $participant->email;
					$field->inputs[1]['defaultValue'] = $participant->email;
					?>
    				<script type="text/javascript">
        				jQuery(document).ready(function(){
							jQuery("#input_23_63").attr("readonly", "readonly");
							jQuery("#input_23_63_2").attr("readonly", "readonly");
        				});
    				</script>
    				<?php
				}
			break;

		    case 64: // Country
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

			case 65: // Prefered Language
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
} // pre_render_23

// -----------------------------------------------------
// Send a notification to the event manager
// Send a notification to the participant
// Send the participant into Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_23', 'notification_23', 10, 3 );
function notification_23( $notification, $form, $entry ) {

	// -------------------------------------------------------------------------
	// TECHNICAL GUIDE
	// > Do the programming here.
	// -----BEGIN--------------------------------------------------------------------
	$breakfast_price = 2000.00;
	$lunch_price = 2500.00;
	$dinner_price = 2500.00;
	$part_time_price_by_day_newcomer = 1000.00;
	$part_time_price_by_day_student = 0.00;
	$part_time_price_by_day_structure = 3100.00;
	$part_time_price_by_day_regular = 2500.00;
	$part_time_price_by_day_OceanSingle = 15000.00;
	$part_time_price_by_day_OceanTwin = 11000.00;
	$part_time_price_by_day_OceanTriple = 9000.00;
	$part_time_price_by_day_MountainSingle = 11000.00;
	$part_time_price_by_day_MountainTwin = 9000.00;
	$eyescolor = rgar ( $entry, '56' );
	$email_not_you = rgar ( $entry, '74' );
	$name_not_you = rgar ( $entry, '120' );
	$not_you = rgar ( $entry, '75' );
	$message = rgar ( $entry, '76' );
	$additional_information = '';

	if ($not_you == 'yes') {
		$additional_information = 
'
Additional information:
	> Submited by: ' . $name_not_you . ' (' . $email_not_you . ') 
	> What color are your eyes? ' . $eyescolor;
	}
	else {
		$additional_information = 
'
Additional information:
> What color are your eyes? ' . $eyescolor;
	}

    $feedback = $message . $additional_information;
	$form = 23;                  	// id GF
	$sem_code = 153;                // e107_db_seminars_reg.semreg_id
	$page_title = 'Seminar Demo';   // Title of the web page

	$student = rgar( $entry, '4' );
	$newcomer = rgar( $entry, '5' );
	$structure = rgar( $entry, '6' );
	$sem_attendance_part_time = rgar( $entry, '87' );  // 'Yes' if part-time

	$status = DetermineParticipantStatus ( $student, $newcomer, $structure );

	// get duration days full-time
	$duration_days_full_time = '2022-07-17,2022-07-18,2022-07-19,2022-07-20,2022-07-21,2022-07-22,2022-07-23';

	// get duration days part-time
	$field = RGFormsModel::get_field( $form, 144 );
	$duration_days_part_time_newcomer = str_replace( '($1,000.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '' ); 
	$field = RGFormsModel::get_field( $form, 146 );
	$duration_days_part_time_student = str_replace( '($0.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');  
	$field = RGFormsModel::get_field( $form, 148 );
	$duration_days_part_time_structure = str_replace( '($3,100.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : ''); 
	$field = RGFormsModel::get_field( $form, 150 );
	$duration_days_part_time_regular = str_replace( '($2,500.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');   

	$duration_days = '';

	switch ($status) {
		case "newcomer": 
			$duration_days = ($sem_attendance_part_time == 'Yes' ? $duration_days_part_time_newcomer : $duration_days_full_time);
			break;
		case "student": 
			$duration_days = ($sem_attendance_part_time == 'Yes' ? $duration_days_part_time_student : $duration_days_full_time);
			break;
		case "structure": 
			$duration_days = ($sem_attendance_part_time == 'Yes' ? $duration_days_part_time_structure : $duration_days_full_time);
			break;
		case "regular": 
			$duration_days = ($sem_attendance_part_time == 'Yes' ? $duration_days_part_time_regular : $duration_days_full_time);
			break;
	}
					
	$duration = 0;

	switch ($status) {
		case "newcomer": 
			$duration = ($sem_attendance_part_time == 'Yes' ? substr_count($duration_days_part_time_newcomer,',')+1 : substr_count($duration_days_full_time,',')+1);
			break;
		case "student": 
			$duration = ($sem_attendance_part_time == 'Yes' ? substr_count($duration_days_part_time_student,',')+1 : substr_count($duration_days_full_time,',')+1);
			break;
		case "structure": 
			$duration = ($sem_attendance_part_time == 'Yes' ? substr_count($duration_days_part_time_structure,',')+1 : substr_count($duration_days_full_time,',')+1);
			break;
		case "regular": 
			$duration = ($sem_attendance_part_time == 'Yes' ? substr_count($duration_days_part_time_regular,',')+1 : substr_count($duration_days_full_time,',')+1);
			break;
	}
					
	// Get all seminar prices
	$sem_fee_full_time_newcomer_price = GFCommon::to_number( rgar( $entry, '193.2' ) );
	$sem_fee_full_time_student_price = GFCommon::to_number( rgar( $entry, '194.2' ) );
	$sem_fee_full_time_structure_price = GFCommon::to_number( rgar( $entry, '196.2' ) );
	$sem_fee_full_time_regular_price = GFCommon::to_number( rgar( $entry, '198.2' ) );
	$sem_fee_part_time_newcomer_price = $duration * $part_time_price_by_day_newcomer;
	$sem_fee_part_time_student_price = $duration * $part_time_price_by_day_student; 
	$sem_fee_part_time_structure_price = $duration * $part_time_price_by_day_structure;
	$sem_fee_part_time_regular_price = $duration * $part_time_price_by_day_regular;

	$fee = 0.0;

	switch ($status) {
		case "newcomer": 
			$fee = ($sem_attendance_part_time == 'Yes' ? $sem_fee_part_time_newcomer_price : $sem_fee_full_time_newcomer_price);
			break;
		case "student": 
			$fee = 0.00;
			break;
		case "structure": 
			$fee = ($sem_attendance_part_time == 'Yes' ? $sem_fee_part_time_structure_price : $sem_fee_full_time_structure_price);
			break;
		case "regular": 
			$fee = ($sem_attendance_part_time == 'Yes' ? $sem_fee_part_time_regular_price : $sem_fee_full_time_regular_price);
			break;
	}

	// Accomodation
	$field = GFFormsModel::get_field( $form, 37 );
	$accom_type = is_object( $field ) ? $field->get_value_export( $entry, 37, true ) : ''; // Get the text of the value selected like: Ocean single

	$field = RGFormsModel::get_field( $form, 161 );
	$accom_days_ocean_single = str_replace( '($15,000.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : ''); // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field = RGFormsModel::get_field( $form, 163 );
	$accom_days_ocean_twin = str_replace( '($11,000.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : ''); // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field = RGFormsModel::get_field( $form, 165 );
	$accom_days_ocean_triple = str_replace( '($9,000.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : ''); // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field = RGFormsModel::get_field( $form, 167 );
	$accom_days_mountain_single = str_replace( '($11,000.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : ''); // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field = RGFormsModel::get_field( $form, 169 );
	$accom_days_mountain_twin = str_replace( '($9,000.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : ''); // Get the value selected like 2022-07-17, 2022-07-18, ....

	$accom_days = '';

	switch ( $accom_type ) {
		case 'Ocean single':
			$accom_days = $accom_days_ocean_single;
			break;
		case 'Ocean twin':
			$accom_days = $accom_days_ocean_twin;
			break;
		case 'Ocean triple':
			$accom_days = $accom_days_ocean_triple;
			break;
		case 'Mountain single':
			$accom_days = $accom_days_mountain_single;
			break;
		case 'Mountain triple':
			$accom_days = $accom_days_mountain_twin;
			break;
	}

	$nb_accom_days = 0.0;
	$nb_accom_days = substr_count($accom_days,',')+1;

	$sem_fee_accom_ocean_single_price = $nb_accom_days * $part_time_price_by_day_OceanSingle;
	$sem_fee_accom_ocean_twin_price = $nb_accom_days * $part_time_price_by_day_OceanTwin;
	$sem_fee_accom_ocean_triple_price = $nb_accom_days * $part_time_price_by_day_OceanTriple;
	$sem_fee_accom_mountain_single_price = $nb_accom_days * $part_time_price_by_day_MountainSingle;
	$sem_fee_accom_mountain_twin_price = $nb_accom_days * $part_time_price_by_day_MountainTwin;

	$fee_accom = 0.0;

	switch ($accom_type) {
		case 'Ocean single':
			$fee_accom = $sem_fee_accom_ocean_single_price;
			break;
		case 'Ocean twin':
			$fee_accom = $sem_fee_accom_ocean_twin_price;
			break;
		case 'Ocean triple':
			$fee_accom = $sem_fee_accom_ocean_triple_price;
			break;
		case 'Mountain single':
			$fee_accom = $sem_fee_accom_mountain_single_price;
			break;
		case 'Mountain twin':
			$fee_accom = $sem_fee_accom_mountain_twin_price;
			break;
	}

	// Meal
	$field = RGFormsModel::get_field( $form, 171 );
	$meal_breakfast_days = str_replace( '($2,000.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field = RGFormsModel::get_field( $form, 173 );
	$meal_lunch_days = str_replace( '($2,500.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....
	$field = RGFormsModel::get_field( $form, 175 );
	$meal_dinner_days = str_replace( '($2,500.00)', '', is_object( $field ) ? $field->get_value_export( $entry ) : '');  // Get the value selected like 2022-07-17, 2022-07-18, ....

	$nb_breakfast = substr_count($meal_breakfast_days,',')+1;
	$nb_lunch = substr_count($meal_lunch_days,',')+1;
	$nb_dinner = substr_count($meal_dinner_days,',')+1;

	$sem_fee_breakfast = $nb_breakfast * $breakfast_price;
	$sem_fee_lunch = $nb_lunch * $lunch_price;
	$sem_fee_dinner = $nb_dinner * $dinner_price;

	$fee_gala_dinner = 0;
	$field = RGFormsModel::get_field( $form, 177 );
	$yes_dinner = is_object( $field ) ? $field->get_value_export( $entry ) : '';
	if (strpos($yes_dinner, 'Yes') !== false) {
		$fee_gala_dinner = 2500.00;
	}

	$fee_transport = 0;
	$field = RGFormsModel::get_field( $form, 180 );
	$yes_transport = is_object( $field ) ? $field->get_value_export( $entry ) : '';
	if (strpos($yes_transport, 'Yes') !== false) {
		$fee_transport = 1000.00;
	}

	$field = RGFormsModel::get_field( $form, 108 );
	$responsability = is_object( $field ) ? $field->get_value_export( $entry ) : '';  // Get the value selected like Artistic, Tech team,  ....

	$participant = array(
		'email' => rgar( $entry, '63' ),
		'firstname' => rgar( $entry, '62.3' ),
		'lastname' => rgar( $entry, '62.6' ),
		'nickname' => rgar( $entry, '116' ),
		'fullname_native' => rgar( $entry, '117' ),
		'country' => rgar( $entry, '64' ),
		'state' => rgar( $entry, '66' ),
		'suburb' => rgar( $entry, '67' ),
		'prefLanguage' => rgar( $entry, '65' ),
        'username' => rgar( $entry, '62.6' ) . '||' . rgar( $entry, '62.3' ), 
		'mobile_phone' => rgar( $entry, '68' ),
		'home_phone' => rgar( $entry, '69' ),
		'work_phone' => rgar( $entry, '107' ),
		'date_birth' => rgar( $entry, '106' ),
		'gender' => rgar( $entry, '118' ),
		'understand_english' => (rgar( $entry, '119' ) == 'yes' ? 1 : 0),
		'sem_code' => $sem_code,
        'year' => '2022', 
        'season' => 'summer',
		'firstseminar' => ($status == 'newcomer' ? 1 : 0),
		'student' => ($status == 'student' ? 1 : 0),         
		'present' => 0,         // In attendance
		'status' => $status, 
		'fee' => $fee,          
		'duration' => $duration,
		'duration_days' => str_replace(' ', '', $duration_days),
		'hotel' => rgar( $entry, '33' ),
		'room_no' => rgar( $entry, '34' ),
		'accom_type' => $accom_type,
		'accom_days' => str_replace(' ', '', $accom_days),
		'fee_accom' => $fee_accom,
		'parking' => rgar( $entry, '39' ),
		'meal_breakfast' => str_replace(' ', '', $meal_breakfast_days),
		'meal_lunch' => str_replace(' ', '', $meal_lunch_days),
		'meal_dinner' => str_replace(' ', '', $meal_dinner_days),
		'meal_count' => '', // See trigger I_seminar
        'arr_date' =>  rgar( $entry, '45' ) . ' ' .  rgar( $entry, '46' ),  // '0000-00-00 00:00:00',
        'arr_number' =>  rgar( $entry, '47' ),
        'arr_location' =>  rgar( $entry, '48' ),
        'dep_date' => rgar( $entry, '49' ) . ' ' .  rgar( $entry, '50' ), 
        'dep_number' => rgar( $entry, '51' ),
        'dep_location' => rgar( $entry, '52' ), 
		'translation' => rgar( $entry, '55' ),
		'transmission' => rgar( $entry, '10' ),
        'donation' => GFCommon::to_number( rgar( $entry, '183' ) ),   // User price type, no need .2
        'dinner' => $fee_gala_dinner,
		'fee_transport' => $fee_transport,
		'responsibility' => str_replace(' ', '', $responsability),
		'absent_ceremony' => (rgar( $entry, '109' ) == 'No' ? 1 : 0),
		'sem_feedback' => $feedback,
		'pay_type' => rgar( $entry, '70' ),
		'pay_amount' => 0.00,
		'pay_received' => 0.00,
		'pay_currency' => 'JPY',
		'fee_meals' => $sem_fee_breakfast + $sem_fee_lunch + $sem_fee_dinner,    
        'ip' => GFFormsModel::get_ip(),
		'fee_discount' => 0.00,
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
	// ---END----------------------------------------------------------------------

    $language = GetLanguageDescription($participant['prefLanguage']);
	$country = GetCountryDescription($participant['country']);
	$language_iso = apply_filters( 'wpml_current_language', NULL );

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

		$cancel_permalink = '';
		$modify_permalink = '';

		switch ($language_iso) {
			case 'en':
				$cancel_permalink = get_permalink(351438) . '?selector=' . $selector;;
				$modify_permalink = get_permalink(351435) . '?selector=' . $selector;;
				break;
			case 'fr':
				$cancel_permalink = get_permalink(0) . '?selector=' . $selector;;
				$modify_permalink = get_permalink(0) . '?selector=' . $selector;;
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
    
} // notification_23