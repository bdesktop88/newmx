<?php
if ($argc < 2) die("Usage: php worker_process.php <userFolder>\n");

$userFolder = $argv[1];
$progressFile = "$userFolder/progress.log";
$uploadFile = "$userFolder/uploaded_emails.txt";

$categories = ['Gmail', 'G Suite', 'Office365', 'Yahoo', 'AOL', 'Hotmail/MSN', 'Others'];
$categoryFiles = [];
foreach ($categories as $cat) {
    $filename = strtolower(str_replace(['/', ' '], ['_', '_'], $cat)) . '.txt';
    $categoryFiles[$cat] = "$userFolder/$filename";
}

$emails = array_filter(array_map('trim', file($uploadFile)), function ($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
});

$total = count($emails);
foreach ($emails as $i => $email) {
    $category = getEmailCategory($email);
    file_put_contents($categoryFiles[$category], $email . PHP_EOL, FILE_APPEND);

    if (($i + 1) % 10 === 0 || ($i + 1) === $total) {
        $percent = round((($i + 1) / $total) * 100);
        file_put_contents($progressFile, "Processing: $percent%\n");
    }
}

$zipFile = "$userFolder/mx_results.zip";
$zip = new ZipArchive();
if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
    foreach ($categoryFiles as $file) {
        if (file_exists($file)) {
            $zip->addFile($file, basename($file));
        }
    }
    $zip->close();
}

file_put_contents($progressFile, "done\n");
@unlink($uploadFile); // Clean up uploaded file

function getEmailCategory($email) {
    $domain = substr(strrchr($email, "@"), 1);
    $mxRecords = [];
    if (getmxrr($domain, $mxRecords)) {
        foreach ($mxRecords as $mx) {
            $mx = strtolower($mx);
            if (strpos($mx, 'google.com') !== false) {
                return $domain === 'gmail.com' ? 'Gmail' : 'G Suite';
            }
            if (strpos($mx, 'outlook.com') !== false || strpos($mx, 'protection.outlook.com') !== false) {
                return in_array($domain, ['hotmail.com', 'msn.com']) ? 'Hotmail/MSN' : 'Office365';
            }
            $yahooMXs = ['mx-vip2.mail.gq1.yahoo.com', 'mta7.am0.yahoodns.net', 'mta5.am0.yahoodns.net'];
            foreach ($yahooMXs as $yahooMX) {
                if (strpos($mx, $yahooMX) !== false) return 'Yahoo';
            }
            $aolMXs = ['aolsmtp-in.odin.com', 'mailin-02.mx.aol.com', 'mailin-03.mx.aol.com', 'mx-aol.mail.gm0.yahoodns.net'];
            foreach ($aolMXs as $aolMX) {
                if (strpos($mx, $aolMX) !== false) return 'AOL';
            }
        }
    }
    return 'Others';
}
