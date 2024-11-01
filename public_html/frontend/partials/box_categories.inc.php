<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/partials/box_categories.inc.php
	 */

	$box_categories = new ent_view('app://frontend/templates/'.settings::get('template').'/partials/box_categories.inc.php');

	$box_categories_cache_token = cache::token('box_categories', ['language']);
	if (!$box_categories->snippets['categories'] = cache::get($box_categories_cache_token)) {

		$box_categories->snippets['categories'] = functions::catalog_categories_query()->fetch_all();

		cache::set($box_categories_cache_token, $box_categories->snippets['categories']);
	}

	echo $box_categories->render();