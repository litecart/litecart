<?php


Hooks::add_action('render_head', function() {
  echo '<meta name="custom-hook" content="LiteCart Custom Head">';
});