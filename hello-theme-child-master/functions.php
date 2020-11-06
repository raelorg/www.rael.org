<?php
require_once "lib/post-types.php";
require_once "lib/assets.php";
require_once "lib/cdn.php";
require_once "lib/performance.php";
require_once "lib/analytics.php";
require_once "lib/elohimnet.php";
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

/* Remove submit button for Contact Us page form */
add_filter('gform_submit_button_9', '__return_false');

/* Don't show messsage for Contact Us page form */
function change_message_9($message, $form) {
  return "<div></div>";
}

add_action('wp_head', 'add_facebook_meta_tag');
function add_facebook_meta_tag(){
?>
<meta name="facebook-domain-verification" content="hkpezs9g9ernscf44grjm0e7o5adl0" />
<?php
};
/* --------------------------- end-luc -------------------------------------- */

/* Arun Kumar work start here */

/*
 * Custom shortcode for displaying future events
*/

add_shortcode('custom_event_slider', 'render_custom_event_slider');

function render_custom_event_slider() {
  $args = [
    'orderby' => 'start_date',
    'order' => 'ASC',
    'post_type' => 'events',
    'posts_per_page' => '-1',
    'meta_query' => [
      'event_slider' => [
        'key' => 'event_slider',
        'value' => '1',
      ],
      'start_date' => [
        'key' => 'start_date',
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
    $query['meta_key']='start_date';
    $query['orderby']='meta_value';
    $query['order']='ASC';
    $query['meta_query']=array(
      array(
        'key' => 'start_date',
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
  if ($settings['post_type_filter'] == 'shop_items' && in_array('classic_posts_per_page', $settings)) {
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
  if ($settings['post_type_filter'] == 'shop_items' && in_array('classic_posts_per_page', $settings)) {
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

/*
* Method 1: Setting.
* Initialization of Google Maps in ACF
* From: Alain Gauthier, 2020-08-26
*/
function my_acf_google_map_api($api) {
  $api['key'] = 'AIzaSyCqKjpzQEVndx1dxBx1FyNSAc6-qKEtcJk';

  return $api;
}
add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');