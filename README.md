# Glitch-Wave
Class design project by Mae Zhao from INFO 2300 at Cornell University. Dynamic/responsive site in PHP. Contains design journey in /documents that tracks the design progress of the project.

## Summary:
A simple music documentation, upload and streaming webpage. Upload capabilities include the ability to upload new music, creating an account, and new playlists. Update capabilities include updating Titles, Authors, mp3 files, playlist, etc., of documented music entries. All update and upload capabilities (except for the log-in)are accessed through signing in as the administrator (username/password displayed on login page).
<ul>
    <li>View all capabilities see the project <a href="https://github.com/MaeZhao/Glitch-Wave/new/main?readme=1#all-webpage-capabilities--step-by-step-instructions">README here</a>.</li>
    <li>View the design process containing design sketches, persona outlines, user testing, etc., <a href="https://github.com/MaeZhao/Glitch-Wave/blob/main/documents/design-journey.md">here</a>. 
    </li>
    <li>View the live site <a href="https://young-forest-50901.herokuapp.com/">here</a>, or click on the live image. 
    </li>
</ul>

## All Webpage Capabilities + Step-by-Step Instructions
Viewing all entries:
1. Go to /home or click on the Music link in navigation

View all entries for a tag:
1. Go to /playlist or click on Playlist link in navigation

View a single entry and all the tags for that entry:
1. Click on song title in table located in /home

How to insert and upload a new entry:
1. Go to /home
2. Insert Title
3. Insert Album
4. Insert Artist
5. Make sure the three values above are unique/are filled in
6. Add playlists if needed
7. Add rating if needed
8. Upload MP3 if needed
9. If you uploaded MP3 make sure to upload a Source
10. Click "Add Song"

How to delete an entry:
1. Go to /home
2. Select desired entry
3. Click "Delete Song"

How to view all tags at once:
1. Go to /laylist

How to add a tag to an existing entry:
1. Go to /home
2. Select existing tags plus the tag you wish to add
3. Click "Edit Song"

How to remove a tag from an existing entry:
1. Go to /home
2. Select existing tags minus the tag you wish to add
3. Click "Edit Song"
