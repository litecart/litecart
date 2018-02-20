<div id="box-filter">
  <?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>

  <?php if (count($manufacturers) > 1) { ?>
  <div class="box manufacturers">
    <h2 class="title"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h2>
    <div class="form-control">
      <ul class="list-unstyled">
        <?php foreach ($manufacturers as $manufacturer) echo '<li><label>'. functions::form_draw_checkbox('manufacturers[]', $manufacturer['id'], true) .' '. $manufacturer['name'] .'</label></li>' . PHP_EOL; ?>
      </ul>
    </div>
  </div>
  <?php } ?>

  <?php if (count($product_groups) > 0) { ?>
  <?php foreach ($product_groups as $group) { ?>
  <div class="box product-group" data-id="<?php echo $group['id']; ?>">
    <h2 class="title"><?php echo $group['name']; ?></h2>
    <div class="form-control">
      <ul class="list-unstyled">
        <?php foreach ($group['values'] as $value) echo '<li><label>' . functions::form_draw_checkbox('product_groups[]', $group['id'].'-'.$value['id']) .' '. $value['name'].'</label></li>' . PHP_EOL; ?>
      </ul>
    </div>
  </div>
  <?php } ?>
  <?php } ?>

  <?php echo functions::form_draw_form_end(); ?>
</div>

<script>
  $('form[name="filter_form"] input[name="manufacturers[]"]').click(function(){
    $(this).closest('form').submit();
  });

  $('form[name="filter_form"] input[name="product_groups[]"]').click(function(){
    $(this).closest('form').submit();
  });
</script>
