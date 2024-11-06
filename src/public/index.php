<?php

require_once "../config.php";
require_once "../includes/database-class.php";
require_once "../includes/tmdb-class.php";
require_once "../includes/bootstrap-twig.php";

try {
    $db = new database();
    $timestamp = $db->getTimestamp();
    $db->close();

    $twigVariables = [
        "caughtException" => false,
        "timestamp" => $timestamp,
    ];
} catch (Exception $e) {
    $twigVariables = [
        "caughtException" => true,
        "caughtExceptionMessage" => $e,
    ];
}

// Call static method to get genres array because using a constructor is not possible
$twigVariables["genres"] = tmdb::getTmdbGenres();
$twig->display("main.html", $twigVariables);
