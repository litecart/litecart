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

	$categories_cache_token = cache::token('categories', ['language']);
	if (!$_page->snippets['categories'] = cache::get($categories_cache_token)) {

		$_page->snippets['categories'] = f::catalog_categories_query()->fetch_all();

		cache::set($categories_cache_token, $_page->snippets['categories']);
	}

	// Headless requests
	if (!empty($_SERVER['HTTP_ACCEPT']) && preg_match('#^application/json#', $_SERVER['HTTP_ACCEPT'])) {
		header('Content-Type: application/json;charset='. mb_http_output());
		echo f::format_json($_page->snippets);
		exit;
	}

	echo $_page->render();
