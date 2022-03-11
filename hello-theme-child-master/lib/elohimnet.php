<?php

// Check out the documentation in Asana: https://app.asana.com/0/1138080737356030/1181305924804953
//
// All the code that follows relates to the link with elohim.net. This link allows to
// send Request contact and newsletter subscriptions into the elohim.net database.
//
// Three forms are involved:
//   > Sunscription Confirmation (5) - double opt-in
//   > Newsletter (6) - Stay in touch with us
//   > Contact Us IPT (7), 
//
// Possible values for language, region and country are provided by elohim.net
//

// > IP address : 34.121.1.40
// > URL person pour obtenir le token GET : https://www.elohim.net/ws/dev/token.php?service=person&method=GET&ip=34.121.1.40
// > URL person pour obtenir le token POST : https://www.elohim.net/ws/dev/token.php?service=person&method=POST&ip=34.121.1.40
// > URL ml pour obtenir le token : https://www.elohim.net/ws/dev/token.php?ip=34.121.1.40

// For request method 'GET' and service 'person'
// Dev token = 6da0bce866abfd4672f63635eac4ce12f2bf25fb333968a1b8043185885ffbfa60e627d065ae7ed19ba4f47db0a176898f31253115fca031d8e8c8f30f3e48f6
// Prod token = 6f9f762e3ad247360de2da415f8721d54423e99e9fc3fc65ad2ba61a1f8cbb6c38a85f9e0054b8c6937893a4e8f55027e009ed77bb89302bac54c79fbeacfbb3

// For request method 'POST' and service 'person'
// Dev token = 567623881841c67f11a9ee3252d3f7a9e35b786e47b0d0e15305d74fb6e9ff0e4a666c17ecdef4f3bc985f007d7241f442342c09924b2eee3e2fb45c7f18fbcf
// Prod token = 24f4676829358cc37fb1df83f4ed6dee3a475b9515acf7decba45b5d2fefe86bca2490278f05ca9c16d5f4addc02bf59df17e02ea5e43924839ed0d3bf722175

// For request method 'GET' and service 'ml'
// Dev token = e9e3e879cd80387f16ac31736d8de827dbed8dff992ec5b88b8931ce7f551ea7c75f87585ea420710f9a4646002b458fbacda5e3de7e08f3c42ddf84c782b661
// Prod token = 9b38b6eee54ec902a2876731846de733a1efedf8e6e1db04ebc319b3eee19eb9dc0af6db18d1f784a2cbd50318b144168d436f65cb583e9e231b9d3bde0458ad

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
// > IP address : 34.121.1.40
// > URL person pour obtenir le token GET : https://www.elohim.net/ws/dev/token.php?service=person&method=GET&ip=34.121.1.40
// > URL person pour obtenir le token POST : https://www.elohim.net/ws/dev/token.php?service=person&method=POST&ip=34.121.1.40
// > URL ml pour obtenir le token : https://www.elohim.net/ws/dev/token.php?ip=34.121.1.40
// > URL person pour obtenir le token GET : https://www.elohim.net/ws/dev/token.php?service=seminar&method=GET&ip=34.121.1.40
// > URL person pour obtenir le token POST : https://www.elohim.net/ws/dev/token.php?service=seminar&method=POST&ip=34.121.1.40
// ---------------------------------------------------------------------------------------------------------------------------------
function GetToken( $service ) {
  $token = '';

  switch ( $service ) {
    case 'ml_pro':
        $token = '9b38b6eee54ec902a2876731846de733a1efedf8e6e1db04ebc319b3eee19eb9dc0af6db18d1f784a2cbd50318b144168d436f65cb583e9e231b9d3bde0458ad';
        break;
    case 'ml_dev':
        $token = 'e9e3e879cd80387f16ac31736d8de827dbed8dff992ec5b88b8931ce7f551ea7c75f87585ea420710f9a4646002b458fbacda5e3de7e08f3c42ddf84c782b661';
        break;
    case 'get_person_prod':
        $token = '6f9f762e3ad247360de2da415f8721d54423e99e9fc3fc65ad2ba61a1f8cbb6c38a85f9e0054b8c6937893a4e8f55027e009ed77bb89302bac54c79fbeacfbb3';
        break;
    case 'get_person_dev':
        $token = '6da0bce866abfd4672f63635eac4ce12f2bf25fb333968a1b8043185885ffbfa60e627d065ae7ed19ba4f47db0a176898f31253115fca031d8e8c8f30f3e48f6';
        break;
    case 'post_person_prod':
        $token = '24f4676829358cc37fb1df83f4ed6dee3a475b9515acf7decba45b5d2fefe86bca2490278f05ca9c16d5f4addc02bf59df17e02ea5e43924839ed0d3bf722175';
        break;
    case 'post_person_dev':
        $token = '567623881841c67f11a9ee3252d3f7a9e35b786e47b0d0e15305d74fb6e9ff0e4a666c17ecdef4f3bc985f007d7241f442342c09924b2eee3e2fb45c7f18fbcf';
        break;
	case 'get_seminar_prod':
		$token = '24f4676829358cc37fb1df83f4ed6dee3a475b9515acf7decba45b5d2fefe86bca2490278f05ca9c16d5f4addc02bf59df17e02ea5e43924839ed0d3bf722175';
		break;
	case 'get_seminar_dev':
		$token = '0c3bd890ba9948a4e3a72e410c8df234bc06b81b5dd62cd9dce4b6e6ab57936c6c192013445eb194577eac74baeb1d94c451b6e29a8be642a4670fa2fce4a3c6';
		break;
	case 'post_seminar_prod':
		$token = 'dc3485b995a17300e588f6f159576e3f7ddb122ca4282e4203f270866054015383642d9ea97c1a932b943f1250ed78708b9899363f73915d100ad3174fc44f5d';
		break;
	case 'post_seminar_dev':
		$token = 'eaf6561e3b74c471c757630df8f7aaf2d2d7f51550ce2729b1386c0c29d5e4e3665b0f65feaccfdf66bc0325c6aa40d0e15664836c93cb1ed6f5823ab2231594';
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
	 	'token' => $sem_code
	);

	$context_get = stream_context_create($options_get);
	$contents = file_get_contents($seminar_service . http_build_query($data), false, $context_get);
	$data = explode(",", $contents);

	$status_line = $http_response_header[0];
	preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
	$status = $match[1];

	if($status == 200){
		error_log('getParticipant - Found: ' . $status);
	
		return $data;	 
	} else {
		error_log('getParticipant - Not found');

		return 'Not found';
	}
}

// -----------------------------------------
// Send person to Elohim.net
// -----------------------------------------
function send_person_to_ElohimNet( $firstname, $lastname, $email, $language, $country, $province, $regions, $message ) {

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

			$wpdb->insert( 'raelorg_contacts_log', $contact_log ); 
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

    // Prepare the table with the data we have
	$person = array(
		'email' => $email,
		'firstname' => $firstname,
		'lastname' => $lastname,
		'country' => $country,
		'state' => $province,
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
  	
	$result->displayForDev( $attempt, 'profile' );
	
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
	  
    $result->displayForDev( $attempt, 'message' );
  }

} // send_person_to_ElohimNet


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

	$row = array();
	$row['selector'] = $selector;
	$row['ip_address'] = $GLOBALS['raelorg_ip_address']; 
	$row['country'] = $GLOBALS['raelorg_country_from_ip'];

	$wpdb->insert( 'raelorg_contacts_bad_selector', $row ); 
	
} // InsertBadSelector

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

	$query = "select email, firstname, lastname, country, language, area from raelorg_contacts where selector = '" . $selector . "'";
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
function InsertContact( $firstname, $lastname, $email, $language, $country, $area, $message, $form, $news_event, $ip ) {
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


// ----------------------------------------------------
// Form : Subscription Confirmation (5)
// > Pre-populate the confirmation form (double opt-in)
// ----------------------------------------------------
add_filter( 'gform_pre_render_5', 'confirmation_pre_render_5' );
function confirmation_pre_render_5( $form ) {
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

	?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            /* apply only to a textarea with a class of gf_readonly */
            jQuery("li.gf_readonly input").attr("readonly","readonly");
        });
    </script>
	<?php

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
			case 253: // Email
				if ( $contact !== null ) {
					$field->defaultValue = $contact->email;
				}
				break;

			case 248: // fisrtname
				if ( $contact !== null )  {
					$field->defaultValue = $contact->firstname;
				}
				break;

			case 249: // lastname
				if ( $contact !== null )  {
					$field->defaultValue = $contact->lastname;
				}
				break;

			case 245: // Prefered Language
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

			case 251: // Country and Region cannot be populated here
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
} // confirmation_pre_render_5

// -----------------------------------------------------------------------
// Form : Subscription Confirmation (5)
//    > Fill in the Country field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_5_251_1', 'confirmation_populate_country_5', 10, 7 );
function confirmation_populate_country_5( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	InsertFormsLog( $GLOBALS['raelorg_session_ID'], 'NL', 'Country', $GLOBALS['raelorg_country_from_ip'], $GLOBALS['raelorg_ip_address'], $GLOBALS['selector'] );
	
	return $GLOBALS['raelorg_countries'];
} // confirmation_populate_country_5

// ------------------------------------------------------------------
// Form : Subscription Confirmation (5)
// > Fill in the Area field
// ------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_5_251_2', 'confirmation_populate_region_5', 11, 7 );
function confirmation_populate_region_5( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

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
} // confirmation_populate_region_5

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
	
	$regions = array( $region ) + array( $GLOBALS['subscriber_region'] );

	$GLOBALS['selector'] = $_GET['selector'];

	UpdateContact( $firstname, $lastname, $language, $country, $region );
	send_person_to_ElohimNet( $firstname, $lastname, $email, $language, $country, '', $regions, '' );

} // confirmation_after_submission_5

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


// -----------------------------------------------------------------------------------------
// Form : IPT (7)
//    > Fill in the Language field
// -----------------------------------------------------------------------------------------
add_filter( 'gform_pre_render_7', 'contact_us_populate_7' );
function contact_us_populate_7( $form ) {

	$GLOBALS['raelorg_session_ID'] = GetUniqueIdSession();
	$GLOBALS['raelorg_ip_address'] = GFFormsModel::get_ip();
	$GLOBALS['raelorg_country_from_ip'] = "";
	$GLOBALS['raelorg_countries'] = array();

	while ( $GLOBALS['raelorg_country_from_ip'] == "" )
		{
			$ip_data = @json_decode(wp_remote_retrieve_body(wp_remote_get( "http://ip-api.com/json/".$GLOBALS['raelorg_ip_address'])));

			if ( $ip_data->status == "success" ) {
				$GLOBALS['raelorg_country_from_ip'] = $ip_data->countryCode;
			}
		}
	
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

			case 9: // Country & Province
				$country = GetParentLabelCountry( apply_filters( 'wpml_current_language', NULL ) );
				$province = GetChildrenLabelProvince( apply_filters( 'wpml_current_language', NULL ) );

				$field->inputs = array(
					array(
					'id' => "{$field->id}.1",
					'label' => '*' . $country
					),
					array(
					'id' => "{$field->id}.2",
					'label' => '*' . $province
					),
				);
				
				// Bug into Chained Selects List:
				// > Loading countries into a global variable to avoid twice http request 
				$url         = $person_service . 'countries&token=' . $person_token;
				$context_get = stream_context_create( $options_get );
				$contents    = file_get_contents( $url, false, $context_get );
				$json_data   = json_decode( $contents );

				foreach ( $json_data as $data ) {
					if ( ! is_object( $data ) ) continue;

					$selected = false;
					if (strtolower($data->iso) == strtolower($GLOBALS['raelorg_country_from_ip'])) {
						$selected = true;
					}
					
					$GLOBALS['raelorg_countries'][] = array(
						'text' => $data->nativeName,
						'value' => $data->iso,
						'isSelected' => $selected
					);
				}
				break;
			}
	}

	return $form;
} // contact_us_populate_7

// -----------------------------------------------------------------------
// Form : IPT (7)
//    > Fill in the Country field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_7_9_1', 'contact_us_populate_country_7', 10, 7 );
function contact_us_populate_country_7( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	InsertFormsLog( $GLOBALS['raelorg_session_ID'], 'IPT', 'Country', $GLOBALS['raelorg_country_from_ip'], $GLOBALS['raelorg_ip_address'], 'N/A' );

	return $GLOBALS['raelorg_countries'];
	
} // contact_us_populate_country_7

// -----------------------------------------------------------------------
// Form : IPT (7)
//    > Fill in the Province field
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_7_9_2', 'contact_us_populate_province_7', 10, 7 );
function contact_us_populate_province_7( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {
	global $wpdb;

	$selected_iso_country = $chain_value[ "{$field->id}.1" ];

	InsertFormsLog( $GLOBALS['raelorg_session_ID'], 'IPT', 'Province', $GLOBALS['raelorg_country_from_ip'], $GLOBALS['raelorg_ip_address'], 'N/A' );
	
	$choices = array ();
	$query   = "select province from raelorg_country_province where code_country = '" . $selected_iso_country . "' and active = 1 order by province";
	$result  = $wpdb->get_results ( $query );

	foreach ( $result as $data )
	{
		$choices[] = array(
			'text' => $data->province,
			'value' => $data->province
		);
	}

	return $choices;
} // contact_us_populate_province_7

// --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
// The gform_entry_is_spam filter is used to mark entries as spam during form submission.
// Notifications and add-on feeds will NOT be processed for submissions which are marked as spam.
// As the submission completes the default “Thanks for contacting us! We will get in touch with you shortly.” message will be displayed instead of the forms configured confirmation. 
// The gform_confirmation filter can be used to change the message.
// --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
add_filter( 'gform_entry_is_spam_7', 'spam_detect', 11, 3 );
function spam_detect( $is_spam, $form, $entry ) {
	if ( $is_spam ) {
        return $is_spam;
    }
	
	$ip_address = empty( $entry['ip'] ) ? GFFormsModel::get_ip() : $entry['ip'];
	$firstname = rgar( $entry, 1 );
	$lastname = rgar( $entry, 2 );
	$email = rgar( $entry, 3 );

	$length_firsname = strlen( $firstname );  // RodneyTrido
	$length_lastname = strlen( $lastname );   // RodneyTridoVF
	$rest = substr($lastname, -2);    		  // VF
	$length_rest = strlen( $rest );			  // 2
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
		 ||	(   ( is_numeric( $firstname )  ) 
			 && ( is_numeric( $lastname )  ) ) 
		 || (   ( strpos( $lastname, $firstname ) === 0 )  // pos start at 0
			 && ( ( $length_lastname - $length_firsname) === 2 )
			 && ( ctype_upper($rest) )
			 && ( $length_rest === 2 ) ) ) {
 
		if ( null == $row ) {
			InsertSpam( $email );
			$selector = InsertContact( $firstname, $lastname, $email, rgpost( 'input_4' ), rgpost( 'input_8' ), '', rgpost( 'input_6' ), 7, '', $ip_address );
		}
		else {
			UpdateSpam( $email, $row->attempt, $ip_address );
		}
		
		return true;
 	}
	
	return false;
} // spam_detect

// -----------------------------------------------------
// Form : IPT (7)
//    > Prepare the notification to the country respondent
//    > Send a notification to the person
// -----------------------------------------------------
add_filter( 'gform_notification_7', 'contact_us_notification_7', 10, 3 );
function contact_us_notification_7( $notification, $form, $entry ) {

	$html = 
'<p>Hello, IPT manager!</p>

<p>Please do NOT reply to this email, this is a <strong>notification</strong> from www.rael.org to let you know that someone wishes to get in touch with us.</p>

<table width="99%" border="0" cellpadding="1" cellspacing="0" bgcolor="#EAEAEA">
	<tbody>
		<tr>
			<td>
				<table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
					<tbody>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Firstname</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-fisrtname-1</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Lastname</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-lastname-2</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Email</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px"><a href="field-email-3" target="_blank" rel="noopener noreferrer" data-auth="NotApplicable" id="LPlnk737062">field-email-3</a></font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Language</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-language-4</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Country</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-state-9.1</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>State/Province/Area</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-state-9.2</font> </td>
						</tr>
						<tr bgcolor="#EAF2FA">
							<td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Message</strong></font> </td>
						</tr>
						<tr bgcolor="#FFFFFF">
							<td width="20">&nbsp;</td>
							<td><font style="font-family:sans-serif; font-size:12px">field-message-6</font> </td>
						</tr>
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
<img class="alignnone wp-image-48617" src="https://www.rael.org/wp-content/uploads/2019/08/raelian_symbol_.svg" alt="" width="37" height="43" /> International Raelian Movement

&nbsp;
';

	$person_service=GetService( 'person' );
	$person_token=GetToken( 'get_person_dev' );

	$firstname = rgar( $entry, '1' );
	$lastname = rgar( $entry, '2' );
	$email = rgar( $entry, '3' );
	$language_iso = rgar( $entry, '4' );
	$message = rgar ( $entry, '6' );
	$iso_country = rgar( $entry, '9.1' );
	$province = rgar( $entry, '9.2' );

	$email_country = 'contact@rael.org';
	$notification['bcc'] = 'loukesir@outlook.com'; // For followup

	// Find the country@rael.org email for the country
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
	$email_country  = 'dev@rael.org'; // In case not found! Shouldn't be producing.
	$country_name = '';

	// Find the email of the country concerned in the list received from Elohim.net
	foreach ( $json_data as $data ) {
		if ( $data->iso == $iso_country ) {
			$email_country = $data->email;  // email is found
			$country_name = $data->nativeName;
			break;
		}
	}

	// Alert notification to the country respondent
	if ( $notification['toType'] === 'email' ) {
		$notification['to'] = $email_country; 
		$notification['subject'] = 'Contact notification from www.rael.org'; 
		$notification['name'] = 'International Raelian Movement'; 
	    $language = GetLanguageDescription($language_iso);

		$html = str_replace('field-fisrtname-1', $firstname, $html );
		$html = str_replace('field-lastname-2', $lastname, $html );
		$html = str_replace('field-email-3', $email, $html );
		$html = str_replace('field-language-4', $language, $html );
		$html = str_replace('field-state-9.1', $country_name, $html );
		$html = str_replace('field-state-9.2', $province, $html );
		$html = str_replace('field-message-6', $message, $html );

		$notification['message'] = $html;
	}

	// Notification sent to the person.
	if ( $notification['toType'] === 'field' ) {
		// Retrieve the form data

		$ip_address = empty( $entry['ip'] ) ? GFFormsModel::get_ip() : $entry['ip'];

		$selector = InsertContact( $firstname, $lastname, $email, $language_iso, $iso_country, '', $message, 7, $news_event, $ip_address );
		send_person_to_ElohimNet( $firstname, $lastname, $email, $language_iso, $iso_country, $province, '', $message );
		
		// Note: Even if a URL contains the English slug for another language, WPML will resolve the slug for us.
		$link_faq = get_permalink( get_page_by_title( 'FAQ' ) );
		$link_download = get_permalink( get_page_by_title( 'DOWNLOADS' ) );
		$link_rael = get_permalink( get_page_by_title( 'HOME' ) );

		if ( strstr( $notification['message'], '{link_to_faq}' ) ) {
			$notification['message'] = str_replace('{link_to_faq}', $link_faq, $notification['message'] );
			$notification['message'] = str_replace('{link_to_download}', $link_download, $notification['message'] );
			$notification['message'] = str_replace('{link_to_rael_org}', $link_rael, $notification['message'] );
		}
		elseif ( strstr( $notification['message'], '%7Blink_to_faq%7D' ) ) {
			$notification['message'] = str_replace('%7Blink_to_faq%7D', $link_faq, $notification['message'] );
			$notification['message'] = str_replace('%7Blink_to_download%7D', $link_download, $notification['message'] );
			$notification['message'] = str_replace('%7Blink_to_rael_org%7D', $link_rael, $notification['message'] );
		} else {
			$notification['message'] = str_replace('%7blink_to_faq%7d', $link_faq, $notification['message'] );
			$notification['message'] = str_replace('%7blink_to_download%7d', $link_download, $notification['message'] );
			$notification['message'] = str_replace('%7blink_to_rael_org%7d', $link_rael, $notification['message'] );
		}

		$notification['replyTo'] = $email_country;
	}

	return $notification;
} // contact_us_notification_7


add_action( 'hook_push_rejects_to_elohimnet', 'push_rejects_to_elohimnet', 13 );
function push_rejects_to_elohimnet () {

	send_person_to_ElohimNet( 'John', 'EDMONDS', 'SEAGHOST15@YAHOO.COM', 'en', 'us', 'Florida', '', 'I am very interested to learn more.' );
	send_person_to_ElohimNet( 'azul', 'Amirouch', 'azemour58@gmail.com', 'en', 'ma', 'Salé', '', 'hello i want to go away to planet like our earth but this planet has a volume as sun.' );
	
}


