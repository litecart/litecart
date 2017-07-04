# Formatting and Standards


## Character Encoding

  Foreign characters should not be present in the source code. If such do, the script must be encoded with character set UTF-8 w/o Byte Order Mark (BOM).
  
  
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
  
  Indentation of comments is subtracted one level:
  
    // This is a comment
      echo 'Hello World!';
  
  Indentation of code after opening a PHP tag:
  
    <?php
      dosomething();
      ..
    ?>
  
  
## Line Breaks

  Do not use more than one empty line for separating logic.
  
  
## Naming of Variables and Elements

  Don't make up shortenings. Always use full words unless they are annoyingly long. Don't mix languages, use english only for code and comments. Don't mix lower and upper cases.
  
  Correct:

	  $customer_address
	  $customer['address']

  Incorrect:

	  $custaddr // Weird shortenings
	  $kund_adress // Foreign language
	  $customerAddress // Mixed cases
	  $customer['customer_address'] // Duplicate prefix
  
  Naming of CSS classes and IDs
  
    <div id="box-hello" class="box white">
  
  
## Encapsulating Parameters - Singe-Quotes vs. Double-Quotes

  Single quote characters should be used for PHP and javascript code unless parsing data or simply inconvenient.
  
  Use double quotes for all HTML element parameters in accordance with SGML.
  
  Correct:
    
    echo '<a href="http://www.site.com">Hello World</a>';

    database::query("select * from Table where id = 'string'");
    
    <img src="" />
    
    $('.myclass').html();

  When it can be compromised:
    
    echo "Hello y'all";
    echo "Hello $name\r\n";
    
    $('input[name="field"]').val();

  Incorrect:

    echo "Hello World!";
    
    <img src='' />
  

## Translating Variables

  When translating variables in strings we use strtr to avoid cryptic coding.

  Correct:

    $string = strtr('Text with %b %a', array('%a' => $a, '%b' => $b));

  Incorrect:

    $string = sprintf('Text with %2$s %1$s', $b, $a);


## File Naming
  
  The filename of the files must be all lowercase characters and contain no more
  than 31 characters to be Apple/Mac compatible. Word separation by underscore.
  
  Name files that are grouped with a prefix e.g:

    box_background_red.png
    box_background_green.png
    box_background_blue.png
  
  
## File Extensions
  
  Non-HTML PHP output scripts should be named by their output format extension like the following:
    
    script_name.xml.php
    script_name.rss.php
    script_name.json.php
    
  Included files should be named:
    
    script_name.inc.php
  
  
## Beginning & Ending PHP Logic

  When starting PHP logic, the tag should be written as "<?php", not in the
  short form of "<?".

  Inline PHP code:

    <?php echo "Hello World!"; ?>
  
  PHP Code Block:
  
    <?php
      echo "Hello World!";
      ...
    ?>


## PHP Variable Scope

  Do not EVER use register_globals as we use PHP Superglobals.

	$_GET['variable']
	$_POST['variable']
	$_COOKIE['variable']
	$_SESSION['variable']


## No Variable Duplication

  Unless there is a certain need to duplicate variables, no variable duplication should be used:

  Incorrect:

	  $name = $_POST['name'];
	  $trimmed_name = trim($name);

  Correct:

  	$_POST['name'] = trim($_POST['name']);


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

## Outputting Line Breaks

  Use the PHP_EOL constant for outputting line breaks in PHP.

  Correct:

    echo 'Hello World!' . PHP_EOL
       . 'This is a new row';

  Incorrect, unless it's JavaScript

    echo "Hello World!\r\nThis is a new row";


## PHP Class Variables and Methods

    class my_class {
      private $_data;
      public $data;

      private function _my_private_method() {
      }

      public function my_public_method() {
      }
    }
  
  
## Database Queries in PHP

  Database queries should be line breaked, indented, and presented in lowercases.

    database::query(
      "select * from ". DB_TABLE_NAME ."
      where id = '". (int)$integrer ."'
      ". (isset($string) ? "and string = '". database::input($string) ."'" : "") ."
      limit 1;"
    );

  Unlike displaying strings, double quote characters are wrapped around the sql query.

  
## Incoming PHP Data

  To see if a variable exists, use the following structure:

    if (isset($_POST['variable']))

  Don't just assume it exists:

    if ($_POST['variable'])
    
  Always assume incoming data is insecure by escaping the input:

    databas::query(
      "update mytable
      set column = '". database::input($_POST['variable']) ."'
      where foo = 'bar'
      limit 1;"
    );
    
    echo htmlspecialchars($_POST['variable']);
  
  
## Function Results in PHP

  General functions shall always return data, not output data to buffer.

  For example:

    function my_function($string) {
      return $string;
    }

  and not:

    function my_function($string) {
      echo $string;
    }


## Conditional Expressions

  Do not use yoda expressions.

  Incorrect:

    if (true == condition) {

  Correct:

    if (condition == true) {


## Repetitive Statements in PHP

  While loops should be written as:

    while (condition == true) {
      ....
    }

  Walking through an array should be written as:

    foreach ($array as $key => $value) {
      ....
    }

  for-loops should be written as foreach:

    foreach (array_keys($array) as $key) {
      echo $array[$key];
    }

  ...rather than:

    for ($i=0, $n=count($array); $i<$n; $i++) {
      echo $array[$i];
    }


## Code Compliance

 - PHP code must comply with PHP 5.3+ using E_STRICT.
  
 - HTML code must be compliant with HTML 5.
  
 - Style definitions must be compliant with CSS 3.
  
 - Any use of javascript must dedicate the jQuery framework.
