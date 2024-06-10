<ul class="breadcrumb">
<?php
  foreach ($breadcrumbs as $breadcrumb) {
    if (!empty($breadcrumb['link'])) {
			echo '<li><a href="'. functions::escape_attr($breadcrumb['link']) .'">'. $breadcrumb['title'] .'</a></li>';
    } else {
      echo '<li>'. $breadcrumb['title'] .'</li>';
    }
  }
?>
</ul>
