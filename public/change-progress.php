<?php

require_once "../vendor/autoload.php";
require_once "../config.php";

// configure logger
Logger::configure("../config.php");
$logger = Logger::getLogger('maltslist change-progress');

// variables
$timestamp = date("Y-m-d H:i:s");
$dataArray = [];

// post variables
$entry = $_POST['entry'];
$formProgressSubtract = $_POST['form-progress-subtract'];
$formProgressAdd = $_POST['form-progress-add'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logger->info("change-progress.php POST request received");
    
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
        
        // check form-progress-subtract
        if (isset($formProgressSubtract)) {

            $allowedMaximumProgress = $dataArray[$entry-1]["progress_length"];
            $currentProgress = $dataArray[$entry-1]["progress"];

            if ($currentProgress-$formProgressSubtract<0) {
                $logger->fatal("Progress check failed. Can't go below 0 in progress");
                header("location:".$siteUrl."?error_title=Can't go below 0 in progress&error_msg=Really?");
            }

            else {
                $formProgressSubtractResult = $currentProgress-$formProgressSubtract;
                $statement = "UPDATE list SET progress=$formProgressSubtractResult WHERE `index`=$entry";
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
        
        // check form-progress-add
        if (isset($formProgressAdd)) {
            $allowedMaximumProgress = $dataArray[$entry-1]["progress_length"];
            $currentProgress = $dataArray[$entry-1]["progress"];
            
            if ($currentProgress+$formProgressAdd>$allowedMaximumProgress) {
                $logger->fatal("Progress check failed. Can't go past the limit specified in progress_length");
                header("location:".$siteUrl."?error_title=Failed to change entry!&error_title=Can't go past the limit specified in progress_length&error_msg=Really?");
            }
            
            else {
                $formProgressAddResult = $currentProgress+$formProgressAdd;
                $statement = "UPDATE list SET progress=$formProgressAddResult WHERE `index`=$entry";
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
}

else {
    $logger->info("change-progress.php GET request received. Redirecting to ".$siteUrl);
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