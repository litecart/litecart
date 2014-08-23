<div id="box-select-region" class="box">
  <div class="heading">
    <h1<?php echo (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ? ' style="margin-top: 0px;"' : ''; ?>><?php echo language::translate('title_regional_settings', 'Regional Settings'); ?></h1>
  </div>
  <div class="content">
    <?php echo functions::form_draw_form_begin('region_form', 'post', document::ilink()); ?>
    <table>
      <tr>
        <td><?php echo language::translate('title_language', 'Language'); ?><br />
          <?php echo functions::form_draw_languages_list('language_code', language::$selected['code']); ?></td>
        <td><?php echo language::translate('title_currency', 'Currency'); ?><br />
          <?php echo functions::form_draw_currencies_list('currency_code', currency::$selected['code']); ?></td>
      </tr>
      <tr>
        <td><?php echo language::translate('title_country', 'Country'); ?><br />
          <?php echo functions::form_draw_countries_list('country_code', customer::$data['country_code']); ?></td>
        <td><?php echo language::translate('title_zone_state_province', 'Zone/State/Province'); ?><br />
          <?php echo functions::form_draw_zones_list(customer::$data['country_code'], 'zone_code', customer::$data['zone_code']); ?></td>
      </tr>
      <tr>
        <td><?php echo language::translate('title_display_prices', 'Display Prices'); ?><br />
          <label><?php echo functions::form_draw_radio_button('display_prices_including_tax', 0, isset(customer::$data['display_prices_including_tax']) ? (int)customer::$data['display_prices_including_tax'] : (int)settings::get('default_display_prices_including_tax')); ?> <?php echo language::translate('title_excl_tax', 'Excl. Tax'); ?></label><br />
          <label><?php echo functions::form_draw_radio_button('display_prices_including_tax', 1, isset(customer::$data['display_prices_including_tax']) ? (int)customer::$data['display_prices_including_tax'] : (int)settings::get('default_display_prices_including_tax')); ?> <?php echo language::translate('title_incl_tax', 'Incl. Tax'); ?></label></td>
        <td></td>
      </tr>
      <tr>
        <td colspan="2"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save')); ?></td>
      </tr>
    </table>
    <?php echo functions::form_draw_form_end(); ?>
    <script>
      $("select[name='country_code']").change(function(){
        $('body').css('cursor', 'wait');
        $.ajax({
          url: '<?php echo WS_DIR_AJAX .'zones.json.php'; ?>?country_code=' + $(this).val(),
          type: 'get',
          cache: true,
          async: true,
          dataType: 'json',
          error: function(jqXHR, textStatus, errorThrown) {
            if (console) console.warn(errorThrown.message);
          },
          success: function(data) {
            $("select[name='zone_code']").html('');
            if ($("select[name='zone_code']").attr('disabled')) $("select[name='zone_code']").removeAttr('disabled');
            if (data) {
              $.each(data, function(i, zone) {
                $("select[name='zone_code']").append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
              });
            } else {
              $("select[name='zone_code']").attr('disabled', 'disabled');
            }
          },
          complete: function() {
            $('body').css('cursor', 'auto');
          }
        });
      });
    </script>
  </div>
</div>