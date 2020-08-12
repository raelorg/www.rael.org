<?php
/**
 * CSS/JS Assets
 */
class Rael_CSS_JS
{
  /**
   * Initialize
   */
  function __construct() {
    add_action( 'admin_print_styles', [$this, 'admin_css'] );
    add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'] );
  }

  /**
 * Admin CSS
 */
  public function admin_css() {
    $version = filemtime( get_stylesheet_directory() . '/dist/admin.css' );
    wp_enqueue_style( 'rael-admin', get_stylesheet_directory_uri() .'/dist/admin.css', false, $version, 'screen' );
  }

  /**
   * Frontend JS & CSS
   */
  public function enqueue_scripts() {
    $version = filemtime( get_stylesheet_directory() . '/dist/frontend.css' );
    wp_enqueue_style( 'rael-frontend', get_stylesheet_directory_uri() . '/dist/frontend.css', ['hello-elementor'], $version );

    $version = filemtime( get_stylesheet_directory() . '/dist/frontend.min.js' );
    wp_register_script( 'slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js', ['jquery'], '1.9.0' );
    wp_enqueue_script( 'rael-frontend', get_stylesheet_directory_uri() . '/dist/frontend.min.js', ['jquery', 'slick'], $version, true );
  }
}
new Rael_CSS_JS();
