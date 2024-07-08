
<?php

require_once "../config.php";

// Load our autoloader
require_once "../vendor/autoload.php";

// Specify our Twig templates location
$loader = new \Twig\Loader\FilesystemLoader("../templates");

 // Instantiate our Twig
$twig = new \Twig\Environment($loader, [
    'debug' => TWIG_DEBUG,
    'use_yield' => true
]);
$twig->addExtension(new \Twig\Extension\DebugExtension());
