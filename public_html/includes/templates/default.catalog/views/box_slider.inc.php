<div id="slider-wrapper" class="theme-default shadow">
  <div id="slider" class="nivoSlider">
<?php
  foreach($slides as $slide) {
    if ($slide['link']) {
      echo '    <a href="'. htmlspecialchars($slide['link']) .'"><img src="'. $slide['image'] .'" alt="" title="'. $slide['caption'] .'" /></a>' . PHP_EOL;
    } else {
      echo '    <img src="'. $slide['image'] .'" alt="" title="'. htmlspecialchars($slide['caption']) .'" />' . PHP_EOL;
    }
  }
?>
  </div>
</div>
<script>
  $('.nivoSlider').nivoSlider({
    controlNav: false,
    pauseTime: 5000     // How long each slide will show in milliseconds
  });
</script>