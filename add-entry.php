<?php

require_once "./config.php";

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

// check if db exists
if (file_exists("./db/$database")) {
    $db = new SQLite3("./db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
	$db->enableExceptions(true);
	
	// get current data
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($which)) {
		if ($which === "remove-entry") {
			if (isset($index)) {
				$currentIndexTitle = $dataArray[$index-1][title];
				
				if (isset($dataArray[$index-1][index])) {
					if ($dataArray[$index-1]["is_deleted"] !== "yes") {
						$statement = "UPDATE 'list' SET is_deleted='yes' WHERE `index`=$index";
						try {
							$db->exec($statement);
							$db->exec("DELETE FROM 'last-updated'");
							$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
							// close
							$db->close();
						} catch (Exception $e) {
							header("location:https://list.wavy.ws?error_title=Failed to remove entry!&error_msg=Failed to remove entry $index from the database -e $e");
						}
						header("location:https://list.wavy.ws?regular_title=Entry removed!&regular_msg=$currentIndexTitle removed from the list!");
					} else {
						header("location:https://list.wavy.ws?error_title=Failed to remove entry!&error_msg=Specified index has already been deleted!");
					}
				} else {
					header("location:https://list.wavy.ws?error_title=Failed to remove entry!&error_msg=Specified index doesn't exist!");
				}
			} else {
				header("location:https://list.wavy.ws?error_title=Failed to remove entry!&error_msg=Index hasn't been specified!");
			}
		} elseif ($which === "add-entry") {
			if (isset($title, $score, $progress, $progress_length, $rewatch, $favorite)) {
				if (!empty($title)) {
					if ($progress_length>=$progress) {
						$statement = "INSERT into 'list' (title, score, progress, progress_length, type, rewatch, favorite, comment)
						VALUES ('$title', $score, $progress, $progress_length, '$type', $rewatch, '$favorite', '$comment')";
						try {
							$db->exec($statement);
							$db->exec("DELETE FROM 'last-updated'");
							$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
							$db->close();
						} catch (Exception $e) {
							header("location:https://list.wavy.ws?error_title=Failed to add entry!&error_msg=Failed to add entry $index from the database - $e");
						}
						header("location:https://list.wavy.ws?regular_title=Entry added!&regular_msg=Title: $title added to the list!");
					} else {
						header("location:https://list.wavy.ws?error_title=Failed to add entry!&error_msg=Progress ($progress) is bigger than Total length ($progress_length)");
					}
				} else {
					header("location:https://list.wavy.ws?error_title=Failed to add entry!&error_msg=Title can't be empty!");
				}
			} else {
				header("location:https://list.wavy.ws?error_title=Missing required POST parameter!&error_msg=One of these parameters was not set: form-title, form-score, form-progress, form-progress-length, form-rewatch, form-favorite");
			}
		} else {
			header("location:https://list.wavy.ws?error_title=Invalid post parameter!&error_msg=form-which: $which");
		}
	}
}

?>
