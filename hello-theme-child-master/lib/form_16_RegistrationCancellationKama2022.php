<?php

function cancel_registration(  $email, $sem_code, $sem_id  ) {

    $seminar_service=GetService( 'seminar' );
    $seminar_post_token=GetToken( 'post_seminar_dev' );

    $data = array(
        "email"    => $email,
        "sem_code" => $sem_code,
        "sem_id"   => $sem_id
    );

    $json_data = json_encode($data);

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
    
    $postCancellationGenericQuery = $seminar_service . http_build_query( array( 'token' => $seminar_post_token ) );
    $context_ressource = stream_context_create($options_post);
    $content = file_get_contents($postCancellationGenericQuery, false, $context_ressource);

    //return new PostParticipantResult(getRestResponseStatus($http_response_header), $content, $participantData['email']);

}


// ----------------------------------------------------
// Form : Event Cancellation (16)
// > Pre-populate the form
// ----------------------------------------------------
add_filter( 'gform_pre_render_16', 'pre_render_16' );
function pre_render_16( $form ) {

	// Registration cancellation
	if ( isset($_GET['selector']) ) {
		$GLOBALS['selector'] = $_GET['selector'];

		$row = SelectContact($GLOBALS['selector']);

		$json_data = GetParticipant($row->email, $row->sem_code);
		$participant = json_decode($json_data);

        // Fill fields
        foreach ( $form['fields'] as $field )  {

            switch ($field->id) {
                case 2: // Email
                    $field->defaultValue = $participant->email;
                    ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function(){
                            jQuery("#input_12_2").attr("readonly", "readonly");
                        });
                    </script>
                    <?php
                    break;
                case 3: // sem_code
                    $field->defaultValue = $participant->sem_code;
                    break;
                case 4: // sem_id
                    $field->defaultValue = $participant->sem_id;
                    break;
                case 5: // firstname
                    $field->defaultValue = $row->firstname;
                    break;
                case 6: // lastname
                    $field->defaultValue = $row->lastname;
                    break;
            }
        }
    }

	return $form;
} // pre_render_16

//------------------------------------------------------------------------------------------------------------------
// TECHNICAL GUIDE
// Adjust this HTML depending of the information you want to show in the notification to responsable
// - The html table mus be placed on one line of code
//------------------------------------------------------------------------------------------------------------------
function setHTMLNotificationEventManager_16() {

	$html = 

'<div><table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF"><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Firstname</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family:sans-serif; font-size:12px">field-firstname</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Lastname</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family:sans-serif; font-size:12px">field-lastname</font></td></tr><tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Email</strong></font></td></tr><tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family:sans-serif; font-size:12px">field-email</font></td></tr></table></div>

<img class="alignnone wp-image-48617" src="https://www.rael.org/wp-content/uploads/2019/08/raelian_symbol_.svg" alt="" width="37" height="43" /> International Raelian Movement

&nbsp;

&nbsp;';

    return $html;
}

// -----------------------------------------------------
// Send a notification to the event manager
// Send a notification to the participant
// Cancel the registration into Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_16', 'notification_16', 10, 3 );
function notification_16( $notification, $form, $entry ) {

	$email = rgar( $entry, 2 );
	$sem_code = rgar( $entry, 3 );
	$sem_id = rgar( $entry, 4 );
	$firstname = rgar( $entry, 5 );
	$lastname = rgar( $entry, 6 );

	// Alert notification to the event manager
	if ( $notification['toType'] === 'email' ) {
		$html = setHTMLNotificationEventManager_16();

        $html = str_replace('field-firstname', $firstname, $html );
        $html = str_replace('field-lastname', $lastname, $html );
        $html = str_replace('field-email', $email, $html );

		$notification['message'] .= $html; 
    }

	// Notification sent to the person.
	if ( $notification['toType'] === 'field' ) {
		$language_iso = apply_filters( 'wpml_current_language', NULL );
		$confirmation = SelectNotification(16, 'confirmation', $language_iso);

		if ( 'not found' !== $confirmation ) {
			$notification['message'] = $confirmation;
		}
	
        // Elohim.net n'a pas encore de colonne cancel
	    // cancel_registration( $email, $sem_code, $sem_id );
        return $notification;
	}

    return $notification;
    
} // notification_12