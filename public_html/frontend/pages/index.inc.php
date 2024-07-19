<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/index.inc.php
	 */

	document::$title = [language::translate('index:head_title', 'Online Store'), settings::get('store_name')];
	document::$description = language::translate('index:meta_description', '');

	document::$head_tags['canonical'] = '<link rel="canonical" href="'. document::href_ilink('') .'">';

	document::$head_tags['opengraph'] = implode(PHP_EOL, [
		'<meta property="og:url" content="'. document::href_ilink('') .'">',
		'<meta property="og:type" content="website">',
		'<meta property="og:image" content="'. document::href_rlink('storage://images/logotype.png') .'">',
	]);

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/index.inc.php');

	echo $_page->render();
