<?php

  document::$snippets['title'][] = language::translate('title_csv_import_export', 'CSV Import/Export');

  breadcrumbs::add(language::translate('title_customers', 'Customers'), document::link(WS_DIR_ADMIN, ['doc' => 'customers'], ['app']));
  breadcrumbs::add(language::translate('title_csv_import_export', 'CSV Import/Export'));

  if (isset($_POST['import'])) {

    try {
      if (!isset($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
        throw new Exception(language::translate('error_must_select_file_to_upload', 'You must select a file to upload'));
      }

      $csv = file_get_contents($_FILES['file']['tmp_name']);

      if (!$csv = functions::csv_decode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'])) {
        throw new Exception(language::translate('error_failed_decoding_csv', 'Failed decoding CSV'));
      }

      $updated = 0;
      $inserted = 0;
      $line = 1;

      foreach ($csv as $row) {
        $line++;

      // Find customer
        if (!empty($row['id']) && $customer = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."customers where id = ". (int)$row['id'] ." limit 1;"))) {
          $customer = new ent_customer($customer['id']);

        } else if (!empty($row['code']) && $customer = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."customers where code = '". database::input($row['code']) ."' limit 1;"))) {
          $customer = new ent_customer($customer['id']);

        } else if (!empty($row['email']) && $customer = database::fetch(database::query("select id from ". DB_TABLE_PREFIX ."customers where email = '". database::input($row['email']) ."' limit 1;"))) {
          $customer = new ent_customer($customer['id']);
        }

        if (!empty($customer->data['id'])) {

          if (empty($_POST['update'])) {
            echo "Skip updating existing customer on line $line" . PHP_EOL;
            continue;
          }

          echo 'Updating existing customer '. (!empty($row['name']) ? $row['firstname'] .' '. $row['lastname'] : "on line $line") . PHP_EOL;
          $updated++;

        } else {

          if (empty($_POST['insert'])) {
            echo "Skip inserting new customer on line $line" . PHP_EOL;
            continue;
          }

          echo 'Inserting new customer: '. (!empty($row['name']) ? $row['firstname'] .' '. $row['lastname'] : "on line $line") . PHP_EOL;
          $inserted++;

          if (!empty($row['id'])) {
            database::query(
              "insert into ". DB_TABLE_PREFIX ."customers (id, date_created)
              values (". (int)$row['id'] .", '". date('Y-m-d H:i:s') ."');"
            );
            $customer = new ent_customer($row['id']);
          } else {
            $customer = new ent_customer();
          }
        }

      // Set customer data
        $fields = [
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
        ];

        foreach ($fields as $field) {
          if (isset($row[$field])) $customer->data[$field] = $row[$field];
        }

        if (!empty($row['new_password'])) $customer->set_password($row['new_password']);

        $customer->save();
      }

      notices::add($updated ? 'success' : 'notice', strtr(language::translate('success_updated_n_existing_entries', 'Updated %n existing entries'), ['%n' => $updated]));
      notices::add($inserted ? 'success' : 'notice', strtr(language::translate('success_insert_n_new_entries', 'Inserted %n new entries'), ['%n' => $inserted]));

      header('Location: '. document::link(WS_DIR_ADMIN, ['app' => $_GET['app'], 'doc' => $_GET['doc']]));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['export'])) {

    try {
      $customers_query = database::query(
        "select c.*, if(nr.id, 1, 0) as newsletter from ". DB_TABLE_PREFIX ."customers c
        left join ". DB_TABLE_PREFIX ."newsletter_recipients nr on (nr.email = c.email)
        order by c.date_created asc;"
      );

      $csv = [];

      while ($customer = database::fetch($customers_query)) {
        $csv[] = [
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
        ];
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

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

?>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?>
    </div>
  </div>

  <div class="card-body">
    <div class="row">

      <div class="col-sm-6 col-lg-4">
        <?php echo functions::form_draw_form_begin('import_form', 'post', '', true); ?>

          <fieldset>
            <legend><?php echo language::translate('title_import', 'Import'); ?></legend>

            <div class="form-group">
              <label><?php echo language::translate('title_csv_file', 'CSV File'); ?></label>
              <?php echo functions::form_draw_file_field('file'); ?></td>
            </div>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_draw_select_field('delimiter', [[language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ''], [','],  [';'], ['TAB', "\t"], ['|']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_draw_select_field('enclosure', [['" ('. language::translate('text_default', 'default') .')', '"']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_draw_select_field('escapechar', [['" ('. language::translate('text_default', 'default') .')', '"'], ['\\', '\\']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
              </div>
            </div>

            <div class="form-group">
              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('update', 'true', true); ?> <?php echo language::translate('title_update_existing', 'Update Existing'); ?></label>
              </div>
              <div class="checkbox">
                <label><?php echo functions::form_draw_checkbox('insert', 'true', true); ?> <?php echo language::translate('title_insert_new', 'Insert New'); ?></label>
              </div>
            </div>

            <?php echo functions::form_draw_button('import', language::translate('title_import', 'Import'), 'submit'); ?>
          </fieldset>

        <?php echo functions::form_draw_form_end(); ?>
      </div>

      <div class="col-sm-6 col-lg-4">
        <?php echo functions::form_draw_form_begin('export_form', 'post'); ?>

          <fieldset>
            <legend><?php echo language::translate('title_export', 'Export'); ?></legend>

            <div class="row">
              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_delimiter', 'Delimiter'); ?></label>
                <?php echo functions::form_draw_select_field('delimiter', [[', ('. language::translate('text_default', 'default') .')', ','], [';'], ['TAB', "\t"], ['|']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_enclosure', 'Enclosure'); ?></label>
                <?php echo functions::form_draw_select_field('enclosure', [['" ('. language::translate('text_default', 'default') .')', '"']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_escape_character', 'Escape Character'); ?></label>
                <?php echo functions::form_draw_select_field('escapechar', [['" ('. language::translate('text_default', 'default') .')', '"'], ['\\', '\\']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_charset', 'Charset'); ?></label>
                <?php echo functions::form_draw_encodings_list('charset', !empty($_POST['charset']) ? true : 'UTF-8'); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_line_ending', 'Line Ending'); ?></label>
                <?php echo functions::form_draw_select_field('eol', [['Win'], ['Mac'], ['Linux']], true); ?>
              </div>

              <div class="form-group col-sm-6">
                <label><?php echo language::translate('title_output', 'Output'); ?></label>
                <?php echo functions::form_draw_select_field('output', [[language::translate('title_file', 'File'), 'file'], [language::translate('title_screen', 'Screen'), 'screen']], true); ?>
              </div>
            </div>

            <?php echo functions::form_draw_button('export', language::translate('title_export', 'Export'), 'submit'); ?>
          </fieldset>

        <?php echo functions::form_draw_form_end(); ?>
      </div>
    </div>
  </div>
</div>

<script>
  $('form[name="import_form"] input[name="insert"]').change(function(){
    $('form[name="import_form"] input[name="reset"]').prop('checked', false).prop('disabled', !$(this).is(':checked'));
  }).trigger('change');
</script>
