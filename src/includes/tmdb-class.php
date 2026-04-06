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

    // https://api.themoviedb.org/3/configuration/languages
    // This includes non-standard codes:
    // xx = "No Language"
    // cn = "Cantonese"
    // mo = "Moldavian"
    private const ISO_639_1_CODES = [
        "bi",
        "cs",
        "ba",
        "ae",
        "av",
        "de",
        "mt",
        "om",
        "rm",
        "so",
        "ts",
        "vi",
        "gn",
        "ig",
        "it",
        "ki",
        "ku",
        "la",
        "ln",
        "lb",
        "ny",
        "pl",
        "si",
        "to",
        "az",
        "ce",
        "cu",
        "da",
        "hz",
        "ie",
        "rw",
        "mi",
        "no",
        "pi",
        "sk",
        "se",
        "sm",
        "uk",
        "en",
        "ay",
        "ca",
        "eo",
        "ha",
        "ho",
        "hu",
        "io",
        "ii",
        "kn",
        "kv",
        "li",
        "oj",
        "ru",
        "sr",
        "sv",
        "ty",
        "zu",
        "ka",
        "ch",
        "be",
        "br",
        "kw",
        "fi",
        "sh",
        "nn",
        "tt",
        "tg",
        "vo",
        "ps",
        "mk",
        "fr",
        "bm",
        "eu",
        "fj",
        "id",
        "mg",
        "na",
        "xx",
        "qu",
        "sq",
        "ti",
        "tw",
        "wa",
        "ab",
        "bs",
        "af",
        "an",
        "fy",
        "gu",
        "ik",
        "ja",
        "ko",
        "lg",
        "nl",
        "os",
        "el",
        "bn",
        "cr",
        "km",
        "lo",
        "nd",
        "ne",
        "sc",
        "sw",
        "tl",
        "ur",
        "ee",
        "aa",
        "co",
        "et",
        "is",
        "ks",
        "kr",
        "ky",
        "kj",
        "nr",
        "or",
        "wo",
        "za",
        "ar",
        "cv",
        "fo",
        "hr",
        "ms",
        "nb",
        "rn",
        "sn",
        "st",
        "tr",
        "am",
        "fa",
        "hy",
        "pa",
        "as",
        "ia",
        "lv",
        "lu",
        "mr",
        "mn",
        "pt",
        "th",
        "tk",
        "ve",
        "dv",
        "gv",
        "kl",
        "kk",
        "lt",
        "my",
        "sl",
        "sd",
        "cn",
        "hi",
        "cy",
        "ht",
        "iu",
        "jv",
        "mh",
        "sa",
        "ss",
        "te",
        "kg",
        "ml",
        "uz",
        "sg",
        "xh",
        "es",
        "su",
        "ug",
        "yi",
        "yo",
        "zh",
        "he",
        "bo",
        "ak",
        "mo",
        "ng",
        "dz",
        "ff",
        "gd",
        "ga",
        "gl",
        "nv",
        "oc",
        "ro",
        "ta",
        "tn",
        "bg",
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

    public static function getTmdbLanguages()
    {
        return self::ISO_639_1_CODES;
    }

    public function getTmdbData()
    {
        return $this->performApiRequest($this->title, $this->year, $this->type);
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
                $headers,
            );
        } else {
            $response = $client->request(
                "GET",
                "https://api.themoviedb.org/3/search/tv?" . $builtQueryParams,
                $headers,
            );
        }

        if (!empty($response)) {
            $response = json_decode($response->getBody(), true);

            // TMDB api returns multiple results in an array but we only want one
            // Sort by popularity to avoid the chances of getting a bad match
            if (
                !empty($response["results"]) &&
                is_array($response["results"])
            ) {
                usort($response["results"], function ($a, $b) {
                    return ($b["popularity"] ?? 0) <=> ($a["popularity"] ?? 0);
                });
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
            } else {
                // If no genre_ids are present in the API response set it to an empty array
                $response["genres"] = [];
            }

            return $response;
        } else {
            return false;
        }
    }
}
