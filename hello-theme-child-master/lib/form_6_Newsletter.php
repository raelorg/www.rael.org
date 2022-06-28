<?php

// ----------------------------------------------------------------------
// Form : Newsletter (6)
// > Send the double opt-in notification to the person.
// ----------------------------------------------------------------------
add_filter( 'gform_notification_6', 'newsletter_notification_6', 10, 3 );
function newsletter_notification_6( $notification, $form, $entry ) {

	// Retrieve the form data and build the link URL
	$email = rgar( $entry, '4' );
	$ip_address = empty( $entry['ip'] ) ? GFFormsModel::get_ip() : $entry['ip'];
	$GLOBALS['raelorg_country_from_ip'] = "";

	$language_wpml = apply_filters( 'wpml_current_language', NULL );
	$language_iso = makeCodeLanguageIso( $language_wpml );

	while ( $GLOBALS['raelorg_country_from_ip'] == "" )
		{
			$ip_data = @json_decode(wp_remote_retrieve_body(wp_remote_get( "http://ip-api.com/json/".$ip_address)));

			if ( $ip_data->status == "success" ) {
				$GLOBALS['raelorg_country_from_ip'] = $ip_data->countryCode;
			}
		}

	$selector = InsertContact( '', '', $email, $language_iso, '', '', '', 6, '', $ip_address );

	$link_form = makeLinkFormWithSelector( $selector );
	$link_rael = getHOMElink(); 

	if ( strstr( $notification['message'], '{link_to_confirmation_form}' ) ) {
		$notification['message'] = str_replace('{link_to_confirmation_form}', $link_form, $notification['message'] );
		$notification['message'] = str_replace('{link_to_rael_org}', $link_rael, $notification['message'] );
	}
	elseif ( strstr( $notification['message'], '%7Blink_to_confirmation_form%7D' ) ) {
		$notification['message'] = str_replace('%7Blink_to_confirmation_form%7D', $link_form, $notification['message'] );
		$notification['message'] = str_replace('%7Blink_to_rael_org%7D', $link_rael, $notification['message'] );
	} else {
		$notification['message'] = str_replace('%7blink_to_confirmation_form%7d', $link_form, $notification['message'] );
		$notification['message'] = str_replace('%7blink_to_rael_org%7d', $link_rael, $notification['message'] );
	}

	return $notification;
} // newsletter_notification_6


