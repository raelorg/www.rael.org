<?php

// All the code that follows relates to the link with elohim.net. This link allows to
// write newsletter subscriptions into the elohim.net database.
//
// Three forms are involved:
//   > Newsletter (6) - Stay in touch with us
//   > Footer Contact Us (7), if newsletter subscription is checked
//   > Sunscription Confirmation (5) - double opt-in
//
// Possible values for language, region and country are provided by elohim.net


// > IP address : 34.125.247.108
// > URL person pour obtenir le token GET : https://www.elohim.net/ws/dev/token.php?service=person&method=GET&ip=34.125.247.108
// > URL person pour obtenir le token POST : https://www.elohim.net/ws/dev/token.php?service=person&method=POST&ip=34.125.247.108
// > URL ml pour obteneir le token : https://www.elohim.net/ws/dev/token.php?ip=34.125.247.108

// For request method 'GET' and service 'person'
// Dev token = 8291a2b1ce438173074c5310dfc912a071a4b8054850929bdca9bf6737d45f0cda3be26bd721a66876a64e52de0e58dbca0e4a821aa544f1b7f1f7c0669ade44
// Prod token = 6f9f762e3ad247360de2da415f8721d54423e99e9fc3fc65ad2ba61a1f8cbb6c38a85f9e0054b8c6937893a4e8f55027e009ed77bb89302bac54c79fbeacfbb3

// For request method 'POST' and service 'person'
// Dev token = a8f66a5db8be6fe8b4dce8241f0ec17fffdd37d25f4787ea64ce320562010f92bd3c5f717db4e2910d1dc657f6f54d10dff1cf11bdeff68316c90e199304f2ae
// Prod token = 24f4676829358cc37fb1df83f4ed6dee3a475b9515acf7decba45b5d2fefe86bca2490278f05ca9c16d5f4addc02bf59df17e02ea5e43924839ed0d3bf722175

// For request method 'GET' and service 'ml'
// Dev token = 5cf518176dc8d2e198898c124e8efaa75b5dab77af72ff084ee8640c050fab2f4c1ef35b88b9699b0210fd9a09c4e66ca8e5671639134f2f60d18fd9216a07fa
// Prod token = 013bfad9cd6faea1e65e1eabaa2450523ba28f4828478cc69cbb7575be9a8d748cbcb98a1c94605017e2bcd5efb48bc0db54265622fe2ba35ce512014a90ea70

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

function GetService( $service_name ) {
  $url = '';

  switch ( $service_name ) {
    case 'ml':
        $url = 'https://elohim.net/ws/ml/?';
        break;
    case 'person':
        $url = 'https://elohim.net/ws/person/?';
        break;
  }

  return $url;
} // GetService

function GetToken( $service ) {
  $token = '';

  switch ( $service ) {
    case 'ml_pro':
        $token = '013bfad9cd6faea1e65e1eabaa2450523ba28f4828478cc69cbb7575be9a8d748cbcb98a1c94605017e2bcd5efb48bc0db54265622fe2ba35ce512014a90ea70';
        break;
    case 'ml_dev':
        $token = '5cf518176dc8d2e198898c124e8efaa75b5dab77af72ff084ee8640c050fab2f4c1ef35b88b9699b0210fd9a09c4e66ca8e5671639134f2f60d18fd9216a07fa';
        break;
    case 'get_person_prod':
        $token = '6f9f762e3ad247360de2da415f8721d54423e99e9fc3fc65ad2ba61a1f8cbb6c38a85f9e0054b8c6937893a4e8f55027e009ed77bb89302bac54c79fbeacfbb3';
        break;
    case 'get_person_dev':
        $token = '8291a2b1ce438173074c5310dfc912a071a4b8054850929bdca9bf6737d45f0cda3be26bd721a66876a64e52de0e58dbca0e4a821aa544f1b7f1f7c0669ade44';
        break;
    case 'post_person_prod':
        $token = '24f4676829358cc37fb1df83f4ed6dee3a475b9515acf7decba45b5d2fefe86bca2490278f05ca9c16d5f4addc02bf59df17e02ea5e43924839ed0d3bf722175';
        break;
    case 'post_person_dev':
        $token = 'a8f66a5db8be6fe8b4dce8241f0ec17fffdd37d25f4787ea64ce320562010f92bd3c5f717db4e2910d1dc657f6f54d10dff1cf11bdeff68316c90e199304f2ae';
        break;
  }

  return $token;
} // GetToken

// --------------------------------------------
// Conserver les paramètres reçus de l'URL
// --------------------------------------------
function add_query_vars($aVars) {
  $aVars[] = "subscriber_email";
  $aVars[] = "subscriber_firstname";
  $aVars[] = "subscriber_lastname";
  $aVars[] = "subscriber_country";
  $aVars[] = "subscriber_preferedlanguage";
  $aVars[] = "subscriber_region";
  $aVars[] = "selector";
  return $aVars;
}
add_filter('query_vars', 'add_query_vars');

$subscriber_firstname = '';
$subscriber_lastname = '';
$subscriber_email = '';
$subscriber_country = '';
$subscriber_preferedlanguage = '';
$subscriber_region = [];
$selector = '';

if(isset($wp_query->query_vars['subscriber_firstname'])) {
  $subscriber_firstname = urldecode($wp_query->query_vars['subscriber_firstname']);
}
if(isset($wp_query->query_vars['subscriber_lastname'])) {
  $subscriber_lastname = urldecode($wp_query->query_vars['subscriber_lastname']);
}
if(isset($wp_query->query_vars['subscriber_email'])) {
  $subscriber_email = urldecode($wp_query->query_vars['subscriber_email']);
}
if(isset($wp_query->query_vars['subscriber_country'])) {
  $subscriber_country = urldecode($wp_query->query_vars['subscriber_country']);
}
if(isset($wp_query->query_vars['subscriber_preferedlanguage'])) {
  $subscriber_preferedlanguage = urldecode($wp_query->query_vars['subscriber_preferedlanguage']);
}
if(isset($wp_query->query_vars['subscriber_region'])) {
  $subscriber_region = urldecode($wp_query->query_vars['subscriber_region']);
}
if(isset($wp_query->query_vars['selector'])) {
  $selector = urldecode($wp_query->query_vars['selector']);
}

// --------------------------------------------------------
// Traduire l'étiquette Children pour le champ Country-Area
// --------------------------------------------------------
function GetChildrenLabel( $iso_language ) {
    $children = array(
        "en" =>"Area",
        "es" =>"Zona",
        "de" =>"Bereich",
        "fr" =>"Région",
        "ar" =>"منطقة",
        "bs" =>"Područje",
        "bg" =>"площ",
        "ca" =>"Àrea",
        "cs" =>"Plocha",
        "sk" =>"rozloha",
        "da" =>"Areal",
        "el" =>"Περιοχή",
        "et" =>"Pindala",
        "fa" =>"حوزه",
        "fi" =>"alue",
        "he" =>"אֵזוֹר",
        "hi" =>"क्षेत्र",
        "hr" =>"područje",
        "hu" =>"Terület",
        "hy" =>"Տարածքը",
        "id" =>"Daerah",
        "it" =>"La zona",
        "ja" =>"範囲",
        "ko" =>"지역",
        "ku" =>"Dewer",
        "lt" =>"Plotas",
        "mk" =>"Област",
        "mn" =>"Талбай",
        "ne" =>"क्षेत्र",
        "nl" =>"Regioun",
        "no" =>"Område",
        "pl" =>"Powierzchnia",
        "pt-pt" =>"Área",
        "pt-br" =>"Área",
        "ro" =>"Zonă",
        "ru" =>"Площадь",
        "sl" =>"Območje",
        "sr" =>"Подручје",
        "sv" =>"Område",
        "ta" =>"பரப்பளவு",
        "th" =>"พื้นที่",
        "tr" =>"alan",
        "uk" =>"Площа",
        "vi" =>"Khu vực",
        "yi" =>"שטח",
        "zh-hans" =>"区",
        "zh-hant" =>"區",
        "bel" =>"ফোন",
        "kz" =>"តំបន់",
        "fp" =>"Lugar",
        "nk" =>"지역",
        );

    if (array_key_exists($iso_language, $children)) {
        return $children[$iso_language];
    } else {
        return 'Area';
    }

} // GetChildrenLabel

// ---------------------------------------------------
// Table SQL qui contient les contacts
// ---------------------------------------------------
function CreateTable() {
	global $wpdb;

	$table_name = $wpdb->prefix . "contacts"; 

	$charset_collate = $wpdb->get_charset_collate();
	
	$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
		selector varchar(24) not null PRIMARY KEY,
		form int not null,
		email varchar(320) not null,
		firstname varchar(50) null,
		lastname varchar(50) null,
		country varchar(2) null,
		language varchar(7) null,
		area varchar(128) null,
		message text null,
		date_created datetime default CURRENT_TIMESTAMP,
		date_double_optin datetime ON UPDATE CURRENT_TIMESTAMP,
		news_event varchar(100) null
		) " . $charset_collate .";";
	
	dbDelta( $sql );
} // CreateTable();

// ---------------------------------------------------
// Contient l'historique des spams
// ---------------------------------------------------
function CreateTable_contacts_spam() {
	global $wpdb;

	$table_name = $wpdb->prefix . "contacts_spam"; 

	$charset_collate = $wpdb->get_charset_collate();
	
	$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
		email varchar(320) not null PRIMARY KEY,
		date_created datetime default CURRENT_TIMESTAMP,
		attempt int null
		) " . $charset_collate .";";
	
	dbDelta( $sql );
} // CreateTable_contacts_spam();

// // ---------------------------------------------------
// Table SQL qui contient le log des opérations
// ---------------------------------------------------
function CreateTable_contacts_log() {
	global $wpdb;

	$table_name = $wpdb->prefix . "contacts_log"; 

	$charset_collate = $wpdb->get_charset_collate();
	
	$sql = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
		id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
		email varchar(320) not null,
		status varchar(10) null,
		debug_content text null,
		date_created datetime default CURRENT_TIMESTAMP,
		attempt int null,
		type_post varchar(10) null
		) " . $charset_collate .";";
	
	dbDelta( $sql );
} // CreateTable_contacts_log();

// ---------------------------------------------------
// Générer une chaîne de caractères unique.
// ---------------------------------------------------
function randomString() {
	$length = 24;
    $characters = "0123456789abcdefghijklmnopqrstuvwxyz$-_.!*()ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $string = '';
	
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters))];
    }

	return $string;
} // randomString

// ---------------------------------------------------
// Obtenir un contact
// ---------------------------------------------------
function SelectContact( $selector ) {
	global $wpdb;

	$query = "select email, firstname, lastname, country, language, area from wp_contacts where selector = '" . $selector . "'";
	$row = $wpdb->get_row( $query );

	if ( null !== $row ) {
		$GLOBALS['selector'] = $selector;
	}
	
	return $row;

} // SelectContact

// ---------------------------------------------------
// Insérer un nouveau contact.
// ---------------------------------------------------
function InsertContact( $firstname, $lastname, $email, $language, $country, $area, $message, $form, $news_event ) {
	global $wpdb;
	$row = 1;

	$contact = array();
	$contact['form'] = $form;
	$contact['firstname'] = $firstname;
	$contact['lastname'] = $lastname;
	$contact['email'] = $email;
	$contact['language'] = $language;
	$contact['country'] = $country;
	$contact['area'] = $area;
	$contact['message'] = $message;
	$contact['news_event'] = $news_event;
	
	do {
		$selector = randomString();
		$contact['selector'] = $selector; 
		$GLOBALS['selector'] = $contact['selector'];

		$row = $wpdb->insert( 'wp_contacts', $contact ); 
	} while ( $row != 1);
	
	return $selector;
} // InsertContact

// --------------------------------------------------------
// Mettre le contact au SUBMIT pour le double opt-in
// --------------------------------------------------------
function UpdateContact( $firstname, $lastname, $language, $country, $area ) {
	global $wpdb;

	$wpdb->update( 
		'wp_contacts', 
		array(
			'firstname' => $firstname,
			'lastname' => $lastname,
			'language' => $language,
			'country' => $country,
			'area' => $area
		),
		array( 'selector' => $GLOBALS['selector'] ),
		array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s'
		),
		array( '%s' )
	);

} // UpdateContact

// ---------------------------------------------------
// Vérifier s'il s'agit d'un spammer qui récidive
// ---------------------------------------------------
function checkSpam( $email ) {
	global $wpdb;

	$query = "select email, attempt from wp_contacts_spam where email = '" . $email . "'";
	$row = $wpdb->get_row( $query );

	return $row;

} // checkSpam

// ---------------------------------------------------
// Insérer un nouveau spammer.
// ---------------------------------------------------
function InsertSpam( $email ) {
	global $wpdb;
	$row = 1;

	$spam = array();
	$spam['email'] = $email;
	$spam['attempt'] = 1;

	$row = $wpdb->insert( 'wp_contacts_spam', $spam ); 
	
} // InsertSpam

// --------------------------------------------------------
// Mettre à jour la récidive du spammer
// --------------------------------------------------------
function UpdateSpam( $email, $attempt ) {
	global $wpdb;

	$wpdb->update( 
		'wp_contacts_spam', 
		array(
			'email' => $email,
			'attempt' => $attempt + 1
		),
		array( 'email' => $email ),
		array(
			'%s',
			'%d'
		),
		array( '%s' )
	);

} // UpdateSpam

// ---------------------------------------
// Obtenir l'information de la personne
// ---------------------------------------
function GetPersonFromElohimNet( $email ) {

  $person_service = GetService( 'person' );
  $person_token = GetToken( 'get_person_dev' );

  $options_get = array(
    'http'=>array(
      'method'=>"GET",
      'header'=>"Accept: application/json\r\n",
            "ignore_errors" => true, // rather read result status that failing
          )
  );

  $data = array(
    'email' => $email,
    'token' => $person_token
  );

  $context_get = stream_context_create($options_get);
  $contents = file_get_contents($person_service . http_build_query($data), false, $context_get);

  $status_line = $http_response_header[0];
  preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
  $status = $match[1];

  if($status == 200){
    return $contents;
  } else {
    return 'Not found';
  }

} // GetPersonFromElohimNet

// ----------------------------------------------------
// Form : Subscription Confirmation (5)
// > Pré-populer le formulaire de confirmation
// ----------------------------------------------------
add_filter( 'gform_pre_render_5', 'confirmation_pre_render_5' );
function confirmation_pre_render_5( $form ) {
	//do_action( 'hook_push_rejects_to_elohimnet' );

	?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            /* apply only to a textarea with a class of gf_readonly */
            jQuery("li.gf_readonly input").attr("readonly","readonly");
        });
    </script>
	<?php

	$selector = get_query_var( 'selector' );
	$contact = SelectContact( $selector );
	
	$person_json = GetPersonFromElohimNet( $contact->email );

	if ( null !== $contact ) {
		$GLOBALS['subscriber_email'] = $contact->email;
		$GLOBALS['subscriber_firstname'] = $contact->firstname;
		$GLOBALS['subscriber_lastname'] = $contact->lastname;
		$GLOBALS['subscriber_country'] = $contact->country;
		$GLOBALS['subscriber_preferedlanguage'] = $contact->language;
		$GLOBALS['subscriber_region'][0] = $contact->area; 
	} 	
	
	if ( ! ($person_json === 'Not found') ) {
		$person = json_decode( $person_json );

		// On donne priorité aux paramètres conservés dans Contacts
		if ( empty( $contact->firstname ) ) {
			$GLOBALS['subscriber_firstname'] = $person->{"firstname"};
		}

		if ( empty( $contact->lastname ) ) {
			$GLOBALS['subscriber_lastname'] = $person->{"lastname"};
		}

		if ( empty( $contact->country ) ) {
			$GLOBALS['subscriber_country'] = $person->{"country"};
		}

		if ( empty( $contact->language ) ) {
			$GLOBALS['subscriber_preferedlanguage'] = $person->{"prefLanguage"};
		}

		if ( empty( $contact->area ) ) {
			$GLOBALS['subscriber_region'] = $person->{"mailingListsIds"};
		}
	}

	// Fill fields
	foreach ( $form['fields'] as $field )  {

		switch ( $field->id ) {
			case 253: // Email
				$field->defaultValue = $GLOBALS['subscriber_email'];
				break;

			case 248: // fisrtname
				if ( ! empty( $contact->firstname ) ) {
					$field->defaultValue = $GLOBALS['subscriber_firstname'];
				}
				break;

			case 249: // lastname
				if ( ! empty( $contact->lastname ) ) {
					$field->defaultValue = $GLOBALS['subscriber_lastname'];
				}
				break;

			case 245: // Prefered Language
				if ( empty( $GLOBALS['subscriber_email'] ) ) {
					break; //continue;
				}

				$options_get = array(
					'http'=>array(
						'method'=>"GET",
						'header'=>"Accept: application/json\r\n"
					)
				);

				$ml_token = GetToken( 'ml_dev' );

				$data = array(
					'values' => 'languages',
					'token' => $ml_token
				);

				// La liste des langues comprend seulement les langues qui ont du mailing
				$ml_service   = GetService( 'ml' );
				$url          = $ml_service . http_build_query( $data );
				$context_get  = stream_context_create( $options_get );
				$contents     = file_get_contents( $url, false, $context_get );
				$json_data    = json_decode( $contents, true );
				$language_iso = ( empty( $GLOBALS['subscriber_preferedlanguage'] ) ? '00' : $GLOBALS['subscriber_preferedlanguage'] );
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

			case 251: // Country et Region ne peuvent pas être populé ici
                $area = GetChildrenLabel( apply_filters( 'wpml_current_language', NULL ) );

                $field->inputs = array(
                  array(
                    'id' => "{$field->id}.1",
                    'label' => $field->label
                  ),
                  array(
                    'id' => "{$field->id}.2",
                    'label' => $area
                  ),
                );
				break;
		}
	} // foreach

	return $form;
} // confirmation_pre_render_5

// -----------------------------------------------------------------------
// Form : Subscription Confirmation (5)
//    > Remplir le champ Country
//    > Las liste des pays comprend juste ceux qui ont du mailing
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_5_251_1', 'confirmation_populate_country_5', 10, 7 );
function confirmation_populate_country_5( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	$ml_service=GetService( 'ml' );
	$ml_token=GetToken( 'ml_dev' );

	$options_get = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/json\r\n"
		)
	);

	$data = array(
		'values' => 'countries',
		'token' => $ml_token
	);

	$url         = $ml_service . http_build_query( $data );
	$context_get = stream_context_create( $options_get );
	$contents    = file_get_contents( $url, false, $context_get );
	$json_data   = json_decode( $contents );
	$choices     = array ();

	foreach ( $json_data as $data ) {
		$choices[] = array(
			'text' => $data->nativeName,
			'value' => $data->iso,
			'isSelected' => ( ( ! empty( $GLOBALS['subscriber_country'] ) ) and ( $GLOBALS['subscriber_country'] === $data->iso ) ),
		);
	}

	array_multisort( $choices, SORT_ASC );

	return $choices;
} // confirmation_populate_country_5

// ------------------------------------------------------------------
// Form : Subscription Confirmation (5)
// > Remplir le champ Region
// ------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_5_251_2', 'confirmation_populate_region_5', 11, 7 );
function confirmation_populate_region_5( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	$ml_service=GetService( 'ml' );
  	$ml_token=GetToken( 'ml_dev' );

  	$selected_country = $chain_value[ "{$field->id}.1" ];

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
      		'text' => $data->name,
      		'value' => $data->id,
      		'isSelected' => ( ( ! empty( $GLOBALS['subscriber_region'] ) ) and ( $GLOBALS['subscriber_region'][0] === $data->id ) ),
    	);
  	}

  	return $choices;
} // confirmation_populate_region_5

// -----------------------------------------
// Send subscription to Elohim.net
// -----------------------------------------
function send_person_to_ElohimNet( $firstname, $lastname, $email, $language, $country, $regions, $message ) {

	class PostPersonResult {
		private $status;
		private $debugContent;
		private $email;

		public function __construct($status, $debugContent, $email) {
		  $this->status = $status;
		  $this->debugContent = $debugContent;
		  $this->email = $email;
		}

		function insertContactLog ( $attempt, $type_post ) {
			global $wpdb;

			$contact_log = array();
			$contact_log['email'] = $this->email;
			$contact_log['status'] = $this->status;
			$contact_log['debug_content'] = $this->debugContent;
			$contact_log['attempt'] = $attempt;
			$contact_log['type_post'] = $type_post;

			$wpdb->insert( 'wp_contacts_log', $contact_log ); 
		}

		function displayForDev( $attempt, $type_post ) {
			$this->insertContactLog( $attempt, $type_post );

			if ($this->status == 201) {
				error_log( "SendPersonToIPT Created " . $this->email );
			} else if ($this->status == 202) {
				error_log( "SendPersonToIPT Updated " . $this->email );
			} else {
				error_log( "Failed, status=" . $this->status . ", content=" . $this->debugContent );
			}
		}

		public function getStatus() {
      		return $this->status;
    	}
		
	} // PostPersonResult

	function getRestResponseStatus($http_response_header) {
		$status_line = $http_response_header[0];
		preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
		$status = $match[1];
		return $status;
	}

	function doPostPerson($personData, $email) {
		$person_service         = GetService( 'person' );
		$person_post_token      = GetToken( 'post_person_dev' );
		$postPersonGenericQuery = $person_service . http_build_query( array( 'token' => $person_post_token ) );

		$json_data = json_encode($personData);
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
		
		$context_ressource = stream_context_create($options_post);
		
		$content = file_get_contents($postPersonGenericQuery, false, $context_ressource);
		return new PostPersonResult(getRestResponseStatus($http_response_header), $content, $email);
	}

    // Préparer le tableau avec les données qu'on a
	$person = array(
		'email' => $email,
		'firstname' => $firstname,
		'lastname' => $lastname,
		'country' => $country,
		'prefLanguage' => $language );

	if ( ! empty( $regions ) ) {
    	$person['mailingListsIds'] = $regions;
  	}

    // Envoyer l'inscription à Elohim.net
	$attempt = 0;
	  
	do {
		$result = doPostPerson($person, $email);
		++$attempt;
	} while (   ( $result->getStatus() != 200 ) &&
				( $result->getStatus() != 201 ) &&
				( $result->getStatus() != 202 ) &&
			    ( $attempt < 3 ) );
  	
	$result->displayForDev( $attempt, 'profile' );
	
	// Envoyer le message à Elohim.net
	if ( ! empty( $message ) ) {
	    $person_message = array(
    	  	"fromEmail" => $email,
      		"message" => $message,
    );

	$attempt = 0;
	  
	do {
	    $result = doPostPerson($person_message, $email);
		++$attempt;
	} while (   ( $result->getStatus() != 200 ) &&
				( $result->getStatus() != 201 ) &&
				( $result->getStatus() != 202 ) &&
			    ( $attempt < 3 ) );
	  
    $result->displayForDev( $attempt, 'message' );
  }

} // send_person_to_ElohimNet

// --------------------------------------------------
// Form : Subscription Confirmation (5)
// > Get subscription
// --------------------------------------------------
add_action( 'gform_after_submission_5', 'confirmation_after_submission_5', 10, 2 );
function confirmation_after_submission_5( $entry, $form ) {

	$firstname = rgar( $entry, '248' );
	$lastname = rgar( $entry, '249' );
	$email = rgar( $entry, '253' );
	$language = rgar ( $entry, '245' );
	$country = rgar( $entry, '251.1' );
	$region = rgar( $entry, '251.2' );

	$regions = array( $region ) + $GLOBALS['subscriber_region'];

	UpdateContact( $firstname, $lastname, $language, $country, $region );
	send_person_to_ElohimNet( $firstname, $lastname, $email, $language, $country, $regions, '' );

	?>
	<!-- Facebook Pixel Code for India72aH -->
	<script>
		!function(f,b,e,v,n,t,s)
		{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};
		if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
		n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t,s)}(window, document,'script',
		'https://connect.facebook.net/en_US/fbevents.js');
		fbq('init', '2064272210260681');
		fbq('track', 'PageView');
		fbq('track', 'CompleteRegistration');
    </script>
	<noscript>
		<img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=2064272210260681&ev=PageView&noscript=1" />
	</noscript>
	<!-- End Facebook Pixel Code for India72aH -->	
	<?php

} // confirmation_after_submission_5

// -------------------------------------------------------------
// Form : Footer Contact Us (7)
// > Send subscription to Elohim.net
// -------------------------------------------------------------
add_action( 'gform_after_submission_7', 'footer_contact_us_after_submission_7', 10, 2 );
function footer_contact_us_after_submission_7( $entry, $form ) {

	$firstname = rgar( $entry, '1' );
	$lastname = rgar( $entry, '2' );
	$email = rgar( $entry, '3' );
	$language = rgar ( $entry, '4' );
	$country = rgar( $entry, '8' );
	$message = rgar ( $entry, '6' );

	send_person_to_ElohimNet( $firstname, $lastname, $email, $language, $country, '', $message );

} // footer_contact_us_after_submission_7

// -----------------------------------------------------------------------------------------
// Form : Footer Contact Us (7)
//    > Remplir le champ Language
//    > La liste des langues comprend toutes les langues qui sont offertes sur Elohim.net
// -----------------------------------------------------------------------------------------
add_filter( 'gform_pre_render_7', 'footer_contact_us_populate_7' );
function footer_contact_us_populate_7( $form ) {

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

			case 8: // Country
				$url         = $person_service . 'countries&token=' . $person_token;
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
		}
	}

	return $form;
} // footer_contact_us_populate_7

// ------------------------------------------------
// Validation supplémentaire pour éviter le spam
// ------------------------------------------------
add_filter( 'gform_validation_7', 'custom_validation_7' );
function custom_validation_7( $validation_result ) {
	$form = $validation_result['form'];

	$firstname = rgpost( 'input_1' );
	$lastname = rgpost( 'input_2' );
	$email = rgpost( 'input_3' );

	$length_firsname = strlen( $firstname );  // RodneyTrido
	$length_lastname = strlen( $lastname );   // RodneyTridoVF
	$rest = substr($lastname, -2);    		  // VF
	$length_rest = strlen( $rest );			  // 2
	$domain = strstr($email, '@');            // @clip-share.net

	$row = checkSpam( $email );

	// 1. email existe déjà dans la blacklist de spam
	// 2. le prénom et le nom sont numériques
	// 3. le prénom est inclus dans le nom et le nom se termine par deux caractères majuscules
	// 4. le domaine est valide
	if  (	( null !== $row )
		 || ( $domain === '@clip-share.net' )
		 ||	(   ( is_numeric( $firstname )  ) 
			 && ( is_numeric( $lastname )  ) ) 
		 || (   ( strpos( $lastname, $firstname ) === 0 )  // pos start at 0
			 && ( ( $length_lastname - $length_firsname) === 2 )
			 && ( ctype_upper($rest) )
			 && ( $length_rest === 2 ) ) ) {
 
		if ( null == $row ) {
			InsertSpam( $email );
			$selector = InsertContact( $firstname, $lastname, $email, rgpost( 'input_4' ), rgpost( 'input_8' ), '', rgpost( 'input_6' ), 7, '' );
		}
		else {
			UpdateSpam( $email, $row->attempt );
		}

        // set the form validation to false
        $validation_result['is_valid'] = false;
 
        //finding Field with ID of 1 and marking it as failed validation
        foreach( $form['fields'] as &$field ) {
			if ( $field->id == '3' ) {
                $field->failed_validation = true;
                $field->validation_message = '*';
                break;
            }
        }
 
	}
		
	//Assign modified $form object back to the validation result
	$validation_result['form'] = $form;
	return $validation_result;	
} // custom_validation_7

// ------------------------------------------------
// Déterminer le code de langue iso pour
// certaines langues
// ------------------------------------------------
function makeCodeLanguageIso( $language_wpml ) {

	$language_iso = $language_wpml;

	// Pour certaines langues, le code utilisé par WPML est différent du code iso de Elohim.net
	switch ( $language_wpml ){
		case 'zh-hans':
			$language_iso = 'cn';  // Chinois simplifié
			break;
		case 'zh-hant':
			$language_iso = 'tw';  // Chinois traditionnel
			break;
		case 'pt-pt':
			$language_iso = 'pt';    // Portuguais
			break;
	}

	return $language_iso;
} // makeCodeLanguageIso

// ------------------------------------------------
// Déterminer le code de langue de WPML pour
// certaines langues
// ------------------------------------------------
function makeCodeLanguageUrl( $language_iso ) {

	$language_url = $language_iso;

	// Pour certaines langues, le code utilisé par WPML est différent du code iso de Elohim.net
	switch ( $language_iso ){
		case 'cn':
			$language_url = 'zh-hans';  // Chinois simplifié
			break;
		case 'tw':
			$language_url = 'zh-hant';  // Chinois traditionnel
			break;
		case 'pt':
			$language_url = 'pt-pt';    // Portuguais
			break;
	}

	return $language_url;
} // makeCodeLanguageUrl

function makeLinkFormWithSelector( $selector ) {

	$link = get_permalink( get_page_by_title( '📩 Newsletter profile' ) ) . '?selector=' . $selector;

	return $link;
} // makeLinkFormWithSelector

// -----------------------------------------------------
// Form : Footer Contact Us (7)
//    > Préparer la notification au responsable du pays
//    > Envoyer une notification au subscriber seulement si
//      demandé par la personne. Cette condition est
//      validée directement dans la notification
// -----------------------------------------------------
add_filter( 'gform_notification_7', 'footer_contact_us_notification_7', 10, 3 );
function footer_contact_us_notification_7( $notification, $form, $entry ) {

	$iso_country = rgar( $entry, '8' );

	// Notification d'alerte au responsable du pays
	if ( $notification['toType'] === 'email' ) {
		if (	( $iso_country === 'ca' )
			||	( $iso_country === 'mx' )
			||	( $iso_country === 'us' ) ) {
			switch ( $iso_country ) {
				case 'ca':
					$notification['to'] = 'info@raelcanada.org' . ',loukesir@outlook.com'; 
					break;
				case 'mx':
					$notification['to'] = 'info@raelmexico.org' . ',loukesir@outlook.com'; 
					break;
				case 'us':
					$notification['to'] = 'info@raelusa.org' . ',loukesir@outlook.com'; 
					break;
				}
		}
		else {
			$person_service=GetService( 'person' );
			$person_token=GetToken( 'get_person_dev' );

			$options_get = array(
			'http'=>array(
				'method'=>"GET",
				'header'=>"Accept: application/json\r\n",
						"ignore_errors" => true, // rather read result status that failing
					)
			);

			// Obtenir de Elohim.net la liste des pays et les courriels des responsables avec la méthode countries
			$url         = $person_service . 'countries&token=' . $person_token;
			$context_get = stream_context_create( $options_get );
			$contents    = file_get_contents( $url, false, $context_get );
			$json_data   = json_decode( $contents );
			$choices     = array ();
			$email_to    = 'dev@rael.org'; // Au cas où pas trouvé! Ne devrais pas de produire

			// Rechercher le courriel du pays concerné dans la liste reçu de Elohim.net
			foreach ( $json_data as $data ) {
			if ( $data->iso == $iso_country ) {
					$email_to = $data->email;  // le courriel est trouvé
					break;
				}
				}

			$notification['to'] = $email_to . ',loukesir@outlook.com'; 
			//$notification['to'] = 'loukesir@yahoo.com';
		}
	}

	// Notification envoyée à la personne. Il existe deux notifications de
	// type 'field'. Une est envoyée si la personne demande l'abonnement à
	// la NL, sinon c'est l'autre qui est envoyée. Ici, je ne peux pas faire
	// la distinction entre les deux mais ça ne dérange pas car dans le
	// second cas, le tag {link_to_confirmation_form} n'est pas trouvé et
	// rien n'est fait
	if ( $notification['toType'] === 'field' ) {
		// Récupérer les données du formulaire
		$firstname = rgar( $entry, '1' );
		$lastname = rgar( $entry, '2' );
		$email = rgar( $entry, '3' );
		$language_iso = rgar( $entry, '4' );
		$message = rgar ( $entry, '6' );

		$field_id = 7; 
		$field = RGFormsModel::get_field( $form, $field_id );
		$news_event = is_object( $field ) ? $field->get_value_export( $entry, $field_id, true ) : '';

		$selector = InsertContact( $firstname, $lastname, $email, $language_iso, $iso_country, '', $message, 7, $news_event );
		
		// Note : Même si une URL contient le slug anglais pour une autre langue, WPML va résoudre le slug pour nous.
		$link_form = makeLinkFormWithSelector( $selector );
		$link_rael = get_permalink( get_page_by_title( 'HOME' ) );
		$link_faq = get_permalink( get_page_by_title( 'FAQ' ) );
		$link_download = get_permalink( get_page_by_title( 'DOWNLOADS' ) );

		if ( strstr( $notification['message'], '{link_to_confirmation_form}' ) ) {
			$notification['message'] = str_replace('{link_to_confirmation_form}', $link_form, $notification['message'] );
			$notification['message'] = str_replace('{link_to_rael_org}', $link_rael, $notification['message'] );
			$notification['message'] = str_replace('{link_to_faq}', $link_faq, $notification['message'] );
			$notification['message'] = str_replace('{link_to_download}', $link_download, $notification['message'] );
		}
		elseif ( strstr( $notification['message'], '%7Blink_to_confirmation_form%7D' ) ) {
			$notification['message'] = str_replace('%7Blink_to_confirmation_form%7D', $link_form, $notification['message'] );
			$notification['message'] = str_replace('%7Blink_to_rael_org%7D', $link_rael, $notification['message'] );
			$notification['message'] = str_replace('%7Blink_to_faq%7D', $link_faq, $notification['message'] );
			$notification['message'] = str_replace('%7Blink_to_download%7D', $link_download, $notification['message'] );
		} else {
			$notification['message'] = str_replace('%7blink_to_confirmation_form%7d', $link_form, $notification['message'] );
			$notification['message'] = str_replace('%7blink_to_rael_org%7d', $link_rael, $notification['message'] );
			$notification['message'] = str_replace('%7blink_to_faq%7d', $link_faq, $notification['message'] );
			$notification['message'] = str_replace('%7blink_to_download%7d', $link_download, $notification['message'] );
		}
	}

	return $notification;
} // footer_contact_us_notification_7

// ----------------------------------------------------------------------
// Form : Newsletter (6)
// > Envoyer le email de confirmation au subscriber
// ----------------------------------------------------------------------
add_filter( 'gform_notification_6', 'newsletter_notification_6', 10, 3 );
function newsletter_notification_6( $notification, $form, $entry ) {

	// Récupérer les données du formulaire et construire l'URL du lien
	$email = rgar( $entry, '4' );

	$language_wpml = apply_filters( 'wpml_current_language', NULL );
	$language_iso = makeCodeLanguageIso( $language_wpml );

	$selector = InsertContact( '', '', $email, $language_iso, '', '', '', 6, '' );

	$link_form = makeLinkFormWithSelector( $selector );
	$link_rael = get_permalink( get_page_by_title( 'HOME' ) ); 

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


add_action( 'hook_push_rejects_to_elohimnet', 'push_rejects_to_elohimnet', 13 );
function push_rejects_to_elohimnet () {

	//send_person_to_ElohimNet( 'محمد', 'رزبری', 'razbar1355@gmail.com', 'fa', 'ir', '', 'سلام.این سایت نسخه فارسی یا کردی داره؟' );
	
}

