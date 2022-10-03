<ul class="card breadcrumbs">
<?php
  foreach ($breadcrumbs as $breadcrumb) {
    if (!empty($breadcrumb['link'])) {
      echo '<a class="breadcrumb" href="'. functions::escape_html($breadcrumb['link']) .'">'. $breadcrumb['title'] .'</a>';
    } else {
      echo '<span class="breadcrumb">'. $breadcrumb['title'] .'</span>';
    }
  }
?>
</ul>
