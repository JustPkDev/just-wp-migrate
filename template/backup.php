<?php


$backups = [];
$files = scandir(JWM_BACKUP_FOLDER);

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'jwm') {
        $size = filesize(JWM_BACKUP_FOLDER . $file);
        $formattedSize = formatSize($size);
        $date = gmdate("Y-m-d H:i:s", filemtime(JWM_BACKUP_FOLDER . $file));

        $backups[] = [
            'name' => $file,
            'size' => $formattedSize,
            'date' => $date
        ];
    }
}

function formatSize($bytes, $decimals = 2)
{
    $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . ' ' . $sizes[$factor];
}

if (count($backups) > 0) {
    $backups = array_reverse($backups);
}

?>
<div class="row w-100 mt-3">
    <div class="col-lg-9 p-1">
        <div class="bg-white rounded p-3">
            <div class="d-flex justify-content-between">
                <div class="d-flex align-items-center justify-content-center">
                    <img
                        src="<?php echo esc_url(JWM_PLUGIN_URL) . 'assets/images/backup.png' ?>"
                        alt="justpkdev"
                        style="width: 35px; height: 35px;">
                    <h5 class="mt-2 ms-2">Backups</h5>
                </div>
                <button class="btn btn-primary btn-css-create" id="create">Create Backup</button>
            </div>
            <div class="mt-3 px-1">
                <?php if (count($backups) > 0): ?>
                    <?php $i = 1 ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Size</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            <?php foreach ($backups as $file): ?>
                                <tr>
                                    <th scope="row"><?php echo intval($i) ?></th>
                                    <td><?php echo esc_html($file['name']) ?></td>
                                    <td><?php echo esc_html($file['date']) ?></td>
                                    <td><?php echo esc_html($file['size']) ?></td>
                                    <td class="position-relative">
                                        <button class="btn btn-toolbar px-1 dots" data-open="false">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16">
                                                <path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0" />
                                            </svg>
                                        </button>
                                        <div class="position-absolute d-flex flex-column overflow-hidden rounded d-none dropdown-js z-3"
                                            style="right: 0px;" data-name="<?php echo esc_attr($file['name']) ?>">
                                            <button
                                                class="btn btn-css-create mt-0 rounded-0 px-3 text-white border-bottom restore"
                                                style="font-size: 14px; height: 35px;">
                                                restore
                                            </button>
                                            <button
                                                class="btn btn-css-create mt-0 rounded-0 px-3 text-white border-bottom download"
                                                style="font-size: 14px; height: 35px;" data-path="<?php echo esc_url(JWM_BACKUP_URL) ?>">
                                                download
                                            </button>
                                            <button
                                                class="btn btn-css-create mt-0 rounded-0 px-3 text-white border-bottom delete"
                                                style="font-size: 14px; height: 35px;">
                                                delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php $i++ ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="mt-5 mb-5 pt-5 pb-5 text-center">Not Created Yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-3 p-1">
        <?php include JWM_PLUGIN_DIR . 'template/contact.php' ?>
    </div>
</div>