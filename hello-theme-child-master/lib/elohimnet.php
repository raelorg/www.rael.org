<?php

// All the code that follows relates to the link with elohim.net. This link allows to
// write newsletter subscriptions into the elohim.net database.
//
// Three forms are involved:
//   > Newsletter (6) - Stay in touch with us
//   > IPT (7), if newsletter subscription is checked
//   > Sunscription Confirmation (5) - double opt-in
//
// Possible values for language, region and country are provided by elohim.net


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
// Traduire l'étiquette parent pour le champ Country-Province
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
// Traduire l'étiquette Children pour le champ Country-Province
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
		news_event varchar(100) null,
		ip_address varchar(100) null
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
function UpdateSpam( $email, $attempt, $ip ) {
	global $wpdb;

	$wpdb->update( 
		'wp_contacts_spam', 
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
      		'text' => $data->nativeName,
      		'value' => $data->id,
      		'isSelected' => ( ( ! empty( $GLOBALS['subscriber_region'] ) ) and ( $GLOBALS['subscriber_region'][0] === $data->id ) ),
    	);
  	}

  	return $choices;
} // confirmation_populate_region_5

// -----------------------------------------
// Send subscription to Elohim.net
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
		'state' => $province,
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
	send_person_to_ElohimNet( $firstname, $lastname, $email, $language, $country, '', $regions, '' );

} // confirmation_after_submission_5

// -----------------------------------------------------------------------------------------
// Form : IPT (7)
//    > Remplir les champs Language et Country
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
				break;
			}
	}

	return $form;
} // footer_contact_us_populate_7

// -----------------------------------------------------------------------
// Form : IPT (7)
//    > Remplir le champ Country
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_7_9_1', 'confirmation_populate_country_7', 10, 7 );
function confirmation_populate_country_7( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {

	$person_service=GetService( 'person' );
	$person_token=GetToken( 'get_person_dev' );

	$options_get = array(
		'http'=>array(
			'method'=>"GET",
			'header'=>"Accept: application/json\r\n",
			"ignore_errors" => true, // rather read result status that failing
		)
	);

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

	return $choices;
} // confirmation_populate_country_7

// -----------------------------------------------------------------------
// Form : IPT (7)
//    > Remplir le champ Province
// -----------------------------------------------------------------------
add_filter( 'gform_chained_selects_input_choices_7_9_2', 'confirmation_populate_province_7', 10, 7 );
function confirmation_populate_province_7( $input_choices, $form_id, $field, $input_id, $chain_value, $value, $index ) {
	global $wpdb;

	$selected_iso_country = $chain_value[ "{$field->id}.1" ];
	
	$choices = array ();
	$query   = "select province from wp_country_province where code_country = '" . $selected_iso_country . "' and active = 1 order by province";
	$result  = $wpdb->get_results ( $query );

	foreach ( $result as $data )
	{
		$choices[] = array(
			'text' => $data->province,
			'value' => $data->province
		);
	}

	return $choices;
} // confirmation_populate_province_7

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

	// 1. email existe déjà dans la blacklist de spam
	// 2. le prénom et le nom sont numériques
	// 3. le prénom est inclus dans le nom et le nom se termine par deux caractères majuscules
	// 4. le domaine est valide
	// 5. le email ne contient pas de xyz
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
}

// // ------------------------------------------------
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
// Form : IPT (7)
//    > Préparer la notification au responsable du pays
//    > Envoyer une notification au subscriber seulement si
//      demandé par la personne. Cette condition est
//      validée directement dans la notification
// -----------------------------------------------------
add_filter( 'gform_notification_7', 'footer_contact_us_notification_7', 10, 3 );
function footer_contact_us_notification_7( $notification, $form, $entry ) {

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
	$language = '';
	$message = rgar ( $entry, '6' );
	$iso_country = rgar( $entry, '9.1' );
	$country_name = '';
	$province = rgar( $entry, '9.2' );

	$email_country = 'contact@rael.org';
	$notification['bcc'] = 'loukesir@outlook.com'; // For followup

	// Rechercher le email country@rael.org du pays
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
	$email_country  = 'dev@rael.org'; // Au cas où pas trouvé! Ne devrais pas de produire

	// Rechercher le courriel du pays concerné dans la liste reçu de Elohim.net
	foreach ( $json_data as $data ) {
		if ( $data->iso == $iso_country ) {
			$email_country = $data->email;  // le courriel est trouvé
			$country_name = $data->nativeName;
			break;
		}
	}

	// Exceptions pour le Canada, Mexique et USA
	if (	( $iso_country === 'ca' )
		||	( $iso_country === 'mx' )
		||	( $iso_country === 'us' ) ) {
		switch ( $iso_country ) {
			case 'ca':
				$email_country = 'info@raelcanada.org'; 
				break;
			case 'mx':
				$email_country = 'info@raelmexico.org'; 
				break;
			case 'us':
				$email_country = 'info@raelusa.org'; 
				break;
		}
	}

	// Notification d'alerte au responsable du pays
	if ( $notification['toType'] === 'email' ) {
		$notification['to'] = $email_country; 
		$notification['subject'] = 'Contact notification from www.rael.org'; 
		$notification['name'] = 'International Raelian Movement'; 


		// Rechercher la description de la langue
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

		$html = str_replace('field-fisrtname-1', $firstname, $html );
		$html = str_replace('field-lastname-2', $lastname, $html );
		$html = str_replace('field-email-3', $email, $html );
		$html = str_replace('field-language-4', $language, $html );
		$html = str_replace('field-state-9.1', $country_name, $html );
		$html = str_replace('field-state-9.2', $province, $html );
		$html = str_replace('field-message-6', $message, $html );

		$notification['message'] = $html;
	}

	// Notification envoyée à la personne. 
	if ( $notification['toType'] === 'field' ) {
		// Récupérer les données du formulaire

		$ip_address = empty( $entry['ip'] ) ? GFFormsModel::get_ip() : $entry['ip'];

		$selector = InsertContact( $firstname, $lastname, $email, $language_iso, $iso_country, '', $message, 7, $news_event, $ip_address );
		send_person_to_ElohimNet( $firstname, $lastname, $email, $language_iso, $iso_country, $province, '', $message );
		
		// Note : Même si une URL contient le slug anglais pour une autre langue, WPML va résoudre le slug pour nous.
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
} // footer_contact_us_notification_7

// ----------------------------------------------------------------------
// Form : Newsletter (6)
// > Envoyer le email de confirmation au subscriber
// ----------------------------------------------------------------------
add_filter( 'gform_notification_6', 'newsletter_notification_6', 10, 3 );
function newsletter_notification_6( $notification, $form, $entry ) {

	// Récupérer les données du formulaire et construire l'URL du lien
	$email = rgar( $entry, '4' );
	$ip_address = empty( $entry['ip'] ) ? GFFormsModel::get_ip() : $entry['ip'];

	$language_wpml = apply_filters( 'wpml_current_language', NULL );
	$language_iso = makeCodeLanguageIso( $language_wpml );

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


add_action( 'hook_push_rejects_to_elohimnet', 'push_rejects_to_elohimnet', 13 );
function push_rejects_to_elohimnet () {

	send_person_to_ElohimNet( 'John', 'EDMONDS', 'SEAGHOST15@YAHOO.COM', 'en', 'us', 'Florida', '', 'I am very interested to learn more.' );
	send_person_to_ElohimNet( 'azul', 'Amirouch', 'azemour58@gmail.com', 'en', 'ma', 'Salé', '', 'hello i want to go away to planet like our earth but this planet has a volume as sun.' );
	
}

