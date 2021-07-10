<?php
$title = "login";
$nav_login_page = "current_page";
include("includes/init.php");
// Handle user sign up
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>Sign In</title>
  <link rel="stylesheet" type="text/css" href="/public/styles/site.css" media="all" />
</head>

<body>
  <?php include("includes/header.php"); ?>

  <main>
    <div class="center">
      <?php if (!is_user_logged_in()) { ?>
        <h2>Log In</h2>
        <p class="hint"><span class="slant">*Admin username: kyle*</span></p>
        <p class="hint"><span class="slant">*Admin password: monkey*</span></p>
        <?php echo_login_form('/login', $session_messages); ?>
    </div>
    <div class="center">
      <h2>Sign up</h2>
    <?php
        echo_signup_form('/login', $session_messages);
      } else {
        header("Location: /"); // Redirect users
      } ?>
    </div>
  </main>
</body>

</html>
