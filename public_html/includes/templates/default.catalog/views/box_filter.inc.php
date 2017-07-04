<div id="box-filter" class="box">
  <?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>

  <?php if ($manufacturers) { ?>
  <div class="manufacturers">
    <h2 class="title"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h2>
    <div class="form-control">
      <ul class="list-unstyled">
        <?php foreach ($manufacturers as $manufacturer) echo '<li><label>'. functions::form_draw_checkbox('manufacturers[]', $manufacturer['id'], true) .' '. $manufacturer['name'] .'</label></li>' . PHP_EOL; ?>
      </ul>
    </div>
  </div>
  <?php } ?>

  <?php if ($product_groups) { ?>
  <?php foreach ($product_groups as $group) { ?>
  <div class="product-groups">
    <div id="product-group-<?php echo $group['id']; ?>">
      <h2 class="title"><?php echo $group['name']; ?></h2>
      <div class="form-control">
        <ul class="list-unstyled">
          <?php foreach ($group['values'] as $value) echo '<li><label>' . functions::form_draw_checkbox('product_groups[]', $group['id'].'-'.$value['id']) .' '. $value['name'].'</label></li>' . PHP_EOL; ?>
        </ul>
      </div>
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