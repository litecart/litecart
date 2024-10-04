<?php

  class breadcrumbs {

    public static $data = [];

    public static function init() {

      self::add(language::translate('title_home', 'Home'), WS_DIR_APP);

      event::register('prepare_output', [__CLASS__, 'prepare_output']);
    }

    public static function prepare_output() {

      if (count(self::$data) <= 1) {
        return;
      }

      document::$schema['breadcrumbs'] = [
        '@context' => 'https://schema.org/',
        '@type' => 'BreadcrumbList',
        'numberOfItems' => count(self::$data),
        'itemListElement' => array_map(function($breadcrumb, $position){
          return [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $breadcrumb['title'],
            'url' => $breadcrumb['link'],
          ];
        }, self::$data, range(0, count(self::$data)-1))
      ];

      $breadcrumbs = new ent_view();

      $breadcrumbs->snippets['breadcrumbs'] = [];
      foreach (self::$data as $breadcrumb) {
        $breadcrumbs->snippets['breadcrumbs'][] = [
          'title' => $breadcrumb['title'],
          'link' => $breadcrumb['link'],
        ];
      }

      document::$snippets['breadcrumbs'] = $breadcrumbs->stitch('views/breadcrumbs');
    }

    ######################################################################

    public static function reset() {
      self::$data = [];
    }

    public static function add($title, $link=null) {
      self::$data[] = [
        'title' => $title,
        'link' => $link,
      ];
    }
  }
