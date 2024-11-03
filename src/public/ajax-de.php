<?php

require_once "../vendor/autoload.php";
require_once "../config.php";
require_once "../includes/database-class.php";
require_once "../includes/bootstrap-twig.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(418);
    echo "I'm a teapot!";
    die();
}

if (!empty($_GET["id"])) {
    $index = (int) trim($_GET["id"]);
} else {
    http_response_code(418);
    echo "I'm a teapot!";
    die();
}

$db = new database();

// Check if entry actually exists
if (empty($db->getSpecificRowByIndex($index, false))) {
    error("Failed to delete entry!", "Entry with this index doesn't exist.");
}

// delete entry
try {
    $entry = $db->deleteListEntry($index);
} catch (Exception $e) {
    error("Caught database exception", $e->getMessage());
}

$db->close();

$twig->display("entry-deleted.html");

function error($title, $body)
{
    global $twig;

    http_response_code(418);
    // This hack is required because htmx won't render the response if there's an error code
    // This isn't required in the other error functions because the hx-target-* attribute is used
    header("HX-Retarget: #lists-container");

    $twigVariables = [
        "htmx_error_title" => $title,
        "htmx_error_body" => $body,
    ];
    $twig->display("entry-deleted.html", $twigVariables);

    die();
}
