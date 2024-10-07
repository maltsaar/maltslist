<?php

require_once "../vendor/autoload.php";
require_once "../config.php";
require_once "../includes/tmdb-class.php";
require_once "../includes/database-class.php";
require_once "../includes/bootstrap-twig.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(418);
    echo "I'm a teapot!";
    die();
}

// form POST variables
$title = (string) trim($_POST["form-title"]);
$year = (int) trim($_POST["form-year"]);
$season = (int) trim($_POST["form-season"]);
$score = (int) trim($_POST["form-score"]);
$progress = (int) trim($_POST["form-progress"]);
$progress_length = (int) trim($_POST["form-progress-length"]);
$type = (string) trim($_POST["form-type"]);
$rewatch = (int) trim($_POST["form-rewatch"]);
$favorite = (string) trim($_POST["form-favorite"]);
$comment = (string) trim($_POST["form-comment"]);

//
// check user input
//

if (!isset($title, $score, $progress, $progress_length, $rewatch, $favorite)) {
    error(
        "Missing required POST parameter!",
        "One of these parameters was not set: form-title, form-score, form-progress, form-progress-length, form-rewatch, form-favorite"
    );
}

if (empty($title) && !is_numeric($title)) {
    error("Failed to add entry!", "Title can't be empty!");
}

if (empty($year)) {
    error("Failed to add entry!", "Year can't be empty!");
}

if ($progress_length < $progress) {
    error(
        "Failed to add entry!",
        "Progress ($progress) is bigger than Total length ($progress_length)"
    );
}

if (!empty($season)) {
    if ($season < 0) {
        error("Failed to add entry!", "Season can't be smaller than 0");
    }

    if ($season > 99) {
        error("Failed to add entry!", "Season can't be bigger than 99");
    }

    if ($season <= 9) {
        $season = "S0${season}";
    } elseif ($season === 0) {
        $season = null;
    } else {
        $season = "S${season}";
    }
} else {
    $season = null;
}

//
// TMDB API
//

$tmdbQuery = new tmdb($title, $year, $type);
try {
    $tmdbData = $tmdbQuery->getTmdbData();
} catch (Exception $e) {
    $json = substr($e->getMessage(), strpos($e->getMessage(), "{"));
    $array = json_decode($msg, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        error("Failed to add entry!", $arr["status_message"]);
    } else {
        error("Failed to add entry!", "An exception occured in tmdb-class.php");
    }
}

if (!isset($tmdbData["results"][0])) {
    error("Failed to add entry!", "TMDB API was unable to find anything");
}

// we only need the first result
$tmdbData = $tmdbData["results"][0];

$id = $tmdbData["id"];
$description = $tmdbData["overview"];
$cover = $tmdbData["poster_path"];
$banner = $tmdbData["backdrop_path"];

//
// db
//

$db = new database();

// check for duplicate entries
$duplicateCheck = $db->getSpecificRowByTitle($title, false);
if (!empty($duplicateCheck)) {
    if (empty($season)) {
        error("Failed to add entry!", "Entry with this title already exists.");
    } else {
        if ($season === $duplicateCheck["season"]) {
            error(
                "Failed to add entry!",
                "Entry with this title and season already exists."
            );
        }
    }
}

try {
    $db->addNewListEntry(
        $title,
        $year,
        $season,
        $score,
        $progress,
        $progress_length,
        $type,
        $rewatch,
        $favorite,
        $comment,
        $id,
        $cover,
        $banner,
        $description
    );
} catch (Exception $e) {
    error("Caught database exception", $e->getMessage());
}

$entry = $db->getSpecificRowByTitle($title, true);
$db->close();

$index = $entry["index"];
header("entry_index: ${index}");

function error($title, $body)
{
    global $twig;

    http_response_code(418);

    $twigVariables = [
        "htmx_error_title" => $title,
        "htmx_error_body" => $body,
    ];
    $twig->display("modal-ae.html", $twigVariables);

    die();
}
