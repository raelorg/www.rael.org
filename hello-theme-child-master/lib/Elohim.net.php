<?php

/**
 * @package Elohim.net
 */
/*
Plugin Name: Elohim.net
Description: Import subscribers from Elohim.net database for mailing.
Version: 1.0.0
Author: raelcanada
Author URI: http://www.raelcanada.org
License: GPLv2 or later
Text Domain: Elohim.net
*/

/*
This program is a free software.

Copyright 2019 raelcanada.org
*/

defined( 'ABSPATH' ) or die( 'Hey, you can\t access this file, you silly human!' );

require_once plugin_dir_path( __FILE__ ) . 'database.php';

function plugin_uninstall() {
   if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
      exit;
   }

   // Check if options exist and delete them if present
   if ( false != get_option( 'elohimnet_options' ) ) {
      delete_option( 'elohimnet_options' );
   }
}

class cElohimNet
{
   public  $plugin;
   public  $id_import;
   private $url_ca = 'http://elohim.net/export.php?token=7A2MuJ!^xX3194qpS2atc358&country=ca&lang=_all_';
   private $url_mx = 'http://elohim.net/export.php?token=7A2MuJ!^xX3194qpS2atc358&country=mx&lang=_all_';
   private $url_us = 'http://elohim.net/export.php?token=7A2MuJ!^xX3194qpS2atc358&country=us&lang=_all_';

   private $first_import;
   private $elohimnet_cron;

   // methods
   function __construct() {
      $this->id_import = 0;
      $this->first_import = false;
      $this->elohimnet_cron = 'elohimnet_cron';
      $this->inactive_cron = 'inactive_cron';

      $this->plugin = plugin_basename( __FILE__ );
   }

   // --------------------------------------------------------------------------------
   // Enregistrer les actions qu'on a besoin
   // --------------------------------------------------------------------------------
	public function register_action() {
      register_activation_hook( __FILE__, array( $this, 'plugin_activate' ) );
      register_activation_hook( __FILE__, array( $this, 'cron_activation_elohimnet' ) );
      register_activation_hook( __FILE__, array( $this, 'cron_activation_inactive' ) );

      register_deactivation_hook( __FILE__, array ( $this, 'plugin_deactivate' ) );
      register_deactivation_hook(__FILE__, array ( $this, 'cron_deactivation_elohimnet' ) );
      register_deactivation_hook(__FILE__, array ( $this, 'cron_deactivation_inactive' ) );

      register_uninstall_hook( __FILE__, 'plugin_uninstall' );

      add_action( 'admin_menu', array ( $this, 'add_admin_pages' ) );
      add_action( 'do_import', array( $this, 'import' ) );
      add_action( 'do_resume', array( $this, 'resume' ) );
      add_action( $this->elohimnet_cron, array( $this, 'cron_execution_elohimnet' ) );
      add_action( $this->inactive_cron, array( $this, 'cron_execution_inactive' ) );
      add_action( 'do_save_settings', array( $this, 'save_settings') );
   }

   // ---------------------------------------------------------
   // Schedule elohimnet_cron task
   // ---------------------------------------------------------
   public function cron_activation_elohimnet()
   {
      if ( wp_next_scheduled( $this->elohimnet_cron ) ) {
         wp_clear_scheduled_hook( $this->elohimnet_cron );
      }

      // Forcer le démarrage une heure plus tard
      wp_schedule_event(time() + (1 * 1 * 60 * 60), 'daily', $this->elohimnet_cron );
   }

   // ---------------------------------------------------------
   // Schedule inactive_cron task
   // ---------------------------------------------------------
   public function cron_activation_inactive()
   {
      if ( wp_next_scheduled( $this->inactive_cron ) ) {
         wp_clear_scheduled_hook( $this->inactive_cron );
      }

      // Forcer le démarrage une heure plus tard
      wp_schedule_event(time() + (1 * 1 * 60 * 60), 'hourly', $this->inactive_cron );
   }

   // ---------------------------------------------------------
   // Clear elohimnet_cron task
   // ---------------------------------------------------------
   public function cron_deactivation_elohimnet()
   {
      wp_clear_scheduled_hook( $this->elohimnet_cron );
   }

   // ---------------------------------------------------------
   // Clear inactive_cron task
   // ---------------------------------------------------------
   public function cron_deactivation_inactive()
   {
      wp_clear_scheduled_hook( $this->inactive_cron );
   }

   // ---------------------------------------------------------
   // elohimnet_cron task execution
   // ---------------------------------------------------------
   public function cron_execution_elohimnet()
   {
      $options = get_option( 'elohimnet_options', array() );

      // date('w') -> 0,1,2,3,4,5,6
      // 0 -> Sunday
      // 1 -> Monday
      // 5 -> Friday
      // 6 -> Saturday
      $dayofweek = date('w') + 1;

      if ( $dayofweek != '' ) {
         if ( $dayofweek == $options['elohimnet_cron'] ) {
            $this->import();
         } else {
            $this->resume(); // Nécessaire pour USA car plusieurs reprises sont nécessaires
         }
      }
   }

   // ---------------------------------------------------------
   // elohimnet_cron task execution
   // ---------------------------------------------------------
   public function cron_execution_inactive()
   {
      $site_url = get_site_url();

      if (  ( $site_url === 'https://raelcanada.org' ) ||
            ( $site_url === 'https://raelmexico.org' ) ||
            ( $site_url === 'https://raelusa.org' ) ) {
         $this->unsubscriber_report();
         $this->inactive_report();
      }
   }

   // --------------------------------------------------------------------------------
   // Récréation des procédure stockées
   // > Pour une raison que j'ignore, les procédures stockées disparaissent de temps
   //   en temps.
   // --------------------------------------------------------------------------------
   public function recreate_SP() {
      $oDatabase = new cDatabase();

      $oDatabase->create_stored_procedure();
   }

   // --------------------------------------------------------------------------------
   // Activation du plugin
   // > Création des tables SQL et procédures stockées
   // --------------------------------------------------------------------------------
   public function plugin_activate() {
      flush_rewrite_rules();

      $oDatabase = new cDatabase();

      $oDatabase->create_tables();
      $oDatabase->create_stored_procedure();

      $this->elohimnet_set_default_options();
   }

   // --------------------------------------------------------------------------------
   // Désactivation du plugin
   // --------------------------------------------------------------------------------
   public function plugin_deactivate() {
      flush_rewrite_rules();

      $this->cron_deactivation_elohimnet();
      $this->cron_deactivation_inactive();
   }

   public function add_admin_pages() {
      add_menu_page ( 'Elohim.net',                        // page_title
                     'Elohim.net',                         // menu_title
                     'manage_options',                     // capabilities
                     'elohimnet_plugin',                   // menu_slug
                     array( $this, 'admin_index' ),        // function
                     'dashicons-admin-multisite',          // icon
                     40 );                                 // position

      add_submenu_page ( 'elohimnet_plugin',               // parent slug
                        'Settings',                        // page_title
                        'Settings',                        // menu_title
                        'manage_options',                  // capabilities
                        'elohimnet_plugin_settings',       // menu_slug
                        array( $this, 'admin_settings' )); // function

      add_submenu_page ( 'elohimnet_plugin',               // parent slug
                        'Help',                            // page_title
                        'Help',                            // menu_title
                        'manage_options',                  // capabilities
                        'elohimnet_plugin_help',           // menu_slug
                        array( $this, 'admin_help' ));     // function
   }

   // --------------------------------------------------------------------------------
   // Template pour l'administration du plugin (consultation du résultat des importations)
   // --------------------------------------------------------------------------------
   public function admin_index() {
      require_once plugin_dir_path( __FILE__ ) . 'templates/admin.php';
   }

   // --------------------------------------------------------------------------------
   // Template pour la configuration du plugin
   // --------------------------------------------------------------------------------
   public function admin_settings() {
      require_once plugin_dir_path( __FILE__ ) . 'templates/settings.php';
   }

   // --------------------------------------------------------------------------------
   // Template pour l'aide sur le plugin
   // --------------------------------------------------------------------------------
   public function admin_help() {
      require_once plugin_dir_path( __FILE__ ) . 'templates/help.php';
   }

   // --------------------------------------------------------------------------------
   // Function to check if option exist and create a new option if it does not
   // --------------------------------------------------------------------------------
   function elohimnet_set_default_options() {
      $options = get_option( 'elohimnet_options', array() );

      $new_options['country'] = 'ca';
      $new_options['id_EN_list'] = 0;
      $new_options['id_FR_list'] = 0;
      $new_options['id_ES_list'] = 0;
      $new_options['unsubscribe'] = 'no';
      $new_options['elohimnet_cron'] = 0;
      $new_options['email_report'] = '';
      $new_options['email_unsubscribers'] = 'loukesir@outlook.com';
      $new_options['email_inactives'] = 'loukesir@outlook.com';

      $merged_options = wp_parse_args( $options, $new_options );

      $compare_options = array_diff_key( $new_options, $options );

      if ( empty( $options ) || !empty( $compare_options ) ) {
          update_option( 'elohimnet_options', $merged_options );
      }
   }

   // -------------------------------------------------------
   // Écrire un commentaire dans le log
   // -------------------------------------------------------
   function elohimnet_log( $comment ) {
      global $wpdb;

      $log_data = array();
		$log_data['id_import'] = $this->id_import;
		$log_data['comment'] = $comment;

      $format_data = array();
      $format_data = '%d';
      $format_data = '%s';

      $wpdb->insert( 'elohimnet_log', $log_data, $format_data );
   }

   // -------------------------------------------------------
   // Déterminer et conserver le dernier id_import
   // -------------------------------------------------------
   function elohimnet_set_id_import() {
      global $wpdb;

      $query = 'select MAX(id_import) from elohimnet_import';
      $id = $wpdb->get_var( $query );

      $this->id_import = $id;
   }

   // -------------------------------------------------------
   // Vérifier l'écriture d'un log
   // -------------------------------------------------------
   function elohimnet_get_log( $comment ) {
      global $wpdb;

      $query = "select * from elohimnet_log where id_import=" . $this->id_import . " and comment='" . $comment . "';";
      $log = $wpdb->get_results( $query, ARRAY_A );

      if ( empty( $log ) ) {
         return false;
      }

      return true;
   }

   // --------------------------------------------------------------------------------
   // Vérifier s'il s'agit de la première importation
   // --------------------------------------------------------------------------------
   function isFirstImport() {
      global $wpdb;

      $query = 'select * from elohimnet_import limit 1;';
      $imports = $wpdb->get_results( $query, ARRAY_A );

      if ( empty( $imports ) ) {
         $this->first_import = true;
      }

      return $this->first_import;
   }

   // --------------------------------------------------------------------------------
   // Insérer une nouvelle importation et récupérer le id_import
   // --------------------------------------------------------------------------------
   function insert_import() {
      global $wpdb;

      $import_data = array();
		$import_data['nb_import'] = 0;
		$import_data['nb_valid'] = 0;
		$import_data['nb_new'] = 0;
		$import_data['nb_deleted'] = 0;
      $import_data['nb_updated'] = 0;

      $format_data = array();
      $format_data = '%d';
      $format_data = '%d';
      $format_data = '%d';
      $format_data = '%d';
      $format_data = '%d';

      $wpdb->insert( 'elohimnet_import', $import_data, $format_data );

      $this->id_import = $wpdb->insert_id;
   }

   // --------------------------------------------------------------------------------
   // Mettre à jour les compteurs dans la table elohimnet_import
   // --------------------------------------------------------------------------------
   function updateImport() {
      global $wpdb;

      $wpdb->query("CALL SP_UpdateImport()");

      $options = get_option( 'elohimnet_options', array() );

      $list = '';

      switch ( $options['country']) {
         case 'ca':
            $list = $options['id_FR_list'] . ',' . $options['id_EN_list'];
            break;
         case 'mx':
            $list = $options['id_ES_list'];
            break;
         case 'us':
            $list = $options['id_EN_list'];
            break;
      }

      $inactive = $wpdb->get_var(  "Select count(*)
                                    From
                                       wp_mailpoet_subscribers sub
                                       Join wp_mailpoet_subscriber_segment seg
                                       on seg.subscriber_id = sub.id
                                    Where seg.segment_id in (" . $list . ")
                                      And sub.status = 'inactive'");

      $active = $wpdb->get_var(      "Select count(distinct sub.email)
                                      From
                                         wp_mailpoet_subscribers sub
                                         Join wp_mailpoet_subscriber_segment seg
                                         on seg.subscriber_id = sub.id
                                      Where seg.segment_id in (" . $list . ")
                                        And seg.status = 'subscribed'
                                        And sub.status = 'subscribed'");

      $wpdb->update( 'elohimnet_import', array( 'nb_mailpoet_inactive' => $inactive, 'nb_mailpoet_active' => $active ), array( 'id_import' => $this->id_import ) );

      $this->elohimnet_log( 'updateImport: completed' );
   }

   // --------------------------------------------------------------------------------
   // Insérer les emails dans la table insert_import_email
   // > Seul les deux dernières imporations sont conservées dans la table elohimnet_import_email
   // > Le data associé au email est conservé dans la table elohimnet_email_data qui
   //   n'est pas versionné par id_import
   // --------------------------------------------------------------------------------
   function insert_import_email( $item ) {
      global $wpdb;

      // Il ne sert à rien d'importer ceux qui ne sont pas inscrits dans une liste.
      // Récupérer moins de email résoud le problème de coupure du CRON pour les USA.
      if ( empty ( $item->subscribed ) ) {
         return 'no_list';
      }

      $import_email_data = array();
		$import_email_data['id_import'] = $this->id_import;
      $import_email_data['email'] = utf8_encode($item->email);
		$import_email_data['language'] = utf8_encode($item->language);
		$import_email_data['type'] = utf8_encode($item->type);
		$import_email_data['firstname'] = utf8_encode($item->firstname);
		$import_email_data['lastname'] = utf8_encode($item->lastname);
		$import_email_data['nickname'] = utf8_encode($item->nickname);
		$import_email_data['town'] = utf8_encode($item->town);
		$import_email_data['state'] = utf8_encode($item->state);
		$import_email_data['level'] = utf8_encode($item->level);
		$import_email_data['gender'] = utf8_encode($item->gender);
		$import_email_data['transmission'] = utf8_encode($item->transmission);
		$import_email_data['datestamp'] = utf8_encode($item->datestamp);
      $import_email_data['FollowStatus'] = utf8_encode($item->followupstatus);

      $email_data = array();
		$email_data['email'] = utf8_encode($item->email);
		$email_data['language'] = utf8_encode($item->language);
		$email_data['type'] = utf8_encode($item->type);
		$email_data['firstname'] = utf8_encode($item->firstname);
		$email_data['lastname'] = utf8_encode($item->lastname);
		$email_data['nickname'] = utf8_encode($item->nickname);
		$email_data['town'] = utf8_encode($item->town);
		$email_data['state'] = utf8_encode($item->state);
		$email_data['level'] = utf8_encode($item->level);
		$email_data['gender'] = utf8_encode($item->gender);
		$email_data['transmission'] = utf8_encode($item->transmission);
		$email_data['datestamp'] = utf8_encode($item->datestamp);
      $email_data['FollowStatus'] = utf8_encode($item->followupstatus);

      $row = $wpdb->insert( 'elohimnet_import_email', $import_email_data );

      if ( $row == 1) {
         $wpdb->replace( 'elohimnet_email_data', $email_data );
      } else {
         return 'duplicate';
      }

      return 'insert';
   }

   // --------------------------------------------------------------------------------
   // Insérer dans la table insert_import_email_list toutes les listes associées à
   // chaque email sauf la liste 'International Mailouts'
   // --------------------------------------------------------------------------------
   function insert_import_email_list( $item ) {
      global $wpdb;

      foreach ($item->subscribed->list as $list) {
         $import_email_list_data = array();
         $import_email_list_data['id_import'] = $this->id_import;
         $import_email_list_data['email'] = utf8_encode($item->email);
         $import_email_list_data['list'] = utf8_encode($list);

         if ($import_email_list_data['list'] != 'International Mailouts') {
            $wpdb->insert( 'elohimnet_import_email_list', $import_email_list_data );
         }
      }
   }


   // --------------------------------------------------------------------------------
   // Retenir que les emails valide
   // --------------------------------------------------------------------------------
   function importInTable_KeepValidEmail() {
      global $wpdb;

      $wpdb->query("CALL SP_LoadValidEmail()");

      $this->elohimnet_log( 'importInTable_KeepValidEmail: SP_LoadValidEmail completed' );
   }

   // --------------------------------------------------------------------------------
   // Importer tous les emails de Elohim.net dans notre base de données
   // --------------------------------------------------------------------------------
   function importInTable() {
      $options = get_option( 'elohimnet_options', array() );

      if ( !empty( $options ) ) {
         $url = '';

         switch ( $options['country'] ) {
            case 'ca' :
               $url = $this->url_ca;
               break;
            case 'mx' :
               $url = $this->url_mx;
               break;
            case 'us' :
               $url = $this->url_us;
               break;
         }

         //$ctx = stream_context_create( array( 'http' => array( 'timeout' => 2400 ) ) );

         ini_set('default_socket_timeout', 900); // 900 Seconds = 15 Minutes

         //$contents = file_get_contents( $url, FALSE, $ctx );
         $contents = file_get_contents( $url, FALSE );
         $items    = simplexml_load_string( $contents );
         $count    = 0; // plante à 14999 sur 15546

         foreach ($items as $item) {
            $count = $count + 1;
            if ( $this->insert_import_email( $item ) === 'insert' ) {
                $this->insert_import_email_list( $item );
            }
         }

         $this->elohimnet_log( 'importInTable: foreach completed' );
      }

      $this->elohimnet_log( 'importInTable: completed' );
   }

   // --------------------------------------------------------------------------------
   // À la première importation, tous les emails valides sont identifiés comme nouveaux
   // --------------------------------------------------------------------------------
   function loadAll() {
      global $wpdb;

      $wpdb->query("CALL SP_LoadAllAsNew()");
   }

   // --------------------------------------------------------------------------------
   // Comparer la nouvelle importation avec la précédente afin d'identifier les
   // nouveaux, les suppressions et les changements.
   // --------------------------------------------------------------------------------
   function CompareImport() {
      global $wpdb;

      $wpdb->query("CALL SP_CompareImport()");

      $this->elohimnet_log( 'CompareImport: SP_CompareImport completed ' );
   }

   // ---------------------------------------------------------------------------------------------
   // Détecter les nouveaux inactifs
   // ---------------------------------------------------------------------------------------------
   function process_inactive() {
      global $wpdb;

      $wpdb->query("CALL SP_inactive()");

   }

   // ----------------------------------------------------------------------------------------------
   // Add all new subscribers in the proper list
   // ----------------------------------------------------------------------------------------------
   function process_new() {
      global $wpdb;

      $options = get_option( 'elohimnet_options', array() );

      $query = "SELECT ed.email, ed.firstname, ed.lastname, ed.language FROM elohimnet_import_new n JOIN elohimnet_email_data ed ON ed.email = n.email WHERE n.id_import in (SELECT max(id_import) FROM elohimnet_import)";

      $allNew = $wpdb->get_results( $query, ARRAY_A );

      if ( $allNew ) {
         if (class_exists(\MailPoet\API\API::class)) {
            $mailpoet_api = \MailPoet\API\API::MP('v1');

            $MP_options = array(
               'send_confirmation_email' => false, // default: true
               'schedule_welcome_email' => false   // default: true
            );

            foreach ( $allNew as $new ) {
               $subscriber_data = array(
                  'email' => $new['email'],
                  'first_name' => $new['firstname'],
                  'last_name' => $new['lastname']
               );

               $list = '';

               switch ( $options['country']) {
                  case 'ca':
                     if ( $new['language'] === 'fr') {
                        $list = $options['id_FR_list'];
                     } else {
                        $list = $options['id_EN_list'];
                     }
                     break;
                  case 'mx':
                     $list = $options['id_ES_list'];
                     break;
                  case 'us':
                     $list = $options['id_EN_list'];
                     break;
               }

               $b_OK = true;
               $subscriber = array();

               try {
                  $subscriber = \MailPoet\API\API::MP('v1')->getSubscriber($new['email']); // $identifier can be either a subscriber ID or e-mail
               } catch(Exception $exception) {
                  try {
                     $subscriber = $mailpoet_api->addSubscriber( $subscriber_data );
                  } catch (\Exception $e) {
                     $b_OK = false;

                     $this->elohimnet_log( 'process_new: duplicate ' . $new['email'] );
                  }
               }

               if ( ( $b_OK ) && ( $this->id_import > 1 ) ) {
                  $segment_data = array();
                  $segment_data['subscriber_id'] = $subscriber['id'];
                  $segment_data['segment_id'] = $list;
                  $segment_data['status'] = 'subscribed';

                  $wpdb->update( 'wp_mailpoet_subscribers', array( 'status' => 'subscribed'), array( 'email' => $new['email'] ) );

                  // La méthode subscribeToList prend trop de temps à s'exécuter, il est mieux de faire un insert SQL
                  // try {
                  //    $subscriber = $mailpoet_api->subscribeToList( $new['email'], $list, $MP_options );
                  // } catch (\Exception $e) {
                  //    error_log( $e->getMessage() );
                  // }
                  $wpdb->insert( 'wp_mailpoet_subscriber_segment', $segment_data );
               }
            }
         }
      }

      $this->elohimnet_log( 'process_new: completed' );
   } // process_new

   // ----------------------------------------------------------------------------------
   // Update first name and last name in Mailpoet
   // ----------------------------------------------------------------------------------
   function process_update() {
      global $wpdb;

      $query = "SELECT ed.email, ed.firstname, ed.lastname FROM elohimnet_import_updated u JOIN elohimnet_email_data ed ON ed.email = u.email WHERE u.id_import in (SELECT max(id_import) FROM elohimnet_import)";

      $allUpdated = $wpdb->get_results( $query, ARRAY_A );

      if ( $allUpdated ) {
         foreach ( $allUpdated as $update ) {
            $data = array();
            $data['first_name'] = $update['firstname'];
            $data['last_name'] = $update['lastname'];

            $where =  array();
            $where['email'] = $update['email'];

            $wpdb->update('wp_mailpoet_subscribers', $data, $where);
         }
      }

      $this->elohimnet_log( 'process_update: completed' );
   }

   // ----------------------------------------------------------------------------
   // Unsubscribe if option
   // ----------------------------------------------------------------------------
   function process_unsubscribe() {
      global $wpdb;

      $options = get_option( 'elohimnet_options', array() );

      $query = "SELECT ed.email, ed.firstname, ed.lastname FROM elohimnet_import_deleted d JOIN elohimnet_email_data ed ON ed.email = d.email WHERE d.id_import in (SELECT max(id_import) FROM elohimnet_import)";

      $allDeleted = $wpdb->get_results( $query, ARRAY_A );

      if ( $allDeleted ) {
         if (class_exists(\MailPoet\API\API::class)) {
            $mailpoet_api = \MailPoet\API\API::MP('v1');

            foreach ( $allDeleted as $delete ) {
               $list = '';

               switch ( $options['country']) {
                  case 'ca':
                     if ( $new['language'] === 'fr') {
                        $list = $options['id_FR_list'];
                     } else {
                        $list = $options['id_EN_list'];
                     }
                     break;
                  case 'mx':
                     $list = $options['id_ES_list'];
                     break;
                  case 'us':
                     $list = $options['id_EN_list'];
                     break;
               }

               try {
                  $subscriber = $mailpoet_api->unsubscribeFromList( $delete['email'], $list );
               } catch (\Exception $e) {
                  $this->elohimnet_log( 'process_unsubscribe: Mailpoet unsubscribe error ' . $exception->getMessage() );
               }
            }
         }
      }

      $this->elohimnet_log( 'process_unsubscribe: completed' );
   } // process_unsubscribe

   // --------------------------------------------------------------------------------
   // Mettre à jour Mailpoet
   // --------------------------------------------------------------------------------
   function update_Mailpoet() {
      $options = get_option( 'elohimnet_options', array() );

      $this->process_new();
      $this->process_update();

      if ( $options['unsubscribe'] === 'yes' ) {
         $this->process_unsubscribe();
      }

      $this->elohimnet_log( 'update_Mailpoet: completed' );
   }

   // --------------------------------------------------------------------------------
   // Retracer les désabonnements depuis la dernière importation.
   // --------------------------------------------------------------------------------
   function unsubscribers_to_Elohimnet() {
      global $wpdb;

      $wpdb->query("CALL SP_unsubscribers_to_Elohimnet()");

      $this->elohimnet_log( 'unsubscribers_to_Elohimnet: completed' );
   }

   // --------------------------------------------------------------------------------
   // Envoyer le rapport d'exécution
   // --------------------------------------------------------------------------------
   function send_report() {
      global $wpdb;

      $options = get_option( 'elohimnet_options', array() );

      $site = '';

      switch ( $options['country']) {
         case 'ca':
            $site = 'www.raelcanada.org';
            break;
         case 'mx':
            $site = 'www.raelmexico.org';
            break;
         case 'us':
            $site = 'www.raelusa.org';
            break;
      }

      $query = 'SELECT id_import, date_extraction, nb_import, nb_valid, nb_new, nb_deleted, nb_updated, nb_bad, nb_unsub_returned, nb_unsub_returned_refused, nb_mailpoet_inactive, nb_mailpoet_active FROM elohimnet_import ORDER BY id_import DESC LIMIT 1';
      $imports = $wpdb->get_results( $query, ARRAY_A );

      foreach ( $imports as $import ) {
         $to = $options['email_report'];
         $subject = $options['country'] . ' - Weekly import report';
         $body =  

         '<p>Hello,</p>
         <p>Please DO NOT reply to this email, it is a <strong>notification</strong> from ' . $site . ' to inform you that the import of new contacts from Elohim.net is complete.</p>
                  <table width="100%" border="0" cellpadding="5" cellspacing="0" bgcolor="#FFFFFF">
                     <tbody>
                        <tr bgcolor="#EAF2FA">
                           <td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>ID</strong></font> </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                           <td width="20">&nbsp;</td>
                           <td><font style="font-family:sans-serif; font-size:12px">tag_ID</font> </td>
                        </tr>
                        <tr bgcolor="#EAF2FA">
                           <td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Date</strong></font> </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                           <td width="20">&nbsp;</td>
                           <td><font style="font-family:sans-serif; font-size:12px">tag_date</font> </td>
                        </tr>
                        <tr bgcolor="#EAF2FA">
                           <td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Imported</strong></font> </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                           <td width="20">&nbsp;</td>
                           <td><font style="font-family:sans-serif; font-size:12px">tag_imported</a></font> </td>
                        </tr>
                        <tr bgcolor="#EAF2FA">
                           <td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Complies with import rules</strong></font> </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                           <td width="20">&nbsp;</td>
                           <td><font style="font-family:sans-serif; font-size:12px">tag_valid</font> </td>
                        </tr>
                        <tr bgcolor="#EAF2FA">
                           <td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>New subscribers</strong></font> </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                           <td width="20">&nbsp;</td>
                           <td><font style="font-family:sans-serif; font-size:12px">tag_new</font> </td>
                        </tr>
                        <tr bgcolor="#EAF2FA">
                           <td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>New unsubscribers</strong></font> </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                           <td width="20">&nbsp;</td>
                           <td><font style="font-family:sans-serif; font-size:12px">tag_unsubscribed</font> </td>
                        </tr>
                        <tr bgcolor="#EAF2FA">
                           <td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Updated</strong></font> </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                           <td width="20">&nbsp;</td>
                           <td><font style="font-family:sans-serif; font-size:12px">tag_updated</font> </td>
                        </tr>
                        <tr bgcolor="#EAF2FA">
                           <td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Unsubscribed, Bounced & Invalides in Elohim.net</strong></font> </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                           <td width="20">&nbsp;</td>
                           <td><font style="font-family:sans-serif; font-size:12px">tag_bad</font> </td>
                        </tr>
                        <tr bgcolor="#EAF2FA">
                           <td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Unsubscribers to send to Elohim.net</strong></font> </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                           <td width="20">&nbsp;</td>
                           <td><font style="font-family:sans-serif; font-size:12px">tag_unsubscribers_returned_to_elohim_net</font> </td>
                        </tr>
                        <tr bgcolor="#EAF2FA">
                           <td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Unsubscribers in Mailpoet refused by Elohim.net</strong></font> </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                           <td width="20">&nbsp;</td>
                           <td><font style="font-family:sans-serif; font-size:12px">tag_unsubscribers_refused_by_elohim_net</font> </td>
                        </tr>
                        <tr bgcolor="#EAF2FA">
                           <td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Inactive: Mailpoet & Elohim.net</strong></font> </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                           <td width="20">&nbsp;</td>
                           <td><font style="font-family:sans-serif; font-size:12px">tag_inactive_in_mailpoet</font> </td>
                        </tr>
                        <tr bgcolor="#EAF2FA">
                           <td colspan="2"><font style="font-family:sans-serif; font-size:12px"><strong>Total subscription from Elohim.net in Mailpoet</strong></font> </td>
                        </tr>
                        <tr bgcolor="#FFFFFF">
                           <td width="20">&nbsp;</td>
                           <td><font style="font-family:sans-serif; font-size:12px">tag_real_subscriptions_in_mailpoet</font> </td>
                        </tr>
                     </tbody>
                  </table>';

      $body = str_replace('tag_ID', $import['id_import'], $body);
      $body = str_replace('tag_date', $import['date_extraction'], $body);
      $body = str_replace('tag_imported', $import['nb_import'], $body);
      $body = str_replace('tag_valid', $import['nb_valid'], $body);
      $body = str_replace('tag_new', $import['nb_new'], $body);
      $body = str_replace('tag_unsubscribed', $import['nb_deleted'], $body);
      $body = str_replace('tag_updated', $import['nb_updated'], $body);
      $body = str_replace('tag_bad', $import['nb_bad'], $body);
      $body = str_replace('tag_unsubscribers_returned_to_elohim_net', $import['nb_unsub_returned'], $body);
      $body = str_replace('tag_unsubscribers_refused_by_elohim_net', $import['nb_unsub_returned_refused'], $body);
      $body = str_replace('tag_inactive_in_mailpoet', $import['nb_mailpoet_inactive'], $body);
      $body = str_replace('tag_real_subscriptions_in_mailpoet', $import['nb_mailpoet_active'], $body);

      $headers = array('Content-Type: text/html; charset=UTF-8');

      wp_mail( $to, $subject, $body, $headers );

      $this->elohimnet_log( 'send_report: completed' );
      }
   }


   // ----------------------------------------------------
   // Rapport pour l'équipe Elohim.net
   // ----------------------------------------------------
   public function unsubscriber_report() {
      global $wpdb;

      $site = '';

      $options = get_option( 'elohimnet_options', array() );

      switch ( $options['country'] ) {
         case 'ca':
            $site = 'raelcanada.org';
            break;
         case 'mx':
            $site = 'raelmexico.org';
            break;
         case 'us':
            $site = 'raelusa.org';
            break;
      }

      $query = "SELECT d.email, d.firstname, d.lastname, d.nickname, d.Followstatus
      FROM
          elohimnet_unsubscribers_return_to_elohim_net u
          JOIN elohimnet_email_data d
          on d.email = u.email
          WHERE u.id_import = (SELECT max(id_import) FROM elohimnet_import) ORDER BY d.email";

      $rows = $wpdb->get_results( $query, ARRAY_A );

      $table = '';

      if ( $rows ) {
         foreach ( $rows as $row ) {
            $table .= 
            '<tr>
               <td>' . $row['email'] . '</td>
               <td>' . $row['firstname'] . '</td>
               <td>' . $row['lastname'] . '</td>
               <td>' . $row['nickname'] . '</td>
               <td>' . $row['Followstatus'] . '</td>
            </tr>';
         } // foreach

         $body =  
         '<p>Hello, List manager!</p>
         <p>Please DO NOT reply to this email, it is a <strong>notification</strong> from ' . $site . ' to inform you of the latest changes relating to unsubscriptions.</p>
         <style>
            table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            }
         </style>
         <table>
            <tr><th>Email</th><th>Firstname</th><th>Lastname</th><th>Nickname</th><th>Follow Status</th></tr>';

         $table .= '</table>';

         $body .= $table;
         $headers = array('Content-Type: text/html; charset=UTF-8');
         $subject = $site . ' - Unsubscriptions of the last week';

         wp_mail( $options['email_unsubscribers'], $subject, $body, $headers );

         $this->elohimnet_log( 'send_unsubscriber: completed' );
      }
   } // unsubscriber_report

   // ----------------------------------------------------
   // Rapport pour l'équipe IPT
   // ----------------------------------------------------
   public function inactive_report() {
      global $wpdb;

      $this->process_inactive();

      $email = '';
      $site = '';
      $dev = '';

      $options = get_option( 'elohimnet_options', array() );

      switch ( $options['country'] ) {
         case 'ca':
            $site = 'raelcanada.org';
            $dev = 'dev@raelcanada.org';
            break;
         case 'mx':
            $site = 'raelmexico.org';
            $dev = 'dev@raelmexico.org';
            break;
         case 'us':
            $site = 'raelusa.org';
            $dev = 'dev@raelusa.org';
            break;
      }

      $query = 
         "SELECT s.name, i.email, mp.first_name, mp.last_name, mp.status, mp.updated_at
         FROM elohimnet_import_inactive i
         JOIN wp_mailpoet_subscribers mp on mp.email = i.email
         JOIN wp_mailpoet_subscriber_segment ss on ss.subscriber_id = mp.id
         JOIN wp_mailpoet_segments s on s.id = ss.segment_id
         LEFT JOIN elohimnet_email_data elo on elo.email = mp.email
         WHERE i.id_import = (SELECT max(id_import) FROM elohimnet_import) 
         ORDER BY i.email";

      $rows = $wpdb->get_results( $query, ARRAY_A );

      $table = '';

      if ( $rows ) {
         foreach ( $rows as $row ) {
            $table .= 
            '<tr>
               <td>' . $row['name'] . '</td>
               <td>' . $row['email'] . '</td>
               <td>' . $row['first_name'] . '</td>
               <td>' . $row['last_name'] . '</td>
               <td>' . $row['status'] . '</td>
               <td>' . $row['updated_at'] . '</td>
            </tr>';
         } // foreach

         $body =  
         '<p>Hello, IPT manager!</p>
         <p>Please DO NOT reply to this email, it is a <strong>notification</strong> from ' . $site . ' to inform you of the latest changes relating to inactive subscriptions.</p>
         <p>What is an inactive subscription? A subscription becomes inactive when the contact has not opened any campaign for more than six months. At this time, the spam protection changes the status from Unsubscribed to Inactive and the contact no longer receives a campaign.</p>
         <p>If you wish to have the complete list of inactive contacts for ' . $site . ', please send a request to <a href="mailto:' . $dev . '">' . $dev .  '</a></p>
         <style>
            table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            }
         </style>
         <table>
            <tr><th>List</th><th>Email</th><th>Firstname</th><th>Lastname</th><th>Status</th><th>Updated at</th></tr>';

         $table .= '</table>';

         $body .= $table;
         $headers = array('Content-Type: text/html; charset=UTF-8');
         $subject = $site . ' - Inactive subscriptions of the last week';

         wp_mail( $options['email_inactives'], $subject, $body, $headers );

         $this->elohimnet_log( 'send_inactive: completed' );
      }
   } // inactive_report

   // --------------------------------------------------------------------------------
   // Lancer une importation
   // --------------------------------------------------------------------------------
   public function import() {
      $this->recreate_SP();

      $this->insert_import();

      $this->elohimnet_log( 'import: beginning' );

      $this->importInTable();
      $this->importInTable_KeepValidEmail();

      if ( $this->isFirstImport() ) {
         $this->loadAll();
      } else {
         $this->CompareImport();
         $this->unsubscribers_to_Elohimnet();
      }

      $this->update_Mailpoet();
      $this->updateImport();
      $this->send_report();

      $this->elohimnet_log( 'import: completed' );
   } // import

   // --------------------------------------------------------------------------------
   // Supprimer une importation partielle
   // --------------------------------------------------------------------------------
   public function deleteImport() {
      global $wpdb;

      $wpdb->delete( 'elohimnet_import_email_list', array( 'id_import' => $this->id_import ) );
      $wpdb->delete( 'elohimnet_import_email', array( 'id_import' => $this->id_import ) );

      $this->elohimnet_log( 'resume: deleteImport' );
   }

   // --------------------------------------------------------------------------------
   // Vérifier si la comparaison a été faite
   // --------------------------------------------------------------------------------
   public function resumeCompare() {
      global $wpdb;
      $retour = true;

      $query = 'SELECT * FROM elohimnet_import_new WHERE id_import = ' . $this->id_import;

      $news = $wpdb->get_results( $query, ARRAY_A );

      if ( $news ) {
         $retour = false;
      }

      $query = 'SELECT * FROM elohimnet_import_updated WHERE id_import = ' . $this->id_import;

      $Updated = $wpdb->get_results( $query, ARRAY_A );

      if ( $Updated ) {
         $retour = false;
      }

      $query = 'SELECT * FROM elohimnet_import_deleted WHERE id_import = ' . $this->id_import;

      $deleted = $wpdb->get_results( $query, ARRAY_A );

      if ( $deleted ) {
         $retour = false;
      }

      $this->elohimnet_log( 'resume: resumeCompare' );

      return $retour;
   }

   // --------------------------------------------------------------------------------
   // Vérifier si elohimnet_unsubscribers_return_to_elohim_net a été fait
   // --------------------------------------------------------------------------------
   public function resume_elohimnet_unsubscribers_return_to_elohim_net() {
      global $wpdb;
      $retour = true;

      $query = 'SELECT * FROM elohimnet_unsubscribers_return_to_elohim_net WHERE id_import = ' . $this->id_import;

      $results = $wpdb->get_results( $query, ARRAY_A );

      if ( $results ) {
         $retour = false;
      }

      $this->elohimnet_log( 'resume: elohimnet_unsubscribers_return_to_elohim_net' );

      return $retour;
   }

   // --------------------------------------------------------------------------------
   // Reprendre une importation
   // --------------------------------------------------------------------------------
   public function resume() {

      $this->recreate_SP();
      $this->elohimnet_set_id_import();

      if ( $this->elohimnet_get_log( 'import: completed' ) ) {
         $this->elohimnet_log( 'resume: nothing to do' );
         return;
      }

      if ( !$this->elohimnet_get_log( 'importInTable: foreach completed' ) ) {
         $this->deleteImport();
         $this->importInTable();
      }

      if ( !$this->elohimnet_get_log( 'importInTable_KeepValidEmail: SP_LoadValidEmail completed' ) ) {
         $this->importInTable_KeepValidEmail();
      }

      if ( $this->resumeCompare() ) {
         $this->CompareImport();
      }

      if ( $this->resume_elohimnet_unsubscribers_return_to_elohim_net() ) {
         $this->unsubscribers_to_Elohimnet();
      }

      if ( !$this->elohimnet_get_log( 'update_Mailpoet: completed' ) ) {
         $this->update_Mailpoet();
      }

      $this->updateImport();
      $this->send_report();

      $this->elohimnet_log( 'resume: completed' );
      $this->elohimnet_log( 'import: completed' );
   } // resume

   public function save_settings() {
      $options['id_EN_list'] = sanitize_text_field( $_POST['id_EN_list'] );
      $options['id_FR_list'] = sanitize_text_field( $_POST['id_FR_list'] );
      $options['id_ES_list'] = sanitize_text_field( $_POST['id_ES_list'] );
      $options['country'] = sanitize_text_field( $_POST['country'] );
      $options['unsubscribe'] = sanitize_text_field( $_POST['unsubscribe'] );
      $options['elohimnet_cron'] = sanitize_text_field( $_POST['elohimnet_cron'] );
      $options['email_report'] = sanitize_text_field( $_POST['email_report'] );
      $options['email_unsubscribers'] = sanitize_text_field( $_POST['email_unsubscribers'] );
      $options['email_inactives'] = sanitize_text_field( $_POST['email_inactives'] );

      update_option( 'elohimnet_options', $options );

      $weekday = array(1,2,3,4,5,6,7);

      if ( in_array( $options['elohimnet_cron'], $weekday ) ) {
         $this->cron_deactivation_elohimnet();
         $this->cron_activation_elohimnet();
      } else {
         $this->cron_deactivation_elohimnet();
      }
   }
}

if (class_exists ( 'cElohimNet' ) ) {

   $oElohimNet = new cElohimNet();
   $oElohimNet->register_action();

}
