<div id="slider-wrapper" class="shadow">
  <ul id="slider" style="listing-style: none; padding: 0;">
<?php
  foreach($slides as $slide) {
    if ($slide['link']) {
      echo '    <li><a href="'. htmlspecialchars($slide['link']) .'"><img src="'. $slide['image'] .'" alt="" /></a>'. (!empty($slide['caption']) ? '<div class="caption">'. $slide['caption'] .'</div>' : '') .'</li>' . PHP_EOL;
    } else {
      echo '    <li><img src="'. $slide['image'] .'" alt="" />'. (!empty($slide['caption']) ? '<div class="caption">'. $slide['caption'] .'</div>' : '') .'</li>' . PHP_EOL;
    }
  }
?>
  </ul>
</div>

<script>
  $('ul#slider').responsiveSlides({
    speed: 500,       // Integer: Speed of the transition, in milliseconds
    timeout: 5000,    // Integer: Time between slide transitions, in milliseconds
    pause: true,       // Boolean: Pause on hover, true or false
    nav: true,
    prevText: '<?php echo functions::draw_fonticon('fa-chevron-left'); ?>',
    nextText: '<?php echo functions::draw_fonticon('fa-chevron-right'); ?>'
  });
</script>