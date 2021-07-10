<?php
$title = "Home";
$nav_home_page = "current_page";
define("MAX_FILE_SIZE", 10000000);
include("includes/init.php");

// $db = open_sqlite_db("db/catalog.sqlite");

// ---Search and Sort---

// SQL SELECT querys
$sql_select_query = 'SELECT * FROM songs';
$sql_select_params = array();
$sql_has_where = False;
$sql_has_search = False;

// Search:

$search_terms = NULL;
// Search sticky
$sticky_search = '';
//  If search is made:
if (isset($_GET['q'])) {

  $search_terms = trim($_GET['q']); // untrusted


  if (empty($search_terms)) {
    $search_terms = NULL;
  }

  //set search sticky:
  $sticky_search = $search_terms; // tainted


  // search SQL
  if ($search_terms) {
    // sql clauses
    $sql_select_query = $sql_select_query . " WHERE ((title LIKE '%' || :search || '%') OR (album LIKE '%' || :search || '%') OR (artist LIKE '%' || :search || '%')  OR (source LIKE '%' || :search || '%'))";
    $sql_select_params = array(':search' => $search_terms);
  }
}

// Sort

$sort = NULL;
if (isset($_GET['sort'])) {
  $sort = $_GET['sort']; // untrusted

}

$order = NULL;
if (isset($_GET['order'])) {
  $order = $_GET['order']; // untrusted

}
// False == hidden True == active
$sort_css_buttons = array(
  'title_asc' => False,
  'artist_asc' => False,
  'album_asc' => False,
  'rating_asc' => False,
  'source_asc' => False,
  'title_desc' => False,
  'artist_desc' => False,
  'album_desc' => False,
  'rating_desc' => False,
  'source_desc' => False
);

// next sort URL query
$order_next_url = array(
  'title' => 'asc',
  'artist' => 'asc',
  'album' => 'asc',
  'rating' => 'asc',
  'source' => 'asc'
);

// switch between asc and desc
if ($order == 'asc') {
  $order_sql = 'ASC';
  $order_next = 'desc';

  $filter_icon = 'up';
} else if ($order == 'desc') {
  $order_sql = 'DESC';
  $order_next = NULL;

  $filter_icon = 'down';
} else {
  $order = NULL;
  $sort = NULL;
}


// ORDER BY SQL Calls
$table_param = array('title', 'artist', 'rating', 'album', 'source');
if (in_array($sort, $table_param)) {
  // sort for the appropriate field
  foreach ($table_param as $param) {
    if ($sort == $param) {
      $sql_select_query = $sql_select_query . " ORDER BY " . $param . " " .  $order_sql;
      break;
    }
  }

  // URL of asc/desc switching
  $order_next_url[$sort] = $order_next;

  // filter icon direction:
  if ($filter_icon == "up") {
    $sort_css_buttons[$sort . '_asc'] = True;
    $sort_css_buttons[$sort . '_desc'] = False;
  } else if ($filter_icon == "down") {
    $sort_css_buttons[$sort . '_asc'] = False;
    $sort_css_buttons[$sort . '_desc'] = True;
  }
} else {
  $sort = NULL;
}

// URL string parameter query:
$sort_url = '/home?';

// Filter and Search:
$sort_query_string = http_build_query(
  array(
    'q' => $search_terms
  )
);


// glue query string to URL using ?
$sort_url = $sort_url . $sort_query_string;

// Adding Songs:
// feedback message CSS classes
$title_feedback_class = 'hidden';
$artist_feedback_class = 'hidden';
$album_feedback_class = 'hidden';
$unique_feedback_class = 'hidden';
$source_feedback_class = 'hidden';

// form values
$title = '';
$artist = '';
$album = '';
$rating = '';
$file_name = '';
$source = '';
//playlist array
$playlist_array = [];
$show_confirmation = False;
$show_form = True;
$upload_filename = NULL;
$upload_ext = NULL;


// sticky values
$sticky_title = '';
$sticky_artist = '';
$sticky_album = '';
$sticky_file_name = '';
$sticky_source = '';
$sticky_playlist_array = [];
$sticky_rating_1 = '';
$sticky_rating_2 = '';
$sticky_rating_3 = '';
$sticky_rating_4 = '';
$sticky_rating_5 = '';


if (isset($_POST['add_song'])) {
  $title = trim($_POST['title']); // untrusted
  $artist = trim($_POST['artist']); // untrusted
  $album = trim($_POST['album']); // untrusted
  $rating = trim($_POST['rating']); // untrusted
  $playlist_array = $_POST['playlist_array'];
  $file_name = trim($_POST['file_name']); // untrusted  TODO
  $file_ext = trim($_POST['file_ext']); // untrusted
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
      debug_log("extensions wrong");
      debug_log(strval($upload['type']));
      debug_log(strval($upload['name']));
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
  if (count($records) > 0) {
    $form_valid = False;
    $unique_feedback_class = '';
  }

  if ($form_valid) {
    $db->beginTransaction();
    $show_form = False;
    $show_confirmation = True;

    // insert new record into database
    $result = exec_sql_query(
      $db,
      "INSERT INTO songs (title, artist, album, rating, file_name,file_ext,source) VALUES (:title, :artist, :album, :rating, :file_name,:file_ext,:source);",
      array(
        ':title' => $title, // tainted
        ':artist' => $artist, // tainted
        ':album' => $album, // tainted
        ':rating' => $rating, //tainted
        ':file_name' => $upload['name'], //tainted
        ':file_ext' => $upload_ext, //tainted
        ':source' => $upload_source //tainted
      )
    );
    $record_id = $db->lastInsertId('id');

    foreach ($playlist_array as $p) {
      $insert_mapper = exec_sql_query(
        $db,
        "INSERT INTO mapper (playlist_id, song_id) VALUES (:playlist_id, :song_id);",
        array(
          ':playlist_id' => $p['id'], // tainted
          ':song_id' => $record_id, // tainted
        )
      );
    }

    // did the insert into the database succeed?
    if ($result) {
      // move the uploaded file to folder:
      $id_filename = 'public/uploads/documents/' . $record_id . '.mp3';
      // Move the file to the uploads/documents folder
      if (move_uploaded_file($upload["tmp_name"], $id_filename)) {
        debug_log("fucking uploaded");
      } else {
        debug_log($id_filename);
        debug_log(strval($upload_ext));
        debug_log("not fucking uploaded");
      }
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

  <title>Music</title>
  <link rel="stylesheet" type="text/css" href="/public/styles/site.css" media="all" />
</head>



<body>
  <?php include("includes/header.php"); ?>

  <main>
    <div class="center">
      <form class="search" action="/home" method="get" novalidate>
        <div class="search_bar">
          <input aria-label="Search" placeholder="Search" class="search_input" type="text" name="q" id="q" required value="<?php echo htmlspecialchars($sticky_search); ?>" />


          <button id="search_button" type="submit">Search</button>
        </div>

        <!-- Sorting -->
        <input type="hidden" name="sort" value="<?php echo $sort; ?>" />
        <input type="hidden" name="order" value="<?php echo $order; ?>" />
      </form>

      <table class="center">
        <tr class="table_head">
          <th><a href="<?php echo $sort_url . '&amp;sort=title&amp;order=' . $order_next_url['title']; ?>"> Title <?php if ($sort_css_buttons['title_asc']) {
                                                                                                                  ?>&#8638;<?php
                                                                                                                          } elseif ($sort_css_buttons['title_desc']) { ?>&#8643;<?php
                                                                                                                                                                              } elseif (!($sort_css_buttons['title_asc'] && $sort_css_buttons['title_desc'])) { ?>&#8638;&#8643;<?php } ?></a></th>

          <th><a href="<?php echo $sort_url . '&amp;sort=artist&amp;order=' . $order_next_url['artist']; ?>"> Artist <?php if ($sort_css_buttons['artist_asc']) {
                                                                                                                      ?>&#8638;<?php
                                                                                                                              } elseif ($sort_css_buttons['artist_desc']) { ?>&#8643;<?php
                                                                                                                                                                                    } elseif (!($sort_css_buttons['artist_asc'] && $sort_css_buttons['artist_desc'])) { ?>&#8638;&#8643;<?php } ?></a></th>

          <th><a href="<?php echo $sort_url . '&amp;sort=album&amp;order=' . $order_next_url['album']; ?>"> Album <?php if ($sort_css_buttons['album_asc']) {
                                                                                                                  ?>&#8638;<?php
                                                                                                                          } elseif ($sort_css_buttons['album_desc']) { ?>&#8643;<?php
                                                                                                                                                                              } elseif (!($sort_css_buttons['album_asc'] && $sort_css_buttons['album_desc'])) { ?>&#8638;&#8643;<?php } ?></a></th>
          <th class="rating"><a href="<?php echo $sort_url . '&amp;sort=rating&amp;order=' . $order_next_url['rating']; ?>"> Rating <?php if ($sort_css_buttons['rating_asc']) {
                                                                                                                                    ?>&#8638;<?php
                                                                                                                                            } elseif ($sort_css_buttons['rating_desc']) { ?>&#8643;<?php
                                                                                                                                                                                                  } elseif (!($sort_css_buttons['rating_asc'] && $sort_css_buttons['rating_desc'])) { ?>&#8638;&#8643;<?php } ?></a></th>

          <th class="links"><a href="<?php echo $sort_url . '&amp;sort=source&amp;order=' . $order_next_url['source']; ?>"> Source <?php if ($sort_css_buttons['source_desc']) {
                                                                                                                                    ?>&#8638;<?php
                                                                                                                                            } elseif ($sort_css_buttons['source_asc']) { ?>&#8643;<?php
                                                                                                                                                                                                } elseif (!($sort_css_buttons['source_desc'] && $sort_css_buttons['source_asc'])) { ?>&#8638;&#8643;<?php } ?></a></th>
        </tr>
        <?php
        // the catalog records query
        $records = exec_sql_query(
          $db,
          $sql_select_query,
          $sql_select_params
        )->fetchAll();

        foreach ($records as $record) { ?>
          <tr>
            <td><a href="/home/song?<?php echo http_build_query(array('id' => $record['id'])); ?>"><?php echo htmlspecialchars($record["title"]); ?></a></td>
            <td><?php echo htmlspecialchars($record["artist"]); ?></td>
            <td><?php echo htmlspecialchars($record["album"]); ?></td>
            <td class="rating_item"><?php make_stars((int)$record["rating"]);
                                    ?></td>
            <td class="links_item"><?php
                                    if (!empty($record["source"])) {
                                    ?><a href="<?php echo htmlspecialchars($record["source"]); ?>"><?php echo htmlspecialchars($record["source"]); ?></a> <?php } ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>



    <div class="center" id="song_form">


      <?php if ($show_confirmation && $is_admin) { ?>
        <h2>Thank you for your submission!</h2>
        <p>See table above to find <strong><?php echo htmlspecialchars($title); ?> by <strong><?php echo htmlspecialchars($artist); ?> from <strong><?php echo htmlspecialchars($album); ?></strong></p>
        <p class="add_another_song"><a href="home"> Add Another Song</a> </p>
      <?php } ?>

      <?php if ($show_form && $is_admin) { ?>
        <h2>Add Song:</h2>
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
            <button type="submit" name="add_song">Add Song</button>
          </div>
        </form>
      <?php } ?>
    </div>
  </main>

</body>

</html>
