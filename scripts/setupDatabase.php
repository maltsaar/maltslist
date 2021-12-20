<?php

require_once("../config.php");

echo "Checking if database already exists...\n";
if (file_exists("../db/$database")) {
    die("  Database already exists!\n");
}

echo "  Database doesn't exist.\nCreating database \"" . $database . "\".\n";
if (!file_exists("../db")) {
	mkdir("../db");
}
$db = new SQLite3("../db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);

echo "Creating tables...";

$db->query('CREATE TABLE IF NOT EXISTS "list" (
    "index" INTEGER PRIMARY KEY AUTOINCREMENT,
    "title" VARCHAR,
    "score" INTEGER,
    "progress" INTEGER,
    "progress_length" INTEGER,
    "type" VARCHAR,
    "rewatch" INTEGER,
    "favorite" VARCHAR,
    "comment" VARCHAR,
    "status" VARCHAR
)');
$db->query('CREATE TABLE IF NOT EXISTS "last-updated" (
    "timestamp" VARCHAR
)');

echo "Done! ...You can now use the serverlist.\n";
