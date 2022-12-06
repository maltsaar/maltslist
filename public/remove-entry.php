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
        if ($which === "remove-entry") {
            if (isset($index)) {
                $currentIndexTitle = $dataArray[$index-1]["title"];
                if (isset($dataArray[$index-1]["index"])) {
                    if ($dataArray[$index-1]["is_deleted"] !== "yes") {
                        $statement = "UPDATE 'list' SET is_deleted='yes' WHERE `index`=$index";
                        
                        try {
                            pushToDatabase($db, $statement, $timestamp);
                        }
                        
                        catch (Exception $e) {
                            $exceptionMessage = $e->getMessage();
                            header("location:".$siteUrl."?error_title=Failed to remove entry!&error_msg=Failed to remove entry $index from the database due to exception: $exceptionMessage");
                            exit;
                        }
                        
                        header("location:".$siteUrl."?regular_title=Entry removed!&regular_msg=$currentIndexTitle removed from the list!");
                    }
                    
                    else {
                        header("location:".$siteUrl."?error_title=Failed to remove entry!&error_msg=Specified index has already been deleted!");
                    }   
                }
                
                else {
                    header("location:".$siteUrl."?error_title=Failed to remove entry!&error_msg=Specified index doesn't exist!");
                }   
            }
            
            else {
                header("location:".$siteUrl."?error_title=Failed to remove entry!&error_msg=Index hasn't been specified!");
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
