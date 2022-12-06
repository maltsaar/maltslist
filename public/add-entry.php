<?php

require_once "../vendor/autoload.php";
require_once "../config.php";

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

    // check if db exists
    if (file_exists("../db/$database")) {
        $db = new SQLite3("../db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        $db->enableExceptions(true);
        
        // get current data
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

    if (isset($which)) {
        
        if ($which === "add-entry") {
            
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
                        
                        try {
                            pushToDatabase($db, $statement, $timestamp);
                        }
                         
                        catch (Exception $e) {
                            $exceptionMessage = $e->getMessage();
                            header("location:".$siteUrl."?error_title=Failed to add entry!&error_msg=Failed to add new entry to database due to exception: $exceptionMessage");
                            exit;
                        }
                        header("location:".$siteUrl."?regular_title=Entry added!&regular_msg=Title: $title added to the list!");
                    }
                    
                    else {
                        header("location:".$siteUrl."?error_title=Failed to add entry!&error_msg=Progress ($progress) is bigger than Total length ($progress_length)");
                    }
                }
                
                else {
                    header("location:".$siteUrl."?error_title=Failed to add entry!&error_msg=Title can't be empty!");
                }
            }
            
            else {
                header("location:".$siteUrl."?error_title=Missing required POST parameter!&error_msg=One of these parameters was not set: form-title, form-score, form-progress, form-progress-length, form-rewatch, form-favorite");
            }
        }
        
        else {
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
