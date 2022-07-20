<?php

require_once "../vendor/autoload.php";
require_once "../config.php";

// configure logger
Logger::configure("../config.php");
$logger = Logger::getLogger('maltslist add-entry');

// variables
$timestamp = date("Y-m-d H:i:s");
$dataArray = [];

// post variables
$which = $_POST["form-which"];

$index = $_POST["form-index"];
$title = $_POST["form-title"];
$score = $_POST["form-score"];
$progress = $_POST["form-progress"];
$progress_length = $_POST["form-progress-length"];
$type = $_POST["form-type"];
$rewatch = $_POST["form-rewatch"];
$favorite = $_POST["form-favorite"];
$comment = $_POST["form-comment"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $logger->info("add-entry.php POST request received");

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
            header("location:".$siteUrl."?error_title=db putsis!&error_msg=$e");
        }
    }
    
    else {
        header("location:".$siteUrl."?error_title=db putsis!&error_msg=File doesn't exist. Please run setupDatabase.php");
    }
    $logger->info("Successfully queried database for current list data");

    if (isset($which)) {
        $logger->info("Trying to check for add-entry or remove-entry");
        
        if ($which === "add-entry") {
            $logger->info("Got add-entry");
            
            if (isset($title, $score, $progress, $progress_length, $rewatch, $favorite)) {
                if (!empty($title)) {
                    if ($progress_length>=$progress) {
                        if (!empty($comment)) {
                            $statement = "INSERT into 'list' (title, score, progress, progress_length, type, rewatch, favorite, comment)
                            VALUES ('$title', $score, $progress, $progress_length, '$type', $rewatch, '$favorite', '$comment')";
                        } else if (empty($comment)) {
                            $statement = "INSERT into 'list' (title, score, progress, progress_length, type, rewatch, favorite, comment)
                            VALUES ('$title', $score, $progress, $progress_length, '$type', $rewatch, '$favorite', NULL)";
                        }
                        $logger->info("Trying to INSERT new data to database");
                        
                        try {
                            pushToDatabase($db, $statement, $timestamp);
                        }
                         
                        catch (Exception $e) {
                            $exceptionMessage = $e->getMessage();
                            $logger->FATAL("Failed to add new entry to database due to exception: $exceptionMessage");
                            header("location:".$siteUrl."?error_title=Failed to add entry!&error_msg=Failed to add new entry to database due to exception: $exceptionMessage");
                            exit;
                        }
                        $logger->info("Successfully added new entry: title - \"$title\" to database");
                        header("location:".$siteUrl."?regular_title=Entry added!&regular_msg=Title: $title added to the list!");
                    }
                    
                    else {
                        $logger->FATAL("Failed add-entry check because progress is bigger than progress_length");
                        header("location:".$siteUrl."?error_title=Failed to add entry!&error_msg=Progress ($progress) is bigger than Total length ($progress_length)");
                    }
                }
                
                else {
                    $logger->FATAL("Failed add-entry check because title can't be empty");
                    header("location:".$siteUrl."?error_title=Failed to add entry!&error_msg=Title can't be empty!");
                }
            }
            
            else {
                $logger->FATAL("Failed add-entry check because one of the required POST parameters is missing");
                header("location:".$siteUrl."?error_title=Missing required POST parameter!&error_msg=One of these parameters was not set: form-title, form-score, form-progress, form-progress-length, form-rewatch, form-favorite");
            }
        }
        
        else {
            $logger->FATAL("Got invalid POST parameter");
            header("location:".$siteUrl."?error_title=Invalid post parameter!&error_msg=form-which: $which");
        }
    }
}

function pushToDatabase($fDB, $fStatement, $fTimestamp) {
    $fDB->exec($fStatement);
    $fDB->exec("DELETE FROM 'last-updated'");
    $fDB->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$fTimestamp')");
    $fDB->close();
}

?>
