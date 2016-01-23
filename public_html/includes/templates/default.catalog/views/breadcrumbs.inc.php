<nav id="breadcrumbs">
  <ul class="list-horizontal">
<?php
  $separator = '';
  foreach ($breadcrumbs as $breadcrumb) {
    if (!empty($breadcrumb['link'])) {
      echo '<li>'. $separator .'<a href="'. htmlspecialchars($breadcrumb['link']) .'">'. htmlspecialchars($breadcrumb['title']) .'</a></li>';
    } else {
      echo '<li>'. $separator . htmlspecialchars($breadcrumb['title']) .'</li>';
    }
    $separator = ' &raquo; ';
  }
?>
  </ul>
</nav>