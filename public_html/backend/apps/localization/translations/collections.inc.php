<?php

	// Define translatable collections

	$collections = include 'app://includes/collections.inc.php';

	$collections = array_combine(array_column($collections, 'id'), $collections);

	$collections = array_filter($collections, function ($collection) {
		return !empty($collection['translatable']);
	});

	$collections = [
		...$collections,
		'modules' => [
			'id' => 'modules',
			'entity' => 'translation',
			'name' => t('title_modules', 'Modules'),
		],
		'setting_groups' => [
			'id' => 'setting_groups',
			'entity' => 'translation',
			'name' => t('title_setting_groups', 'Setting Groups'),
		],
		'settings' => [
			'id' => 'settings',
			'entity' => 'translation',
			'name' => t('title_settings', 'Settings'),
		],
	];

	uasort($collections, function($a, $b) {
		return strcasecmp($a['name'], $b['name']);
	});

	$collections = [
		'translations' => [
			'id' => 'translations',
			'name' => t('title_translations', 'Translations'),
			'entity' => 'translation',
			'identified_by' => ['code'],
			'translatable' => f::array_each(array_keys(language::$languages), fn($language) => 'text_'.$language),
		],
		...$collections,
	];

	return $collections;
