<?php

  class breadcrumbs {

    public static $data = [];

    public static function init() {

      self::add(language::translate('title_home', 'Home'), WS_DIR_APP);

      event::register('after_capture', [__CLASS__, 'after_capture']);
    }

    public static function after_capture() {

      if (count(self::$data) > 1) {
        $breadcrumbs = new ent_view(FS_DIR_TEMPLATE . 'partials/breadcrumbs.inc.php');

        $breadcrumbs->snippets['breadcrumbs'] = [];
        foreach (self::$data as $breadcrumb) {
          $breadcrumbs->snippets['breadcrumbs'][] = [
            'title' => $breadcrumb['title'],
            'link' => $breadcrumb['link'],
          ];
        }

        document::$snippets['breadcrumbs'] = $breadcrumbs;
      }
    }

    ######################################################################

    public static function reset() {
      self::$data = [];
    }

    public static function add(string $title, string $link='') {
      self::$data[] = [
        'title' => $title,
        'link' => $link,
      ];
    }
  }
