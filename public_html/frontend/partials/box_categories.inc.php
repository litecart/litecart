<?php

	/*
		This file contains PHP logic that is separated from the HTML view.
		Visual changes can be made to the file found in the template folder:
		- frontend/templates/default/partials/box_categories.inc.php
	*/

	$box_categories = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_categories.inc.php');

	$categories_cache_token = cache::token('categories', ['language']);
	if (!$categories = cache::get($categories_cache_token)) {

		$categories = f::catalog_categories_query()->fetch_all();

		cache::set($categories_cache_token, $categories);
	}

	$box_categories->snippets['categories'] = $categories;

	echo $box_categories->render();
