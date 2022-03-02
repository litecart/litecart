<?php
  $box_categories_cache_token = cache::token('box_categories', ['language']);
  if (cache::capture($box_categories_cache_token)) {

    $categories_query = functions::catalog_categories_query();

    $box_categories = new ent_view(FS_DIR_TEMPLATE . 'partials/box_categories.inc.php');

    $box_categories->snippets = [
      'categories' => [],
    ];

    list($width, $height) = functions::image_scale_by_width(480, settings::get('category_image_ratio'));

    while ($category = database::fetch($categories_query)) {
      $box_categories->snippets['categories'][] = [
        'id' => $category['id'],
        'name' => $category['name'],
        'link' => document::ilink('category', ['category_id' => $category['id']]),
        'image' => [
          'original' => 'images/' . $category['image'],
          'thumbnail' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $category['image'], $width, $height, settings::get('category_image_clipping')),
          'thumbnail_2x' => functions::image_thumbnail(FS_DIR_STORAGE . 'images/' . $category['image'], $width*2, $height*2, settings::get('category_image_clipping')),
          'aspect_ratio' => str_replace(':', '/', settings::get('category_image_ratio')),
          'viewport' => [
            'width' => $width,
            'height' => $height,
          ],
        ],
        'short_description' => $category['short_description'],
      ];
    }

    echo $box_categories;

    cache::end_capture($box_categories_cache_token);
  }
