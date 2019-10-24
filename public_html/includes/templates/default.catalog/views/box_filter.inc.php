<section id="box-filter" class="box">
  <?php echo functions::form_draw_form_begin('filter_form', 'get'); ?>

    <?php if ($manufacturers) { ?>
    <div class="box manufacturers">
      <h2 class="title"><?php echo language::translate('title_manufacturers', 'Manufacturers'); ?></h2>
      <div class="form-control">
        <ul class="list-unstyled">
          <?php foreach ($manufacturers as $manufacturer) echo '<li><label>'. functions::form_draw_checkbox('manufacturers[]', $manufacturer['id'], true) .' '. $manufacturer['name'] .'</label></li>' . PHP_EOL; ?>
        </ul>
      </div>
    </div>
    <?php } ?>

    <?php if ($attributes) { ?>
    <div class="box attributes">
      <?php foreach ($attributes as $group) { ?>
      <div class="group">
        <h2><?php echo $group['name']; ?></h2>
        <?php if (!empty($group['select_multiple'])) { ?>
        <div class="form-control">
          <?php foreach ($group['values'] as $value) { ?>
          <div>
            <label><?php echo functions::form_draw_checkbox('attributes['. $group['id'] .'][]', !empty($value['id']) ? $value['id'] : $value['value'], true); ?> <?php echo $value['value']; ?></label>
          </div>
          <?php } ?>
        </div>
        <?php } else { ?>
<?php
  $options = array(array('-- '. language::translate('title_select', 'Select') . ' --', ''));
  foreach ($group['values'] as $value) {
    $options[] = array($value['value'], $value['id']);
  }
  echo functions::form_draw_select_field('attributes['. $group['id'] .'][]', $options, true);
?>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
    <?php } ?>

  <?php echo functions::form_draw_form_end(); ?>
</section>

<script>
  $('form[name="filter_form"]').change(function(){
    var url = new URL(location.protocol + '//' + location.host + location.pathname + '?' + $('form[name="filter_form"]').serialize() + '&sort=<?php echo isset($_GET['sort']) ? $_GET['sort'] : ''; ?>');
    //window.location = url.href;
    $("#content").load(url.href + ' #content');
  });
</script>