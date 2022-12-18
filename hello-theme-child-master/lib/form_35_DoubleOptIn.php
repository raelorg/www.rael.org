<?php

// ----------------------------------------------------
// Form : Subscription Confirmation (35)
// > Pre-populate the confirmation form (double opt-in)
// ----------------------------------------------------
add_filter( 'gform_pre_render_35', 'confirmation_pre_render_35' );
function confirmation_pre_render_35( $form ) {
	//do_action( 'hook_push_rejects_to_elohimnet' );

	$ml_token = GetToken( 'ml_dev' );
	$ml_service   = GetService( 'ml' );
	
	$GLOBALS['selector'] = $_GET['selector'];
	$GLOBALS['raelorg_session_ID'] = GetUniqueIdSession();
	$GLOBALS['raelorg_ip_address'] = GFFormsModel::get_ip();
	$GLOBALS['subscriber_country'] = "";
	$GLOBALS['subscriber_region'] = "";
	$GLOBALS['raelorg_country_from_ip'] = "";
	$GLOBALS['raelorg_countries'] = array();

	while ( $GLOBALS['raelorg_country_from_ip'] == "" )
		{
			$ip_data = @json_decode(wp_remote_retrieve_body(wp_remote_get( "http://ip-api.com/json/".$GLOBALS['raelorg_ip_address'])));

			if ( $ip_data->status == "success" ) {
				$GLOBALS['raelorg_country_from_ip'] = $ip_data->countryCode;
			}
		}

	$contact = SelectContact( $GLOBALS['selector'] );
	
	if ( $contact !== null) {
		$GLOBALS['subscriber_country'] = $contact->country;
		$GLOBALS['subscriber_region'] = $contact->area; 
	}

	$options_get = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/json\r\n"
		)
	);

	// Fill fields
	foreach ( $form['fields'] as $field )  {

		switch ( $field->id ) {
			case 3: // Email
				if ( $contact !== null ) {
					$field->defaultValue = $contact->email;
                }


                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        jQuery("#input_35_3").attr("readonly", "readonly");
                    });
                </script>
                <?php

                break;

			case 1: // Name
				if ( $contact !== null )  {
					$field->inputs[1]['defaultValue'] = $contact->firstname;
					$field->inputs[3]['defaultValue'] = $contact->lastname;
				}
				break;

			case 4: // Prefered Language
				$data = array(
					'values' => 'languages',
					'token' => $ml_token
				);

				// The language list includes only the languages that have the mailing
				$url          = $ml_service . http_build_query( $data );
				$context_get  = stream_context_create( $options_get );
				$contents     = file_get_contents( $url, false, $context_get );
				$json_data    = json_decode( $contents, true );
				$language_iso = ( empty( $contact->language ) ? '00' : $contact->language );
				$choices      = array ();

				foreach ( $json_data as $key => $item ) {
					$choices[] = array(
						'text' => $item,
						'value' => $key,
						'isSelected' => ( $language_iso === $key ),
					);
				}

				array_multisort( $choices, SORT_ASC );

				$field->choices = $choices;

				break;

			case 5: // Country and Region cannot be populated here
				$country = GetParentLabelCountry( apply_filters( 'wpml_current_language', NULL ) );
                $area = GetChildrenLabelArea( apply_filters( 'wpml_current_language', NULL ) );

                $field->inputs = array(
                  array(
                    'id' => "{$field->id}.1",
                    'label' => '*' . $country
                  ),
                  array(
                    'id' => "{$field->id}.2",
                    'label' => '*' . $area
                  ),
                );
				
				// Bug into Chained Selects List:
				// > Loading countries into a global variable to avoid twice http request 
				// > The list of countries includes only those that have mailing
				$data = array(
					'values' => 'countries',
					'token' => $ml_token
				);

				$url         = $ml_service . http_build_query( $data );
				$context_get = stream_context_create( $options_get );
				$contents    = file_get_contents( $url, false, $context_get );
				$json_data   = json_decode( $contents );

				foreach ( $json_data as $data ) {
					$selected = false;
					if (strtolower($data->iso) == strtolower($GLOBALS['raelorg_country_from_ip'])) {
						$selected = true;
					}
		
					$GLOBALS['raelorg_countries'][] = array(
							'text' => $data->nativeName,
							'value' => $data->iso,
							'isSelected' => $selected || ( ( ! empty( $GLOBALS['subscriber_country'] ) ) and ( $GLOBALS['subscriber_country'] === $data->iso ) ),
					);
				}
				break;
		}
	} // foreach

	return $form;
} // confirmation_pre_render_35

// -----------------------------------------------------------------------
// Form : Subscription Confirmation (35)
//    > Fill in the Country field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_35_5_1', 'confirmation_populate_country_35', 10, 7 );
function confirmation_populate_country_35( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	InsertFormsLog( $GLOBALS['raelorg_session_ID'], 'NL', 'Country', $GLOBALS['raelorg_country_from_ip'], $GLOBALS['raelorg_ip_address'], $GLOBALS['selector'] );
	
	return $GLOBALS['raelorg_countries'];
} // confirmation_populate_country_35

// ------------------------------------------------------------------
// Form : Subscription Confirmation (35)
// > Fill in the Area field
// ------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_35_5_2', 'confirmation_populate_area_35', 11, 7 );
function confirmation_populate_area_35( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	$ml_service=GetService( 'ml' );
  	$ml_token=GetToken( 'ml_dev' );

  	$selected_country = $chain_value[ "{$field->id}.1" ];

	InsertFormsLog( $GLOBALS['raelorg_session_ID'], 'NL', 'Area', $GLOBALS['raelorg_country_from_ip'], $GLOBALS['raelorg_ip_address'], $GLOBALS['selector'] );
	
  	$options_get = array(
    	'http'=>array(
      	'method'=>"GET",
      	'header'=>"Accept: application/json\r\n"
    	)
  	);

  	$data = array(
   		'country' => $selected_country,
   		'token' => $ml_token,
 	);

  	$url         = $ml_service . http_build_query( $data );
  	$context_get = stream_context_create( $options_get );
  	$contents    = file_get_contents( $url, false, $context_get );
  	$json_data   = json_decode( $contents );
  	$choices     = array ();

  	foreach ( $json_data as $data ) {
    	$choices[] = array(
      		'text' => $data->nativeName,
      		'value' => $data->id,
      		'isSelected' => ( ( ! empty( $GLOBALS['subscriber_region'] ) ) and ( $GLOBALS['subscriber_region'] === $data->id ) ),
    	);
  	}

  	return $choices;
} // confirmation_populate_region_35

// --------------------------------------------------
// Form : Subscription Confirmation (35)
// > Get subscription
// --------------------------------------------------
add_action( 'gform_after_submission_35', 'confirmation_after_submission_35', 10, 2 );
function confirmation_after_submission_35( $entry, $form ) {

	$firstname = rgar( $entry, '1.3' );
	$lastname = rgar( $entry, '1.6' );
	$email = rgar( $entry, '3' );
	$language = rgar ( $entry, '4' );
	$country = rgar( $entry, '5.1' );
	$region = rgar( $entry, '5.2' );
	
	$regions = array( $region ) + array( $GLOBALS['subscriber_region'] );

	$GLOBALS['selector'] = $_GET['selector'];

	UpdateContact( $firstname, $lastname, $language, $country, $region );
	send_person_to_ElohimNet( $firstname, $lastname, $email, $language, $country, '', $regions, '', $GLOBALS['selector'] );

} // confirmation_after_submission_35

