# Formatting and Standards

## Code Compliance

 - PHP code must comply with PHP 5.4+ using E_STRICT.

 - HTML code mustcomply with HTML 5.

 - Style definitions must be compliant with CSS 3.

 - Any use of javascript should dedicate the jQuery framework.


## Character Encoding

  UTF-8 without Byte Order Mark (BOM)


## Line Breaks

  We use Linux line feed (LF) \n for new lines in the source code.
  Do not use more than one empty line for separating logic.

  Incorrect:

    \r\n
    \r

  Correct:

    \n

  Do not use more than one empty line for separating logic.


## No Trailing Whitespace

  Make sure you have no trailing whitespace after your code

  Incorrect:

    <?php
    ··echo·$variable;\n·····
    ··\n
    \EOF

  Correct:

    <?php
    ··echo·$variable;\n
    \n
    \EOF

  Note: Most code editors offer a way to trim trailing whitespace upon save.


## Outputting Line Breaks

  Use the PHP_EOL constant for outputting line breaks in PHP.

  Incorrect:

    echo "<p>Hello World!<br />\r\nThis is a new row</p>";

  Correct:

    echo '<p>Hello World!</br />' . PHP_EOL
       . 'This is a new row</p>';

  For emails we use Windows style Carriage Return + Line Feed (CRLF) \r\n for new lines because the standard tells us to.

    Content-Type: text/plain\r\n
    Content-Length: 128\r\n
    \r\n
    Lorem ipsum dolor\r\n
    \r\n


## Indentation

  Indentation of logic should be 2 whitespace characters.

    Level 1
      Level 2
        Level 3
          Level 4

  TABs should not be used.

		Level 1
			Level 2
				Level 3
					Level 4

  The indentation of comments is subtracted one level, sticking out just like the bookmarks in a book:

    // This is a comment
      echo 'Hello World!';

  Code is indented after opening a PHP tag:

    <?php
      ...
    ?>

  Note: Your code editor should have the indentation format as a setting.


## PHP Tags

  When starting PHP logic, the tag should be written as "<?php", and not in the short form of "<?".

  Incorrect:

    <?=$variable?>
    <? echo $variable; ?>

  Correct:

    <?php echo $variable; ?>


## PHP Closing Tags

  We do NOT use PHP closing tags at the end of a script. This is industry standard to prevent any whitespace accidentally being sent to the output buffer.

  Incorrect:

    <?php\n
    ··...\n
    ··last_line_of_code();\n
    ?>\n <-- See this
    \EOF

  Correct:
    <?php\n
    ··...\n
    ··last_line_of_code();\n
    \EOF


## File Paths

  ALWAYS use Linux/Unix directory separator / as it also work on Windows - Windows \ does not work on Linux.

  Incorrect:

    C:\path\to\file.php

  Correct:

    /C/path/to/file


## File Naming

  The filename of the files must be all lowercase characters and contain no more
  than 31 characters to be Apple/Mac compatible. Word separation by underscore.

  Name files that are grouped with a prefix e.g:

  Incorrect:

    red_background_box.png
    green_background_box.png
    blue_background_box.png

  Correct:

    box_background_red.png
    box_background_green.png
    box_background_blue.png


## File Extensions

  Scripts that outputs something else but HTML should be named by their output format extension like the following:

    .php
    .json.php

  Included files should be named:

    .inc.php
    .json.inc.php


## Encapsulating Parameters - Singe-Quotes vs. Double-Quotes

  Single quote characters should be used for PHP and javascript code. Exceptions can be made for best convenience.

  Use double quotes for all HTML element parameters in accordance with SGML.

  Incorrect:

    $foo = "bar";

    <img src='' />

    echo "<a href='http://www.site.com'>Hello World</a>";
    echo "<a href=\"http://www.site.com\">Hello World</a>";

    database::query('select * from Table where id = \'string\'');

    $("input[name='value']").val();
    $("input[name=\"value\"]").val();

  Correct:

    $foo = 'bar';

    <img src="" />

    echo '<a href="http://www.site.com">Hello World</a>';

    database::query("select * from Table where id = 'string'");

    $('input[name="value"]').val();

  When it is being compromised for best convenience:

    echo "Hello y'all";
    echo "Hello $name\r\n";


## PHP Variable Scope

  Do not EVER use register_globals as we use PHP Superglobals.

	$_GET['variable']
	$_POST['variable']
	$_COOKIE['variable']
	$_SESSION['variable']


## Naming of Variables and Elements

  Don't make up shortenings. Always use full words unless they are annoyingly long. Don't mix languages, use english only for code and comments. Don't mix lower and upper cases.

  Incorrect:

    $custaddr // Weird shortenings
    $kund_adress // Foreign language
    $customerAddress // Mixed cases
    $customer['customer_address'] // Duplicate prefix

  Correct:

    $customer_address
    $customer['address']


## Naming of CSS IDs and Classes

  Same rules as the naming of variables but we use dash - for separating words rather than underscore _.
  We try to avoid repeatitive prefixes for subclasses.

  Incorrect:

    <div id="dummmyBox" class="box box-white">
      <div class="box-title">...</div>
      <div class="box-text">...</div>
    </div>

  Correct:

    <div id="box-dummy" class="box white">
      <div class="title">...</div>
      <div class="text">...</div>
    </div>

  How to reference a subclass:

    jQuery: $('#box-dummy .title')

    CSS: #box-dummy .title {}

  Note: Some predefined CSS classes are not following this guideline as they are Bootstrap compatible.


## No Variable Duplication

  Unless there is a certain need for duplicating variables, no variable duplication should be used:

  Incorrect:

    $name = $_POST['name'];
    $trimmed_name = trim($name);
    $trimmed_and_lowercase_name = lowercase($trimmed_name);

  Correct:

    $_POST['name'] = strtolower(trim($_POST['name']));


## PHP Arrays

  Inline arrays

    my_function(array('this', 'that'));

  Defining a variable with more than a handful of values

    $variable = array(
      'this',
      'that',
      ...
      'last', // <-- Make note of the ending coma
    );


## PHP Conditions

  Do not use yoda expressions.

  Incorrect:

    if (true === condition) {

  Correct:

    if (condition === true) {


## PHP Class Variables and Methods

    class dummy {
      private $_data;
      public $data;

      private function _private_method() {
      }

      public function public_method() {
      }
    }


## PHP Function Results

  General functions shall always return data, not output data to the buffer.

  Incorrect:

    function my_function($string) {
      echo $string;
    }

  Correct:

    function my_function($string) {
      return $string;
    }

  Local functions that are just used in a single local script file should be anonymous functions:

    $iterator = function() {
      ...
    };

    $variable = $iterator();


## Repetitive Statements in PHP

  Try to avoid this at all costs:

    for ($i=0, $n=count($array); $i<$n; $i++) {
      $array[$i] = 'value';
    }

  Walking through an array:

    foreach ($array as $key => $item) {
      ....
    }

  Walking through an array and back reference the source variable:

    foreach ($array as $key => &$item) {
      $item = 'value';
    }


## Matryoshka Dolls

  Avoid conditional conditions inside loops.

  Incorrect:

    foreach ($array => $node) {
      if ($node['first'] == 'a') {
        if ($node['second'] == 'b') {
          if ($node['third'] == 'c') {
            return true;
          }
        }
      }
    }

  Correct:

    foreach ($array => $node) {
      if ($node['first'] != 'a') continue;
      if ($node['second'] != 'b') continue;
      if ($node['third'] != 'c') continue;
      return true;
    }


## Translating String Content

  When translating variables in strings we use strtr to avoid cryptic coding.

  Incorrect:

    $string = sprintf('Text with %2$s %1$s', $b, $a);
    $string = str_replace(array('%a', %b), array($a, $b), 'Text with %a %b');

  Correct:

    $string = strtr('Text with %b %a', array(
      '%a' => $a,
      '%b' => $b,
    ));


## Database Queries in PHP

  Database queries should be line breaked, indented, and presented in lowercases.

    database::query(
      "select * from ". DB_TABLE_NAME ."
      where id = '". (int)$integrer ."'
      ". (isset($string) ? "and string = '". database::input($string) ."'" : "") ."
      limit 1;"
    );

  Unlike displaying strings, double quote characters are wrapped around the sql query.


## Handling User Input Data

  Don't just assume a variable exists with a value:

    if ($_POST['variable'])

  See if it exists:

    if (!empty($_POST['variable']))
    if (isset($_POST['variable']) && $_POST['variable'] == 'value')

  Always assume incoming data is insecure by escaping the input:

    databas::query(
      "update mytable
      set number = ". (int)$_POST['number'] .",
        string = '". database::input($_POST['string']) ."',
        date = '". date('Y-m-d', strtotime($_POST['string'])) ."',
      where this = 'that'
      limit 1;"
    );

    echo htmlspecialchars($_POST['variable']);
