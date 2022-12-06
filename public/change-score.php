<?php

require_once "../vendor/autoload.php";
require_once "../config.php";

// variables
$timestamp = date("Y-m-d H:i:s");
$dataArray = [];

// post variables
$entry = $_POST['entry'];
$formScoreChange = $_POST['form-score-change'];

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
            header("location:".$siteUrl."?error_title=db putsis!&error_msg=$exceptionMessage");
            exit;
        }
    }
    
    else {
        header("location:".$siteUrl."?error_title=db putsis!&error_msg=File doesn't exist. Please run setupDatabase.php");
    }
    
    if (isset($entry)) {
        $currentEntryTitle = $dataArray[$entry-1]["title"];

        // check form-score-change
        if (isset($formScoreChange)) {
            $formScoreChangeNrArray = explode('-',$formScoreChange);
            $formScoreChangeResult = $formScoreChangeNrArray[1];

            $statement = "UPDATE list SET score=$formScoreChangeResult WHERE `index`=$entry";

            try {
                pushToDatabase($db, $statement, $timestamp);
            }
            
            catch (Exception $e) {
                $exceptionMessage = $e->getMessage();
                header("location:".$siteUrl."?error_title=Failed to update entry!&error_msg=Couldn't update $currentEntryTitle due to exception: $exceptionMessage");
                exit;
            }

            header("location:".$siteUrl."?regular_title=Entry updated!&regular_msg=$currentEntryTitle has been updated!");
        }
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