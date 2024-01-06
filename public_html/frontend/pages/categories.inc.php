<?php
  document::$title[] = language::translate('categories:head_title', 'Categories');
  document::$description = language::translate('categories:meta_description', '');

  breadcrumbs::add(language::translate('title_categories', 'Categories'));

  $_page = new ent_view('app://frontend/templates/'.settings::get('template').'/pages/categories.inc.php');
  echo $_page->render();
