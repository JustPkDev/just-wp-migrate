<?php

class Import
{
    public static function import_ajax()
    {
        if (!function_exists('wp_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';

        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            WP_Filesystem();
        }

        check_ajax_referer('jwm_import_nonce', 'nonce');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error(["message" => "Only POST Method Allowed"]);
            wp_die();
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(["message" => "File Not Found"]);
            wp_die();
        }

        $file = $_FILES['file'];
        $upload_overrides = ['test_form' => false];
        $movefile = wp_handle_upload($file, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $targetDir = trailingslashit(JWM_BACKUP_FOLDER);
            $newFileName = sanitize_file_name(pathinfo($file['name'], PATHINFO_FILENAME)) . '-' . wp_rand(1000, 9999) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFilePath = $targetDir . $newFileName;

            if (!$wp_filesystem || !isset($wp_filesystem)) {
                wp_send_json_error(["message" => "Filesystem API is not initialized"]);
                wp_die();
            }

            if ($wp_filesystem->move($movefile['file'], $newFilePath, true)) {
                wp_send_json_success(["message" => "File Uploaded Successfully!", "file_url" => esc_url($newFilePath)]);
            } else {
                wp_send_json_error(["message" => "File move failed!"]);
            }
        } else {
            wp_send_json_error(["message" => "File Not Uploaded: " . esc_html($movefile['error'])]);
        }

        wp_die();
    }

    public static function jwm_allow_custom_uploads($mimes)
    {
        $mimes['jwm'] = 'application/octet-stream';
        $mimes['zip'] = 'application/zip';
        return $mimes;
    }

    public static function register()
    {
        add_filter('upload_mimes', [__CLASS__, 'jwm_allow_custom_uploads']);
        add_action('wp_ajax_jwm_import_file', [__CLASS__, 'import_ajax']);
    }
}
