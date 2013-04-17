<?php
  $system->document->snippets['head_tags']['nivo-slider'] = '<script src="'. WS_DIR_EXT .'nivo-slider/jquery.nivo.slider.pack.js"></script>' . PHP_EOL
                                                         . '<link rel="stylesheet" href="'. WS_DIR_EXT .'nivo-slider/nivo-slider.css" media="screen" />' . PHP_EOL
                                                         . '<link rel="stylesheet" href="'. WS_DIR_EXT .'nivo-slider/themes/default/default.css" media="screen" />';
?>
<div id="slider-wrapper" class="theme-default">
  <div id="slider" class="nivoSlider">
    <img src="<?php echo WS_DIR_EXT; ?>nivo-slider/demo/images/toystory.jpg" data-thumb="<?php echo WS_DIR_EXT; ?>nivo-slider/demo/images/toystory.jpg" alt="" />
    <a href="http://dev7studios.com"><img src="<?php echo WS_DIR_EXT; ?>nivo-slider/demo/images/up.jpg" data-thumb="<?php echo WS_DIR_EXT; ?>nivo-slider/demo/images/up.jpg" alt="" title="This is an example of a caption" /></a>
    <img src="<?php echo WS_DIR_EXT; ?>nivo-slider/demo/images/walle.jpg" data-thumb="<?php echo WS_DIR_EXT; ?>nivo-slider/demo/images/walle.jpg" alt="" data-transition="slideInLeft" />
    <img src="<?php echo WS_DIR_EXT; ?>nivo-slider/demo/images/nemo.jpg" data-thumb="<?php echo WS_DIR_EXT; ?>nivo-slider/demo/images/nemo.jpg" alt="" title="#htmlcaption" />
  </div>
  <div id="htmlcaption" class="nivo-html-caption">
    <strong>This</strong> is an example of a <em>HTML</em> caption with <a href="#">a link</a>. 
  </div>
</div>
<script type="text/javascript">
$(window).load(function() {
    $('#slider').nivoSlider({
      controlNav: false
    });
});
</script>