<?php
  if (!isset($_GET['template'])) $_GET['template'] = '';
?>
<style>
#service-providers li a {
  position: relative;
  padding: 10px;
}
#service-providers li img {
  position: absolute;
  top: 12px;
  left: 10px;
  width: 32px;
  height: 32px;
  vertical-align: middle;
}
#service-providers li .name {
  font-size: 1.5em;
  margin-left: 40px;
}
#service-providers li .offer {
  margin-left: 40px;
}
</style>

<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle; margin-right: 10px;" /><?php echo language::translate('title_newsletter', 'Newsletter'); ?></h1>

<h2><?php echo language::translate('title_list_of_subscribers', 'List of Subscribers'); ?></h2>

<ul class="list-horizontal">
  <li><a href="<?php echo document::href_link('', array('template' => 'raw'), array('app', 'doc')); ?>">Raw</a></li>
  <li><a href="<?php echo document::href_link('', array('template' => 'email'), array('app', 'doc')); ?>">E-mail Formatted</a></li>
  <li><a href="<?php echo document::href_link('', array('template' => 'csv'), array('app', 'doc')); ?>">CSV</a></li>
</ul>

<?php
  $output = '';
  
    switch($_GET['template']) {
      case 'csv':
        $output .= 'First Name;Last Name;E-mail Address' . PHP_EOL;
        break;
    }
  
  $customers_query = database::query(
    "select firstname, lastname, email from ". DB_TABLE_CUSTOMERS ."
    where newsletter
    order by firstname, lastname;"
  );
  while ($customer = database::fetch($customers_query)) {
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
  
  echo functions::form_draw_textarea('subscribers', $output, 'style="width: 100%; height: 400px;"');
?>

<ul id="service-providers" class="list-horizontal">
  <li>
    <a href="http://eepurl.com/JAeav" target="_blank" class="button">
      <img src="<?php echo WS_DIR_ADMIN . 'customers.app/mailchimp.png'; ?>" />
      <div class="name">MailChimp</div>
      <div class="offer">LiteCart gives you $30 free credits</div>
    </a>
  </li>
</ul>