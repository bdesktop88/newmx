<?php
$folder = __DIR__ . '/mx_results';
$now = time();
$expireSeconds = 6 * 3600; // 6 hours

foreach (glob("$folder/*") as $sessionFolder) {
    if (is_dir($sessionFolder)) {
        $lastModified = filemtime($sessionFolder);
        if ($now - $lastModified > $expireSeconds) {
            deleteFolder($sessionFolder);
        }
    }
}

function deleteFolder($dir) {
    $items = array_diff(scandir($dir), ['.', '..']);
    foreach ($items as $item) {
        $path = "$dir/$item";
        is_dir($path) ? deleteFolder($path) : unlink($path);
    }
    rmdir($dir);
}
