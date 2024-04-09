<section id="box-checkout-payment">
  <h2 class="title"><?php echo language::translate('title_payment', 'Payment'); ?></h2>

  <div class="options btn-group-vertical">

    <?php foreach ($options as $module) foreach ($module['options'] as $option) { ?>
    <label class="option btn btn-default btn-block<?php if (!empty($selected['id']) && $selected['id'] == $module['id'].':'.$option['id']) echo ' active'; ?><?php if (!empty($option['error'])) echo ' disabled'; ?>">
      <?php echo functions::form_radio_button('payment[option_id]', $module['id'].':'.$option['id'], !empty($selected['id']) ? $selected['id'] : '', 'style="display: none;"'. (!empty($option['error'])) ? ' disabled' : ''); ?>
      <div class="header row" style="margin: 0;">
        <div class="col-xs-3 thumbnail" style="margin: 0;">
          <?php echo functions::draw_thumbnail('storage://' . $option['icon'], 140, 60, 'fit'); ?>
        </div>

        <div class="col-xs-9 text-start" style="padding-bottom: 0;">
          <div class="title"><?php echo $module['title']; ?></div>
          <div class="name"><?php echo $option['name']; ?></div>
          <div class="price"><?php if (empty($option['error']) && (float)$option['cost'] != 0) echo '+ ' . currency::format(tax::get_price($option['cost'], $option['tax_class_id'])); ?></div>
          <?php if (!empty($option['error'])) { ?>
          <div class="error"><?php echo $option['error']; ?></div>
          <?php } ?>
        </div>
      </div>

      <?php if (empty($option['error']) && (!empty($option['description']) || !empty($option['fields']))) { ?>
      <div class="content">
        <hr />

        <?php if (!empty($option['description'])) { ?>
        <p class="description text-start"><?php echo $option['description']; ?></p>
        <?php } ?>

        <?php if (!empty($option['fields'])) { ?>
        <div class="fields text-start"><?php echo $option['fields']; ?></div>
        <?php } ?>
      </div>
      <?php } ?>
    </label>
    <?php } ?>

  </div>
</section>
