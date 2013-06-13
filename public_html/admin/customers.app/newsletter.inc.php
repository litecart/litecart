<?php
  if (!isset($_GET['template'])) $_GET['template'] = '';
?>
<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo $system->language->translate('title_newsletter', 'Newsletter'); ?></h1>

<h2><?php echo $system->language->translate('title_list_of_subscribers', 'List of Subscribers'); ?></h2>

<ul class="list-horizontal">
  <li><a href="<?php echo $system->document->href_link('', array('template' => 'raw'), array('app', 'doc')); ?>">Raw</a></li>
  <li><a href="<?php echo $system->document->href_link('', array('template' => 'email'), array('app', 'doc')); ?>">E-mail Formatted</a></li>
  <li><a href="<?php echo $system->document->href_link('', array('template' => 'csv'), array('app', 'doc')); ?>">CSV</a></li>
</ul>

<?php
  $output = '';
  
    switch($_GET['template']) {
      case 'csv':
        $output .= 'First Name;Last Name;E-mail Address' . PHP_EOL;
        break;
    }
  
  $customers_query = $system->database->query(
    "select firstname, lastname, email from ". DB_TABLE_CUSTOMERS ."
    where newsletter
    order by firstname, lastname;"
  );
  while ($customer = $system->database->fetch($customers_query)) {
    switch($_GET['template']) {
      case 'email':
        $output .= '"'. $customer['firstname'] .' '. $customer['lastname'] .'" <'. $customer['email'] .'>;' . PHP_EOL;
        break;
      case 'csv':
        $output .= implode(';', array($customer['firstname'], $customer['lastname'], $customer['email'])) . PHP_EOL;
        break;
      case 'raw':
      default:
        $output .= $customer['email'] . PHP_EOL;
        break;
    }
  }
  
  echo $system->functions->form_draw_textarea('subscribers', $output, 'style="width: 100%; height: 400px;"');
?>

<ul class="list-horizontal">
  <li><a href="http://www.mailchimp.com" target="_blank">MailChimp</a></li>
  <li><a href="http://www.getanewsletter.com" target="_blank">Get A Newsletter</a></li>
</ul>