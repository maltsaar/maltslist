<?php

// change this
$database = "maltslist.sqlite3"; // name of the database file in the db/ directory
$siteUrl = "https://list.wavy.ws";
$ssoEnabled = true; // this determines if the logout button gets displayed
$ssoUrlLogout = "https://sso.wavy.ws/logout"; // this is used exclusively for the logout button

// debugging
ini_set ('display_errors', 1);
ini_set ('display_startup_errors', 1);
error_reporting (E_ALL);