<?php

  function form_begin($name='', $method='post', $action=false, $multipart=false, $parameters='') {
		return  '<form'. (($name) ? ' name="'. functions::escape_html($name) .'"' : '') .' method="'. ((strtolower($method) == 'get') ? 'get' : 'post') .'" enctype="'. (($multipart == true) ? 'multipart/form-data' : 'application/x-www-form-urlencoded') .'" accept-charset="'. mb_http_output() .'"'. (($action) ? ' action="'. functions::escape_html($action) .'"' : '') . ($parameters ? ' ' . $parameters : '') .'>';
  }

  function form_end() {
    return '</form>' . PHP_EOL;
  }

  function form_reinsert_value($name, $array_value=null) {

    if (empty($name)) return;

    foreach ([$_POST, $_GET] as $superglobal) {
      if (empty($superglobal)) continue;

    // Extract name parts
      $parts = preg_split('#[\]\[]+#', preg_replace('#\[\]$#', '', $name), -1, PREG_SPLIT_NO_EMPTY);

    // Get array node
      $node = $superglobal;
      foreach ($parts as $part) {
        if (!isset($node[$part])) continue 2;
        $node = $node[$part];
      }

    // Reinsert node value
      if (is_array($node) && $array_value !== null) {

      // Attempt reinserting a numerical indexed array value
        if (preg_match('#\[\]$#', $name)) {
          if (!is_array($node) || !in_array($array_value, $node)) continue;
          return $array_value;

      // Reinsert a defined key array value
        } else {
          if ($array_value != $node) continue;
          return $array_value;
        }
      }

      if ($node || $node != '') return $node;
    }

    return '';
  }

  function form_button($name, $value, $type='submit', $parameters='', $fonticon='') {

    if (!is_array($value)) {
      $value = [$value, $value];
    }

    return '<button'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="btn btn-default"' : '') .' type="'. functions::escape_html($type) .'" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value[0]) .'"'. ($parameters ? ' '.$parameters : '') .'>'. (($fonticon) ? functions::draw_fonticon($fonticon) . ' ' : '') . (isset($value[1]) ? $value[1] : $value[0]) .'</button>';
  }

  function form_button_link($url, $title, $parameters='', $fonticon='') {
    return '<a '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn btn-default"' : '') .' href="'. functions::escape_html($url) .'"'. ($parameters ? ' '.$parameters : '') .'>'. ($fonticon ? functions::draw_fonticon($fonticon) . ' ' : '') . $title .'</a>';
  }

  function form_button_predefined($name, $parameters='') {

    switch($name) {
      case 'save':
        return functions::form_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success"' . ($parameters ? ' '. $parameters : ''), 'save');

      case 'delete':
        return functions::form_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(&quot;'. language::translate('text_are_you_sure', 'Are you sure?') .'&quot;)) return false;"' . ($parameters ? ' '. $parameters : ''), 'delete');

      case 'cancel':
        return functions::form_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"' . ($parameters ? ' '. $parameters : ''), 'cancel');
    }

    trigger_error('Unknown predefined button ('. functions::escape_html($name) .')', E_USER_WARNING);

    return form_button($name, $value, 'submit', $parameters);
  }

  function form_input_captcha($id, $config=[], $parameters='') {

    $config = [
      //'width' => !empty($config['width']) ? $config['width'] : 125,
      //'height' => !empty($config['height']) ? $config['height'] : 60,
      'length' => !empty($config['length']) ? $config['length'] : 4,
      'set' => !empty($config['set']) ? $config['set'] : 'numbers',
    ];

    return functions::captcha_draw($id, $config, $parameters);
	}

  function form_input_checkbox($name, $value, $input=true, $parameters='') {

    if (is_array($value)) {

      if ($input === true) {
        $input = form_reinsert_value($name, $value[0]);
      }

			return implode(PHP_EOL, [
				'<label'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-check"' : '') .'>',
				'  <input type="checkbox" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value[0]) .'" '. (!strcmp($input, $value[0]) ? ' checked' : '') . ($parameters ? ' ' . $parameters : '') .'>',
				'  ' . (isset($value[1]) ? $value[1] : $value[0]),
				'</label>',
			]);
    }

    if ($input === true) {
      $input = form_reinsert_value($name, $value);
    }

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-check"' : '') .' type="checkbox" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" '. (!strcmp($input, $value) ? ' checked' : '') . ($parameters ? ' ' . $parameters : '') .'>';
	}

  function form_input_code($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

		document::$javascript[] = implode(PHP_EOL, [
			'$(\'textarea[name="'. $name .'"]\').on(\'keydown\', function(e){',
			'	if (e.keyCode != 9) return;',
			'	e.preventDefault();',
			' var start = this.selectionStart, end = this.selectionEnd;',
			'	this.value = this.value.substring(0, start) + \'\t\' + this.value.substring(end);',
			'	this.selectionStart = this.selectionEnd = start + 1;',
			'});',
		]);

    return '<textarea'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-code"' : '') .' name="'. functions::escape_html($name) .'"'. ($parameters ? ' '.$parameters : '') .'>'. functions::escape_html($input) .'</textarea>';
  }

  function form_input_color($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="color" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'"'. ($parameters ? ' '.$parameters : '') .'>';
  }

  function form_input_csv($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    if ($input && $csv = functions::csv_decode($input)) {
      $columns = array_keys($csv[0]);
    } else {
      $csv = [];
      $columns = [];
    }

    $html = implode(PHP_EOL, [
      '<table class="table table-striped table-hover data-table" data-toggle="csv">',
      '  <thead>',
      '    <tr>',

      implode(PHP_EOL, array_map(function($column) {
        return '      <th>'. $column .'<button name="remove_column" class="btn btn default btn-sm">'. functions::draw_fonticon('remove') .'</button></th>';
      }, $columns)),

      '      <th><button class="btn btn-default btn-sm" name="add_column" type="button">'. functions::draw_fonticon('fa-plus', 'style="color: #6c6;"') .' '.  language::translate('title_add_column', 'Add Column') .'</button></th>',
      '    </tr>',
      '  </thead>',
      '  <tbody>',
    ]);

    foreach ($csv as $row) {
      $html .= '    <tr>' . PHP_EOL;
      foreach ($columns as $column) {
        $html .= '      <td contenteditable>'. $row[$column] .'</td>' . PHP_EOL;
      }
      $html .= '      <td><button name="remove_row" class="btn btn default btn-sm">'. functions::draw_fonticon('remove') .'</button></td>' . PHP_EOL;
      $html .= '    </tr>' . PHP_EOL;
    }

    $html .= implode(PHP_EOL, [
      '  <tfoot>',
      '    <tr>',
      '      <td colspan="'. (count($columns)+1) .'"><button class="btn btn-default btn-sm" name="add_row" type="button">'. functions::draw_fonticon('fa-plus', 'style="color: #6c6;"') .' '.  language::translate('title_add_row', 'Add Row') .'</button></td>',
      '    </tr>',
      '  </tfoot>',
      '</table>',
      form_input_textarea($name, $input, 'style="display: none;"'),
    ]);

    document::$javascript['table2csv'] = implode(PHP_EOL, [
      '$(\'table[data-toggle="csv"]\').on(\'click\', \'button[name="remove_row"]\', function(e) {',
      '  e.preventDefault();',
      '  var parent = $(this).closest(\'tbody\');',
      '  $(this).closest(\'tr\').remove();',
      '  $(parent).trigger(\'input\');',
      '});',
      '',
      '$(\'table[data-toggle="csv"] button[name="add_row"]\').click(function(e) {',
      '  e.preventDefault();',
      '  var n = $(this).closest(\'table\').find(\'thead th:not(:last-child)\').length;',
      '  $(this).closest(\'table\').find(\'tbody\').append(',
      '    \'<tr>\' + (\'<td contenteditable></td>\'.repeat(n)) + \'<td><button name="remove_row" class="btn btn default btn-sm">'. functions::draw_fonticon('remove') .'</button></td>\' +\'</tr>\'',
      '  ).trigger(\'input\');',
      '});',
      '',
      '$(\'table[data-toggle="csv"] button[name="add_column"]\').click(function(e) {',
      '  e.preventDefault();',
      '  var $table = $(this).closest(\'table\');',
      '  var title = prompt("'. functions::escape_js(language::translate('title_column_title', 'Column Title')) .'");',
      '  if (!title) return;',
      '  $table.find(\'thead tr th:last-child:last-child\').before(\'<th>\'+ title +\'<button name="remove_column" class="btn btn default btn-sm">'. functions::draw_fonticon('remove') .'</button></th>\');',
      '  $table.find(\'tbody tr td:last-child:last-child\').before(\'<td contenteditable></td>\');',
      '  $table.find(\'tfoot tr td\').attr(\'colspan\', $(this).closest(\'table\').find(\'tfoot tr td\').attr(\'colspan\') + 1);',
      '  $(this).trigger(\'input\');',
      '});',
      '',
      '$(\'table[data-toggle="csv"]\').on(\'input\', function(e) {',
      '   var csv = $(this).find(\'thead tr, tbody tr\').map(function (i, row) {',
      '      return $(row).find(\'th:not(:last-child),td:not(:last-child)\').map(function (j, col) {',
      '        var text = $(col).text();',
      '        if (/(\'|,)/.test(text)) {',
      '          return "\\"\'"+ text.replace(/"/g, "\\"\"") +"\\"";',
      '        } else {',
      '          return text;',
      '        }',
      '      }).get().join(\',\');',
      '    }).get().join(\'\\r\\n\');',
      '  $(this).next(\'textarea\').val(csv);',
      '});',
    ]);

    return $html;
  }

  function form_input_date($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    if ($input && !in_array(substr($input, 0, 10), ['0000-00-00', '1970-01-01'])) {
      $input = date('Y-m-d', strtotime($input));
    } else {
      $input = '';
    }

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="date" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'" placeholder="YYYY-MM-DD"'. ($parameters ? ' '.$parameters : '') .'>';
  }

  function form_input_datetime($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    if ($input && !in_array(substr($input, 0, 10), ['0000-00-00', '1970-01-01'])) {
      $input = date('Y-m-d\TH:i', strtotime($input));
    } else {
      $input = '';
    }

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="datetime-local" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'" placeholder="YYYY-MM-DD [hh:nn]"'. ($parameters ? ' '.$parameters : '') .'>';
  }

  function form_input_decimal($name, $input=true, $decimals=2, $parameters='') {

    if (count($args = func_get_args()) > 4) {
      trigger_error('Passing min and max separate parameters in form_input_decimal() is deprecated. Instead define min="0" max="999" in $parameters', E_USER_DEPRECATED);
      if (isset($args[5])) $parameters = $args[5];
      if (isset($args[3])) $parameters .= ($parameters ? ' ' : '') . 'min="'. (int)$args[3] .'"';
      if (isset($args[4])) $parameters .= ($parameters ? ' ' : '') . 'min="'. (int)$args[4] .'"';
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    if ($input != '') {
      $input = number_format((float)$input, (int)$decimals, '.', '');
    }

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="number" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'" data-decimals="'. $decimals .'"'. ($parameters ? ' '.$parameters : '') .'>';
  }

  function form_input_email($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-icon">'. functions::draw_fonticon('fa-envelope-o fa-fw') .'</span>',
			'  <input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="email" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'"'. ($parameters ? ' '.$parameters : '') .'>',
			'</div>',
		]);
  }

  function form_input_file($name, $parameters='') {
		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="file" name="'. functions::escape_html($name) .'"'. ($parameters ? ' '.$parameters : '') .'>';
  }

  function form_input_hidden($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

		return '<input type="hidden" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'"'. ($parameters ? ' '.$parameters : '') .'>';
	}

  function form_input_month($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

		if (!in_array(substr($input, 0, 7), ['', '0000-00', '1970-00', '1970-01'])) {
			$input = date('Y-m', strtotime($input));
		} else {
			$input = '';
		}

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="month" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'" maxlength="7" pattern="[0-9]{4}-[0-9]{2}" placeholder="YYYY-MM"'. ($parameters ? ' '.$parameters : '') .'>';
	}

  function form_input_money($name, $currency_code=null, $input=true, $parameters='') {

    if (preg_match('#^[A-Z]{3}$#', $name) && !preg_match('#^[A-Z]{3}$#', $currency_code)) {
      trigger_error('Passing currency code as 1st parameter in form_input_money() is deprecated. Instead, use form_input_money($name, $currency_code, $input, $parameters)', E_USER_DEPRECATED);
      list($name, $currency_code) = [$currency_code, $name];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    if (empty($currency_code)) {
      $currency_code = settings::get('store_currency_code');
    }

    $currency = currency::$currencies[$currency_code];

    if ($input != '') {
      $input = number_format((float)$input, $currency['decimals'], '.', '');
      //$input = rtrim(preg_replace('#(\.'. str_repeat('\d', 2) .')0{1,2}$#', '$1', $input), '.'); // Auto decimals
    }

    return implode(PHP_EOL, [
      '<div class="input-group">',
      '  <strong class="input-group-text" style="opacity: 0.75; font-family: monospace;">'. functions::escape_html($currency['code']) .'</strong>',
      '  ' . form_input_decimal($name, $input, $currency['decimals'], 'step="any" data-type="currency"'),
      '</div>',
    ]);
  }

  function form_input_number($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = (int)form_reinsert_value($name);
    }

    if ($input != '') {
      $input = round($input);
    }

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="number" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'" step="1"'. ($parameters ? ' '.$parameters : '') .'>';
  }

  function form_input_password($name, $input='', $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-icon">'. functions::draw_fonticon('fa-key fa-fw') .'</span>',
			'  <input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="password" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'"'. ($parameters ? ' '.$parameters : '') .'>',
			'</div>',
		]);
  }

  function form_input_phone($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-icon">'. functions::draw_fonticon('fa-phone fa-fw') .'</span>',
			'  <input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="tel" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'" pattern="^\+?([0-9]|-| )+$"'. ($parameters ? ' '.$parameters : '') .'>',
			'</div>',
		]);
  }

  function form_input_radio_button($name, $value, $input=true, $parameters='') {

    if (is_array($value)) {

      if ($input === true) {
        $input = form_reinsert_value($name, $value[0]);
      }

			return implode(PHP_EOL, [
				'<label'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-check"' : '') .'>',
				'  <input type="radio" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value[0]) .'" '. (!strcmp($input, $value[0]) ? ' checked' : '') . ($parameters ? ' ' . $parameters : '') .'>',
				'  ' . (isset($value[1]) ? $value[1] : $value[0]),
				'</label>',
			]);
    }

    if ($input === true) {
      $input = form_reinsert_value($name, $value);
    }

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-check"' : '') .' type="radio" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($value) .'" '. (!strcmp($input, $value) ? ' checked' : '') . ($parameters ? ' ' . $parameters : '') .'>';
  }

  function form_input_range($name, $input=true, $min='', $max='', $step='', $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-range"' : '') .' type="range" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'" min="'. (float)$min .'" max="'. (float)$max .'" step="'. (float)$step .'"'. ($parameters ? ' '.$parameters : '') .'>';
  }

	function form_input_search($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    return implode(PHP_EOL, [
      '<div class="input-group">',
      '  <span class="input-group-icon">'. functions::draw_fonticon('fa-search fa-fw') .'</span>',
      '  <input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="search" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'"'. ($parameters ? ' '.$parameters : '') .'>',
      '</div>',
    ]);
  }

  function form_input_tags($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="text" data-toggle="tags" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'"'. ($parameters ? ' '.$parameters : '') .'>';
  }

  function form_input_text($name, $input=true, $parameters='') {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="text" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'"'. ($parameters ? ' '.$parameters : '') .'>';
  }

  function form_input_textarea($name, $input=true, $parameters='') {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		return '<textarea'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' name="'. functions::escape_html($name) .'"'. ($parameters ? ' '.$parameters : '') .'>'. functions::escape_html($input) .'</textarea>';
  }

	function form_input_time($name, $input=true, $parameters='') {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="time" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'"'. ($parameters ? ' '.$parameters : '') .'>';
	}

	function form_input_url($name, $input=true, $parameters='') {

		if ($input === true) {
			$input = form_reinsert_value($name);
		}

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="url" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'"'. ($parameters ? ' '.$parameters : '') .'>';
	}

  function form_input_username($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-icon">'. functions::draw_fonticon('fa-user fa-fw') .'</span>',
			'  <input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="text" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($input) .'"'. ($parameters ? ' '.$parameters : '') .'>',
			'</div>',
		]);
  }

  function form_input_wysiwyg($name, $input=true, $parameters='') {

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
			'  autogrowOnEnter: true,',
			'  imageWidthModalEdit: true,',
			'  removeformatPasted: true,',
			'  semantic: false',
			'});'
		]);

		return '<textarea name="'. functions::escape_html($name) .'"'. ($parameters ? ' '.$parameters : '') .'>'. functions::escape_html($input) .'</textarea>';
  }

  function form_regional_input($name, $language_code='', $input=true, $type='text', $parameters='') {

    if (preg_match('#^[a-z]{2}$#', $name)) {
      trigger_error('Passing $language code as 1st parameter in form_regional_text() is deprecated. Instead, use form_regional_text($name, $language_code, $input, $parameters)', E_USER_DEPRECATED);
      list($name, $language_code) = [$language_code, $name];
    }

    if (empty($language_code)) {
      $language_code = settings::get('store_language_code');
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-text" style="font-family: monospace;" title="'. functions::escape_html(language::$languages[$language_code]['name']) .'">'. functions::escape_html($language_code) .'</span>',
			'  <input class="form-input" name="'. functions::escape_html($name) .'" type="'. functions::escape_html($type) .'" value="'. functions::escape_html($input) .'">',
			'</div>'
		]);
  }

  function form_regional_text($name, $language_code='', $input=true, $parameters='') {

    if (empty($language_code)) {
      $language_code = settings::get('store_language_code');
    }

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-text" style="font-family: monospace;" title="'. functions::escape_html(language::$languages[$language_code]['name']) .'">'. functions::escape_html($language_code) .'</span>',
			'  ' . form_input_text($name, $input, $parameters),
			'</div>',
		]);
  }

  function form_regional_textarea($name, $language_code='', $input=true, $parameters='') {

    if (preg_match('#^[a-z]{2}$#', $name)) {
      trigger_error('Passing language code as 1st parameter in form_regional_textarea() is deprecated. Instead, use form_regional_textarea($name, $language_code, $input, $parameters)', E_USER_DEPRECATED);
      list($name, $language_code) = [$language_code, $name];
    }

    if (empty($language_code)) {
      $language_code = settings::get('store_language_code');
    }

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-text" style="font-family: monospace;" title="'. functions::escape_html(language::$languages[$language_code]['name']) .'">'. functions::escape_html($language_code) .'</span>',
			'  ' . form_input_textarea($name, $input, $parameters),
			'</div>',
		]);
  }

  function form_regional_wysiwyg($name, $language_code='', $input=true, $parameters='') {

    if (preg_match('#^[a-z]{2}$#', $name)) {
      trigger_error('Passing language code as 1st parameter in form_regional_wysiwyg() is deprecated. Instead, use form_regional_wysiwyg($name, $language_code, $input, $parameters)', E_USER_DEPRECATED);
      list($name, $language_code) = [$language_code, $name];
    }

    if (empty($language_code)) {
      $language_code = settings::get('store_language_code');
    }

		return implode(PHP_EOL, [
			'<div class="input-group">',
			'  <span class="input-group-text" style="font-family: monospace;" title="'. functions::escape_html(language::$languages[$language_code]['name']) .'">'. functions::escape_html($language_code) .'</span>',
			'  ' . form_input_wysiwyg($name, $input, $parameters),
			'</div>',
		]);
  }

  function form_select($name, $options=[], $input=true, $parameters='') {

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    }

		$html = '<select '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-select"' : '') .' name="'. functions::escape_html($name) .'"'. ($parameters ? ' ' . $parameters : '') .'>' . PHP_EOL;

    $is_numerical_index = (array_keys($options) === range(0, count($options) - 1));

    foreach ($options as $key => $option) {

      if (!is_array($option)) {
        if ($is_numerical_index) {
          $option = [$option];
        } else {
          $option = [$key, $option];
        }
      }

      if ($input === true) {
        $option_input = form_reinsert_value($name, $option[0]);
      } else {
        $option_input = $input;
      }

      $html .= '  <option value="'. functions::escape_html($option[0]) .'"'. (!strcmp((string)$option[0], (string)$option_input) ? ' selected' : '') . ((isset($option[2])) ? ' ' . $option[2] : '') . '>'. (isset($option[1]) ? $option[1] : $option[0]) .'</option>' . PHP_EOL;
    }

    $html .= '</select>';

    return $html;
  }

  function form_select_dropdown($name, $options=[], $input=true, $parameters='') {

    $html = implode(PHP_EOL, [
      '<div class="dropdown"'. ($parameters ? ' ' . $parameters : '') .'>',
      '  <div class="form-select" data-toggle="dropdown">-- '. language::translate('title_select', 'Select') .' --</div>',
      '  <ul class="dropdown-menu">',
    ]);

    $is_numerical_index = (array_keys($options) === range(0, count($options) - 1));

    foreach ($options as $key => $option) {

      if (!is_array($option)) {
        if ($is_numerical_index) {
          $option = [$option, $option];
        } else {
          $option = [$key, $option];
        }
      }

      if (preg_match('#\[\]$#', $name)) {
        $html .= '<li class="option">' . functions::form_input_checkbox($name, $option, $input, isset($option[2]) ? $option[2] : '') .'</li>' . PHP_EOL;
      } else {
        $html .= '<li class="option">' . functions::form_input_radio_button($name, $option, $input, isset($option[2]) ? $option[2] : '') .'</li>' . PHP_EOL;
      }
    }

    $html .= implode(PHP_EOL, [
      '  </ul>',
      '</div>',
    ]);

    return $html;
  }

  function form_select_multiple($name, $options=[], $input=true, $parameters='') {

		$html = '<div class="form-input"' . ($parameters ? ' ' . $parameters : '') .'>' . PHP_EOL;

    $is_numerical_index = (array_keys($options) === range(0, count($options) - 1));

    foreach ($options as $key => $option) {

      if (!is_array($option)) {
        if ($is_numerical_index) {
          $option = [$option, $option];
        } else {
          $option = [$key, $option];
        }
      }

      $html .= form_input_checkbox($name, $option, $input, isset($option[2]) ? $option[2] : '');
    }

    $html .= '</div>';

    return $html;
  }

  function form_select_optgroup($name, $groups=[], $input=true, $parameters='') {

    if (count($args = func_get_args()) > 3 && is_bool($args[3])) {
      trigger_error('Passing $multiple as 4th parameter in form_select_optgroup() is deprecated as determined by input name instead.', E_USER_DEPRECATED);
      if (isset($args[4])) $parameters = $args[3];
    }

    if (!is_array($groups)) {
    	$groups = [$groups];
	}

		$html = '<select class="form-select" name="'. functions::escape_html($name) .'"'. (preg_match('#\[\]$#', $name) ? ' multiple' : '') . ($parameters ? ' ' . $parameters : '') .'>' . PHP_EOL;

    foreach ($groups as $group) {
      $html .= '    <optgroup label="'. $group['label'] .'">' . PHP_EOL;

      $is_numerical_index = (array_keys($group['options']) === range(0, count($group['options']) - 1));

      foreach ($group['options'] as $key => $option) {

        if (!is_array($option)) {
          if ($is_numerical_index) {
            $option = [$option, $option];
          } else {
            $option = [$key, $option];
          }
        }

        if ($input === true) {
          $option_input = form_reinsert_value($name, $option[0]);
        } else {
          $option_input = $input;
        }

        $html .= '      <option value="'. functions::escape_html($option[0]) .'"'. (($option[0] == $option_input) ? ' selected' : '') . ((isset($option[2])) ? ' ' . $option[2] : '') . '>'. $option[1] .'</option>' . PHP_EOL;
      }

      $html .= '    </optgroup>' . PHP_EOL;
    }

    $html .= '  </select>';

    return $html;
  }

  function form_switch($name, $value, $label, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    return '<label><input '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="form-switch"' : '') .' name="'. functions::escape_html($name) .'"'. ($parameters ? ' '.$parameters : '') .'>'. functions::escape_html($label) .'</label>';
  }

  function form_toggle($name, $type='t/f', $input=true, $parameters='') {

    if (strpos($input, '/') === true) {
      trigger_error('Passing type as 3rd parameter in form_toggle() is deprecated. Use instead form_toggle($name, $type, $input, $parameters)', E_USER_DEPRECATED);
      list($type, $input) = [$input, $type];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    $input = preg_match('#^(1|active|enabled|on|true|yes)$#i', $input) ? '1' : '0';

    switch ($type) {
      case 'a/i':
        $options = [
          '1' => language::translate('title_active', 'Active'),
          '0' => language::translate('title_inactive', 'Inactive'),
        ];
        break;

      case 'e/d':
        $options = [
          '1' => language::translate('title_enabled', 'Enabled'),
          '0' => language::translate('title_disabled', 'Disabled'),
        ];
        break;

      case 'y/n':
        $options = [
          '1' => language::translate('title_yes', 'Yes'),
          '0' => language::translate('title_no', 'No'),
        ];
        break;

      case 'o/o':
        $options = [
          '1' => language::translate('title_on', 'On'),
          '0' => language::translate('title_off', 'Off'),
        ];
        break;

      case 't/f':
      default:
        $options = [
          '1' => language::translate('title_true', 'True'),
          '0' => language::translate('title_false', 'False'),
        ];
        break;
    }

    return form_toggle_buttons($name, $options, $input, $parameters);
  }

  function form_toggle_buttons($name, $options, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    $html = '<div '. (!preg_match('#class="([^"]+)?"#', $parameters) ? 'class="btn-group btn-block btn-group-inline"' : '') .' data-toggle="buttons"'. ($parameters ? ' '.$parameters : '') .'>'. PHP_EOL;

    $is_numerical_index = (array_keys($options) === range(0, count($options) - 1));

    foreach ($options as $key => $option) {

      if (!is_array($option)) {
        if ($is_numerical_index) {
          $option = [$option, $option];
        } else {
          $option = [$key, $option];
        }
      }

			$html .= implode(PHP_EOL, [
				'  <label class="btn btn-default'. ($input == $option[0] ? ' active' : '') .'">',
				'    <input type="radio" name="'. functions::escape_html($name) .'" value="'. functions::escape_html($option[0]) .'"'. (!strcmp($input, $option[0]) ? ' checked' : '') . (!empty($option[2]) ? ' '. $option[2] : '') .'>'. $option[1],
				'  </label>',
			]);
    }

    $html .= '</div>';

    return $html;
  }

  ##################################
  # Platform specific form helpers #
  ##################################

  function form_function($name, $function, $input=true, $parameters='') {

    if (preg_match('#\)$#', $name)) {
      trigger_error('Passing function as 1st parameter in form_function() is deprecated. Instead, use form_function($name, $function, $input, $parameters)', E_USER_DEPRECATED);
      list($name, $function) = [$function, $name];
    }

    if (!preg_match('#(\w*)\((.*?)\)$#i', $function, $matches)) {
      trigger_error('Invalid form function ('. $function .')', E_USER_WARNING);
    }

    $options = [];
    if (!empty($matches[2])) {
      $options = preg_split('#\s*,\s*#', $matches[2], -1, PREG_SPLIT_NO_EMPTY);
      $options = array_map(function($s){ return trim($s, '\'" '); }, $options);
    }

    switch ($matches[1]) {

      case 'administrator':
      case 'administrators':
        return form_select_administrator($name, $input, $parameters);

      case 'date':
        return form_input_date($name, $input, $parameters);

      case 'datetime':
        return form_input_datetime($name, $input, $parameters);

      case 'decimal':
      case 'float':
        return form_input_decimal($name, $input, 2, $parameters);

      case 'number':
      case 'int':
        return form_input_number($name, $input, $parameters);

      case 'checkbox':
        $html = '';
        foreach ($options as $option) {
          $html .= form_input_checkbox($name, [$option, $option], $input, $parameters);
        }
        return $html;

      case 'color':
        return form_input_color($name, $input, $parameters);

      case 'smallinput': // Deprecated
      case 'smalltext': // Deprecated
      case 'input': // Deprecated
      case 'text':
        return form_input_text($name, $input, $parameters);

      case 'password':
        return form_input_password($name, $input, $parameters);

      case 'mediumtext':
      case 'textarea':
        return form_input_textarea($name, $input, $parameters . ' rows="5"');

      case 'bigtext':
        return form_input_textarea($name, $input, $parameters . ' rows="10"');

      case 'category':
        return form_draw_category_field($name, $input, $parameters);

      case 'categories':
        return form_select_category($name, $input, $parameters);

      case 'customer':
      case 'customers':
        return form_select_customer($name, $input, $parameters);

      case 'country':
      case 'countries':
        return form_select_country($name, $input, $parameters);

      case 'currency':
      case 'currencies':
        return form_select_currency($name, $input, $parameters);

      case 'csv':
        return form_input_csv($name, $input, true, $parameters);

      case 'delivery_status':
      case 'delivery_statuses':
        return form_select_delivery_status($name, $input, $parameters);

      case 'email':
        return form_input_email($name, $input, $parameters);

      case 'file':
      case 'files':
        return form_select_file($name, $options[0], $input, $parameters);

      case 'geo_zone':
      case 'geo_zones':
        return form_select_geo_zone($name, $input, $parameters);

      case 'incoterm':
      case 'incoterms':
        return form_select_incoterm($name, $input, false, $parameters);

      case 'language':
      case 'languages':
        return form_select_language($name, $input, $parameters);

      case 'length_class': // Deprecated
      case 'length_classes': // Deprecated
      case 'length_unit':
      case 'length_units':
        return form_select_length_unit($name, $input, $parameters);

      case 'payment_term':
      case 'payment_terms':
        return form_select_draw_payment_term($name, $input, $parameters);

      case 'product':
      case 'products':
        return form_select_product($name, $input, $parameters);

      case 'payment_term':
      case 'payment_terms':
        return form_select_payment_term($name, $input, false, $parameters);

      case 'quantity_unit':
      case 'quantity_units':
        return form_select_quantity_unit($name, $input, $parameters);

      case 'stock_option':
      case 'stock_options':
        return form_select_stock_option($name, $input, $parameters);

      case 'order_status':
      case 'order_statuses':
        return form_select_order_status($name, $input, $parameters);

      case 'page':
      case 'pages':
        return form_select_page($name, $input, $parameters);

      case 'password':
        return functions::form_input_password($name, $input);

      case 'phone':
        return functions::form_input_phone($name, $input);

      case 'radio':
        $html = '';
        foreach ($options as $option) {
          $html .= form_input_radio_button($name, [$option, $option], $input, $parameters);
        }
        return $html;

      case 'regional_input': //Deprecated
      case 'regional_text':
        $html = '';
        foreach (array_keys(language::$languages) as $language_code) {
          $html .= form_regional_text($name.'['. $language_code.']', $language_code, $input, $parameters);
        }
        return $html;

      case 'regional_textarea':
        $html = '';
        foreach (array_keys(language::$languages) as $language_code) {
          $html .= form_regional_textarea($name.'['. $language_code.']', $language_code, $input, $parameters);
        }
        return $html;

      case 'regional_wysiwyg':
        $html = '';
        foreach (array_keys(language::$languages) as $language_code) {
          $html .= form_regional_wysiwyg($name.'['. $language_code.']', $language_code, $input, $parameters);
        }
        return $html;

      case 'page':
      case 'pages':
        return form_select_page($name, $input, $parameters);

      case 'radio':
        $html = '';
        for ($i=0; $i<count($options); $i++) {
          $html .= '<div class="radio"><label>'. form_input_radio_button($name, $options[$i], $input, $parameters) .' '. $options[$i] .'</label></div>';
        }
        return $html;

      case 'select':
      case 'select_multiple': // Deprecated
        for ($i=0; $i<count($options); $i++) $options[$i] = [$options[$i]];
        return form_select($name, $options, $input, $parameters);

      case 'tags':
        return form_input_tags($name, $input, $parameters);

      case 'textarea':
        return form_input_textarea($name, $input, $parameters);

      case 'template':
      case 'templates':
        return form_select_template($name, $input, $parameters);

      case 'time':
        return form_input_time($name, $input, $parameters);

      case 'timezone':
      case 'timezones':
        return form_select_timezone($name, $input, $parameters);

      case 'toggle':
        return form_toggle($name, fallback($options[0], null), $input);

      case 'sold_out_status':
      case 'sold_out_statuses':
        return form_select_sold_out_status($name, $input, $parameters);

      case 'tax_class':
      case 'tax_classes':
        return form_select_tax_class($name, $input, $parameters);

      case 'upload':
        return form_input_file($name, $parameters);

      case 'url':
        return form_input_url($name, $input, $parameters);

      case 'weight_class': // Deprecated
      case 'weight_classes': // Deprecated
      case 'weight_unit':
      case 'weight_units':
        return form_select_weight_unit($name, $input, $parameters);

      case 'volume_unit':
      case 'volume_units':
        return form_select_volume_unit($name, $input, $parameters);

      case 'wysiwyg':
        return form_input_wysiwyg($input, $name, $parameters);

      case 'zone':
      case 'zones':
        $option = !empty($options) ? $options[0] : '';
        //if (empty($option)) $option = settings::get('store_country_code');
        return form_select_zone($name, $option, $input, $parameters);

      default:
        trigger_error('Unknown function name ('. $function .')', E_USER_WARNING);
        return form_input_text($name, $input, $parameters);
        break;
    }
  }

  function form_select_address($name, $input=true, $parameters='') {

    $addresses = database::query(
      "select * from ". DB_TABLE_PREFIX ."customers_addresses
      where customer_id = ". (int)customer::$data['id'] ."
      order by id asc;"
    )->fetch_custom(function($address){
      $formatted_address = reference::country($address['country_code'])->format_address($address);
      return [$address['id'], $formatted_address];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple_field($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_administrator($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_administrator() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = database::query(
			"select id, username from ". DB_TABLE_PREFIX ."administrators
      order by username;"
    )->fetch_custom(function($administrator){
      return [$administrator['id'], $administrator['username']];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_attribute_group($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_attribute_group() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = database::query(
      "select ag.id, agi.name from ". DB_TABLE_PREFIX ."attribute_groups ag
      left join ". DB_TABLE_PREFIX ."attribute_groups_info agi on (agi.group_id = ag.id and agi.language_code = '". database::input(language::$selected['code']) ."')
      order by name;"
    )->fetch_custom(function($group){
      return [$group['id'], $group['name']];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_attribute_value($name, $group_id, $input=true, $parameters='') {

    if (is_numeric($name)) {
      trigger_error('form_select_attribute_value_lit() no longer takes group ID as 1st parameter. Instead, use form_attribute_values($name, $group_id, $input, $parameters)', E_USER_DEPRECATED);
      list($name, $group_id) = [$group_id, $name];
    }

    if (count($args = func_get_args()) > 3 && is_bool($args[3])) {
      trigger_error('Passing $multiple as 4th parameter in form_select_attribute_value() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[4])) $parameters = $args[3];
    }

    $options = database::query(
      "select av.id, avi.name from ". DB_TABLE_PREFIX ."attribute_values av
      left join ". DB_TABLE_PREFIX ."attribute_values_info avi on (avi.value_id = av.id and avi.language_code = '". database::input(language::$selected['code']) ."')
      where group_id = ". (int)$group_id ."
      order by name;"
    )->fetch_custom(function($value){
      return [$value['id'], $value['name']];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_brand($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_brand() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = database::query(
      "select id, name from ". DB_TABLE_PREFIX ."brands
      order by name asc;"
    )->fetch_custom(function($brand){
      return [$brand['id'], $brand['name']];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_category($name, $input=true, $parameters='') {

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple_categories($name, $input, $parameters);
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    if ($input) {
      $category_name = reference::category($input)->name;
    } else {
      $category_name = language::translate('title_root', 'Root');
    }

    functions::draw_lightbox();

    return implode(PHP_EOL, [
      '<div class="input-group"'. ($parameters ? ' ' . $parameters : '') .'>',
      '  <div class="form-input">',
      '    ' . form_input_hidden($name, true),
      '    '. functions::draw_fonticon('folder') .' <span class="name" style="display: inline-block;">'. $category_name .'</span>',
      '  </div>',
      '  <div style="align-self: center;">',
      '    <a href="'. document::href_ilink('b:catalog/category_picker', ['parent_id' => $input]) .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin: .5em;">'. language::translate('title_change', 'Change') .'</a>',
      '  </div>',
      '</div>',
    ]);
  }

  function form_select_multiple_categories($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_multiple_categories() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if (!preg_match('#\[\]$#', $name)) {
      return form_select_category($name, $input, $parameters);
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    if (preg_match('#\[\]$#', $name) && !is_array($input)) {
      $input = [$input];
    }

    $html = implode(PHP_EOL, [
      '<div data-toggle="category-picker"' . ($parameters ? ' ' . $parameters : '') .'>',
      '  <div class="form-input" style="overflow-y: auto; min-height: 100px; max-height: 480px;">',
      '    <ul class="categories list-unstyled">',
    ]);

    if (empty($parent_id)) {
      $options[] = ['0' => functions::draw_fonticon('fa-folder fa-lg', 'style="color: #cccc66;"') .' ['. language::translate('title_root', 'Root') .']',];
    }

    $categories_query = database::query(
      "select c.id, ci.name from ". DB_TABLE_PREFIX ."categories c
      left join ". DB_TABLE_PREFIX ."categories_info ci on (c.id = ci.category_id and ci.language_code = '". database::input(language::$selected['code']) ."')
      where c.id in ('". implode("', '", database::input($input)) ."');"
    );

    while ($category = database::fetch($categories_query)) {

      $path = [];
      if (!empty(reference::category($category['id'])->path)) {
        foreach (reference::category($category['id'])->path as $ancestor) {
          $path[] = $ancestor->name;
        }
      }

      $html .= implode(PHP_EOL, [
        '<li class="list-item" style="display: flex;">',
        '  ' . form_input_hidden($name, $category['id'], 'data-name="'. functions::escape_html($category['name']) .'"'),
        '  <div style="flex-grow: 1;">' . functions::draw_fonticon('folder') .' '. implode(' &gt; ', $path) .'</div>',
        '  <button class="remove btn btn-default btn-sm" type="button">'. language::translate('title_remove', 'Remove') .'</button>',
        '</li>',
      ]);
    }

    $html .= implode(PHP_EOL, [
      '    </ul>',
      '  </div>',
      '  <div class="dropdown">',
      '  '. form_input_search('', '', 'autocomplete="off" placeholder="'. functions::escape_html(language::translate('text_search_categories', 'Search categories')) .'&hellip;"'),
      '    <ul class="dropdown-menu" style="padding: 1em; right: 0; max-height: 480px; overflow-y: auto;"></ul>',
      '  </div>',
    ]);

    document::$javascript['category-picker'] = implode(PHP_EOL, [
      '$(\'[data-toggle="category-picker"]\').categoryPicker({',
      '  inputName: "'. functions::escape_js($name) .'",',
      '  link: "'. document::ilink('b:catalog/categories.json') .'",',
      '  icons: {',
      '    folder: \''. functions::draw_fonticon('folder') .'\',',
      '    back: \''. functions::draw_fonticon('fa-arrow-left') .'\'',
      '  },',
      '  translations: {',
      '    search_results: "'. language::translate('title_search_results', 'Search Results') .'",',
      '    root: "'. language::translate('title_root', 'Root') .'",',
      '    add: "'. language::translate('title_add', 'Add') .'",',
      '    remove: "'. language::translate('title_remove', 'Remove') .'",',
      '    root: "'. language::translate('title_root', 'Root') .'"',
      '  }',
      '});',
    ]);

    return $html;
  }

  function form_select_country($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_country() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);

      if ($input == '' && file_get_contents('php://input') == '') {
        $input = settings::get('default_country_code');
      }
    }

    switch ($input) {

      case 'customer_country_code':
        $input = customer::$data['country_code'];
        break;

      case 'default_country_code':
        $input = settings::get('default_country_code');
        break;

      case 'store_country_code':
        $input = settings::get('store_country_code');
        break;
    }

    $options = database::query(
      "select * from ". DB_TABLE_PREFIX ."countries
      where status
      order by name asc;"
    )->fetch_custom(function($country) {
      return [$country['iso_code_2'], $country['name'], 'data-tax-id-format="'. $country['tax_id_format'] .'" data-postcode-format="'. $country['postcode_format'] .'" data-phone-code="'. $country['phone_code'] .'"'];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_currency($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_currency() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = [];
    foreach (currency::$currencies as $currency) {
      $options[] = [$currency['code'], $currency['name'], 'data-value="'. (float)$currency['value'] .'" data-decimals="'. (int)$currency['decimals'] .'" data-prefix="'. functions::escape_html($currency['prefix']) .'" data-suffix="'. functions::escape_html($currency['suffix']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_customer($name, $input=true, $parameters='') {

    if (empty(administrator::$data['id'])) {
      trigger_error('Must be logged in to use form_select_customer()', E_USER_ERROR);
    }

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_customer() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if (!preg_match('#\[\]$#', $name)) {
      return form_select_multiple_customers($name, $input, $parameters);
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    $account_name = language::translate('title_guest', 'Guest');

    if ($input) {
      $customer = database::query(
        "select * from ". DB_TABLE_PREFIX ."customers
        where id = ". (int)$input ."
        limit 1;"
      )->fetch();

      if ($customer) {
        $account_name = $customer['company'] ? $customer['company'] : $customer['firstname'] .' '. $customer['lastname'];
      } else {
        $account_name = '<em>'. language::translate('title_unknown', 'Unknown') .'</em>';
      }
    }

    return implode(PHP_EOL, [
      '<div class="form-input"'. ($parameters ? ' ' . $parameters : '') .'>',
      '  ' . form_input_hidden($name, true),
      '  '. language::translate('title_id', 'ID') .': <span class="id">'. (int)$input .'</span> &ndash; <span class="name">'. $account_name .'</span> <a href="'. document::href_ilink('b:customers/customer_picker') .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin-inline-start: 5px;">'. language::translate('title_change', 'Change') .'</a>',
      '</div>',
    ]);
  }

  function form_select_multiple_customers($name, $input=true, $parameters='') {

    if (empty(administrator::$data['id'])) {
      trigger_error('Must be logged in to use form_select_multiple_customers()', E_USER_ERROR);
    }

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_multiple_customers() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if (!preg_match('#\[\]$#', $name)) {
      return form_select_customer($name, $input, $parameters);
    }

    $options = database::query(
      "select id, email, company, firstname, lastname from ". DB_TABLE_PREFIX ."customers
      order by email;"
    )->fetch_custom(function($customer) {
      return [$customer['id'], $customer['email'], 'data-name="'. functions::escape_html($customer['company'] ? $customer['company'] : $customer['firstname'] .' '. $customer['lastname']) .'"'];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_delivery_status($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_delivery_status() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);

      if ($input == '' && file_get_contents('php://input') == '') {
        $input = settings::get('default_delivery_status_id');
      }
    }

    $options = database::query(
      "select ds.id, dsi.name , dsi.description from ". DB_TABLE_PREFIX ."delivery_statuses ds
      left join ". DB_TABLE_PREFIX ."delivery_statuses_info dsi on (dsi.delivery_status_id = ds.id and dsi.language_code = '". database::input(language::$selected['code']) ."')
      order by dsi.name asc;"
    )->fetch_custom(function($row) {
      return [$row['id'], $row['name'], 'title="'. functions::escape_html($row['description']) .'"'];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_encoding($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_encoding() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
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
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_file($name, $parameters='') {

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple_files($name, $options, $input, $parameters);
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

		return implode(PHP_EOL, [
			'<div class="form-input"'. ($parameters ? ' ' . $parameters : '') .'>',
			'  ' . form_input_hidden($name, true),
			'  <span class="value">'. ($input ? $input : '('. language::translate('title_none', 'None') .')') .'</span> <a href="'. document::href_ilink('b:files/file_picker') .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin-inline-start: 5px;">'. language::translate('title_change', 'Change') .'</a>',
			'</div>',
		]);

		return '<input'. (!preg_match('#class="([^"]+)?"#', $parameters) ? ' class="form-input"' : '') .' type="file" name="'. functions::escape_html($name) .'"'. ($parameters ? ' '.$parameters : '') .'>';
  }

  function form_select_multiple_files($name, $glob, $input=true, $parameters='') {

    if (!preg_match('#\[\]$#', $name)) {
      return form_select_file($name, $options, $input, $parameters);
    }

    $options = [];

		foreach (functions::file_search($glob) as $file) {
			$file = preg_replace('#^'. preg_quote('app://', '#') .'#', '', $file);
			if (is_dir('app://' . $file)) {
        $options[] = [basename($file).'/', $file.'/'];
      } else {
        $options[] = [basename($file), $file];
      }
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_geo_zone($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_geo_zone() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = database::query(
      "select * from ". DB_TABLE_PREFIX ."geo_zones
      order by name asc;"
    )->fetch_custom(function($geo_zone) {
      return [$geo_zone['id'], $geo_zone['name']];
    });

    if (!$options) {
      return form_select($name, $options, $input, $parameters . ' disabled');
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_incoterm($name, $input=true, $parameters='') {

    $options = [
      ['EXW', 'EXW &ndash; '. language::translate('title_incoterm_exw', 'Ex Works')],
      ['FCA', 'FCA &ndash; '. language::translate('title_incoterm_fca', 'Free Carrier')],
      ['FAS', 'FAS &ndash; '. language::translate('title_incoterm_fas', 'Free Alongside Ship')],
      ['FOB', 'FOB &ndash; '. language::translate('title_incoterm_fob', 'Free On Board')],
      ['CFR', 'CFR &ndash; '. language::translate('title_incoterm_cfr', 'Cost and Freight')],
      ['CIF', 'CIF &ndash; '. language::translate('title_incoterm_cif', 'Cost, Insurance and Freight')],
      ['CPT', 'CPT &ndash; '. language::translate('title_incoterm_cpt', 'Carriage Paid To')],
      ['CIP', 'CIP &ndash; '. language::translate('title_incoterm_cip', 'Carriage and Insurance Paid')],
      ['DDP', 'DDP &ndash; '. language::translate('title_incoterm_ddp', 'Delivered Duty Paid')],
      ['DPU', 'DPU &ndash; '. language::translate('title_incoterm_dpu', 'Delivered At Place Unloaded')],
      ['DAP', 'DAP &ndash; '. language::translate('title_incoterm_dap', 'Delivered At Place')],
    ];

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_language($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_language() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = [];

    foreach (language::$languages as $language) {
      $options[] = [$language['code'], $language['name'], 'data-decimal-point="'. $language['decimal_point'] .'" data-thousands-sep="'. $language['thousands_sep'] .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_length_unit($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_length_unit() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);

      if ($input == '' && file_get_contents('php://input') == '') {
        $input = settings::get('store_length_unit');
      }
    }

    $options = [];
    foreach (length::$units as $unit) {
      $options[] = [$unit['unit'], $unit['unit'], 'data-value="'. (float)$unit['value'] .'" data-decimals="'. (int)$unit['decimals'] .'" title="'. functions::escape_html($unit['name']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['--', '']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_mysql_collation($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_mysql_collation() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = database::query(
      "select COLLATION_NAME from information_schema.COLLATIONS
      where CHARACTER_SET_NAME = 'utf8mb4'
      order by COLLATION_NAME;"
    )->fetch_all('COLLATION_NAME');

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_order_status($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_order_status() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = database::query(
      "select os.id, os.icon, os.color, osi.name from ". DB_TABLE_PREFIX ."order_statuses os
      left join ". DB_TABLE_PREFIX ."order_statuses_info osi on (osi.order_status_id = os.id and osi.language_code = '". database::input(language::$selected['code']) ."')
      order by field(os.state, 'created', 'on_hold', 'ready', 'delayed', 'processing', 'completed', 'dispatched', 'in_transit', 'delivered', 'returning', 'returned', 'cancelled', ''), os.priority, osi.name asc;"
    )->fetch_custom(function($row) {
      return [$row['id'], functions::draw_fonticon($row['icon'], 'style="color: '. $row['color'] .';"') .' '. $row['name'], 'data-icon="'. functions::escape_html($row['icon']) .'" data-color="'. functions::escape_html($row['color']) .'"'];
    });

    if (!preg_match('#\[\]$#', $name)) {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
    }

    return form_select_dropdown($name, $options, $input, $parameters);
  }

  function form_select_page($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_page() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $iterator = function($parent_id, $level) use (&$iterator) {

      $options = [];

      if (empty($parent_id)) {
        $options[] = ['0', '['.language::translate('title_root', 'Root').']'];
      }

      $pages_query = database::query(
        "select p.id, pi.title from ". DB_TABLE_PREFIX ."pages p
        left join ". DB_TABLE_PREFIX ."pages_info pi on (pi.page_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
        where p.parent_id = '". (int)$parent_id ."'
        order by p.priority asc, pi.title asc;"
      );

      while ($page = database::fetch($pages_query)) {

        $options[] = [$page['id'], str_repeat('&nbsp;&nbsp;&nbsp;', $level) . $page['title']];

        $sub_pages_query = database::query(
          "select id from ". DB_TABLE_PREFIX ."pages
          where parent_id = '". (int)$page['id'] ."'
          limit 1;"
        );

        $sub_options = $iterator($page['id'], $level+1);

        $options = array_merge($options, $sub_options);
      }

      return $options;
    };

    $options = $iterator(0, 1);

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_payment_module($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_payment_module() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = database::query(
      "select * from ". DB_TABLE_PREFIX ."modules
      where type = 'payment'
      and status;"
    )->fetch_custom(function($module) {
      $module = new $module();
      return [$module['id'], $module['name']];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_payment_term($name, $input=true, $parameters='') {

    $options = [
      ['PIA', 'PIA &ndash; '. language::translate('title_payment_terms_pia', 'Payment In Advance')],
      ['PWO', 'PWO &ndash; '. language::translate('title_payment_terms_pwo', 'Payment With Order')],
      ['CBS', 'CBS &ndash; '. language::translate('title_payment_terms_cbs', 'Cash Before Shipment')],
      ['COD', 'COD &ndash; '. language::translate('title_payment_terms_cod', 'Cash On Delivery')],
      ['NET7', 'NET7 &ndash; '. language::translate('title_payment_terms_net7', 'Payment 7 days after invoice date')],
      ['NET10', 'NET10 &ndash; '. language::translate('title_payment_terms_net10', 'Payment 10 days after invoice date')],
      ['NET20', 'NET20 &ndash; '. language::translate('title_payment_terms_net20', 'Payment 20 days after invoice date')],
      ['NET30', 'NET30 &ndash; '. language::translate('title_payment_terms_net30', 'Payment 30 days after invoice date')],
    ];

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_product($name, $input=true, $parameters='') {

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple_products($name, $input, $parameters);
    }

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    $product_name = '('. language::translate('title_no_product', 'No Product') .')';

    if ($input) {
      $product = database::query(
        "select p.id, p.sku, pp.price, pi.name
        from ". DB_TABLE_PRODUCTS ." p
        left join ". DB_TABLE_PRODUCTS_INFO ." pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')
        left join (
          select product_id, if(`". database::input(currency::$selected['code']) ."`, `". database::input(currency::$selected['code']) ."` * ". (float)currency::$selected['value'] .", `". database::input(settings::get('store_currency_code')) ."`) as price
          from ". DB_TABLE_PREFIX ."products_prices
        ) pp on (pp.product_id = p.id)
        where p.id = ". (int)$value ."
        limit 1;"
      )->fetch();
    }

    functions::draw_lightbox();

    return implode(PHP_EOL, [
      '<div class="input-group"' . ($parameters ? ' ' . $parameters : '') . '>',
      '  <div class="form-input">',
      '    ' . form_input_hidden($name, true, $product ? 'data-sku="'. $product['sku'] .'" data-price="'. $product['price'] .'"' : ''),
      '    <span class="name" style="display: inline-block;">'. $product_name .'</span>',
      '    [<span class="id" style="display: inline-block;">'. (int)$input .'</span>]',
      '  </div>',
      '  <div style="align-self: center;">',
      '    <a href="'. document::href_ilink('b:catalog/product_picker') .'" data-toggle="lightbox" class="btn btn-default btn-sm" style="margin: .5em;">'. language::translate('title_change', 'Change') .'</a>',
      '  </div>',
      '</div>',
    ]);
  }

  function form_select_multiple_products($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_product() is deprecated.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if (!preg_match('#\[\]$#', $name)) {
      return form_select_product($name, $input, $parameters);
    }

    $options = database::query(
      "select p.*, pi.name from ". DB_TABLE_PREFIX ."products p
      left join ". DB_TABLE_PREFIX ."products_info pi on (p.id = pi.product_id and pi.language_code = '". database::input(language::$selected['code']) ."')
      order by pi.name"
    )->fetch_custom(function($product) {
      return [$product['id'], $product['name'] .' &mdash; '. $product['sku'] . ' ['. (float)$product['quantity'] .']'];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_product_stock_option($name, $product_id, $input=true, $parameters='') {

    $product = reference::product($product_id);

    $options = [];
    foreach ($product->stock_options as $stock_option) {

      $aliases = [
        '%name' => $stock_option['name'],
        '%image' => '',
      ];

      if ($product->quantity_unit) {
        $quantity_available_formatted = language::number_format($stock_option['quantity_available'], $product->quantity_unit['decimals']) .' '. $product->quantity_unit['name'];
      } else {
        $quantity_available_formatted = language::number_format($stock_option['quantity_available']);
      }

      if (!empty($stock_option['image'])) {
        $aliases['%image'] = functions::draw_thumbnail('storage://images/' . $stock_option['image'], 40, 0, 'product');
      }

      if ($stock_option['quantity_available'] > 0) {
        $aliases['%icon'] = functions::draw_fonticon('on');
        $aliases['%notice'] = language::translate('title_available', 'Available') . (settings::get('display_stock_count') ?  ' (' . $quantity_available_formatted . ')' : '');
      } else if (!empty($product->sold_out_status) && !empty($product->sold_out_status['orderable'])) {
        $aliases['%icon'] = functions::draw_fonticon('semi-off');
        $aliases['%notice'] = $product->sold_out_status['name'];
      } else {
        $aliases['%icon'] = functions::draw_fonticon('off');
        $aliases['%notice'] = language::translate('title_sold_out', 'Sold Out');
      }

      $options[] = [$stock_option['stock_option_id'], strtr('%image %name &ndash; %icon %notice', $aliases), 'data-name="'. functions::escape_html($stock_option['name']) .'" data-sku="'. functions::escape_html($stock_option['sku']) .'" data-weight="'. functions::escape_html($stock_option['weight']) .'" data-weight-unit="'. functions::escape_html($stock_option['weight_unit']) .'" data-length="'. functions::escape_html($stock_option['length']) .'" data-width="'. functions::escape_html($stock_option['width']) .'" data-height="'. functions::escape_html($stock_option['height']) .'" data-length-unit="'. functions::escape_html($stock_option['length_unit']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_select_dropdown($name, $options, $input, $parameters);
    } else {
      return form_select_dropdown($name, $options, $input, $parameters);
    }
  }

  function form_select_quantity_unit($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_quantity_unit() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);

      if ($input == '' && file_get_contents('php://input') == ''){
        $input = settings::get('default_quantity_unit_id');
      }
    }

    $options = database::query(
      "select qu.*, qui.name, qui.description from ". DB_TABLE_PREFIX ."quantity_units qu
      left join ". DB_TABLE_PREFIX ."quantity_units_info qui on (qui.quantity_unit_id = qu.id and language_code = '". database::input(language::$selected['code']) ."')
      order by qu.priority, qui.name asc;"
    )->fetch_custom(function($quantity_unit) {
      return [$quantity_unit['id'], $quantity_unit['name'], 'data-separate="'. (!empty($quantity_unit['separate']) ? 'true' : 'false') .'" data-decimals="'. (int)$quantity_unit['decimals'] .'" title="'. functions::escape_html($quantity_unit['description']) .'"'];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_shipping_module($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_shipping_module() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = database::query(
      "select * from ". DB_TABLE_PREFIX ."modules
      where type = 'shipping'
      and status;"
    )->fetch_custom(function($module) {
      $module = new $module();
      return [$module['id'], $module['name']];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_sold_out_status($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_sold_out_status() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);

      if ($input == '' && file_get_contents('php://input') == '') {
        $input = settings::get('default_sold_out_status_id');
      }
    }

    $options = database::query(
      "select sos.id, sosi.name, sosi.description from ". DB_TABLE_PREFIX ."sold_out_statuses sos
      left join ". DB_TABLE_PREFIX ."sold_out_statuses_info sosi on (sosi.sold_out_status_id = sos.id and sosi.language_code = '". database::input(language::$selected['code']) ."')
      order by sosi.name asc;"
    )->fetch_custom(function($row) {
      return [$row['id'], $row['name'], 'title="'. functions::escape_html($row['description']) .'"'];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_supplier($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_supplier() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = database::query(
      "select id, name, description from ". DB_TABLE_PREFIX ."suppliers
      order by name;"
    )->fetch_custom(function($supplier) {
      return [$supplier['id'], $supplier['name'], 'title="'. functions::escape_html($supplier['description']) .'"'];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_tax_class($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_tax_class() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
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
    )->fetch_custom(function($tax_class) {
      return [$tax_class['id'], $tax_class['name'], 'title="'. functions::escape_html($tax_class['description']) .'"'];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_template($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_template() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $folders = functions::file_search('app://frontend/templates/*', GLOB_ONLYDIR);

    $options = [];
    foreach ($folders as $folder) {
      $options[] = basename($folder);
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_timezone($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_timezone() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    $options = [];
    foreach (timezone_identifiers_list() as $timezone) {
      $timezone = explode('/', $timezone); // 0 => Continent, 1 => City

      if (in_array($timezone[0], ['Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific'])) {
        if (!empty($timezone[1])) {
          $options[] = implode('/', $timezone);
        }
      }
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_weight_unit($name, $input=true, $parameters='') {

    if (count($args = func_get_args()) > 2 && is_bool($args[2])) {
      trigger_error('Passing $multiple as 3rd parameter in form_select_weight_unit() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[3])) $parameters = $args[2];
    }

    if ($input === true) {
      $input = form_reinsert_value($name);

      if ($input == '' && file_get_contents('php://input') == '') {
        $input = settings::get('store_weight_unit');
      }
    }

    $options = [];
    foreach (weight::$units as $unit) {
      $options[] = [$unit['unit'], $unit['unit'], 'data-value="'. (float)$unit['value'] .'" data-decimals="'. (int)$unit['decimals'] .'" title="'. functions::escape_html($unit['name']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['--', '']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_volume_unit($name, $input=true, $parameters='') {

    if ($input === true) {
      $input = form_reinsert_value($name);
    }

    $options = [];
    foreach (volume::$units as $unit) {
      $options[] = [$unit['unit'], $unit['unit'], 'data-value="'. (float)$unit['value'] .'" data-decimals="'. (int)$unit['decimals'] .'" title="'. functions::escape_html($unit['name']) .'"'];
    }

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      array_unshift($options, ['--', '']);
      return form_select($name, $options, $input, $parameters);
    }
  }

  function form_select_zone($name, $country_code='', $input=true, $parameters='', $preamble='none') {

    if (preg_match('#^([A-Z]{2}|default_country_code|store_country_code)$#', $name)) {
      trigger_error('form_select_zone() no longer takes country code as 1st parameter. Instead, use form_zones($name, $country_code, $input)', E_USER_DEPRECATED);
      list($name, $country_code) = [$country_code, $name];
    }

    if (count($args = func_get_args()) > 3 && is_bool($args[3])) {
      trigger_error('Passing $multiple as 4th parameter in form_select_zone() is deprecated as instead determined by input name.', E_USER_DEPRECATED);
      if (isset($args[4])) $parameters = $args[3];
    }

	switch ($country_code) {

		case 'customer_country_code':
			$country_code = customer::$data['country_code'];
			break;

		case 'store_country_code':
			$country_code = settings::get('store_country_code');
			break;

		default:
			settings::get('default_country_code');
			break;
	}

    $options = database::query(
      "select * from ". DB_TABLE_PREFIX ."zones
      where country_code = '". database::input($country_code) ."'
      order by name asc;"
    )->fetch_custom(function($zone) {
      return [$zone['code'], $zone['name']];
    });

    if (preg_match('#\[\]$#', $name)) {
      return form_select_multiple($name, $options, $input, $parameters);
    } else {
      switch($preamble) {
        case 'all':
          array_unshift($options, ['', '-- '. language::translate('title_all_zones', 'All Zones') . ' --']);
          break;
        case 'select':
          array_unshift($options, ['', '-- '. language::translate('title_select', 'Select') . ' --']);
          break;
      }
      return form_select($name, $options, $input, $parameters);
    }
  }
