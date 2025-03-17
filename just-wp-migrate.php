<?php

// Plugin Name: Just WP Migrate
// Plugin URI: https://justpkdev.vercel.app/projects/27
// Description: A fast and efficient WordPress migration tool.
// Version: 1.0.0
// Author: JustPkDev
// Author URI: https://justpkdev.vercel.app
// License: GPL2

if (!defined('ABSPATH')) exit;

// modules
require_once plugin_dir_path(__FILE__) . 'config.php';
require_once JWM_PLUGIN_DIR . 'public/class-link.php';
require_once JWM_PLUGIN_DIR . 'includes/class-option.php';
require_once JWM_PLUGIN_DIR . 'includes/class-backup.php';
require_once JWM_PLUGIN_DIR . 'includes/class-import.php';
require_once JWM_PLUGIN_DIR . 'admin/class-admin.php';

Links::register();
Admin::init();
Backup::register_ajax();
Import::register();

function jwm_activate_plugin()
{
    Option::set('jwm_activated', 'yes');
    Backup::create_folder();
}
register_activation_hook(__FILE__, 'jwm_activate_plugin');
