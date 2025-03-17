<?php

if (!defined('WP_UNINSTALL_PLUGIN')) exit;

require_once plugin_dir_path(__FILE__) . 'config.php';
require_once JWM_PLUGIN_DIR . 'public/class-link.php';
require_once JWM_PLUGIN_DIR . 'includes/class-option.php';
require_once JWM_PLUGIN_DIR . 'includes/class-backup.php';
require_once JWM_PLUGIN_DIR . 'includes/class-import.php';
require_once JWM_PLUGIN_DIR . 'admin/class-admin.php';
Backup::delete_folder();
