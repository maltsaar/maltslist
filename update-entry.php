<?php

require_once "./config.php";

// variables
$timestamp = date("Y-m-d H:i:s");
$dataArray = [];

// post variables
$entry = $_POST['entry'];

$formScoreChange = $_POST['form-score-change'];
$formProgressSubtract = $_POST['form-progress-subtract'];
$formProgressAdd = $_POST['form-progress-add'];
$formRewatchAdd = $_POST['form-rewatch-add'];
$formIsCheckmark = $_POST['form-isCheckmark'];
$formFavoriteBool = $_POST['form-favorite-bool'];

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
	if (isset($entry)) {
		$currentEntryTitle = $dataArray[$entry-1][title];
		
		// check form-score-change
		if (isset($formScoreChange)) {
			$formScoreChangeNrArray = explode('-',$formScoreChange);
			$formScoreChangeResult = $formScoreChangeNrArray[1];
			
			$statement = "UPDATE list SET score=$formScoreChangeResult WHERE `index`=$entry";
			try {
				$db->exec($statement);
				$db->exec("DELETE FROM 'last-updated'");
				$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
				$db->close();
			} catch (Exception $e) {
				header("location:https://list.wavy.ws?error_title=Failed to update entry!&error_msg=Couldn't update $currentEntryTitle. Exception - $e!");
			}
			header("location:https://list.wavy.ws?regular_title=Entry updated!&regular_msg=$currentEntryTitle has been updated!");
		}
		
		// check form-progress-subtract
		if (isset($formProgressSubtract)) {
			
			$allowedMaximumProgress = $dataArray[$entry-1][progress_length];
			$currentProgress = $dataArray[$entry-1][progress];
			if ($currentProgress-$formProgressSubtract<0) {
				header("location:https://list.wavy.ws?error_title=Can't go below 0 in progress&error_msg=Really?&error_img=https://auk.wavy.ws/i/bdbse.png");
			} else {
				$formProgressSubtractResult = $currentProgress-$formProgressSubtract;
				$statement = "UPDATE list SET progress=$formProgressSubtractResult WHERE `index`=$entry";
				try {
					$db->exec($statement);
					$db->exec("DELETE FROM 'last-updated'");
					$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
					$db->close();
				} catch (Exception $e) {
					header("location:https://list.wavy.ws?error_title=Failed to change entry!&error_msg=Exception - $e!");
				}
				header("location:https://list.wavy.ws?regular_title=Entry updated!&regular_msg=$currentEntryTitle has been updated!");
			}
		}
		
		// check form-progress-add
		if (isset($formProgressAdd)) {
			
			$allowedMaximumProgress = $dataArray[$entry-1][progress_length];
			$currentProgress = $dataArray[$entry-1][progress];
			
			if ($currentProgress+$formProgressAdd>$allowedMaximumProgress) {
				header("location:https://list.wavy.ws?error_title=Failed to change entry!&error_title=Can't go past the limit specified in progress_length&error_msg=Really?&error_img=https://auk.wavy.ws/i/bdbse.png");
			} else {
				
				$formProgressAddResult = $currentProgress+$formProgressAdd;
				$statement = "UPDATE list SET progress=$formProgressAddResult WHERE `index`=$entry";
				try {
					$db->exec($statement);
					$db->exec("DELETE FROM 'last-updated'");
					$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
					$db->close();
				} catch (Exception $e) {
					header("location:https://list.wavy.ws?error_title=Failed to change entry!&error_msg=Exception - $e!");
				}
				header("location:https://list.wavy.ws?regular_title=Entry updated!&regular_msg=$currentEntryTitle has been updated!");
			}
		}
		
		// check form-rewatch-add
		
		if (isset($formRewatchAdd)) {
			
			$currentRewatch = $dataArray[$entry-1][rewatch];
			
			$statement = "UPDATE list SET rewatch=$currentRewatch+1 WHERE `index`=$entry";
			try {
				$db->exec($statement);
				$db->exec("DELETE FROM 'last-updated'");
				$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
				$db->close();
			} catch (Exception $e) {
				header("location:https://list.wavy.ws?error_title=Failed to change entry!&error_msg=Exception - $e!");
			}
			header("location:https://list.wavy.ws?regular_title=Entry updated!&regular_msg=$currentEntryTitle has been updated!");
		}
		
		// check form-favorite-bool
		if (isset($formIsCheckmark)) {
			if (isset($formFavoriteBool) && $formFavoriteBool === "on") {
				
				$statement = "UPDATE list SET favorite='on' WHERE `index`=$entry";
				try {
					$db->exec($statement);
					$db->exec("DELETE FROM 'last-updated'");
					$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
					$db->close();
				} catch (Exception $e) {
					header("location:https://list.wavy.ws?error_title=Failed to change entry!&error_msg=Exception - $e!");
				}
				header("location:https://list.wavy.ws?regular_title=Entry updated!&regular_msg=$currentEntryTitle has been updated!");
			} else {
			
				$statement = "UPDATE list SET favorite='off' WHERE `index`=$entry";
				try {
					$db->exec($statement);
					$db->exec("DELETE FROM 'last-updated'");
					$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
					$db->close();
				} catch (Exception $e) {
					header("location:https://list.wavy.ws?error_title=Failed to change entry!&error_msg=Exception - $e!");
				}
				header("location:https://list.wavy.ws?regular_title=Entry updated!&regular_msg=$currentEntryTitle has been updated!");
			}
		}
	} else {
		header('location: https://list.wavy.ws');
		exit;
	}
	
	
}

?>
