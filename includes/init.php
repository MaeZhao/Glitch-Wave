<?php
// Nav class variables
$nav_home_page = '';
$nav_playlist_page = '';
$nav_login_page = '';
//ADD LOGIN AND DOCUMENTS

// open connection to database
include_once("includes/db.php");
$db = init_sqlite_db('db/site.sqlite', 'db/init.sql');

// check login/logout
include("includes/sessions.php");
$session_messages = array();
process_session_params($db, $session_messages);

// CHECK WHETHER USER IS ADMIN
define('ADMIN_GROUP_ID', 1);
$is_admin = is_user_member_of($db, ADMIN_GROUP_ID);
process_signup_params($db);
