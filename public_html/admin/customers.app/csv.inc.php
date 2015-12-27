<?php
  
  if (!empty($_POST['import'])) {
  
    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    
      $csv = file_get_contents($_FILES['file']['tmp_name']);
      
      if (empty($_POST['delimiter'])) {
        preg_match('/^([^(\r|\n)]+)/', $csv, $matches);
        if (strpos($matches[1], ',') !== false) {
          $_POST['delimiter'] = ',';
        } elseif (strpos($matches[1], ';') !== false) {
          $_POST['delimiter'] = ';';
        } elseif (strpos($matches[1], "\t") !== false) {
          $_POST['delimiter'] = "\t";
        } elseif (strpos($matches[1], '|') !== false) {
          $_POST['delimiter'] = '|';
        } else {
          trigger_error('Unable to determine CSV delimiter', E_USER_ERROR);
        }
      }
      
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
          'newsletter'
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
        'mobile' => $customer['mobile'],
        'newsletter' => $customer['newsletter'],
      );
    }
    
    ob_clean();
    
    if ($_POST['output'] == 'screen') {
      header('Content-type: text/plain; charset='. $_POST['charset']);
    } else {
      header('Content-type: application/csv; charset='. $_POST['charset']);
      header('Content-Disposition: attachment; filename=customers.csv');
    }
    
    switch($_POST['eol']) {
      case 'Linux':
        echo functions::csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], $_POST['charset'], "\r");
        break;
      case 'Max':
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
<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_csv_import_export', 'CSV Import/Export'); ?></h1>

<table style="width: 100%;">
  <tr>
    <td>
      <?php echo functions::form_draw_form_begin('import_form', 'post', '', true); ?>
      <h2><?php echo language::translate('title_import_to_csv', 'Import From CSV'); ?></h2>
      <table border="0" cellpadding="5" cellspacing="0" style="margin: -5px;">
        <tr>
          <td colspan="3"><?php echo language::translate('title_csv_file', 'CSV File'); ?></br>
            <?php echo functions::form_draw_file_field('file'); ?></td>
        </tr>
        <tr>
          <td><?php echo language::translate('title_delimiter', 'Delimiter'); ?><br />
            <?php echo functions::form_draw_select_field('delimiter', array(array(language::translate('title_auto', 'Auto') .' ('. language::translate('text_default', 'default') .')', ''), array(','),  array(';'), array('TAB', "\t"), array('|')), true, false, 'data-size="auto"'); ?></td>
          <td><?php echo language::translate('title_enclosure', 'Enclosure'); ?><br />
            <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false, 'data-size="auto"'); ?></td>
          <td><?php echo language::translate('title_escape_character', 'Escape Character'); ?><br />
            <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false, 'data-size="auto"'); ?></td>
        </tr>
        <tr>
          <td><?php echo language::translate('title_charset', 'Charset'); ?><br />
            <?php echo functions::form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1')), true, false, 'data-size="auto"'); ?></td>
          <td></td>
          <td></td>
        </tr>
        <tr>
          <td colspan="3"><?php echo functions::form_draw_button('import', language::translate('title_import', 'Import'), 'submit'); ?></td>
        </tr>
      </table>
      <?php echo functions::form_draw_form_end(); ?>
    </td>
    <td>
    <?php echo functions::form_draw_form_begin('export_form', 'post'); ?>
    <h2><?php echo language::translate('title_export_to_csv', 'Export To CSV'); ?></h2>
    <table border="0" cellpadding="5" cellspacing="0" style="margin: -5px;">
      <tr>
        <td><?php echo language::translate('title_delimiter', 'Delimiter'); ?><br />
          <?php echo functions::form_draw_select_field('delimiter', array(array(', ('. language::translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|')), true, false, 'data-size="auto"'); ?></td>
        <td><?php echo language::translate('title_enclosure', 'Enclosure'); ?><br />
          <?php echo functions::form_draw_select_field('enclosure', array(array('" ('. language::translate('text_default', 'default') .')', '"')), true, false, 'data-size="auto"'); ?></td>
        <td><?php echo language::translate('title_escape_character', 'Escape Character'); ?><br />
          <?php echo functions::form_draw_select_field('escapechar', array(array('" ('. language::translate('text_default', 'default') .')', '"'), array('\\', '\\')), true, false, 'data-size="auto"'); ?></td>
      </tr>
      <tr>
        <td><?php echo language::translate('title_charset', 'Charset'); ?><br />
          <?php echo functions::form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1')), true, false, 'data-size="auto"'); ?></td>
        <td><?php echo language::translate('title_line_ending', 'Line Ending'); ?><br />
          <?php echo functions::form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux')), true, false, 'data-size="auto"'); ?></td>
        <td><?php echo language::translate('title_output', 'Output'); ?><br />
          <?php echo functions::form_draw_select_field('output', array(array(language::translate('title_file', 'File'), 'file'), array(language::translate('title_screen', 'Screen'), 'screen')), true, false, 'data-size="auto"'); ?></td>
      </tr>
      <tr>
        <td colspan="3"><?php echo functions::form_draw_button('export', language::translate('title_export', 'Export'), 'submit'); ?></td>
      </tr>
    </table>
    <?php echo functions::form_draw_form_end(); ?>
    </td>
  </tr>
</table>