<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../vendor/autoload.php";
require_once "../config.php";

// configure logger
Logger::configure("../config.php");
$logger = Logger::getLogger('maltslist increment-rewatch');

// variables
$timestamp = date("Y-m-d H:i:s");
$dataArray = [];

// post variables
$entry = $_POST['entry'];
$formRewatchAdd = $_POST['form-rewatch-add'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logger->info("increment-rewatch.php POST request received");
    
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
    
    if (isset($entry)) {
        $currentEntryTitle = $dataArray[$entry-1]["title"];
        
        // check form-rewatch-add
        if (isset($formRewatchAdd)) {
            $currentRewatch = $dataArray[$entry-1]["rewatch"];
            
            $statement = "UPDATE list SET rewatch=$currentRewatch+1 WHERE `index`=$entry";
            $logger->info("Trying to update database for index $entry");
            
            try {
                pushToDatabase($db, $statement, $timestamp);
            }
            
            catch (Exception $e) {
                $exceptionMessage = $e->getMessage();
                $logger->fatal("Failed to update database for index $entry entry due to exception: $exceptionMessage");
                header("location:".$siteUrl."?error_title=Failed to change entry!&error_msg=Exception: $exceptionMessage");
                exit;
            }
            
            $logger->info("Successfully updated database for index $entry");
            header("location:".$siteUrl."?regular_title=Entry updated!&regular_msg=$currentEntryTitle has been updated!");
        }
    }
}
    
else {
    $logger->info("increment-rewatch.php GET request received. Redirecting to ".$siteUrl);
    header("location:".$siteUrl);
    exit;
}

function pushToDatabase($fDB, $fStatement, $fTimestamp) {
    $fDB->exec($fStatement);
    $fDB->exec("DELETE FROM 'last-updated'");
    $fDB->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$fTimestamp')");
    $fDB->close();
}

?>