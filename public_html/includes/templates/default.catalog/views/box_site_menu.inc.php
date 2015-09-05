<nav id="site-menu" class="twelve-eighty">
  <?php
    if (!function_exists('custom_draw_site_menu')) {
      function custom_draw_site_menu($items, $indent=0) {
        echo '<ul>' . PHP_EOL;
        foreach ($items as $item) {
          echo '  <li class="'. $item['type'] .'-'. $item['id'] .'"><a href="'. htmlspecialchars($item['link']) .'">'. $item['title'] .'</a>';
          if (!empty($item['subitems'])) {
            echo PHP_EOL . custom_draw_site_menu($item['subitems'], $indent+1);
          }
          echo '  </li>' . PHP_EOL;
        }
        echo '</ul>' . PHP_EOL;
      }
    }
    custom_draw_site_menu($items);
  ?>
</nav>