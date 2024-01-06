<?php

  class breadcrumbs {

    public static $data = [];

    public static function init() {

      self::add(functions::draw_fonticon('fa-home', 'title="'. functions::escape_html(language::translate('title_home', 'Home')) .'"'), WS_DIR_APP);
    }

    public static function reset() {
      self::$data = [];
    }

    public static function add($title, $link='') {
      self::$data[] = [
        'title' => $title,
        'link' => ($link === true) ? document::link() : $link,
      ];
    }

    public static function render() {

      if (count(self::$data) <= 1) return '';

      if (preg_match('#^'. preg_quote(BACKEND_ALIAS, '#') .'#', route::$request)) {
        $view = new ent_view('app://backend/template/partials/breadcrumbs.inc.php');
      } else {
        $view = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/breadcrumbs.inc.php');
      }

      $view->snippets['breadcrumbs'] = self::$data;

      return $view->render();
    }
  }
