<?php

require_once "../vendor/autoload.php";
require_once "../config.php";

class tmdb
{
    public $title;
    public $year;
    public $type;

    private const GENRES = [
        // Movie and TV genres
        12 => "Adventure",
        14 => "Fantasy",
        16 => "Animation",
        18 => "Drama",
        27 => "Horror",
        28 => "Action",
        35 => "Comedy",
        36 => "History",
        37 => "Western",
        53 => "Thriller",
        80 => "Crime",
        99 => "Documentary",
        878 => "Science Fiction",
        9648 => "Mystery",
        10402 => "Music",
        10749 => "Romance",
        10751 => "Family",
        10752 => "War",
        10770 => "TV Movie",
        // Exclusively TV genres
        10759 => "Action & Adventure",
        10762 => "Kids",
        10763 => "News",
        10764 => "Reality",
        10765 => "Sci-Fi & Fantasy",
        10766 => "Soap",
        10767 => "Talk",
        10768 => "War & Politics",
    ];

    function __construct($title, $year, $type)
    {
        $this->title = $title;
        $this->year = $year;
        $this->type = $type;
    }

    public static function getTmdbGenres()
    {
        return self::GENRES;
    }

    public function getTmdbData()
    {
        if ($this->type === "tv") {
            $title = $this->fixTvTitle($this->title, $this->type);
        }

        return $this->performApiRequest($this->title, $this->year, $this->type);
    }

    // Because the season is currently included in the title we need to remove it
    // Example: "Person of Interest S01" -> "Person of Interest"
    private function fixTvTitle($title, $type)
    {
        // Create array with values "S0-S99"
        $seasons = range(0, 99);
        $seasons = preg_filter("/^/", " S", $seasons);

        // Instead of S0-S9 have S00-S09
        foreach ($seasons as $key => $value) {
            if ($key < 10) {
                $fixedValue = str_replace("S" . $key, "S0" . $key, $value);
                $seasons[$key] = $fixedValue;
            }
        }

        if ($type === "film") {
            return $title;
        } else {
            $title = str_replace($seasons, "", $title);
            return $title;
        }
    }

    private function performApiRequest($title, $year, $type)
    {
        $client = new \GuzzleHttp\Client();

        $headers = [
            "headers" => [
                "Authorization" => "Bearer " . TMDB_API_KEY,
                "Accept" => "application/json",
            ],
        ];

        $queryParams = [
            "query" => $title,
            "year" => $year,
            "include_adult" => TMDB_INCLUDE_ADULT,
            "page" => "1",
        ];

        $builtQueryParams = http_build_query($queryParams);

        if ($type === "film") {
            $response = $client->request(
                "GET",
                "https://api.themoviedb.org/3/search/movie?" .
                    $builtQueryParams,
                $headers
            );
        } else {
            $response = $client->request(
                "GET",
                "https://api.themoviedb.org/3/search/tv?" . $builtQueryParams,
                $headers
            );
        }

        if (!empty($response)) {
            $response = json_decode($response->getBody(), true);
            if (isset($response["results"][0])) {
                // we only need the first result
                $response = $response["results"][0];
            } else {
                return false;
            }

            // append an array of strings containing the genres to the response
            if (!empty($response["genre_ids"])) {
                $genres = [];
                foreach ($response["genre_ids"] as $genreId) {
                    $genres[] = self::GENRES[$genreId];
                }
                $response["genres"] = $genres;
            }

            return $response;
        } else {
            return false;
        }
    }
}
