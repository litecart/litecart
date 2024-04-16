<ul class="breadcrumbs">
  <?php foreach ($breadcrumbs as $breadcrumb) { ?>
  <li class="breadcrumb">
    <?php
      if (!empty($breadcrumb['link'])) {
          echo '<a href="'. functions::escape_html($breadcrumb['link']) .'">'. $breadcrumb['title'] .'</a>';
        } else {
          echo '<span>'. $breadcrumb['title'] .'</span>';
      }
    ?>
  </li>
  <?php } ?>
</ul>
