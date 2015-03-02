<?php

  require_once('../includes/config.inc.php');
  
  require_once('includes/header.inc.php');
  require_once('includes/functions.inc.php');

   $database = new database(null);


 $products_query =  $database->query(
    "select id, categories from ". DB_TABLE_PRODUCTS .";"
 );

 while ($product = $database->fetch($products_query)) {

   $is_first = TRUE; 
   $categories = explode( ',', $product['categories']);

      foreach($categories as $category_id){
        
        if($is_first){
          //Make first default category
          $database->query(
          "update ". DB_TABLE_PRODUCTS ." set
          default_category_id = ". $category_id . "
          where id = '". (int)$product['id'] ."'
          limit 1;"
        );
       }
        $database->query(
          "insert into ". DB_TABLE_PRODUCTS_TO_CATEGORIES ."
          (product_id, category_id)
           values ('". (int)$product['id'] ."', '". $category_id ."');"
        );
        $is_first = FALSE;
      }
 }


?>

