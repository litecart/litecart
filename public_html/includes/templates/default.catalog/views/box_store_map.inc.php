<?php if (settings::get('store_visiting_address')) { ?>
<div id="box-store-map" class="box">

  <div class="map" style="height: 400px;" class="shadow">
    <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo document::href_link('https://www.google.com/maps', array('q' => settings::get('store_visiting_address'), 'output' => 'svembed')); ?>"></iframe>
  </div>

</div>
<?php } ?>