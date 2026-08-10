<?php

	class breadcrumbs {

		public static $data = [];

		public static function init(): void {
		}

		## Node specific methods

		public static function reset(): void {
			self::$data = [];
		}

		public static function add(string $title, ?string $link=''): void {
			self::$data[] = [
				'title' => $title,
				'link' => ($link === true) ? document::link() : $link,
			];
		}

		public static function render(): string {

			if (!count(self::$data)) {
				return '';
			}

			$view = match(route::$selected['endpoint'] ?? null) {
				'backend' => new ent_view('app://backend/template/partials/breadcrumbs.inc.php'),
				default => new ent_view('app://frontend/templates/'.settings::get('template').'/partials/breadcrumbs.inc.php'),
			};

			$view->snippets['breadcrumbs'] = self::$data;

			return $view->render();
		}
	}
