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

try {
	$db = new SQLite3("./db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
	
	$result = $db->query("SELECT * from 'last-updated'");
	$timestamp = [];
	while ($row = $result->fetchArray(1))
	{
		array_push($timestamp, $row);
	}
} catch (Exception $e) {
    $errorType = "db";
}

/*
foreach ($data as $data_ind) {
	print_r ($data_ind);
	echo "<br><span>end</span><br>";
}
*/

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" />
	
    <title>maltslist</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
	<style>	
      .marginDown {
	    margin-bottom: 15px;
	  }
	  
	  .checkmark {
		  text-align: center;
	  }
	  
	  .left-button {
		  float: left;
	  }
	  
	  .right-button {
		  float: right;
	  }
	  
	  .progress {
	  }
	</style>
  </head>
  <body>
    <header class="marginDown">
      <section class="hero is-primary">
        <div class="hero-body">
		  <div class="container animate__animated animate__fadeInDown">
		    <a href="">
			  <p class="title">maltslist</p>
			  <p class="subtitle">kuna mitte keegi ei suutnud paremat käkki teha...</p>
		    </a>
		  </div>
        </div>
      </section>
    </header>

    <main>
    <div class="container marginDown">
	  <h3 class="title is-3">Watching:</h3>
      <table class="table is-bordered">
		  <thead>
			<tr>
			  <th><abbr title="Postition in list">Index</abbr></th>
			  <th width="250px">Title</th>
			  <th>Score</th>
			  <th width="250px">Progress</th>
			  <th>Type</th>
			  <th>Rewatch?</th>
			  <th>❤️</th>
			  <th>Comment</th>
			</tr>
		  </thead>
		  <tbody>
			<?php foreach ($dataArray as $data) {
			
			if ($data["progress"] === $data["progress_length"]) {
				continue;
			}
			
			if ($data["favorite"] === "on") {
				continue;
			}
			
			?>
			<tr>
			  <th><?= $data["index"]; ?></th>
			  <td><?= $data["title"]; ?></td>
			  <td>
				<form action="/update-entry.php" method="post">
					<input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
				    <select name="form-score-change" id="score" onchange="this.form.submit()">
					  <option value="change-1" <?php if ($data["score"] === 1) { echo 'selected'; } ?>>1/5</option>
					  <option value="change-2" <?php if ($data["score"] === 2) { echo 'selected'; } ?>>2/5</option>
					  <option value="change-3" <?php if ($data["score"] === 3) { echo 'selected'; } ?>>3/5</option>
					  <option value="change-4" <?php if ($data["score"] === 4) { echo 'selected'; } ?>>4/5</option>
					  <option value="change-5" <?php if ($data["score"] === 5) { echo 'selected'; } ?>>5/5</option>
				    </select>
				</form>
			  </td>
			  <td>
			  	<form action="/update-entry.php" method="post">
					<input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
				    <button style="margin-right: 5px;" type="submit" name="form-progress-subtract" value="1">-1</button>
					<span style="display: inline-block; width: 65px;"><?= $data["progress"]; ?>/<?= $data["progress_length"]; ?></span>
					<button type="submit" name="form-progress-add" value="1">+1</button>
				</form>
			  </td>
			  <td><?= $data["type"]; ?></td>
			  <td>
			    <form action="/update-entry.php" method="post">
				  <span style=""><?= $data["rewatch"]; ?>x</span>
				  <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
				  <button style="float: right;" type="submit" name="form-rewatch-add" value="1">+1</button>
			    </form>
			  </td>
			  <td>
			    <form action="/update-entry.php" method="post">
					<input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
					<input type="hidden" name="form-isCheckmark" value="1"/>
					<div class="checkmark">
					<input type="checkbox" name="form-favorite-bool" onchange="this.form.submit()" <?php if ($data["favorite"] === "on") { echo 'checked'; } ?>>
					</div>
				</form>
			  </td>
			  <td>Lemmik</td>
			</tr>
			<?php } ?>
		  </tbody>
		</table>
		
		
		
		
		
		
		
		
		<h3 class="title is-3">Completed:</h3>
      <table class="table is-bordered">
		  <thead>
			<tr>
			  <th><abbr title="Postition in list">Index</abbr></th>
			  <th width="250px">Title</th>
			  <th>Score</th>
			  <th width="250px">Progress</th>
			  <th>Type</th>
			  <th>Rewatch?</th>
			  <th>❤️</th>
			  <th>Comment</th>
			</tr>
		  </thead>
		  <tbody>
			<?php foreach ($dataArray as $data) {
			
			if ($data["progress"] !== $data["progress_length"]) {
				continue;
			}
			
			if ($data["favorite"] === "on") {
				continue;
			}
			
			?>
			<tr>
			  <th><?= $data["index"]; ?></th>
			  <td><?= $data["title"]; ?></td>
			  <td>
				<form action="/update-entry.php" method="post">
					<input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
				    <select name="form-score-change" id="score" onchange="this.form.submit()">
					  <option value="change-1" <?php if ($data["score"] === 1) { echo 'selected'; } ?>>1/5</option>
					  <option value="change-2" <?php if ($data["score"] === 2) { echo 'selected'; } ?>>2/5</option>
					  <option value="change-3" <?php if ($data["score"] === 3) { echo 'selected'; } ?>>3/5</option>
					  <option value="change-4" <?php if ($data["score"] === 4) { echo 'selected'; } ?>>4/5</option>
					  <option value="change-5" <?php if ($data["score"] === 5) { echo 'selected'; } ?>>5/5</option>
				    </select>
				</form>
			  </td>
			  <td>
			  	<form action="/update-entry.php" method="post">
					<input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
				    <button style="margin-right: 5px;" type="submit" name="form-progress-subtract" value="1">-1</button>
					<span style="display: inline-block; width: 65px;"><?= $data["progress"]; ?>/<?= $data["progress_length"]; ?></span>
					<button type="submit" name="form-progress-add" value="1">+1</button>
				</form>
			  </td>
			  <td><?= $data["type"]; ?></td>
			  <td>
			    <form action="/update-entry.php" method="post">
				  <span><?= $data["rewatch"]; ?>x</span>
				  <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
				  <button style="float: right;" type="submit" name="form-rewatch-add" value="1">+1</button>
			    </form>
			  </td>
			  <td>
			    <form action="/update-entry.php" method="post">
					<input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
					<input type="hidden" name="form-isCheckmark" value="1"/>
					<div class="checkmark">
					<input type="checkbox" name="form-favorite-bool" onchange="this.form.submit()" <?php if ($data["favorite"] === "on") { echo 'checked'; } ?>>
					</div>
				</form>
			  </td>
			  <td>Lemmik</td>
			</tr>
			<?php } ?>
		  </tbody>
		</table>		
		
		
		
		
		

		<h3 class="title is-3">Favorites:</h3>
		
	      <table class="table is-bordered">
		  <thead>
			<tr>
			  <th><abbr title="Postition in list">Index</abbr></th>
			  <th width="250px">Title</th>
			  <th>Score</th>
			  <th width="250px">Progress</th>
			  <th>Type</th>
			  <th>Rewatch?</th>
			  <th>❤️</th>
			  <th>Comment</th>
			</tr>
		  </thead>
		  <tbody>
			<?php foreach ($dataArray as $data) {
			
			if ($data["favorite"] === "off") {
				continue;
			}
			
			?>
			<tr>
			  <th><?= $data["index"]; ?></th>
			  <td><?= $data["title"]; ?></td>
			  <td>
				<form action="/update-entry.php" method="post">
					<input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
				    <select name="form-score-change" id="score" onchange="this.form.submit()">
					  <option value="change-1" <?php if ($data["score"] === 1) { echo 'selected'; } ?>>1/5</option>
					  <option value="change-2" <?php if ($data["score"] === 2) { echo 'selected'; } ?>>2/5</option>
					  <option value="change-3" <?php if ($data["score"] === 3) { echo 'selected'; } ?>>3/5</option>
					  <option value="change-4" <?php if ($data["score"] === 4) { echo 'selected'; } ?>>4/5</option>
					  <option value="change-5" <?php if ($data["score"] === 5) { echo 'selected'; } ?>>5/5</option>
				    </select>
				</form>
			  </td>
			  <td>
			  	<form action="/update-entry.php" method="post">
					<input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
				    <button style="margin-right: 5px;" type="submit" name="form-progress-subtract" value="1">-1</button>
					<span style="display: inline-block; width: 65px;"><?= $data["progress"]; ?>/<?= $data["progress_length"]; ?></span>
					<button type="submit" name="form-progress-add" value="1">+1</button>
				</form>
			  </td>
			  <td><?= $data["type"]; ?></td>
			  <td>
			    <form action="/update-entry.php" method="post">
				  <span><?= $data["rewatch"]; ?>x</span>
				  <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
				  <button style="float: right;" type="submit" name="form-rewatch-add" value="1">+1</button>
			    </form>
			  </td>
			  <td>
			    <form action="/update-entry.php" method="post">
					<input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
					<input type="hidden" name="form-isCheckmark" value="1"/>
					<div class="checkmark">
					<input type="checkbox" name="form-favorite-bool" onchange="this.form.submit()" <?php if ($data["favorite"] === "on") { echo 'checked'; } ?>>
					</div>
				</form>
			  </td>
			  <td>Lemmik</td>
			</tr>
			<?php } ?>
		  </tbody>
		</table>		
		
		
		
		
		
		
		
		
		
		
    </div>
    </main>

    <header class="">
      <section class="hero is-primary">
        <div class="hero-body">
		  <div class="container animate__animated animate__fadeInUp">
			  <?= !empty($timestamp) ? '<span>Last updated: ' . $timestamp[0]["timestamp"] . " EEST</span>" : '' ?>
		  </div>
        </div>
      </section>
    </footer>
  </body>
</html>