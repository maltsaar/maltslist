<html>
	<body>
		<h3>Add a new entry!</h3>
		<form action="" method="post">
			<label>Title:</label>
			<input name="form-title" type="text">
			<br>
			<label>Score:</label>
			<select name="form-score">
				<option value="5">5/5</option>
                                <option value="4">4/5</option>
                                <option value="3">3/5</option>
                                <option value="2">2/5</option>
                                <option value="1">1/5</option>
			</select>
			<br>
                        <label>Total episodes:</label>
                        <input name="form-progress-length" type="number">
			<br>
			<label>Progress:</label>
			<input name="form-progress" type="number">
			<br>
			<label>Type:</label>
                        <select name="form-type">
                                <option value="tv">TV</option>
                                <option value="film">Film</option>
                        </select>
			<br>
			<label>Rewatch count:</label>
			<input name="form-rewatch" type="number">
			<br>
			<label>Include in faovorites?:</label>
			<input type="checkbox" name="form-favorite">
			<br>
			<label>Comment:</label>
			<input type="text" name="form-comment">
			<br>
			<button type="submit">Submit</button>
		</form>
		<h3>Log:</h3>
	</body>
</html>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

require_once "./config.php";

$title = $_POST["form-title"];
$score = $_POST["form-score"];
$progress = $_POST["form-progress"];
$progress_length = $_POST["form-progress-length"];
$type = $_POST["form-type"];
$rewatch = $_POST["form-rewatch"];
$favorite = $_POST["form-favorite"];
$comment = $_POST["form-comment"];
$status = "";
/*
// no changing

echo date("H:i:s") . " - Trying to connect to database \"$database\"";
if (file_exists("./db/$database")) {
    $db = new SQLite3("./db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    echo " Success!\n";
} else {
    die(" Fail! (Please run setupDatabase.php)\n");
}

$statement = "INSERT into 'list' (title, score, progress, progress_length, type, rewatch, favorite, comment, status)
    VALUES ('$title', $score, $progress, $progress_length, '$type', $rewatch, '$favorite', '$comment', '$status')";

echo date("H:i:s") . " - Trying to add new entry to database\n";
try {
        try {
            echo date("H:i:s") . " - Inserting entry to database.\n";
            $db->exec($statement);
        } catch (Exception $e) {
            echo date("H:i:s") . " - Failed to insert entry!";
        }
} catch (Exception $e) {
    echo date("H:i:s") . " - Failed!\n";
}

}
*/
?>
