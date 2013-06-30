<?php
  
  if (!empty($_POST['import'])) {
  
    if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    
      $csv = file_get_contents($_FILES['file']['tmp_name']);
      
      $csv = $system->functions->csv_decode($csv);
      
      foreach ($csv as $row) {
        
        $customers_query = $system->database->query(
          "select id from ". DB_TABLE_CUSTOMERS ."
          where email = '". $row['email'] ."'
          limit 1;"
        );
        $customer = $system->database->fetch($customers_query);
        
        if (!empty($customer['id'])) {
          $customer = new ctrl_customer($customer['id']);
        } else {
          $customer = new ctrl_customer();
        }
        
        foreach (array('email', 'tax_id', 'company', 'firstname', 'lastname', 'address1', 'address2', 'postcode', 'city', 'country_code', 'zone_code', 'phone', 'newsletter') as $field) {
          if (isset($row[$field])) $customer->data[$field] = $row[$field];
        }
        
        if (empty($customer->data['id'])) $customer->set_password(md5(serialize($row)));
        
        $customer->save();
      }
      
      $system->notices->add('success', $system->language->translate('success_customers_imported', 'Customers successfully imported.'));
      
      header('Location: '. $system->document->link('', array('app' => $_GET['app'], 'doc' => $_GET['doc'])));
      exit;
    }
  }
  
  if (!empty($_POST['export'])) {
  
    $customers_query = $system->database->query(
      "select * from ". DB_TABLE_CUSTOMERS ."
      order by date_created asc;"
    );
    
    $csv = array();
    
    while ($customer = $system->database->fetch($customers_query)) {
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
    
    if ($_POST['output'] == 'screen') {
      header('Content-type: text/plain; charset='. $_POST['charset']);
    } else {
      header('Content-type: application/csv; charset='. $_POST['charset']);
      header('Content-Disposition: attachment; filename=customers.csv');
    }
    
    switch($_POST['eol']) {
      case 'Linux':
        echo $system->functions->csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], "\r", $_POST['charset']);
        break;
      case 'Max':
        echo $system->functions->csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], "\n", $_POST['charset']);
        break;
      case 'Win':
      default:
        echo $system->functions->csv_encode($csv, $_POST['delimiter'], $_POST['enclosure'], $_POST['escapechar'], "\r\n", $_POST['charset']);
        break;
    }
    
    exit;
  }

?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" border="0" align="absmiddle" style="margin-right: 10px;" /><?php echo $system->language->translate('title_csv_import_export', 'CSV Import/Export'); ?></h1>

<div id="import-wrapper" style="margin-bottom: 20px;">
  <?php echo $system->functions->form_draw_form_begin('import_form', 'post', '', true); ?>
  <h2><?php echo $system->language->translate('title_import_to_csv', 'Import From CSV'); ?></h2>
  <table border="0" cellpadding="5" cellspacing="0" style="margin: -5px;">
    <tr>
      <td colspan="3"><?php echo $system->language->translate('title_csv_file', 'CSV File'); ?></br>
        <?php echo $system->functions->form_draw_file_field('file'); ?></td>
    </tr>
    <tr>
      <td><?php echo $system->language->translate('title_delimiter', 'Delimiter'); ?><br />
        <?php echo $system->functions->form_draw_select_field('delimiter', array(array(', ('. $system->language->translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|'))); ?></td>
      <td><?php echo $system->language->translate('title_enclosure', 'Enclosure'); ?><br />
        <?php echo $system->functions->form_draw_select_field('enclosure', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'))); ?></td>
      <td><?php echo $system->language->translate('title_escape_character', 'Escape Character'); ?><br />
        <?php echo $system->functions->form_draw_select_field('escapechar', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'), array('\\', '\\'))); ?></td>
    </tr>
    <tr>
      <td><?php echo $system->language->translate('title_charset', 'Charset'); ?><br />
        <?php echo $system->functions->form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1'))); ?></td>
      <td></td>
      <td></td>
    </tr>
    <tr>
      <td colspan="3"><?php echo $system->functions->form_draw_button('import', $system->language->translate('title_import', 'Import'), 'submit'); ?></td>
    </tr>
  </table>
  <?php echo $system->functions->form_draw_form_end(); ?>
</div>

<div id="import-wrapper">
  <?php echo $system->functions->form_draw_form_begin('export_form', 'post'); ?>
  <h2><?php echo $system->language->translate('title_export_to_csv', 'Export To CSV'); ?></h2>
  <table border="0" cellpadding="5" cellspacing="0" style="margin: -5px;">
    <tr>
      <td><?php echo $system->language->translate('title_delimiter', 'Delimiter'); ?><br />
        <?php echo $system->functions->form_draw_select_field('delimiter', array(array(', ('. $system->language->translate('text_default', 'default') .')', ','), array(';'), array('TAB', "\t"), array('|'))); ?></td>
      <td><?php echo $system->language->translate('title_enclosure', 'Enclosure'); ?><br />
        <?php echo $system->functions->form_draw_select_field('enclosure', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'))); ?></td>
      <td><?php echo $system->language->translate('title_escape_character', 'Escape Character'); ?><br />
        <?php echo $system->functions->form_draw_select_field('escapechar', array(array('" ('. $system->language->translate('text_default', 'default') .')', '"'), array('\\', '\\'))); ?></td>
    </tr>
    <tr>
      <td><?php echo $system->language->translate('title_charset', 'Charset'); ?><br />
        <?php echo $system->functions->form_draw_select_field('charset', array(array('UTF-8'), array('ISO-8859-1'))); ?></td>
      <td><?php echo $system->language->translate('title_line_ending', 'Line Ending'); ?><br />
        <?php echo $system->functions->form_draw_select_field('eol', array(array('Win'), array('Mac'), array('Linux'))); ?></td>
      <td><?php echo $system->language->translate('title_output', 'Output'); ?><br />
        <?php echo $system->functions->form_draw_select_field('output', array(array($system->language->translate('title_file', 'File'), 'file'), array($system->language->translate('title_screen', 'Screen'), 'screen'))); ?></td>
    </tr>
    <tr>
      <td colspan="3"><?php echo $system->functions->form_draw_button('export', $system->language->translate('title_export', 'Export'), 'submit'); ?></td>
    </tr>
  </table>
  <?php echo $system->functions->form_draw_form_end(); ?>
</div>