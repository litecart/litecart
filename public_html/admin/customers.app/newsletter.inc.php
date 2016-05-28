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

<h1 style="margin-top: 0px;"><?php echo $app_icon; ?> <?php echo language::translate('title_newsletter', 'Newsletter'); ?></h1>

<h2><?php echo language::translate('title_list_of_subscribers', 'List of Subscribers'); ?></h2>

<ul class="list-horizontal">
  <li><a href="<?php echo document::href_link('', array('template' => 'raw'), array('app', 'doc')); ?>">Raw</a></li>
  <li><a href="<?php echo document::href_link('', array('template' => 'email'), array('app', 'doc')); ?>">Email Formatted</a></li>
  <li><a href="<?php echo document::href_link('', array('template' => 'csv'), array('app', 'doc')); ?>">CSV</a></li>
</ul>
<div style="float: left; width: 50%;">
  <h2><?php echo language::translate('title_customers', 'Customers'); ?></h2>
<?php
  $output = '';

    switch($_GET['template']) {
      case 'csv':
        $output .= 'First Name;Last Name;Email Address' . PHP_EOL;
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

  echo functions::form_draw_textarea('subscribers', $output, 'style="width: 98%; height: 400px;"');
?>
</div>

<div style="float: right; width: 50%;">
  <h2><?php echo language::translate('title_guests', 'Guests'); ?></h2>
<?php
  $output = '';

    switch($_GET['template']) {
      case 'csv':
        $output .= 'First Name;Last Name;Email Address' . PHP_EOL;
        break;
    }

  $customers_query = database::query(
    "select customer_firstname as firstname, customer_lastname as lastname, customer_email as email from ". DB_TABLE_ORDERS ."
    where customer_id = 0
    group by customer_email
    order by customer_firstname, customer_lastname;"
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

  echo functions::form_draw_textarea('subscribers', $output, 'style="width: 98%; height: 400px;"');
?>
</div>

<ul id="service-providers" class="list-horizontal">
  <li>
    <a href="http://eepurl.com/JAeav" target="_blank" class="button">
      <img src="<?php echo WS_DIR_ADMIN . 'customers.app/mailchimp.png'; ?>" alt="" />
      <div class="name">MailChimp</div>
      <div class="offer">LiteCart gives you $30 free credits</div>
    </a>
  </li>
</ul>