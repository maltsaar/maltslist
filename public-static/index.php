<?php

require_once "../vendor/autoload.php";
require_once "../config.php";

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
    $db = new SQLite3("../db/" . $database, SQLITE3_OPEN_CREATE | SQLITE3_OPEN_READWRITE);
    $db->enableExceptions(true);
    
    // get current data
    try {
        $result = $db->query("SELECT * from 'list'");
        while ($row = $result->fetchArray(1)) {
            array_push($dataArray, $row);
        }
        $querySuccess = true;
    }
    
    catch (Exception $e) {
        $exceptionMessage = $e->getMessage();
        $error_msg = "$exceptionMessage";
        $error_title = "database exception";
    }
    
    // get timestamp
    try {
        $result = $db->query("SELECT * from 'last-updated'");
        while ($row = $result->fetchArray(1)) {
            array_push($timestamp, $row);
        }
    }
    
    catch (Exception $e) {
        $exceptionMessage = $e->getMessage();
        $error_msg = "$exceptionMessage";
        $error_title = "database exception";
    }
    
    // close
    $db->close();
}

else {
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
        <title>maltslist static</title>
        <link rel="stylesheet" href="/css/bulma-0.9.4.min.css">
        <link rel="stylesheet" href="/css/style.css">
        <link rel="stylesheet" href="/css/animate-4.1.1.min.css"/>
    </head>
    
    <body>
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
                            <span class="animate__animated animate__fadeIn">This is a static site. You won't be able to edit the list without logging in.</span>                          
                        </div>
                    </div>
                </div>
            </nav>
        </header>

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
                            <td style="width: 75px;" data-sort="<?= $data["score"]; ?>"><?php if ($data["score"] === 0) { echo "🤔"; } else { echo $data["score"]."/5"; } ?></td>
                            
                            <!-- Progress -->
                            <td data-sort="<?= $data["progress_length"]; ?>"><?= $data["progress"]; ?>/<?= $data["progress_length"]; ?></td>
                            
                            <!-- Type -->
                            <td><?= $data["type"]; ?></td>
                            
                            <!-- Rewatch -->
                            <td><?= $data["rewatch"]; ?>x</td>
                            
                            <!-- Favorite -->
                            <td>
                                <div class="checkmark">
                                    <input type="checkbox" disabled="disabled" <?php if ($data["favorite"] === "on") { echo 'checked'; } ?>>
                                </div>
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
                            <td style="width: 75px;" data-sort="<?= $data["score"]; ?>"><?php if ($data["score"] === 0) { echo "🤔"; } else { echo $data["score"]."/5"; } ?></td>
                            
                            <!-- Progress -->
                            <td data-sort="<?= $data["progress_length"]; ?>"><?= $data["progress"]; ?>/<?= $data["progress_length"]; ?></td>
                            
                            <!-- Type -->
                            <td><?= $data["type"]; ?></td>
                            
                            <!-- Rewatch -->
                            <td><?= $data["rewatch"]; ?>x</td>
                            
                            <!-- Favorite -->
                            <td>
                                <div class="checkmark">
                                    <input type="checkbox" disabled="disabled" <?php if ($data["favorite"] === "on") { echo 'checked'; } ?>>
                                </div>
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
                            <td style="width: 75px;" data-sort="<?= $data["score"]; ?>"><?php if ($data["score"] === 0) { echo "🤔"; } else { echo $data["score"]."/5"; } ?></td>
                            
                            <!-- Progress -->
                            <td data-sort="<?= $data["progress_length"]; ?>"><?= $data["progress"]; ?>/<?= $data["progress_length"]; ?></td>
                            
                            <!-- Type -->
                            <td><?= $data["type"]; ?></td>
                            
                            <!-- Rewatch -->
                            <td><?= $data["rewatch"]; ?>x</td>
                            
                            <!-- Favorite -->
                            <td>
                                <div class="checkmark">
                                    <input type="checkbox" disabled="disabled" <?php if ($data["favorite"] === "on") { echo 'checked'; } ?>>
                                </div>
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
                            <td style="width: 75px;" data-sort="<?= $data["score"]; ?>"><?php if ($data["score"] === 0) { echo "🤔"; } else { echo $data["score"]."/5"; } ?></td>
                            
                            <!-- Progress -->
                            <td data-sort="<?= $data["progress_length"]; ?>"><?= $data["progress"]; ?>/<?= $data["progress_length"]; ?></td>
                            
                            <!-- Type -->
                            <td><?= $data["type"]; ?></td>
                            
                            <!-- Rewatch -->
                            <td><?= $data["rewatch"]; ?>x</td>
                            
                            <!-- Favorite -->
                            <td>
                                <div class="checkmark">
                                    <input type="checkbox" disabled="disabled" <?php if ($data["favorite"] === "on") { echo 'checked'; } ?>>
                                </div>
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