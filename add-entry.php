<?php

require_once "./vendor/autoload.php";
require_once "./config.php";

// configure logger
Logger::configure("config.php");
$logger = Logger::getLogger('maltslist add-entry');

// variables
$timestamp = date("Y-m-d H:i:s");
$dataArray = [];

// post variables
$which = $_POST["form-which"];

$index = $_POST["form-index"];
$title = $_POST["form-title"];
$score = $_POST["form-score"];
$progress = $_POST["form-progress"];
$progress_length = $_POST["form-progress-length"];
$type = $_POST["form-type"];
$rewatch = $_POST["form-rewatch"];
$favorite = $_POST["form-favorite"];
$comment = $_POST["form-comment"];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	
	$logger->info("add-entry.php POST request received");

	// check if db exists
	if (file_exists("./db/$database")) {
		$db = new SQLite3("./db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
		$db->enableExceptions(true);
		
		// get current data
		$logger->info("Trying to query database for current list data");
		try {
			$result = $db->query("SELECT * from 'list'");
			
			while ($row = $result->fetchArray(1))
			{
				array_push($dataArray, $row);
			}
		} catch (Exception $e) {
			header("location:https://list.wavy.ws?error_title=db putsis!&error_msg=$e");
		}
	} else {
		header("location:https://list.wavy.ws?error_title=db putsis!&error_msg=File doesn't exist. Please run setupDatabase.php");
	}
	$logger->info("Successfully queried database for current list data");

	if (isset($which)) {
		$logger->info("Trying to check for add-entry or remove-entry");
		if ($which === "remove-entry") {
			$logger->info("Get remove-entry");
			if (isset($index)) {
				$currentIndexTitle = $dataArray[$index-1][title];
				
				if (isset($dataArray[$index-1][index])) {
					if ($dataArray[$index-1]["is_deleted"] !== "yes") {
						$logger->info("Trying to SET is_deleted flag for index $index");
						$statement = "UPDATE 'list' SET is_deleted='yes' WHERE `index`=$index";
						try {
							$db->exec($statement);
							$db->exec("DELETE FROM 'last-updated'");
							$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
							// close
							$db->close();
						} catch (Exception $e) {
							$exceptionMessage = $e->getMessage();
							$logger->FATAL("Failed to SET is_deleted flag for index $index due to exception: $exceptionMessage");
							header("location:https://list.wavy.ws?error_title=Failed to remove entry!&error_msg=Failed to remove entry $index from the database due to exception: $exceptionMessage");
							exit;
						}
						$logger->info("Successfully SET is_deleted flag for index $index");
						header("location:https://list.wavy.ws?regular_title=Entry removed!&regular_msg=$currentIndexTitle removed from the list!");
					} else {
						$logger->FATAL("Failed to SET is_deleted flag for index $index because the flag is already set");
						header("location:https://list.wavy.ws?error_title=Failed to remove entry!&error_msg=Specified index has already been deleted!");
					}
				} else {
					$logger->FATAL("Failed to SET is_deleted flag for index $index because the specified index doesn't exist");
					header("location:https://list.wavy.ws?error_title=Failed to remove entry!&error_msg=Specified index doesn't exist!");
				}
			} else {
				$logger->FATAL("Failed to SET is_deleted flag for index $index because the index hasn't been specified");
				header("location:https://list.wavy.ws?error_title=Failed to remove entry!&error_msg=Index hasn't been specified!");
			}
		} elseif ($which === "add-entry") {
			$logger->info("Got add-entry");
			if (isset($title, $score, $progress, $progress_length, $rewatch, $favorite)) {
				if (!empty($title)) {
					if ($progress_length>=$progress) {
						$statement = "INSERT into 'list' (title, score, progress, progress_length, type, rewatch, favorite, comment)
						VALUES ('$title', $score, $progress, $progress_length, '$type', $rewatch, '$favorite', '$comment')";
						$logger->info("Trying to INSERT new data to database");
						try {
							$db->exec($statement);
							$db->exec("DELETE FROM 'last-updated'");
							$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
							$db->close();
						} catch (Exception $e) {
							$exceptionMessage = $e->getMessage();
							$logger->FATAL("Failed to add new entry to database due to exception: $exceptionMessage");
							header("location:https://list.wavy.ws?error_title=Failed to add entry!&error_msg=Failed to add new entry to database due to exception: $exceptionMessage");
							exit;
						}
						$logger->info("Successfully added new entry: title - \"$title\" to database");
						header("location:https://list.wavy.ws?regular_title=Entry added!&regular_msg=Title: $title added to the list!");
					} else {
						$logger->FATAL("Failed add-entry check because progress is bigger than progress_length");
						header("location:https://list.wavy.ws?error_title=Failed to add entry!&error_msg=Progress ($progress) is bigger than Total length ($progress_length)");
					}
				} else {
					$logger->FATAL("Failed add-entry check because title can't be empty");
					header("location:https://list.wavy.ws?error_title=Failed to add entry!&error_msg=Title can't be empty!");
				}
			} else {
				$logger->FATAL("Failed add-entry check because one of the required POST parameters is missing");
				header("location:https://list.wavy.ws?error_title=Missing required POST parameter!&error_msg=One of these parameters was not set: form-title, form-score, form-progress, form-progress-length, form-rewatch, form-favorite");
			}
		} else {
			$logger->FATAL("Got invalid POST parameter");
			header("location:https://list.wavy.ws?error_title=Invalid post parameter!&error_msg=form-which: $which");
		}
	}
}

?>
