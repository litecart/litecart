<?php
  document::$snippets['head_tags']['responsiveslides'] = '<link rel="stylesheet" href="'. WS_DIR_EXT .'responsiveslides/responsiveslides.min.css" media="screen" />';
  document::$snippets['foot_tags']['responsiveslides'] = '<script src="'. WS_DIR_EXT .'responsiveslides/responsiveslides.min.js"></script>';

  $box_slides_cache_id = cache::cache_id('box_slides', array('language'));
  if (cache::capture($box_slides_cache_id, 'file')) {

    $slides_query = database::query(
      "select s.*, si.caption, si.link from ". DB_TABLE_SLIDES ." s
      left join ". DB_TABLE_SLIDES_INFO ." si on (s.id = si.slide_id and si.language_code = '". database::input(language::$selected['code']) ."')
      where s.status
      and (s.languages = '' or find_in_set('". database::input(language::$selected['code']) ."', s.languages))
      and (s.date_valid_from <= '". date('Y-m-d H:i:s') ."')
      and (year(s.date_valid_to) < '1971' or s.date_valid_to >= '". date('Y-m-d H:i:s') ."')
      order by s.priority, s.name;"
    );

    if (database::num_rows($slides_query)) {

      $box_slides = new view();

      $box_slides->snippets['slides'] = array();

      while ($slide = database::fetch($slides_query)) {
        $box_slides->snippets['slides'][] = array(
          'id' => $slide['link'],
          'link' => $slide['link'],
          'image' => WS_DIR_IMAGES . $slide['image'],
          'caption' => $slide['caption'],
        );
      }

      echo $box_slides->stitch('views/box_slides');
    }

    cache::end_capture($box_slides_cache_id);
  }
