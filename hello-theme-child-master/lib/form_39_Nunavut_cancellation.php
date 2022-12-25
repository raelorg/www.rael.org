<?php

// ----------------------------------------------------
// > Pre-populate the form
// ----------------------------------------------------
add_filter( 'gform_pre_render_39', 'pre_render_39' );
function pre_render_39( $form ) {

	// Registration cancellation
	if ( isset($_GET['selector']) ) {
		$GLOBALS['selector'] = $_GET['selector'];

		$row = SelectContact($GLOBALS['selector']);
		$participant = GetParticipant($row->email, $row->sem_code);

        // Fill fields
        foreach ( $form['fields'] as $field )  {

            switch ($field->id) {
                case 8: // Email
                    $field->defaultValue = $participant->email;
                    break;
                case 6: // sem_code
                    $field->defaultValue = $participant->sem_code;
                    break;
                case 7: // sem_id
                    $field->defaultValue = $participant->sem_id;
                    break;
                case 4: // firstname
                    $field->defaultValue = $row->firstname;
                    break;
                case 5: // lastname
                    $field->defaultValue = $row->lastname;
                    break;
            }
        }
    }

	return $form;
} // pre_render_39

// -----------------------------------------------------
// Send a notification to the event manager
// Send a notification to the participant
// Cancel the registration into Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_39', 'notification_39', 10, 3 );
function notification_39( $notification, $form, $entry ) {

	$email = rgar( $entry, 8 );
	$sem_code = rgar( $entry, 6 );
	$sem_id = rgar( $entry, 7 );
	$firstname = rgar( $entry, 4 );
	$lastname = rgar( $entry, 5 );

	// Alert notification to the event manager
	if ( $notification['toType'] === 'email' ) {
        $fields = array(
                    'Firstname' => $firstname,
                    'Lastname' => $lastname,
                    'Email' => $email
                    );

		$notification['message'] .= setNotificationArrayFields($fields); 
    }

	// Notification sent to the person.
	if ( $notification['toType'] === 'field' ) {
		$language_iso = apply_filters( 'wpml_current_language', NULL );
		$confirmation = SelectNotification(40, 'confirmation', $language_iso);

		if ( 'not found' !== $confirmation ) {
			$notification['message'] = $confirmation;
		}
	
	    cancel_registration( $email, $sem_code, $sem_id );
        return $notification;
	}

    return $notification;
    
} // notification_39