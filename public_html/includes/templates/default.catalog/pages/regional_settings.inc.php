<?php
  $currency_options = [['-- '. language::translate('title_select', 'Select') .' --', '']];
  foreach ($currencies as $currency) {
    $currency_options[] = [$currency['name'], $currency['code']];
  }

  $language_options = [['-- '. language::translate('title_select', 'Select') .' --', '']];
  foreach ($languages as $language) {
    $language_options[] = [$language['name'], $language['code']];
  }
?>
<div class="fourteen-forty container">

  <main id="content">
    {snippet:notices}

    <section id="box-regional-settings" class="card">
      <div class="card-header">
        <h1 class="card-title"><?php echo language::translate('title_regional_settings', 'Regional Settings'); ?></h1>
      </div>

      <div class="card-body">
        <?php echo functions::form_draw_form_begin('region_form', 'post', document::ilink(), false, 'style="max-width: 480px;"'); ?>

          <div class="row">
            <?php if (count($languages) > 1) { ?>
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_language', 'Language'); ?></label>
              <?php echo functions::form_draw_select_field('language_code', $language_options, language::$selected['code']); ?>
            </div>
            <?php } ?>

            <?php if (count($currencies) > 1) { ?>
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_currency', 'Currency'); ?></label>
              <?php echo functions::form_draw_select_field('currency_code', $currency_options, currency::$selected['code']); ?>
            </div>
            <?php } ?>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_country', 'Country'); ?></label>
              <?php echo functions::form_draw_countries_list('country_code', customer::$data['country_code']); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
              <?php echo functions::form_draw_zones_list(customer::$data['country_code'], 'zone_code', customer::$data['zone_code']); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_postcode', 'Postal Code'); ?></label>
              <?php echo functions::form_draw_text_field('postcode', customer::$data['postcode']); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_display_prices_including_tax', 'Display Prices Including Tax'); ?></label>
              <?php echo functions::form_draw_toggle('display_prices_including_tax', customer::$data['display_prices_including_tax'], 'y/n'); ?>
            </div>
          </div>

          <p>
            <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save')); ?>
          </p>

        <?php echo functions::form_draw_form_end(); ?>
      </div>
    </section>
  </main>

</div>

<script>
$('select[name="country_code"]').change(function(){

  if ($(this).find('option:selected').data('postcode-format')) {
    $('input[name="postcode"]').attr('pattern', $(this).find('option:selected').data('postcode-format'));
  } else {
    $('input[name="postcode"]').removeAttr('pattern');
  }

  $.ajax({
    url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
    type: 'get',
    cache: true,
    async: true,
    dataType: 'json',
    error: function(jqXHR, textStatus, errorThrown) {
      if (console) console.warn(errorThrown.message);
    },
    success: function(data) {
      $('select[name="zone_code"]').html('');
      if ($('select[name="zone_code"]').attr('disabled')) $('select[name="zone_code"]').prop('disabled', false);
      if (data) {
        $.each(data, function(i, zone) {
          $('select[name="zone_code"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
        });
      } else {
        $('select[name="zone_code"]').prop('disabled', true);
      }
    },
  });
});
</script>