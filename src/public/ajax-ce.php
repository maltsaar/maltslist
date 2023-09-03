<?php

require_once "../vendor/autoload.php";
require_once "../config.php";
require_once "../includes/database-class.php";
require_once "../includes/bootstrap-twig.php";

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    // form GET variables
    $index = (int) trim($_GET["id"]);

    // get the actual entry
    $db = new database();
    $entry = $db->getSpecificRowByIndex($index, false);
    $db->close();

    $twigVariables = [
        "entry" => $entry ?? null
    ];
    $twig->display("modal-ce-form.html", $twigVariables);
    
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // form POST variables
    $index           = (int)    trim($_POST["form-index"]);
    $score           = (int)    trim($_POST["form-score"]);
    $progress        = (int)    trim($_POST["form-progress"]);
    $progress_length = (int)    trim($_POST["form-progress-length"]);
    $type            = (string) trim($_POST["form-type"]);
    $rewatch         = (int)    trim($_POST["form-rewatch"]);
    $favorite        = (string) trim($_POST["form-favorite"]);
    $comment         = (string) trim($_POST["form-comment"]);

    // get the actual entry
    $db = new database();
    $actualEntry = $db->getSpecificRowByIndex($index, false);

    // check which element was changed
    if ($score !== $actualEntry["score"]) {
        $changedElement = "score";
    }

    if ($progress !== $actualEntry["progress"]) {
        $changedElement = "progress";
    }

    if ($progress_length !== $actualEntry["progress_length"]) {
        $changedElement = "progress_length";
    }

    if ($type !== $actualEntry["type"]) {
        $changedElement = "type";
    }

    if ($rewatch !== $actualEntry["rewatch"]) {
        $changedElement = "rewatch";
    }

    if ($favorite !== $actualEntry["favorite"]) {
        $changedElement = "favorite";
    }

    // hack
    if (empty($comment)) {
        $comment = null;
    }

    if ($comment !== $actualEntry["comment"]) {
        $changedElement = "comment";
    }

    if (!isset($changedElement)) {
        error("Failed to change entry", "Failed to identify that any element has changed");
    }

    function update($db, $column, $newValue, $oldValue, $index, $sqliteType) {
        try {
            $db->updateSpecificColumn($column, $newValue, $oldValue, $index, $sqliteType);
        } catch(Exception $e) {
            error("Caught database exception", $e->getMessage());
        }
    }

    function changeScore($db, $newValue, $oldValue, $index) {
        if ($newValue > 5) {
            error("Failed to change progress", "Can't increment over 5");
        }

        if ($newValue < 0) {
            error("Failed to change progress", "Can't decrement under 0");
        }

        update($db, "score", $newValue, $oldValue, $index, "integer");
    }

    function changeProgress($db, $newValue, $oldValue, $index, $progress_length) {
        // subtract
        if ($newValue < $oldValue) {
            if ($newValue < 0) {
                error("Failed to change progress", "Can't decrement under 0");
            }
            update($db, "progress", $newValue, $oldValue, $index, "integer");
        }

        // add
        if ($newValue > $oldValue) {
            if ($newValue>$progress_length) {
                error("Failed to change progress", "Can't increment over length");
            }
            update($db, "progress", $newValue, $oldValue, $index, "integer");
        }
    }

    function changeProgressLength($db, $newValue, $oldValue, $index, $progress) {
        // subtract
        if ($newValue < $oldValue) {
            if ($newValue < $progress) {
                error("Failed to change length", "Can't decrement under progress");
            }
            update($db, "progress_length", $newValue, $oldValue, $index, "integer");
        }

        // add
        if ($newValue > $oldValue) {
            update($db, "progress_length", $newValue, $oldValue, $index, "integer");
        }
    }

    function changeType($db, $newValue, $oldValue, $index) {
        if ($newValue !== "film" && $newValue !== "tv") {
            error("Failed to change type", "User supplied incorrect value");
        }

        update($db, "type", $newValue, $oldValue, $index, "text");
    }

    function changeRewatch($db, $newValue, $oldValue, $index) {
        if ($newValue < 0) {
            error("Failed to change rewatch", "Can't decrement under 0");
        }

        update($db, "rewatch", $newValue, $oldValue, $index, "integer");
    }

    function changeFavorite($db, $newValue, $oldValue, $index) {
        if ($newValue !== "on" && $newValue !== "off") {
            error("Failed to change favorite", "User supplied incorrect value");
        }
        
        update($db, "favorite", $newValue, $oldValue, $index, "text");
    }

    function changeComment($db, $newValue, $oldValue, $index) {
        if (empty($oldValue)) {
        $oldValue = null;
        }

        update($db, "comment", $newValue, $oldValue, $index, "text");
    }

    switch ($changedElement) {
        case "score":
            changeScore($db, $score, $actualEntry["score"], $index);
            break;
        case "progress":
            changeProgress($db, $progress, $actualEntry["progress"], $index, $progress_length);
            break;
        case "progress_length":
            changeProgressLength($db, $progress_length, $actualEntry["progress_length"], $index, $progress);
            break;
        case "type":
            changeType($db, $type, $actualEntry["type"], $index);
            break;
        case "rewatch":
            changeRewatch($db, $rewatch, $actualEntry["rewatch"], $index);
            break;
        case "favorite":
            changeFavorite($db, $favorite, $actualEntry["favorite"], $index);
            break;
        case "comment":
            changeComment($db, $comment, $actualEntry["comment"], $index);
            break;
    }

    $entry = $db->getSpecificRowByIndex($index, false);
    $db->close();

    $twigVariables = [
        "entry" => $entry
    ];
    $twig->display("modal-ce-form.html", $twigVariables);
}

function error($title, $body) {
    global $twig;
    global $index;
    global $db;

    http_response_code(418);
    
    $twigVariables = [
        "htmx_error_title"   => $title,
        "htmx_error_body"    => $body,
        "entry"              => $db->getSpecificRowByIndex($index, false)
    ];
    $twig->display("modal-ce-form.html", $twigVariables);

    die();
}