<?php
include("includes/init.php");
$nav_home_page = "current_page";
define("MAX_FILE_SIZE", 10000000);
// show song with song_id:
$song_id = (int)trim($_GET['id']);
$url = "/home/song?" . http_build_query(array('id' => $song_id));

// $edit_song_mode = True;
$edit_permission = False;

// // CHECK EDITING MODE
// if (isset($_GET['edit'])) {
//   $edit_song_mode = True;


//   // edit param value is also song ID
//   $song_id = (int)trim($_GET['edit']);
// }

// find the song from songs
if ($song_id) {
  $records = exec_sql_query(
    $db,
    "SELECT * FROM songs WHERE id = :id;",
    array(':id' => $song_id)
  )->fetchAll();
  if (count($records) > 0) {
    $song = $records[0];
  } else {
    $song = NULL;
  }
}



// Only continue if we have a valid song
if ($song) {
  // // Adding students and more:
  // // feedback message CSS classes
  // $title_feedback_class = 'hidden';
  // $artist_feedback_class = 'hidden';
  // $album_feedback_class = 'hidden';
  // $unique_feedback_class = 'hidden';
  // sticky values
  $sticky_title = $song['title'];
  $sticky_artist = $song['artist'];
  $sticky_album = $song['album'];
  $sticky_source = $song['source'];
  $sticky_rating_1 = ($song['rating'] == '1' ? 'selected' : '');
  $sticky_rating_2 = ($song['rating'] == '2' ? 'selected' : '');
  $sticky_rating_3 = ($song['rating'] == '3' ? 'selected' : '');
  $sticky_rating_4 = ($song['rating'] == '4' ? 'selected' : '');
  $sticky_rating_5 = ($song['rating'] == '5' ? 'selected' : '');
  //   // Check if user has permission (ADMIN)
  //   // if ($edit_permission) {
  // song information
  $title = htmlspecialchars($song['title']);
  $url = "/home/song?" . http_build_query(array('id' => $song['id']));
  $edit_url = "/home/song?" . http_build_query(array('edit' => $song['id']));
  if ($is_admin) {
    $edit_permission = True;
  }

  $playlists = exec_sql_query(
    $db,
    "SELECT playlists.title, playlists.id
                          FROM songs INNER JOIN mapper
                          ON mapper.song_id = songs.id
                          INNER JOIN playlists
                          ON mapper.playlist_id = playlists.id
                          WHERE songs.id = :id;",
    array(
      ':id' => $song["id"]
    )
  )->fetchAll();
  // }
  // feedback message CSS classes
  $title_feedback_class = 'hidden';
  $artist_feedback_class = 'hidden';
  $album_feedback_class = 'hidden';
  $unique_feedback_class = 'hidden';
  $source_feedback_class = 'hidden';

  // form values
  //playlist array
  $playlist_array = $playlists['id'];
  $show_confirmation = False;
  $show_form = True;
  $upload_filename = $song['file_name'];
  $upload_ext = $song['file_ext'];
  $deleted = False;

  // sticky values
  $sticky_file_name = $song['file_name'];
  $sticky_source = $song['source'];
  $sticky_playlist_array = $playlists['id'];
  // $sticky_rating_1 = '';
  // $sticky_rating_2 = '';
  // $sticky_rating_3 = '';
  // $sticky_rating_4 = '';
  // $sticky_rating_5 = '';
  if (isset($_POST['delete'])) {

    // Deletes record from mapper database
    $delete_from_mapper = exec_sql_query(
      $db,
      "DELETE FROM mapper WHERE song_id=:id;",
      array(
        ':id' => $song_id
      )
    );

    // Deletes record from song database
    $delete_from_songs = exec_sql_query(
      $db,
      "DELETE FROM songs WHERE id=:id ;",
      array(
        ':id' => $song_id
      )
    );
    if ($delete_from_mapper && $delete_from_songs) {
      $deleted = True;
    } else {
      debug_log("FUCK");
    }
  }

  if (isset($_POST['add_song'])) {
    $title = trim($_POST['title']); // untrusted
    $artist = trim($_POST['artist']); // untrusted
    $album = trim($_POST['album']); // untrusted
    $rating = trim($_POST['rating']); // untrusted
    $playlist_array = $_POST['playlist_array'];
    $source = trim($_POST['source']); // untrusted
    $upload_source = trim($_POST['source']); // untrusted
    $upload = $_FILES["mp3-file"]; //TODO

    $form_valid = True;

    // title is required
    if (empty($title)) {
      $form_valid = False;
      $title_feedback_class = '';
    }

    // artist is required
    if (empty($artist)) {
      $form_valid = False;
      $artist_feedback_class = '';
    }

    // album is required
    if (empty($album)) {
      $form_valid = False;
      $album_feedback_class = '';
    }

    // if file is uploaded:

    if ($upload['error'] == UPLOAD_ERR_OK && is_uploaded_file($upload['tmp_name'])) {
      $upload_filename = basename($upload['name']);
      $upload_ext = strtolower(pathinfo($upload_filename, PATHINFO_EXTENSION));

      // Must upload mp3
      if (!in_array($upload_ext, array('mp3'))) {
        $form_valid = false;
      }

      if (empty($source)) {
        $form_valid = False;
        $source_feedback_class = '';
      }
    }

    // album, artist, title must be unique:
    $records = exec_sql_query(
      $db,
      "SELECT * FROM songs WHERE (title = :title) AND  (artist = :artist) AND  (album = :album);",
      array(
        ':title' => $title,
        ':artist' => $artist,
        ':album' => $album
      )
    )->fetchAll();
    if (count($records) > 1) {
      $form_valid = False;
      $unique_feedback_class = '';
    }

    if ($form_valid) {
      $db->beginTransaction();
      $show_form = False;
      $show_confirmation = True;

      // updates new record into database
      $result = exec_sql_query(
        $db,
        "UPDATE songs SET title=:title, artist=:artist, album=:album, rating=:rating, file_name=:file_name, source=:source WHERE (id=:id) ;",
        array(
          ':title' => $title, // tainted
          ':artist' => $artist, // tainted
          ':album' => $album, // tainted
          ':rating' => $rating, //tainted
          ':file_name' => $upload['name'], //tainted
          ':source' => $upload_source, //tainted
          ':id' => $song_id
        )
      );
      $record_id = $db->lastInsertId('id');

      //remove all mapper values with song_id
      $remove_mapper = exec_sql_query(
        $db,
        "DELETE FROM mapper WHERE song_id=:song_id;",
        array(
          ':song_id' => $song_id, // tainted
        )
      );
      foreach ($playlist_array as $p) {
        $insert_mapper = exec_sql_query(
          $db,
          "INSERT INTO mapper (playlist_id, song_id) VALUES (:playlist_id, :song_id);",
          array(
            ':playlist_id' => $p['id'], // tainted
            ':song_id' => $song_id, // tainted
          )
        );
      }
      // did the insert into the database succeed?
      if ($result) {
        // move the uploaded file to folder:
        $id_filename = 'public/uploads/documents/' . $song_id . '.mp3';
        // Move the file to the uploads/documents folder
        unlink($id_filename);
        move_uploaded_file($upload["tmp_name"], $id_filename);
      }
      //commit changes
      $db->commit();
    } else {
      // form is invalid, set sticky values
      $sticky_title = $title;
      $sticky_artist = $artist;
      $sticky_album = $album;
      $sticky_playlist_array = $playlist_array;
      $sticky_file_name = $file_name;
      $sticky_rating_1 = ($rating == '1' ? 'selected' : '');
      $sticky_rating_2 = ($rating == '2' ? 'selected' : '');
      $sticky_rating_3 = ($rating == '3' ? 'selected' : '');
      $sticky_rating_4 = ($rating == '4' ? 'selected' : '');
      $sticky_rating_5 = ($rating == '5' ? 'selected' : '');
    }
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
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>Song Detail</title>
  <link rel="stylesheet" type="text/css" href="/public/styles/site.css" media="all" />
</head>

<body>
  <?php include("includes/header.php"); ?>

  <main>
    <?php if (!$deleted && !$show_confirmation) { ?>
      <div class="center">
        <h2><?php echo $title ?></h2>
        <p>Album: <?php echo htmlspecialchars($song['album']) ?> </p>
        <p>Artist: <?php echo htmlspecialchars($song['artist']) ?> </p>
        <p>Rating: <?php echo htmlspecialchars($song['rating']) ?> </p>
        <?php
        if (!($song['file_name'] == "" || is_null($song['file_name']))) { ?>
          <p>MP3:<audio controls>
              <source src='..\public\uploads\documents\<?php echo htmlspecialchars($song['id']) ?>.mp3' type="audio/mpeg">
            </audio></p>
        <?php       } ?>
        <p>Playlist(s): <?php
                        $playlist_list = '';
                        foreach ($playlists as $p) {
                          if ($playlist_list == '') {
                            $playlist_list = $p['title'];
                          } else {
                            $playlist_list = $playlist_list . ', ' . $p['title'];
                          }
                        }
                        echo htmlspecialchars($playlist_list) ?> </p>
        <p>Source: <?php echo htmlspecialchars($song['source']) ?> </p>
      </div>
    <?php } ?>
    <div class="center" id="edit_song_form">
      <?php if ($show_confirmation && $is_admin) { ?>
        <h2>Thank you for your edit!</h2>
        <p><strong><?php echo htmlspecialchars($title); ?> has been corrected.</p>
        <p class="add_another_song"><a href="<?php echo htmlspecialchars($url) ?>"> View Song >></a> </p>
      <?php } ?>

      <?php if ($deleted && $is_admin) { ?>
        <h2>Song Deleted!</h2>
        <p>Go to home to find <strong><?php echo htmlspecialchars($title); ?> deleted.</strong></p>
        <p class="add_another_song"><a href="/home"> Go Home >> </a> </p>
      <?php } ?>

      <?php if (!$show_confirmation && $is_admin && !$deleted) { ?>
        <h2>Edit Song:</h2>
        <form id="delete" action="" method="post" novalidate>
          <button type="delete" name="delete">Delete Song</button>
        </form>

        <form id="add_song" action="#song_form" method="post" enctype="multipart/form-data" novalidate>

          <div class="input">
            <label for="add_title">Title:</label>
            <input id="add_title" type="text" name="title" value="<?php echo htmlspecialchars($sticky_title); ?>" required />
          </div>
          <p id="title_feedback" class="feedback <?php echo $title_feedback_class; ?>">Please include a song title.</p>


          <div class="input">
            <label for="add_album">Album:</label>
            <input id="add_album" type="text" name="album" value="<?php echo htmlspecialchars($sticky_album); ?>" required />
          </div>
          <p id="album_feedback" class="feedback <?php echo $album_feedback_class; ?>">Please include a album title.</p>


          <div class="input">
            <label for="add_artist">Artist:</label>
            <input id="add_artist" type="text" name="artist" value="<?php echo htmlspecialchars($sticky_artist); ?>" required />
          </div>
          <p id="artist_feedback" class="feedback <?php echo $artist_feedback_class; ?>">Please include a artist.</p>

          <p id="unique_feedback" class="feedback <?php echo $unique_feedback_class; ?>">Song already exists in table. Insert a new song.</p>

          <div class="input">
            <label for="playlist_array">Add to Playlists:</label>
            <select id="playlist_array" name="playlist_array[]" multiple="multiple">
              <?php
              //Get all playlists:
              $all_playlists = exec_sql_query(
                $db,
                "SELECT * FROM playlists;"
              )->fetchAll();
              foreach ($all_playlists as $p) {
              ?>
                <option value='<?php echo htmlspecialchars($p['id']) ?>' <?php if (in_array($p['id'], $sticky_playlist_array)) { ?> selected <?php } ?>>
                  <?php echo htmlspecialchars($p['title']) ?></option>
              <?php } ?>
            </select>
          </div>


          <div class="group_label_input input">
            <label for="add_rating">Rating:</label>
            <select id="add_rating" name="rating">
              <option label=" "></option>
              <option value='1' <?php echo htmlspecialchars($sticky_rating_1); ?>><?php make_stars(1) ?></option>
              <option value='2' <?php echo htmlspecialchars($sticky_rating_2); ?>><?php make_stars(2) ?></option>
              <option value='3' <?php echo htmlspecialchars($sticky_rating_3); ?>><?php make_stars(3) ?></option>
              <option value='4' <?php echo htmlspecialchars($sticky_rating_4); ?>><?php make_stars(4) ?></option>
              <option value='5' <?php echo htmlspecialchars($sticky_rating_5); ?>><?php make_stars(5) ?></option>
            </select>
          </div>

          <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_FILE_SIZE; ?>" />
          <div class="input">
            <label for="add_file_name">Upload MP3:</label>
            <input id="add_file_name" type="file" name="mp3-file" type='file' accept='.mp3,audio/mpeg' ; ?>
          </div>


          <div class="input">
            <label for="add_source">Source:</label>
            <input id="add_source" type="text" name="source" value="<?php echo htmlspecialchars($sticky_source); ?>" />
          </div>
          <p id="source_feedback" class="feedback <?php echo $source_feedback_class; ?>">Please include a source for uploaded MP3.</p>

          <div class=" submit_button">
            <button type="submit" name="add_song">Edit Song</button>
          </div>
        </form>
      <?php } ?>
    </div>
  </main>
</body>

</html>
