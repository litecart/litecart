<?php
  $draw_page = function($page, $page_path, $depth=1) use (&$draw_page) {
    echo '<li class="page-'. $page['id'] . (!empty($page['opened']) ? ' opened' : '') . (!empty($page['active']) ? ' active' : '') .'">' . PHP_EOL
       . '  <a href="'. functions::escape_html($page['link']) .'">'. $page['title'] .'</a>' . PHP_EOL;
    if (!empty($page['subpages'])) {
      echo '  <ul class="nav nav-pills nav-stacked">' . PHP_EOL;
      foreach ($page['subpages'] as $subpage) {
        echo PHP_EOL . $draw_page($subpage, $page_path, $depth+1);
      }
      echo '  </ul>' . PHP_EOL;
    }
    echo '</li>' . PHP_EOL;
  };
?>

<section id="box-information-links" class="card">

  <div class="card-header">
    <h2 class="card-title"><?php echo language::translate('title_information', 'Information'); ?></h2>
  </div>

  <div class="card-body">
    <ul class="nav nav-stacked nav-pills">
      <?php foreach ($pages as $page) $draw_page($page, $page_path, 0, $draw_page); ?>
    </ul>
  </div>

</section>