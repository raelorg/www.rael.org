<?php

// -----------------------------------------------------------------------------------------
// Form : IPT (28)
//    > Fill in the Language field
// -----------------------------------------------------------------------------------------
add_filter( 'gform_pre_render_28', 'contact_us_populate_28' );
function contact_us_populate_28( $form ) {
	$GLOBALS['raelorg_session_ID'] = GetUniqueIdSession();
	$GLOBALS['raelorg_ip_address'] = GFFormsModel::get_ip();
	$GLOBALS['raelorg_country_from_ip'] = "";
	$GLOBALS['raelorg_countries'] = array();

	while ( $GLOBALS['raelorg_country_from_ip'] == "" )
		{
			$ip_data = @json_decode(wp_remote_retrieve_body(wp_remote_get( "http://ip-api.com/json/".$GLOBALS['raelorg_ip_address'])));

			if ( $ip_data->status == "success" ) {
				$GLOBALS['raelorg_country_from_ip'] = $ip_data->countryCode;
				
				if ($ip_data->countryCode == 'hk') {
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

	foreach ( $form['fields'] as &$field ) {
		switch ( $field->id ) {
			case '8': // Language
				$url         = $person_service . 'prefPublicLanguages&token=' . $person_token;
				$context_get = stream_context_create( $options_get );
				$contents    = file_get_contents( $url, false, $context_get );
				$json_data   = json_decode( $contents );
				$choices     = array ();

				$language_iso = apply_filters( 'wpml_current_language', NULL );

				foreach ( $json_data as $data ) {
					if ( ! is_object( $data ) ) continue;

					$selected = false;
					if (strtolower($data->iso) == strtolower($language_iso)) {
						$selected = true;
					}

					$choices[] = array(
						'text' => $data->nativeName,
						'value' => $data->iso,
						'isSelected' => $selected
					);
				}

				array_multisort( $choices, SORT_ASC );

				$field->choices = $choices;
				break;

			case '10': // Country & Province
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
} // contact_us_populate_28

// -----------------------------------------------------------------------
// Form : IPT (28)
//    > Fill in the Country field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_28_10_1', 'contact_us_populate_country_28', 10, 7 );
function contact_us_populate_country_28( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	InsertFormsLog( $GLOBALS['raelorg_session_ID'], 'IPT', 'Country', $GLOBALS['raelorg_country_from_ip'], $GLOBALS['raelorg_ip_address'], 'N/A' );

	return $GLOBALS['raelorg_countries'];
	
} // contact_us_populate_country_28

// -----------------------------------------------------------------------
// Form : IPT (28)
//    > Fill in the Province field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_28_10_2', 'contact_us_populate_province_28', 10, 7 );
function contact_us_populate_province_28( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {
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
} // contact_us_populate_province_28

// --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
// The gform_entry_is_spam filter is used to mark entries as spam during form submission.
// Notifications and add-on feeds will NOT be processed for submissions which are marked as spam.
// As the submission completes the default “Thanks for contacting us! We will get in touch with you shortly.” message will be displayed instead of the forms configured confirmation. 
// The gform_confirmation filter can be used to change the message.
// --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
add_filter( 'gform_entry_is_spam_28', 'spam_detect_28', 11, 3 );
function spam_detect_28( $is_spam, $form, $entry ) {
	if ( $is_spam ) {
        return $is_spam;
    }
	
	$email = rgar( $entry, 3 );
	$ip_address = empty( $entry['ip'] ) ? GFFormsModel::get_ip() : $entry['ip'];

	$row = checkSpam( $email );

	if ( null !== $row ) {
		$is_spam = true;
		UpdateSpam( $email, $row->attempt, $ip_address );
	}

	if ( $is_spam && method_exists( 'GFCommon', 'set_spam_filter' ) ) {
		GFCommon::set_spam_filter( rgar( $form, 'id' ), 'The name of your spam check', 'the reason this entry is being marked as spam' );
	}

	return $is_spam;
} // spam_detect

// -----------------------------------------------------
// Form : IPT (28)
//    > Prepare the notification to the country respondent
//    > Send a notification to the person
// -----------------------------------------------------
add_filter( 'gform_notification_28', 'contact_us_notification_28', 10, 3 );
function contact_us_notification_28( $notification, $form, $entry ) {

	$person_service=GetService( 'person' );
	$person_token=GetToken( 'get_person_dev' );

	$news_event = '';
	$firstname = rgar( $entry, '1.3' );
	$lastname = rgar( $entry, '1.6' );
	$email = rgar( $entry, '3' );
	$phone = rgar( $entry, '5' );
	$language_iso = rgar( $entry, '8' );
	$message = rgar ( $entry, '9' );
	$iso_country = rgar( $entry, '10.1' );
	$province = rgar( $entry, '10.2' );

	$email_country = 'contact@rael.org';
	//$notification['bcc'] = 'loukesir@outlook.com'; // For followup

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

        $fields = array(
			'Firstname' => $firstname,
			'Lastname' => $lastname,
			'Email' => $email,
			'Language' => $language,
			'Phone' => $phone,
			'Country' => $country_name,
			'State / Province' => $province,
			'Message' => $message
			);

		$arrayFields = setNotificationArrayFields($fields); 

		// Check if a notification exist for the current language and use it as replacement
		// > Sometimes it's better to keep notifications in the database than to waste time with WPML.
		$notificationResponsable = SelectNotification(28, 'responsable', $language_iso);

		if ( 'not found' !== $notificationResponsable ) {
			$notification['message'] = $notificationResponsable;
		}
		
		//$notification['to'] = 'loukesir@outlook.com';
		$notification['message'] .= $arrayFields; 
	}

	// Notification sent to the person.
	if ( $notification['toType'] === 'field' ) {
		// Retrieve the form data

		$ip_address = empty( $entry['ip'] ) ? GFFormsModel::get_ip() : $entry['ip'];

		$selector = InsertContact( $firstname, $lastname, $email, $language_iso, $iso_country, '', $message, 28, $news_event, $ip_address );
		send_person_to_ElohimNet( $firstname, $lastname, $email, $language_iso, $iso_country, $province, '', $message, '', $phone );
		
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
} // contact_us_notification_28


