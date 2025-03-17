<?php

class Admin
{
    public static function init()
    {
        // Run inside WordPress Admin
        add_action('admin_menu', [__CLASS__, 'register']);
        add_action('admin_notices', [__CLASS__, 'notice']);
    }

    public static function register()
    {
        // Main Menu
        add_menu_page(
            'Just WP Migrate',
            'Just WP Migrate',
            'manage_options',
            'jwm-plugin',
            [__CLASS__, 'main_page'],
            JWM_PLUGIN_URL . 'assets/images/icon.png',
            75
        );

        // Sub Menu: Backups
        add_submenu_page(
            'jwm-plugin',
            'Backups',
            'Backups',
            'manage_options',
            'jwm-plugin',
            [__CLASS__, 'main_page']
        );

        // Sub Menu: Import
        add_submenu_page(
            'jwm-plugin',
            'Import',
            'Import',
            'manage_options',
            'jwm-import',
            [__CLASS__, 'import_page']
        );
    }

    public static function main_page()
    {
        include JWM_PLUGIN_DIR . 'template/backup.php';
    }

    public static function import_page()
    {
        include JWM_PLUGIN_DIR . 'template/import.php';
    }

    public static function notice()
    {
        if (Option::get('jwm_activated')) {
            echo '<div class="notice notice-info is-dismissible" style="border-left-color: #0073aa; padding: 15px;">
                    Thanks for Choosing <strong>Just WP Migrate</strong>.
                  </div>';

            Option::delete('jwm_activated');
        }
    }
}
