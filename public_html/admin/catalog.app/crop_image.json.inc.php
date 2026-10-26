<?php

  ob_clean();

  header('Content-Type: application/json; charset='. language::$selected['charset']);

  try {

    if (empty($_POST['product_id'])) throw new Exception(language::translate('error_must_provide_product', 'You must provide a product'));
    if (empty($_POST['image_id'])) throw new Exception('Missing image_id');

    foreach (['x', 'y', 'width', 'height'] as $parameter) {
      if (!isset($_POST[$parameter])) throw new Exception('Missing '. $parameter);
    }

    $product = new ent_product((int)$_POST['product_id']);

    if (empty($product->data['id'])) throw new Exception(language::translate('error_invalid_product', 'Invalid product'));

    $product->crop_image((int)$_POST['image_id'], (int)$_POST['x'], (int)$_POST['y'], (int)$_POST['width'], (int)$_POST['height']);

    $filename = $product->data['images'][(int)$_POST['image_id']]['filename'];

    list($thumbnail_width, $thumbnail_height) = functions::image_scale_by_width(480, settings::get('product_image_ratio'));

  // document::href_rlink() appends the file mtime, so the urls bust the browser cache by themselves
    $json = [
      'image_id' => (int)$_POST['image_id'],
      'filename' => $filename,
      'original' => document::href_rlink(FS_DIR_STORAGE . 'images/' . $filename),
      'thumbnail' => document::href_rlink(FS_DIR_STORAGE . functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $filename, $thumbnail_width, $thumbnail_height, settings::get('product_image_clipping'))),
    ];

  } catch (Exception $e) {
    http_response_code(400);
    $json = ['error' => $e->getMessage()];
  }

  language::convert_characters($json, language::$selected['charset'], 'UTF-8');
  $json = json_encode($json, JSON_UNESCAPED_SLASHES);

  language::convert_characters($json, 'UTF-8', language::$selected['charset']);
  echo $json;

  exit;
