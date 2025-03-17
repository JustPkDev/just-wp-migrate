<?php

if (!defined('ABSPATH')) exit;

ini_set('max_execution_time', '3000');
ini_set('max_input_time', '3000');
ini_set('memory_limit', '1024M');

define('JWM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JWM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('JWM_BACKUP_NAME', 'jwm_backups');
define('JWM_BACKUP_FOLDER', ABSPATH . '/wp-content/jwm_backups/');
define('JWM_BACKUP_URL', content_url() . '/jwm_backups/');
