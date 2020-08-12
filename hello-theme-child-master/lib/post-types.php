<?php
/** start - by Kevin
 
/**
 * Custom Post Type Definitions
 *
 * Imported from the "Custom Post Type UI" plugin, these should
 * always be handled with code and never pulled from a database.
 */
class Rael_Post_Types
{
  /**
   * Initialize
   */
  function __construct() {
    add_action( 'init',  [$this, 'custom_post_types'] );
  }


  /**
   * Defnitions
   */
  public function custom_post_types() {

    /**
     * Post Type: Events.
     */
    $labels = [
      "name" => __( "Events", "hello-elementor-child" ),
      "singular_name" => __( "event", "hello-elementor-child" ),
    ];

    $args = [
      "label" => __( "Events", "hello-elementor-child" ),
      "labels" => $labels,
      "description" => "",
      "public" => true,
      "publicly_queryable" => true,
      "show_ui" => true,
      "show_in_rest" => false,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "has_archive" => false,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "delete_with_user" => false,
      "exclude_from_search" => false,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false,
      "rewrite" => [ "slug" => "events", "with_front" => true ],
      "query_var" => true,
      "menu_position" => 5,
      "menu_icon" => "dashicons-calendar",
      "supports" => [ "title", "editor", "thumbnail", "custom-fields", "revisions", "author" ],
      "taxonomies" => [ "category" ],
    ];

    register_post_type( "events", $args );

    /**
     * Post Type: Shop.
     */
    $labels = [
      "name" => __( "Shop", "hello-elementor-child" ),
      "singular_name" => __( "shop_items", "hello-elementor-child" ),
    ];

    $args = [
      "label" => __( "Shop", "hello-elementor-child" ),
      "labels" => $labels,
      "description" => "",
      "public" => true,
      "publicly_queryable" => true,
      "show_ui" => true,
      "show_in_rest" => true,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "has_archive" => false,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "delete_with_user" => false,
      "exclude_from_search" => false,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false,
      "rewrite" => [ "slug" => "shop_items", "with_front" => true ],
      "query_var" => true,
      "menu_position" => 7,
      "menu_icon" => "dashicons-products",
      "supports" => [ "title", "editor", "thumbnail", "revisions", "author" ],
      "taxonomies" => [ "category" ],
    ];

    register_post_type( "shop_items", $args );

    /**
     * Post Type: Book Download link.
     */
    $labels = [
      "name" => __( "Book Download Link", "hello-elementor-child" ),
      "singular_name" => __( "eb", "hello-elementor-child" ),
    ];

    $args = [
      "label" => __( "Book Download Link", "hello-elementor-child" ),
      "labels" => $labels,
      "description" => "",
      "public" => true,
      "publicly_queryable" => true,
      "show_ui" => true,
      "show_in_rest" => true,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "has_archive" => false,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "delete_with_user" => false,
      "exclude_from_search" => false,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false,
      "rewrite" => [ "slug" => "eb", "with_front" => true ],
      "query_var" => true,
      "menu_position" => 9,
      "menu_icon" => "dashicons-download",
      "supports" => [ "title", "editor" ],
    ];

    register_post_type( "eb", $args );

    /**
     * Post Type: eBooks.
     */
    $labels = [
      "name" => __( "eBooks", "hello-elementor-child" ),
      "singular_name" => __( "ebook", "hello-elementor-child" ),
    ];

    $args = [
      "label" => __( "eBooks", "hello-elementor-child" ),
      "labels" => $labels,
      "description" => "",
      "public" => true,
      "publicly_queryable" => true,
      "show_ui" => true,
      "show_in_rest" => true,
      "rest_base" => "",
      "rest_controller_class" => "WP_REST_Posts_Controller",
      "has_archive" => false,
      "show_in_menu" => true,
      "show_in_nav_menus" => true,
      "delete_with_user" => false,
      "exclude_from_search" => false,
      "capability_type" => "post",
      "map_meta_cap" => true,
      "hierarchical" => false,
      "rewrite" => [ "slug" => "ebook", "with_front" => true ],
      "query_var" => true,
      "menu_position" => 8,
      "menu_icon" => "dashicons-book-alt",
      "supports" => [ "title", "editor", "thumbnail", "custom-fields", "revisions", "author" ],
      "taxonomies" => [ "category" ],
    ];

    register_post_type( "ebook", $args );
  }

}

new Rael_Post_Types();

/** end - by Kevin