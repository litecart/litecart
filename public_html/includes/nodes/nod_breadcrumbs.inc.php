<?php

	class breadcrumbs {

		public static $data = [];

		public static function init() {

			self::add(functions::draw_fonticon('fa-home', 'title="'. functions::escape_attr(language::translate('title_home', 'Home')) .'"'), WS_DIR_APP);
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

			if (!count(self::$data)) {
				return '';
			}

			switch (route::$selected['endpoint']) {

				case 'backend':
					$view = new ent_view('app://backend/template/partials/breadcrumbs.inc.php');
					break;

				default:
					$view = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/breadcrumbs.inc.php');
					break;
			}

			$view->snippets['breadcrumbs'] = self::$data;

			return $view->render();
		}
	}
