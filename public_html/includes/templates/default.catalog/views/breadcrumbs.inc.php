<nav id="breadcrumbs">
  <ul class="list-horizontal">
<?php
  $separator = '';
  foreach ($breadcrumbs as $breadcrumb) {
    echo '<li>'. $separator .'<a href="'. htmlspecialchars($breadcrumb['link']) .'">'. $breadcrumb['title'] .'</a></li>';
    $separator = ' &raquo; ';
  }
?>
  </ul>
</nav>