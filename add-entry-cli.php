<?php

require_once "./config.php";

$title = "Kes sa tsiviilis oled?";
$score = 1;
$progress = 0;
$progress_length = 1;
$type = "Film";
$rewatch = 0;
$favorite = "off";
$comment = "Good cinematography";
$status = "watching";

// no changing

echo date("H:i:s") . " - Trying to connect to database \"$database\"";
if (file_exists("./db/$database")) {
    $db = new SQLite3("./db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    echo " Success!\n";
} else {
    die(" Fail! (Please run setupDatabase.php)\n");
}

$statement = "INSERT into 'list' (title, score, progress, progress_length, type, rewatch, favorite, comment, status)
    VALUES ('$title', $score, $progress, $progress_length, '$type', $rewatch, '$favorite', '$comment', '$status')";

echo date("H:i:s") . " - Trying to add new entry to database\n";
try {
        try {
            echo date("H:i:s") . " - Inserting entry to database.\n";
            $db->exec($statement);
        } catch (Exception $e) {
            echo date("H:i:s") . " - Failed to insert entry!";
        }
} catch (Exception $e) {
    echo date("H:i:s") . " - Failed!\n";
}

?>
