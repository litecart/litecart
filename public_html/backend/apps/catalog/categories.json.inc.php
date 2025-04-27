<?php

	try {

		if (empty($_GET['parent_id'])) {
			$_GET['parent_id'] = 0;
		}

		if (empty($_GET['language_code'])) {
			$_GET['language_code'] = language::$selected['code'];
		}

		if (!empty($_GET['query'])) {
			$sql_find = [
				"c.id = '". database::input($_GET['query']) ."'",
				"json_value(c.name, '$.". database::input(language::$selected['code']) ."') like '%". database::input($_GET['query']) ."%'",
			];
		}

		$category = reference::category($_GET['parent_id']);

		$json = [
			'status' => 'ok',
			'id' => fallback($_GET['parent_id'], 0),
			'name' => !empty($_GET['parent_id']) ? $category->name : '['. language::translate('title_root', 'Root') .']',
			'parent' => [
				'id' => $category->parent ? $category->parent->id : 0,
				'name' => $category->parent ? $category->parent->name : '['. language::translate('title_root', 'Root') .']',
			],
			'subcategories' => [],
		];

		$json['subcategories'] = database::query(
			"select c.id, c.parent_id, c.created_at, coalesce(
				". implode(', ', array_map(function($language) {
					return "json_value(c.name, '$.". database::input($language['code']) ."')";
				}, language::$languages)) .",
				'(". database::input(language::translate('title_untitled', 'Untitled')) .")'
			) as name
			from ". DB_TABLE_PREFIX ."categories c
			where ". (!empty($_GET['parent_id']) ? "c.parent_id = ". (int)$_GET['parent_id'] : "c.parent_id is null") ."
			". (!empty($sql_find) ? "and (". implode(" or ", $sql_find) .")" : "") ."
			order by c.priority, name;"
		)->fetch_all(function($subcategory) {

			$subcategory['path'] = [];

			if (!empty(reference::category($subcategory['id'])->path)) {
				foreach (reference::category($subcategory['id'])->path as $ancestor) {
					$subcategory['path'][] = $ancestor->name;
				}
			}

			return $subcategory;
		});

	} catch (Exception $e) {
		$json = ['error' => $e->getMessage()];
	}

	ob_clean();
	header('Content-Type: application/json');
	echo json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	exit;
