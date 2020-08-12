<?php
/** - by Kevin
 * Advanced CDN Support
 *
 * Kinsta isn't supporting replacements for all images and static file types
 * for this theme. Let's patch what isn't covered by their mu-plugin here to
 * make sure that all static resources are hosted using their CDN.
 */
class Rael_CDN
{
  /**
   * Initialize
   */
  function __construct() {
    add_action( 'get_header', [$this, 'get_header'] );
    add_action( 'wp_print_footer_scripts', [$this, 'wp_print_footer_scripts'], 999 );
    add_filter( 'elementor/frontend/the_content', [$this, 'elementor_frontend_content'], 11, 1 );
  }

  /**
   * Elementor: CDN Hosted .mp4 Video Backgrounds
   */
  public function elementor_frontend_content( $content ) {

    // Regex replacement for streaming Elementor background videos from Kinsta CDN
    // Kinsta's CDN string replacement isn't supporting Elementor's escaped JSON
    // [data] attribute settings.
    $host = isset( $_SERVER['SERVER_NAME'] ) ? $_SERVER['SERVER_NAME'] : null;
    if ( ! $host ) return $content;
    $host = str_replace( '.', '\.', $host );
    $find = "@(https:\\\\/\\\\/$host)(\\\\/wp-content\\\\/uploads\\\\/[0-9]{4,4}\\\\/[0-9]{2,2}\\\\/[^\.]+\.mp4)@mi";
    $replace = 'https:\\/\\/mk0raelorgiua5hd7uvs.kinstacdn.com$2';
    $content = preg_replace( $find, $replace, $content );

    return $content;
  }

  /**
   * Begin buffering output
   */
  public function get_header() {
    ob_start();
  }

  /**
   * Perform replacements and output HTML
   */
  public function wp_print_footer_scripts() {
    $html = ob_get_clean();

    // CDN
    $find = '@(https://raelorgi\.kinsta\.cloud)(/wp-content/uploads/[0-9]{4,4}/[0-9]{2,2}/[^\.]+\.(?:jpg|png|webp|gif|mp4|webm|pdf|mp3|mov|epub|mobi|jpeg|css|js|svg|json|xml))@mi';
    $replace = 'https://mk0raelorgiua5hd7uvs.kinstacdn.com$2';
    $html = preg_replace( $find, $replace, $html );

    // Correct W3C reported HTML Errors
    $html = str_replace( ' target="_blank" all life on earth created by ET="">', ' target="_blank">', $html );
    $html = str_replace( '<script type="text/javascript"', '<script', $html );
    $html = str_replace( "<script type='text/javascript'", '<script', $html );
    $html = str_replace( '<style type="text/css">', '<style>', $html );
    $html = str_replace( "<style type='text/css'>", '<style>', $html );
    $html = str_replace( 'role="navigation" ', '', $html );
    $html = str_replace( ' page-type="shop"', '', $html );
    $html = str_replace( '<img class="uael-video__thumb" src="', '<img class="uael-video__thumb" alt="Video Preview" src="', $html );

    // Output modified source
    echo $html;
  }
}
new Rael_CDN();

/** - end Kevin