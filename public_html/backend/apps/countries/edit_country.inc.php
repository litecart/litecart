<?php

  if (!empty($_GET['country_code'])) {
    $country = new ent_country($_GET['country_code']);
  } else {
    $country = new ent_country();
  }

  if (!$_POST) {
    $_POST = $country->data;
  }

  document::$snippets['title'][] = !empty($country->data['id']) ? language::translate('title_edit_country', 'Edit Country') : language::translate('title_create_new_country', 'Create New Country');

  breadcrumbs::add(language::translate('title_countries', 'Countries'));
  breadcrumbs::add(!empty($country->data['id']) ? language::translate('title_edit_country', 'Edit Country') : language::translate('title_create_new_country', 'Create New Country'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['iso_code_1'])) throw new Exception(language::translate('error_missing_code', 'You must enter a code'));
      if (empty($_POST['iso_code_2'])) throw new Exception(language::translate('error_missing_code', 'You must enter a code'));
      if (empty($_POST['iso_code_3'])) throw new Exception(language::translate('error_missing_code', 'You must enter a code'));
      if (empty($_POST['name'])) throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));

      if (empty($_POST['zones'])) $_POST['zones'] = [];

      foreach ($_POST['zones'] as $zone) {
        if (empty($zone['code']) || empty($zone['name'])) throw new Exception(language::translate('error_zone_must_have_name_and_code', 'A zone/state/province must have a name and code'));
      }

      $_POST['iso_code_2'] = strtoupper($_POST['iso_code_2']);
      $_POST['iso_code_3'] = strtoupper($_POST['iso_code_3']);

      $fields = [
        'status',
        'iso_code_1',
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
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $country->data[$field] = $_POST[$field];
      }

      $country->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/countries'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($country->data['id'])) throw new Exception(language::translate('error_must_provide_country', 'You must provide a country'));

      $country->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/countries'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($country->data['id']) ? language::translate('title_edit_country', 'Edit Country') : language::translate('title_create_new_country', 'Create New Country'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('country_form', 'post', false, false); ?>

      <div class="row">
        <div class="col-lg-5">
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_status', 'Status'); ?></label>
              <?php echo functions::form_toggle('status', 'e/d', (file_get_contents('php://input') != '') ? true : '1'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_number', 'Number'); ?> (ISO 3166-1 numeric) <a href="https://en.wikipedia.org/wiki/ISO_3166-1_numeric" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
              <?php echo functions::form_text_field('iso_code_1', true, 'required pattern="[0-9]{3}"'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_code', 'Code'); ?> (ISO 3166-1 alpha-2) <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
              <?php echo functions::form_text_field('iso_code_2', true, 'required pattern="[A-Z]{2}"'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_code', 'Code'); ?> (ISO 3166-1 alpha-3) <a href="http://en.wikipedia.org/wiki/ISO_3166-1_alpha-3" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
              <?php echo functions::form_text_field('iso_code_3', true, 'required pattern="[A-Z]{3}"'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_name', 'Name'); ?></label>
              <?php echo functions::form_text_field('name', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_domestic_name', 'Domestic Name'); ?></label>
              <?php echo functions::form_text_field('domestic_name', true); ?>
            </div>
          </div>

          <div class="form-group">
            <label><?php echo language::translate('title_address_format', 'Address Format'); ?> (<a id="address-format-hint" href="#">?</a>) <a href="https://en.wikipedia.org/wiki/Address_(geography)" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
            <?php echo functions::form_textarea('address_format', true, 'style="height: 150px;"'); ?>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_tax_id_format', 'Tax ID Format'); ?> <a href="https://en.wikipedia.org/wiki/Regular_expression" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
              <?php echo functions::form_text_field('tax_id_format', true); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_postcode_format', 'Postcode Format'); ?> <a href="https://en.wikipedia.org/wiki/Regular_expression" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
              <?php echo functions::form_text_field('postcode_format', true); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-4">
              <label><?php echo language::translate('title_language_code', 'Language Code'); ?> <a href="http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
              <?php echo functions::form_text_field('language_code', true); ?>
            </div>

            <div class="form-group col-md-4">
              <label><?php echo language::translate('title_currency_code', 'Currency Code'); ?> <a href="https://en.wikipedia.org/wiki/List_of_countries_and_capitals_with_currency_and_language" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
              <?php echo functions::form_text_field('currency_code', true); ?>
            </div>

            <div class="form-group col-md-4">
              <label><?php echo language::translate('title_phone_country_code', 'Phone Country Code'); ?> <a href="https://en.wikipedia.org/wiki/List_of_country_calling_codes" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
              <?php echo functions::form_text_field('phone_code', true); ?>
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <h2><?php echo language::translate('title_zones', 'Zones'); ?></h2>
          <table class="table table-striped table-hover data-table">
            <thead>
              <tr>
                <th><?php echo language::translate('title_id', 'ID'); ?></th>
                <th style="padding-inline-end: 50px;"><?php echo language::translate('title_code', 'Code'); ?></th>
                <th class="main"><?php echo language::translate('title_name', 'Name'); ?></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($_POST['zones'])) foreach (array_keys($_POST['zones']) as $key) { ?>
              <tr>
                <td><?php echo functions::form_hidden_field('zones['. $key .'][id]', true); ?><?php echo $_POST['zones'][$key]['id']; ?></td>
                <td><?php echo functions::form_text_field('zones['. $key .'][code]', true); ?></td>
                <td><?php echo functions::form_text_field('zones['. $key .'][name]', true); ?></td>
                <td class="text-end"><a class="remove" href="#" title="<?php echo language::translate('title_remove', 'Remove'); ?>"><?php echo functions::draw_fonticon('remove'); ?></a></td>
              </tr>
              <?php } ?>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="4"><a class="add btn btn-default" href="#"><?php echo functions::draw_fonticon('add'); ?> <?php echo language::translate('title_add_zone', 'Add Zone'); ?></a></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo (!empty($country->data['id'])) ? functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!window.confirm(\''. language::translate('text_are_you_sure', 'Are you sure?') .'\')) return false;"', 'delete') : false; ?>
        <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>

<script>
  $('#address-format-hint').click(function() {
    alert(
      '<?php echo language::translate('title_syntax', 'Syntax'); ?>:\n\n' +
      '%company, %firstname, %lastname, \n' +
      '%address1, %address2\n' +
      '%postcode %city\n' +
      '%zone_code, %zone_name\n' +
      '%country_number, %country_code, %country_code_3, %country_name, %country_domestic_name\n'
    );
  });

  let new_zone_index = 0;
  $('form[name="country_form"] .add').click(function(event) {
    event.preventDefault();
    if ($('select[name="country[code]"]').find('option:selected').val() == '') return;

    let output = [
      '<tr>'
      '  <td><?php echo functions::escape_js(functions::form_hidden_field('zones[new_zone_index][id]', '')); ?></td>',
      '  <td><?php echo functions::escape_js(functions::form_text_field('zones[new_zone_index][code]', '')); ?></td>',
      '  <td><?php echo functions::escape_js(functions::form_text_field('zones[new_zone_index][name]', '')); ?></td>',
      '  <td class="text-end"><a class="remove" href="#" title="<?php echo functions::escape_js(language::translate('title_remove', 'Remove'), true); ?>"><?php echo functions::escape_js(functions::draw_fonticon('remove')); ?></a></td>',
      '</tr>'
    ].join('')
    .replace(/new_zone_index/g, 'new_' + new_zone_index++)
    .replace(/new_zone_code/g, $('input[name="zone[code]"]').val())
    .replace(/new_zone_name/g, $('input[name="zone[name]"]').val());

    $(this).closest('table').find('tbody').append(output);
  });

  $('form[name="country_form"]').on('click', '.remove', function(event) {
    event.preventDefault();
    $(this).closest('tr').remove();
  });
</script>