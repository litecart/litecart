<div id="box-slides" class="rslides box shadow">
  <?php foreach($slides as $slide) { ?>
  <div class="slide">
<?php
  if ($slide['link']) {
    echo '<a href="'. htmlspecialchars($slide['link']) .'"><img src="'. $slide['image'] .'" alt="" /></a>';
  } else {
    echo '<img src="'. $slide['image'] .'" alt="" />';
  }

  if (!empty($slide['caption'])) {
    echo '<div class="caption">'. $slide['caption'] .'</div>';
  }
?>
  </div>
  <?php } ?>
</div>

<script>
  $('#box-slides').responsiveSlides({
    speed: 500,       // Integer: Speed of the transition, in milliseconds
    timeout: 5000,    // Integer: Time between slide transitions, in milliseconds
    pause: true,       // Boolean: Pause on hover, true or false
    nav: true,
    prevText: '<?php echo functions::draw_fonticon('fa-chevron-left'); ?>',
    nextText: '<?php echo functions::draw_fonticon('fa-chevron-right'); ?>'
  });
</script>