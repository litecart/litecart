<?php

	/*
		Array object with chained methods
		$array = new type_array(['c', 'b', 'a']);
		$array->map(fn($val) => $val.$val)->slice(0, 2)->sort()->value; // ['aa', 'bb']
	*/

	class type_array implements JsonSerializable {
		private $_array;

		public function __construct(array $array) {
			$this->_array = $array;
		}

		public function __get(string $name): mixed {

			switch ($name) {

				case 'value':
					return $this->_array;

				case 'length':
					return count($this->_array);

				default:
					trigger_error('Unknown array property ('.$name.')', E_USER_WARNING);
			}
		}

		public function __set(string $name, mixed $value): void {

			switch ($name) {

				case 'value':
					$this->_array = $value;

				case 'first_key':
					array_key_first(array_map('trim', $this->_array));

				case 'last_key':
					array_key_last(array_map('trim', $this->_array));

				default:
					trigger_error('Unknown array property ('.$name.')', E_USER_WARNING);
			}
		}

		public function __call(string $method, array $args): mixed {

			switch ($method) {

				// Aliases
				case 'each':
					array_map($args[0], $this->_array);
					return $this;

				case 'filter_keys':
					return $this->keys($args);

				// Functions with self in first argument
				case 'change_key_case':
				case 'chunk':
				case 'column':
				case 'count':
				case 'count_values':
				case 'diff_assoc':
				case 'diff_key':
				case 'diff_uassoc':
				case 'diff_ukey':
				case 'diff':
				case 'filter':
				case 'flip':
				case 'intersect_assoc':
				case 'intersect_key':
				case 'intersect_uassoc':
				case 'intersect_ukey':
				case 'intersect':
				case 'is_list':
				case 'keys': // filter_keys
				case 'map':
				case 'merge_recursive':
				case 'merge':
				case 'multisort':
				case 'pad':
				case 'product':
				case 'push':
				case 'rand':
				case 'reduce':
				case 'replace_recursive':
				case 'replace':
				case 'reverse':
				case 'slice':
				case 'sum':
				case 'udiff_assoc':
				case 'udiff_uassoc':
				case 'udiff':
				case 'uintersect_assoc':
				case 'uintersect_uassoc':
				case 'uintersect':
				case 'unique':
				case 'unshift':
				case 'values':
					$this->_array = call_user_func_array('array_'.$method, array_merge([$this->_array], $args));
					return $this;

				// Functions with self in first argument passed by reference
				case 'shuffle':
				case 'sort':
				case 'splice':
				case 'uasort':
				case 'uksort':
				case 'usort':
				case 'walk':
				case 'walk_recursive':
					call_user_func_array('array_'.$method, array_merge([&$this->_array], $args));
					return $this;

				// Functions with self in second argument
				case 'array_combine':
				case 'array_fill_keys':
				case 'array_fill':
				case 'array_key_exists':
				case 'array_search':
					$this->_array = call_user_func_array('array_'.$method, array_merge([$args[0], $this->_array], array_slice($args, 2)));
					return $this;

				// Functions with self in first argument passed by reference, returned value is not the array
				case 'pop':
				case 'shift':
					return call_user_func_array('array_'.$method, array_merge([&$this->_array], $args));

				default:
					trigger_error('Unknown array method ('.$method.')', E_USER_WARNING);
			}
		}

		public function jsonSerialize(): mixed {
			return $this->_array;
		}
	}
