<section id="box-slides" class="carousel slide" data-ride="carousel">

  <div class="carousel-inner">
<?php
  foreach ($slides as $key => $slide) {
    echo '<div class="item'. (($key == 0) ? ' active' : '') .'">' . PHP_EOL;

    if ($slide['link']) {
      echo '<a href="'. functions::escape_html($slide['link']) .'">' . PHP_EOL;
    }

    echo '<img src="'. document::href_rlink($slide['image']) .'" alt="'. functions::escape_html($slide['name']) .'" style="width: 100%;" />' . PHP_EOL;

    if (!empty($slide['caption'])) {
      echo '<div class="carousel-caption">'. $slide['caption'] .'</div>' . PHP_EOL;
    }

    if ($slide['link']) {
      echo '</a>' . PHP_EOL;
    }

    echo '</div>' . PHP_EOL;
  }
?>
  </div>

  <?php if (count($slides) > 1) { ?>
  <ol class="carousel-indicators">
    <?php foreach ($slides as $key => $slide) echo '<li data-target="#box-slides" data-slide-to="'.  $key .'"'. (($key == 0) ? ' class="active"' : '') .'></li>'; ?>
  </ol>

  <a class="left carousel-control" href="#box-slides" data-slide="prev">
    <span class="icon-prev"><?php echo functions::draw_fonticon('fa-chevron-left'); ?></span>
  </a>
  <a class="right carousel-control" href="#box-slides" data-slide="next">
    <span class="icon-next"><?php echo functions::draw_fonticon('fa-chevron-right'); ?></span>
  </a>
  <?php } ?>
</section>
