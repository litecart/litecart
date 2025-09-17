<?php

	class event {

		private static $_callbacks = [];
		private static $_fired_events = [];

		public static function register($event, $callback) {

			$checksum = crc32(functions::format_json($callback, false));

			if (!empty(self::$_callbacks[$event][$checksum])) {
				trigger_error("Callback already registered ($event)", E_USER_WARNING);
				return;
			}

			if (in_array($event, self::$_fired_events)) {
				call_user_func_array($callback, array_slice(func_get_args(), 2));
				return;
			}

			self::$_callbacks[$event][$checksum] = $callback;
		}

		public static function fire($event) {

			if (in_array($event, self::$_fired_events)) {
				trigger_error("Event already fired ($event)", E_USER_WARNING);
				return;
			}

			if (empty(self::$_callbacks[$event])) return;

			$args = array_slice(func_get_args(), 1);

			foreach (self::$_callbacks[$event] as $callback) {
				call_user_func_array($callback, $args);
			}

			self::$_fired_events[] = $event;
		}
	}
