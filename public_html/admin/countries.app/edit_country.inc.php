<?php

  if (!empty($_GET['country_code'])) {
    $country = new ctrl_country($_GET['country_code']);
  } else {
    $country = new ctrl_country();
  }

  if (empty($_POST)) {
    foreach ($country->data as $key => $value) {
      $_POST[$key] = $value;
    }
  }

  breadcrumbs::add(!empty($country->data['id']) ? language::translate('title_edit_country', 'Edit Country') : language::translate('title_add_new_country', 'Add New Country'));

  if (isset($_POST['save'])) {

    if (empty($_POST['iso_code_2'])) notices::add('errors', language::translate('error_missing_code', 'You must enter a code'));
    if (empty($_POST['iso_code_3'])) notices::add('errors', language::translate('error_missing_code', 'You must enter a code'));
    if (empty($_POST['name'])) notices::add('errors', language::translate('error_must_enter_name', 'You must enter a name'));

    if (empty($_POST['zones'])) $_POST['zones'] = array();

    if (empty(notices::$data['errors'])) {

      $_POST['iso_code_2'] = strtoupper($_POST['iso_code_2']);
      $_POST['iso_code_3'] = strtoupper($_POST['iso_code_3']);

      $fields = array(
        'status',
        'iso_code_2',
        'iso_code_3',
        'name',
        'domestic_name',
        'tax_id_format',
        'address_format',
        'postcode_format',
        'currency_code',
        'phone_code',
        'zones',
      );

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $country->data[$field] = $_POST[$field];
      }

      $country->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link('', array('doc' => 'countries'), true, array('country_id')));
      exit;
    }
  }

  if (isset($_POST['delete'])) {

    $country->delete();

    notices::add('success', language::translate('success_post_deleted', 'Post deleted'));
    header('Location: '. document::link('', array('doc' => 'countries'), true, array('country_id')));
    exit;
  }

?>
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo !empty($country->data['id']) ? language::translate('title_edit_country', 'Edit Country') : language::translate('title_add_new_country', 'Add New Country'); ?></h1>

<?php echo functions::form_draw_form_begin(false, 'post', false, true); ?>

  <table>
    <tr>
      <td><strong><?php echo language::translate('title_status', 'Status'); ?></strong><br />
        <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '1', 'e/d'); ?>
      </td>
    </tr>
    <tr>
        <td><strong><?php echo language::translate('title_code', 'Code'); ?></strong> (ISO 3166-1 alpha-2)<br />
          <?php echo functions::form_draw_text_field('iso_code_2', true, 'data-size="tiny" required="required" pattern="[A-Z]{2}"'); ?> <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a>
      </td>
    </tr>
    <tr>
        <td><strong><?php echo language::translate('title_code', 'Code'); ?></strong> (ISO 3166-1 alpha-3) <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-3" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a><br />
          <?php echo functions::form_draw_text_field('iso_code_3', true, 'data-size="tiny" required="required" pattern="[A-Z]{3}"'); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_name', 'Name'); ?></strong><br />
        <?php echo functions::form_draw_text_field('name', true); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_domestic_name', 'Domestic Name'); ?></strong><br />
        <?php echo functions::form_draw_text_field('domestic_name', true); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_tax_id_format', 'Tax ID Format'); ?></strong> <a href="https://en.wikipedia.org/wiki/Regular_expression" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a><br />
        <?php echo functions::form_draw_text_field('tax_id_format', true); ?></td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_address_format', 'Address Format'); ?></strong> (<a id="address-format-hint" href="#">?</a>) <a href="http://www.addressdoctor.com/en/countries-data/address-formats.html" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a><br />
        <?php echo functions::form_draw_textarea('address_format', true, 'data-size="medium" style="height: 150px;"'); ?>
        <script>
          $("#address-format-hint").click(function() {
            alert(
              "<?php echo language::translate('title_syntax', 'Syntax'); ?>:\n\n" +
              "%company, %firstname, %lastname, \n" +
              "%address1, %address2\n" +
              "%postcode %city\n" +
              "%zone_code, %zone_name\n" +
              "%country_code_2, %country_code_3, %country_name\n"
            );
          });
        </script>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_postcode_format', 'Postcode Format'); ?></strong> <a href="https://en.wikipedia.org/wiki/Regular_expression" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a><br />
        <?php echo functions::form_draw_text_field('postcode_format', true); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_currency_code', 'Currency Code'); ?></strong> <a href="https://en.wikipedia.org/wiki/List_of_countries_and_capitals_with_currency_and_language" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a><br />
        <?php echo functions::form_draw_text_field('currency_code', true, 'data-size="tiny"'); ?>
      </td>
    </tr>
    <tr>
      <td><strong><?php echo language::translate('title_phone_country_code', 'Phone Country Code'); ?></strong> <a href="https://en.wikipedia.org/wiki/List_of_country_calling_codes" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a><br />
        <?php echo functions::form_draw_text_field('phone_code', true, 'data-size="tiny"'); ?>
      </td>
    </tr>
  </table>

  <h2><?php echo language::translate('title_zones', 'Zones'); ?></h2>
  <table width="100%" class="dataTable" id="table-zones">
    <tr class="header">
      <th style="vertical-align: text-top;"><?php echo language::translate('title_id', 'ID'); ?></th>
      <th style="vertical-align: text-top;"><?php echo language::translate('title_code', 'Code'); ?></th>
      <th style="vertical-align: text-top; width: 100%;"><?php echo language::translate('title_name', 'Name'); ?></th>
      <th style="text-align: center; vertical-align: text-top;">&nbsp;</th>
    </tr>
  <?php
      if (!empty($_POST['zones'])) {
        foreach (array_keys($_POST['zones']) as $key) {
  ?>
    <tr>
      <td><?php echo functions::form_draw_hidden_field('zones['. $key .'][id]', true); ?><?php echo $_POST['zones'][$key]['id']; ?></td>
      <td><?php echo functions::form_draw_hidden_field('zones['. $key .'][code]', true); ?><?php echo $_POST['zones'][$key]['code']; ?></td>
      <td><?php echo functions::form_draw_hidden_field('zones['. $key .'][name]', true); ?><?php echo $_POST['zones'][$key]['name']; ?></td>
      <td style="text-align: right;"><a id="remove-zone" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
    </tr>
  <?php
        }
      }
  ?>
    <tr>
      <td>&nbsp;</td>
      <td><?php echo functions::form_draw_text_field('zone[code]', '', 'data-size="small"'); ?></td>
      <td><?php echo functions::form_draw_text_field('zone[name]', ''); ?></td>
      <td style="text-align: right;"><?php echo functions::form_draw_button('add_zone', language::translate('title_add', 'Add'), 'button'); ?></td>
    </tr>
  </table>

  <script>
    $("body").on("click", "#remove-zone", function(event) {
      event.preventDefault();
      $(this).closest('tr').remove();
    });

    $("select[name='country[code]']").change(function(){
      $('body').css('cursor', 'wait');
      $.ajax({
        url: '<?php echo document::ilink('ajax/zones.json'); ?>?country_code=' + $(this).val(),
        type: 'get',
        cache: true,
        async: true,
        dataType: 'json',
        error: function(jqXHR, textStatus, errorThrown) {
          alert(jqXHR.readyState + '\n' + textStatus + '\n' + errorThrown.message);
        },
        success: function(data) {
          $('select[name=\'zone[code]\']').html('');
          if ($('select[name=\'zone[code]\']').attr('disabled')) $('select[name=\'zone[code]\']').removeAttr('disabled');
          if (data) {
            $('select[name=\'zone[code]\']').append('<option value="">-- <?php echo functions::general_escape_js(language::translate('title_all_zones', 'All Zones')); ?> --</option>');
            $.each(data, function(i, zone) {
              $('select[name=\'zone[code]\']').append('<option value="'+ zone.code +'">'+ zone.name +'</option>');
            });
          } else {
            $('select[name=\'zone[code]\']').attr('disabled', 'disabled');
          }
        },
        complete: function() {
          $('body').css('cursor', 'auto');
        }
      });
    });

    var new_zone_i = <?php echo isset($_POST['zones']) ? count($_POST['zones']) : '0'; ?>;
    $("button[name=add_zone]").click(function(event) {
      event.preventDefault();
      if ($("select[name='country[code]']").find("option:selected").val() == "") return;
      new_zone_i++;
      var output = '    <tr>'
                 + '      <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('zones[new_zone_i][id]', '')); ?></td>'
                 + '      <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('zones[new_zone_i][code]', 'new_zone_code')); ?>new_zone_code</td>'
                 + '      <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('zones[new_zone_i][name]', 'new_zone_name')); ?>new_zone_name</td>'
                 + '      <td style="text-align: right;"><a id="remove-zone" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
                 + '    </tr>';
      output = output.replace(/new_zone_i/g, 'new_' + new_zone_i);
      output = output.replace(/new_zone_code/g, $("input[name='zone[code]']").val());
      output = output.replace(/new_zone_name/g, $("input[name='zone[name]']").val());
      $("#table-zones tr:last").before(output);
    });
  </script>

  <p><span class="button-set"><?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?> <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?> <?php echo (isset($country->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?></span></p>

<?php echo functions::form_draw_form_end(); ?>