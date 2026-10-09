<?php
require_once "lib/post-types.php";
require_once "lib/assets.php";
require_once "lib/cdn.php";
require_once "lib/performance.php";
require_once "lib/analytics.php";
require_once "lib/elohimnet.php";          // Link with Elohim.net & common functions
require_once "lib/form_28_ContactUs.php";
require_once "lib/form_34_Newsletter.php";
require_once "lib/form_35_DoubleOptIn.php";
require_once "lib/redirects.php";


if ( isset( $_GET['debug'] ) ) {
  error_reporting(E_ALL);
  fix_options_table();
}

function hello_elementor_child_enqueue_scripts() {
  // Modified by Juliana Gonzalez - Modify version of css file
  wp_enqueue_style('hello-elementor-child', get_stylesheet_directory_uri() . '/style.css', ['hello-elementor'], '1.0.2.4');
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_scripts');

/* jasmin */

/*end*/

/* --------------------------- begin-luc ------------------------------------ */
function shortcode_whatsapp ( $atts ){
	$ip_address = GFFormsModel::get_ip();
	$language_iso = apply_filters( 'wpml_current_language', NULL );
	$country_from_ip = '';
	$whatsapp_phone = '';
	$url = '';

	while ( $country_from_ip == '' )
	{
		$ip_data = @json_decode(wp_remote_retrieve_body(wp_remote_get( "http://ip-api.com/json/".$ip_address)));

		if ( $ip_data->status == 'success' ) {
			$country_from_ip = strtolower($ip_data->countryCode);

			// Honk Kong fait partie de la Chine
			if ($ip_data->countryCode == 'hk') {
				$country_from_ip = 'cn';
			}
		}
	}

	switch ($country_from_ip) {
		case 'al': // Albania
			if ($language_iso == 'sq') {
				$whatsapp_phone = '393408879365';
			}
			break;
		case 'au': // Australia
			if ($language_iso == 'en') {
				$whatsapp_phone = '61466414211';
			}
			break;
		case 'ba': // Bosnia
			if ($language_iso == 'en') {
				$whatsapp_phone = '393408879365';
			}
			break;
		case 'bg': // Bulgaria
			if ($language_iso == 'bg') {
				$whatsapp_phone = '393408879365';
			}
			break;
		case 'bz': // Belize
			if ($language_iso == 'es') {
				$whatsapp_phone = '573153363000';
			}
			break;
		case 'ci': // Côte d'Ivoire
			if ($language_iso == 'fr') {
				$whatsapp_phone = '2250707828300';
			}
			break;
		case 'co': // Columbia
			if ($language_iso == 'es') {
				$whatsapp_phone = '573153363000';
			}
			break;
		case 'cr': // Costa Rica
			if ($language_iso == 'es') {
				$whatsapp_phone = '573153363000';
			}
			break;
		case 'ec': // Ecuador
			if ($language_iso == 'es') {
				$whatsapp_phone = '573153363000';
			}
			break;
		case 'fr': // France
			if ($language_iso == 'fr') {
				$whatsapp_phone = '330769162172';
			}
			break;
		case 'gr': // Greece
			if ($language_iso == 'el') {
				$whatsapp_phone = '393408879365';
			}
			break;
		case 'gt': // Guatemala
			if ($language_iso == 'es') {
				$whatsapp_phone = '573153363000';
			}
			break;
		case 'gy': // Guyana
			if ($language_iso == 'es') {
				$whatsapp_phone = '573153363000';
			}
			break;
		case 'hr': // Croatia
			if ($language_iso == 'en') {
				$whatsapp_phone = '393408879365';
			}
			break;
		case 'hu': // Hungary
			if ($language_iso == 'hu') {
				$whatsapp_phone = '393408879365';
			}
			break;
		case 'kv': // Kosovo
			if ($language_iso == 'en') {
				$whatsapp_phone = '393408879365';
			}
			break;
		case 'in': // India
			if (($language_iso == 'hi') || ($language_iso == 'en')) {
				$whatsapp_phone = '16048286401';
			}
			break;
		case 'me': // Montenegro
			if ($language_iso == 'en') {
				$whatsapp_phone = '393408879365';
			}
			break;
		case 'mk': // Macedonia
			if ($language_iso == 'en') {
				$whatsapp_phone = '393408879365';
			}
			break;
		case 'mx': // Mexique
			if ($language_iso == 'es') {
				$whatsapp_phone = '522227132693';
			}
			break;
		case 'ni': // Nicaragua
			if ($language_iso == 'es') {
				$whatsapp_phone = '573153363000';
			}
			break;
		case 'nz': // New Zeland
			if ($language_iso == 'en') {
				$whatsapp_phone = '61466414211';
			}
			break;
		case 'pa': // Panama
			if ($language_iso == 'es') {
				$whatsapp_phone = '573153363000';
			}
			break;
		case 'pl': // Poland
			if ($language_iso == 'pl') {
				$whatsapp_phone = '48792940499';
			}
			break;
		case 'rs': // Serbia
			if ($language_iso == 'en') {
				$whatsapp_phone = '393408879365';
			}
			break;
		case 'si': // Slovenia
			if ($language_iso == 'sl') {
				$whatsapp_phone = '393408879365';
			}
			break;
		case 'sk': // Slovakia
			if ($language_iso == 'sk') {
				$whatsapp_phone = '40763083998';
			}
			break;
		case 'ro': // Roumanie
			if ($language_iso == 'ro') {
				$whatsapp_phone = '40763083998';
			}
			break;
		case 'ua': // Ukraine
			if ($language_iso == 'en') {
				$whatsapp_phone = '34664549176';
			}
			break;
		case 've': // Venezuela
			if ($language_iso == 'es') {
				$whatsapp_phone = '573153363000';
			}
			break;
		case 'za': // South Africa
			if ($language_iso == 'en') {
				$whatsapp_phone = '027693395091';
			}
			break;
	}
	
	if ($whatsapp_phone != '') {
		$url = '<a href="https://wa.me/' . $whatsapp_phone . '" target="_blank" rel="noopener noreferrer"><img src="https://www.rael.org/wp-content/uploads/2024/09/WhatsApp_button.svg" alt="Button Image"></a><br><br>';
	}

	return $url;
}
add_shortcode ('whatsapp_button','shortcode_whatsapp');


// Add Japanese currency Yen
add_filter( 'gform_currencies', 'add_yen_currency' );
function add_yen_currency( $currencies ) {
    $currencies['JPY'] = array(
        'name'               => __( 'japanese Yen', 'gravityforms' ),
        'code'               => 'JPY',
        'symbol_left'        => '¥',
        'symbol_right'       => '',
        'symbol_padding'     => ' ',
        'thousand_separator' => ',',
        'decimal_separator'  => '.',
        'decimals'           => 0
    );
  
    return $currencies;
}

// Redirection exception
function redirect_exception( $enqueue ) {
    global $post;
	
	if (!isset($post->ID)) {
	    return $enqueue;
	}
	
	// hi home page: 342356
	// hi intelligent design: 340795
    if (   ( $post->ID === 342356 )             
		|| ( $post->ID === 340795 ) ) {
        $enqueue = false;
    }
    return $enqueue;
}
add_filter( 'wpml_enqueue_browser_redirect_language', 'redirect_exception' );


//Remove page title
add_filter( 'the_title', 'remove_page_title', 10, 2 );
function remove_page_title( $title, $id ) {
	$hide_title_page_ids = array(347825, 347828);//Page IDs
	foreach($hide_title_page_ids as $page_id) {
		if( $page_id == $id ) return '';
	}
	return $title;
}

/* Remove submit button for Contact Us page form */
add_filter('gform_submit_button_9', '__return_false');

/* Don't show messsage for Contact Us page form */
function change_message_9($message, $form) {
  return "<div></div>";
}

/* meta_tag for FB pixel */
add_action('wp_head', 'add_facebook_meta_tag');
function add_facebook_meta_tag(){
?>
<meta name="facebook-domain-verification" content="hkpezs9g9ernscf44grjm0e7o5adl0" />
<?php
}


// Add Google Tag code which is supposed to be placed after opening body tag.
add_action( 'wp_body_open', 'wpdoc_add_custom_body_open_code' );
 
function wpdoc_add_custom_body_open_code() {
    echo '<!-- Google Tag Manager (noscript) --><noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-58TMKC" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript><!-- End Google Tag Manager (noscript) -->';
}


// When someone click link from social media
// > Block redirection according to browser language
function skip_redirection() {

	global $post;

	$pages_to_skip = [71863, // https://www.rael.org/ebook/intelligent-design/
                    72111, // https://www.rael.org/ar/ebook/intelligent-design/
                    72063, // https://www.rael.org/zh-hans/ebook/intelligent-design-1/
                    72081, // https://www.rael.org/zh-hant/ebook/intelligent-design-1/
                    79768, // https://www.rael.org/cs/ebook/inteligentni-design/
                    87622, // https://www.rael.org/da/ebook/intelligent-design/
                    81041, // https://www.rael.org/nl/ebook/intelligent-design/
                    71929, // https://www.rael.org/fr/ebook/le-message-des-extra-terrestres/
                    72224, // https://www.rael.org/fp/ebook/matalinong-disenyo/
                    72150, // https://www.rael.org/de/ebook/intelligentes-design/
                    330641, // https://www.rael.org/el/ebook/intelligent-design/
                    71926, // https://www.rael.org/he/ebook/intelligent-design/
                    340795, // https://www.rael.org/hi/ebook/intelligent-design/
                    295185, // https://www.rael.org/id/ebook/desain-cerdas/
                    71915, // https://www.rael.org/it/ebook/il-libro-che-dice-la-verita/
                    72097, // https://www.rael.org/ja/ebook/intelligent-design/
                    71887, // https://www.rael.org/ko/ebook/intelligent-design/
                    81450, // https://www.rael.org/lt/ebook/intelligent-design/
                    72214, // https://www.rael.org/mn/ebook/intelligent-design/
                    72130, // https://www.rael.org/fa/ebook/intelligent-design-1/
                    79839, // https://www.rael.org/pl/ebook/przeslanie-od-przybyszow-z-kosmosu/
                    72209, // https://www.rael.org/pt-pt/ebook/a-mensagem-transmitida-pelos-extraterrestres/
                    72185, // https://www.rael.org/ro/ebook/designul-inteligent/
                    72115, // https://www.rael.org/ru/ebook/intelligent-design-1/
                    88716, // https://www.rael.org/sk/ebook/intelligent-design-1/
                    72195, // https://www.rael.org/sl/ebook/intelligent-design-1/
                    71909, // https://www.rael.org/es/ebook/intelligent-design/
                    96486, // https://www.rael.org/sv/ebook/intelligent-design/
                    87864, // https://www.rael.org/th/ebook/intelligent-design-1/
                    281629 // https://www.rael.org/tr/ebook/zeki-dizayn/
                   ];
 
	if ( ! is_admin() && $post && in_array( $post->ID, $pages_to_skip, true ) ) {
        add_filter( 'wpml_enqueue_browser_redirect_language', '__return_false' );
    }
}
 
add_action( 'get_header', 'skip_redirection' );

/* --------------------------- end-luc -------------------------------------- */

/* Arun Kumar work start here */

/*
 * Custom shortcode for displaying future events
*/

add_shortcode('custom_event_slider', 'render_custom_event_slider');

function render_custom_event_slider() {
  $args = [
    'orderby' => 'end_date',
    'order' => 'ASC',
    'post_type' => 'events',
    'posts_per_page' => '-1',
    'meta_query' => [
      'event_slider' => [
        'key' => 'event_slider',
        'value' => '1',
      ],
      'end_date' => [
        'key' => 'end_date',
        'value' => date("Ymd"),
        'compare' => '>=',
      ],
    ],
  ];
  $query = new WP_Query($args);
  $events = $query->posts;
  ob_start();
  if (is_array($events) && count($events) > 0) {
    ?>
    <div class="event-items">
      <?php
      foreach ($events as $index => $event) {
        $start_date = get_post_meta($event->ID,'start_date',true );
        if ( !$start_date ) {
          continue;
        } ?>
        <div class="event-item-slide slick-slide">
          <div class="ei-row">
            <div class="ei-column ei-col-10 event_date">
              <?php
              if (!empty($start_date)) {
                
                $sd_format = DateTime::createFromFormat('Ymd', $start_date);
                ?>
                <p class="month"><?php echo date_i18n('M',$sd_format->getTimestamp()); ?></p>
                <hr>
                <p class="date"><?php echo date_i18n('d',$sd_format->getTimestamp()); ?></p>
                <?php
              } ?>
            </div>
            <div class="ei-column ei-col-40 event-item-img" style="background-image: url('<?php echo get_the_post_thumbnail_url($event->ID, "large"); ?>');"></div>
            <div class="ei-column ei-col-50 event-item-desc">
              <h4 class="event-title"> <?php echo $event->post_title; ?> </h4>
              <p class="event-location"><?php echo get_field('city_name', $event->ID); ?></p>
              <p class="event-date">
                <?php
                 $end_date = get_post_meta($event->ID,'end_date', true);
                 echo get_events_date_range($sd_format,$end_date);
                ?>
              </p>
              <p class="event-description">
                <?php
                $terms = get_the_terms($event->ID,'category');
                $field_desc='description';
                if($terms){
                  foreach($terms as $term){
                    if($term->slug == 'online-event'){
                      $field_desc='description_online_event';
                      break;
                    }
                  }
                }
                $desc = get_field($field_desc, $event->ID);
                $desc = wp_strip_all_tags($desc);
                if (strlen($desc) > 370) {
                  echo substr($desc, 0, 370) . '...';
                } else {
                  echo $desc;
                } ?>
              </p>
              <hr>
              <a href="<?php echo esc_url(get_permalink($event->ID)); ?>" class="slider_view_event button elementor-button elementor-size-sm"><?php _e('View Full Event Details','hello-elementor-child'); ?></a>
              <a href="<?php echo esc_url(get_page_link(17)); ?>" class="slider_view_events button elementor-button elementor-size-sm"><?php _e('View All Events','hello-elementor-child'); ?></a>
            </div>
          </div>
        </div>
        <?php
      } ?>
    </div>
    <style>
      .event-items {
        visibility: visible !important;
      }
    </style>
    <?php
  }
  $html = ob_get_clean();

  return $html;
}

/*
 * Calculates the date range between two dates and retun paring them accordingly
*/

function get_events_date_range($d1, $end_date) {
  if(empty($end_date)){
    return date_i18n('F d, Y',$d1->getTimestamp());
  }
  $d1ts = $d1->getTimestamp();
  $d2 = DateTime::createFromFormat('Ymd', $end_date);
  $d2ts = $d2->getTimestamp();
  if ($d1->format('Y-m-d') === $d2->format('Y-m-d')) {
    # Same day
    return date_i18n('F d, Y',$d1ts);
  } elseif ($d1->format('Y-m') === $d2->format('Y-m')) {
    # Same calendar month
    return date_i18n('F d - ',$d1ts) . date_i18n('d, Y',$d2ts);
  } elseif ($d1->format('Y') === $d2->format('Y')) {
    # Same calendar year
    return date_i18n('F d - ',$d1ts) . date_i18n('F d, Y',$d2ts);
  } else {
    # General case (spans calendar years)
    return date_i18n('F d, Y',$d1ts) . date_i18n(' - F d, Y',$d2ts);
  }
}

/*
 * Elementor filter for changing the post date with custom ACF dates range value
*/

add_filter('uael_post_the_date_format', function ($date, $post_id, $date_format) {
  if (get_post_meta($post_id, 'start_date', true)) {
    $start_date = get_post_meta($post_id,'start_date',true );
    $sd_format = DateTime::createFromFormat('Ymd', $start_date);
    $end_date = get_post_meta($post_id,'end_date', true);
    return get_events_date_range($sd_format,$end_date);
  }
  return $date;
}
, 10, 3);

/*
* Elementor filter for showing future events only for post widget
*/

add_filter( 'uael_posts_query_args', function( $query, $settings ) {
  if($settings['post_type_filter']=='events'){
    $query['meta_key']='end_date';
    $query['orderby']='meta_value';
    $query['order']='ASC';
    $query['meta_query']=array(
      array(
        'key' => 'end_date',
        'value' => date("Ymd"),
        'compare' => '>=',
        'type' => 'DATE'
      )
    );
  }
  return $query;
}, 10, 2 );

/*
 * Showing books download links
*/

add_action('uael_single_post_after_thumbnail', 'uael_books_links_info', 10, 2);

function uael_books_links_info($post_id, $settings) {
  if ($settings['post_type_filter'] == 'shop_items' && array_key_exists('classic_posts_per_page', $settings)) {
    echo "<ul class='source_icon'>";
    $links_exists = true;
    if (get_field('ibook_link', $post_id)) {
      $links_exists = false;
      echo "<li><a href='" . get_field('ibook_link', $post_id) . "' target='_blank'>";
      if (get_field('ibook_image', $post_id)) {
        $ibook_image = get_field('ibook_image', $post_id);
        echo "<img width='300' height='109' src='" . $ibook_image['url'] . "' class='elementor-animation-grow attachment-large size-large' alt='" . $ibook_image['title'] . "'>";
      } else {
        _e('iBooks','hello-elementor-child');
      }
      echo "</a></li>";
    }
    if (get_field('amazon_link', $post_id)) {
      $links_exists = false;
      echo "<li><a href='" . get_field('amazon_link', $post_id) . "' target='_blank'>";
      if (get_field('amazon_image', $post_id)) {
        $amazon_image = get_field('amazon_image', $post_id);
        echo "<img width='300' height='109' src='" . $amazon_image['url'] . "' class='elementor-animation-grow attachment-large size-large' alt='" . $amazon_image['title'] . "'>";
      } else {
        _e('Amazon','hello-elementor-child');
      }
      echo "</a></li>";
    }
    if (get_field('kindle_link', $post_id)) {
      $links_exists = false;
      echo "<li><a href='" . get_field('kindle_link', $post_id) . "' target='_blank'>";
      if (get_field('kindle_image', $post_id)) {
        $kindle_image = get_field('kindle_image', $post_id);
        echo "<img width='300' height='109' src='" . $kindle_image['url'] . "' class='elementor-animation-grow attachment-large size-large' alt='" . $kindle_image['title'] . "'>";
      } else {
        _e('Kindle','hello-elementor-child');
      }
      echo "</a></li>";
    }
    echo "</ul>";
    if ($links_exists) {
      echo "<div class='no_online_links'>";
      if (get_field('coming_soon', $post_id)) {
        echo get_field('coming_soon', $post_id);
      } else {
        _e('Coming Soon','hello-elementor-child');
      }
      echo "</div>";
    }
  }
}

/*
 * Changing the permalink for Shop Items
*/

add_filter('uael_single_post_link', function ($link, $post_id, $settings) {
  if ($settings['post_type_filter'] == 'shop_items' && array_key_exists('classic_posts_per_page', $settings)) {
    if (get_field('market_link', $post_id)) {
      return get_field('market_link', $post_id);
    } else {
      return 'javascript:void(0)';
    }
  }
  return $link;
}
, 10, 3);


/*
* Show custom date range for events
*/
function render_event_date_range(){
  global $post;
  if (get_post_meta($post->ID, 'start_date', true)) {
    $start_date = get_post_meta($post->ID,'start_date',true );
    $sd_format = DateTime::createFromFormat('Ymd', $start_date);
    $end_date = get_post_meta($post->ID,'end_date', true);
    return get_events_date_range($sd_format,$end_date);
  }
}

add_shortcode('event_date_range','render_event_date_range');

/*  Arun Kumar work end here */



/* Gediminas work starts here */

/*
 * Shortcode used in meditation page to make iframes translatable
 * The attributes of this function that need to be translated must be defined in
 * WPML -> Settings -> Custom XML Configuration
*/
function iframe_embed( $atts ) {
	
	$a = shortcode_atts( array (
		'url' => 'https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/playlists/872025926&color=%230ecad4&auto_play=false&hide_related=false&' . 
				 'show_comments=false&show_user=false&show_reposts=false&show_teaser=false', 	// default URL if not provided in params
		'height' => '500',					// default iframe height if not provided in params
	), $atts );

	$iframe_to_embed='<iframe width="100%" height="' . $a['height'] . '" scrolling="no" frameborder="no" allow="autoplay" src="' . $a['url'] . '"></iframe>';
	return $iframe_to_embed;
}

add_shortcode('translatable_iframe', 'iframe_embed' );

/* Gediminas work ends here */

/* Matt Doyle (matt@elated.com) work starts here */


/**
 * Insert AdRoll pixel into the page HEAD.
 */
add_action('wp_head', function() {
?>
<script type="text/javascript">
    adroll_adv_id = "YP2ZA24FSRHVFOXA6H4TJN";
    adroll_pix_id = "CN2GLR4VF5DM7E5CN4Q7PM";
    adroll_version = "2.0";

    (function(w, d, e, o, a) {
        w.__adroll_loaded = true;
        w.adroll = w.adroll || [];
        w.adroll.f = [ 'setProperties', 'identify', 'track' ];
        var roundtripUrl = "https://s.adroll.com/j/" + adroll_adv_id
                + "/roundtrip.js";
        for (a = 0; a < w.adroll.f.length; a++) {
            w.adroll[w.adroll.f[a]] = w.adroll[w.adroll.f[a]] || (function(n) {
                return function() {
                    w.adroll.push([ n, arguments ])
                }
            })(w.adroll.f[a])
        }

        e = d.createElement('script');
        o = d.getElementsByTagName('script')[0];
        e.async = 1;
        e.src = roundtripUrl;
        o.parentNode.insertBefore(e, o);
    })(window, document);
    adroll.track("pageView");
</script>
<?php
});

/**
 * Attach AdRoll event tracking handlers to the download buttons
 * on /ebook/intelligent-design/ .
 */
add_action('wp_footer', function() {
?>
<script type="text/javascript">
	const ebookDownloadButton = document.getElementById('ebook-download');
	if ( ebookDownloadButton ) {
		ebookDownloadButton.addEventListener( "click", function() {
			try { 
			__adroll.track("pageView", {"segment_name":"25629b2f"});
			} catch(err) {};
		});
	};      

	const audiobookDownloadButton = document.getElementById('audiobook-download');
	if ( audiobookDownloadButton ) {
		audiobookDownloadButton.addEventListener( "click", function() {
			try { 
				__adroll.track("pageView", {"segment_name":"25629b2f"});
			} catch(err) {};
		});
	};      
</script>
<?php
});

/**
 * Force the envelope sender address to match the header From address
 * for all emails sent from WordPress, so that DMARC alignment checks
 * pass when using SPF. 
 */
add_action('phpmailer_init', function ( $phpmailer ) {
    $phpmailer->Sender = $phpmailer->From;
});

/* Matt Doyle (matt@elated.com) work ends here */

