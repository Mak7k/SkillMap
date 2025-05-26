<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Not authorized");
}

$purchase_id = (int)($_GET['purchase_id'] ?? 0);
if ($purchase_id <= 0) {
    die("Invalid purchase_id");
}

$mysqli = new mysqli("localhost", "root", "", "skillmap_db");
if ($mysqli->connect_error) {
    die("DB error: " . $mysqli->connect_error);
}

$sql = "SELECT * FROM user_purchases WHERE id = ? AND buyer_id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $purchase_id, $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
$purchase = $res->fetch_assoc();
if (!$purchase) {
    die("Purchase not found or you don't own it.");
}
$stmt->close();

$project_files_json = $purchase['project_files'] ?? '[]';
$project_files = json_decode($project_files_json, true);
if (!is_array($project_files)) {
    $project_files = [];
}
if (empty($project_files)) {
    die("No files to download for this purchase.");
}

$zip = new ZipArchive();
$zipName = "purchase_" . $purchase_id . ".zip";
$tmpFile = tempnam(sys_get_temp_dir(), "zip");
if ($zip->open($tmpFile, ZipArchive::CREATE) !== TRUE) {
    die("Could not create zip file in temp.");
}

function getFullPath($relativePath) {
    $relativePath = str_replace('\\', '/', $relativePath);
    return __DIR__ . '/projects/' . $relativePath;
}

foreach ($project_files as $file) {
    if (is_string($file)) {
        $relative = $file;
    } elseif (is_array($file) && isset($file['src'])) {
        $relative = $file['src']; 
    } else {
        continue;
    }
    $filePath = getFullPath($relative);
    if (file_exists($filePath) && is_file($filePath)) {
        $zip->addFile($filePath, basename($filePath));
    }
}

$zip->close();

if (filesize($tmpFile) === 0) {
    unlink($tmpFile);
    die("No valid files found to download.");
}

// Устанавливаем cookie, сигнализирующую об окончании формирования архива
setcookie("fileDownload", "true", time()+3600, "/");

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($tmpFile));
readfile($tmpFile);
unlink($tmpFile);
exit;
?>
