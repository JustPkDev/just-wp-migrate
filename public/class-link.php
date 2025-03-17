<?php

class Links
{

    public static function jwp_migrate_assets()
    {
        wp_enqueue_style(
            'jwp-migrate-bootstrap-css',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css'
        );

        wp_enqueue_style(
            'jwp-migrate-style-css',
            JWM_PLUGIN_URL . 'assets/css/style.css',
            ['jwp-migrate-bootstrap-css']
        );

        wp_enqueue_script(
            'jwp-migrate-bootstrap-script',
            "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js",
            ['jquery'],
            null,
            true
        );

        wp_enqueue_script(
            'jwp-migrate-backup-script',
            JWM_PLUGIN_URL . "assets/js/backup.js",
            ['jquery', 'jwp-migrate-bootstrap-script'],
            null,
            true
        );

        wp_enqueue_script(
            'jwp-migrate-sweetalert-script',
            "https://cdn.jsdelivr.net/npm/sweetalert2@11",
            ['jquery', 'jwp-migrate-bootstrap-script'],
            null,
            true
        );

        wp_enqueue_script(
            'jwp-migrate-import-script',
            JWM_PLUGIN_URL . "assets/js/import.js",
            ['jquery', 'jwp-migrate-sweetalert-script'],
            null,
            true
        );

        wp_localize_script('jwp-migrate-backup-script', 'jwm_backup_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('jwm_backup_nonce')
        ]);

        wp_localize_script('jwp-migrate-import-script', 'jwm_import_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('jwm_import_nonce')
        ]);
    }

    public static function register()
    {
        add_action('admin_enqueue_scripts', [__CLASS__, 'jwp_migrate_assets']);
    }
}
