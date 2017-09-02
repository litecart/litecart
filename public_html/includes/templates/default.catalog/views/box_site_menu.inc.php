<?php
  if (!function_exists('custom_draw_site_menu_item')) {
    function custom_draw_site_menu_item($item, $indent=0) {
      $output = '<li data-type="'. $item['type'] .'" data-id="'. $item['id'] .'">'
              . '  <a href="'. htmlspecialchars($item['link']) .'">'. $item['title'] .'</a>';
      if (!empty($item['subitems'])) {
        $output .= '  <ul>' . PHP_EOL;
        foreach ($item['subitems'] as $subitem) {
          $output .= custom_draw_site_menu_item($subitem, $indent+1);
        }
        $output .= '  </ul>' . PHP_EOL;
      }
      $output .= '</li>' . PHP_EOL;
      return $output;
    }
  }
?>
<nav id="site-menu" class="twelve-eighty">
  <ul>
    <li class="home"><a href="<?php echo document::href_ilink(''); ?>"><?php echo functions::draw_fonticon('fa-home'); ?></a></li>
    <?php foreach ($categories as $item) echo custom_draw_site_menu_item($item); ?>
    <?php foreach ($pages as $item) echo custom_draw_site_menu_item($item); ?>
  </ul>
</nav>