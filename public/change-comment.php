<?php

require_once "../vendor/autoload.php";
require_once "../config.php";

// variables
$timestamp = date("Y-m-d H:i:s");
$dataArray = [];

// post variables
$index = $_POST["form-index"];
$formComment = $_POST['form-comment'];

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
            $exceptionMessage = $e->getMessage();
            //header("location:".$siteUrl."?error_title=db putsis!&error_msg=$exceptionMessage");
            exit;
        }
    }
    
    else {
        //header("location:".$siteUrl."?error_title=db putsis!&error_msg=File doesn't exist. Please run setupDatabase.php");
    }
    
        $currentEntryTitle = $dataArray[$index-1]["title"];
        
        // check form-progress-subtract
        if (isset($index)) {

            if(!empty($formComment) and ($formComment !== "n/a")) {
                
                $statement = "UPDATE list SET comment='$formComment' WHERE `index`=$index";

                try {
                    pushToDatabase($db, $statement, $timestamp);
                }
                
                catch (Exception $e) {
                    $exceptionMessage = $e->getMessage();
                    header("location:https://list.wavy.ws?error_title=Failed to update entry!&error_msg=Couldn't update $currentEntryTitle due to exception: $exceptionMessage");
                    exit;
                }

                header("location:https://list.wavy.ws?regular_title=Entry updated!&regular_msg=$currentEntryTitle has been updated!");
                
            }

            else if ($formComment === "n/a") {
            
                $statement = "UPDATE list SET comment=NULL WHERE `index`=$index";

                try {
                    pushToDatabase($db, $statement, $timestamp);
                }
                
                catch (Exception $e) {
                    $exceptionMessage = $e->getMessage();
                    header("location:https://list.wavy.ws?error_title=Failed to update entry!&error_msg=Couldn't update $currentEntryTitle due to exception: $exceptionMessage");
                    exit;
                }

                header("location:https://list.wavy.ws?regular_title=Entry updated!&regular_msg=$currentEntryTitle has been updated!");
            
            }
            
            else {
                header("location:https://list.wavy.ws?error_title=Failed to change comment!&error_msg=Comment value empty");
            }

        }
        
        else {
            header("location:https://list.wavy.ws?error_title=Failed to change comment!&error_msg=Index wasn't not set");
        }
}

else {
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