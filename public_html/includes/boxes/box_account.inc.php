<?php

  if (empty(customer::$data['id'])) return;
  
  $account_snippets = array(
    'name' => customer::$data['firstname'] .' '. customer::$data['lastname'],
    'email' => customer::$data['email'],
  );
  
  echo document::stitch('file', 'box_account', $account_snippets);

 ?>