<style>
input[name="payment_option[id]"]:checked + .option::after {
  content: '<?php echo language::translate('title_selected', 'Selected'); ?>';
}
</style>

<section id="box-checkout-payment">
  <div class="card-header">
    <h2 class="card-title"><?php echo language::translate('title_payment', 'Payment'); ?></h2>
  </div>

  <div class="card-body">
    <div class="options">

      <?php foreach ($options as $option) { ?>
      <label class="option-wrapper">
        <input name="payment_option[id]" value="<?php echo $option['id']; ?>" type="radio" hidden <?php if (!empty($selected) && $selected['id'] == $option['id']) echo ' checked'; ?><?php if (!empty($option['error'])) echo ' disabled'; ?>>
        <div class="option">
          <div class="header row" style="margin: 0;">
            <div class="col-3" style="margin: 0;">
              <?php echo functions::draw_thumbnail('storage://' . $option['icon'], 160, 80, 'fit'); ?>
            </div>

            <div class="col-9" style="align-self: center;">
              <div class="name"><?php echo $option['name']; ?></div>

              <?php if (!empty($option['description'])) { ?>
              <div class="description"><?php echo $option['description']; ?></div>
              <?php } ?>

              <div class="price"><?php if (empty($option['error']) && $option['fee'] != 0) echo '+ ' . currency::format(tax::get_price($option['fee'], $option['tax_class_id'])); ?></div>
              <?php if (!empty($option['error'])) { ?>
              <div class="error"><?php echo $option['error']; ?></div>
              <?php } ?>
            </div>
          </div>

          <?php if (empty($option['error']) && !empty($option['fields'])) { ?>
          <div class="content">
            <hr>
            <div class="fields text-start"><?php echo $option['fields']; ?></div>
          </div>
          <?php } ?>
        </div>
      </label>
      <?php } ?>
    </div>

  </div>
</section>

<script>
  $(':input[name="payment_option[id]"]:not(:checked) .option :input').prop('disabled', true);

// Payment Form: Process Data

  $(':input[name="payment_option[id]"]').on('change', '', function(e){

    $('input[name="payment_option[id]"]:not(:checked) + .option :input').prop('disabled', true);
    $(this).next('.option').find(':input').prop('disabled', false);

    let formdata = $(this).closest('.option-wrapper :input').serialize();

    $('#box-checkout').trigger('update', [{component: 'payment', data: formdata + '&select_payment=true', refresh: false}])
                      .trigger('update', [{component: 'summary', refresh: true}]);
  });
</script>