<?php

  $box_slides_cache_token = cache::token('box_slides', ['language']);
  if (cache::capture($box_slides_cache_token)) {

    $slides_query = database::query(
      "select s.*, si.caption, si.link from ". DB_TABLE_PREFIX ."slides s
      left join ". DB_TABLE_PREFIX ."slides_info si on (s.id = si.slide_id and si.language_code = '". database::input(language::$selected['code']) ."')
      where s.status
      and (s.languages = '' or find_in_set('". database::input(language::$selected['code']) ."', s.languages))
      and (s.date_valid_from is null or s.date_valid_from <= '". date('Y-m-d H:i:s') ."')
      and (s.date_valid_to is null or s.date_valid_to >= '". date('Y-m-d H:i:s') ."')
      order by s.priority, s.name;"
    );

    if (database::num_rows($slides_query)) {

      $box_slides = new ent_view();

      $box_slides->snippets['slides'] = [];

      while ($slide = database::fetch($slides_query)) {
        $box_slides->snippets['slides'][] = [
          'id' => $slide['id'],
          'name' => $slide['name'],
          'link' => $slide['link'],
          'image' => 'storage://images/' . $slide['image'],
          'caption' => $slide['caption'],
        ];
      }

      echo $box_slides->render(FS_DIR_TEMPLATE . 'partials/box_slides.inc.php');
    }

    cache::end_capture($box_slides_cache_token);
  }
