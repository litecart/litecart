<style>
#slider-wrapper {
  position: relative;
  margin-bottom: 20px;
}

.rslides_nav {
  position: absolute;
  z-index: 99;
  padding: 0px 20px;
  margin-top: -1em;

  font-size: 2em;
  color: #000 !important;
  text-shadow: 1px 1px #fff;

  opacity: 0;
  -moz-transition: opacity .1s ease-in;
  -o-transition: opacity .1s ease-in;
  -webkit-transition: opacity .1s ease-in;
  transition: opacity .1s ease-in;
}
#slider-wrapper:hover .rslides_nav {
  opacity: 0.25;
}
#slider-wrapper:hover .rslides_nav:hover {
  opacity: 0.5;
}

.rslides_nav.prev {
  left: 0px;
  top: 50%;
}

.rslides_nav.next {
  position: absolute;
  right: 0px;
  top: 50%;
}

.rslides li .caption {
  position: absolute;
  width: 100%;
  padding: 10px;
  bottom: 0px;

  background: rgba(0,0,0,0.5);
  color: #fff;
  text-shadow: -1px -1px #000;
  font-size: 1.5em;
  opacity: 0.5;
}

@media (max-width: 640px) { /* WVGA 480x800 and lower */
  .rslides li .caption {
    font-size: 1em;
  }
}
</style>

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