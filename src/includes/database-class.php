<?php

require_once "../vendor/autoload.php";
require_once "../config.php";

class database
{
    public $db;

    function __construct()
    {
        $this->db = $this->connect();
    }

    private function connect()
    {
        if (!file_exists("../db/" . DATABASE)) {
            throw new Exception("Database file doesn't exist");
        }

        $db = new SQLite3("../db/" . DATABASE, SQLITE3_OPEN_READWRITE);
        $db->enableExceptions(true);

        return $db;
    }

    public function close()
    {
        $this->db->close();
    }

    public function getSpecificRowByTitle($title, $escape)
    {
        $statement = $this->db->prepare("
    		SELECT * FROM 'list' WHERE title IS :title
    	");

        $statement->bindValue(":title", $title, SQLITE3_TEXT);

        $result = $statement->execute();

        $dataArray = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($escape === true) {
                foreach ($row as $key => $value) {
                    if (!empty($value)) {
                        $row[$key] = htmlspecialchars($value);
                    }
                }
            }
            array_push($dataArray, $row);
        }

        // FIXME
        if (!empty($dataArray)) {
            return $dataArray[0];
        }
    }

    public function getSpecificRowByIndex($index, $escape)
    {
        $statement = $this->db->prepare("
    		SELECT * FROM 'list' WHERE `index` IS :index
    	");

        $statement->bindValue(":index", $index, SQLITE3_TEXT);

        $result = $statement->execute();

        $dataArray = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($escape === true) {
                foreach ($row as $key => $value) {
                    if (!empty($value)) {
                        $row[$key] = htmlspecialchars($value);
                    }
                }
            }
            array_push($dataArray, $row);
        }

        // FIXME
        if (!empty($dataArray)) {
            return $dataArray[0];
        }
    }

    public function getAllRows()
    {
        $result = $this->db->prepare("SELECT * FROM 'list'")->execute();
        return $result;
    }

    /* FIXME: Remove redundant $oldValue parameter */
    public function updateSpecificColumn(
        $column,
        $newValue,
        $oldValue,
        $index,
        $sqliteType
    ) {
        // https://stackoverflow.com/a/182353
        // !!! DON'T DURGASIR THE SQL STATEMENT !!!
        // TLDR: We can't bindParam or bindValue a column or a table name so we have to whitelist instead
        $validColumns = [
            "score",
            "progress",
            "progress_length",
            "type",
            "rewatch",
            "favorite",
            "comment",
        ];

        if (!in_array($column, $validColumns)) {
            throw new Exception("Failed database column whitelist check");
        }

        $statement = $this->db->prepare("
    		UPDATE 'list' SET $column=:newValue WHERE `index` IS :index
    	");

        $statement->bindValue(":index", $index, SQLITE3_INTEGER);

        if ($sqliteType === "integer") {
            $statement->bindValue(":newValue", $newValue, SQLITE3_INTEGER);

            $statement->bindValue(":oldValue", $oldValue, SQLITE3_INTEGER);
        }

        if ($sqliteType === "text") {
            $statement->bindValue(":newValue", $newValue, SQLITE3_TEXT);

            $statement->bindValue(":oldValue", $oldValue, SQLITE3_TEXT);
        }

        $statement->execute();
        $this->updateTimestamp();
    }

    public function addNewListEntry(
        $title,
        $year,
        $season,
        $score,
        $progress,
        $progress_length,
        $type,
        $rewatch,
        $favorite,
        $comment,
        $id,
        $cover,
        $banner,
        $description
    ) {
        // prepared statement
        $statement = $this->db->prepare("
		    INSERT into 'list' (
		        title,
		        year,
				season,
		        score,
		        progress,
		        progress_length,
		        type,
		        rewatch,
		        favorite,
		        comment,
		        tmdb_id,
		        tmdb_cover,
		        tmdb_banner,
		        tmdb_description
		    )
		    VALUES (
		        :title,
		        :year,
				:season,
		        :score,
		        :progress,
		        :progress_length,
		        :type,
		        :rewatch,
		        :favorite,
		        :comment,
		        :id,
		        :cover,
		        :banner,
		        :description
            )
		");

        // bind values
        $statement->bindValue(":title", $title, SQLITE3_TEXT);
        $statement->bindValue(":year", $year, SQLITE3_INTEGER);

        if (!empty($season)) {
            $statement->bindValue(":season", $season, SQLITE3_TEXT);
        } else {
            $statement->bindValue(":season", null, SQLITE3_TEXT);
        }

        $statement->bindValue(":score", $score, SQLITE3_INTEGER);
        $statement->bindValue(":progress", $progress, SQLITE3_INTEGER);
        $statement->bindValue(
            ":progress_length",
            $progress_length,
            SQLITE3_INTEGER
        );
        $statement->bindValue(":type", $type, SQLITE3_TEXT);
        $statement->bindValue(":rewatch", $rewatch, SQLITE3_INTEGER);
        $statement->bindValue(":favorite", $favorite, SQLITE3_TEXT);

        if (!empty($comment)) {
            $statement->bindValue(":comment", $comment, SQLITE3_TEXT);
        } else {
            $statement->bindValue(":comment", null, SQLITE3_NULL);
        }

        $statement->bindValue(":id", $id, SQLITE3_INTEGER);
        $statement->bindValue(":cover", $cover, SQLITE3_TEXT);
        $statement->bindValue(":banner", $banner, SQLITE3_TEXT);
        $statement->bindValue(":description", $description, SQLITE3_TEXT);

        $statement->execute();
        $this->updateTimestamp();
    }

    // FIXME: These functions below are ugly
    public function updateTimestamp()
    {
        $this->db->exec("DELETE FROM 'last-updated'");

        $timestamp = date("d.m.Y H:i:s");

        $this->db->exec(
            "INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')"
        );
    }

    public function getTimestamp()
    {
        $result = $this->db->query("SELECT * from 'last-updated'");

        $timestamp = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            array_push($timestamp, $row);
        }

        return $timestamp[0]["timestamp"];
    }
}
