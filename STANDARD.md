File Format
-----------

The filename of the files must be all lowercases characters and contain
no more than 31 characters to be Apple/Mac compatible. Word separation by underscore.

Non-HTML PHP output scripts should be named like the following:

	scriptname.xml.php
	scriptname.rss.php
	scriptname.json.php

Included files should be named:

	scriptname.inc.php

Character Encoding
------------------
Foreign characters should not be present in source code but instead database translations. If they do, the script must be saved in UTF-8 w/o BOM format which is the recommended output encoding.

Indentation
-----------

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

Starting and Ending PHP Logic
-----------------------------

When starting PHP logic, the tag should be written as "<?php", not in the
short form of "<?".

A valid example:

	<?php
	  echo "Hello World!";
	?>


Variable Scope
--------------

Do not ever use registered globals as we use superglobals instead.

	$_GET['variable']
	$_POST['variable']
	$_COOKIE['variable']
	$_SESSION['variable']


No Variable Duplication
-----------------------

Incorrect:

	  $name = $_POST['name'];
	  $trimmed_name = trim($name);

Correct:

  	$_POST['name'] = trim($_POST['name']);


Variable Naming
---------------
Don't make up shortenings. Always use full words unless they are annoyingly long.
Don't mix languages, english only.
Don't mix lower and upper cases.

Incorrect:

	  $custaddr // weird shortenings
	  $kund_adress // foreign language
	  $customerAddress // mixed cases
	  $customer['customer_address'] // duplicate prefix

Correct:

	  $customer_address
	  $customer['address']


Displaying Strings
------------------

Strings or values should be displayed as:

	<?php echo $variable; ?>

The following styles should be avoided:

	<?php print $variable; ?>
	
	<?=$variable;?>


Singe-Quotes vs Double-Quotes
-----------------------------

Single quote characters should be used for displaying strings unless it's inconvenient.

For example:

	echo '<a href="http://www.site.com">Hello World</a>';

	echo "Hello $name\r\n";

	query("select * from Table where id = 'value'");

Outputting Line Breaks
----------------------

Use the PHP_EOL constant for outputting line breaks.

Right:

	echo 'Hello World!' . PHP_EOL
       . 'This is a new row';


Wrong

	echo "Hello World!\r\nThis is a new row";


Class Variables & Methods
---------------

	private $_data;
	public $data;

    private function _my_private_method() {
	}

    public function my_public_method() {
	}


Database Queries
----------------

Database queries should be structured as:

	$system->database->query(
	  "select * from ". DB_TABLE_NAME ."
	  where id = '". (int)$integrer ."'
	  ". (isset($condition) ? "and condition= '". $system->database->input($condition) ."'" : "") ."
	  limit 1;"
	);

Unlike displaying strings, double quote characters are wrapped around the sql query.


Function Output
---------------

All custom functions should return strings; not directly via echo().

For example:

	function tep_my_function($string) {
	  return $string;
	}

and not:

	function tep_my_function($string) {
	  echo $string;
	}


Expressions
--------------------

Do not use yoda expressions.

Incorrect:

	if (true == condition) {

Correct:

	if (condition == true) {

Switch-Case statements should be written as:

	switch ($value) {
	  case 'a':
	    ....
	    break;
	  case 'b':
	    ....
	    break;
	  default:
	    ....
	    break;
	}


Form Data Checking
------------------

To see if a variable exists, use the following structure:

	if (isset($_POST['variable']))

Don't just assume it exists:

	if ($_POST['variable'])


Repetitive Statements
---------------------

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


Error level
-----------

	E_STRICT
