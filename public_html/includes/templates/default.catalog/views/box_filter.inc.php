<div id="box-filter" class="box">
  <?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>
  
  <?php if ($manufacturers) { ?>
  <div class="manufacturers">
    <h3 class="title" style="margin-bottom: 0px;"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h3>
    <div class="input-wrapper" style="display: block; color: inherit;">
      <ul class="list-vertical">
        <?php foreach ($manufacturers as $manufacturer) echo '<li><label>'. functions::form_draw_checkbox('manufacturers[]', $manufacturer['id'], true) .' '. $manufacturer['name'] .'</label> <a href="'. document::href_ilink('manufacturer', array('manufacturer_id' => $manufacturer['id'])) .'">&raquo;</a></li>' . PHP_EOL; ?>
      </ul>
    </div>
    <script>
      $("form[name='filter_form'] input[name='manufacturers[]']").click(function(){
        $(this).closest("form").submit();
      });
    </script>
  </div>
  <?php } ?>
  
  <?php if ($product_groups) { ?>
  <?php foreach ($product_groups as $group) { ?>
  <div class="product-groups">
    <div id="product-group-<?php echo $group['id']; ?>">
      <h3 class="title" style="margin-bottom: 0px;"><?php echo $group['name']; ?></h3>
      <div class="input-wrapper" style="display: block; color: inherit;">
        <ul class="list-vertical">
          <?php foreach ($group['values'] as $value) echo '<li><label>' . functions::form_draw_checkbox('product_groups[]', $group['id'].'-'.$value['id']) .' '. $value['name'].'</label></li>' . PHP_EOL; ?>
        </ul>
        <script>
          $("form[name='filter_form'] input[name='product_groups[]']").click(function(){
            $(this).closest("form").submit();
          });
        </script>
      </div>
    </div>
  </div>
  <?php } ?>
  <?php } ?>
  
  <?php echo functions::form_draw_form_end(); ?>
</div>