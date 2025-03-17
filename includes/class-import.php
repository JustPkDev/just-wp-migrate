<?php

class Import
{

    public static function import_ajax()
    {
        // Security Check
        check_ajax_referer('jwm_import_nonce', 'nonce');

        // Allow Only POST Requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(["message" => "Only POST Method Allowed"]);
            wp_die();
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(["message" => "File Not Found"]);
            wp_die();
        }

        $file = $_FILES['file'];
        $targetDir = JWM_BACKUP_FOLDER;
        $filePath = $targetDir . pathinfo($file['name'], PATHINFO_FILENAME) . '-' . rand(1000, 9999) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            wp_send_json_error(["message" => "File Not Uploaded"]);
            wp_die();
        }

        wp_send_json_success(["message" => "✅ File Uploaded Successfully."]);
        wp_die();
    }

    public static function register()
    {
        add_action('wp_ajax_jwm_import_file', [__CLASS__, 'import_ajax']);
    }
}
