<?php
session_start();

$baseDir = realpath(__DIR__ . '/mx_results');
$expectedDir = realpath($baseDir . '/' . session_id());

if (isset($_GET['folder'])) {
    $requestedDir = realpath(urldecode($_GET['folder']));

    if ($requestedDir === $expectedDir && is_dir($requestedDir)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($requestedDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            $todo = $fileinfo->isDir() ? 'rmdir' : 'unlink';
            $todo($fileinfo->getRealPath());
        }
        rmdir($requestedDir);
        echo "<p style='color:white;'>✅ Your folder has been safely deleted.</p>";
    } else {
        echo "<p style='color:red;'>⛔ Unauthorized or invalid folder path.</p>";
    }
} else {
    echo "<p style='color:orange;'>⚠️ No folder specified.</p>";
}
?>
<button onclick="history.back();" style="margin-top: 20px;">🔙 Go Back</button>
