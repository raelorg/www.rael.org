<?php

// ----------------------------------------------------
// Form : Demo Event (11)
// > Pre-populate the form
// ----------------------------------------------------
add_filter( 'gform_pre_render_11', 'pre_render_11' );
function pre_render_11( $form ) {
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
			case 2: // Email
				if ( $found ) {
					$field->inputs[0]['defaultValue'] = $participant->email;
					$field->inputs[1]['defaultValue'] = $participant->email;
					?>
    				<script type="text/javascript">
        				jQuery(document).ready(function(){
							jQuery("#input_11_2").attr("readonly", "readonly");
							jQuery("#input_11_2_2").attr("readonly", "readonly");
        				});
    				</script>
    				<?php
				}
				break;

				case 3: // Prefered Language
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
} // pre_render_11

//------------------------------------------------------------------------------------------------------------------
// TECHNICAL GUIDE
// Adjust this HTML depending of the information you want to show in the notification to responsable
// - The html table mus be placed on one line of code
//------------------------------------------------------------------------------------------------------------------
function setHTMLNotificationEventManager() {

	$html = 

'<div><table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF"><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Firstname</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family:sans-serif; font-size:12px">field-name-1.3</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Lastname</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family:sans-serif; font-size:12px">field-name-1.6</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Email</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family:sans-serif; font-size:12px">field-email-2</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Language</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family:sans-serif; font-size:12px">field-language-3</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Country</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family:sans-serif; font-size:12px">field-country-4</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Message</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family:sans-serif; font-size:12px">field-message-6</font></td></tr></table></div>

<img class="alignnone wp-image-48617" src="https://luctestoct16.temp513.kinsta.cloud/wp-content/uploads/2019/08/raelian_symbol_.svg" alt="" width="37" height="43" /> International Raelian Movement

&nbsp;

&nbsp;';

    return $html;
}

// -----------------------------------------------------
// Send a notification to the event manager
// Send a notification to the participant
// Send the participant into Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_11', 'notification_11', 10, 3 );
function notification_11( $notification, $form, $entry ) {

	$page_cancel = 'Event cancellation';
	$status = 'Registered';
	$message = rgar ( $entry, '6' );

	// -------------------------------------------------------------------------
	// TECHNICAL GUIDE
	// Variables to set depending of the event
	// -----BEGIN--------------------------------------------------------------------
	$sem_code = 150;                // e107_db_seminars_reg.semreg_id
	$year = '2022';                 // Year
	$page_title = 'Demo Event';     // Title of the web page
	$season = 'winter';             // Season
	$participant = array(
		'email' => rgar( $entry, '2' ),
		'firstname' => rgar( $entry, '1.3' ),
		'lastname' => rgar( $entry, '1.6' ),
		'country' => rgar( $entry, '4' ),
		'prefLanguage' => rgar( $entry, '3' ),
        'username' => rgar( $entry, '1.6' ) . '||' . rgar( $entry, '1.3' ), 
        'arr_date' => '0000-00-00 00:00:00',
        'arr_number' => '',     // KLM687
        'arr_location' => '',   // Airport
        'dep_date' => '0000-00-00 00:00:00', 
        'dep_number' => '',     // KLM231
        'dep_location' => '',   // Hotel
        'dinner' => 0.00,       // ($field_value === 'Yes') ? '30' : '10',
        'donation' => 0.00,     // rgar ( $entry, '41' ),
		'sem_code' => $sem_code,// Mandatory
        'year' => $year,        // Mandatory
        'season' => $season,    // Mandatory
        'ip' => GFFormsModel::get_ip(),
		'firstseminar' => 0,    // 1
		'student' => 0,         // 1
		'pay_type' => 'n/a',
		'pay_amount' => 0.00,   // 10.12,
		'pay_received' => 0.00, // 1.11,
		'pay_currency' => '',   // 'CAD',
		'translation' => '',    // 'French',
		'fee' => 0.00,          // 1.00
		'fee_accom' => 0.00,    // 1.00
		'fee_meals' => 0.00,    // 1.00
		'fee_cc' => 0.00,       // 1.00
		'fee_discount' => 0.00, // 1.00
		'paypal_txn' => '',     // '1SR7173971509790K',
		'paypal_status' => '',  // 'Completed',
		'paypal_date' => '',    // '13:54:06 Dec 17, 2008 PST',
		'paypal_fee' => 0.00,   // 1.00,
		'paypal_data' => '',    // 'array()',
		'present' => 1,         // 0
		'absent_ceremony' => 0, // 1
		'responsibility' => '', // 'Tech_team',
		'accom_days' => '',     // '2008-04-29,2008-04-30,2008-05-01,2008-05-02,2008-05-03,2008-05-04,2008-05-05',
		'accom_type' => '',     // 'Single',
		'hotel' => '',          //'Wyndham',
		'room_no' => '',        // '101',
		'meal_count' => '',     // 'Array(...)',
		'meal_breakfast' => '', // '2008-04-30,2008-05-01,2008-05-02,2008-05-03,2008-05-04,2008-05-05,2008-05-06',
		'meal_lunch' => '',     // '2008-04-30,2008-05-01,2008-05-02,2008-05-03,2008-05-04,2008-05-05,2008-05-06',
		'meal_dinner' => '',    // '2008-04-30,2008-05-01,2008-05-02,2008-05-03,2008-05-04,2008-05-05,2008-05-06',
		'duration' => 0,        // 7,
		'duration_days' => '',  // '2007-07-24,2007-07-25,2007-07-26,2007-07-27,2007-07-28,2007-07-29,2007-07-30',
		'status' => $status,    // 'structure',
		'parking' => 0,         // 1
		'survey' => '',   		//' Please give details here',
		'updateby' => 0,
		'transmission' => 0,    // 1
		'formtext' => '',       // array
		'fee_transport' => 0    // 1
    );
	// ---END----------------------------------------------------------------------

    $language = GetLanguageDescription($participant['prefLanguage']);
	$country = GetCountryDescription($participant['country']);

	$notification['bcc'] = 'loukesir@outlook.com'; // For followup

	// Alert notification to the event manager
	if ( $notification['toType'] === 'email' ) {
		$html = setHTMLNotificationEventManager();

        $html = str_replace('field-name-1.3', $participant['firstname'], $html );
        $html = str_replace('field-name-1.6', $participant['lastname'], $html );
        $html = str_replace('field-email-2', $participant['email'], $html );
        $html = str_replace('field-language-3', $language, $html );
        $html = str_replace('field-country-4', $country, $html );
        $html = str_replace('field-message-6', $message, $html );

		$notification['message'] .= $html; 
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
									11, // id form
									'', // $news_event, 
									$participant['ip'],
									$sem_code );

		$cancel_link = get_permalink( get_page_by_title( $page_cancel ) ) . '?selector=' . $selector;
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
    
} // notification_11