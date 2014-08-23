<?php
  if (empty(customer::$data['id'])) return;
  
  $box_account = new view();
  
  $box_account->snippets = array(
    'name' => customer::$data['firstname'] .' '. customer::$data['lastname'],
    'email' => customer::$data['email'],
  );
  
  echo $box_account->stitch('views/box_account');
 ?>