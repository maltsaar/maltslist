<?php

require_once "../includes/tmdb-class.php";

echo "This script will update all TMDB metadata (tmdb_cover, tmdb_banner, tmdb_description, tmdb_genres, tmdb_original_language) in the database.\n";

$db = new SQLite3("../db/maltslist.sqlite3", SQLITE3_OPEN_READWRITE);
$db->enableExceptions(true);
$result = $db->query("SELECT * FROM LIST");

$i = 0;

while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $i = ++$i;

    $client = new \GuzzleHttp\Client();

    $headers = [
        "headers" => [
            "Authorization" =>
                "Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiI2MjYxNDM5MTZlODAzOGE1ZDU1MTU2YjU3ZjM2ZWM3YyIsInN1YiI6IjY0YTkzZWE0OWM5N2JkMDBjNWY3ODgxNiIsInNjb3BlcyI6WyJhcGlfcmVhZCJdLCJ2ZXJzaW9uIjoxfQ.oKTZCn3V7v9edfVzqMAxahQv4JzESxMLAIQ9VyHlcpk",
            "Accept" => "application/json",
        ],
    ];

    $queryParams = [
        "include_adult" => true,
        "page" => "1",
    ];
    $builtQueryParams = http_build_query($queryParams);

    if ($row["type"] === "film") {
        $response = $client->request(
            "GET",
            "https://api.themoviedb.org/3/movie/{$row["tmdb_id"]}?" .
                $builtQueryParams,
            $headers,
        );
    } else {
        $response = $client->request(
            "GET",
            "https://api.themoviedb.org/3/tv/{$row["tmdb_id"]}?" .
                $builtQueryParams,
            $headers,
        );
    }

    if ($i !== 1) {
        echo "\n";
    }

    echo "\n{$i} - {$row["title"]}";

    echo "\nQuerying TMDB API...";
    try {
        $response = json_decode($response->getBody(), true);

        echo " OK";
    } catch (Exception $e) {
        echo " FAIL";
        echo "\n$e";
    }

    $tmdbId = $row["tmdb_id"];

    echo "\nUpdating tmdb_cover...";
    try {
        $cover = $response["poster_path"];
        $statement = $db->prepare("
            UPDATE list SET tmdb_cover = :cover WHERE tmdb_id = :tmdbId
        ");
        $statement->bindValue(":cover", $cover, SQLITE3_TEXT);
        $statement->bindValue(":tmdbId", $tmdbId, SQLITE3_INTEGER);
        $statement->execute();

        echo " OK";
    } catch (Exception $e) {
        echo " FAIL";
        echo "\n$e";
    }

    echo "\nUpdating tmdb_banner...";
    try {
        $banner = $response["backdrop_path"];
        $statement = $db->prepare("
            UPDATE list SET tmdb_banner = :banner WHERE tmdb_id = :tmdbId
        ");
        $statement->bindValue(":banner", $banner, SQLITE3_TEXT);
        $statement->bindValue(":tmdbId", $tmdbId, SQLITE3_INTEGER);
        $statement->execute();

        echo " OK";
    } catch (Exception $e) {
        echo " FAIL";
        echo "\n$e";
    }

    echo "\nUpdating tmdb_description...";
    try {
        $description = $response["overview"];
        $statement = $db->prepare("
            UPDATE list SET tmdb_description = :description WHERE tmdb_id = :tmdbId
        ");
        $statement->bindValue(":description", $description, SQLITE3_TEXT);
        $statement->bindValue(":tmdbId", $tmdbId, SQLITE3_INTEGER);
        $statement->execute();

        echo " OK";
    } catch (Exception $e) {
        echo " FAIL";
        echo "\n$e";
    }

    echo "\nUpdating tmdb_genres...";
    try {
        $genres = [];
        foreach ($response["genres"] as $genre) {
            $genres[] = $genre["name"];
        }
        $genre = implode(", ", $genres);

        $statement = $db->prepare("
            UPDATE list SET tmdb_genres = :genre WHERE tmdb_id = :tmdbId
        ");
        $statement->bindValue(":genre", $genre, SQLITE3_TEXT);
        $statement->bindValue(":tmdbId", $tmdbId, SQLITE3_INTEGER);
        $statement->execute();

        echo " OK";
    } catch (Exception $e) {
        echo " FAIL";
        echo "\n$e";
    }

    echo "\nUpdating tmdb_original_language...";
    try {
        $original_language = $response["original_language"];
        $statement = $db->prepare("
            UPDATE list SET tmdb_original_language = :original_language WHERE tmdb_id = :tmdbId
        ");
        $statement->bindValue(
            ":original_language",
            $original_language,
            SQLITE3_TEXT,
        );
        $statement->bindValue(":tmdbId", $tmdbId, SQLITE3_INTEGER);
        $statement->execute();

        echo " OK";
    } catch (Exception $e) {
        echo " FAIL";
        echo "\n$e";
    }

    usleep(250000);
}

echo "\n\nDone.\n";
