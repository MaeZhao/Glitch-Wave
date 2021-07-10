
<header class="header horizontal">
  <h1><a href="/">Glitch Wave</a></h1>
  <!-- Navigation Menu -->
  <nav>
    <ul>
      <li class="<?php echo $nav_home_page; ?>"><a href="/"> Music</a>
      </li>
      <li class="<?php echo $nav_playlist_page; ?>"><a href="/playlist"> Playlists </a></li>
      <?php if (!is_user_logged_in()) { ?>
        <li id="nav-login"><a href="/login">Sign In</a></li>
      <?php } else { ?>
        <li id="nav-logout"><a href="<?php echo logout_url(); ?>">Sign Out</a></li>
      <?php } ?>
    </ul>
  </nav>
</header>
