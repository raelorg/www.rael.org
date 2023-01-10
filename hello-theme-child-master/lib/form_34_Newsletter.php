<?php

// ----------------------------------------------------------------------
// Form : Newsletter (34)
// > changes form tabindex start value to 99
// ----------------------------------------------------------------------
add_filter( 'gform_tabindex_34', 'change_tabindex_34' , 10, 2 );
function change_tabindex_34( $tabindex, $form ) {
    return 99;
}

// --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
// The gform_entry_is_spam filter is used to mark entries as spam during form submission.
// Notifications and add-on feeds will NOT be processed for submissions which are marked as spam.
// As the submission completes the default “Thanks for contacting us! We will get in touch with you shortly.” message will be displayed instead of the forms configured confirmation. 
// The gform_confirmation filter can be used to change the message.
// --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
add_filter( 'gform_entry_is_spam_34', 'spam_detect_34', 11, 3 );
function spam_detect_34( $is_spam, $form, $entry ) {
	if ( $is_spam ) {
        return $is_spam;
    }
	
	$ip_address = empty( $entry['ip'] ) ? GFFormsModel::get_ip() : $entry['ip'];
	$email = rgar( $entry, 1 );
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
        ) {
 
		if ( null == $row ) {
			$language_wpml = apply_filters( 'wpml_current_language', NULL );
			$language_iso = makeCodeLanguageIso( $language_wpml );
			
			InsertSpam( $email );
			$selector = InsertContact( '', '', $email, $language_iso, '', '', '', 34, '', $ip_address );
		}
		else {
			UpdateSpam( $email, $row->attempt, $ip_address );
		}
		
		return true;
 	}
	
	return false;
} // spam_detect_34

// ----------------------------------------------------------------------
// Form : Newsletter (34)
// > Send the double opt-in notification to the person.
// ----------------------------------------------------------------------
add_filter( 'gform_notification_34', 'newsletter_notification_34', 10, 3 );
function newsletter_notification_34( $notification, $form, $entry ) {

	// Retrieve the form data and build the link URL
	$email = rgar( $entry, '1' );
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

	$selector = InsertContact( '', '', $email, $language_iso, '', '', '', 34, '', $ip_address );

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
} // newsletter_notification_34