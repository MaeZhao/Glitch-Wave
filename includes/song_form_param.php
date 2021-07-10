<?php
// form values
$title = '';
$artist = '';
$album = '';
$rating = '';
$link = '';
$file_name = '';
$source = '';
$show_confirmation = False;
$show_form = True;

if (isset($_POST['add_song'])) {
  $title = trim($_POST['title']); // untrusted
  $artist = trim($_POST['artist']); // untrusted
  $album = trim($_POST['album']); // untrusted
  $rating = trim($_POST['rating']); // untrusted
  $file_name = trim($_POST['file_name']); // untrusted
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

  //TODO: if file is uploaded:
  if($upload['error']==UPLOAD_ERR_OK){
    $upload_filename=basename($upload['name']);
    $upload_ext = strtolower(pathinfo($upload_filename,PATHINFO_EXTENSION));
    // Must upload mp3
    if (!in_array($upload_ext, array('mp3'))) {
        $form_valid = False;
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
        ':file_name' => $file_name, //tainted
        ':file_ext' => $upload_ext,//tainted
        ':source' => $upload_source//tainted
      )
    );

    // did the insert into the database succeed?
    if ($result) {
      //TODO: move the uploaded file to folder:
        $record_id = $db->lastInsertId('id');
        $id_filename = 'public/uploads/documents/' . $record_id . '.' . $upload_ext;
        // Move the file to the uploads/documents folder
         move_uploaded_file($upload["tmp_name"], $id_filename);
      }
      //commit changes
      $db->commit();
  } else {
    // form is invalid, set sticky values
    $sticky_title = $title;
    $sticky_artist = $artist;
    $sticky_album = $album;
    $sticky_file_name = $file_name;
    $sticky_source= $source;
    $sticky_rating_1 = ($rating == '1' ? 'selected' : '');
    $sticky_rating_2 = ($rating == '2' ? 'selected' : '');
    $sticky_rating_3 = ($rating == '3' ? 'selected' : '');
    $sticky_rating_4 = ($rating == '4' ? 'selected' : '');
    $sticky_rating_5 = ($rating == '5' ? 'selected' : '');
  }
}

?>
