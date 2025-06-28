<?php

	/*!
	 * This file contains PHP logic that is separated from the HTML view.
	 * Visual changes can be made to the file found in the template folder:
	 *
	 *   ~/frontend/templates/default/pages/information.inc.php
	 */

	try {

		if (empty($_GET['page_id'])) {
			throw new Exception('Missing page_id', 400);
		}

		$page = reference::page($_GET['page_id']);

		if (empty($page->id)) {
			throw new Exception('Page does not exist', 410);
		}

		if (empty($page->status)) {
			throw new Exception('Page is disabled', 404);
		}

		document::$title[] = $page->head_title ?: $page->title;
		document::$description = $page->meta_description;

		breadcrumbs::add(t('title_information', 'Information'));
		foreach (array_slice($page->path, 0, -1, true) as $crumb) {
			breadcrumbs::add($crumb->title, document::ilink('information', ['page_id' => $crumb->id]));
		}
		breadcrumbs::add($page->title, document::ilink('information', ['page_id' => $page->id]));

		$_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/information.inc.php');

		$_page->snippets = [
			'title' => $page->title,
			'content' => $page->content,
		];

		echo $_page->render();

	} catch (Exception $e) {
		http_response_code($e->getCode() ?: 500);
		include 'app://frontend/pages/error_document.inc.php';
		return;
	}
