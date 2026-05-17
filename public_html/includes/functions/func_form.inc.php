<?php

	// Helper function to parse a legacy string of HTML attributes into an array of key-value pairs
	function form_attributes($attributes) {

		if (is_array($attributes)) {
			return $attributes;
		}

		$characters = mb_str_split($attributes);
		$length = mb_strlen($attributes);

		$in = '';
		$key = '';
		$value = '';
		$result = [];

		for ($i=0; $i < $length; $i++) {

			switch ($in) {
				case 'key':

					if ($characters[$i] == '=') {
						$in = 'value';
					} else if ($characters[$i] == ' ') {
						$result[trim($key)] = '';
						$key = '';
					} else {
						$key .= $characters[$i];
					}

					break;

				case 'value':

					if ($characters[$i] == '"' || $characters[$i] == '\'') {
						$quote = $characters[$i];
						$in = 'quoted_value';
					} else if ($characters[$i] == ' ') {
						$result[trim($key)] = trim($value);
						$key = '';
						$value = '';
						$in = '';
					} else {
						$value .= $characters[$i];
					}

					break;

				case 'quoted_value':

					if ($characters[$i] == $quote) {
						$result[trim($key)] = $value;
						$key = '';
						$value = '';
						$in = '';
					} else {
						$value .= $characters[$i];
					}

					break;

				default:

					if ($characters[$i] != ' ') {
						$key .= $characters[$i];
						$in = 'key';
					}

					break;
			}
		}

		return $result;
	}

	function form_begin($name='', $method='post', $action='', $multipart=false, $attributes=[]) {

		$attributes = is_array($attributes) ? implode(' ', array_map(function($k, $v) { return $k.'="'. f::escape_attr($v) .'"'; }, array_keys($attributes), $attributes)) : $attributes;

		$html = '<form'. (($name) ? ' name="'. f::escape_attr($name) .'"' : '') .' method="'. ((strtolower($method) == 'get') ? 'get' : 'post') .'" enctype="'. (($multipart == true) ? 'multipart/form-data' : 'application/x-www-form-urlencoded') .'" accept-charset="'. mb_http_output() .'"'. (($action) ? ' action="'. f::escape_attr($action) .'"' : '') . ($attributes ? ' '. $attributes : '') .'>';

		// Auto-inject CSRF token for POST forms
		if (strtolower($method) !== 'get' && class_exists('session', false)) {
			$html .= f::draw_element('input', ['type' => 'hidden', 'name' => 'csrf_token', 'value' => session::csrf_token()]);
		}

		return $html;
	}

	function form_end() {
		return '</form>';
	}

	function form_reinsert_value($name) {

		if ($name == '') {
			return '';
		}

		foreach ([$_POST, $_GET] as $superglobal) {

			if (!$superglobal) continue;

			// Extract name parts
			$parts = preg_split('#[\]\[]+#', preg_replace('#\[\]$#', '', $name), -1, PREG_SPLIT_NO_EMPTY);

			// Get array node
			$node = $superglobal;

			foreach ($parts as $part) {
				if (!isset($node[$part])) continue 2;
				$node = $node[$part];
			}

			return ($node != '') ? $node : '';
		}

		return preg_match('#\[\]$#', $name) ? [] : '';
	}

	function form_button($name, $value, $type='submit', $attributes=[], $fonticon='') {

		if (!is_array($value)) {
			$value = [$value, $value];
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('button', ['class' => 'btn btn-default', 'type' => $type, 'name' => $name, 'value' => $value[0], ...$attributes], ($fonticon ? f::draw_fonticon($fonticon) . ' ' : '') . ($value[1] ?? $value[0]));
	}

	function form_button_predefined($name, $attributes=[]) {

		$button = match($name) {
			'cancel' => f::form_button('cancel', t('title_cancel', 'Cancel'), 'button', ['onclick' => 'history.go(-1);'] + $attributes, 'cancel'),
			'delete' => f::form_button('delete', t('title_delete', 'Delete'), 'submit', ['formnovalidate' => true, 'class' => 'btn btn-danger', 'onclick' => 'if (!confirm(&quot;'. t('text_are_you_sure', 'Are you sure?') .'&quot;)) return false;'] + $attributes, 'delete'),
			'enable' => f::form_button('enable', t('title_enable', 'Enable'), 'submit', $attributes, 'on'),
			'disable' => f::form_button('disable', t('title_disable', 'Disable'), 'submit', $attributes, 'off'),
			'move-up' => f::form_button('move_up', t('title_move_up', 'Move Up'), 'button', ['class' => 'btn btn-default'] + $attributes, 'move-up'),
			'move-up-sm' => f::form_button('move_up', '', 'button', ['title' => t('title_move_up', 'Move Up'), 'class' => 'btn btn-default btn-sm'] + $attributes, 'move-up'),
			'move-down' => f::form_button('move_down', t('title_move_up', 'Move Up'), 'button', ['class' => 'btn btn-default'] + $attributes, 'move-down'),
			'move-down-sm' => f::form_button('move_down', '', 'button', ['title' => t('title_move_down', 'Move Down'), 'class' => 'btn btn-default btn-sm'] + $attributes, 'move-down'),
			'remove' => f::form_button('remove', t('title_remove', 'Remove'), 'button', ['class' => 'btn btn-default'] + $attributes, 'remove'),
			'remove-sm' => f::form_button('remove', '', 'button', ['title' => t('title_remove', 'Remove'), 'class' => 'btn btn-default btn-sm'] + $attributes, 'remove'),
			'save' => f::form_button('save', t('title_save', 'Save'), 'submit', ['class' => 'btn btn-success'] + $attributes, 'save'),
			'quicksave' => implode(PHP_EOL, [
				'<div class="btn-group">',
				'	'. f::form_button('quicksave', ['true', ''], 'submit', ['class' => 'btn btn-success btn-icon', 'title' => t('title_quicksave', 'Quicksave'), 'style' => 'padding-left: .75em; padding-right: .75em;'] + $attributes, 'save'),
				'	'. f::form_button('save', t('title_save', 'Save'), 'submit', ['class' => 'btn btn-success', 'style' => 'padding-left: .75em;'] + $attributes, 'save'),
				'</div>',
			]),
			'send' => f::form_button('send', t('title_send', 'Send'), 'submit', ['class' => 'btn btn-success'] + $attributes, 'send'),
		};

		if (!$button) {
			trigger_error('Unknown predefined button ('. f::escape_html($name) .')', E_USER_WARNING);
			$button = f::form_button($name, $name, 'submit', $attributes);
		}

		return $button;
	}

	function form_button_link($url, $title, $attributes=[], $fonticon='') {

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('a', ['class'=>'btn btn-default', 'href'=>$url, ...(is_array($attributes) ? $attributes : form_parse_attributes($attributes))], ($fonticon ? f::draw_fonticon($fonticon) . ' ' : '') . $title);
	}

	function form_button_link_predefined($name, $url, $attributes=[]) {

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		$button = match($name) {
			'create' => form_button_link($url, t('title_create', 'Create'), $attributes, 'add'),
			'edit' => form_button_link($url, t('title_edit', 'Edit'), $attributes, 'edit'),
			'edit-sm' => form_button_link($url, '', ['title' => t('title_edit', 'Edit')] + $attributes, 'edit'),
		};

		if (!$button) {
			trigger_error('Unknown predefined link button ('. f::escape_html($name) .')', E_USER_WARNING);
			$button = form_button_link($url, $name, $attributes);
		}

		return $button;
	}

	function form_captcha($id, $config=[], $attributes=[]) {

		$config = [
			'width' => $config['width'] ?? 100,
			'height' => $config['height'] ?? 40,
			'length' => $config['length'] ?? 4,
			'set' => $config['set'] ?? 'numbers',
		];

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::captcha_draw($id, $config, $attributes);
	}

	function form_checkbox($name, $value, $input=true, $attributes=[]) {

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		if (is_array($value)) {
			return f::draw_element('label', ['class' => 'form-check', ...$attributes], implode(PHP_EOL, [
				form_checkbox($name, $value[0], $input),
				$value[1] ?? $value[0],
			]));
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if (preg_match('#\[\]$#', $name)) {
			return f::draw_element('input', ['class' => 'form-check', 'type' => 'checkbox', 'name' => $name, 'value' => $value, ...$attributes] + ((is_array($input) && in_array($value, $input)) ? ['checked' => ''] : []));
		} else {
			return f::draw_element('input', ['class' => 'form-check', 'type' => 'checkbox', 'name' => $name, 'value' => $value, ...$attributes] + (!strcmp($input, $value) ? ['checked' => ''] : []));
		}
	}

	function form_dropdown($name, $options=[], $input=true, $attributes=[]) {

		$content = [];

		$is_numerical_index = array_is_list($options);

		foreach ($options as $key => $option) {

			if (!is_array($option)) {
				if ($is_numerical_index) {
					$option = [$option, $option];
				} else {
					$option = [$key, $option];
				}
			}

			if (preg_match('#\[\]$#', $name)) {
				$content[] = '<li class="dropdown-item">' . f::form_checkbox($name, $option, $input, $option[2] ?? '') .'</li>';
			} else {
				$content[] = '<li class="dropdown-item">' . f::form_radio_button($name, $option, $input, $option[2] ?? '') .'</li>';
			}
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('div', ['class' => 'dropdown', 'data-placeholder' => '-- '. t('title_select', 'Select') .' --', ...$attributes], implode(PHP_EOL, [
			'  <div class="form-select" data-toggle="dropdown">',
			'    -- '. t('title_select', 'Select') .' --',
			'  </div>',
			'  <ul class="dropdown-menu">',
			implode(PHP_EOL, $content),
			'  </ul>',
		]));
	}

	function form_input_code($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		document::$javascript[] = implode(PHP_EOL, [
			'$(\'textarea[name="'. $name .'"]\').on(\'keydown\', function(e) {',
			'  if (e.keyCode != 9) return;',
			'  e.preventDefault();',
			' var start = this.selectionStart, end = this.selectionEnd;',
			'  this.value = this.value.substring(0, start) + \'\t\' + this.value.substring(end);',
			'  this.selectionStart = this.selectionEnd = start + 1;',
			'});',
		]);

		return f::draw_element('textarea', ['class' => 'form-code', 'name' => $name, ...$attributes], f::escape_html($input));
	}

	function form_input_color($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-input', 'type' => 'color', 'name' => $name, 'value' => $input, ...$attributes]);
	}

	function form_input_csv($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if ($input && $csv = f::csv_decode($input)) {
			$columns = array_keys($csv[0]);
		} else {
			$csv = [];
			$columns = [];
		}

		$html = implode(PHP_EOL, [
			'<table class="table data-table" data-toggle="csv">',
			'  <thead>',
			'    <tr>',

			implode(PHP_EOL, f::array_each($columns, fn($column) =>
				'      <th>'. $column .'<button name="remove_column" class="btn btn default btn-sm">'. f::draw_fonticon('remove') .'</button></th>'
			)),

			'      <th><button class="btn btn-default btn-sm" name="add_column" type="button">'. f::draw_fonticon('add') .' '.  t('title_add_column', 'Add Column') .'</button></th>',
			'    </tr>',
			'  </thead>',
			'  <tbody>',
		]);

		foreach ($csv as $row) {
			$html .= '    <tr>' . PHP_EOL;
			foreach ($columns as $column) {
				$html .= '      <td contenteditable>'. $row[$column] .'</td>' . PHP_EOL;
			}
			$html .= '      <td><button name="remove_row" class="btn btn default btn-sm">'. f::draw_fonticon('remove') .'</button></td>' . PHP_EOL;
			$html .= '    </tr>' . PHP_EOL;
		}

		$html .= implode(PHP_EOL, [
			'  <tfoot>',
			'    <tr>',
			'      <td colspan="99">',
			'        <button class="btn btn-default btn-sm" name="add_row" type="button">',
			'          '. f::draw_fonticon('add') .' '.  t('title_add_row', 'Add Row'),
			'        </button>',
			'      </td>',
			'    </tr>',
			'  </tfoot>',
			'</table>',
			form_textarea($name, $input, 'style="display: none;"'),
		]);

		document::$javascript['table2csv'] = implode(PHP_EOL, [
			'$(\'table[data-toggle="csv"]\').on(\'click\', \'button[name="remove_row"]\', function(e) {',
			'  e.preventDefault()',
			'  var $parent = $(this).closest(\'tbody\')',
			'  $(this).closest(\'tr\').remove()',
			'  $parent.trigger(\'input\')',
			'})',
			'',
			'$(\'table[data-toggle="csv"] button[name="add_row"]\').on(\'click\', function(e) {',
			'  e.preventDefault();',
			'  var n = $(this).closest(\'table\').find(\'thead th:not(:last-child)\').length',
			'  $(this).closest(\'table\').find(\'tbody\').append(',
			'    \'<tr>\' + (\'<td contenteditable></td>\'.repeat(n)) + \'<td><button name="remove_row" class="btn btn default btn-sm">'. f::draw_fonticon('remove') .'</button></td>\' +\'</tr>\'',
			'  ).trigger(\'input\')',
			'})',
			'',
			'$(\'table[data-toggle="csv"] button[name="add_column"]\').on(\'click\', function(e) {',
			'  e.preventDefault()',
			'  var $table = $(this).closest(\'table\')',
			'  var title = prompt("'. f::escape_js(t('title_column_title', 'Column Title')) .'")',
			'  if (!title) return',
			'  $(\'thead tr th:last-child:last-child\', $table).before(\'<th>\'+ title +\'<button name="remove_column" class="btn btn default btn-sm">'. f::draw_fonticon('remove') .'</button></th>\')',
			'  $(\'tbody tr td:last-child:last-child\', $table).before(\'<td contenteditable></td>\')',
			'  $(\'tfoot tr td\', $table).attr(\'colspan\', $table.find(\'tfoot tr td\').attr(\'colspan\') + 1)',
			'  $(this).trigger(\'input\')',
			'});',
			'',
			'$(\'table[data-toggle="csv"]\').on(\'input\', function(e) {',
			'   var csv = $(\'thead tr, tbody tr\', this).map(function (i, $row) {',
			'      return $(\'th:not(:last-child, $row),td:not(:last-child)\').map(function (j, $col) {',
			'        var $col = $(this)',
			'        var text = $col.text()',
			'        if (/(\'|,)/.test(text)) {',
			'          return "\\"\'"+ text.replace(/"/g, "\\"\"") +"\\""',
			'        } else {',
			'          return text',
			'        }',
			'      }).get().join(\',\')',
			'    }).get().join(\'\\r\\n\')',
			'  $(this).next(\'textarea\').val(csv)',
			'});',
		]);

		return $html;
	}

	function form_input_date($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if ($input && !in_array(substr($input, 0, 10), ['0000-00-00', '1970-01-01'])) {
			$input = date('Y-m-d', strtotime($input));
		} else {
			$input = '';
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-input', 'type' => 'date', 'name' => $name, 'value' => $input, 'placeholder' => 'YYYY-MM-DD', ...$attributes]);
	}

	function form_input_datetime($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if ($input && !in_array(substr($input, 0, 10), ['0000-00-00', '1970-01-01'])) {
			$input = date('Y-m-d\TH:i', strtotime($input));
		} else {
			$input = '';
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-input', 'type' => 'datetime-local', 'name' => $name, 'value' => $input, 'placeholder' => 'YYYY-MM-DD [hh:nn]', ...$attributes]);
	}

	function form_input_decimal($name, $input=true, $decimals=null, $attributes=[]) {

		if (count($args = func_get_args()) > 4) {
			trigger_error('Passing min and max as 3rd and 4th parameter in form_input_decimal() is deprecated. Instead define min="0" and max="999" in 3rd parameter $attributes', E_USER_DEPRECATED);
			if (isset($args[5])) $attributes = $args[5];
			if (isset($args[3])) $attributes .= ($attributes ? ' ' : '') . 'min="'. (int)$args[3] .'"';
			if (isset($args[4])) $attributes .= ($attributes ? ' ' : '') . 'max="'. (int)$args[4] .'"';
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if ($input != '' && is_numeric($decimals)) {

			// Circumvent floating point precision problem if differing by one 10th of the smallest fraction

			$fractions = strpos($input, '.') ? strlen(substr(strrchr($input, '.'), 1)) : 0;
			$absdiff = abs((float)$input - round((float)$input, 2));
			$offset = (1 / pow(10, $decimals+1));

			if ($fractions < $decimals) {
				$input = number_format((float)$input, $decimals, '.', '');
			} else if ($absdiff > $offset) {
				$input = number_format((float)$input, $decimals+2, '.', '');
			} else {
				$input = number_format((float)$input, $decimals, '.', '');
			}
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-input', 'type' => 'number', 'name' => $name, 'value' => $input, 'step' => 'any', 'data-decimals' => (int)$decimals, ...$attributes]);
	}

	function form_input_decimal_toggle($name, $input=true, $decimals=null, $attributes=[]) {

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <button name="decrease" class="btn btn-default" type="button">-</button>',
			'  ' . form_input_decimal($name, $input, $decimals, $attributes),
			'  <button name="increase" class="btn btn-default" type="button">+</button>',
			'</div>',
		]);
	}

	function form_input_email($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-icon">'. f::draw_fonticon('icon-envelope') .'</span>',
			'  ' . f::draw_element('input', ['class' => 'form-input', 'type' => 'email', 'name' => $name, 'value' => $input, ...$attributes]),
			'</div>',
		]);
	}

	function form_input_file($name, $attributes=[]) {

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-input', 'type' => 'file', 'name' => $name, ...$attributes]);
	}

	function form_input_hidden($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['type' => 'hidden', 'name' => $name, 'value' => $input, ...$attributes]);
	}

	function form_input_month($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if (!in_array(substr($input, 0, 7), ['', '0000-00', '1970-00', '1970-01'])) {
			$input = date('Y-m', strtotime($input));
		} else {
			$input = '';
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-input', 'type' => 'month', 'name' => $name, 'value' => $input, 'maxlength' => 7, 'pattern' => '\d{4}-\d{2}', 'placeholder' => 'YYYY-MM', ...$attributes]);
	}

	function form_input_money($name, $currency_code=null, $input=true, $attributes=[]) {

		if (preg_match('#^[A-Z]{3}$#', $name) && !preg_match('#^[A-Z]{3}$#', $currency_code)) {
			trigger_error('Passing currency code as 1st parameter in form_input_money() is deprecated. Instead, use form_input_money($name, $currency_code, $input, $attributes)', E_USER_DEPRECATED);
			[$name, $currency_code] = [$currency_code, $name];
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if (!$currency_code) {
			$currency_code = settings::get('store_currency_code');
		}

		if (!empty(currency::$currencies[$currency_code])) {
			$decimals = currency::$currencies[$currency_code]['decimals'];
		} else {
			$decimals = 2;
		}

		if ($input != '') {
			$input = number_format((float)$input, $decimals, '.', '');
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <strong class="input-group-text" style="opacity: 0.75; font-family: monospace;">'. f::escape_html($currency_code) .'</strong>',
			'  ' . form_input_decimal($name, $input, $decimals, ['step' => 'any', 'data-type' => 'currency', 'placeholder' => f::format_number(0, $decimals), ...$attributes]) ,
			'</div>',
		]);
	}

	function form_input_number($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if ($input != '') {
			$input = round((int)$input);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-input', 'type' => 'number', 'name' => $name, 'value' => $input, 'step' => 1, ...$attributes]);
	}

	function form_input_number_toggle($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if ($input != '') {
			$input = round((int)$input);
		}

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <button name="decrease" class="btn btn-default" type="button">-</button>',
			'  '. form_input_number($name, $input, $attributes),
			'  <button name="increase" class="btn btn-default" type="button">+</button>',
			'</div>',
		]);
	}

	function form_input_password($name, $input='', $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-icon">'. f::draw_fonticon('icon-key') .'</span>',
			'  ' . f::draw_element('input', ['class' => 'form-input', 'type' => 'password', 'name' => $name, 'value' => $input, ...$attributes]),
			'</div>',
		]);
	}

	function form_input_password_unmaskable($name, $input='', $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-icon">'. f::draw_fonticon('icon-key') .'</span>',
			'  ' . f::draw_element('input', ['class' => 'form-input', 'type' => 'password', 'name' => $name, 'value' => $input, ...$attributes]),
			'  ' . f::draw_element('button', ['class' => 'btn btn-default', 'type' => 'button', 'onclick' => "$(this).prev().attr('type', ($(this).prev().attr('type') == 'password') ? 'text' : 'password');"], f::draw_fonticon('icon-eye')),
			'</div>',
		]);
	}

	function form_input_percent($name, $input=true, $decimals=2, $attributes=[]) {

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  ' . form_input_decimal($name, $input, $decimals, $attributes),
			'  <span class="input-group-text">%</span>',
			'</div>',
		]);
	}

	function form_input_phone($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-icon">'. f::draw_fonticon('icon-phone') .'</span>',
			'  ' . f::draw_element('input', ['class' => 'form-input', 'type' => 'tel', 'name' => $name, 'value' => $input, 'pattern' => '\+?(\d|-| )+', ...$attributes]),
			'</div>',
		]);
	}

	function form_radio_button($name, $value, $input=true, $attributes=[]) {

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		if (is_array($value)) {
			return f::draw_element('label', ['class' => 'form-check', ...$attributes], implode(PHP_EOL, [
				'  ' . form_radio_button($name, $value[0], $input, $attributes),
				'  ' . ($value[1] ?? $value[0]),
			]));
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-radio', 'type' => 'radio', 'name' => $name, 'value' => $value, ...$attributes] + (!strcmp($input, $value) ? ['checked' => ''] : []));
	}

	function form_input_range($name, $input=true, $min='', $max='', $step='', $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-range', 'type' => 'range', 'name' => $name, 'value' => $input, 'min' => (float)$min, 'max' => (float)$max, 'step' => (float)$step, ...$attributes]);
	}

	function form_input_search($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-icon">'. f::draw_fonticon('icon-search') .'</span>',
			'  ' . f::draw_element('input', ['class' => 'form-input', 'type' => 'search', 'name' => $name, 'value' => $input, ...$attributes]),
			'</div>',
		]);
	}

	function form_input_tags($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-input', 'type' => 'text', 'data-toggle' => 'tags', 'name' => $name, 'value' => $input, ...$attributes]);
	}

	function form_input_text($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-input', 'type' => 'text', 'name' => $name, 'value' => $input, ...$attributes]);
	}

	function form_input_time($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-input', 'type' => 'time', 'name' => $name, 'value' => $input, ...$attributes]);
	}

	function form_input_url($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-input', 'type' => 'url', 'name' => $name, 'value' => $input, ...$attributes]);
	}

	function form_input_username($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-icon">'. f::draw_fonticon('icon-user') .'</span>',
			'  ' . f::draw_element('input', ['class' => 'form-input', 'type' => 'text', 'name' => $name, 'value' => $input, ...$attributes]),
			'</div>',
		]);
	}

	function form_input_week($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if ($input && !in_array(substr($input, 0, 10), ['0000-00-00', '1970-01-01'])) {
			$input = date('Y-\WW', strtotime($input));
		} else {
			$input = '';
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('input', ['class' => 'form-input', 'type' => 'week', 'name' => $name, 'value' => $input, 'maxlength' => 7, 'pattern' => '\d{4}-W\d{2}', 'placeholder' => 'YYYY-WW', ...$attributes]);
	}

	function form_input_wysiwyg($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		document::load_style([
			'app://assets/trumbowyg/ui/trumbowyg.min.css',
			'app://assets/trumbowyg/plugins/colors/ui/trumbowyg.colors.min.css',
			'app://assets/trumbowyg/plugins/table/ui/trumbowyg.table.min.css',
		], 'trumbowyg');

		document::load_script([
			'app://assets/trumbowyg/trumbowyg.min.js',
			'app://assets/trumbowyg/plugins/colors/trumbowyg.colors.min.js',
			'app://assets/trumbowyg/plugins/upload/trumbowyg.upload.min.js',
			'app://assets/trumbowyg/plugins/table/trumbowyg.table.min.js',
		], 'trumbowyg');

		if (language::$selected['code'] != 'en') {
			document::load_script('app://assets/trumbowyg/langs/'. language::$selected['code'] .'.min.js', 'trumbowyg-lang');
		}

		document::$javascript[] = implode(PHP_EOL, [
			'$(\'textarea[name="'. $name .'"]\').trumbowyg({',
			'  btns: [["viewHTML"], ["formatting"], ["strong", "em", "underline", "del"], ["foreColor", "backColor"], ["link"], ["insertImage"], ["table"], ["justifyLeft", "justifyCenter", "justifyRight"], ["lists"], ["preformatted"], ["horizontalRule"], ["removeformat"], ["fullscreen"]],',
			'  btnsDef: {',
			'    lists: {',
			'      dropdown: ["unorderedList", "orderedList"],',
			'      title: "Lists",',
			'      ico: "unorderedList",',
			'    }',
			'  },',
			'  plugins: {',
			'    upload: {',
			'      serverPath: "'. document::href_rlink('app://assets/trumbowyg/plugins/upload/trumbowyg.upload.php') .'",',
			'    }',
			'  },',
			'  lang: "'. language::$selected['code'] .'",',
			//'  autogrowOnEnter: true,',
			'  imageWidthModalEdit: true,',
			'  removeformatPasted: true,',
			'  semantic: false',
			'});',
		]);

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('textarea', ['name' => $name, ...$attributes], f::escape_html($input));
	}

	function form_regional($name, $language_code='', $input=true, $type='text', $attributes=[]) {

		if (preg_match('#^[a-z]{2}$#', $name)) {
			trigger_error('Passing $language code as 1st parameter in form_regional_text() is deprecated. Instead, use form_regional_text($name, $language_code, $input, $attributes)', E_USER_DEPRECATED);
			[$name, $language_code] = [$language_code, $name];
		}

		if (!$language_code) {
			$language_code = settings::get('store_language_code');
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-text" style="font-family: monospace;" title="'. f::escape_attr(language::$languages[$language_code]['name']) .'">'. f::escape_html($language_code) .'</span>',
			'	 ' . f::draw_element('input', ['class' => 'form-input', 'name' => $name, 'type' => $type, 'value' => $input, ...$attributes]),
			'</div>'
		]);
	}

	function form_regional_text($name, $language_code='', $input=true, $attributes=[]) {

		if (!$language_code) {
			$language_code = settings::get('store_language_code');
		}

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-text" style="font-family: monospace;" title="'. f::escape_attr(language::$languages[$language_code]['name']) .'">'. f::escape_html($language_code) .'</span>',
			'  ' . form_input_text($name, $input, $attributes),
			'</div>',
		]);
	}

	function form_regional_textarea($name, $language_code='', $input=true, $attributes=[]) {

		if (preg_match('#^[a-z]{2}$#', $name)) {
			trigger_error('Passing language code as 1st parameter in form_regional_textarea() is deprecated. Instead, use form_regional_textarea($name, $language_code, $input, $attributes)', E_USER_DEPRECATED);
			[$name, $language_code] = [$language_code, $name];
		}

		if (!$language_code) {
			$language_code = settings::get('store_language_code');
		}

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-text" style="font-family: monospace;" title="'. f::escape_attr(language::$languages[$language_code]['name']) .'">'. f::escape_html($language_code) .'</span>',
			'  ' . form_textarea($name, $input, $attributes),
			'</div>',
		]);
	}

	function form_regional_wysiwyg($name, $language_code='', $input=true, $attributes=[]) {

		if (preg_match('#^[a-z]{2}$#', $name)) {
			trigger_error('Passing language code as 1st parameter in form_regional_wysiwyg() is deprecated. Instead, use form_regional_wysiwyg($name, $language_code, $input, $attributes)', E_USER_DEPRECATED);
			[$name, $language_code] = [$language_code, $name];
		}

		if (!$language_code) {
			$language_code = settings::get('store_language_code');
		}

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-text" style="font-family: monospace;" title="'. f::escape_attr(language::$languages[$language_code]['name']) .'">'. f::escape_html($language_code) .'</span>',
			'  ' . form_input_wysiwyg($name, $input, $attributes),
			'</div>',
		]);
	}

	function form_select($name, $options=[], $input=true, $attributes=[]) {

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		$content = [];

		$is_numerical_index = array_is_list($options);

		foreach ($options as $key => $option) {

			if (!is_array($option)) {
				if ($is_numerical_index) {
					$option = [$option];
				} else {
					$option = [$key, $option];
				}
			}

			if (!strcmp($option[0], $input)) {
				$content[] = f::draw_element('option', ['value' => $option[0], 'selected' => ''], $option[1] ?? $option[0]);
			}	else {
				$content[] = f::draw_element('option', ['value' => $option[0]], $option[1] ?? $option[0]);
			}

		}

		return f::draw_element('select', ['class' => 'form-select', 'name' => $name, ...$attributes], implode(PHP_EOL, $content));
	}

	function form_select_multiple($name, $options=[], $input=true, $attributes=[]) {

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		$content = [];

		$is_numerical_index = array_is_list($options);

		foreach ($options as $key => $option) {

			if (!is_array($option)) {
				if ($is_numerical_index) {
					$option = [$option, $option];
				} else {
					$option = [$key, $option];
				}
			}

			$content[] = form_checkbox($name, $option, $input, $option[2] ?? '');
		}

		return f::draw_element('div', ['class' => 'form-input', ...$attributes], implode(PHP_EOL, $content));
	}

	function form_select_optgroup($name, $groups=[], $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 3 && is_bool($args[3])) {
			trigger_error('Passing $multiple as 4th parameter in form_select_optgroup() is deprecated as determined by input name instead.', E_USER_DEPRECATED);
			if (isset($args[4])) $attributes = $args[3];
		}

		if (!is_array($groups)) {
			$groups = [$groups];
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		$content = [];
		foreach ($groups as $group) {
			$content[] = '  <optgroup label="'. f::escape_attr($group['label']) .'">' . PHP_EOL;

			$is_numerical_index = array_is_list($group['options']);

			foreach ($group['options'] as $key => $option) {

				if (!is_array($option)) {
					if ($is_numerical_index) {
						$option = [$option, $option];
					} else {
						$option = [$key, $option];
					}
				}

				if (preg_match('#\[\]$#', $name)) {
					$content[] = '  <option value="'. f::escape_attr($option[0]) .'"'. (in_array($option[0], $input) ? ' selected' : '') . (!empty($option[2]) ? ' ' . $option[2] : '') . '>'. ($option[1] ?? $option[0]) .'</option>' . PHP_EOL;
				} else {
					$content[] = '  <option value="'. f::escape_attr($option[0]) .'"'. (!strcmp($option[0], $input) ? ' selected' : '') . (!empty($option[2]) ? ' ' . $option[2] : '') . '>'. ($option[1] ?? $option[0]) .'</option>' . PHP_EOL;
				}
			}

			$content[] = '  </optgroup>' . PHP_EOL;
		}

		return f::draw_element('select', ['class' => 'form-select', 'name' => $name, ...$attributes], implode(PHP_EOL, $content));
	}

	function form_switch($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('div', ['class' => 'form-switch', ...$attributes], f::draw_element('label', [], f::draw_element('input', ['type' => 'checkbox', 'name' => $name, 'value' => 1, 'hidden' => '', ...($input ? ['checked' => ''] : [])])));
	}

	function form_textarea($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('textarea', ['class' => 'form-input', 'name' => $name, ...$attributes], f::escape_html($input));
	}

	function form_toggle($name, $options='t/f', $input=true, $attributes=[]) {

		if (str_contains($input, '/')) {
			trigger_error('Passing type as 3rd parameter in form_toggle() is deprecated. Use instead form_toggle($name, $type, $input, $attributes)', E_USER_DEPRECATED);
			[$options, $input] = [$input, $options];
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if ($options === null) {
			$options = 't/f';
		}

		if (is_string($options)) {
			$options = match($options) {

				'a/i' => [
					'1' => t('title_active', 'Active'),
					'0' => t('title_inactive', 'Inactive'),
				],

				'e/d' => [
					'1' => t('title_enabled', 'Enabled'),
					'0' => t('title_disabled', 'Disabled'),
				],

				'y/n' => [
					'1' => t('title_yes', 'Yes'),
					'0' => t('title_no', 'No'),
				],

				'o/o' => [
						'1' => t('title_on', 'On'),
						'0' => t('title_off', 'Off'),
					],

				't/f' => [
						'1' => t('title_true', 'True'),
						'0' => t('title_false', 'False'),
					],
			};

			if (!$options) {
				trigger_error('Invalid toggle type ('. $options .')', E_USER_WARNING);
				$options = [
					'1' => t('title_true', 'True'),
					'0' => t('title_false', 'False'),
				];
			}
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		$content = '';

		$is_numerical_index = (is_array($options) && array_is_list($options)) ? true : false;

		foreach ($options as $key => $option) {

			if (!is_array($option)) {
				$option = [$key, $option];
			}

			if (preg_match('#\[\]$#', $name)) {
				$content .= implode(PHP_EOL, [
					'  <label>',
					'    <input type="checkbox" name="'. f::escape_attr($name) .'" value="'. f::escape_attr($option[0]) .'" hidden'. ((is_array($input) && in_array($option[0], $input)) ? ' checked' : '') . (!empty($option[2]) ? ' '. $option[2] : '') .'>'. $option[1],
					'  </label>',
				]) . PHP_EOL;
			} else {
				$content .= implode(PHP_EOL, [
					'  <label>',
					'    <input type="radio" name="'. f::escape_attr($name) .'" value="'. f::escape_attr($option[0]) .'" hidden'. (($option[0] == $input) ? ' checked' : '') . (!empty($option[2]) ? ' '. $option[2] : '') .'>'. $option[1],
					'  </label>',
				]) . PHP_EOL;
			}
		}

		return f::draw_element('div', ['class' => 'form-toggle', ...$attributes], $content);
	}

	##################################
	# Platform specific form helpers #
	##################################

	function form_function($name, $function, $input=true, $attributes=[]) {

		if (preg_match('#\)$#', $name)) {
			trigger_error('Passing function as 1st parameter in form_function() is deprecated. Instead, use form_function($name, $function, $input, $attributes)', E_USER_DEPRECATED);
			[$name, $function] = [$function, $name];
		}

		if (!preg_match('#(\w*)\((.*?)\)$#i', $function, $matches)) {
			trigger_error('Invalid form function ('. $function .')', E_USER_WARNING);
			return form_textarea($name, $input, $attributes);
		}

		$options = [];
		if (!empty($matches[2])) {
			$options = preg_split('#\s*,\s*#', $matches[2], -1, PREG_SPLIT_NO_EMPTY);
			$options = f::array_each($options, fn($s) => trim($s, '\'" '));
		}

		switch ($matches[1]) {

			case 'administrator':
				return form_select_administrator($name, $input, $attributes);

			case 'bigtext':
				return form_textarea($name, $input, $attributes . ' rows="10"');

			case 'campaign':
				return form_select_campaign($name, $input, $attributes);

			case 'category':
				if (preg_match('#\[\]$#', $name)) {
					return form_select_multiple_categories($name, $input, $attributes);
				}
				return form_select_category($name, $input, $attributes);

			case 'checkbox':
				return implode(PHP_EOL, array_map(function($option) use ($name, $input, $attributes) {
					return form_checkbox($name, [$option, $option], $input, $attributes);
				}, $options));

			case 'code':
				return form_input_code($name, $input, $attributes);

			case 'color':
				return form_input_color($name, $input, $attributes);

			case 'currency':
				return form_select_currency($name, $input, $attributes);

			case 'customer':
				return form_select_customer($name, $input, $attributes);

			case 'customer_group':
				return form_select_customer_group($name, $input);

			case 'country':
				return form_select_country($name, $input, $attributes);

			case 'csv':
				return form_textarea($name, $input, true, $attributes);

			case 'date':
				return form_input_date($name, $input, $attributes);

			case 'datetime':
				return form_input_datetime($name, $input, $attributes);

			case 'decimal':
				return form_input_decimal($name, $input, 2, $attributes);

			case 'delivery_status':
				return form_select_delivery_status($name, $input, $attributes);

			case 'email':
				return form_input_email($name, $input, $attributes);

			case 'file':
				return form_select_file($name, $options[0], $input, $attributes);

			case 'geo_zone':
				return form_select_geo_zone($name, $input, $attributes);

			case 'incoterm':
				return form_select_incoterm($name, $input, $attributes);

			case 'language':
				return form_select_language($name, $input, $attributes);

			case 'length_class': // Deprecated
			case 'length_unit':
				return form_select_length_unit($name, $input, $attributes);

			case 'number':
				return form_input_number($name, $input, $attributes);

			case 'order_status':
				return form_select_order_status($name, $input, $attributes);

			case 'page':
				return form_select_page($name, $input, $attributes);

			case 'password':
				return form_input_password($name, $input, $attributes);

			case 'password_unmaskable':
				return form_input_password_unmaskable($name, $input, $attributes);

			case 'payment_term':
				return form_select_payment_term($name, $input, $attributes);

			case 'percent':
				return form_input_percent($name, $input);

			case 'phone':
				return form_input_phone($name, $input);

			case 'product':
				return form_select_product($name, $input, $attributes);

			case 'quantity_unit':
				return form_select_quantity_unit($name, $input, $attributes);

			case 'stock_option':
				return form_select_product_stock_option($name, $input, $attributes);

			case 'radio':
				return implode(PHP_EOL, array_map(function($option) use ($name, $input, $attributes) {
					return form_radio_button($name, [$option, $option], $input, $attributes);
				}, $options));

			case 'regional_text':
				$html = '';
				foreach (array_keys(language::$languages) as $language_code) {
					$html .= form_regional_text($name.'['. $language_code.']', $language_code, $input, $attributes);
				}
				return $html;

			case 'regional_textarea':
				$html = '';
				foreach (array_keys(language::$languages) as $language_code) {
					$html .= form_regional_textarea($name.'['. $language_code.']', $language_code, $input, $attributes);
				}
				return $html;

			case 'regional_wysiwyg':
				$html = '';
				foreach (array_keys(language::$languages) as $language_code) {
					$html .= form_regional_wysiwyg($name.'['. $language_code.']', $language_code, $input, $attributes);
				}
				return $html;

			case 'select':
				for ($i=0; $i<count($options); $i++) $options[$i] = [$options[$i]];
				return form_select($name, $options, $input, $attributes);

			case 'sold_out_status':
				return form_select_sold_out_status($name, $input, $attributes);

			case 'tags':
				return form_tags($name, $input, $attributes);

			case 'text':
				return form_input_text($name, $input, $attributes);

			case 'textarea':
				return form_textarea($name, $input, $attributes);

			case 'template':
				return form_select_template($name, $input, $attributes);

			case 'time':
				return form_input_time($name, $input, $attributes);

			case 'timezone':
				return form_select_timezone($name, $input, $attributes);

			case 'toggle':
				return form_toggle($name, $options[0] ?? null, $input);

			case 'tax_class':
				return form_select_tax_class($name, $input, $attributes);

			case 'upload':
				return form_input_file($name, $attributes);

			case 'url':
				return form_input_url($name, $input, $attributes);

			case 'weight_class': // Deprecated
			case 'weight_unit':
				return form_select_weight_unit($name, $input, $attributes);

			case 'volume_unit':
				return form_select_volume_unit($name, $input, $attributes);

			case 'wysiwyg':
				return form_input_wysiwyg($input, $name, $attributes);

			case 'zone':
				$option = $options ? $options[0] : '';
				return form_select_zone($name, $option, $input, $attributes);

			default:
				trigger_error('Unknown function name "'. $function .'"', E_USER_WARNING);
				return form_input_text($name, $input, $attributes);
				break;
		}
	}

	function form_select_administrator($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_administrator() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = database::query(
			"select id, username from ". DB_TABLE_PREFIX ."administrators
			order by username;"
		)->fetch_all(function($administrator){
			return [$administrator['id'], $administrator['username']];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_attribute_group($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_attribute_group() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = database::query(
			"select ag.id,
				json_value(ag.name, '$.". database::input(language::$selected['code']) ."') as name
			from ". DB_TABLE_PREFIX ."attribute_groups ag
			order by name;"
		)->fetch_all(function($group){
			return [$group['id'], $group['name']];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_attribute_value($name, $group_id, $input=true, $attributes=[]) {

		if (is_numeric($name)) {
			trigger_error('form_select_attribute_value_list() no longer takes group ID as 1st parameter. Instead, use form_select_attribute_value($name, $group_id, $input, $attributes)', E_USER_DEPRECATED);
			[$name, $group_id] = [$group_id, $name];
		}

		if (count($args = func_get_args()) > 3 && is_bool($args[3])) {
			trigger_error('Passing $multiple as 4th parameter in form_select_attribute_value() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[4])) $attributes = $args[3];
		}

		$options = database::query(
			"select av.id, json_value(av.value, '$.". database::input(language::$selected['code']) ."') as name
			from ". DB_TABLE_PREFIX ."attribute_values av
			where group_id = ". (int)$group_id ."
			order by name;"
		)->fetch_all(function($value){
			return [$value['id'], $value['name']];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_brand($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_brand() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = database::query(
			"select id, name from ". DB_TABLE_PREFIX ."brands
			order by name asc;"
		)->fetch_all(function($brand){
			return [$brand['id'], $brand['name']];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_campaign($name, $input=true, $attributes=[]) {

		$options = database::query(
			"select id, name, valid_from, valid_to
			from ". DB_TABLE_PREFIX ."campaigns
			order by status desc, name asc;"
		)->fetch_all(function($campaign){
			return [$campaign['id'], $campaign['name'], 'data-valid-from="'. ($campaign['valid_from'] ? f::datetime_when($campaign['valid_from']) : '') .'" data-valid-to="'. ($campaign['valid_to'] ? f::datetime_when($campaign['valid_to']) : '') .'"'];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_category($name, $input=true, $attributes=[]) {

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple_categories($name, $input, $attributes);
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if ($input) {
			$category_name = reference::category($input)->name;
		} else {
			$category_name = t('title_root', 'Root');
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('div', ['class' => 'input-group', ...$attributes], implode(PHP_EOL, [
			'  <div class="form-input">',
			'    ' . form_input_hidden($name, $input),
			'    '. f::draw_fonticon('folder') .' <span class="name" style="display: inline-block;">'. ($input ? reference::category($input)->name : t('title_root', 'Root')) .'</span>',
			'  </div>',
			'  <div style="align-self: center;">',
			'    <a href="'. document::href_ilink('b:catalog/category_picker', ['parent_id' => $input]) .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin: .5em;">',
			'      '. t('title_change', 'Change'),
			'    </a>',
			'  </div>',
		]));
	}

	function form_select_multiple_categories($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_multiple_categories() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		if (!preg_match('#\[\]$#', $name)) {
			return form_select_category($name, $input, $attributes);
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if (preg_match('#\[\]$#', $name) && !is_array($input)) {
			$input = [$input];
		}

		$content = implode(PHP_EOL, [
			'  <div class="form-input" style="overflow-y: auto; min-height: 100px; max-height: 480px; margin-bottom: .25em;">',
			'    <ul class="list-unstyled">',
		]);

		if (empty($parent_id)) {
			$options[] = ['0' => f::draw_fonticon('icon-folder', 'style="color: #cccc66;"') .' ['. t('title_root', 'Root') .']',];
		}

		database::query(
			"select c.id, coalesce(
				". implode(', ', f::array_each(language::$languages, fn($language) =>
					"json_value(c.name, '$.". database::input($language['code']) ."')"
				)) .",
				'(". database::input(t('title_untitled', 'Untitled')) .")'
			) as name
			from ". DB_TABLE_PREFIX ."categories c
			where c.id in ('". implode("', '", database::input($input)) ."');"
		)->each(function($category) use (&$content, $name) {

			$path = [];
			if (!empty(reference::category($category['id'])->path)) {
				foreach (reference::category($category['id'])->path as $ancestor) {
					$path[] = $ancestor->name;
				}
			}

			$content .= implode(PHP_EOL, [
				'      <li class="list-item flex">',
				'        <div style="flex-grow: 1;">',
				'          '. form_input_hidden($name, $category['id'], ['data-id' => (int)$category['id'], 'data-name' => f::escape_attr($category['name'])]),
				'          '. f::draw_fonticon('folder') .' '. implode(' &gt; ', $path),
				'        </div>',
				'        <button name="remove" class="btn btn-default btn-sm float-end" type="button">',
				'          '. t('title_remove', 'Remove'),
				'        </button>',
				'      </li>',
			]);
		});

		$content .= implode(PHP_EOL, [
			'    </ul>',
			'  </div>',
			'',
			'  <div class="dropdown">',
			'  '. form_input_search('', '', ['autocomplete' => 'off', 'placeholder' => t('text_search_categories', 'Search categories') . '&hellip;']),
			'    <div class="dropdown-content" style="padding: 1em; inset-inline-end: 0; max-height: 480px; overflow-y: auto;">',
			'    </div>',
			'  </div>',
		]);

		document::$javascript['category-picker'] = implode(PHP_EOL, [
			'$(\'[data-toggle="category-picker"]\').categoryPicker({',
			'  inputName: "'. f::escape_js($name) .'",',
			'  link: "'. document::ilink('b:catalog/categories.json') .'",',
			'  icons: {',
			'    folder: \''. f::draw_fonticon('folder') .'\',',
			'    back: \''. f::draw_fonticon('icon-arrow-left') .'\'',
			'  },',
			'  translations: {',
			'    search_results: "'. t('title_search_results', 'Search Results') .'",',
			'    root: "'. t('title_root', 'Root') .'",',
			'    add: "'. t('title_add', 'Add') .'",',
			'    remove: "'. t('title_remove', 'Remove') .'",',
			'    root: "'. t('title_root', 'Root') .'"',
			'  }',
			'});',
		]);

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('div', ['data-toggle' => 'category-picker', ...$attributes], $content);
	}

	function form_select_country($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_country() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		if ($input === true) {
			$input = form_reinsert_value($name);

			if ($input == '' && file_get_contents('php://input') == '') {
				$input = settings::get('default_country_code');
			}
		}

		$input = match($input) {
			'customer_country_code' => customer::$data['country_code'],
			'default_country_code' => settings::get('default_country_code'),
			'store_country_code' => settings::get('store_country_code'),
			default => $input
		};

		$options = database::query(
			"select * from ". DB_TABLE_PREFIX ."countries
			where status
			order by name asc;"
		)->fetch_all(function($country){
			return [$country['iso_code_2'], $country['name'], 'data-tax-id-format="'. $country['tax_id_format'] .'" data-postcode-format="'. $country['postcode_format'] .'" data-phone-code="'. $country['phone_code'] .'"'];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_currency($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_currency() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = f::array_each(currency::$currencies, fn($currency) =>
			[$currency['code'], $currency['name'], 'data-value="'. (float)$currency['value'] .'" data-decimals="'. (int)$currency['decimals'] .'" data-prefix="'. f::escape_attr($currency['prefix']) .'" data-suffix="'. f::escape_attr($currency['suffix']) .'"']
		);

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_customer($name, $input = true, $attributes = '') {

		if (empty(administrator::$data['id'])) {
			throw new Error('Must be logged in to use form_select_customer()');
		}

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_customer() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple_customers($name, $input, $attributes);
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$account_name = t('title_guest', 'Guest');

		if ($input) {
			$customer = database::query(
				"select * from ". DB_TABLE_PREFIX ."customers
				where id = ". (int)$input ."
				limit 1;"
			)->fetch();

			if ($customer) {
				$account_name = $customer['company'] ?: $customer['firstname'] .' '. $customer['lastname'];
			} else {
				$account_name = '<em>'. t('title_unknown', 'Unknown') .'</em>';
			}
		}

		document::add_style(implode(PHP_EOL, [
			'	.customer-dropdown {',
			'		position: relative;',
			'	}',
			'	.customer-dropdown .search-input {',
			'		width: 100%;',
			'		margin-bottom: 5px;',
			'	}',
			'	.customer-dropdown .dropdown-results {',
			'		position: absolute;',
			'		top: 100%;',
			'		left: 0;',
			'		right: 0;',
			'		max-height: 200px;',
			'		overflow-y: auto;',
			'		background: #fff;',
			'		border: 1px solid #ccc;',
			'		z-index: 1000;',
			'	}',
			'	.customer-dropdown .dropdown-results li {',
			'		padding: 8px;',
			'		cursor: pointer;',
			'	}',
			'	.customer-dropdown .dropdown-results li:hover,',
			'	.customer-dropdown .dropdown-results li.active {',
			'		background: #f5f5f5;',
			'	}',
			'	.customer-dropdown .selected-customer {',
			'		display: inline-block;',
			'		margin-top: 5px;',
			'	}',
		]), 'select-customer');

		document::add_script([
			'	var xhr_customer_search = null;',
			'	var $dropdown = $(".customer-dropdown");',
			'	var $searchInput = $dropdown.find(".search-input");',
			'	var $results = $dropdown.find(".dropdown-results");',
			'	var $list = $results.find("ul");',
			'',
			'	// Preselect the current customer if exists',
			'	if ($searchInput.val() && ' . (int)$input . ') {',
			'		$list.prepend(\'<li class="active" data-id="' . (int)$input . '">' . f::escape_js($name) . ' (ID: ' . (int)$input . ')</li>\');',
			'	}',
			'',
			'	$searchInput.on("input", function() {',
			'		var query = $(this).val();',
			'		$results.show();',
			'',
			'		if (query === "") {',
			'			$list.html(\'<li class="set-guest' . (!empty($input) ? '' : ' active') . '" data-id="0">(' . f::escape_js(t('title_guest', 'Guest')) . ')</li>\');',
			'			return;',
			'		}',
			'',
			'		if (xhr_customer_search) xhr_customer_search.abort();',
			'',
			'		xhr_customer_search = $.ajax({',
			'			type: "get",',
			'			async: true,',
			'			cache: false,',
			'			url: "' . document::ilink('customers/customers.json') . '?query=" + encodeURIComponent(query),',
			'			dataType: "json",',
			'			beforeSend: function(jqXHR) {',
			'				jqXHR.overrideMimeType("text/html;charset=" + $("html meta[charset]").attr("charset"));',
			'			},',
			'			error: function(jqXHR, textStatus, errorThrown) {',
			'				if (textStatus !== "abort") console.error(textStatus + ": " + errorThrown);',
			'			},',
			'			success: function(json) {',
			'				$list.html(\'<li class="set-guest" data-id="0">(' . f::escape_js(t('title_guest', 'Guest')) . ')</li>\');',
			'				$.each(json, function(i, row) {',
			'					if (row) {',
			'						var isActive = (row.id == '. (int)$input .');',
			'						$list.append(',
			'							\'<li class="\' + (isActive ? "active" : "") + \'" data-id="\' + row.id + \'">\' +',
			'							row.id + \' &ndash; \' + row.name + \' (\' + row.email + \')</li>\'',
			'						);',
			'					}',
			'				});',
			'				if ($list.find("li").length === 1) {',
			'					$list.append(\'<li><em>' . f::escape_js(t('text_no_results', 'No results')) . '</em></li>\');',
			'				}',
			'			}',
			'		});',
			'	});',
			'',
			'	$dropdown.on("click", ".dropdown-results li", function() {',
			'		var id = $(this).data("id");',
			'		var name = $(this).text();',
			'		$dropdown.find(":input[name=\'' . f::escape_js($name) . '\']").val(id).trigger("change");',
			'		$searchInput.val(name);',
			'		$list.find("li").removeClass("active");',
			'		$(this).addClass("active");',
			'		$results.hide();',
			'	});',
			'',
			'	$(document).on("click", function(e) {',
			'		if (!$(e.target).closest(".customer-dropdown").length) {',
			'			$results.hide();',
			'		}',
			'	});',
			'',
			'	$searchInput.on("focus", function() {',
			'		$results.show();',
			'	});',
		]);

		return f::draw_element('div', ['class' => 'customer-dropdown', ...form_attributes($attributes)], implode(PHP_EOL, [
			'  <input type="hidden" name="' . f::escape_html($name) . '" value="' . (int)$input . '" />',
			'  <input type="text" class="form-input search-input" placeholder="' . f::escape_html(t('title_search', 'Search')) . '" autocomplete="off" value="' . f::escape_html($input ? $name : '') . '">',
			'  <div class="dropdown-results" style="display: none;">',
			'    <ul class="list-unstyled">',
			'      <li class="set-guest' . ($input ? '' : ' active') . '" data-id="0">(' . f::escape_html(t('title_guest', 'Guest')) . ')</li>',
			'    </ul>',
			'  </div>',
		]));
	}

	function form_select_customer_group($name, $input=true, $attributes=[]) {

		if (!administrator::check_login()) {
			throw new Error('Must be logged in to use form_select_customer_group()');
		}

		$options = database::query(
			"select id, name
			from ". DB_TABLE_PREFIX ."customer_groups
			order by name asc;"
		)->fetch_all(function($group){
			return [$group['id'], $group['name']];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_multiple_customers($name, $input=true, $attributes=[]) {

		if (!administrator::check_login()) {
			throw new Error('Must be logged in to use form_select_multiple_customers()');
		}

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_multiple_customers() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		if (!preg_match('#\[\]$#', $name)) {
			return form_select_customer($name, $input, $attributes);
		}

		$options = database::query(
			"select id, email, company, firstname, lastname
			from ". DB_TABLE_PREFIX ."customers
			order by email;"
		)->fetch_all(function($customer) {
			return [$customer['id'], $customer['email'], 'data-name="'. f::escape_attr($customer['company'] ?: $customer['firstname'] .' '. $customer['lastname']) .'"'];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_delivery_status($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_delivery_status() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		if ($input === true) {
			$input = form_reinsert_value($name);

			if ($input == '' && file_get_contents('php://input') == '') {
				$input = settings::get('default_delivery_status_id');
			}
		}

		$options = database::query(
			"select id,
				json_value(name, '$.name') as name,
				json_value(description, '$.description') as description
			from ". DB_TABLE_PREFIX ."delivery_statuses
			order by name asc;"
		)->fetch_all(function($row) {
			return [$row['id'], $row['name'], 'title="'. f::escape_attr($row['description']) .'"'];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_encoding($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_encoding() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = [
			'BIG-5',
			'CP50220',
			'CP50221',
			'CP50222',
			'CP51932',
			'CP850',
			'CP932',
			'EUC-CN',
			'EUC-JP',
			'EUC-KR',
			'EUC-TW',
			'GB18030',
			'ISO-8859-1',
			'ISO-8859-2',
			'ISO-8859-3',
			'ISO-8859-4',
			'ISO-8859-5',
			'ISO-8859-6',
			'ISO-8859-7',
			'ISO-8859-8',
			'ISO-8859-9',
			'ISO-8859-10',
			'ISO-8859-13',
			'ISO-8859-14',
			'ISO-8859-15',
			'ISO-8859-16',
			'KOI8-R',
			'KOI8-U',
			'SJIS',
			'UTF-8',
			'UTF-16',
			'Windows-1251',
			'Windows-1252',
			'Windows-1254',
		];

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_function($name, $input=true, $attributes=[]) {

		$options = [
			'administrator()',
			'bigtext()',
			'checkbox()',
			'color()',
			'country()',
			'csv()',
			'currency()',
			'customer()',
			'date()',
			'datetime()',
			'decimal()',
			'email()',
			'file()',
			'geo_zone()',
			'language()',
			'mediumtext()',
			'number()',
			'password()',
			'percent()',
			'phone()',
			'radio()',
			'regional_text()',
			'regional_textarea()',
			'regional_wysiwyg()',
			'select()',
			'tags()',
			'text()',
			'textarea()',
			'time()',
			'timezone()',
			'toggle()',
			'upload()',
			'tax_class()',
			'url()',
			'wysiwyg()',
			'zone()',
		];

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_file($name, $pattern, $input=true, $attributes=[]) {

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple_files($name, $pattern, $input, $attributes);
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		return f::draw_element('div', ['class' => 'form-input', ...$attributes], implode(PHP_EOL, [
			'  ' . form_input_hidden($name, true),
			'  <span class="value">'. ($input ? f::escape_html($input) : '('. t('title_none', 'None') .')') .'</span> <a href="'. document::href_ilink('b:files/file_picker') .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin-inline-start: 5px;">'. t('title_change', 'Change') .'</a>',
		]));
	}

	function form_select_multiple_files($name, $pattern, $input=true, $attributes=[]) {

		if (!preg_match('#\[\]$#', $name)) {
			return form_select_file($name, $pattern, $input, $attributes);
		}

		$options = array_map(function($file) {

			$file = preg_replace('#^'. preg_quote('app://', '#') .'#', '', $file);

			if (is_dir('app://' . $file)) {
				return [basename($file).'/', $file.'/'];
			} else {
				return [basename($file), $file];
			}

		}, f::file_search($pattern, GLOB_BRACE));

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		}

		array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
		return form_select($name, $options, $input, $attributes);
	}

	function form_select_geo_zone($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_geo_zone() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = database::query(
			"select * from ". DB_TABLE_PREFIX ."geo_zones
			order by name asc;"
		)->fetch_all(function($geo_zone) {
			return [$geo_zone['id'], $geo_zone['name']];
		});

		if (!$options) {
			return form_select($name, $options, $input, $attributes . ' disabled');
		}

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_incoterm($name, $input=true, $attributes=[]) {

		$options = [
			['EXW', 'EXW &ndash; '. t('title_incoterm_exw', 'Ex Works')],
			['FCA', 'FCA &ndash; '. t('title_incoterm_fca', 'Free Carrier')],
			['FAS', 'FAS &ndash; '. t('title_incoterm_fas', 'Free Alongside Ship')],
			['FOB', 'FOB &ndash; '. t('title_incoterm_fob', 'Free On Board')],
			['CFR', 'CFR &ndash; '. t('title_incoterm_cfr', 'Cost and Freight')],
			['CIF', 'CIF &ndash; '. t('title_incoterm_cif', 'Cost, Insurance and Freight')],
			['CPT', 'CPT &ndash; '. t('title_incoterm_cpt', 'Carriage Paid To')],
			['CIP', 'CIP &ndash; '. t('title_incoterm_cip', 'Carriage and Insurance Paid')],
			['DDP', 'DDP &ndash; '. t('title_incoterm_ddp', 'Delivered Duty Paid')],
			['DPU', 'DPU &ndash; '. t('title_incoterm_dpu', 'Delivered At Place Unloaded')],
			['DAP', 'DAP &ndash; '. t('title_incoterm_dap', 'Delivered At Place')],
		];

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_intl_locale($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if (!class_exists('ResourceBundle')) {
			trigger_error('The PHP extension "intl" is required to use form_select_locale()', E_USER_WARNING);
			return form_input_text($name, $input, $attributes . ($attributes ? ' ' : '') .'placeholder="en_US.utf8, en-US.UTF-8, english"');
		}

		$options = f::array_each(ResourceBundle::getLocales(''), fn($locale) => [$locale]);

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_language($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_language() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = f::array_each(language::$languages, fn($language) =>
			[$language['code'], $language['name'], 'data-decimal-point="'. $language['decimal_point'] .'" data-thousands-sep="'. $language['thousands_sep'] .'"']
		);

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_length_unit($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_length_unit() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		if ($input === true) {
			$input = form_reinsert_value($name);

			if ($input == '' && file_get_contents('php://input') == '') {
				$input = settings::get('store_length_unit');
			}
		}

		$options = f::array_each(length::$units, fn($unit) =>
			[$unit['unit'], $unit['unit'], 'data-value="'. (float)$unit['value'] .'" data-decimals="'. (int)$unit['decimals'] .'" title="'. f::escape_attr($unit['name']) .'"']
		);

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '--']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_mysql_collation($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_mysql_collation() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = database::query(
			"select COLLATION_NAME from information_schema.COLLATIONS
			where CHARACTER_SET_NAME = 'utf8mb4'
			order by COLLATION_NAME;"
		)->fetch_all('COLLATION_NAME');

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_mysql_engine($name, $input=true, $attributes=[]) {

		$options = database::query(
			"SHOW ENGINES;"
		)->fetch_all(function($engine){
			if (!in_array(strtoupper($engine['Support']), ['YES', 'DEFAULT'])) return false;
			if (!in_array($engine['Engine'], ['InnoDB', 'MyISAM', 'Aria'])) return false;
			return [$engine['Engine'], $engine['Engine'] . ' -- '. $engine['Comment']];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_order_status($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_order_status() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = database::query(
			"select os.id, os.icon, os.color, json_value(os.name, '$.". database::input(language::$selected['code']) ."') as name
			from ". DB_TABLE_PREFIX ."order_statuses os
			order by field(os.state, 'created', 'on_hold', 'ready', 'delayed', 'processing', 'completed', 'dispatched', 'in_transit', 'delivered', 'returning', 'returned', 'cancelled', ''), os.priority, name asc;"
		)->fetch_all(function($row) {
			return [$row['id'], f::draw_fonticon($row['icon'], 'style="color: '. $row['color'] .';"') .' '. $row['name'], 'data-icon="'. f::escape_attr($row['icon']) .'" data-color="'. f::escape_attr($row['color']) .'"'];
		});

		if (!preg_match('#\[\]$#', $name)) {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
		}

		return form_dropdown($name, $options, $input, $attributes);
	}

	function form_select_page($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_page() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$iterator = function($parent_id, $level) use (&$iterator) {

			$options = [];

			if (empty($parent_id)) {
				$options[] = ['0', '['.t('title_root', 'Root').']'];
			}

			database::query(
				"select p.id, json_value(p.title, '$.". database::input(language::$selected['code']) ."') as title
				from ". DB_TABLE_PREFIX ."pages p
				where ". (empty($parent_id) ? "p.parent_id is null" : "p.parent_id = ". (int)$parent_id) ."
				order by p.priority asc, title asc;"
			)->each(function($page) use(&$options, $iterator, $level) {

				$options[] = [$page['id'], str_repeat('&nbsp;&nbsp;&nbsp;', $level) . $page['title']];

				if (database::query(
					"select id from ". DB_TABLE_PREFIX ."pages
					where parent_id = ". (int)$page['id'] ."
					limit 1;"
				)->num_rows) {
					$sub_options = $iterator($page['id'], $level+1);
					$options = array_merge($options, $sub_options);
				}
			});

			return $options;
		};

		$options = $iterator(0, 1);

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_parent_page($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_page() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$iterator = function($parent_id, $dock, $level=1) use (&$iterator) {

			$options = [];

			database::query(
				"select p.id, json_value(p.title, '$.". database::input(language::$selected['code']) ."') as title
				from ". DB_TABLE_PREFIX ."pages p
				where ". ($parent_id ? "p.parent_id = ". (int)$parent_id : "dock = '". database::input($dock) ."' and parent_id is null") ."
				order by p.priority asc, title asc;"
			)->each(function($page) use(&$options, $iterator, $dock, $level) {
				$options[] = [$dock.':'.$page['id'], str_repeat('&nbsp;&nbsp;&nbsp;', $level) . $page['title']];
				$sub_options = $iterator($dock.':'.$page['id'], '', $level+1);
				$options = array_merge($options, $sub_options);
			});

			return $options;
		};

		$options = array_merge(
			[['menu:', t('title_site_menu', 'Site Menu')]],
			$iterator(null, 'menu'),
			[['information:', t('title_information', 'Information')]],
			$iterator(null, 'information'),
		);

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_payment_module($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_payment_module() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = database::query(
			"select * from ". DB_TABLE_PREFIX ."modules
			where type = 'payment'
			and status;"
		)->fetch_all(function($module) {
			$module = new $module['module_id'];
			return [$module->id, $module->name];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_payment_term($name, $input=true, $attributes=[]) {

		$options = [
			['PIA', 'PIA &ndash; '. t('title_payment_terms_pia', 'Payment In Advance')],
			['PWO', 'PWO &ndash; '. t('title_payment_terms_pwo', 'Payment With Order')],
			['CBS', 'CBS &ndash; '. t('title_payment_terms_cbs', 'Cash Before Shipment')],
			['COD', 'COD &ndash; '. t('title_payment_terms_cod', 'Cash On Delivery')],
			['NET7', 'NET7 &ndash; '. t('title_payment_terms_net7', 'Payment 7 days after invoice date')],
			['NET10', 'NET10 &ndash; '. t('title_payment_terms_net10', 'Payment 10 days after invoice date')],
			['NET20', 'NET20 &ndash; '. t('title_payment_terms_net20', 'Payment 20 days after invoice date')],
			['NET30', 'NET30 &ndash; '. t('title_payment_terms_net30', 'Payment 30 days after invoice date')],
		];

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_product($name, $input=true, $attributes=[]) {

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple_products($name, $input, $attributes);
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$product_name = '('. t('title_no_product', 'No Product') .')';

		if ($input) {

			$sql_column_price = "coalesce(". implode(", ", array_map(function($currency) {
				return "if(json_value(price, '$.". database::input($currency['code']) ."') != 0, json_value(price, '$.". database::input($currency['code']) ."') * ". $currency['value'] .", null)";
			})) .")";

			$product = database::query(
				"select p.id, p.code, pp.regular_price, pp.final_price, json_value(p.name, '$.". database::input(language::$selected['code']) ."') as name
				from ". DB_TABLE_PREFIX ."products p
				left join (
					select product_id, max($sql_column_price) as regular_price, min($sql_column_price) as final_price
					from ". DB_TABLE_PREFIX ."products_prices
					where (campaign_id is null or campaign_id in (
						select id from ". DB_TABLE_PREFIX ."campaigns
						where status
						and (valid_from is not null and valid_from > '". date('Y-m-d H:i:s') ."')
						and (valid_to is not null and valid_to < '". date('Y-m-d H:i:s') ."')
					) or campaign_id is null)
					and (customer_group_id is null)
				) pp on (pp.product_id = p.id)
				where p.id = ". (int)$input ."
				limit 1;"
			)->fetch();
		}

		return f::draw_element('div', ['class' => 'input-group', ...$attributes], implode(PHP_EOL, [
			'  <div class="form-input">',
			'    ' . form_input_hidden($name, true, !empty($product) ? 'data-code="'. $product['code'] .'" data-price="'. $product['price'] .'"' : ''),
			'    <span class="name" style="display: inline-block;">'. $product_name .'</span>',
			'    [<span class="id" style="display: inline-block;">'. (int)$input .'</span>]',
			'  </div>',
			'  <div style="align-self: center;">',
			'    <a href="'. document::href_ilink('b:catalog/product_picker') .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin: .5em;">'. t('title_change', 'Change') .'</a>',
			'  </div>',
		]));
	}

	function form_select_multiple_products($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_product() is deprecated.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		if (!preg_match('#\[\]$#', $name)) {
			return form_select_product($name, $input, $attributes);
		}

		$options = database::query(
			"select p.id, p.code, json_value(p.name, '$.". database::input(language::$selected['code']) ."') as name
			from ". DB_TABLE_PREFIX ."products p
			order by name"
		)->fetch_all(function($product) {
			return [$product['id'], $product['name'] .' &mdash; '. $product['code']];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_product_stock_option($name, $product_id, $input=true, $attributes=[]) {

		$product = reference::product($product_id);

		if ($product->stock_option_type != 'variants' || !$product->stock_options) {
			trigger_error('Product '. $product->id .' has no stock options', E_USER_WARNING);
			return;
		}

		$has_images = array_filter(array_column($product->stock_options, 'image')) ? true : false;

		$options = array_map(function($stock_option) use ($product, $has_images) {

			if ($product->quantity_unit) {
				$formatted_quantity_available = f::format_number($stock_option['quantity_available'], $product->quantity_unit['decimals']) .' '. $product->quantity_unit['name'];
			} else {
				$formatted_quantity_available = f::format_number($stock_option['quantity_available']);
			}

			switch (true) {

				case ($stock_option['quantity_available'] === null || $stock_option['quantity_available'] > 0):
					$icon = f::draw_fonticon('on');
					$notice = t('title_available', 'Available') . (settings::get('display_stock_count') ?  ' (' . $formatted_quantity_available . ')' : '');
					break;

				case (!empty($product->sold_out_status) && !empty($product->sold_out_status['orderable'])):
					$icon = f::draw_fonticon('semi-off');
					$notice = $product->sold_out_status['name'];
					break;

				default:
					$icon = f::draw_fonticon('off');
					$notice = t('title_sold_out', 'Sold Out');
					break;
			}

			$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

			$option = f::draw_element('div', ['class' => 'dropdown-item flex flex-nogap', ...$attributes], implode(PHP_EOL, [
				'<div class="dropdown-item flex flex-nogap">',
				'  '. f::draw_thumbnail('storage://images/' . ($stock_option['image'] ?? 'no_image.svg'), 0, 64, 'product', 'style="width: 64px; height: 64px; margin-inline-end: 1em;"'),
				'  <div class="flex-grow">',
				'    <div class="name">'. $stock_option['name'] .' ['. $stock_option['sku'] .']</div>',
				'    <div class="notice">'. $icon .' '. $notice .'</div>',
				'  </div>',
				'</div>',
			]));

			foreach (['name', 'sku', 'weight', 'weight_unit', 'length', 'width', 'height', 'length_unit'] as $key) {
				if (isset($stock_option[$key])) {
					$attributes['data-'. $key] =  $stock_option[$key];
				}
			}

			return [$stock_option['id'], $option, 'hidden '.$attributes];
		}, $product->stock_options);

		if (preg_match('#\[\]$#', $name)) {
			return form_dropdown($name, $options, $input, $attributes);
		}

		return form_dropdown($name, $options, $input, $attributes);
	}

	function form_select_quantity_unit($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_quantity_unit() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		if ($input === true) {
			$input = form_reinsert_value($name);

			if ($input == '' && file_get_contents('php://input') == ''){
				$input = settings::get('default_quantity_unit_id');
			}
		}

		$options = database::query(
			"select qu.*,
				json_value(qu.name, '$.". database::input(language::$selected['code']) ."') as name,
				json_value(qu.description, '$.". database::input(language::$selected['code']) ."') as description
			from ". DB_TABLE_PREFIX ."quantity_units qu
			order by qu.priority, name asc;"
		)->fetch_all(function($quantity_unit) {
			return [$quantity_unit['id'], $quantity_unit['name'], 'data-separate="'. (!empty($quantity_unit['separate']) ? 'true' : 'false') .'" data-decimals="'. (int)$quantity_unit['decimals'] .'" title="'. f::escape_attr($quantity_unit['description']) .'"'];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_shipping_module($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_shipping_module() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = database::query(
			"select * from ". DB_TABLE_PREFIX ."modules
			where type = 'shipping'
			and status;"
		)->fetch_all(function($module) {
			$module = new $module();
			return [$module['id'], $module['name']];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_sold_out_status($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_sold_out_status() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		if ($input === true) {
			$input = form_reinsert_value($name);

			if ($input == '' && file_get_contents('php://input') == '') {
				$input = settings::get('default_sold_out_status_id');
			}
		}

		$options = database::query(
			"select sos.id,
				json_value(sos.name, '$.". database::input(language::$selected['code']) ."') as name,
				json_value(sos.description, '$.". database::input(language::$selected['code']) ."') as description
			from ". DB_TABLE_PREFIX ."sold_out_statuses sos
			order by name asc;"
		)->fetch_all(function($row) {
			return [$row['id'], $row['name'], 'title="'. f::escape_attr($row['description']) .'"'];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_stock_item($name, $input=true, $attributes=[]) {

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple_stock_items($name, $input, $attributes);
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if ($input) {
			$item = database::query(
				"select si.id, si.sku, json_value(si.name, '$.". database::input(language::$selected['code']) ."') as name
				from ". DB_TABLE_PREFIX ."stock_items si
				where p.id = ". (int)$input ."
				limit 1;"
			)->fetch();
		} else {
			$item_name = '('. t('title_no_items', 'No Item') .')';
		}

		return f::draw_element('div', ['class' => 'input-group', ...$attributes], implode(PHP_EOL, [
			'  <div class="form-input">',
			'    ' . form_input_hidden($name, true, !empty($item) ? 'data-sku="'. $item['sku'] .'"' : ''),
			'    <span class="name" style="display: inline-block;">'. $item_name .'</span>',
			'    [<span class="id" style="display: inline-block;">'. (int)$input .'</span>]',
			'  </div>',
			'  <div style="align-self: center;">',
			'    <a href="'. document::href_ilink('b:catalog/item_picker') .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin: .5em;">'. t('title_change', 'Change') .'</a>',
			'  </div>',
		]));
	}

	function form_select_multiple_stock_items($name, $input=true, $attributes=[]) {

		if (!preg_match('#\[\]$#', $name)) {
			return form_select_stock_item($name, $input, $attributes);
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		if (!is_array($input)) {
			$input = preg_split('#\s*,\s*#', (string)$input, -1, PREG_SPLIT_NO_EMPTY);
		}

		$items = database::query(
			"select si.id, si.sku, si.quantity, json_value(si.name, '$.". database::input(language::$selected['code']) ."') as name
			from ". DB_TABLE_PREFIX ."stock_items si
			where si.id in ('". implode("', '", database::input($input)) ."')
			order by name"
		)->fetch_all();

		$attributes = is_array($attributes) ? $attributes : form_attributes($attributes);

		$uid = uniqid();

		$output = f::draw_element('div', ['id' => 'input-'.$uid, 'class' => 'form-input flex flex-rows', ...$attributes], implode(PHP_EOL, [
			'  <div class="stock-items flex-grow">',
			//!$input ? '<em>'. t('text_no_items', 'No items') .'</em>' : '',
			implode(PHP_EOL, f::array_each($items, fn($item) =>
				'    <div class="stock-item" data-id="'. $item['id'] .'">'. $item['name'] .' &mdash; '. $item['sku'] .' ['. (float)$item['quantity'] .']</div>'
			)),
			'  </div>',
			'  '. form_button('add', t('title_add_item', 'Add'), 'button', ['data-toggle' => 'lightbox', 'data-target' => document::href_ilink('catalog/item_picker', ['js_callback' => '_callback_'.$uid])]),
			'</div>',
		]));

		document::$javascript[] = implode(PHP_EOL, [
			'window._callback_'.$uid.' = function(item){',
			'  let $input = $(\'#input-'.$uid.'\');',
			'  var $item = $(\'<div class="item"></div>\').attr("data-id", item.id).html(item.name +\' &mdash; \'+ item.sku +\' [\'+ item.quantity +\']\').append(\'<button class="btn btn-default btn-sm" class="float-end">x</span>\');',
			'  $(\'.stock-items\', $input).append($item);',
			'}'
		]);

		return $output;
	}

	function form_select_supplier($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_supplier() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = database::query(
			"select id, name, description from ". DB_TABLE_PREFIX ."suppliers
			order by name;"
		)->fetch_all(function($supplier) {
			return [$supplier['id'], $supplier['name'], 'title="'. f::escape_attr($supplier['description']) .'"'];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_tax_class($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_tax_class() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		if ($input === true) {
			$input = form_reinsert_value($name);
			if ($input == '' && file_get_contents('php://input') == '') {
				$input = settings::get('default_tax_class_id');
			}
		}

		$options = database::query(
			"select * from ". DB_TABLE_PREFIX ."tax_classes
			order by name asc;"
		)->fetch_all(function($tax_class) {
			return [$tax_class['id'], $tax_class['name'], 'title="'. f::escape_attr($tax_class['description']) .'"'];
		});

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_template($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_template() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = f::array_each(f::file_search('app://frontend/templates/*', GLOB_ONLYDIR), fn($folder) =>
			basename($folder)
		);

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_timezone($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_timezone() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		$options = array_filter(f::array_each(timezone_identifiers_list(), function($timezone){
			$timezone = explode('/', $timezone); // 0 => Continent, 1 => City

			if (empty($timezone[1]) || !in_array($timezone[0], ['Africa', 'America', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'])) {
				return false;
			}

			return implode('/', $timezone);
		}));

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_weight_unit($name, $input=true, $attributes=[]) {

		if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
			trigger_error('Passing $multiple as 3rd parameter in form_select_weight_unit() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[3])) $attributes = $args[2];
		}

		if ($input === true) {
			$input = form_reinsert_value($name);

			if ($input == '' && file_get_contents('php://input') == '') {
				$input = settings::get('store_weight_unit');
			}
		}

		$options = f::array_each(weight::$units, fn($unit) =>
			[$unit['unit'], $unit['unit'], 'data-value="'. (float)$unit['value'] .'" data-decimals="'. (int)$unit['decimals'] .'" title="'. f::escape_attr($unit['name']) .'"']
		);

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '--']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_volume_unit($name, $input=true, $attributes=[]) {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		$options = [];
		foreach (volume::$units as $unit) {
			$options[] = [$unit['unit'], $unit['unit'], 'data-value="'. (float)$unit['value'] .'" data-decimals="'. (int)$unit['decimals'] .'" title="'. f::escape_attr($unit['name']) .'"'];
		}

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		} else {
			array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']);
			return form_select($name, $options, $input, $attributes);
		}
	}

	function form_select_zone($name, $country_code='', $input=true, $attributes=[], $preamble='none') {

		if (preg_match('#^([A-Z]{2}|default_country_code|store_country_code)$#', $name)) {
			trigger_error('form_select_zone() no longer takes country code as 1st parameter. Instead, use form_zones($name, $country_code, $input)', E_USER_DEPRECATED);
			[$name, $country_code] = [$country_code, $name];
		}

		if (count($args = func_get_args()) > 3 && is_bool($args[3])) {
			trigger_error('Passing $multiple as 4th parameter in form_select_zone() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
			if (isset($args[4])) $attributes = $args[3];
		}

		$country_code = match($country_code) {
			'customer_country_code' => customer::$data['country_code'],
			'default_country_code' => settings::get('default_country_code'),
			'store_country_code' => settings::get('store_country_code'),
			default => $country_code,
		};

		$options = database::query(
			"select * from ". DB_TABLE_PREFIX ."zones
			where country_code = '". database::input($country_code) ."'
			order by name asc;"
		)->fetch_all(function($zone){
			return [$zone['code'], $zone['name']];
		});

		if (!$options) {
			$attributes .= ' disabled';
		}

		if (preg_match('#\[\]$#', $name)) {
			return form_select_multiple($name, $options, $input, $attributes);
		}

		match($preamble) {
			'all' => array_unshift($options, ['', '-- '. t('title_all_zones', 'All Zones') . ' --']),
			'select' => array_unshift($options, ['', '-- '. t('title_select', 'Select') . ' --']),
			'none' => null,
		};

		return form_select($name, $options, $input, $attributes);
	}
