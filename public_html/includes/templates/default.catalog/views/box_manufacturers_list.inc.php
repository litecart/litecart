<div id="box-manufacturers-list" class="box">
  <div class="heading"><h3><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h3></div>
  <div class="content">
    <?php echo functions::form_draw_form_begin('manufacturers_form', 'get', document::ilink('manufacturer')); ?>
      <?php echo functions::form_draw_select_field('manufacturer_id', $options, true, false, 'style="width: 100%;" onchange="this.form.submit()"'); ?>
    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>