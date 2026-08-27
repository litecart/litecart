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
        'itemListElement' => [],
      ];

      $i = 1;
      foreach (self::$data as $breadcrumb) {
        if (empty($breadcrumb['link'])) continue;
        document::$schema['breadcrumbs']['itemListElement'][] = [
          '@type' => 'ListItem',
          'position' => $i++,
          'name' => $breadcrumb['title'],
          'url' => $breadcrumb['link'],
        ];
      }

      $breadcrumbs = new ent_view();

      $breadcrumbs->snippets['breadcrumbs'] = [];

      foreach (self::$data as $breadcrumb) {
        if (empty($breadcrumb['link'])) continue;
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
