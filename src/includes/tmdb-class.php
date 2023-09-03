<?php

require_once "../vendor/autoload.php";
require_once "../config.php";

class tmdb {
    public $title;
    public $year;
    public $type;

    function __construct($title, $year, $type) {
        $this->title = $title;
        $this->year  = $year;
        $this->type  = $type;
    }

    public function getTmdbData() {
        if ($this->type === "tv") {
            $title = $this->fixTvTitle($this->title, $this->type);
        }

        return $this->performApiRequest($this->title, $this->year, $this->type);
    }

    // Because the season is currently included in the title we need to remove it
    // Example: "Person of Interest S01" -> "Person of Interest"
    private function fixTvTitle($title, $type) {
        // Create array with values "S0-S99"
        $seasons = range(0,99);
        $seasons = preg_filter('/^/', ' S', $seasons);

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

    private function performApiRequest($title, $year, $type) {
        $client = new \GuzzleHttp\Client();

        $headers = [
            "headers" => [
                "Authorization" => "Bearer " . TMDB_API_KEY,
                "Accept" => "application/json"
            ]
        ];

        $queryParams = [
            "query"            => $title,
            "year"             => $year,
            "include_adult"    => TMDB_INCLUDE_ADULT,
            "page"             => "1"
        ];

        $builtQueryParams = http_build_query($queryParams);

        if ($type === "film") {
            $response = $client->request('GET', "https://api.themoviedb.org/3/search/movie?".$builtQueryParams, $headers);
        } else {
            $response = $client->request('GET', "https://api.themoviedb.org/3/search/tv?".$builtQueryParams, $headers);
        }

        if (!empty($response)) {
            $response = json_decode($response->getBody(), true);
            return $response;
        }
    }
}