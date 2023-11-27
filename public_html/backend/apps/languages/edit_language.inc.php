<?php

  if (!empty($_GET['language_code'])) {
    $language = new ent_language($_GET['language_code']);
  } else {
    $language = new ent_language();
    $language->data['direction'] = 'ltr';
    $language->data['url_type'] = 'path';
  }

  if (!$_POST) {
    $_POST = $language->data;
  }

  document::$snippets['title'][] = !empty($language->data['id']) ? language::translate('title_edit_language', 'Edit Language') : language::translate('title_create_new_language', 'Create New Language');

  breadcrumbs::add(language::translate('title_languages', 'Languages'), document::ilink(__APP__.'/languages'));
  breadcrumbs::add(!empty($language->data['id']) ? language::translate('title_edit_language', 'Edit Language') : language::translate('title_create_new_language', 'Create New Language'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['code'])) {
        throw new Exception(language::translate('error_must_enter_code', 'You must enter a code'));
      }

      if (empty($_POST['name'])) {
        throw new Exception(language::translate('error_must_enter_name', 'You must enter a name'));
      }

      if (!empty($_POST['url_type']) && $_POST['url_type'] == 'domain' && empty($_POST['domain_name'])) {
        throw new Exception(language::translate('error_must_provide_domain', 'You must provide a domain name'));
      }

      if (!empty($_POST['url_type']) && $_POST['url_type'] == 'domain' && !empty($_POST['domain_name'])) {
        if (!empty($language->data['id']) && database::query("select id from ". DB_TABLE_PREFIX ."languages where domain_name = '". database::input($_POST['domain_name']) ."' and id != ". (int)$language->data['id'] ." limit 1;")->num_rows) {
          throw new Exception(language::translate('error_domain_in_use_by_other_language', 'The domain name is already in use by another domain name.'));
        }
      }

      if (empty($_POST['set_default']) && isset($language->data['code']) && $language->data['code'] == settings::get('default_language_code') && $language->data['code'] != $_POST['code']) {
        throw new Exception(language::translate('error_cannot_rename_default_language', 'You must change the default language before renaming it.'));
      }

      if (empty($_POST['set_store']) && isset($language->data['code']) && $language->data['code'] == settings::get('store_language_code') && $language->data['code'] != $_POST['code']) {
        throw new Exception(language::translate('error_cannot_rename_store_language', 'You must change the store language before renaming it.'));
      }

      if (!empty($_POST['set_default']) && empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == settings::get('default_language_code')) {
        throw new Exception(language::translate('error_cannot_set_disabled_default_language', 'You cannot set a disabled language as default language.'));
      }

      if (!empty($_POST['set_store']) && empty($_POST['status']) && isset($language->data['code']) && $language->data['code'] == settings::get('store_language_code')) {
        throw new Exception(language::translate('error_cannot_set_disabled_store_language', 'You cannot set a disabled language as store language.'));
      }

      if (!preg_grep('#'. preg_quote($_POST['charset'], '#') .'#i', mb_list_encodings())) {
        throw new Exception(strtr(language::translate('error_not_a_supported_charset', '%charset is not a supported character set'), ['%charset' => fallback($_POST['charset'], 'NULL')]));
      }

      if (!setlocale(LC_ALL, preg_split('#\s*,\s*#', $_POST['locale'], -1, PREG_SPLIT_NO_EMPTY))) {
        throw new Exception(strtr(language::translate('error_not_a_valid_system_locale', '%locale is not a valid system locale on this machine'), ['%locale' => fallback($_POST['locale'], 'NULL')]));
      }

      setlocale(LC_ALL, preg_split('#\s*,\s*#', language::$selected['locale'], -1, PREG_SPLIT_NO_EMPTY)); // Restore

      ##########

      if (empty($_POST['domain_name'])) $_POST['domain_name'] = '';

      $_POST['code'] = strtolower($_POST['code']);
      $_POST['raw_datetime'] = $_POST['raw_date'] .' '. $_POST['raw_time'];
      $_POST['format_datetime'] = $_POST['format_date'] .' '. $_POST['format_time'];

      $fields = [
        'status',
        'code',
        'code2',
        'name',
        'direction',
        'charset',
        'locale',
        'url_type',
        'domain_name',
        'raw_date',
        'raw_time',
        'raw_datetime',
        'format_date',
        'format_time',
        'format_datetime',
        'decimal_point',
        'thousands_sep',
        'currency_code',
        'priority',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) {
          $language->data[$field] = $_POST[$field];
        }
      }

      $language->save();

      if (!empty($_POST['set_default'])) {
        database::query("update ". DB_TABLE_PREFIX ."settings set `value` = '". database::input($_POST['code']) ."' where `key` = 'default_language_code' limit 1;");
      }

      if (!empty($_POST['set_store'])) {
        database::query("update ". DB_TABLE_PREFIX ."settings set `value` = '". database::input($_POST['code']) ."' where `key` = 'store_language_code' limit 1;");
      }

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/languages'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {

      if (empty($language->data['id'])) {
        throw new Exception(language::translate('error_must_provide_language', 'You must provide a language'));
      }

      $language->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::ilink(__APP__.'/languages'));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  $date_format_options = [
    '%e %b %Y' => language::strftime('%e %b %Y'),
    '%b %e %Y' => language::strftime('%b %e %Y'),
  ];

  $time_format_options = [
    [
      'label' => '12-Hour Format',
      'options' => [
        '%I:%M %P' => language::strftime('%I:%M %p'),
      ],
    ],
    [
      'label' => '24-Hour Format',
      'options' => [
        '%H:%M' => language::strftime('%H:%M'),
      ],
    ],
  ];

  $raw_date_options = [
    [
      'label' => 'Big-endian (YMD)', 'null', 'style="font-weight: bold;" disabled',
      'options' => [
        'Y-m-d' => date('Y-m-d'),
        'Y.m.d' => date('Y.m.d'),
        'Y/m/d' => date('Y/m/d'),
      ],
    ],
    [
      'label' => 'Little-endian (DMY)', 'null', 'style="font-weight: bold;" disabled',
      'options' => [
        'd-m-Y' => date('d-m-Y'),
        'd.m.Y' => date('d.m.Y'),
        'd/m/Y' => date('d/m/Y'),
      ],
    ],
    [
      'label' => 'Middle-endian (MDY)', 'null', 'style="font-weight: bold;" disabled',
      'options' => [
        'm/d/y' => date('m/d/y'),
      ],
    ],
  ];

  $raw_time_options = [
    [
      'label' => '12-hour format',
      'options' => [
        'h:i A' => date('h:i A'),
      ],
    ],
    [
      'label' => '24-hour format',
      'options' => [
        'H:i' => date('H:i'),
      ]
    ],
  ];

  $decimal_point_options = [
    '.' => language::translate('char_dot', 'Dot'),
    ',' => language::translate('char_comma', 'Comma'),
  ];

  $thousands_separator_options = [
    ',' => language::translate('char_comma', 'Comma'),
    '.' => language::translate('char_dot', 'Dot'),
    ' ' => language::translate('char_space', 'Space'),
    ' ' => language::translate('char_nonbreaking_space', 'Non-Breaking Space'),
    '\'' => language::translate('char_single_quote', 'Single quote'),
  ];

// Prefillable currencies
  $prefillable_language_options = [];

  if (empty($currency->data['id'])) {

    $prefillable_language_options[] = ['', '-- '. language::translate('title_select', 'Select') .' --'];

  // Get all existing languages
    $existing_languages = database::query(
      "select code from ". DB_TABLE_PREFIX ."languages;"
    )->fetch_all('code');

  // Get languages from i18n repository
    $client = new http_client();
    $result = $client->call('GET', 'https://raw.githubusercontent.com/litecart/i18n/master/languages.csv');
    $available_languages = functions::csv_decode($result);

  // Filter already added
    $available_languages = array_filter($available_languages, function($a) use ($existing_languages) {
      return !in_array($a['code'], $existing_languages);
    });

  // Sort by native name
    uasort($available_languages, function($a, $b){
      return ($a['code'] < $b['code']) ? -1 : 1;
    });

  // Append to array of options
    foreach ($available_languages as $available_language) {
      $prefillable_language_options[] = [
        $available_language['code'],
        $available_language['code'] .' &ndash; '. $available_language['native'],
        implode(' ', array_map(function($k, $v){
          return 'data-'. str_replace('_', '-', $k) .'="'. functions::escape_html($v) .'"';
        }, array_keys($available_language), array_values($available_language))),
      ];
    }
  }

?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($language->data['id']) ? language::translate('title_edit_language', 'Edit Language') : language::translate('title_create_new_language', 'Create New Language'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_begin('language_form', 'post', false, false, 'style="max-width: 640px;"'); ?>

      <?php if (!empty($available_languages)) { ?>
      <div class="form-group col-md-6">
        <label><?php echo language::translate('text_prefill_from_the_web', 'Prefill from the web'); ?></label>
        <?php echo functions::form_select_field('prefill', $prefillable_language_options, ''); ?>
      </div>
      <?php } ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_status', 'Status'); ?></label>
          <div class="btn-group btn-block btn-group-inline" data-toggle="buttons">
            <label class="btn btn-default<?php echo (isset($_POST['status']) && $_POST['status'] == 1) ? ' active' : ''; ?>"><?php echo functions::form_radio_button('status', '1', true); ?> <?php echo language::translate('title_enabled', 'Enabled'); ?></label>
            <label class="btn btn-default<?php echo (isset($_POST['status']) && $_POST['status']  == -1) ? ' active' : ''; ?>"><?php echo functions::form_radio_button('status', '-1', true); ?><?php echo language::translate('title_hidden', 'Hidden'); ?></label>
            <label class="btn btn-default<?php echo empty($_POST['status']) ? ' active' : ''; ?>"><?php echo functions::form_radio_button('status', '0', true); ?><?php echo language::translate('title_disabled', 'Disabled'); ?></label>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php echo functions::form_text_field('name', true, 'list="available-languages"'); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_text_direction', 'Text Direction'); ?></label>
          <div class="btn-group btn-block btn-group-inline" data-toggle="buttons">
          <label class="btn btn-default<?php echo (!isset($_POST['direction']) || $_POST['direction'] == 'ltr') ? ' active' : ''; ?>" style="text-align: left;"><?php echo functions::form_radio_button('direction', 'ltr', true); ?> <?php echo language::translate('title_left_to_right', 'Left To Right'); ?></label>
          <label class="btn btn-default<?php echo (isset($_POST['direction']) && $_POST['direction'] == 'rtl') ? ' active' : ''; ?>" style="text-align: right;"><?php echo functions::form_radio_button('direction', 'rtl', true); ?><?php echo language::translate('title_right_to_left', 'Right To Left'); ?></label>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_code', 'Code'); ?> (ISO 639-1) <a href="https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
          <?php echo functions::form_text_field('code', true, 'required pattern="[a-z]{2}"'); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_code', 'Code'); ?> 2 (ISO 639-2) <a href="https://en.wikipedia.org/wiki/List_of_ISO_639-2_codes" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
          <?php echo functions::form_text_field('code2', true, 'required pattern="[a-z]{3}"'); ?>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_system_locale', 'System Locale'); ?></label>
        <?php echo functions::form_text_field('locale', true, 'placeholder="en_US.utf8, en-US.UTF-8, english"'); ?>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_url_type', 'URL Type'); ?></label>
          <div class="btn-group btn-block btn-group-inline" data-toggle="buttons">
          <label class="btn btn-default<?php echo (!empty($_POST['url_type']) && $_POST['url_type'] == 'none') ? ' active' : ''; ?>"><?php echo functions::form_radio_button('url_type', 'none', !empty($_POST['url_type']) ? true : 'none'); ?> <?php echo language::translate('title_none', 'None'); ?></label>
          <label class="btn btn-default<?php echo (!empty($_POST['url_type']) && $_POST['url_type'] == 'path') ? ' active' : ''; ?>"><?php echo functions::form_radio_button('url_type', 'path', true); ?> <?php echo language::translate('title_path_prefix', 'Path Prefix'); ?></label>
          <label class="btn btn-default<?php echo (!empty($_POST['url_type']) && $_POST['url_type'] == 'domain') ? ' active' : ''; ?>"><?php echo functions::form_radio_button('url_type', 'domain', true); ?> <?php echo language::translate('title_domain', 'Domain'); ?></label>
          </div>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_domain_name', 'Domain Name'); ?></label>
          <?php echo functions::form_text_field('domain_name', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_date_format', 'Date Format'); ?> <a href="https://php.net/manual/en/function.strftime.php" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
          <?php echo functions::form_select_field('format_date', $date_format_options, true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_time_format', 'Time Format'); ?> <a href="https://php.net/manual/en/function.strftime.php" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
          <?php echo functions::form_select_optgroup_field('format_time', $time_format_options, true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_raw_date_format', 'Raw Date Format'); ?> <a href="https://php.net/manual/en/function.date.php" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
          <?php echo functions::form_select_optgroup_field('raw_date', $raw_date_options, true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_raw_time_format', 'Raw Time Format'); ?> <a href="https://php.net/manual/en/function.date.php" target="_blank"><?php echo functions::draw_fonticon('fa-external-link'); ?></a></label>
          <?php echo functions::form_select_optgroup_field('raw_time', $raw_time_options, true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_decimal_point', 'Decimal Point'); ?></label>
          <?php echo functions::form_select_field('decimal_point', $decimal_point_options, true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_thousands_sep', 'Thousands Separator'); ?></label>
          <?php echo functions::form_select_field('thousands_sep', $thousands_separator_options, true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_force_currency_code', 'Force Currency Code'); ?></label>
          <?php echo functions::form_text_field('currency_code', true); ?>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_priority', 'Priority'); ?></label>
          <?php echo functions::form_number_field('priority', true); ?>
        </div>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <?php echo functions::form_checkbox('set_default', ['1', language::translate('description_set_as_default_language', 'Set as default language')], (isset($language->data['code']) && $language->data['code'] && $language->data['code'] == settings::get('default_language_code')) ? '1' : true); ?>
          <?php echo functions::form_checkbox('set_store', ['1', language::translate('description_set_as_store_language', 'Set as store language')], (isset($language->data['code']) && $language->data['code'] && $language->data['code'] == settings::get('store_language_code')) ? '1' : true); ?></label>
        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"', 'save'); ?>
        <?php echo !empty($language->data['id']) ? functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(&quot;'. language::translate('text_are_you_sure', 'Are you sure?') .'&quot;)) return false;"', 'delete') : false; ?>
        <?php echo functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_end(); ?>
  </div>
</div>

<datalist id="available-languages"></datalist>

<script>
  $('input[name="url_type"]').change(function(){
    if ($('input[name="url_type"][value="domain"]:checked').length) {
      $('input[name="domain_name"]').prop('disabled', false);
    } else {
      $('input[name="domain_name"]').prop('disabled', true);
    }
  }).first().trigger('change');

  <?php if (!empty($available_languages)) { ?>
  $('select[name="prefill"]').on('change', function() {
    $.each($(this).find('option:selected').data(), function(key, value) {
      var field_name = key.replace(/([A-Z])/, '_$1').toLowerCase()
                          .replace(/^date_format$/, 'format_date')
                          .replace(/^time_format$/, 'format_time');
      $(':input[name="'+field_name+'"]').not('[type="checkbox"]').not('[type="radio"]').val(value);
      $('input[name="'+field_name+'"][type="checkbox"][value="'+value+'"], input[name="'+field_name+'"][type="radio"][value="'+value+'"]').prop('checked', true);
      if (key == 'direction') {
        $('input[name="'+field_name+'"]:checked').parent('.btn').addClass('active').siblings().removeClass('active');
      }
    });
  });
  <?php } ?>
</script>