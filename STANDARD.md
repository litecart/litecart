# Syntax Formatting and Code Standards

## Code Compliance

 - PHP code must comply with minimum PHP 5.4+ E_STRICT.

 - HTML code must comply with HTML 5.

 - Style definitions must be compliant with CSS 3.

 - Any use of JavaScript should honour the jQuery framework.


## Character Encoding

  UTF-8 without Byte Order Mark (BOM)


## PHP File Paths

  ALWAYS use Linux/Unix directory separator slash (/) as it also work on Mac and Windows.
  Windows backslash (\) does not work on Mac or Linux.

  Incorrect:

    C:\path\to\file.php

  Correct:

    /C/path/to/file
    C:/path/to/file


## File Naming

  The filename of the files should be all lowercase characters with underscore (_) for
  word separation. No more than 31 characters to be Apple/Mac compatible.

  When files can be grouped. Attempt to give them the same preceeding names.

  Incorrect:

    red-background-box.png
    greenBoxBackground.png
    blue_background_box.png

  Correct:

    box_background_red.png
    box_background_green.png
    box_background_blue.png


## File Extensions

  Scripts that output something other than HTML should be named by their output format extension like the following:

    myjsonoutput.json.php

  Included files should be named .inc.php:

    .php  >>  .inc.php


## Line Breaks in Code

  Use no more than one empty line when line separating logic.


## Outputting Line Breaks

  Use the PHP_EOL constant for outputting line breaks in PHP.

  Incorrect:

    echo "<p>Hello World!<br />\r\nThis is a new row</p>";

  Correct:

    echo '<p>Hello World!</br />' . PHP_EOL
       . 'This is a new row</p>';

  For emails and HTTP headers we always use Windows style Carriage Return + Line Feed (CRLF) \r\n
  for new lines because the standard tells us to.

    Content-Type: text/plain\r\n
    Content-Length: 128\r\n
    \r\n
    Lorem ipsum dolor\r\n
    \r\n


## No Trailing Whitespace

  Make sure you have no trailing whitespace after your code

  Incorrect:

    <?php
    ··echo·$variable;·····\n
    ··\n
    \n
    \EOF

  Correct:

    <?php
    ··echo·$variable;\n
    \n
    \EOF

  Note: Most code editors offer a way to trim trailing whitespace upon save.
  This is also covered by .editorconfig.


## Indentation

  Committed code should use an indentation of 2 blankspace characters. This is supported by .editorconfig.
  Make sure your code editor has enabled support for .editorconfig. See https://editorconfig.org/

  Incorrect (using TABs):

    Level 1
    	Level 2
    		Level 3
    			Level 4

  Correct (using spaces):

    Level 1
      Level 2
        Level 3
          Level 4

  The indentation of comments is subtracted one level, sticking out like bookmarks in a book:

    // This is a comment
      echo 'Hello World!';

  Code is immediately indented after opening a PHP tag:

    <?php
      ...
    ?>


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


## Encapsulating Parameters - Singe-Quotes vs. Double-Quotes

  Single quote characters should be used for PHP and JavaScript code. Exceptions can be made for best convenience.

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


## Escaping HTML Parameters

  HTML Parameters that contains special characters or user data must be escaped.

  Incorrect:

    <img src="..." alt="<?php echo $title; ?>" />

  Correct:

    <img src="..." alt="<?php echo htmlspecialchars($title); ?>" />


## PHP Variable Scope

  Do not EVER enable register_globals in your PHP configuration as we use PHP Superglobals to access user data.

    $_GET['variable']
    $_POST['variable']
    $_COOKIE['variable']
    $_SESSION['variable']


## Naming of Variables and Elements

  Simply use PECL styled naming with lowercases and underscores. Don't use CAPS, CamelCase or camelCase.
  Don't make up abbreviations. Always use full words unless they are annoyingly long. Don't mix languages, use English only for code and comments.

  Incorrect:

    $CUSTOMER_ADDRESS // Yelling
    $custaddr // Weird shortenings
    $kund_adress // Foreign language
    $customerStreetAddress // Mixed cases
    $customer['customer_address1'] // Duplicate prefix
    $customer_shipping_street_address_name // Annoyingly long

  Correct:

    $address1
    $customer['address1']


## No Variable Duplication

  No variable duplication should be used. Unless there is a certain need for duplicating variables.
  One common case for variable duplication is during santizing.

  Incorrect:

    $name = $_POST['name'];
    $trimmed_name = trim($name);
    $trimmed_and_lowercase_name = lowercase($trimmed_name);

  Correct:

    $_POST['name'] = strtolower(trim($_POST['name']));  // We most likely will not ever use the unsanitized data


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

  How to reference a class:

    jQuery: $('#box-dummy .title')
    jQuery: $('.box.white')

    CSS: #box-dummy .title {}
    CSS: .box.white {}

  Note: Some predefined CSS classes are not following this guideline as they are third party components or compatible with third party components.


## PHP Arrays

  Inline arrays

    $variable = my_function(param, ['this', 'that']);

  Defining a variable with more than a handful of values

    $variable = [
      'this',
      'that',
      ...
      'last', // <-- Make note of the ending comma
    ];


## Code Brackets

  Do not start new lines for opening brackets.

  Incorrect:

    if (condition)
    {
      ...
    }
    else
    {
      ...
    }

  Correct:

    if (condition) {
      ...
    } else {
      ...
    }


## PHP Conditions

  Do not use if/endif or yoda expressions.

  Incorrect:

    if (condition):
      ...
    endif;

    if ('happy' == $my_mood) {

  Correct:

    if (condition) {
      ...
    }

    if ($my_mood == 'happy') {


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

  General functions should always return data, not output data to the buffer.

  Incorrect:

    function my_function($string) {
      echo $string;
    }

  Correct:

    function my_function($string) {
      return $string;
    }

  Functions in a local variable scope that are just used inside the scope should be anonymous functions:

    $my_function = function() {
      ...
    };

    $variable = $my_function();


## Repetitive Statements in PHP

  Try to avoid this at all costs:

    for ($i=0, $n=count($array); $i<$n; $i++) {
      $array[$i] = 'value';
    }

  Walking through an array:

    foreach ($array as $key => $item) {
      ....
    }

  Walking through an array and overwriting a source variable:

    foreach ($array as $key => $node) {
      $node[$key] = 'newvalue';
    }


## Iterators

  Preferably use anonymous functions for iterators unless they are also used elsewhere in the platform.

    $iterator = function($input) use (&$iterator)  {
      $iterator();
    };


## No Matryoshka Dolls

  Avoid conditional conditions inside loops.

  Incorrect:

    foreach ($array => $node) {
      if ($node['first'] == 'a') {
        if ($node['second'] == 'b') {
          if ($node['third'] == 'c') {
            // Do some stuff
          }
        }
      }
    }

  Correct:

    foreach ($array => $node) {
      if ($node['first'] != 'a') continue;
      if ($node['second'] != 'b') continue;
      if ($node['third'] != 'c') continue;
      // Do some stuff
    }


## Translating String Content

  When translating variables in strings we use strtr to avoid cryptic coding.

  Incorrect:

    $string = sprintf('Text with %2$s %1$s', $b, $a);
    $string = str_replace(['%a', %b], [$a, $b], 'Text with %a %b');

  Correct:

    $string = strtr('Text with %b %a', [
      '%a' => $a,
      '%b' => $b,
    ]);


## Database Queries in PHP

  Database queries should be line breaked, indented, and presented in lowercase.

    $query = database::query(
      "select * from ". DB_TABLE_NAME ."
      where id = '". (int)$integrer ."'
      ". (isset($string) ? "and string = '". database::input($string) ."'" : "") ."
      limit 1;"
    );

  Unlike when displaying strings, double quote characters are use to wrap SQL queries.


## Passing User Input Data to the Database

  Don't just assume a variable exists with a value:

    if ($_POST['variable'])

  See if it exists:

    if (!empty($_POST['variable']))
    if (isset($_POST['variable']) && $_POST['variable'] == 'value')

  Always assume incoming data is insecure by escaping the input:

    database::query(
      "update mytable
      set number = ". (int)$_POST['number'] .",
        string = '". database::input($_POST['string']) ."',
        date = '". date('Y-m-d', strtotime($_POST['string'])) ."',
      where this = 'that'
      limit 1;"
    );

    echo '<input value="<?php echo htmlspecialchars($_POST['variable']); ?>" />


## No Sloppy HTML

  No sloppy coding for single HTML tags. We use the strict standard:

  Incorrect:

        <img src="">
        <br>

  Correct:

        <img src="" />
        <br />
