<?php

class Links
{

    public static function jwp_migrate_assets()
    {
        wp_enqueue_style(
            'jwp-migrate-bootstrap-css',
            JWM_PLUGIN_URL . 'assets/bootstrap/css/bootstrap.min.css',
            [],
            "1.0.0"
        );

        wp_enqueue_style(
            'jwp-migrate-style-css',
            JWM_PLUGIN_URL . 'assets/css/style.css',
            ['jwp-migrate-bootstrap-css'],
            '1.0.0'
        );

        wp_enqueue_style(
            'jwp-migrate-sweetalert-css',
            JWM_PLUGIN_URL . 'assets/sweetalert/sweetalert2.min.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'jwp-migrate-bootstrap-script',
            JWM_PLUGIN_URL . 'assets/bootstrap/js/bootstrap.bundle.min.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'jwp-migrate-backup-script',
            JWM_PLUGIN_URL . "assets/js/backup.js",
            ['jquery', 'jwp-migrate-bootstrap-script'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'jwp-migrate-sweetalert-script',
            JWM_PLUGIN_URL . 'assets/sweetalert/sweetalert2.min.js',
            ['jquery', 'jwp-migrate-bootstrap-script'],
            '1.0.0',
            true
        );

        wp_enqueue_script(
            'jwp-migrate-import-script',
            JWM_PLUGIN_URL . "assets/js/import.js",
            ['jquery', 'jwp-migrate-sweetalert-script'],
            '1.0.0',
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
