# 1. File Extensions

The filename of the files must be all lowercases characters and contain
no more than 31 characters to be Apple/Mac compatible. Word separation by underscore.

Non-HTML PHP output scripts should be named by their output format extension like the following:

	scriptname.xml.php
	scriptname.rss.php
	scriptname.json.php

Included files should be named:

	scriptname.inc.php


# 2. Character Encoding

Foreign characters should not be present in the source code. If such do, the script must be compatible with UTF-8 w/o BOM character set.

# 3. Indentation

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


# 4. Line Breaks

  Maximum one empty line for separating logic.


# 5. Beginning & Ending PHP Logic

When starting PHP logic, the tag should be written as "<?php", not in the
short form of "<?".

A valid example:

	<?php echo "Hello World!"; ?>

Wrong

    <?=$greeting?>


# 6. Variable Scope

Do not ever use register_globals as we use superglobals.

	$_GET['variable']
	$_POST['variable']
	$_COOKIE['variable']
	$_SESSION['variable']


# 7. No Variable Duplication

Unless there is a certain need to duplicate variables, no variable duplication should be used:

Incorrect:

	  $name = $_POST['name'];
	  $trimmed_name = trim($name);

Correct:

  	$_POST['name'] = trim($_POST['name']);


# 8. Variable Naming

Don't make up shortenings. Always use full words unless they are annoyingly long. Don't mix languages, use english only for code and comments. Don't mix lower and upper cases.

Incorrect:

	  $custaddr // weird shortenings
	  $kund_adress // foreign language
	  $customerAddress // mixed cases
	  $customer['customer_address'] // duplicate prefix

Correct:

	  $customer_address
	  $customer['address']


# 9. Displaying Strings

Strings or values should be displayed as:

	<?php echo $variable; ?>

The following variants should be avoided:

	<?php print $variable; ?>
	
	<?=$variable;?>


# 10. Singe-Quotes vs. Double-Quotes

Single quote characters should be used for displaying strings unless it's inconvenient.

For example:

    echo 'Hello World';
	
    echo '<a href="http://www.site.com">Hello World</a>';

	echo "Hello y'all";

	echo "Hello $name\r\n";

	database::query("select * from Table where id = 'string'");


# 11. Outputting Line Breaks

Use the PHP_EOL constant for outputting line breaks.

Right:

	echo 'Hello World!' . PHP_EOL
       . 'This is a new row';

Wrong

	echo "Hello World!\r\nThis is a new row";


# 12. Class Variables & Methods

  private $_data;
  public $data;

  private function _my_private_method() {
	}

  public function my_public_method() {
	}


# 13. Database Queries

Database queries should be structured as:

	$system->database->query(
	  "select * from ". DB_TABLE_NAME ."
	  where id = '". (int)$integrer ."'
	  ". (isset($string) ? "and string = '". $system->database->input($string) ."'" : "") ."
	  limit 1;"
	);

Unlike displaying strings, double quote characters are wrapped around the sql query.


# 14. Function Output

General functions shall always return data, not output to browser via echo.

For example:

	function my_function($string) {
	  return $string;
	}

and not:

	function my_function($string) {
	  echo $string;
	}


# 15. Expressions

Do not use yoda expressions.

Incorrect:

	if (true == condition) {

Correct:

	if (condition == true) {


# 16. Form Data Checking

To see if a variable exists, use the following structure:

	if (isset($_POST['variable']))

Don't just assume it exists:

	if ($_POST['variable'])


# 17. Repetitive Statements

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


# 18. MySQL Syntax

  MySQL code should be line breaked, indented, and presented in lowercases.

    $category_query = database::query(
      "select * from ". DB_TABLE_CATEGORIES ."
      where status = 1;"
    );


# 19. Error level

	All code must comply with PHP error level E_STRICT.


# 20. HTML 5

  HTML code must be compliant with HTML 5.

  Parameter encapsulator is " not '

  Right

    <img src="" />

  Wrong

    <img src='' />


# 19. CSS

	All style definitions must be compliant with CSS 3.


# 21. JavaScript -> jQuery

   For any use of javascript use the jQuery framework.

