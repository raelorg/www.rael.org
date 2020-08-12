<?php
/**
 * CloudFlare Caching Rules
 *
 * Avoid Edge cache when user is logged-in
 */
class CloudFlare_Cache_Purge {
  public $secure;
  public $wpe_us_expire;

  function __construct() {

    // Vars
    $this->secure = ( is_ssl() || force_ssl_admin() );
    $this->wpe_us_expire = time() + YEAR_IN_SECONDS;

    // Hooks
    add_action( 'wpmdb_migration_complete', [$this, 'purgeall'], 10, 2 );
    add_action( 'admin_bar_menu', [$this, 'admin_bar_menu'], 99999 );
    add_action( 'init', [$this, 'purge_cache_request'], 2 );
    add_action( 'init', [$this, 'cache_rules'], 3 );
    //add_filter( 'show_admin_bar', '__return_false' );
  }


  /**
   * Admin Bar CSS
   */
  public function admin_bar_css() {
    ob_start(); ?>
    <style>
      #wpadminbar ul#wp-admin-bar-root-default .cloudflare-cache {
        position: relative;
      }
      #wpadminbar ul#wp-admin-bar-root-default .cloudflare-cache > .ab-item {
        margin-left: 38px;
      }
      #wpadminbar ul#wp-admin-bar-root-default .cloudflare-cache__svg {
        width: 38px;
        height: 32px;
        padding-left: 10px;
        position: absolute;
        left: 0;
        top: 0;
      }
      #wpadminbar ul#wp-admin-bar-root-default .cloudflare-cache__svg svg {
        width: 28px !important;
        height: auto !important;
        display: block;
        margin-top: 50%;
        transform: translateY(-76%);
      }
      #wpadminbar ul#wp-admin-bar-root-default .cloudflare-cache__svg svg #CF-Path-1 {
        fill: #888888;
      }
      #wpadminbar ul#wp-admin-bar-root-default .cloudflare-cache__svg svg #CF-Path-2 {
        fill: #AAAAAA;
      }
      #wpadminbar ul#wp-admin-bar-root-default .cloudflare-cache:hover {
        background-color: #32373c;
      }
      #wpadminbar ul#wp-admin-bar-root-default .cloudflare-cache:hover .cloudflare-cache__svg svg #CF-Path-1 {
        fill: #F38020;
      }
      #wpadminbar ul#wp-admin-bar-root-default .cloudflare-cache:hover .cloudflare-cache__svg svg #CF-Path-2 {
        fill: #FBAE40;
      }
    </style>
    <?php
    $css = ob_get_clean();

    return $css;
  }


  /**
   * Admin Bar Button
   */
  public function admin_bar_menu($wp_admin_bar) {
    $html = '<div class="cloudflare-cache__svg"><svg width="51px" height="23px" viewBox="0 0 51 23" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
    <g id="cloudflare" fill-rule="nonzero">
    <polygon id="Path" fill="#FFFFFF" points="47 14.0022148 40.5403012 10.4606866 39.4260407 10 13 10.1727575 13 23 47 23"></polygon>
    <path d="M34.8161237,21.7627997 C35.1276442,20.6895658 35.0089698,19.705768 34.4897689,18.9753727 C34.015071,18.3046014 33.2140183,17.9170447 32.2497882,17.8723266 L13.9887536,17.6338302 C13.8700791,17.6338302 13.766239,17.5742061 13.7069017,17.4847699 C13.6475645,17.3953338 13.6327302,17.2760855 13.6623988,17.1568373 C13.721736,16.977965 13.8997477,16.8438108 14.0925938,16.8289047 L32.5168058,16.5904083 C34.6974493,16.4860661 37.0709387,14.7122489 37.9016601,12.5359689 L38.954896,9.77835386 C38.9993989,9.65910564 39.0142332,9.53985742 38.9845646,9.4206092 C37.7978199,4.02462735 33.006338,0 27.2802947,0 C21.9992806,0 17.5193192,3.42838626 15.9172138,8.18340894 C14.8788121,7.40829553 13.5585586,6.99092677 12.1344649,7.12508101 C9.59779807,7.37848347 7.5654977,9.4206092 7.31331445,11.9695399 C7.25397721,12.6254051 7.29848014,13.2663642 7.44682323,13.8626053 C3.30805096,13.9818535 0,17.3804277 0,21.5690214 C0,21.9416721 0.0296686184,22.3143227 0.0741715461,22.6869734 C0.103840165,22.8658458 0.252183257,23 0.430194967,23 L34.1337455,23 C34.3265915,23 34.5046032,22.8658458 34.5639405,22.6720674 L34.8161237,21.7627997 Z" id="CF-Path-1" fill="#F38020"></path>
    <path d="M40.9265283,10 C40.7618801,10 40.582264,10 40.4176159,10.0149083 C40.2978718,10.0149083 40.1930957,10.1043578 40.1481917,10.2236239 L39.4297271,12.7133028 C39.1153989,13.7866972 39.235143,14.7706422 39.7590234,15.5011468 C40.2379997,16.1720183 41.0462723,16.559633 42.0191931,16.6043578 L45.9408121,16.8428899 C46.0605562,16.8428899 46.1653323,16.9025229 46.2252043,16.9919725 C46.2850764,17.081422 46.3000444,17.2155963 46.2701084,17.3199541 C46.2102363,17.4988532 46.0306202,17.6330275 45.836036,17.6479358 L41.7497689,17.8864679 C39.5345032,17.9908257 37.1545893,19.7649083 36.3163807,21.9415138 L36.0170205,22.7018349 C35.9571484,22.8509174 36.0619245,23 36.2265726,23 L50.2665674,23 C50.4312156,23 50.5808957,22.8956422 50.6257997,22.7316514 C50.8652879,21.8669725 51,20.9575688 51,20.0183486 C51,14.5022936 46.4796605,10 40.9265283,10" id="CF-Path-2" fill="#FBAE40"></path>
    </g>
    </svg></div>';

    $html .= $this->admin_bar_css();

    // Parent
    $wp_admin_bar->add_node( [
      'id' => 'cloudflare-cache',
      'title' => 'CloudFlare',
      'meta' => [
        'class' => 'cloudflare-cache',
        'html' => $html,
      ],
    ] );

    // Submenu items
    $wp_admin_bar->add_node( [
      'parent' => 'cloudflare-cache',
      'id' => 'cloudflare-cache__purge',
      'title' => 'Purge Edge Cache',
      'href' => site_url( '/wp-admin/?purge-cloudflare-cache' ),
      'meta' => [
        'class' => 'cloudflare-cache__purge',
      ],
    ] );
  }


  /**
   * Cache Rules
   *
   * Edge cache HTTP headers
   */
  public function cache_rules() {
    if ( is_user_logged_in() ) {
      header( "Cache-Control: max-age=0, private, no-cache" );
      header( "Pragma: no-cache" );
    } else {
      $browser = HOUR_IN_SECONDS;
      $edge = DAY_IN_SECONDS;
      header( "Cache-Control: public, must-revalidate, max-age=$browser, s-maxage=$edge" );
    }
  }


  /**
   * Purge CloudFlare Cache
   *
   * Requires
   */
  public function purgeall() {

    // API request
    $api_token = 'T6H5F81yYyN0JlaEFStr7VjsBI5G2JbLudZWyY5d';
    $zone_id = '379b6693880ca772a8e52a4458b19a2c';
    $req = wp_remote_post( "https://api.cloudflare.com/client/v4/zones/$zone_id/purge_cache", [
      'data_format' => 'body',
      'headers' => [
        'Authorization' => "Bearer $api_token",
        'Content-Type' => 'application/json',
      ],
      'body' => json_encode( [
        'purge_everything' => true,
      ] ),
    ] );

    $body = wp_remote_retrieve_body( $req );
    $result = json_decode( $body, true );

    if ( $result['success'] == 1 ) {
      echo 'All CloudFlare cache has successfully been purged.';
    } else {
      echo 'Failed to purge CloudFlare cache due to the following errors:<br>';
      echo '<pre>';
      print_r( $result['errors'] );
      echo '</pre>';
    }

    exit;
  }


  /**
   * Purge CloudFlare Cache Request
   */
  public function purge_cache_request() {
    $query_param = isset( $_GET['purge-cloudflare-cache'] );
    if ( !$query_param ) {
      return;
    }

    if ( !is_user_logged_in() ) {
      return;
    }

    header( "Cache-Control: max-age=0, private, no-cache" );
    header( "Pragma: no-cache" );

    $this->purgeall();

    echo 'CloudFlare Edge Cache has successfully been purged for all pages. This could take up to 30 secs to resolve.';
    exit;
  }
}

new CloudFlare_Cache_Purge();