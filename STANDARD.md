# Syntax Formatting and Code Standards

## Code Compliance

- PHP code must comply with modern PHP standards no earlier than 8.0+ (recommended 8.3+).
- HTML code must comply with HTML 5. Self-closing tags required (`<br />`, `<img ... />`).
- Style definitions must be compliant with CSS 3. All color values must use CSS custom properties defined in `variables.less`.
- Any use of JavaScript should honour the jQuery framework.


## Character Encoding

UTF-8 without Byte Order Mark (BOM)


## PHP File Paths

ALWAYS use Linux directory separator slash (/) as it works universally on all OS (Linux, Mac and Windows).
Windows backslash (\) does not work on Mac or Linux.

Incorrect:

- C:\path\to\file.php

Correct:

- /C/path/to/file
- C:/path/to/file


## Trailing Directory Separators

Directories should be ended with the directory separator. This communicates if the intended path is a file or directoy.

- /path/name/  # Oh it's a directory
- /path/name   # Ah it's a file


## File Naming

The filename of the files should be all lowercase characters with underscore (_) for
word separation. No more than 31 characters to be Apple/Mac compatible.

When files can be grouped. Attempt to give them the same preceeding names.

Incorrect:

	- red-background-box.png
	- greenBoxBackground.png
	- blue_background_box.png

Correct:

	- box_background_red.png
	- box_background_green.png
	- box_background_blue.png


## File Extensions

Scripts that output something other than HTML should be named by their output format extension like the following:

	- myoutput.json.php

Included files should be named .inc.php:

	- .php  >>  .inc.php


## Line Breaks in Code

Use no more than one empty line when line separating logic.


## Outputting Line Breaks

Use the PHP_EOL constant for outputting line breaks in PHP.

Incorrect:

```php
	echo "<p>Hello World!<br>\r\nThis is a new row</p>\r\n<p>And here is more</p>";
```
Correct:

```php
	echo implode(PHP_EOL, [
		'<p>Hello World!<br>',
		'This is a new row</p>',
		'<p>And here is more</p>',
	]);
```

For emails and HTTP headers we always use Windows style Carriage Return + Line Feed (CRLF) \r\n
for new lines because the standard tells us to.

```tex
	Content-Type: text/plain\r\n
	Content-Length: 128\r\n
	\r\n
	Lorem ipsum dolor\r\n
	\r\n
```


## No Trailing Whitespace

Make sure you have no trailing whitespace after your code

Incorrect:

```php
	<?php
	··echo·$variable;·····\n
	··\n
	\n
	\EOF
```

Correct:

```php
	<?php
	··echo·$variable;\n
	\n
	\EOF
```

Note: Most code editors offer a way to trim trailing whitespace upon save.
This is also covered by .editorconfig.


## Indentation

Indentations are made using tabs with one tab character per depth level.
You can set the size of a tab character to your preference in the [.editorconfig](https://editorconfig.org/) file.

Incorrect (using multiple spaces):

```
Level 1
····Level 2
········Level 3
············Level 4
```
Correct (using TABs):

```
Level 1
→ Level 2
→ → Level 3
→ → → Level 4
```

Code is immediately indented after opening a PHP or script tag:

```php
<?php
	...
?>
```

```html
<script>
	...
<script/>
```


## Code Commenting

Comments should have the same indentation as the code:

```php
	// Title comment
	echo 'Hello World!';
```

Inline side notes are made at the end of the line:

```php
	$array = [
		'foo' => 'bar', // Side note
	];
```


## Block Comments

Avoid legacy block comments as they break EditorConfig linting:

```php
	/**
		* ...
		*/
```

Instead to minimal style:

```php
	/*
		...
	*/
```


## PHP Tags

When starting PHP logic, the tag should be written as "<?php", and not in the short form of "<?".

Incorrect:

```php
	<?=$variable?>
	<? echo $variable; ?>
```

Correct:

```php
	<?php echo $variable; ?>
```


## PHP Closing Tags

We do NOT use PHP closing tags at the end of a script. This is industry standard to prevent any whitespace accidentally being sent to the output buffer.

Incorrect:

```php
<?php\n
	...code...\n
?>\n <-- See this
\EOF
```

Correct:

```php
<?php\n
	...code...\n
\EOF
```


## Encapsulating Parameters - Singe-Quotes vs. Double-Quotes

Single quote characters should be used for PHP and JavaScript code. Exceptions can be made for best convenience.

Use double quotes for all HTML element parameters in accordance with SGML.

Incorrect:

```php
	$foo = "bar";

	<img src=''>

	echo "<a href='http://www.site.com'>Hello World</a>";
	echo "<a href=\"http://www.site.com\">Hello World</a>";

	database::query('select * from Table where id = \'string\'');

	$("input[name='value']").val();
	$("input[name=\"value\"]").val();
```

Correct:

```php
	$foo = 'bar';

	<img src="">

	echo '<a href="http://www.site.com">Hello World</a>';

	database::query(
		"select * from `tablename`
			where `column` = 'string';"
	);

	$('input[name="value"]').val();
```

When it is being compromised for best convenience:

```php
	echo "Hey y'all";
	echo "Hello $name\r\n";
```


## Escaping HTML Parameters

HTML Parameters that contains special characters or user data must be escaped.

Incorrect:

```html
	<img src="..." alt="<?php echo $title; ?>">
```

Correct:

```html
	<img src="..." alt="<?php echo f::escape_attr($title); ?>">
```

## PHP Variable Scope

Do not EVER enable register_globals in your PHP configuration as we use PHP Superglobals to access user data.

	$_GET['variable']
	$_POST['variable']
	$_COOKIE['variable']
	$_SESSION['variable']


## Naming of Variables and Elements

Name your variables and elements using lowercases and underscores (a.k.a. snake_case). Don't use CAPS, camelCase, or PascalCase.
Don't make up abbreviations. Always use full words unless they are annoyingly long. Don't mix languages, use English only for code and comments.

Incorrect:

```php
	$CUSTOMER_ADDRESS // YELLING!
	$custaddr // Weird shortenings
	$kundadress // Foreign language
	$customerStreetAddress // Mixed cases
	$customer['customer_address1'] // Repetitive naming
	$customer_shipping_street_address_name // Annoyingly long
```

Correct:

```php
	$address1
	$customer['address1']
```


## No Variable Duplication

No variable duplication. Unless there is a certain need for duplicating variables.
A common case for variable duplication is during santizing.

Incorrect:

```php
	$name = $_POST['name'];
	$trimmed_name = trim($name);
	$trimmed_and_lowercase_name = lowercase($trimmed_name);
```

Correct:

```php
	$_POST['name'] = strtolower(trim($_POST['name']));  // We most likely will not ever use the unsanitized data
```


## Avoid One-Time Variables

Creating variables for one-time use should be avoided (unless it serves good purpose).

Incorrect:

```php
	$array = ['foo', 'bar'];

	foreach ($array as $item) {
		echo $item;
	}
```
Correct:

```php
	foreach ([
		'foo',
		'bar',
	] as $item) {
		echo $item;
	}
```


## Naming of CSS IDs and Classes

Same rules as the naming of variables but we use dash - for separating words rather than underscore _.
We try to avoid repeatitive prefixes for subclasses.

Incorrect:

```html
	<div id="dummmyBox" class="white-box">
		<div class="box-title">...</div>
		<div class="box-text">...</div>
	</div>
```
Correct:

```html
	<div id="box-dummy" class="box box-white">
		<div class="box-title">...</div>
		<div class="box-body">...</div>
	</div>
```

How to reference a class:

Javascript/jQuery:

```js
	$('#box-dummy .title')
	$('.box.white')
```

CSS:

```css
	#box-dummy .title {}
	.box.white {}
```

Note: Some predefined CSS classes are not following this guideline as they are third party components or compatible with third party components.


## PHP Arrays

Inline arrays

```php
	$variable = my_function(param, ['this', 'that']);
```

Defining a variable with more than a handful of values

```php
	$variable = [
		'this',
		'that',
		...
		'last', // <-- Make note of the ending comma
	];
```


## Code Brackets

Do not start new lines for opening brackets.

Incorrect:

```php
	if (condition)
	{
		...
	}
	else
	{
		...
	}
```

Correct:

```php
	if (condition) {
		...
	} else {
		...
	}
```

Edge-Case:

```
	if (condition) {
		...
	}

	else {
		...
	}
```


## PHP Conditions

Do not use if/endif or yoda expressions.

Incorrect:

```php
	if ('orange' == $fruit):
		...
	endif;
```

Correct:

```php
	if ($fruit == 'orange') {
		...
	}
```


## PHP Class Variables and Methods

```php
	class dummy {
		private $_data;
		public $data;

		private function _private_method() {
		}

		public function public_method() {
		}
	}
```


## PHP Function Results

General functions should always return data, not output data to the buffer.

Incorrect:

```php
	function my_function($string) {
		echo $string;
	}
```

Correct:

```php
	function my_function($string) {
		return $string;
	}
```

Functions in a local variable scope that are just used inside the scope should be anonymous functions:

```php
	$my_function = function() {
		...
	};

	$variable = $my_function();
```


## For Iterating

Try to avoid this at all costs:

```php
	for ($i=0, $n=count($array); $i<$n; $i++) {
		$array[$i] = '...';
	}
```

Instead do this:

```php
	foreach ($array as $key => $node) {
		$array[$key] = '...';
	}
```

When it's okay to use for-iterating:

```php
	for ($ts=time(); $ts < strtotime('+1 months'); $ts=strtotime('+1 days', $ts)) {
		...
	}
```


## Anonymous Functions

Use anonymous functions when they are not needed elsewhere in the platform.

```php
	$tempfunc = function($var) use (&$tempfunc)  {
		$tempfunc();
	};
```


## Translating String Content

When translating variables in strings we use strtr to avoid cryptic coding.

Incorrect:

```php
	$string = sprintf('Text with %1$s %2$s', $a, $b);
	$string = str_replace(['%a', %b], [$a, $b], 'Text with %a %b');
```

Correct:

```php
	$string = strtr('Text with {a} {b}', [
		'{a}' => $a,
		'{b}' => $b,
	]);
```


## Database Queries in PHP

Database queries should be line breaked, indented, and presented in lowercase.

```php
	database::query(
		"select * from ". DB_TABLE_NAME ."
		where id = ". (int)$integrer ."
		". (isset($string) ? "and string = '". database::input($string) ."'" : "") ."
		limit 1;"
	);
```

Use double quote characters to wrap a SQL query string.


## Processing User Input

Don't just assume a variable exists with a value:

```php
	if ($_POST['variable'])
```

See if it exists:

```php
	if (!empty($_POST['variable']))
	if (isset($_POST['variable']) && $_POST['variable'] == 'value')
```

Always assume user data is insecure by escaping the input:

```php
	database::query(
		"update mytable
		set number = ". (int)$_POST['number'] .",
			string = '". database::input($_POST['string']) ."',
			date = '". date('Y-m-d', strtotime($_POST['string'])) ."',
		where foo like '%". database::input_like($foo) ."%'
		limit 1;"
	);

	echo '<input value="<?php echo htmlspecialchars($_POST['variable']); ?>">
```


## Autoloader Conventions

Class prefixes determine the autoloader directory:

```
	nod_*    nodes/               Static singletons (core services)
	ent_*    entities/            Data objects (product, order, customer, etc.)
	ref_*    references/          Read-only factory models with lazy properties
	abs_*    abstracts/           Base classes
	cm_*     modules/customer/    Customer modules
	om_*     modules/order/       Order modules
	ot_*     modules/order_total/ Order total modules
	pm_*     modules/payment/     Payment modules
	sm_*     modules/shipping/    Shipping modules
	job_*    modules/jobs/        Background job modules
	mod_*    modules/             Generic modules
	url_*    routes/              Route handlers
	stream_* streams/             StreamWrappers
	wrap_*   wrappers/            Service layers / API clients
```
All autoloaded files use `.inc.php` extension and pass through the vMod system.


## Stream Wrappers

Use vMod FS stream wrappers for file paths within the application:

	app://       Application files (templates, includes, assets)
	storage://   User storage (images, config, cache)

Incorrect:

	include FS_DIR_APP . 'includes/templates/default/layouts/default.inc.php';

Correct:

	include 'app://frontend/templates/default/layouts/default.inc.php';


## CSS Referencing

Example:

```html
<style>
#named-element { --text-color: white; --text-color-hover: red; }
.generic { padding: 1 rem }
.generic-detail { color: var(--text-color) }
.generic-detail:hover { color: var(--text-color--hover) }
<style>

<div id="named-element" class="generic generic-detail">
</div>
```

## CSS Custom Properties

All coloring in CSS/LESS/SCSS files should use CSS variables defined in `variables.scss`.

Incorrect:

```css
.element {
	color: #c00;
	background: rgba(0, 0, 0, 0.5);
}
```

Correct:

```css
.element {
	color: var(--sale-price-tex-color);
	background: var(--overlay-dark);
}
```
Exceptions: `transparent`, `inherit`, `currentColor`.

