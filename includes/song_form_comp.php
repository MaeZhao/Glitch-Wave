<?php function make_stars($score)
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


          <div class="input">
            <label for="add_file_name">Upload MP3:</label>
            <input id="add_file_name" type="text" name="mp3-file" type='file' accept='.mp3'; ?>" />
          </div>
<div class="input">
            <label for="add_source">Source:</label>
            <input id="add_source" type="text" name="source" value="<?php echo htmlspecialchars($sticky_source); ?>"/>
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
 <p id="unique_feedback" class="feedback <?php echo $unique_feedback_class; ?>">Song already exists in table. Insert a new song.</p>
