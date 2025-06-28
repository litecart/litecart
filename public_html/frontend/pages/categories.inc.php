<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/categories.inc.php
	 */

	document::$title[] = t('categories:head_title', 'Categories');
	document::$description = t('categories:meta_description', '');

	breadcrumbs::add(t('title_categories', 'Categories'), document::ilink('categories'));

	$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/categories.inc.php');
	echo $_page->render();
