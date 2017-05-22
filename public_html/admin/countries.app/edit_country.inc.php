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
        'language_code',
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
<h1><?php echo $app_icon; ?> <?php echo !empty($country->data['id']) ? language::translate('title_edit_country', 'Edit Country') : language::translate('title_add_new_country', 'Add New Country'); ?></h1>

<?php echo functions::form_draw_form_begin('country_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_status', 'Status'); ?></label>
      <?php echo functions::form_draw_toggle('status', isset($_POST['status']) ? $_POST['status'] : '1', 'e/d'); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_code', 'Code'); ?> (ISO 3166-1 alpha-2) <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
      <?php echo functions::form_draw_text_field('iso_code_2', true, 'required="required" pattern="[A-Z]{2}"'); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_code', 'Code'); ?> (ISO 3166-1 alpha-3) <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-3" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
      <?php echo functions::form_draw_text_field('iso_code_3', true, 'required="required" pattern="[A-Z]{3}"'); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_name', 'Name'); ?></label>
      <?php echo functions::form_draw_text_field('name', true); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_domestic_name', 'Domestic Name'); ?></label>
      <?php echo functions::form_draw_text_field('domestic_name', true); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md">
      <label><?php echo language::translate('title_address_format', 'Address Format'); ?> (<a id="address-format-hint" href="#">?</a>) <a href="http://www.addressdoctor.com/en/countries-data/address-formats.html" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
      <?php echo functions::form_draw_textarea('address_format', true, 'style="height: 150px;"'); ?>
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
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_tax_id_format', 'Tax ID Format'); ?> <a href="https://en.wikipedia.org/wiki/Regular_expression" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
      <?php echo functions::form_draw_text_field('tax_id_format', true); ?>
    </div>

    <div class="form-group col-md-6">
      <label><?php echo language::translate('title_postcode_format', 'Postcode Format'); ?> <a href="https://en.wikipedia.org/wiki/Regular_expression" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
      <?php echo functions::form_draw_text_field('postcode_format', true); ?>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-4">
      <label><?php echo language::translate('title_language_code', 'Language Code'); ?> <a href="http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
      <?php echo functions::form_draw_text_field('language_code', true); ?>
    </div>

    <div class="form-group col-md-4">
      <label><?php echo language::translate('title_currency_code', 'Currency Code'); ?> <a href="https://en.wikipedia.org/wiki/List_of_countries_and_capitals_with_currency_and_language" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
      <?php echo functions::form_draw_text_field('currency_code', true); ?>
    </div>

    <div class="form-group col-md-4">
      <label><?php echo language::translate('title_phone_country_code', 'Phone Country Code'); ?> <a href="https://en.wikipedia.org/wiki/List_of_country_calling_codes" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
      <?php echo functions::form_draw_text_field('phone_code', true); ?>
    </div>
  </div>

  <h2><?php echo language::translate('title_zones', 'Zones'); ?></h2>
  <table class="table table-striped data-table">
    <thead>
      <tr>
        <th><?php echo language::translate('title_id', 'ID'); ?></th>
        <th style="padding-right: 50px;"><?php echo language::translate('title_code', 'Code'); ?></th>
        <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
        <th>&nbsp;</th>
      </tr>
    </thead>
    <tbody>
  <?php
      if (!empty($_POST['zones'])) {
        foreach (array_keys($_POST['zones']) as $key) {
  ?>
      <tr>
        <td><?php echo functions::form_draw_hidden_field('zones['. $key .'][id]', true); ?><?php echo $_POST['zones'][$key]['id']; ?></td>
        <td><?php echo functions::form_draw_text_field('zones['. $key .'][code]', true); ?></td>
        <td><?php echo functions::form_draw_text_field('zones['. $key .'][name]', true); ?></td>
        <td class="text-right"><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"'); ?></a></td>
      </tr>
  <?php
        }
      }
  ?>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="4"><a class="add" href="#"><?php echo functions::draw_fonticon('fa-plus-circle', 'style="color: #66cc66;"'); ?> <?php echo language::translate('title_add_zone', 'Add Zone'); ?></a></td>
      </tr>
    </tfoot>
  </table>

  <p class="btn-group">
    <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', '', 'save'); ?>
    <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
    <?php echo (isset($country->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'onclick="if (!confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
  </p>

<?php echo functions::form_draw_form_end(); ?>

<script>
  var new_zone_i = <?php echo isset($_POST['zones']) ? count($_POST['zones']) : '0'; ?>;
  $('form[name="country_form"] .add').click(function(event) {
    event.preventDefault();
    if ($('select[name="country[code]"]').find('option:selected').val() == '') return;
    new_zone_i++;
    var output = '    <tr>'
               + '      <td><?php echo functions::general_escape_js(functions::form_draw_hidden_field('zones[new_zone_i][id]', '')); ?></td>'
               + '      <td><?php echo functions::general_escape_js(functions::form_draw_text_field('zones[new_zone_i][code]', '')); ?></td>'
               + '      <td><?php echo functions::general_escape_js(functions::form_draw_text_field('zones[new_zone_i][name]', '')); ?></td>'
               + '      <td style="text-align: right;"><a class="remove" href="#" title="<?php echo functions::general_escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::general_escape_js(functions::draw_fonticon('fa-times-circle fa-lg', 'style="color: #cc3333;"')); ?></a></td>'
               + '    </tr>';
    output = output.replace(/new_zone_i/g, 'new_' + new_zone_i);
    output = output.replace(/new_zone_code/g, $('input[name="zone[code]"]').val());
    output = output.replace(/new_zone_name/g, $('input[name="zone[name]"]').val());
    $(this).closest('table').find('tbody').append(output);
  });

  $('form[name="country_form"]').on('click', '.remove', function(event) {
    event.preventDefault();
    $(this).closest('tr').remove();
  });
</script>