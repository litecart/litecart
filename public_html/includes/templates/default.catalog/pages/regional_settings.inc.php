<main id="content">
  {snippet:notices}

  <div id="box-regional-settings">
    <h1 class="title"><?php echo language::translate('title_regional_settings', 'Regional Settings'); ?></h1>

    <?php echo functions::form_draw_form_begin('region_form', 'post', document::ilink(), false, 'style="max-width: 480px;"'); ?>

      <div class="row half-gutter">
        <?php if (count(language::$languages) > 1) { ?>
        <div class="form-group col-sm-halfs">
          <label><?php echo language::translate('title_language', 'Language'); ?></label>
          <?php echo functions::form_draw_languages_list('language_code', language::$selected['code']); ?>
        </div>
        <?php } ?>

        <?php if (count(currency::$currencies) > 1) { ?>
        <div class="form-group col-sm-halfs">
          <label><?php echo language::translate('title_currency', 'Currency'); ?></label>
          <?php echo functions::form_draw_currencies_list('currency_code', currency::$selected['code']); ?>
        </div>
        <?php } ?>

        <div class="form-group col-sm-halfs">
          <label><?php echo language::translate('title_country', 'Country'); ?></label>
          <?php echo functions::form_draw_countries_list('country_code', customer::$data['country_code']); ?>
        </div>

        <div class="form-group col-sm-halfs">
          <label><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?></label>
          <?php echo functions::form_draw_zones_list(customer::$data['country_code'], 'zone_code', customer::$data['zone_code']); ?>
        </div>

        <div class="form-group col-sm-halfs">
          <label><?php echo language::translate('title_display_prices', 'Display Prices'); ?></label>
          <div class="radio">
            <label><?php echo functions::form_draw_radio_button('display_prices_including_tax', 0, isset(customer::$data['display_prices_including_tax']) ? (int)customer::$data['display_prices_including_tax'] : (int)settings::get('default_display_prices_including_tax')); ?> <?php echo language::translate('title_excl_tax', 'Excl. Tax'); ?></label>
            <label style="margin-left: 1em;"><?php echo functions::form_draw_radio_button('display_prices_including_tax', 1, isset(customer::$data['display_prices_including_tax']) ? (int)customer::$data['display_prices_including_tax'] : (int)settings::get('default_display_prices_including_tax')); ?> <?php echo language::translate('title_incl_tax', 'Incl. Tax'); ?></label>
          </div>
        </div>
      </div>

      <p class="btn-group btn-block">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save')); ?>
      </p>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</main>

<script>
  if ($('#regional-settings .title').parents('.modal')) {
    $('#regional-settings .title').closest('.modal').find('.modal-title').text($('#regional-settings .title').text());
    $('#regional-settings .title').remove();
  }

  $('select[name="country_code"]').change(function(){
    $('body').css('cursor', 'wait');
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
        if ($('select[name="zone_code"]').attr('disabled')) $('select[name="zone_code"]').removeAttr('disabled');
        if (data) {
          $.each(data, function(i, zone) {
            $('select[name="zone_code"]').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
          });
        } else {
          $('select[name="zone_code"]').attr('disabled', 'disabled');
        }
      },
      complete: function() {
        $('body').css('cursor', 'auto');
      }
    });
  });
</script>