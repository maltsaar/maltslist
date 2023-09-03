<?php

require_once "../includes/database-class.php";
require_once "../includes/bootstrap-twig.php";

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(418);
    echo "I'm a teapot!";
    die();
}

$twigVariables = [
    "index"              => $_GET["index"] ?? null
];
$twig->display("modal-ce.html", $twigVariables);