<?php
$title = "Playlists";
$nav_playlist_page = "current_page";
include("includes/init.php");


// $db = open_sqlite_db("db/catalog.sqlite");
// Adding playlist:
// feedback message CSS classes
$title_feedback_class = 'hidden';
$author_feedback_class = 'hidden';
$unique_feedback_class = 'hidden';



// additional validation constraints
// TODO

// form values
$title = '';
$author = '';
$show_confirmation = False;
$show_form = True;


// sticky values
$sticky_title = '';
$sticky_author = '';


if (isset($_POST['add_form'])) {
  $title = trim($_POST['title']); // untrusted
  $author = trim($_POST['author']); // untrusted


  $form_valid = True;

  // title is required
  if (empty($title)) {
    $form_valid = False;
    $title_feedback_class = '';
  }

  // artist is required
  if (empty($author)) {
    $form_valid = False;
    $author_feedback_class = '';
  }

  // album, artist, title must be unique:
  $records = exec_sql_query(
    $db,
    "SELECT * FROM playlists WHERE (title = :title)",
    array(
      ':title' => $title,
    )
  )->fetchAll();
  if (count($records) > 0) {
    $form_valid = False;
    $unique_feedback_class = '';
  }

  if ($form_valid) {
    $show_form = False;
    $show_confirmation = True;
    // insert new record into database
    $result = exec_sql_query(
      $db,
      "INSERT INTO playlists (author, title) VALUES (:author, :title);",
      array(
        ':title' => $title, // tainted
        ':author' => $author
      )
    );

    // did the insert into the database succeed?
    if ($result) {
      //TODO
    }
  } else {
    // form is invalid, set sticky values
    $sticky_title = $title;
    $sticky_author = $author;
  }
}

// Function that will allow the generation of stars:
function make_stars($score)
{
  for ($s = 1; $s <= 5; $s++) {
    if ($s <= $score) {
      echo "★";
    } else {
      echo "☆";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta creator="viewport" content="width=device-width, initial-scale=1" />

  <title>Playlists</title>
  <link rel="stylesheet" type="text/css" href="/public/styles/site.css" media="all" />
</head>

<body>
  <?php include("includes/header.php"); ?>

  <?php
  // creates a table for each playlist:
  $records_playlists = exec_sql_query(
    $db,
    "SELECT * FROM Playlists ;"
  )->fetchAll();

  foreach ($records_playlists as $playlist) { ?>
    <div class="center">
      <h1><?php echo htmlspecialchars($playlist["title"]) ?></h1>
      <p><?php echo htmlspecialchars($playlist["author"]) ?></p>
      <table class="center">
        <tr>
          <th>Title</th>
          <th>Artist</th>
          <th>Album</th>
          <th class="rating">Rating</th>
          <th>File Uploaded</th>
        </tr>
        <?php
        // searches for all songs in that playlist
        $precords = exec_sql_query(
          $db,
          "SELECT songs.title, artist, album, rating, file_name
          FROM songs INNER JOIN mapper
          ON mapper.song_id = songs.id
          INNER JOIN playlists
          ON mapper.playlist_id = playlists.id
          WHERE playlists.id = :id;",
          array(
            ':id' => $playlist["id"]
          )
        )->fetchAll();

        foreach ($precords as $precord) { ?>
          <tr>
            <td><?php echo htmlspecialchars($precord["title"]); ?></td>
            <td><?php echo htmlspecialchars($precord["artist"]); ?></td>
            <td><?php echo htmlspecialchars($precord["album"]); ?></td>
            <td class="rating_item"><?php echo htmlspecialchars($precord["rating"]); ?></td>
            <td><?php echo htmlspecialchars($precord["file_name"]); ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>
  <?php } ?>

  <div class="center" id="playlist_form">


    <?php if ($show_confirmation && is_user_logged_in()) { ?>
      <h2>Thank you for your submission!</h2>
      <p>See playlists above to find <strong><?php echo htmlspecialchars($title); ?> by <strong><?php echo htmlspecialchars($author); ?></strong></p>
      <p>Add songs to <strong><?php echo htmlspecialchars($title); ?> by editing songs in <a href="home" class="add_another_song"> Music>></a></p>
      <p class="add_another_song"><a href="playlist"> Add Another Playlist>></a> </p>
    <?php } ?>

    <?php if ($show_form && is_user_logged_in()) { ?>
      <h2>Add Playlist:</h2>
      <form id="add_form" action="#playlist_form" method="post" novalidate>
        <div class="input">
          <label for="add_title">Title:</label>
          <input id="add_title" type="text" name="title" value="<?php echo htmlspecialchars($sticky_title); ?>" required />
        </div>
        <p id="title_feedback" class="feedback <?php echo $title_feedback_class; ?>">Please include a song title.</p>

        <div class="input">
          <label for="add_author">Creator:</label>
          <input id="add_author" type="author" name="author" value="<?php echo htmlspecialchars($sticky_author); ?>" required />
        </div>
        <p id="author_feedback" class="feedback <?php echo $author_feedback_class; ?>">Please include your name.</p>


        <p id="unique_feedback" class="feedback <?php echo $unique_feedback_class; ?>">Playlist already exists in table. Insert a new Playlist.</p>

        <div class=" submit_button">
          <button type="submit" name="add_form">Add Playlist</button>
        </div>
      </form>
    <?php } ?>
  </div>
</body>

</html>
