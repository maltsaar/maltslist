<?php

require_once "./config.php";

try {
	$db = new SQLite3("./db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
	
	$result = $db->query("SELECT * from 'list'");
	$dataArray = [];
	while ($row = $result->fetchArray(1))
	{
		array_push($dataArray, $row);
	}
} catch (Exception $e) {
    $errorType = "db";
}


foreach ($_POST as $key => $value) {
    echo "Field ".htmlspecialchars($key)." is ".htmlspecialchars($value)."<br>";
}

// variables

$entry = $_POST['entry'];

$formScoreChange = $_POST['form-score-change'];
$formProgressSubtract = $_POST['form-progress-subtract'];
$formProgressAdd = $_POST['form-progress-add'];
$formRewatchAdd = $_POST['form-rewatch-add'];
$formIsCheckmark = $_POST['form-isCheckmark'];
$formFavoriteBool = $_POST['form-favorite-bool'];

$timestamp = date("Y-m-d H:i:s");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($entry)) {
		// check form-score-change
		if (isset($formScoreChange)) {
			$formScoreChangeNrArray = explode('-',$formScoreChange);
			$formScoreChangeResult = $formScoreChangeNrArray[1];
			
			$statement = "UPDATE list SET score=$formScoreChangeResult WHERE `index`=$entry";
			try {
				$db = new SQLite3("./db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
				$db->enableExceptions(true);
				$db->exec($statement);
				$db->exec("DELETE FROM 'last-updated'");
				$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
				$db->close();
			} catch (Exception $e) {
				echo $e;
			}
			
			echo "DB Updated! Statement: $statement";
		}
		
		// check form-progress-subtract
		if (isset($formProgressSubtract)) {
			
			$allowedMaximumProgress = $dataArray[$entry-1][progress_length];
			$currentProgress = $dataArray[$entry-1][progress];
			if ($currentProgress-$formProgressSubtract<0) {
				echo "Nigga: How can you minus 1 a 1 from a 0 what is you doing smhsmh";
			} else {
				$formProgressSubtractResult = $currentProgress-$formProgressSubtract;
				$statement = "UPDATE list SET progress=$formProgressSubtractResult WHERE `index`=$entry";
				try {
					$db = new SQLite3("./db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
					$db->enableExceptions(true);
					$db->exec($statement);
					$db->exec("DELETE FROM 'last-updated'");
					$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
					$db->close();
				} catch (Exception $e) {
					echo $e;
				}
				
				echo "DB Updated! Statement: $statement";
				}
		}
		
		// check form-progress-add
		if (isset($formProgressAdd)) {
			
			$allowedMaximumProgress = $dataArray[$entry-1][progress_length];
			$currentProgress = $dataArray[$entry-1][progress];
			
			if ($currentProgress+$formProgressAdd>$allowedMaximumProgress) {
				echo "Nigga: How can you add a 1 to something that is already full smhsmh";
			} else {
				
				$formProgressAddResult = $currentProgress+$formProgressAdd;
				$statement = "UPDATE list SET progress=$formProgressAddResult WHERE `index`=$entry";
				try {
					$db = new SQLite3("./db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
					$db->enableExceptions(true);
					$db->exec($statement);
					$db->exec("DELETE FROM 'last-updated'");
					$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
					$db->close();
				} catch (Exception $e) {
					echo $e;
				}
				
				echo "DB Updated! Statement: $statement";
				
			}
			
		}
		
		// check form-rewatch-add
		
		if (isset($formRewatchAdd)) {
			
			$currentRewatch = $dataArray[$entry-1][rewatch];
			
			$statement = "UPDATE list SET rewatch=$currentRewatch+1 WHERE `index`=$entry";
			try {
				$db = new SQLite3("./db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
				$db->enableExceptions(true);
				$db->exec($statement);
				$db->exec("DELETE FROM 'last-updated'");
				$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
				$db->close();
			} catch (Exception $e) {
				echo $e;
			}
			
			echo "DB Updated! Statement: $statement";
		}
		
		// check form-favorite-bool
		if (isset($formIsCheckmark)) {
			if (isset($formFavoriteBool) && $formFavoriteBool === "on") {
				
				$statement = "UPDATE list SET favorite='on' WHERE `index`=$entry";
				try {
					$db = new SQLite3("./db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
					$db->enableExceptions(true);
					$db->exec($statement);
					$db->exec("DELETE FROM 'last-updated'");
					$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
					$db->close();
				} catch (Exception $e) {
					echo $e;
				}
				
				echo "DB Updated! Statement: $statement";
			} else {
			
				$statement = "UPDATE list SET favorite='off' WHERE `index`=$entry";
				try {
					$db = new SQLite3("./db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
					$db->enableExceptions(true);
					$db->exec($statement);
					$db->exec("DELETE FROM 'last-updated'");
					$db->exec("INSERT INTO 'last-updated' (timestamp) VALUES ('$timestamp')");
					$db->close();
				} catch (Exception $e) {
					echo $e;
				}
				
				echo "DB Updated! Statement: $statement";
			
			}
		}

		header('location: https://list.wavy.ws');
	} else {
		header('location: https://list.wavy.ws');
		exit;
	}
	
	
}


?>
