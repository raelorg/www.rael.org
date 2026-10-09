<?php
// 0) Charger tôt : idéalement placer ce fichier en mu-plugin: wp-content/mu-plugins/pys-cron-cleanup.php

// 1) Ajouter l’intervalle 1 heure
add_filter( 'cron_schedules', function( $schedules ) {
    $schedules['one_hour'] = array(
        'interval' => HOUR_IN_SECONDS,
        'display'  => __( 'Every Hour' ),
    );
    return $schedules;
});

// 2) Planifier sur init (fiable en cron)
add_action( 'init', function() {
    if ( ! wp_next_scheduled( 'pys_cleanup_queue_cron' ) ) {
        wp_schedule_event( time(), 'one_hour', 'pys_cleanup_queue_cron' );
        error_log('CRON: scheduled pys_cleanup_queue_cron');
    }
});

// 3) Exécution + diagnostics
add_action( 'pys_cleanup_queue_cron', function() {
    error_log('CRON: pys_cleanup_queue_cron_exec fired | doing_cron=' . ( defined('DOING_CRON') && DOING_CRON ? 'yes' : 'no' ) );

    // Essai PixelYourSite
    if ( function_exists( 'PYS' ) ) {
        error_log('CRON: PYS() is available');
        $pys = PYS();
        if ( method_exists( $pys, 'cleanupOldEvents' ) ) {
            $deleted = $pys->cleanupOldEvents();
            error_log('CRON: PixelYourSite deleted ' . intval($deleted) . ' rows');
        }
        return;
    }

    // Fallback: forcer le chargement du plugin si non dispo
    $plugin_file = WP_PLUGIN_DIR . '/pixelyoursite-pro/pixelyoursite-pro.php';
    if ( file_exists( $plugin_file ) && ! function_exists( 'PYS' ) ) {
        include_once $plugin_file;
        error_log('CRON: forced include of PixelYourSite');
    }
    if ( function_exists( 'PYS' ) ) {
        $pys = PYS();
        if ( method_exists( $pys, 'cleanupOldEvents' ) ) {
            $deleted = $pys->cleanupOldEvents();
            error_log('CRON: PixelYourSite (forced) deleted ' . intval($deleted) . ' rows');
            return;
        }
    }

    // Dernier recours: purge SQL (robustesse)
    global $wpdb;
    $table  = $wpdb->prefix . 'pys_events_queue';
    $cutoff = gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS );
    $deleted = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$table} WHERE status IN ('processed','failed') AND created_at < %s",
            $cutoff
        )
    );
    error_log('CRON: Fallback SQL deleted ' . intval($deleted) . ' rows (cutoff=' . $cutoff . ')');
});