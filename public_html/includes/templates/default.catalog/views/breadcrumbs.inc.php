<nav id="breadcrumbs">
  <ul class="list-horizontal">
<?php
  $separator = '';
  foreach ($breadcrumbs as $breadcrumb) {
    if (!empty($breadcrumb['link'])) {
      echo '<li>'. $separator .'<a href="'. htmlspecialchars($breadcrumb['link']) .'">'. $breadcrumb['title'] .'</a></li>';
    } else {
      echo '<li>'. $separator . $breadcrumb['title'] .'</li>';
    }
    $separator = ' &raquo; ';
  }
?>
  </ul>
</nav>