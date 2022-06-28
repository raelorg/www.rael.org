<?php

// -----------------------------------------------------------------------------------------
// Form : IPT (7)
//    > Fill in the Language field
// -----------------------------------------------------------------------------------------
add_filter( 'gform_pre_render_7', 'contact_us_populate_7' );
function contact_us_populate_7( $form ) {

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

	$options_get = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/json\r\n",
			"ignore_errors" => true, // rather read result status that failing
		)
	);

	foreach ( $form['fields'] as &$field ) {
		switch ( $field->id ) {
			case 4: // Language
				$url         = $person_service . 'prefPublicLanguages&token=' . $person_token;
				$context_get = stream_context_create( $options_get );
				$contents    = file_get_contents( $url, false, $context_get );
				$json_data   = json_decode( $contents );
				$choices     = array ();

				foreach ( $json_data as $data ) {
					if ( ! is_object( $data ) ) continue;

					$choices[] = array(
						'text' => $data->nativeName,
						'value' => $data->iso,
					);
				}

				array_multisort( $choices, SORT_ASC );

				$field->choices = $choices;
				break;

			case 9: // Country & Province
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
			}
	}

	return $form;
} // contact_us_populate_7

// -----------------------------------------------------------------------
// Form : IPT (7)
//    > Fill in the Country field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_7_9_1', 'contact_us_populate_country_7', 10, 7 );
function contact_us_populate_country_7( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	InsertFormsLog( $GLOBALS['raelorg_session_ID'], 'IPT', 'Country', $GLOBALS['raelorg_country_from_ip'], $GLOBALS['raelorg_ip_address'], 'N/A' );

	return $GLOBALS['raelorg_countries'];
	
} // contact_us_populate_country_7

// -----------------------------------------------------------------------
// Form : IPT (7)
//    > Fill in the Province field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_7_9_2', 'contact_us_populate_province_7', 10, 7 );
function contact_us_populate_province_7( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {
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
} // contact_us_populate_province_7

// --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
// The gform_entry_is_spam filter is used to mark entries as spam during form submission.
// Notifications and add-on feeds will NOT be processed for submissions which are marked as spam.
// As the submission completes the default “Thanks for contacting us! We will get in touch with you shortly.” message will be displayed instead of the forms configured confirmation. 
// The gform_confirmation filter can be used to change the message.
// --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
add_filter( 'gform_entry_is_spam_7', 'spam_detect', 11, 3 );
function spam_detect( $is_spam, $form, $entry ) {
	if ( $is_spam ) {
        return $is_spam;
    }
	
	$ip_address = empty( $entry['ip'] ) ? GFFormsModel::get_ip() : $entry['ip'];
	$firstname = rgar( $entry, 1 );
	$lastname = rgar( $entry, 2 );
	$email = rgar( $entry, 3 );

	$length_firsname = strlen( $firstname );  // RodneyTrido
	$length_lastname = strlen( $lastname );   // RodneyTridoVF
	$rest = substr($lastname, -2);    		  // VF
	$length_rest = strlen( $rest );			  // 2
	$domain = strstr($email, '@');            // @clip-share.net

	$row = checkSpam( $email );

	// 1. email already exists in the spam blacklist
	// 2. le prénom et le nom sont numériques
	// 3. first and last name are numeric
	// 4. the domain is invalid
	// 5. the email does not contain xyz
	if  (	( null !== $row )
		 || ( $domain === '@clip-share.net' )
		 || ( strstr($email, 'xyz') )
		 ||	(   ( is_numeric( $firstname )  ) 
			 && ( is_numeric( $lastname )  ) ) 
		 || (   ( strpos( $lastname, $firstname ) === 0 )  // pos start at 0
			 && ( ( $length_lastname - $length_firsname) === 2 )
			 && ( ctype_upper($rest) )
			 && ( $length_rest === 2 ) ) ) {
 
		if ( null == $row ) {
			InsertSpam( $email );
			$selector = InsertContact( $firstname, $lastname, $email, rgpost( 'input_4' ), rgpost( 'input_8' ), '', rgpost( 'input_6' ), 7, '', $ip_address );
		}
		else {
			UpdateSpam( $email, $row->attempt, $ip_address );
		}
		
		return true;
 	}
	
	return false;
} // spam_detect

// -----------------------------------------------------
// Form : IPT (7)
//    > Prepare the notification to the country respondent
//    > Send a notification to the person
// -----------------------------------------------------
add_filter( 'gform_notification_7', 'contact_us_notification_7', 10, 3 );
function contact_us_notification_7( $notification, $form, $entry ) {

	$html = 
'<p>Hello, IPT manager!</p>

<p>Please do NOT reply to this email, this is a <strong>notification</strong> from www.rael.org to let you know that someone wishes to get in touch with us.</p>

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
							<td><font style="font-family:sans-serif; font-size:12px">field-fisrtname-1</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Lastname</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-lastname-2</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Email</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px"><a href="field-email-3" target="_blank" rel="noopener noreferrer" data-auth="NotApplicable" id="LPlnk737062">field-email-3</a></font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Language</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-language-4</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Country</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-state-9.1</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>State/Province/Area</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-state-9.2</font> </td>
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
<img class="alignnone wp-image-48617" src="https://www.rael.org/wp-content/uploads/2019/08/raelian_symbol_.svg" alt="" width="37" height="43" /> International Raelian Movement

&nbsp;
';

	$person_service=GetService( 'person' );
	$person_token=GetToken( 'get_person_dev' );

	$firstname = rgar( $entry, '1' );
	$lastname = rgar( $entry, '2' );
	$email = rgar( $entry, '3' );
	$language_iso = rgar( $entry, '4' );
	$message = rgar ( $entry, '6' );
	$iso_country = rgar( $entry, '9.1' );
	$province = rgar( $entry, '9.2' );

	$email_country = 'contact@rael.org';
	$notification['bcc'] = 'loukesir@outlook.com'; // For followup

	// Find the country@rael.org email for the country
	$options_get = array(
	'http'=>array(
		'method'=>"GET",
		'header'=>"Accept: application/json\r\n",
				"ignore_errors" => true, // rather read result status that failing
			)
	);

	// Obtain from Elohim.net the list of countries and the e-mails of the respondents
	$url         = $person_service . 'countries&token=' . $person_token;
	$context_get = stream_context_create( $options_get );
	$contents    = file_get_contents( $url, false, $context_get );
	$json_data   = json_decode( $contents );
	$email_country  = 'dev@rael.org'; // In case not found! Shouldn't be producing.
	$country_name = '';

	// Find the email of the country concerned in the list received from Elohim.net
	foreach ( $json_data as $data ) {
		if ( $data->iso == $iso_country ) {
			$email_country = $data->email;  // email is found
			$country_name = $data->nativeName;
			break;
		}
	}

	// Alert notification to the country respondent
	if ( $notification['toType'] === 'email' ) {
		$notification['to'] = $email_country; 
		$notification['subject'] = 'Contact notification from www.rael.org'; 
		$notification['name'] = 'International Raelian Movement'; 
	    $language = GetLanguageDescription($language_iso);

		$html = str_replace('field-fisrtname-1', $firstname, $html );
		$html = str_replace('field-lastname-2', $lastname, $html );
		$html = str_replace('field-email-3', $email, $html );
		$html = str_replace('field-language-4', $language, $html );
		$html = str_replace('field-state-9.1', $country_name, $html );
		$html = str_replace('field-state-9.2', $province, $html );
		$html = str_replace('field-message-6', $message, $html );

		$notification['message'] = $html;
	}

	// Notification sent to the person.
	if ( $notification['toType'] === 'field' ) {
		// Retrieve the form data

		$ip_address = empty( $entry['ip'] ) ? GFFormsModel::get_ip() : $entry['ip'];

		$selector = InsertContact( $firstname, $lastname, $email, $language_iso, $iso_country, '', $message, 7, $news_event, $ip_address );
		send_person_to_ElohimNet( $firstname, $lastname, $email, $language_iso, $iso_country, $province, '', $message, $selector );
		
		// Note: Even if a URL contains the English slug for another language, WPML will resolve the slug for us.
		$link_faq = getFAQlink();
		$link_download = getDOWNLOADlink();
		$link_rael = getHOMElink();

		if ( strstr( $notification['message'], '{link_to_faq}' ) ) {
			$notification['message'] = str_replace('{link_to_faq}', $link_faq, $notification['message'] );
			$notification['message'] = str_replace('{link_to_download}', $link_download, $notification['message'] );
			$notification['message'] = str_replace('{link_to_rael_org}', $link_rael, $notification['message'] );
		}
		elseif ( strstr( $notification['message'], '%7Blink_to_faq%7D' ) ) {
			$notification['message'] = str_replace('%7Blink_to_faq%7D', $link_faq, $notification['message'] );
			$notification['message'] = str_replace('%7Blink_to_download%7D', $link_download, $notification['message'] );
			$notification['message'] = str_replace('%7Blink_to_rael_org%7D', $link_rael, $notification['message'] );
		} else {
			$notification['message'] = str_replace('%7blink_to_faq%7d', $link_faq, $notification['message'] );
			$notification['message'] = str_replace('%7blink_to_download%7d', $link_download, $notification['message'] );
			$notification['message'] = str_replace('%7blink_to_rael_org%7d', $link_rael, $notification['message'] );
		}

		$notification['replyTo'] = $email_country;
	}

	return $notification;
} // contact_us_notification_7




