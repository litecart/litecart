<ul class="breadcrumb">
<?php
  foreach ($breadcrumbs as $breadcrumb) {
    if (!empty($breadcrumb['link'])) {
      echo '<li><a class="breadcrumb-item" href="'. functions::escape_html($breadcrumb['link']) .'">'. $breadcrumb['title'] .'</a></li>';
    } else {
      echo '<li><span class="breadcrumb-item">'. $breadcrumb['title'] .'</span></li>';
    }
  }
?>
</ul>
