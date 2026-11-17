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

			$view = new ent_view(match(route::$selected['endpoint'] ?? null) {
				'backend' => 'app://backend/template/partials/breadcrumbs.inc.php',
				default => 'app://frontend/templates/'.settings::get('template').'/partials/breadcrumbs.inc.php',
			}, [
				'breadcrumbs' => self::$data,
			]);

			return $view->render();
		}
	}
