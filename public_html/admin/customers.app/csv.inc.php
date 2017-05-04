<?php

  if (!empty($_POST['import'])) {

    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {

      $csv = file_get_contents($_FILES['file']['tmp_name']);

      $csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset']);

      foreach ($csv as $row) {

        $customer = null;

        if (!empty($row['id'])) {
          $customers_query = database::query(
            "select id from ". DB_TABLE_CUSTOMERS ."
            where id = '". (int)$row['id'] ."'
            limit 1;"
          );
          $customer = database::fetch($customers_query);

        } else if (!empty($row['code'])) {
          $customers_query = database::query(
            "select id from ". DB_TABLE_CUSTOMERS ."
            where code = '". database::input($row['code']) ."'
            limit 1;"
          );
          $customer = database::fetch($customers_query);

        } else if (!empty($row['email'])) {
          $customers_query = database::query(
            "select id from ". DB_TABLE_CUSTOMERS ."
            where email like '". database::input($row['email']) ."'
            limit 1;"
          );
          $customer = database::fetch($customers_query);
        }

        if (!empty($customer)) {
          $customer = new ctrl_customer($customer['id']);
        } else {
          $customer = new ctrl_customer();
        }

        $fields = array(
          'code',
          'email',
          'tax_id',
          'company',
          'firstname',
          'lastname',
          'address1',
          'address2',
          'postcode',
          'city',
          'country_code',
          'zone_code',
          'phone',
          'newsletter',
          'notes',
        );

        foreach ($fields as $field) {
          if (isset($row[$field])) $customer->data[$field] = $row[$field];
        }

        if (!empty($row['new_password'])) $customer->set_password($row['new_password']);

        $customer->save();
      }

      notices::add('success', language::translate('success_customers_imported', 'Customers successfully imported.'));

      header('Location: '. document::link('', array('app' => $_GET['app'], 'doc' => $_GET['doc'])));
      exit;
    }
  }

  if (!empty($_POST['export'])) {

    $customers_query = database::query(
      "select * from ". DB_TABLE_CUSTOMERS ."
      order by date_created asc;"
    );

    $csv = array();

    while ($customer = database::fetch($customers_query)) {
      $csv[] = array(
        'id' => $customer['id'],
        'code' => $customer['code'],
        'email' => $customer['email'],
        'tax_id' => $customer['tax_id'],
        'company' => $customer['company'],
        'firstname' => $customer['firstname'],
        'lastname' => $customer['lastname'],
        'address1' => $customer['address1'],
        'address2' => $customer['address2'],
        'postcode' => $customer['postcode'],
        'city' => $customer['city'],
        'country_code' => $customer['country_code'],
        'zone_code' => $customer['zone_code'],
        'phone' => $customer['phone'],
        'newsletter' => $customer['newsletter'],
        'notes' => $customer['notes'],
      );
    }

    ob_clean();

    if ($_POST['output'] == 'screen') {
      header('Content-Type: text/plain; charset='. $_POST['charset']);
    } else {
      header('Content-Type: application/csv; charset='. $_POST['charset']);
      header('Content-Disposition: attachment; filename=customers.csv');
    }

    switch($_POST['eol']) {
      case 'Linux':
        echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r");
        break;
      case 'Mac':
        echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\n");
        break;
      case 'Win':
      default:
        echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r\n");
        break;
    }

    exit;
  }

?>

<h1><?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?></h1>

<div class="row">
  <div class="col-md-3">

    <fieldset>
      <legend><?php echo language::translate('title_import_to_csv', 'Import From CSV'); ?></legend>

      <?php echo functions::form_draw_form_begin('import_form', 'post', '', true); ?>

        <div class="form-group">
          <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
          <?php echo functions::form_draw_file_field('file'); ?></td>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
          <?php echo functions::form_draw_select_field('delimiter', array(array(language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ''), array(','),  array(';'), array('TAB', "\t"), array('|')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
          <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?><br />
          <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
          <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8', false); ?>
        </div>

      <?php echo functions::form_draw_button('import', language::translate('title_import', 'Import'), 'submit'); ?>

      <?php echo functions::form_draw_form_end(); ?>
    </fieldset>
  </div>

  <div class="col-md-3">

    <fieldset>
      <legend><?php echo language::translate('title_export_to_csv', 'Export To CSV'); ?></legend>

      <?php echo functions::form_draw_form_begin('export_form', 'post'); ?>

        <div class="form-group">
          <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
          <?php echo functions::form_draw_select_field('delimiter', array(array(', ('. language::translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
          <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
          <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
          <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8', false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
          <?php echo functions::form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux')), true, false); ?>
        </div>

        <div class="form-group">
          <label><?php echo language::translate('title_output', 'Output'); ?></label>
          <?php echo functions::form_draw_select_field('output', array(array(language::translate('title_file', 'File'), 'file'), array(language::translate('title_screen', 'Screen'), 'screen')), true, false); ?>
        </div>

        <?php echo functions::form_draw_button('export', language::translate('title_export', 'Export'), 'submit'); ?>

      <?php echo functions::form_draw_form_end(); ?>
    </fieldset>

  </div>
</div>
