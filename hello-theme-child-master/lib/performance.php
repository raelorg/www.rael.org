<?php
/** start - by Kevin
/**
 * Performance Improvements
 *
 * Adjustments to speed up the site and prepare it
 * for a higher volume of incoming traffic.
 */
class Rael_Performance {
  /**
   * Initialize
   */
  function __construct() {
    add_filter( 'wp_calculate_image_srcset', '__return_false' );
    add_filter( 'hello_elementor_add_woocommerce_support', '__return_false' );
    add_filter( 'wp_get_attachment_image_attributes', [$this, 'attachment_image_attributes'], 99, 3 );
    add_action( 'wp_enqueue_scripts', [$this, 'remove_wp_block_library_css'], 100 );
    add_filter( 'wp_calculate_image_srcset', '__return_false' );
    add_filter( 'the_content', [$this, 'lazyload_images_in_content'] );
  }


  /**
   * Remove Gutenburg Editor CSS
   */
  public function remove_wp_block_library_css() {
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wc-block-style' );
  }


  /**
   * Native Lazy Loading for <img>'s in `the_content`
   */
  public function lazyload_images_in_content($content) {
    if ( !is_admin() && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
      preg_match_all( '@<img(?:[^>]*) src="([^"]+)"(?:[^>]*)\s?\/?>@', $content, $images, PREG_SET_ORDER );

      if ( empty( $images ) ) {
        return $content;
      }

      foreach ( $images as $image ) {
        $src = $image[1];
        $img = $image[0];
        $original_src = $image[1];
        $original_img = $image[0];

        if ( !strstr( $src, site_url() ) ) {
          continue;
        }

        $src = str_replace( 'rael.test', 'www.rael.org', $src );
        $src = str_replace( 'https://', 'https://images.weserv.nl/?url=', $src );

        $img = str_replace( $original_src, $src, $original_img );
        $img = str_replace( '<img ', '<img lazyload="lazyload" ', $img );

        $content = str_replace( $original_img, $img, $content );
      }
    }

    return $content;
  }


  /**
   * Image Resize
   */
  public function resize_image($src, $query = null) {
    if ( strstr( $src, '?' ) ) {
      $pieces = explode( '?', $src );
      $src = $pieces[0];
    }

    $query = wp_parse_args( $query, [
      'trim' => 10,
      'q' => 85,
    ] );

    $query_str = $query ? http_build_query( $query ) : '';

    // photon api resizing + cdn
    //$cdn = 'https://i0.wp.com/';
    $cdn = 'https://images.weserv.nl/?url=';
    $src = str_replace( 'https://', $cdn, $src );
    $src = str_replace( 'rael.test', 'www.rael.org', $src );

    return "$src?$query_str";
  }


  /**
   * Adjustments to Attachment Images
   */
  public function attachment_image_attributes($attr, $attachment, $size) {

    // Native lazy loading
    $attr['lazyload'] = 'lazyload';

    // Special case for resizing site logo
    if ( strstr( $attr['src'], 'cropped-raelian-movement-logo_White.png' ) ) {
      $attr['src'] = $this->resize_image( $attr['src'], [
        'w' => 458,
        'h' => 122,
        'q' => 100,
      ] );

      return $attr;
    }

    // Set a max width to avoid images that are impractically large for web usage
    $attr['src'] = $this->resize_image( $attr['src'] );

    return $attr;
  }
}

new Rael_Performance();

/** end - by Kevin