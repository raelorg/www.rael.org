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
// Form : Event Cancellation (12)
// > Pre-populate the form
// ----------------------------------------------------
add_filter( 'gform_pre_render_12', 'pre_render_12' );
function pre_render_12( $form ) {

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
            }
        }
    }

	return $form;
} // pre_render_12


// -----------------------------------------------------
// Send a notification to the event manager
// Send a notification to the participant
// Cancel the registration into Elohim.net
// -----------------------------------------------------
add_filter( 'gform_notification_12', 'notification_12', 10, 3 );
function notification_12( $notification, $form, $entry ) {

	$email = rgar( $entry, 2 );
	$sem_code = rgar( $entry, 3 );
	$sem_id = rgar( $entry, 4 );

	// Variables to set depending of the event

	$page_title = 'Event cancellation';
	$page_cancel = 'Event cancellation';

	$notification['bcc'] = 'loukesir@outlook.com'; // For followup

	// Alert notification to the event manager
	if ( $notification['toType'] === 'email' ) {
		$notification['to'] = 'lukhaton@outlook.com'; 
		$notification['subject'] = 'Event cancellation notification from www.rael.org'; 
		$notification['name'] = 'International Raelian Movement'; 
    }

	// Notification sent to the person.
	if ( $notification['toType'] === 'field' ) {
	
	    cancel_registration( $email, $sem_code, $sem_id );
	}

    return $notification;
    
} // notification_12