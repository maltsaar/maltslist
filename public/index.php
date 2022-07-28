<?php

require_once "../vendor/autoload.php";
require_once "../config.php";

// configure logger
Logger::configure("../config.php");
$logger = Logger::getLogger('maltslist index');

// variables
$dataArray = [];
$timestamp = [];

if (isset($_GET["error_msg"], $_GET["error_title"])) {
    $error_msg = $_GET["error_msg"];
    $error_title = $_GET["error_title"];
}

if (isset($_GET["regular_msg"], $_GET["regular_title"])) {
    $regular_msg = $_GET["regular_msg"];
    $regular_title = $_GET["regular_title"];
}

// check if db exists
if (file_exists("../db/$database")) {
    try {
        $db = new SQLite3("../db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
        $db->enableExceptions(true);
        $dbOpenSuccess = true;
    }
    catch (Exception $e) {
        $exceptionMessage = $e->getMessage();
        $logger->FATAL("Unable to open database file due to excetion: $exceptionMessage");
        $error_msg = "$exceptionMessage";
        $error_title = "database exception";   
    }
}

if ($dbOpenSuccess === true) {    
    // get current data
    $logger->info("Trying to query database for current list data");
    try {
        $result = $db->query("SELECT * from 'list'");
        while ($row = $result->fetchArray(1)) {
            array_push($dataArray, $row);
        }
        $querySuccess = true;
        $logger->info("Successfully queried database for current list data");
    }
    
    catch (Exception $e) {
        $exceptionMessage = $e->getMessage();
        $logger->FATAL("Unable to query database for current list data due to excetion: $exceptionMessage");
        $error_msg = "$exceptionMessage";
        $error_title = "database exception";
    }
    
    // get timestamp
    $logger->info("Trying to query database for last timestamp");
    try {
        $result = $db->query("SELECT * from 'last-updated'");
        while ($row = $result->fetchArray(1)) {
            array_push($timestamp, $row);
        }
        $logger->info("Successfully queried database for last timestamp");
    }
    
    catch (Exception $e) {
        $exceptionMessage = $e->getMessage();
        $logger->FATAL("Unable to query database for last timestamp due to excetion: $exceptionMessage");
        $error_msg = "$exceptionMessage";
        $error_title = "database exception";
    }
    
    // close
    $db->close();
}

else {
    $logger->FATAL("database file doesn't exist");
    $error_msg = "File doesn't exist. Please run setupDatabase.php";
    $error_title = "database missing";
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        
        <link rel="icon" type="image/png" href="/images/rei.png"/>
        <title>maltslist</title>

        <link rel="stylesheet" href="/css/bulma-0.9.4.min.css">
        <link rel="stylesheet" href="/css/style.css">
        <link rel="stylesheet" href="/css/animate-4.1.1.min.css"/>
    </head>
    
    <body>
        <!-- + modal -->
        <div class="modal" id="modal-plus">
            <div class="modal-background animate__animated animate__fadeIn"></div>
            <div class="modal-card modal-card-plus animate__animated animate__zoomIn">
                <section class="modal-card-header">
                    <div class="modalBanner hero is-info is-bold">
                        <h1 class="title">Add new entry!</h1>
                    </div>
                </section>
                
                <section class="modal-card-body">
                    <form action="/add-entry.php" method="post">
                        <div class="field">
                        <input type="hidden" name="form-which" value="add-entry"/>
                        <label class="label">Title</label>
                        <div class="control">
                            <input class="input" name="form-title" type="text" placeholder="Example: Pulp Fiction (1994)" maxlength="50">
                        </div>
                        
                        <label class="label">Score</label>
                        <div class="control">
                            <div class="select">
                                <select name="form-score">
                                    <option value="0">🤔 (I haven't decided yet)</option>
                                    <option value="1">1/5 (Very poor)</option>
                                    <option value="2">2/5 (Poor)</option>
                                    <option value="3">3/5 (Fair)</option>
                                    <option value="4">4/5 (Good)</option>
                                    <option value="5">5/5 (Excellent)</option>
                                </select>
                            </div>
                        </div>
                        
                        <label class="label">Progress</label>
                        <div class="control">
                            <input class="input" name="form-progress" type="number" value="0" min="0">
                        </div>
                        <label class="label">Total length</label>
                        <div class="control">
                            <input class="input" name="form-progress-length" type="number" value="1" min="1">
                        </div>
                        
                        <label class="label">Type</label>
                        <div class="control">
                            <div class="select">
                                <select name="form-type">
                                    <option value="tv">TV</option>
                                    <option value="film" selected="selected">Film</option>
                                </select>
                            </div>
                        </div>
                        
                        <label class="label">Rewatch count</label>
                        <div class="control">
                            <input class="input" name="form-rewatch" type="number" min="0" value="0">
                        </div>
                        
                        <label class="label">❤️ (Favorite)</label>
                        <div class="control">
                            <div class="select">
                                <select name="form-favorite">
                                    <option value="on">Yes</option>
                                    <option value="off" selected="selected">No</option>
                                </select>
                            </div>
                        </div>
                        
                        <label class="label">Comment</label>
                        <div class="control">
                            <input class="input" type="text" name="form-comment" placeholder="Example: Ending was very bad">
                        </div>
                        <button class="button marginUp" type="submit">Submit</button>
                        </div>
                    </form>
                </section>
            </div>

            <button class="modal-close is-large" aria-label="close"></button>
        </div>
        
        <!-- - modal -->
        <div class="modal" id="modal-minus">
            <div class="modal-background animate__animated animate__fadeIn"></div>
            <div class="modal-card modal-card-minus animate__animated animate__zoomIn">
                <section class="modal-card-header">
                    <div class="modalBanner hero is-danger is-bold">
                        <h1 class="title">Remove entry!</h1>
                        <h6 class="subtitle is-6">
                            Note: This action is <span class="has-text-weight-bold">permanent</span>
                        </h6>
                    </div>
                </section>
                
                <section class="modal-card-body">
                    <form action="/remove-entry.php" method="post">
                        <div class="field">
                            <input type="hidden" name="form-which" value="remove-entry"/>
                            <label class="label">Index</label>
                            <div class="control">
                                <input class="input" name="form-index" type="number" min="0" value="0">
                            </div>
                            <button class="button marginUp" type="submit">Submit</button>
                        </div>
                    </form>
                </section>
            </div>

            <button class="modal-close is-large" aria-label="close"></button>
        </div>        

        <!-- comment modal -->
        <div class="modal" id="modal-comment">
            <div class="modal-background animate__animated animate__fadeIn"></div>
            <div class="modal-card modal-card-comment animate__animated animate__zoomIn">
                <section class="modal-card-header">
                    <div class="modalBanner hero is-warning is-bold">
                        <h1 class="title">Change comment!</h1>
                        <h6 class="subtitle is-6">
                            Note: Type "n/a" if you want to remove a comment from an entry
                        </h6>
                    </div>
                </section>
                
                <section class="modal-card-body">
                    <form action="/change-comment.php" method="post">
                        <div class="field">
                            <label class="label">Index</label>
                            <div class="control">
                                <input class="input" name="form-index" type="number" min="0" value="0">
                            </div>
                            <label class="label">Comment</label>
                            <div class="control">
                                <textarea class="textarea" name="form-comment" placeholder="Example: Ending was bad"></textarea>
                            </div>
                            <button class="button marginUp" type="submit">Submit</button>
                        </div>
                    </form>
                </section>
            </div>

            <button class="modal-close is-large" aria-label="close"></button>
        </div>
        
        <!-- csv modal -->
        <div class="modal" id="modal-csv">
            <div class="modal-background animate__animated animate__fadeIn"></div>
            <div class="modal-card modal-card-csv animate__animated animate__zoomIn">
                <section class="modal-card-header">
                    <div class="modalBanner hero is-success is-bold">
                        <h1 class="title">Export as CSV!</h1>
                        <h6 class="subtitle is-6">
                            This converts the sqlite database into a csv file.
                        </h6>
                    </div>
                </section>
                
                <section class="modal-card-body">
                    <div class="field">
                        <a href="/export-xlsx.php"><button class="button">Convert and Download</button></a>
                    </div>
                </section>
            </div>

            <button class="modal-close is-large" aria-label="close"></button>
        </div>
        
        <header class="marginDown">
            <!-- header -->
            <section class="hero is-info is-bold">
                <div class="hero-body">
                    <div class="container header-container animate__animated animate__fadeInDown">
                        <a class="header-thing-a" href="/index.php">
                            <div class="header-thing-1">
                                <img src="images/rei.png" width="85">
                            </div>
                            <div class="header-thing-2">
                                <span class="title">maltslist</span>
                            </div>
                        </a>
                    </div>
                </div>
            </section>
            
            <!-- navbar -->
            <nav class="navbar is-dark">
                <div id="navMenuColordark-example" class="navbar-menu">
                    <div class="container container-nav">
                        <div class="navbar-start">
                            <button data-target="modal-plus"    class="modal-button button is-info is-small animate__animated animate__fadeIn">+</button>
                            <button data-target="modal-minus"   class="modal-button button is-danger is-small animate__animated animate__fadeIn">-</button>
                            <button data-target="modal-comment" class="modal-button button is-warning is-small animate__animated animate__fadeIn">Change comment</button>
                            <button data-target="modal-csv"     class="modal-button button is-success is-small animate__animated animate__fadeIn">Export CSV</button>
                            
                            <?php if ($ssoEnabled === true) { ?><a href="<?= $ssoUrlLogout; ?>"><button class="button is-danger is-small animate__animated animate__fadeIn">Logout</button></a><?php } ?>                            
                        </div>
                    </div>
                </div>
            </nav>
            
            <!-- message error -->
            <?php if (isset($error_msg) && isset($error_title)) { ?>
            <div id="message-error" class="container container-message marginUp marginDown">
                <article class="message message-error-body is-danger">
                    <div class="message-header">
                        <p>Error: <?= $error_title; ?></p>
                    </div>
                    <div class="message-body">
                        <?= $error_msg; ?>
                    </div>
                </article>
            </div>
            <?php } ?>
            
            <!-- message regular -->
            <?php if (isset($regular_msg) && isset($regular_title)) { ?>
            <div id="message-regular" class="container marginUp marginDown">
                <article class="message message-regular-body is-primary">
                    <div class="message-header">
                        <p><?= $regular_title; ?></p>
                    </div>
                    <div class="message-body">
                        <?= $regular_msg; ?>
                    </div>
                </article>
            </div>
            <?php } ?>
        </header>

        <main>
            <div class="container">
                <?php if ($querySuccess === true) { ?>
                <!-- Plan to watch -->
                <h3 class="title is-3" id="plantowatch">Plan to watch:</h3>
                <table class="table is-bordered is-hoverable">
                    <thead>
                        <tr>
                            <th><abbr title="Index">#</abbr></th>
                            <th width="250px">Title</th>
                            <th>Score</th>
                            <th width="150px">Progress</th>
                            <th>Type</th>
                            <th>Rewatch?</th>
                            <th data-sort-method="none" class="unscrollable">❤️</th>
                            <th data-sort-method="none" class="unscrollable">Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        
                        foreach ($dataArray as $data) {
                        
                            if ($data["progress"] !== 0) {
                                continue;
                            }
                            
                            if ($data["favorite"] === "on") {
                                continue;
                            }
                            
                            if ($data["is_deleted"] === "yes") {
                                continue;
                            }                         
                        
                        ?>
                        <tr>
                            <!-- Index nr -->
                            <th><?= $data["index"]; ?></th>
                            
                            <!-- Title -->
                            <td><?= $data["title"]; ?></td>
                            
                            <!-- Score -->
                            <td style="width: 75px;" data-sort="<?= $data["score"]; ?>">
                                <form action="/change-score.php" method="post">
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <select name="form-score-change" id="score" onchange="this.form.submit()">
                                      <option value="change-0" <?php if ($data["score"] === 0) { echo 'selected'; } ?>>🤔</option>
                                      <option value="change-1" <?php if ($data["score"] === 1) { echo 'selected'; } ?>>1/5</option>
                                      <option value="change-2" <?php if ($data["score"] === 2) { echo 'selected'; } ?>>2/5</option>
                                      <option value="change-3" <?php if ($data["score"] === 3) { echo 'selected'; } ?>>3/5</option>
                                      <option value="change-4" <?php if ($data["score"] === 4) { echo 'selected'; } ?>>4/5</option>
                                      <option value="change-5" <?php if ($data["score"] === 5) { echo 'selected'; } ?>>5/5</option>
                                    </select>
                                    <?php if ($data["score"] === 1) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/1-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 2) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/2-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 3) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/3-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 4) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/4-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 5) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/5-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                </form> 
                            </td>
                            
                            <!-- Progress -->
                            <td data-sort="<?= $data["progress_length"]; ?>">
                                <form action="/change-progress.php" method="post">
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <button style="margin-right: 5px;" type="submit" name="form-progress-subtract" value="1">-1</button>
                                    <span style="display: inline-block; width: 65px;"><?= $data["progress"]; ?>/<?= $data["progress_length"]; ?></span>
                                    <button type="submit" name="form-progress-add" value="1">+1</button>
                                </form>
                            </td>
                            
                            <!-- Type -->
                            <td><?= $data["type"]; ?></td>
                            
                            <!-- Rewatch -->
                            <td>
                                <form action="/increment-rewatch.php" method="post">
                                    <span style=""><?= $data["rewatch"]; ?>x</span>
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <button style="float: right;" type="submit" name="form-rewatch-add" value="1">+1</button>
                                </form>
                            </td>
                            
                            <!-- Favorite -->
                            <td>
                                <form action="/favorite.php" method="post">
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <input type="hidden" name="form-isCheckmark" value="1"/>
                                    <div class="checkmark">
                                    <input type="checkbox" name="form-favorite-bool" onchange="this.form.submit()" <?php if ($data["favorite"] === "on") { echo 'checked'; } ?>>
                                    </div>
                                </form>
                            </td>
                            
                            <!-- Comment -->
                            <td><?php if(!empty($data["comment"])) { echo $data["comment"]; } else { echo "n/a"; }?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- Watching -->
                <h3 class="title is-3" id="watching">Watching:</h3>
                <table class="table is-bordered is-hoverable">
                    <thead>
                        <tr>
                            <th><abbr title="Index">#</abbr></th>
                            <th width="250px">Title</th>
                            <th>Score</th>
                            <th width="150px">Progress</th>
                            <th>Type</th>
                            <th>Rewatch?</th>
                            <th data-sort-method="none" class="unscrollable">❤️</th>
                            <th data-sort-method="none" class="unscrollable">Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        
                        foreach ($dataArray as $data) {
                        
                            if ($data["progress"] === $data["progress_length"]) {
                                continue;
                            }
                            
                            if ($data["favorite"] === "on") {
                                continue;
                            }
                            
                            if ($data["progress"] === 0) {
                                continue;
                            }
                            
                            if ($data["is_deleted"] === "yes") {
                                continue;
                            }                          
                        
                        ?>
                        <tr>
                            <!-- Index nr -->
                            <th><?= $data["index"]; ?></th>
                            
                            <!-- Title -->
                            <td><?= $data["title"]; ?></td>
                            
                            <!-- Score -->
                            <td style="width: 75px;" data-sort="<?= $data["score"]; ?>">
                                <form action="/change-score.php" method="post">
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <select name="form-score-change" id="score" onchange="this.form.submit()">
                                      <option value="change-0" <?php if ($data["score"] === 0) { echo 'selected'; } ?>>🤔</option>
                                      <option value="change-1" <?php if ($data["score"] === 1) { echo 'selected'; } ?>>1/5</option>
                                      <option value="change-2" <?php if ($data["score"] === 2) { echo 'selected'; } ?>>2/5</option>
                                      <option value="change-3" <?php if ($data["score"] === 3) { echo 'selected'; } ?>>3/5</option>
                                      <option value="change-4" <?php if ($data["score"] === 4) { echo 'selected'; } ?>>4/5</option>
                                      <option value="change-5" <?php if ($data["score"] === 5) { echo 'selected'; } ?>>5/5</option>
                                    </select>
                                    <?php if ($data["score"] === 1) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/1-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 2) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/2-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 3) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/3-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 4) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/4-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 5) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/5-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                </form> 
                            </td>
                            
                            <!-- Progress -->
                            <td data-sort="<?= $data["progress_length"]; ?>">
                                <form action="/change-progress.php" method="post">
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <button style="margin-right: 5px;" type="submit" name="form-progress-subtract" value="1">-1</button>
                                    <span style="display: inline-block; width: 65px;"><?= $data["progress"]; ?>/<?= $data["progress_length"]; ?></span>
                                    <button type="submit" name="form-progress-add" value="1">+1</button>
                                </form>
                            </td>
                            
                            <!-- Type -->
                            <td><?= $data["type"]; ?></td>
                            
                            <!-- Rewatch -->
                            <td>
                                <form action="/increment-rewatch.php" method="post">
                                    <span style=""><?= $data["rewatch"]; ?>x</span>
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <button style="float: right;" type="submit" name="form-rewatch-add" value="1">+1</button>
                                </form>
                            </td>
                            
                            <!-- Favorite -->
                            <td>
                                <form action="/favorite.php" method="post">
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <input type="hidden" name="form-isCheckmark" value="1"/>
                                    <div class="checkmark">
                                    <input type="checkbox" name="form-favorite-bool" onchange="this.form.submit()" <?php if ($data["favorite"] === "on") { echo 'checked'; } ?>>
                                    </div>
                                </form>
                            </td>
                            
                            <!-- Comment -->
                            <td><?php if(!empty($data["comment"])) { echo $data["comment"]; } else { echo "n/a"; }?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- Completed -->
                <h3 class="title is-3" id="completed">Completed:</h3>
                <table class="table is-bordered is-hoverable">
                    <thead>
                        <tr>
                            <th><abbr title="Index">#</abbr></th>
                            <th width="250px">Title</th>
                            <th>Score</th>
                            <th width="150px">Progress</th>
                            <th>Type</th>
                            <th>Rewatch?</th>
                            <th data-sort-method="none" class="unscrollable">❤️</th>
                            <th data-sort-method="none" class="unscrollable">Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        
                        foreach ($dataArray as $data) {
                        
                            if ($data["progress"] !== $data["progress_length"]) {
                                continue;
                            }

                            if ($data["favorite"] === "on") {
                                continue;
                            }
                            
                            if ($data["is_deleted"] === "yes") {
                                continue;
                            }                            
                        
                        ?>
                        <tr>
                            <!-- Index nr -->
                            <th><?= $data["index"]; ?></th>
                            
                            <!-- Title -->
                            <td><?= $data["title"]; ?></td>
                            
                            <!-- Score -->
                            <td style="width: 75px;" data-sort="<?= $data["score"]; ?>">
                                <form action="/change-score.php" method="post">
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <select name="form-score-change" id="score" onchange="this.form.submit()">
                                      <option value="change-0" <?php if ($data["score"] === 0) { echo 'selected'; } ?>>🤔</option>
                                      <option value="change-1" <?php if ($data["score"] === 1) { echo 'selected'; } ?>>1/5</option>
                                      <option value="change-2" <?php if ($data["score"] === 2) { echo 'selected'; } ?>>2/5</option>
                                      <option value="change-3" <?php if ($data["score"] === 3) { echo 'selected'; } ?>>3/5</option>
                                      <option value="change-4" <?php if ($data["score"] === 4) { echo 'selected'; } ?>>4/5</option>
                                      <option value="change-5" <?php if ($data["score"] === 5) { echo 'selected'; } ?>>5/5</option>
                                    </select>
                                    <?php if ($data["score"] === 1) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/1-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 2) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/2-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 3) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/3-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 4) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/4-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 5) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/5-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                </form> 
                            </td>
                            
                            <!-- Progress -->
                            <td data-sort="<?= $data["progress_length"]; ?>">
                                <form action="/change-progress.php" method="post">
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <button style="margin-right: 5px;" type="submit" name="form-progress-subtract" value="1">-1</button>
                                    <span style="display: inline-block; width: 65px;"><?= $data["progress"]; ?>/<?= $data["progress_length"]; ?></span>
                                    <button type="submit" name="form-progress-add" value="1">+1</button>
                                </form>
                            </td>
                            
                            <!-- Type -->
                            <td><?= $data["type"]; ?></td>
                            
                            <!-- Rewatch -->
                            <td>
                                <form action="/increment-rewatch.php" method="post">
                                    <span style=""><?= $data["rewatch"]; ?>x</span>
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <button style="float: right;" type="submit" name="form-rewatch-add" value="1">+1</button>
                                </form>
                            </td>
                            
                            <!-- Favorite -->
                            <td>
                                <form action="/favorite.php" method="post">
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <input type="hidden" name="form-isCheckmark" value="1"/>
                                    <div class="checkmark">
                                    <input type="checkbox" name="form-favorite-bool" onchange="this.form.submit()" <?php if ($data["favorite"] === "on") { echo 'checked'; } ?>>
                                    </div>
                                </form>
                            </td>
                            
                            <!-- Comment -->
                            <td><?php if(!empty($data["comment"])) { echo $data["comment"]; } else { echo "n/a"; }?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <!-- Favorites -->
                <h3 class="title is-3" id="favorites">Favorites:</h3>
                <table class="table is-bordered is-hoverable">
                    <thead>
                        <tr>
                            <th><abbr title="Index">#</abbr></th>
                            <th width="250px">Title</th>
                            <th>Score</th>
                            <th width="150px">Progress</th>
                            <th>Type</th>
                            <th>Rewatch?</th>
                            <th data-sort-method="none" class="unscrollable">❤️</th>
                            <th data-sort-method="none" class="unscrollable">Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        
                        foreach ($dataArray as $data) {

                            if ($data["favorite"] === "off") {
                                continue;
                            }
                            
                            if ($data["is_deleted"] === "yes") {
                                continue;
                            }                           
                        
                        ?>
                        <tr>
                            <!-- Index nr -->
                            <th><?= $data["index"]; ?></th>
                            
                            <!-- Title -->
                            <td><?= $data["title"]; ?></td>
                            
                            <!-- Score -->
                            <td style="width: 75px;" data-sort="<?= $data["score"]; ?>">
                                <form action="/change-score.php" method="post">
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <select name="form-score-change" id="score" onchange="this.form.submit()">
                                      <option value="change-0" <?php if ($data["score"] === 0) { echo 'selected'; } ?>>🤔</option>
                                      <option value="change-1" <?php if ($data["score"] === 1) { echo 'selected'; } ?>>1/5</option>
                                      <option value="change-2" <?php if ($data["score"] === 2) { echo 'selected'; } ?>>2/5</option>
                                      <option value="change-3" <?php if ($data["score"] === 3) { echo 'selected'; } ?>>3/5</option>
                                      <option value="change-4" <?php if ($data["score"] === 4) { echo 'selected'; } ?>>4/5</option>
                                      <option value="change-5" <?php if ($data["score"] === 5) { echo 'selected'; } ?>>5/5</option>
                                    </select>
                                    <?php if ($data["score"] === 1) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/1-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 2) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/2-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 3) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/3-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 4) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/4-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                    <?php if ($data["score"] === 5) { ?> <div style="height: 24px; position: absolute; transform: translateX(+700%); display: inline;"><img src="images/5-dot.png" width="7px" height="7px" style="margin: 0; padding 0; transform: translateY(-50%);"></div> <?php } ?>
                                </form> 
                            </td>
                            
                            <!-- Progress -->
                            <td data-sort="<?= $data["progress_length"]; ?>">
                                <form action="/change-progress.php" method="post">
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <button style="margin-right: 5px;" type="submit" name="form-progress-subtract" value="1">-1</button>
                                    <span style="display: inline-block; width: 65px;"><?= $data["progress"]; ?>/<?= $data["progress_length"]; ?></span>
                                    <button type="submit" name="form-progress-add" value="1">+1</button>
                                </form>
                            </td>
                            
                            <!-- Type -->
                            <td><?= $data["type"]; ?></td>
                            
                            <!-- Rewatch -->
                            <td>
                                <form action="/increment-rewatch.php" method="post">
                                    <span style=""><?= $data["rewatch"]; ?>x</span>
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <button style="float: right;" type="submit" name="form-rewatch-add" value="1">+1</button>
                                </form>
                            </td>
                            
                            <!-- Favorite -->
                            <td>
                                <form action="/favorite.php" method="post">
                                    <input type="hidden" name="entry" value="<?= $data["index"]; ?>"/>
                                    <input type="hidden" name="form-isCheckmark" value="1"/>
                                    <div class="checkmark">
                                    <input type="checkbox" name="form-favorite-bool" onchange="this.form.submit()" <?php if ($data["favorite"] === "on") { echo 'checked'; } ?>>
                                    </div>
                                </form>
                            </td>
                            
                            <!-- Comment -->
                            <td><?php if(!empty($data["comment"])) { echo $data["comment"]; } else { echo "n/a"; }?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php } ?>
            </div>
        </main>

        <!-- footer -->
        <nav class="navbar is-dark marginUp">
            <div id="navMenuColordark-example" class="navbar-menu">
                <div class="container container-nav">
                    <div class="navbar-start">
                        <div class="container animate__animated animate__fadeInUp">
                            <?php if (!empty($timestamp)) { echo "<span>Last updated: ".$timestamp[0]["timestamp"]." EEST</span>"; } ?>
                        </div>                            
                    </div>
                </div>
            </div>
        </nav>
        
        <script src="/js/jquery-3.4.1.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {

                if (localStorage.getItem("maltslist-quote-scroll") != null) {
                    $(window).scrollTop(localStorage.getItem("maltslist-quote-scroll"));
                }

                $(window).on("scroll", function() {
                    localStorage.setItem("maltslist-quote-scroll", $(window).scrollTop());
                });

            });
        </script>
        <script>
            'use strict';

            document.addEventListener('DOMContentLoaded', function () {

              // Modals

              var rootEl = document.documentElement;
              var $modals = getAll('.modal');
              var $modalButtons = getAll('.modal-button');
              var $modalCloses = getAll('.modal-background, .modal-close, .modal-card-head .delete, .modal-card-foot .button');

              if ($modalButtons.length > 0) {
                $modalButtons.forEach(function ($el) {
                  $el.addEventListener('click', function () {
                    var target = $el.dataset.target;
                    var $target = document.getElementById(target);
                    rootEl.classList.add('is-clipped');
                    $target.classList.add('is-active');
                  });
                });
              }

              if ($modalCloses.length > 0) {
                $modalCloses.forEach(function ($el) {
                  $el.addEventListener('click', function () {
                    closeModals();
                  });
                });
              }

              document.addEventListener('keydown', function (event) {
                var e = event || window.event;
                if (e.keyCode === 27) {
                  closeModals();
                }
              });

              function closeModals() {
                rootEl.classList.remove('is-clipped');
                $modals.forEach(function ($el) {
                  $el.classList.remove('is-active');
                });
              }

              // Functions

              function getAll(selector) {
                return Array.prototype.slice.call(document.querySelectorAll(selector), 0);
              }

            });
        </script>
        <script>
            setTimeout(function(){
                $('#message-error').fadeOut(250);
            }, 5000);
            
            setTimeout(function(){
                $('#message-regular').fadeOut(250);
            }, 3000);
        </script>
        <script src='/js/tablesort.js'></script>
        <script src='/js/sorts/tablesort.number.js'></script>
        <script>
            Array.prototype.forEach.call(document.getElementsByClassName('table'), el => {
                new Tablesort(el);
            });
        </script>
    </body>
</html>