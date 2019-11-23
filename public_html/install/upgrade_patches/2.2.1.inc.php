<?php

  $categories_images_query = database::query(
    "select id from `". DB_TABLE_PREFIX ."categories_images`
    group by category_id
    having count(*) >= 2;"
  );

  if (!database::num_rows($categories_images_query)) {
    unlink(FS_DIR_APP . 'vqmod/xml/multiple_category_images.xml');
  }
