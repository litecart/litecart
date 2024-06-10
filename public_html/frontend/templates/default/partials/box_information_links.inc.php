<?php
  $draw_page = function($page, $page_path, $depth=1) use (&$draw_page) {

    $output = [
      '<li class="page-'. $page['id'] .'">',
      '  <a class="nav-item'. (!empty($page['opened']) ? ' opened' : '') . (!empty($page['active']) ? ' active' : '') .'" href="'. functions::escape_attr($page['link']) .'">'. $page['title'] .'</a>',
    ];

    if (!empty($page['subpages'])) {
      $output[] = '  <ul>';
      foreach ($page['subpages'] as $subpage) {
        echo PHP_EOL . $draw_page($subpage, $page_path, $depth+1);
      }
      $output[] = '  </ul>';
    }

    $output[] = '</li>';

    return implode(PHP_EOL, $output);
  };
?>

<section id="box-information-links">

  <h2 class="title"><?php echo language::translate('title_information', 'Information'); ?></h2>

  <ul class="nav nav-stacked nav-pills">
    <?php foreach ($pages as $page) echo $draw_page($page, $page_path, 0, $draw_page); ?>
  </ul>

</section>