<?php

require_once "../vendor/autoload.php";
require_once "../config.php";

// configure logger
Logger::configure("../config.php");
$logger = Logger::getLogger('maltslist export-xlsx');

// variables
$timestamp = date("Y-m-d H:i:s");
$dataArray = [];

// check if db exists
if (file_exists("../db/$database")) {
    $db = new SQLite3("../db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $db->enableExceptions(true);

    // get current data
    $logger->info("Trying to query database for current list data");
    try {
        $result = $db->query("SELECT * from 'list'");

        while ($row = $result->fetchArray(1)) {
            array_push($dataArray, $row);
        }
    }
    
    catch (Exception $e) {
        $exceptionMessage = $e->getMessage();
        $logger->FATAL("Unable to query database for current list data due to excetion: $exceptionMessage");
        header("location:".$siteUrl."?error_title=db putsis!&error_msg=$exceptionMessage");
        exit;
    }
}

else {
    $logger->FATAL("database file doesn't exist");
    header("location:".$siteUrl."?error_title=db putsis!&error_msg=File doesn't exist. Please run setupDatabase.php");
}

$logger->info("Successfully queried database for current list data");

$tempFile = tmpfile();

fprintf($tempFile, chr(0xEF).chr(0xBB).chr(0xBF)); // Change it to utf-8 lmao
$csvHeaders = array("index","title","score","progress","progress_length","type","rewatch","favorite","comment","is_deleted");
fputcsv($tempFile, $csvHeaders);

foreach ($dataArray as $array_ind) {
    fputcsv($tempFile, $array_ind);
}

$tempFileMetaData = stream_get_meta_data($tempFile);
$tempFilePath = $tempFileMetaData["uri"];

$size = filesize($tempFilePath);

$timestampCsv = date("Y-m-d");

header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: 0");
header("Content-Disposition: attachment; filename=\"maltslist-".$timestampCsv."-".generateRandomString(5).".csv\"");
header("Content-Length: " . $size);
header("Pragma: public");
readfile($tempFilePath);

fclose($tempFile);

function generateRandomString($length) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}