<?php
add_filter( 'gform_notification_64', 'notification_64', 10, 3 );
function notification_64( $notification, $form, $entry ) {

    return $notification;
}