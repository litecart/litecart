<h1 style="margin-top: 0px;"><img src="<?php echo WS_DIR_ADMIN . $_GET['app'] .'.app/icon.png'; ?>" width="32" height="32" style="vertical-align: middle;" style="margin-right: 10px;" /><?php echo $system->language->translate('title_newsletter', 'Newsletter'); ?></h1>

<h2><?php echo $system->language->translate('title_list_of_subscribers', 'List of Subscribers'); ?></h2>

<?php
  $subscribers = array();
  
  $customers_query = $system->database->query(
    "select firstname, lastname, email from ". DB_TABLE_CUSTOMERS ."
    where newsletter
    order by rand();"
  );
  while ($customer = $system->database->fetch($customers_query)) {
    //$subscribers[] = '"'. $customer['firstname'] .' '. $customer['lastname'] .'" <'. $customer['email'] .'>';
    $subscribers[] = $customer['email'];
  }
  
  //echo $system->functions->form_draw_textarea('subscribers', implode("; ", $subscribers));
  echo $system->functions->form_draw_textarea('subscribers', implode("\r\n", $subscribers), 'style="width: 100%; height: 400px;"');
?>

<ul class="navigation-horizontal">
  <li><a href="http://www.mailchimp.com" target="_blank">mailchimp.com</a></li>
  <li><a href="http://www.getanewsletter.com" target="_blank">getanewsletter.com</a></li>
</ul>