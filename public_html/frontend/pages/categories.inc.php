<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/categories.inc.php
	 */

	document::$title[] = language::translate('categories:head_title', 'Categories');
	document::$description = language::translate('categories:meta_description', '');

	breadcrumbs::add(language::translate('title_categories', 'Categories'));

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/categories.inc.php');
	echo $_page->render();
