<?php
session_start();
$userFolder = 'mx_results/' . session_id();
if (!is_dir($userFolder)) {
    mkdir($userFolder, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileUpload'])) {
    $file = $_FILES['fileUpload']['tmp_name'];
    if (!file_exists($file) || filesize($file) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'File is missing or empty']);
        exit;
    }

    $destFile = "$userFolder/uploaded_emails.txt";
    if (!move_uploaded_file($file, $destFile)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file']);
        exit;
    }

    file_put_contents("$userFolder/progress.log", "Starting...\n");
    exec("php worker_process.php " . escapeshellarg($userFolder) . " > /dev/null 2>&1 &");

    echo json_encode(['status' => 'started']);
    exit;
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}
