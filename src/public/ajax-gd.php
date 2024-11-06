<?php

require_once "../includes/database-class.php";
require_once "../includes/bootstrap-twig.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(418);
    echo "I'm a teapot!";
    die();
}

// db arrays
$dataArray = [];
$dataArrayPlantowatch = [];
$dataArrayWatching = [];
$dataArrayCompleted = [];
$dataArrayFavorites = [];
$timestamp = [];

$db = new database();
$result = $db->getAllRows();

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $watchTypeSet = false;

    if (!empty($row["tmdb_genres"])) {
        $row["tmdb_genres"] = explode(", ", $row["tmdb_genres"]);
    }

    // title search
    // If search query isn't present skip
    if (!empty($_GET["search"])) {
        // If title field isn't present in search query skip and reiterate
        if (
            !str_contains(
                strtolower($row["title"]),
                strtolower($_GET["search"])
            )
        ) {
            continue;
        }
    }

    // genre filter
    // If genre filter query isn't present skip
    if (!empty($_GET["genre"])) {
        // if genre field in database is empty skip and reiterate
        if (empty($row["tmdb_genres"])) {
            continue;
        }

        // if queried genre is not present in database row skip and reiterate
        if (!in_array($_GET["genre"], $row["tmdb_genres"])) {
            continue;
        }
    }

    // plan to watch
    if ($watchTypeSet === false) {
        if ($row["progress"] === 0 && $row["favorite"] !== "on") {
            $watchTypeSet = true;
            $watchType = "plantowatch";
        }
    }

    // watching
    if ($watchTypeSet === false) {
        if (
            $row["progress"] !== $row["progress_length"] &&
            $row["favorite"] !== "on" &&
            $row["progress"] !== "0"
        ) {
            $watchTypeSet = true;
            $watchType = "watching";
        }
    }

    // completed
    if ($watchTypeSet === false) {
        if (
            $row["progress"] === $row["progress_length"] &&
            $row["favorite"] !== "on"
        ) {
            $watchTypeSet = true;
            $watchType = "completed";
        }
    }

    // favorites
    if ($watchTypeSet === false) {
        if ($row["favorite"] !== "off") {
            $watchTypeSet = true;
            $watchType = "favorites";
        }
    }

    switch ($watchType) {
        case "plantowatch":
            array_push($dataArrayPlantowatch, $row);
            break;
        case "watching":
            array_push($dataArrayWatching, $row);
            break;
        case "completed":
            array_push($dataArrayCompleted, $row);
            break;
        case "favorites":
            array_push($dataArrayFavorites, $row);
            break;
    }
}

$dataArray = [];

if (!empty($dataArrayWatching)) {
    $dataArray["Watching"] = $dataArrayWatching;
}

if (!empty($dataArrayPlantowatch)) {
    $dataArray["Plan to watch"] = $dataArrayPlantowatch;
}

if (!empty($dataArrayCompleted)) {
    $dataArray["Completed"] = $dataArrayCompleted;
}

if (!empty($dataArrayFavorites)) {
    $dataArray["Favorites"] = $dataArrayFavorites;
}

// close
$db->close();

$twigVariables = [
    "dataArray" => $dataArray,
];
$twig->display("list.html", $twigVariables);
