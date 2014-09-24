<?php
  if (!isset($tweets->errors)) {
    foreach ($tweets as $tweet):
      $date = new DateTime($tweet->created_at); ?>

      <li class="tweet">
        <p><?php echo $tweet->text; ?></p>
        <span class="freight italic">tweeted <?php echo $date->format('j M'); ?></span>
      </li>

    <?php endforeach?>

      <li class="links">
        <a href="https://twitter.com/<?php the_field('twitter'); ?>" class="gotham medium more" target="_blank">Follow Us on Twitter</a>
      </li>

  <?php } else { ?>
    <p>Sorry no tweets.</p>
  <?php } ?>