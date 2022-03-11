<?php

function send_participant_to_ElohimNet( $participant ) {

	class PostParticipantResult {
		private $status;
		private $debugContent;
		private $email;

		public function __construct($status, $debugContent, $email) {
			$this->status = $status;
			$this->debugContent = $debugContent;
			$this->email = $email;
		}

		function displayForDev( $attempt, $type_post ) {
			if ($this->status == 201) {
				error_log( "SendParticipant Created " . $this->email );
			} else if ($this->status == 202) {
				error_log( "Sendparticipant Updated " . $this->email );
			} else {
				error_log( "Failed, status=" . $this->status . ", content=" . $this->debugContent );
			}
		}

		public function getStatus() {
      		return $this->status;
    	}
		
	} 

	function getRestResponseStatus($http_response_header) {
		$status_line = $http_response_header[0];
		preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
		$status = $match[1];
		return $status;
	}

	function doPostParticipant( $participantData ) {
        $seminar_service=GetService( 'seminar' );
        $seminar_post_token=GetToken( 'post_seminar_dev' );

		$postParticipantGenericQuery = $seminar_service . http_build_query( array( 'token' => $seminar_post_token ) );

		$json_data = json_encode($participantData);

		$options_post = array(
		'http' => array(
			'method' => "POST",
			'header' =>
			"Content-type: application/json\r\n" .
			"Accept: application/json\r\n" .
			"Connection: close\r\n" .
			"Content-length: " . strlen($json_data) . "\r\n",
			'protocol_version' => 1.1,
			'content' => $json_data
		)
		);
		
		$context_ressource = stream_context_create($options_post);
		
		$content = file_get_contents($postParticipantGenericQuery, false, $context_ressource);

		return new PostParticipantResult(getRestResponseStatus($http_response_header), $content, $participantData['email']);
	}

    // Send registration to Elohim.net
	$attempt = 0;
	do {
		$result = doPostParticipant( $participant );
		++$attempt;
	} while (   ( $result->getStatus() != 200 ) &&
				( $result->getStatus() != 201 ) &&
				( $result->getStatus() != 202 ) &&
			    ( $attempt < 3 ) );
  	
	$result->displayForDev( $attempt, 'profile' );
} // send_participant_to_ElohimNet

// ----------------------------------------------------
// Form : Demo Event (11)
// > Pre-populate the form
// ----------------------------------------------------
add_filter( 'gform_pre_render_11', 'pre_render_11' );
function pre_render_11( $form ) {
	$person_service=GetService( 'person' );
	$person_token=GetToken( 'get_person_dev' );

	// Registration modification
	if ( isset($_GET['selector']) ) {
		$GLOBALS['selector'] = $_GET['selector'];

		$row = SelectContact($GLOBALS['selector']);

		$data = GetParticipant($row->email, 150);
	}

	$options_get = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/json\r\n",
			"ignore_errors" => true, // rather read result status that failing
		)
	);

    $country_iso_from_ip = GetCountryCodeFromIP(GFFormsModel::get_ip());

	// Fill fields
	foreach ( $form['fields'] as $field )  {

		switch ( $field->id ) {
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

			case 3: // Prefered Language
                $url         = $person_service . 'prefPublicLanguages&token=' . $person_token;
                $context_get = stream_context_create( $options_get );
                $contents    = file_get_contents( $url, false, $context_get );
                $json_data   = json_decode( $contents );
                $items       = array ();

				$language_iso = apply_filters( 'wpml_current_language', NULL );
				
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
		}
	} // foreach

	return $form;
} // pre_render_11


function setHTMLNotificationEventManager() {

$html = 

'<p>******************* Demo event notification to responsable (example) ********************</p>
<p>Hello, Event Demo manager!</p>

<p>Please do NOT reply to this email, this is a <strong>notification</strong> from www.rael.org to let you know that someone just register the Demo Event.</p>

<span style="color: #0ecad4;">** The profile for this person has already been created in Elohim.net **</span>

<table width="99%" border="0" cellpadding="1" cellspacing="0" bgcolor="#EAEAEA">
	<tbody>
		<tr>
			<td>
				<table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
					<tbody>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Firstname</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-name-1.3</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Lastname</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-name-1.6</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Email</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-email-2</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Language</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-language-3</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Country</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-country-4</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Message</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-message-6</font> </td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>

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

	// Variables to set depending of the event
	$sem_code = 150;
	$page_title = 'Demo Event';
	$page_cancel = 'Event cancellation';

	$seminar_service=GetService( 'seminar' );
	$seminar_token=GetToken( 'get_seminar_dev' );

	$message = rgar ( $entry, '6' );

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
        'year' => '2022',       // Mandatory
        'season' => 'winter',   // Mandatory
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
		'status' => '',   		// 'structure',
		'parking' => 0,         // 1
		'survey' => '',   		//' Please give details here',
		'updateby' => 0,
		'transmission' => 0,    // 1
		'formtext' => '',       // array
		'fee_transport' => 0    // 1
    );

    $language = GetLanguageDescription($participant['prefLanguage']);
	$country = GetCountryDescription($participant['country']);

	$notification['bcc'] = 'loukesir@outlook.com'; // For followup

	// Alert notification to the event manager
	if ( $notification['toType'] === 'email' ) {

		$notification['to'] = 'lukhaton@outlook.com'; 
		$notification['subject'] = 'Event subscription notification from www.rael.org'; 
		$notification['name'] = 'International Raelian Movement'; 

        $html = setHTMLNotificationEventManager();

        $html = str_replace('field-name-1.3', $participant['firstname'], $html );
        $html = str_replace('field-name-1.6', $participant['lastname'], $html );
        $html = str_replace('field-email-2', $participant['email'], $html );
        $html = str_replace('field-language-3', $language, $html );
        $html = str_replace('field-country-4', $country, $html );
        $html = str_replace('field-message-6', $message, $html );

        $notification['message'] = $html;
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
									$participant['ip'] );

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