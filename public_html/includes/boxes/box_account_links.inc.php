<?php
  if (!empty(customer::$data['id'])) {

    $box_account = new ent_view();

    $box_account->snippets = array(
      'name' => customer::$data['firstname'] .' '. customer::$data['lastname'],
      'email' => customer::$data['email'],
    );

    echo $box_account->stitch('views/box_account_links');

  } else if (empty(route::$route['page']) || route::$route['page'] != 'login') {

    $box_account_login = new ent_view();

    echo $box_account_login->stitch('views/box_account_login');
  }
