<?php

//
// config
//

define("DATABASE", getenv("DATABASE"));
define("TMDB_API_KEY", getenv("TMDB_API_KEY"));
// This determines if the TMDB API will return results on adult content
define("TMDB_INCLUDE_ADULT", getenv("TMDB_INCLUDE_ADULT"));

//
// developer debug options
//

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define("TWIG_DEBUG", false);