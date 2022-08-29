<ul class="card breadcrumbs">
<?php
  foreach ($breadcrumbs as $breadcrumb) {
    if (!empty($breadcrumb['link'])) {
      echo '<li class="breadcrumb"><a href="'. functions::escape_html($breadcrumb['link']) .'">'. $breadcrumb['title'] .'</a></li>';
    } else {
      echo '<li class="breadcrumb"><span>'. $breadcrumb['title'] .'</span></li>';
    }
  }
?>
</ul>
