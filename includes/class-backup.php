<?php

class Backup
{

    private $robot_path = JWM_BACKUP_FOLDER . "robot.txt";
    private $database_path = JWM_BACKUP_FOLDER . "database.sql";
    private $css_path = JWM_BACKUP_FOLDER . "style.css";
    private $htaccess_path = ABSPATH . ".htaccess";
    private $robot_content = "";
    private $database_content = "";
    private $htaccess_content = "\n\n
    <IfModule mod_php.c>
        php_value upload_max_filesize 10G
        php_value post_max_size 10G
        php_value memory_limit 256M
        php_value max_execution_time 600
        php_value max_input_time 600
    </IfModule>\n\n";

    public static function delete_dir($folderPath)
    {
        if (!is_dir($folderPath)) {
            return false;
        }

        clearstatcache();
        wp_cache_flush();
        global $wpdb;
        $wpdb->close();

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            @exec("rm -rf " . escapeshellarg($folderPath), $output, $returnCode);
            if ($returnCode === 0) {
                return true;
            }
        } else {
            @exec("rmdir /s /q " . escapeshellarg($folderPath), $output, $returnCode);
            if ($returnCode === 0) {
                return true;
            }
        }

        return false;
    }

    private function create_content()
    {
        $this->robot_content .= "User-agent: * \n";
        $this->robot_content .= "Disallow: /" . JWM_BACKUP_NAME . "/\n";
        $this->robot_content .= "Disallow: /wp-content/" . JWM_BACKUP_NAME . "/\n";

        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();
        global $wp_filesystem;

        $existing_content = $wp_filesystem->get_contents($this->htaccess_path);

        $new_content = $existing_content . "\n" . $this->htaccess_content;

        $wp_filesystem->put_contents($this->htaccess_path, $new_content, FS_CHMOD_FILE);
    }

    public static function create_folder()
    {
        $instance = new self();

        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();
        global $wp_filesystem;

        if (!$wp_filesystem->is_dir(JWM_BACKUP_FOLDER)) {
            $wp_filesystem->mkdir(JWM_BACKUP_FOLDER, FS_CHMOD_DIR);
        } else {
            return;
        }

        $instance->create_content();
        file_put_contents($instance->robot_path, $instance->robot_content);
    }

    public static function delete_folder()
    {
        $instance = new self();

        $content = file_get_contents($instance->htaccess_path);
        $updatedContent = preg_replace('/<IfModule mod_php\.c>.*?<\/IfModule>/s', '', $content);
        file_put_contents($instance->htaccess_path, $updatedContent);

        self::delete_dir(JWM_BACKUP_FOLDER);
    }

    public static function create_sql()
    {
        $instance = new self();
        global $wpdb;
        $instance->database_content .= "-- OLD_URL=" . get_site_url() . "\n";
        $tables = $wpdb->get_col('SHOW TABLES');

        foreach ($tables as $table) {
            $instance->database_content .= "DROP TABLE IF EXISTS `$table`;\n";
            $createTable = $wpdb->get_row("SHOW CREATE TABLE `$table`", ARRAY_N);
            $instance->database_content .= $createTable[1] . ";\n\n";

            $rows = $wpdb->get_results("SELECT * FROM `$table`", ARRAY_A);
            foreach ($rows as $row) {
                $values = array_map('addslashes', array_values($row));
                $instance->database_content .= "INSERT INTO `$table` VALUES ('" . implode("','", $values) . "');\n";
            }

            $instance->database_content .= "\n\n";
        }

        return file_put_contents($instance->database_path, $instance->database_content);
    }

    public static function create_zip()
    {
        $instance = new self();
        $zip_path = JWM_BACKUP_FOLDER . wp_parse_url(home_url(), PHP_URL_HOST) . '-' . sanitize_file_name(get_bloginfo('name')) .
            "-backup-" . time() . '-' . wp_rand(0, 10000) . '.jwm';
        $source = WP_CONTENT_DIR;
        $excluded = [JWM_BACKUP_NAME, 'just-wp-migrate'];
        $zip = new ZipArchive();

        if ($zip->open($zip_path, ZipArchive::CREATE) !== true) {
            wp_send_json_error(['message' => 'Failed to create zip']);
        }

        $zip->addFile($instance->database_path, 'database.sql');
        $zip->addFile($instance->css_path, 'style.css');

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            $filePath = str_replace($source . DIRECTORY_SEPARATOR, '', $file);

            foreach ($excluded as $exclude) {
                if (strpos($filePath, $exclude) !== false) {
                    continue 2;
                }
            }

            $zip->addFile($file, 'wp-content/' . $filePath);
        }

        $zip->close();
    }

    public static function send_progress($status, $progress)
    {
        echo json_encode(['status' => $status, 'progress' => $progress]) . "\n";
        ob_flush();
        flush();
        sleep(1);
    }

    public static function extract_file($file_path)
    {
        $extractPath = JWM_BACKUP_FOLDER . 'extracted-' . time();

        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();
        global $wp_filesystem;

        if (!$wp_filesystem->is_dir($extractPath)) {
            $wp_filesystem->mkdir($extractPath, FS_CHMOD_DIR);
        }

        $zip = new ZipArchive();

        if ($zip->open($file_path) === true) {
            $zip->extractTo($extractPath);
            $zip->close();
            return $extractPath;
        } else {
            return false;
        }
    }

    public static function replace_old_site($extractedPath)
    {
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        WP_Filesystem();
        global $wp_filesystem;
        $wpContent = WP_CONTENT_DIR;

        $skipFolders = [
            'plugins/just-wp-migrate',
            basename(JWM_BACKUP_FOLDER)
        ];

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($wpContent, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            $filePath = str_replace('\\', '/', $file->getRealPath());
            foreach ($skipFolders as $skip) {
                if (strpos($filePath, $skip) !== false) {
                    continue 2;
                }
            }
            if ($file->isDir()) {
                $wp_filesystem->delete($filePath, true);
            } else {
                $wp_filesystem->delete($filePath);
            }
        }

        $backupContent = $extractedPath . '/wp-content';
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($backupContent, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $destPath = $wpContent . DIRECTORY_SEPARATOR . $files->getSubPathName();

            if ($file->isDir()) {
                if (!$wp_filesystem->is_dir($destPath)) {
                    $wp_filesystem->mkdir($destPath, FS_CHMOD_DIR);
                }
            } else {
                $wp_filesystem->copy($file->getRealPath(), $destPath, true);
            }
        }

        return true;
    }

    public static function import_database($extractedPath)
    {
        global $wpdb;
        $databasePath = $extractedPath . '/database.sql';

        if (!file_exists($databasePath)) {
            return false;
        }

        $sql = file_get_contents($databasePath);
        $newUrl = get_site_url();
        preg_match('/--\s*OLD_URL=(.+)/', $sql, $matches);
        $oldUrl = $matches[1] ?? null;

        if (!$oldUrl) {
            return false;
        }

        $sql = str_replace($oldUrl, $newUrl, $sql);
        $queries = explode(";\n", $sql);

        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $wpdb->query($wpdb->prepare("%s", $query));
            }
        }

        return true;
    }

    public static function create_backup_ajax()
    {
        $instance = new self();

        // Security Check
        check_ajax_referer('jwm_backup_nonce', 'nonce');

        // Allow Only POST Requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(["message" => "Only Post Method Allowed"]);
            wp_die();
        }

        $theme = get_option('stylesheet');
        $custom_css = wp_get_custom_css($theme);
        if (!file_put_contents($instance->css_path, $custom_css)) {
            wp_send_json_error(["message" => "Css Not Added!"]);
            wp_die();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $instance->send_progress('Initializing', 10);
        sleep(2);

        $instance->send_progress('Adding Database', 30);
        if (!$instance->create_sql()) {
            wp_send_json_error(["message" => "Database Not Added!"]);
            wp_die();
        }

        $instance->send_progress('Adding Content', 60);
        $instance->create_zip();

        $instance->send_progress('Finishing Backup', 90);
        global $wp_filesystem;
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if (empty($wp_filesystem)) {
            WP_Filesystem();
        }

        if ($wp_filesystem->exists($instance->database_path)) {
            $wp_filesystem->delete($instance->database_path);
        }

        if ($wp_filesystem->exists($instance->css_path)) {
            $wp_filesystem->delete($instance->css_path);
        }


        $instance->send_progress('Finished', 100);
        exit;
    }

    public static function delete_file_ajax()
    {
        global $wp_filesystem;

        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if (empty($wp_filesystem)) {
            WP_Filesystem();
        }

        // Security Check
        check_ajax_referer('jwm_backup_nonce', 'nonce');

        // Allow Only POST Requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(["message" => "Only Post Method Allowed"]);
            wp_die();
        }

        if (!isset($_POST['name']) || empty($_POST['name'])) {
            wp_send_json_error(["message" => "name is missing or required!"]);
            wp_die();
        }

        if (!file_exists(JWM_BACKUP_FOLDER . $_POST['name'])) {
            wp_send_json_error(["message" => "File is not exist!"]);
            wp_die();
        }

        $file_path = trailingslashit(JWM_BACKUP_FOLDER) . sanitize_file_name($_POST['name']);

        if (!$wp_filesystem->exists($file_path) || !$wp_filesystem->delete($file_path, false)) {
            wp_send_json_error(["message" => "Cannot Delete!"]);
            wp_die();
        }

        wp_send_json_success(["message" => "File Deleted."]);
        wp_die();
    }

    public static function restore_file_ajax()
    {
        $instance = new self();

        // Security Check
        check_ajax_referer('jwm_backup_nonce', 'nonce');

        // Allow Only POST Requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(["message" => "Only Post Method Allowed"]);
            wp_die();
        }

        if (!isset($_POST['name']) || empty($_POST['name'])) {
            wp_send_json_error(["message" => "name is missing or required!"]);
            wp_die();
        }

        if (!file_exists(JWM_BACKUP_FOLDER . $_POST['name'])) {
            wp_send_json_error(["message" => "File is not exist!"]);
            wp_die();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');

        $instance->send_progress('Initializing', 10);
        sleep(2);

        $instance->send_progress('Extracting', 20);
        $ext_path = $instance->extract_file(JWM_BACKUP_FOLDER . $_POST['name']);
        if (!$ext_path) {
            wp_send_json_error(["message" => "File is not extracted!"]);
            wp_die();
        }

        $instance->send_progress('Replacing Content', 30);
        if (!$instance->replace_old_site($ext_path)) {
            wp_send_json_error(["message" => "Error While Replacing!"]);
            wp_die();
        }

        $instance->send_progress('Replacing Database', 70);
        if (!$instance->import_database($ext_path)) {
            wp_send_json_error(["message" => "Error While importing database!"]);
            wp_die();
        }

        $instance->send_progress('Finshing', 90);

        // css
        $css = file_get_contents($ext_path . '/style.css');
        wp_update_custom_css_post($css);

        // clear cache
        clearstatcache();
        wp_cache_flush();
        $instance->delete_dir($ext_path . '/');

        $instance->send_progress('Finished', 100);
        exit;
    }

    public static function register_ajax()
    {
        add_action('wp_ajax_jwm_create_backup', [__CLASS__, 'create_backup_ajax']);
        add_action('wp_ajax_jwm_delete_file', [__CLASS__, 'delete_file_ajax']);
        add_action('wp_ajax_jwm_restore_backup', [__CLASS__, 'restore_file_ajax']);
    }
}
