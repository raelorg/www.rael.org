<?php
/**
 * raelorg_elohimnet_ws.php
 *
 * ==================================================================================================
 * CHANGELOG
 * ==================================================================================================
 * Date       | Author             | Description / Reason for the modification
 * ----------------------------------------------------------------------------
 * 2026-10-22 | Luc                | New external IP 147.224.210.24 => elohim.net security token
 * ==================================================================================================
 * 
 * Check out the documentation in Asana: https://app.asana.com/0/1138080737356030/1181305924804953
 *
 * All the code that follows relates to the link with elohim.net. This link allows to send
 * request contact, seminar subscriptions and newsletter subscriptions into elohim.net database.
 *
 * Values for language, region and country are provided by elohim.net
*/

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

// ---------------------------------------------------------------------------------------------------------------------------------
// Get web service URL
// ---------------------------------------------------------------------------------------------------------------------------------
function GetService( $service_name ) {
  $url = '';

  switch ( $service_name ) {
    case 'ml':
        $url = 'https://elohim.net/ws/ml/?';
        break;
    case 'person':
        $url = 'https://elohim.net/ws/person/?';
        break;
	case 'seminar':
		$url = 'https://elohim.net/ws/seminar/?';
		break;
	}

  return $url;
} // GetService

// ---------------------------------------------------------------------------------------------------------------------------------
// > IP address : 147.224.210.24
// > URL person pour obtenir le token GET : https://www.elohim.net/ws/dev/token.php?service=person&method=GET&ip=147.224.210.24
// > URL person pour obtenir le token POST : https://www.elohim.net/ws/dev/token.php?service=person&method=POST&ip=147.224.210.24
// > URL ml pour obtenir le token : https://www.elohim.net/ws/dev/token.php?ip=147.224.210.24
// > URL seminar pour obtenir le token GET : https://www.elohim.net/ws/dev/token.php?service=seminar&method=GET&ip=147.224.210.24
// > URL seminar pour obtenir le token POST : https://www.elohim.net/ws/dev/token.php?service=seminar&method=POST&ip=147.224.210.24
// ---------------------------------------------------------------------------------------------------------------------------------
function GetToken( $service ) {
  $token = '';

  switch ( $service ) {
    case 'get_person_dev':
        $token = '80b5721f194415e4ca358ed3e0b6f0222d8f1f3068e9d8f1d919796b8931e43f747ecc37cc8a1f8faeeed976025f16614ca569c2d84402f855a9e5a969191c44';
        break;
    case 'get_person_prod':
        $token = '6f9f762e3ad247360de2da415f8721d54423e99e9fc3fc65ad2ba61a1f8cbb6c38a85f9e0054b8c6937893a4e8f55027e009ed77bb89302bac54c79fbeacfbb3';
        break;
    case 'post_person_dev':
        $token = '88f94358eb1d5a3c74b76809d2e3eeb6e1b9c71b038e4e1bdf1a687641d9c7f57af07ac0c1d7aace47627a5a4e7d4d12d622c82d990e95b545d68546cd542a7c';
        break;
    case 'post_person_prod':
        $token = '7e9efa7c2211ed46c44ee4712cb6e6455bd8232c4305be9e4adc62aa632850de37123b91412a7fe3439ad856f454933836f8c4ee2765d91836d46cc20e0f9ee6';
        break;
    case 'ml_dev':
        $token = 'b7d77b559e2d2f0cc681fb077d3f873aa53d39b7538985897ffe3bceade12db97f945b365542338dea273adfc168c143f6c944d9ec5e6a225b09540ce67f5dda';
        break;
    case 'ml_pro':
        $token = '9b38b6eee54ec902a2876731846de733a1efedf8e6e1db04ebc319b3eee19eb9dc0af6db18d1f784a2cbd50318b144168d436f65cb583e9e231b9d3bde0458ad';
        break;
	case 'get_seminar_dev':
		$token = '7919263d364b46330c39f07d498a5fcb62e04833aa06e3150b909a1bc5ad2a0636f17d1c517be2c2db37a089a771dc0b0b0c47d8a6eb3969af51a23776e4c816';
		break;
	case 'get_seminar_prod':
		$token = '9f1693bead99fc14a39010b55271bad79f203690e6a8687351461bb721021ceddc5348487bab6f382c834787380d60ec2e7261e034a112a13b0cd322ce1274d0';
		break;
	case 'post_seminar_dev':
		$token = '8c1a47a0b2a6e5886234a08db7b8b0e2f5377f3bf1e4810df1bc2f97f3586bf489aaec761fe5d2f27d3004a6cd97eb04ac558db69416a930fcc93d8ce7b1f0f3';
		break;
	case 'post_seminar_prod':
		$token = 'dc3485b995a17300e588f6f159576e3f7ddb122ca4282e4203f270866054015383642d9ea97c1a932b943f1250ed78708b9899363f73915d100ad3174fc44f5d';
		break;
	}

  return $token;
} // GetToken

// --------------------------------------------------------------------
// Get the country iso code from the IP addres of the current connexion
// --------------------------------------------------------------------
function GetCountryCodeFromIP( $ip ) {
	$country_iso_from_ip = '';

	while ( $country_iso_from_ip == "" ) {
	 	$ip_data = @json_decode(wp_remote_retrieve_body(wp_remote_get( "http://ip-api.com/json/".$ip)));

	 	if ( $ip_data->status == "success" ) {
	 		$country_iso_from_ip = $ip_data->countryCode;
		}
	}

	return $country_iso_from_ip;

} // GetCountryCodeFromIP

// ---------------------------------------
// Get information of the person.
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

// --------------------------------------------------
// Find the language description
// --------------------------------------------------
function getLanguageDescription( $language_iso ) {
	$person_service=GetService( 'person' );
	$person_token=GetToken( 'get_person_dev' );

	$options_get = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/json\r\n",
					"ignore_errors" => true, // rather read result status that failing
				)
		);
	
	$language = '';

	$url         = $person_service . 'prefPublicLanguages&token=' . $person_token;
	$context_get = stream_context_create( $options_get );
	$contents    = file_get_contents( $url, false, $context_get );
	$json_data   = json_decode( $contents );

	foreach ( $json_data as $data ) {
		if ( ! is_object( $data ) ) continue;

		if ( $data->iso === $language_iso ) {
			$language = $data->nativeName;
		}
	}

	return $language;
} // getLanguageDescription

// --------------------------------------------------
// Find the country description
// --------------------------------------------------
function GetCountryDescription( $country_iso ) {
	$person_service=GetService( 'person' );
	$person_token=GetToken( 'get_person_dev' );

	$country = '';

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

	// Find the email of the country concerned in the list received from Elohim.net
	foreach ( $json_data as $data ) {
		if ( $data->iso == $country_iso ) {
			$country = $data->nativeName;
			break;
		}
	}

	return $country;
} // GetCountryDescription

// --------------------------------------------------------------------------
// Get a participant registration
// --------------------------------------------------------------------------
function getParticipant( $email, $sem_code ) {
	$seminar_service=GetService( 'seminar' );
	$seminar_token=GetToken( 'get_seminar_dev' );

	$options_get = array(
		'http'=>array(
		'method'=>"GET",
	 	'header'=>"Accept: application/json\r\n",
	 		"ignore_errors" => true, // rather read result status that failing
	 		)
	);

	$data = array(
	 	'email' => $email,
	 	'sem_code' => $sem_code,
	 	'token' => $seminar_token
	);

	$context_get = stream_context_create($options_get);
	$contents = file_get_contents($seminar_service . http_build_query($data), false, $context_get);
	$json_data = json_decode( $contents );

	$status_line = $http_response_header[0];
	preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
	$status = $match[1];

	if($status == 200){
		error_log('getParticipant - Found: ' . $status);
	
		return $json_data;	 
	} else {
		error_log('getParticipant - Not found');

		return 'Not found';
	}
}

// --------------------------------------------------------------------------
// Cancel a registration
// --------------------------------------------------------------------------
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

}

// -----------------------------------------
// Send person to Elohim.net
// -----------------------------------------
function send_person_to_ElohimNet( $firstname, $lastname, $email, $language, $country, $province, $regions, $message, $selector='', $phone='' ) {

	class PostPersonResult {
		private $status;
		private $debugContent;
		private $email;

		public function __construct($status, $debugContent, $email) {
		  $this->status = $status;
		  $this->debugContent = $debugContent;
		  $this->email = $email;
		}

		function insertContactLog ( $attempt, $type_post, $selector ) {
			global $wpdb;

			$contact_log = array();
			$contact_log['email'] = $this->email;
			$contact_log['status'] = $this->status;
			$contact_log['debug_content'] = $this->debugContent;
			$contact_log['attempt'] = $attempt;
			$contact_log['type_post'] = $type_post;
			$contact_log['selector'] = $selector;

			$wpdb->insert( 'raelorg_contacts_log', $contact_log ); 
		}

		function displayForDev( $attempt, $type_post, $selector ) {
			$this->insertContactLog( $attempt, $type_post, $selector );

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

    // Prepare the table with the data we have
	$person = array(
		'email' => $email,
		'firstname' => $firstname,
		'lastname' => $lastname,
		'country' => $country,
		'state' => $province,
		'mobile_phone' => $phone,
		'prefLanguage' => $language );

	if ( ! empty( $regions ) ) {
    	$person['mailingListsIds'] = $regions;
  	}

    // Send registration to Elohim.net
	$attempt = 0;
	  
	do {
		$result = doPostPerson($person, $email);
		++$attempt;
	} while (   ( $result->getStatus() != 200 ) &&
				( $result->getStatus() != 201 ) &&
				( $result->getStatus() != 202 ) &&
			    ( $attempt < 3 ) );
  	
	$result->displayForDev( $attempt, 'profile', $selector );
	
	// Send the message to Elohim.net
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
	  
    $result->displayForDev( $attempt, 'message', $selector );
  }

} // send_person_to_ElohimNet

// -----------------------------------------
// Send registration to Elohim.net
// -----------------------------------------
function send_participant_to_ElohimNet( $participant, $selector='' ) {

	class PostParticipantResult {
		private $status;
		private $debugContent;
		private $email;

		public function __construct($status, $debugContent, $email) {
			$this->status = $status;
			$this->debugContent = $debugContent;
			$this->email = $email;
		}

		function insertContactLog ( $attempt, $type_post, $selector ) {
			global $wpdb;

			$contact_log = array();
			$contact_log['email'] = $this->email;
			$contact_log['status'] = $this->status;
			$contact_log['debug_content'] = $this->debugContent;
			$contact_log['attempt'] = $attempt;
			$contact_log['type_post'] = $type_post;
			$contact_log['selector'] = $selector;

			$wpdb->insert( 'raelorg_contacts_log', $contact_log ); 
		}
		
		function displayForDev( $attempt, $type_post, $selector ) {
			$this->insertContactLog( $attempt, $type_post, $selector );

			if ($this->status == 201) {
				error_log( "SendParticipant Created " . $this->email );
			} else if ($this->status == 202) {
				error_log( "Sendparticipant Updated " . $this->email );
			} else {
				error_log( "Failed, status=" . $this->status . ", content=" . $this->debugContent );
			}
		}

		public function getStatus() {
      		return $this->status;
    	}
		
	} 

	function getRestResponseStatus($http_response_header) {
		$status_line = $http_response_header[0];
		preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
		$status = $match[1];
		return $status;
	}

	function doPostParticipant( $participantData ) {
        $seminar_service=GetService( 'seminar' );
        $seminar_post_token=GetToken( 'post_seminar_dev' );

		$postParticipantGenericQuery = $seminar_service . http_build_query( array( 'token' => $seminar_post_token ) );

		$json_data = json_encode($participantData);

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
		$content = file_get_contents($postParticipantGenericQuery, false, $context_ressource);

		return new PostParticipantResult(getRestResponseStatus($http_response_header), $content, $participantData['email']);
	}

    // Send registration to Elohim.net
	$attempt = 0;
	do {
		$result = doPostParticipant( $participant );
		++$attempt;
	} while (   ( $result->getStatus() != 200 ) &&
				( $result->getStatus() != 201 ) &&
				( $result->getStatus() != 202 ) &&
			    ( $attempt < 3 ) );
  	
	$result->displayForDev( $attempt, 'seminar', $selector );
} // send_participant_to_ElohimNet

//------------------------------------------------------------------------------------------------------------------
// Construct the array of values to put in the notification sent to the responsable of the event
//------------------------------------------------------------------------------------------------------------------
function setNotificationArrayFields($fields) {

    $html = '<div><table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">';

    foreach ($fields as $label => $val) {
        $html .= '<tr bgcolor="#EAF2FA"><td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>' . $label . '</strong></font></td></tr>';
        $html .= '<tr bgcolor="#FFFFFF"><td width="20">&nbsp;</td><td><font style="font-family:sans-serif; font-size:12px">' . $val . '</font></td></tr>';
    }

	$html .=

'</table></div><img class="alignnone wp-image-48617" src="https://www.rael.org/wp-content/uploads/2019/08/raelian_symbol_.svg" alt="" width="37" height="43" /> International Raelian Movement

&nbsp;

&nbsp;';

    return $html;
}

// ---------------------------------------------------------
// Send the email alert from the CRON task
// ---------------------------------------------------------
function raelorg_cron_execution()
{
	global $wpdb;

	$query   = "SELECT `id_session`,`form`,count(*) as nb_logs FROM `raelorg_forms_log` WHERE `checked` IS NULL GROUP BY `id_session`,`form` HAVING COUNT(*) < 2";
	$result  = $wpdb->get_results ( $query );

	$headers = array('Content-Type: text/html; charset=UTF-8');
	$to = 'loukesir@outlook.com';
	$subject = 'Rael.org Daily Alert Report';
	$body = 'Today the following cases have been detected:' . '<br><br>';

	foreach ( $result as $data )
	{
		$body = $body . '==> id_session: ' . $data->id_session . '; form: ' . $data->form . '; nb_logs: ' . $data->nb_logs . ';<br>';
	}

	wp_mail( $to, $subject, $body, $headers );
	
	// Keep only last 7 days
	$wpdb->query("DELETE FROM `raelorg_forms_log` WHERE `date` < DATE_ADD(now(), INTERVAL -7 DAY);");
}
add_action( 'raelorg_cron_alert', 'raelorg_cron_execution' );

// -------------------------------------------------------------------------------------
// Configure a CRON task to alert us in case the IPT or NL forms do not work.
//
if ( ! wp_next_scheduled( 'raelorg_cron_alert' ) ) {
	// Force start 1 minute later
	wp_schedule_event(time() + (1 * 1 * 1 * 60), 'daily', 'raelorg_cron_alert' );
}

// To stop the CRON task, remove the comment of the following 2 lines of code:
//$timestamp = wp_next_scheduled( 'raelorg_cron_execution' );
//wp_unschedule_event( $timestamp, 'raelorg_cron_execution' );
//
// -------------------------------------------------------------------------------------
 

// --------------------------------------------------------
// Translate the Children label for the Country-Area field
// --------------------------------------------------------
function GetChildrenLabelArea( $iso_language ) {
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

} // GetChildrenLabelArea

// --------------------------------------------------------
// Translate the parent label for the Country-Province field
// --------------------------------------------------------
function GetParentLabelCountry( $iso_language ) {
    $children = array(
        "en" =>"Country",
        "es" =>"País",
        "de" =>"Land",
		"fr" =>"Pays",
		"ar" =>"بلد",
        "bs" =>"Država",
        "bg" =>"Страна",
        "ca" =>"País",
        "cs" =>"Země",
        "sk" =>"Krajina",
        "da" =>"Land",
        "el" =>"Χώρα",
        "et" =>"Riik",
        "fa" =>"کشور",
        "fi" =>"Maa",
        "he" =>"מדינה",
        "hi" =>"देश",
        "hr" =>"Zemlja",
        "hu" =>"Ország",
        "hy" =>"Երկիր",
        "id" =>"Negara",
        "it" =>"Nazione",
        "ja" =>"国",
        "ko" =>"국가",
        "ku" =>"Welat",
        "lt" =>"Šalis",
        "mk" =>"Земја",
        "mn" =>"Улс",
        "ne" =>"देश",
        "nl" =>"Land",
        "no" =>"Land",
        "pl" =>"Kraj",
        "pt-pt" =>"País",
        "pt-br" =>"País",
        "ro" =>"Țară",
        "ru" =>"Страна",
        "sl" =>"Država",
        "sr" =>"Цоунтри",
        "sv" =>"Land",
        "ta" =>"நாடு",
        "th" =>"ประเทศ",
        "tr" =>"Ülke",
        "uk" =>"Країна",
        "vi" =>"Quốc gia",
        "yi" =>"לאַנד",
        "zh-hans" =>"国家",
        "zh-hant" =>"國家",
        "bel" =>"Краіна1",
        "kz" =>"Ел",
        "fp" =>"Bansa",
        );

    if (array_key_exists($iso_language, $children)) {
        return $children[$iso_language];
    } else {
        return 'Country';
    }

} // GetParentLabelCountry


// --------------------------------------------------------
// Translate the Children label for the Country-Province field
// --------------------------------------------------------
function GetChildrenLabelProvince( $iso_language ) {
    $children = array(
        "en" =>"State / Province / Area",
        "es" =>"Estado / provincia / área",
        "de" =>"Bundesland / Provinz / Gebiet",
		"fr" =>"État / Province / Région",
		"ar" =>"الولاية / المقاطعة / المنطقة",
        "bs" =>"Država / pokrajina / područje",
        "bg" =>"Щат / провинция / област",
        "ca" =>"Estat / Província / Àrea",
        "cs" =>"Stát / provincie / oblast",
        "sk" =>"Štát / provincia / oblasť",
        "da" =>"Stat / provins / område",
        "el" =>"Πολιτεία / Επαρχία / Περιοχή",
        "et" =>"Osariik / provints / piirkond",
        "fa" =>"استان / استان / منطقه",
        "fi" =>"Osavaltio / provinssi / alue",
        "he" =>"מדינה / מחוז / אזור",
        "hi" =>"राज्य / प्रांत / क्षेत्र",
        "hr" =>"Država / pokrajina / područje",
        "hu" =>"Állam / tartomány / terület",
        "hy" =>"Նահանգ / մարզ / տարածք",
        "id" =>"Negara Bagian / Provinsi / Area",
        "it" =>"Stato / provincia / area",
        "ja" =>"州/県/地域",
        "ko" =>"주 /도 / 지역",
        "ku" =>"Dewlet / Parêzgeh / Herêm",
        "lt" =>"Valstybė / provincija / sritis",
        "mk" =>"Држава / провинција / област",
        "mn" =>"Муж / муж",
        "ne" =>"राज्य / प्रान्त / क्षेत्र",
        "nl" =>"Staat / provincie / gebied",
        "no" =>"Stat / provins / område",
        "pl" =>"Stan / prowincja / obszar",
        "pt-pt" =>"Estado / Província / Área",
        "pt-br" =>"Estado / Província / Área",
        "ro" =>"Stat / Provincie / Zona",
        "ru" =>"Штат / провинция / область",
        "sl" =>"Država / provinca / območje",
        "sr" =>"Држава / Покрајина / Област",
        "sv" =>"Stat / provins / område",
        "ta" =>"மாநிலம் / மாகாணம் / பகுதி",
        "th" =>"รัฐ / จังหวัด / พื้นที่",
        "tr" =>"Eyalet / İl / Bölge",
        "uk" =>"Штат / провінція / область",
        "vi" =>"Bang / Tỉnh / Khu vực",
        "yi" =>"שטאַט / פּראַווינס / שטח",
        "zh-hans" =>"州/省/地区",
        "zh-hant" =>"州/省/地區",
        "bel" =>"Штат / правінцыя / вобласць",
        "kz" =>"Штат / провинция / аймақ",
        "fp" =>"Estado / Lalawigan / Lugar",
        "nk" =>"மாநிலம் / மாகாணம் / பகுதி",
        );

    if (array_key_exists($iso_language, $children)) {
        return $children[$iso_language];
    } else {
        return 'Province ';
    }

} // GetChildrenLabelProvince

// ---------------------------------------------------
// Generate a single character string.
// ---------------------------------------------------
function randomString() {
	$length = 24;
    $characters = "0123456789abcdefghijklmnopqrstuvwxyz$-_.!*()ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $string = '';
	
    for ($p = 0; $p < $length; $p++) {
        $string .= $characters[mt_rand(0, strlen($characters)-1)];
    }

	return $string;
} // randomString

// ---------------------------------------------------
// Generate a unique id_session for raelorg_forms_log.
// ---------------------------------------------------
function GetUniqueIdSession() {
	global $wpdb;

	$id_session;
	$rowcount = 0;

	do {
		$id_session = randomString();
		$query = "select id_session from raelorg_forms_log where id_session = '" . $id_session . "';";
		$result = $wpdb->get_results( $query );
		$rowcount = $wpdb->num_rows;
	} while ( $rowcount > 0 );

	return $id_session;

} // GetUniqueIdSession

// ---------------------------------------------------
// Register false contact selector for the nl-profile form only (double opt-in).
// See also the function InsertFormsLog()
//
// When a person requests a news subscription with their email address, they receive a notification 
// with a confirmation link that directs to the nl-profile form. This link has a special unique 
// parameter called 'selector' for each contact request. This 'selector' is kept in the database to 
// be able to complete the double opt-in.
//
// Example of the selector in the database before the double opt-in is done:
//    "selector","form","email","firstname","lastname","country","language","area","message","date_created","date_double_optin","news_event","ip_address","country_from_ip","selector_return"
//    "__7lo)fMl5Pw9Xn3iZc623CC","6","milad.shabani24@yahoo.com",,,,"en",,,"2021-09-04 08:59:31",NULL,,"5.188.214.222","US",NULL
//
// Example of the selector in the database after the double opt-in is done:
//    "selector","form","email","firstname","lastname","country","language","area","message","date_created","date_double_optin","news_event","ip_address","country_from_ip","selector_return"
//    "3nFaxjWmMvLI4rzE*pSIS)!v","6","a.navarrete11@hotmail.com","ANA MA","NAVARRETE","mx","es","152",,"2021-12-07 17:47:26","2021-12-23 21:42:19",,"189.217.215.163","MX","Yes"
//
// If someone tries to access this form with a 'selector' that does not exist in rael.org, it is not normal! If this happens often, 
// it may explain why there are a lot of visits but few double opt-ins.
// ---------------------------------------------------
function InsertBadSelector( $selector ) {
	global $wpdb;
	
	if ( !is_null( $selector ) ) {
		
		$row = array();
		$row['selector'] = $selector;
		$row['ip_address'] = $GLOBALS['raelorg_ip_address']; 
		$row['country'] = $GLOBALS['raelorg_country_from_ip'];

		$wpdb->insert( 'raelorg_contacts_bad_selector', $row );
	}
	
} // InsertBadSelector

// ---------------------------------------------------
// Get a notification
// ---------------------------------------------------
function SelectNotification( $form, $type, $language ) {
	global $wpdb;

	$query = "select notification from raelorg_notifications where form = " . $form . " and type = '" . $type . "' and language = '" . $language . "'";
	$row = $wpdb->get_row( $query );

	if ( null !== $row ) {
		return $row->notification;
	} else {
		return 'not found';
	}
} // SelectNotification

// --------------------------------------------------------
// Update Contact information if the contact comes back to confirm the subscription
// --------------------------------------------------------
function UpdateContactReturn( $selector ) {
	global $wpdb;

	$wpdb->update( 
		'raelorg_contacts',                   // table
		array( 'selector_return' => 'Yes' ),  // set clause
		array( 'selector' => $selector ),     // where clause
		array( '%s'	),                        // set format
		array( '%s' )                         // where format
	);

} // UpdateContactReturn

// ---------------------------------------------------
// Get a contact
// ---------------------------------------------------
function SelectContact( $selector ) {
	global $wpdb;

	$query = "select email, firstname, lastname, country, language, area, sem_code from raelorg_contacts where selector = '" . $selector . "'";
	$row = $wpdb->get_row( $query );

	if ( null !== $row ) {
		$GLOBALS['selector'] = $selector;
		UpdateContactReturn( $selector );
	} else {
		InsertBadSelector( $selector );
	}
	
	return $row;

} // SelectContact

// ---------------------------------------------------
// Insert a new contact.
// ---------------------------------------------------
function InsertContact( $firstname, $lastname, $email, $language, $country, $area, $message, $form, $news_event, $ip, $sem_code=0 ) {
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
	$contact['ip_address'] = $ip;
	$contact['country_from_ip'] = $GLOBALS['raelorg_country_from_ip'];

	if ($sem_code != 0) {
		$contact['sem_code'] = $sem_code;
	}
	
	do {
		$selector = randomString();
		$contact['selector'] = $selector; 
		$GLOBALS['selector'] = $contact['selector'];

		$row = $wpdb->insert( 'raelorg_contacts', $contact ); 
	} while ( $row != 1);
	
	return $selector;
} // InsertContact

// --------------------------------------------------------
// When submitting the double opt-in, update the contact with is information
// --------------------------------------------------------
function UpdateContact( $firstname, $lastname, $language, $country, $area ) {
	global $wpdb;

	$wpdb->update( 
		'raelorg_contacts',                            // table
		array(                                         // set clause
			'firstname' => $firstname,
			'lastname' => $lastname,
			'language' => $language,
			'country' => $country,
			'area' => $area
		),
		array( 'selector' => $GLOBALS['selector'] ),   // where clause
		array(                                         // set format
			'%s',
			'%s',
			'%s',
			'%s',
			'%s'
		),
		array( '%s' )                                  // where format
	);

} // UpdateContact

// ---------------------------------------------------
// Check if it is a repeat spammer
// ---------------------------------------------------
function checkSpam( $email ) {
	global $wpdb;

	$query = "select email, attempt from raelorg_contacts_spam where email = '" . $email . "'";
	$row = $wpdb->get_row( $query );

	return $row;

} // checkSpam

// ---------------------------------------------------
// Insert a new spammer.
// ---------------------------------------------------
function InsertSpam( $email ) {
	global $wpdb;
	$row = 1;

	$spam = array();
	$spam['email'] = $email;
	$spam['attempt'] = 1;

	$row = $wpdb->insert( 'raelorg_contacts_spam', $spam ); 
	
} // InsertSpam

// --------------------------------------------------------
// Update spammer recurrence
// --------------------------------------------------------
function UpdateSpam( $email, $attempt, $ip ) {
	global $wpdb;

	$wpdb->update( 
		'raelorg_contacts_spam', 
		array(
			'email' => $email,
			'attempt' => $attempt + 1,
			'ip_address' => $ip
		),
		array( 'email' => $email ),
		array(
			'%s',
			'%d',
			'%s'
		),
		array( '%s' )
	);

} // UpdateSpam

// ---------------------------------------------------
// Insert a log to detect if the form is working.
// See also the function InsertBadSelector()
//
// How to detect if the form is working? Whenever we've had an issue in the past it's because Cloudflare or 
// Rocket loader was preventing the Province field from loading with the selected country. The load function 
// of the Province field was not called. In this situation, it was not possible to submit the form because Country 
// and Province are required. This is what we want to detect.
// 
// Solution
// Now that the country is preselected, it is possible to check whether the execution of the Country and Province 
// fields have been executed by recording the passage of the execution in the database. We always expect to have two 
// entries for the same session. 
//
// > Here is an example if the form is working:
//    "id", "id_session","form","relation","date","language","country","ip_address","selector","checked","comment"
//    "4123","VUL_PHnYPRqCFauNycwyKtK5","IPT","Country","2021-12-27 14:57:31","en","DE","144.76.23.209","N/A",NULL,NULL
//    "4125","VUL_PHnYPRqCFauNycwyKtK5","IPT","Province","2021-12-27 14:57:31","en","DE","144.76.23.209","N/A",NULL,NULL
//
// > Here is an example if the form is not working:
//	  "4019","-W_0FkPpG.oMVI!rNTFW.djz","IPT","Country","2021-12-27 09:43:27","fp","DE","144.76.23.194","N/A",NULL,NULL
//
// The SQL query to detect if the form is not working is as follows:
//     > SELECT `id_session`,`form`,count(*) as nb_logs 
//       FROM `raelorg_forms_log` 
//       WHERE `checked` IS NULL 
//       GROUP BY `id_session`,`form` 
//       HAVING COUNT(*) < 2
// ---------------------------------------------------
function InsertFormsLog( $id_session, $form, $relation, $country, $ip, $selector ) {
	global $wpdb;

	$log = array();
	$log['id_session'] = $id_session;
	$log['form'] = $form;
	$log['relation'] = $relation;
	$log['language'] = apply_filters( 'wpml_current_language', NULL );
	$log['country'] = $country;
	$log['ip_address'] = $ip;
	$log['selector'] = $selector;

    $query = "select 1 from raelorg_forms_log where id_session = '" . $id_session . "' and form = '" . $form . "' and relation = '" . $relation . "'";
	$row = $wpdb->get_row( $query );
	
	if ( null == $row ) {
		$wpdb->insert( 'raelorg_forms_log', $log ); 
	}

} // InsertFormsLog

// ------------------------------------------------
// Determine the iso language code
// ------------------------------------------------
function makeCodeLanguageIso( $language_wpml ) {

	$language_iso = $language_wpml;

	// For some languages the code used by WPML is different from the iso code of Elohim.net
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
// Determine the WPML language code
// ------------------------------------------------
function makeCodeLanguageUrl( $language_iso ) {

	$language_url = $language_iso;

	// For some languages the code used by WPML is different from the iso code of Elohim.net
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

// --------------------------------------------------------------------------------
// Return the url link of FAQ page depending of the current language
// --------------------------------------------------------------------------------
function getFAQlink() {
	$arrayFAQ = array(
		'ar' => 71399,   		// Arabic
		'bn' => 26,      		// Bengali
		'bg' => 76916,    		// Bulgarian 
		'zh-hans' => 71306,		// Chinese simplified
		'zh-hant' => 55858,		// Chinese traditional
		'cs' => 72477,          // Czech
		'da' => 176347,			// Danish
		'nl' => 77800,          // Netherland
		'en' => 26,				// English
		'fp' => 74325,			// Filipino
		'fr' => 55786,			// French
		'de' => 71602,			// German
		'el' =>	330982,			// Greek
		'he' => 55827,			// Hebrew
		'hi' => 348233,			// Hindi
		'hu' => 26,				// Hungarian
		'id' => 295130,			// Indonesian
		'it' => 55798,			// Italian
		'ja' => 55271,			// Japanese
		'ko' => 51746,			// Korean
		'lt' => 77813,			// Lithuanian
		'mn' => 76340,			// Mongolian
		'fa' => 58551,			// Persian
		'pl' => 77163,			// Polish
		'pt-pt' => 74849, 		// Portuguese
		'ro' => 61982,			// Romanian
		'ru' => 74542,			// Russian
		'sk' => 77810,			// Slovakian
		'sl' => 71157,			// Slovenian
		'es' => 55732,			// Spanish
		'sv' => 77805,			// Swedish
		'th' => 74678,			// Thai
		'tr' => 344586			// Turkish
	);

	$language_wpml = apply_filters( 'wpml_current_language', NULL );

    if (array_key_exists($language_wpml, $arrayFAQ)) {
        return get_permalink ($arrayFAQ[$language_wpml]);
    } else {
        return get_permalink ($arrayFAQ['en']);
    }
}

// --------------------------------------------------------------------------------
// Return the url link of HOME page depending of the current language
// --------------------------------------------------------------------------------
function getHOMElink() {
	$arrayHOME = array(
		'ar' => 68781,   		// Arabic
		'bn' => 11,      		// Bengali
		'bg' => 76768,    		// Bulgarian 
		'zh-hans' => 56275,		// Chinese simplified
		'zh-hant' => 55759,		// Chinese traditional
		'cs' => 64143,          // Czech
		'da' => 72650,			// Danish
		'nl' => 64695,          // Netherland
		'en' => 11,				// English
		'fp' => 59400,			// Filipino
		'fr' => 55725,			// French
		'de' => 69454,			// German
		'el' =>	329697,			// Greek
		'he' => 51744,			// Hebrew
		'hi' => 342356,			// Hindi
		'hu' => 72655,			// Hungarian
		'id' => 70544,			// Indonesian
		'it' => 55795,			// Italian
		'ja' => 55264,			// Japanese
		'ko' => 49428,			// Korean
		'lt' => 60022,			// Lithuanian
		'mn' => 56863,			// Mongolian
		'fa' => 57499,			// Persian
		'pl' => 65382,			// Polish
		'pt-pt' => 60075, 		// Portuguese
		'ro' => 57399,			// Romanian
		'ru' => 55762,			// Russian
		'sk' => 58453,			// Slovakian
		'sl' => 60076,			// Slovenian
		'es' => 55382,			// Spanish
		'sv' => 57813,			// Swedish
		'th' => 70082,			// Thai
		'tr' => 280680			// Turkish
	);

	$language_wpml = apply_filters( 'wpml_current_language', NULL );

    if (array_key_exists($language_wpml, $arrayHOME)) {
        return get_permalink ($arrayHOME[$language_wpml]);
    } else {
        return get_permalink ($arrayHOME['en']);
    }
}

// --------------------------------------------------------------------------------
// Return the url link of HOME page depending of the current language
// --------------------------------------------------------------------------------
function getDOWNLOADlink() {
	$arrayDOWNLOAD = array(
		'ar' => 71196,   		// Arabic
		'bn' => 16,      		// Bengali
		'bg' => 76781,    		// Bulgarian 
		'zh-hans' => 56276,		// Chinese simplified
		'zh-hant' => 56270,		// Chinese traditional
		'cs' => 64194,          // Czech
		'da' => 72800,			// Danish
		'nl' => 64698,          // Netherland
		'en' => 16,				// English
		'fp' => 59405,			// Filipino
		'fr' => 56862,			// French
		'de' => 66307,			// German
		'el' =>	330565,			// Greek
		'he' => 56230,			// Hebrew
		'hi' => 340841,			// Hindi
		'hu' => 72651,			// Hungarian
		'id' => 72826,			// Indonesian
		'it' => 55761,			// Italian
		'ja' => 55268,			// Japanese
		'ko' => 49447,			// Korean
		'lt' => 64960,			// Lithuanian
		'mn' => 56613,			// Mongolian
		'fa' => 57570,			// Persian
		'pl' => 65357,			// Polish
		'pt-pt' => 60077, 		// Portuguese
		'ro' => 57395,			// Romanian
		'ru' => 59480,			// Russian
		'sk' => 58363,			// Slovakian
		'sl' => 60095,			// Slovenian
		'es' => 55377,			// Spanish
		'sv' => 58175,			// Swedish
		'th' => 70684,			// Thai
		'tr' => 281097	 		// Turkish
	);

	$language_wpml = apply_filters( 'wpml_current_language', NULL );

    if (array_key_exists($language_wpml, $arrayDOWNLOAD)) {
        return get_permalink ($arrayDOWNLOAD[$language_wpml]);
    } else {
        return get_permalink ($arrayDOWNLOAD['en']);
    }
}

