<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../vendor/autoload.php";
require_once "../config.php";

// configure logger
Logger::configure("../config.php");
$logger = Logger::getLogger('maltslist change-comment');

// variables
$timestamp = date("Y-m-d H:i:s");
$dataArray = [];

// post variables
$index = $_POST["form-index"];
$formComment = $_POST['form-comment'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $logger->info("change-comment.php POST request received");
    
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
            //header("location:".$siteUrl."?error_title=db putsis!&error_msg=$exceptionMessage");
            exit;
        }
    }
    
    else {
        $logger->FATAL("database file doesn't exist");
        //header("location:".$siteUrl."?error_title=db putsis!&error_msg=File doesn't exist. Please run setupDatabase.php");
    }
    
    $logger->info("Successfully queried database for current list data");
    
        $currentEntryTitle = $dataArray[$index-1]["title"];
        
        // check form-progress-subtract
        if (isset($index)) {

            if(!empty($formComment) and ($formComment !== "n/a")) {
                
                $statement = "UPDATE list SET comment='$formComment' WHERE `index`=$index";
                $logger->info("Trying to update database for index $index");

                try {
                    pushToDatabase($db, $statement, $timestamp);
                }
                
                catch (Exception $e) {
                    $exceptionMessage = $e->getMessage();
                    $logger->fatal("Failed to update database for index $index entry due to exception: $exceptionMessage");
                    header("location:https://list.wavy.ws?error_title=Failed to update entry!&error_msg=Couldn't update $currentEntryTitle due to exception: $exceptionMessage");
                    exit;
                }

                $logger->info("Successfully updated database for index $index");
                header("location:https://list.wavy.ws?regular_title=Entry updated!&regular_msg=$currentEntryTitle has been updated!");
                
            }

            else if ($formComment === "n/a") {
            
                $statement = "UPDATE list SET comment=NULL WHERE `index`=$index";
                $logger->info("Trying to update database for index $index");

                try {
                    pushToDatabase($db, $statement, $timestamp);
                }
                
                catch (Exception $e) {
                    $exceptionMessage = $e->getMessage();
                    $logger->fatal("Failed to update database for index $index entry due to exception: $exceptionMessage");
                    header("location:https://list.wavy.ws?error_title=Failed to update entry!&error_msg=Couldn't update $currentEntryTitle due to exception: $exceptionMessage");
                    exit;
                }

                $logger->info("Successfully updated database for index $index");
                header("location:https://list.wavy.ws?regular_title=Entry updated!&regular_msg=$currentEntryTitle has been updated!");
            
            }
            
            else {
                $logger->info("Comment value empty. Redirecting to ".$siteUrl);
                header("location:https://list.wavy.ws?error_title=Failed to change comment!&error_msg=Comment value empty");
            }

        }
        
        else {
            $logger->info("Index not set. Redirecting to ".$siteUrl);
            header("location:https://list.wavy.ws?error_title=Failed to change comment!&error_msg=Index wasn't not set");
        }
}

else {
    $logger->info("change-comment.php GET request received. Redirecting to ".$siteUrl);
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