<?php

// ----------------------------------------------------
// Form : Event Cancellation
// > Pre-populate the form
// ----------------------------------------------------
add_filter( 'gform_pre_render_31', 'pre_render_31' );
function pre_render_31( $form ) {

	// Registration cancellation
	if ( isset($_GET['selector']) ) {
		$GLOBALS['selector'] = $_GET['selector'];

		$row = SelectContact($GLOBALS['selector']);

		// $json_data = GetParticipant($row->email, $row->sem_code);
		// $participant = json_decode($json_data);
		$participant = GetParticipant($row->email, $row->sem_code);

        // Fill fields
        foreach ( $form['fields'] as $field )  {

            // TECHNICAL GUIDE
            // In this switch instruction, adjust field ID and #input_99_9
            switch ($field->id) {
                case 2: // Email
                    $field->defaultValue = $participant->email;
                    ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function(){
                            jQuery("#input_31_2").attr("readonly", "readonly");
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
} // pre_render_31

// -----------------------------------------------------
// Send a notification to the event manager
// Send a notification to the participant
// Cancel the registration into Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_31', 'notification_31', 10, 3 );
function notification_31( $notification, $form, $entry ) {

	$email = rgar( $entry, 2 );
	$sem_code = rgar( $entry, 3 );
	$sem_id = rgar( $entry, 4 );
	$firstname = rgar( $entry, 5 );
	$lastname = rgar( $entry, 6 );

	// Alert notification to the event manager
	if ( $notification['toType'] === 'email' ) {
        $fields = array(
                    'Firstname' => $firstname,
                    'Lastname' => $lastname,
                    'Email' => $email
                    );

		//$notification['to'] = 'loukesir@outlook.com';
		$notification['message'] .= setNotificationArrayFields($fields); 
    }

	// Notification sent to the person.
	if ( $notification['toType'] === 'field' ) {
		$language_iso = apply_filters( 'wpml_current_language', NULL );
		$confirmation = SelectNotification(20, 'person', $language_iso);

		if ( 'not found' !== $confirmation ) {
			$notification['message'] = $confirmation;
		}
	
	    cancel_registration( $email, $sem_code, $sem_id );
        return $notification;
	}

    return $notification;
    
} // notification_31