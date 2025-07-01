<?php

	function catalog_categories_query($parent_ids=null, $filter=[]) {

		if ($parent_ids && !is_array($parent_ids)) {
			$parent_ids = [$parent_ids];
		}

		$query = database::query(
			"select c.id, c.parent_id, c.image, c.priority, c.updated_at,
				json_value(c.name, '$.". database::input(language::$selected['code']) ."') as name,
				json_value(c.short_description, '$.". database::input(language::$selected['code']) ."') as short_description

			from ". DB_TABLE_PREFIX ."categories c

			left join (
				select category_id, count(product_id) as num_products
				from lc_products_to_categories
				group by category_id
			) ptc on (ptc.category_id = c.id)

			left join (
				select parent_id, count(id) as num_subcategories
				from lc_categories
				where status
				group by parent_id
			) c2 on (c2.parent_id = c.id)

			where c.status
			and ". ($parent_ids ? "c.parent_id in ('". implode("', '", database::input($parent_ids)) ."')" : "c.parent_id is null") ."
			and (ptc.num_products > 0 or c2.num_subcategories > 0)

			order by c.priority asc, name asc;"
		);

		return $query;
	}

	function catalog_categories_search_query($filter=[]) {

		if (!empty($filter['categories'])) {
			$filter['categories'] = array_filter($filter['categories']);
		}

		$sql_select_relevance = [];
		$sql_where = [];

		if (!empty($filter['query'])) {

			$code_regex = functions::format_regex_code($_GET['query']);
			$query_fulltext = functions::escape_mysql_fulltext($_GET['query']);

			$sql_select_relevance[] = (
				"if(json_value(c.name, '$.". database::input(language::$selected['code']) ."') like '%". database::input($query_fulltext) ."%', 10, 0)"
			);

			$sql_select_relevance[] = (
				"if(json_value(c.short_description, '$.". database::input(language::$selected['code']) ."') like '%". database::input($query_fulltext) ."%', 5, 0)"
			);

			$sql_select_relevance[] = (
				"if(json_value(c.description, '$.". database::input(language::$selected['code']) ."') like '%". database::input($query_fulltext) ."%', 5, 0)"
			);

			$sql_select_relevance[] = (
				"if(json_value(c.synonyms, '$.". database::input(language::$selected['code']) ."') like '%". database::input($query_fulltext) ."%', 10, 0)"
			);
		}

		if (!empty($filter['keywords'])) {
			$sql_select_relevance['keywords'] = (
				"if(find_in_set('". implode("', p.keywords), 1, 0) + if(find_in_set('", database::input($filter['keywords'])) ."', p.keywords), 1, 0)"
			);
		}

		if (!empty($filter['exclude_categories'])) {
			$sql_where['exclude_categories'] = (
				"and c.id not in ('". implode("', '", database::input($filter['exclude_categories'])) ."')"
			);
		}

		$query = (
			"select c.id, c.parent_id, c.image, c.priority, c.updated_at,
				json_value(c.name, '$.". database::input(language::$selected['code']) ."') as name,
				json_value(c.short_description, '$.". database::input(language::$selected['code']) ."') as short_description,
				(". implode(" + ", $sql_select_relevance) .") as relevance

			from ". DB_TABLE_PREFIX ."categories c

			left join (
				select category_id, count(product_id) as num_products
				from lc_products_to_categories
				group by category_id
			) ptc on (ptc.category_id = c.id)

			left join (
				select parent_id, count(id) as num_subcategories
				from lc_categories
				where status
				group by parent_id
			) c2 on (c2.parent_id = c.id)

			where c.status
			and (ptc.num_products > 0 or c2.num_subcategories > 0)
			". (!empty($sql_where) ? implode(" and ", $sql_where) : "") ."

			having relevance > 0

			order by relevance desc
			". (!empty($filter['limit']) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : "") . (int)$filter['limit'] : "") .";"
		);

		return database::query($query);
	}

	// Filter function using AND syntax
	function catalog_products_query($filter=[]) {

		if (!is_array($filter)) {
			throw new Error('Invalid array filter for products query');
		}

		if (!empty($filter['categories'])) {
			$filter['categories'] = array_filter($filter['categories']);
		}

		if (!empty($filter['brands'])) {
			$filter['brands'] = array_filter($filter['brands']);
		}

		if (!empty($filter['attributes'])) {
			$filter['attributes'] = array_filter($filter['attributes']);
		}

		if (!empty($filter['products'])) {
			$filter['products'] = array_filter($filter['products']);
		}

		if (!empty($filter['exclude_products'])) {
			$filter['exclude_products'] = array_filter($filter['exclude_products']);
		}

		if (empty($filter['sort'])) {
			$filter['sort'] = 'popularity';
		}

		$sql_inner_sort = [];
		$sql_outer_sort = [];

		if (!empty($filter['campaigns_first'])) {
			$sql_outer_sort[] = "if(pc.campaign_price, 0, 1)";
		}

		switch ($filter['sort']) {

			case 'name':
				$sql_outer_sort[] = "name asc";
				break;

			case 'price':
				$sql_outer_sort[] = "final_price asc";
				break;

			case 'date':
				$sql_inner_sort[] = "p.created_at desc";
				$sql_outer_sort[] = "p.created_at desc";
				break;

			case 'popularity':
				$sql_inner_sort[] = "(p.purchases / ceil(datediff(now(), p.created_at)/7)) desc, (p.views / ceil(datediff(now(), p.created_at)/7)) desc";
				$sql_outer_sort[] = "(p.purchases / ceil(datediff(now(), p.created_at)/7)) desc, (p.views / ceil(datediff(now(), p.created_at)/7)) desc";
				break;

			case 'products':
				if (empty($filter['products'])) break;
				$sql_inner_sort[] = "Field(p.id, '". implode("', '", $filter['products']) ."')";
				$sql_outer_sort[] = "Field(p.id, '". implode("', '", $filter['products']) ."')";
				break;

			case 'random':
				$sql_outer_sort[] = "rand()";
				break;
		}

		$sql_where_categories = '';
		if (!empty($filter['categories'])) {
			$sql_where_categories =
				"and p.id in (
					select distinct product_id from ". DB_TABLE_PREFIX ."products_to_categories
					where category_id in ('". implode("', '", database::input($filter['categories'])) ."')
				)";
		}

		$sql_where_attributes = [];
		if (!empty($filter['attributes']) && is_array($filter['attributes'])) {
			foreach ($filter['attributes'] as $group_id => $values) {
				$sql_where_attributes[] =
					"and p.id in (
						select distinct product_id from ". DB_TABLE_PREFIX ."products_attributes
						where (group_id = ". (int)$group_id ." and (value_id in ('". implode("', '", database::input($values)) ."') or custom_value in ('". implode("', '", database::input($values)) ."')))
					)";
			}
			$sql_where_attributes = implode(PHP_EOL, $sql_where_attributes);
		}

		$sql_column_price = "coalesce(". implode(", ", array_map(function($currency) {
			return "if(json_value(price, '$.". database::input($currency['code']) ."') != 0, json_value(price, '$.". database::input($currency['code']) ."') * ". $currency['value'] .", null)";
		}, currency::$currencies)) .")";

		$query = (
			"select p.*, b.id as brand_id, b.name as brand_name,
				pp.price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, pp.price) as final_price,
				ifnull(pso.num_stock_options, 0) as num_stock_options, pso.total_quantity, pso.quantity_available,
				pa.attributes, ss.hidden

			from (
				select p.id, p.delivery_status_id, p.sold_out_status_id, p.code, p.brand_id, p.keywords, p.image,
					p.recommended_price, p.tax_class_id, p.quantity_unit_id, p.views, p.purchases, p.created_at,
					json_value(p.name, '$.". database::input(language::$selected['code']) ."') as name,
					json_value(p.short_description, '$.". database::input(language::$selected['code']) ."') as short_description

				from ". DB_TABLE_PREFIX ."products p

				where p.status
				". (!empty($filter['featured']) ? "and featured = 1" : "") ."
				". (!empty($filter['products']) ? "and p.id in ('". implode("', '", database::input($filter['products'])) ."')" : null) ."
				". (!empty($filter['product_name']) ? "and json_value(p.name, '$.". database::input(language::$selected['code']) ."') like '%". addcslashes(database::input($filter['product_name']), '%_') ."%'" : "") ."
				". fallback($sql_where_categories) ."
				". fallback($sql_where_attributes) ."
				". (!empty($filter['brands']) ? "and p.brand_id in ('". implode("', '", database::input($filter['brands'])) ."')" : null) ."
				". (!empty($filter['keywords']) ? "and (". implode(" or ", array_map(function($s){ return "find_in_set('$s', p.keywords)"; }, database::input($filter['keywords']))) .")" : null) ."
				and (p.valid_from is null or p.valid_from <= '". date('Y-m-d H:i:s') ."')
				and (p.valid_to is null or p.valid_to >= '". date('Y-m-d H:i:s') ."')
				". (!empty($filter['purchased']) ? "and p.purchases" : "") ."
				". (!empty($filter['exclude_products']) ? "and p.id not in ('". implode("', '", $filter['exclude_products']) ."')" : "") ."

				". ((!empty($sql_inner_sort) && !empty($filter['limit'])) ? "order by " . implode(",", $sql_inner_sort) : "") ."
				". ((!empty($filter['limit']) && empty($filter['sql_where']) && empty($filter['product_name']) && empty($filter['product_name']) && empty($filter['campaign']) && empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : "") . (int)$filter['limit'] : "") ."
			) p

			left join ". DB_TABLE_PREFIX ."brands b on (b.id = p.brand_id)

			left join (
				select
					product_id, group_concat(concat(group_id, '-', if(custom_value != '', custom_value, value_id)) separator ',') as attributes
				from ". DB_TABLE_PREFIX ."products_attributes
				group by product_id
				order by id
			) pa on (p.id = pa.product_id)

			left join (
				select product_id, $sql_column_price as price
				from ". DB_TABLE_PREFIX ."products_prices
				where customer_group_id is null
				and min_quantity = 1
			) pp on (pp.product_id = p.id)

			left join (
				select product_id, $sql_column_price as campaign_price
				from ". DB_TABLE_PREFIX ."campaigns_products
				where campaign_id in (
					select id from ". DB_TABLE_PREFIX ."campaigns
					where status
					and (valid_from is null or valid_from <= '". date('Y-m-d H:i:s') ."')
					and (valid_to is null or valid_to >= '". date('Y-m-d H:i:s') ."')
				)
				group by product_id
				order by $sql_column_price asc
				limit 1
			) pc on (pc.product_id = p.id)

			left join (
				select pso.product_id, pso.id as stock_option_id, count(pso.id) as num_stock_options,
					sum(si.quantity) as total_quantity, sum(si.quantity - oi.quantity_reserved) as quantity_available
				from ". DB_TABLE_PREFIX ."products_stock_options pso

				left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)

				left join (
					select oi.stock_option_id, sum(oi.quantity) as quantity_reserved
					from ". DB_TABLE_PREFIX ."orders_items oi
					left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
					where o.order_status_id in (
						select id from ". DB_TABLE_PREFIX ."order_statuses
						where stock_action = 'reserve'
					)
					group by oi.stock_option_id
				) oi on (oi.stock_option_id = pso.id)
				group by pso.product_id
			) pso on (pso.product_id = p.id)

			left join ". DB_TABLE_PREFIX ."sold_out_statuses ss on (p.sold_out_status_id = ss.id)

			where (
				(ifnull(pso.num_stock_options, 0) = 0 or pso.quantity_available > 0 or ss.hidden != 1)
				". (!empty($filter['sql_where']) ? "and (". $filter['sql_where'] .")" : "") ."
				". (!empty($filter['campaign']) ? "and campaign_price > 0" : "") ."
				". (!empty($filter['price_range']['min']) ? "and final_price >= ". (float)$filter['price_range']['min'] : "") ."
				". (!empty($filter['price_range']['max']) ? "and final_price <= ". (float)$filter['price_range']['max'] : "") ."
			)

			group by p.id

			". (!empty($sql_outer_sort) ? "order by ". implode(",", $sql_outer_sort) : "") ."
			". (!empty($filter['limit']) && (!empty($filter['sql_where']) || !empty($filter['product_name']) || !empty($filter['campaign']) || !empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : "") . (int)$filter['limit'] : "") .";"
		);

		return database::query($query);
	}

	// Search function using OR syntax
	function catalog_products_search_query($filter=[]) {

		if (!is_array($filter)) {
			throw new Error('Invalid array filter for products query');
		}

		if (!empty($filter['categories'])) {
			$filter['categories'] = array_filter($filter['categories']);
		}
		if (empty($filter['sort'])) $filter['sort'] = 'relevance';

		if (!empty($filter['brands'])) {
			$filter['brands'] = array_filter($filter['brands']);
		}

		if (!empty($filter['products'])) {
			$filter['products'] = array_filter($filter['products']);
		}

		if (!empty($filter['exclude_products'])) {
			$filter['exclude_products'] = array_filter($filter['exclude_products']);
		}

		$sql_select_relevance = [];
		$sql_inner_where = [];
		$sql_where = [];
		$sql_order_by = "relevance desc";

		if (!empty($filter['query'])) {

			$code_regex = functions::format_regex_code($_GET['query']);
			$query_fulltext = functions::escape_mysql_fulltext($_GET['query']);

			$sql_select_relevance[] = "if(p.code regexp '". database::input($code_regex) ."', 5, 0)";

			$sql_select_relevance[] = (
				"if(p.id in (
					select distinct product_id from ". DB_TABLE_PREFIX ."products_stock_options
					where stock_item_id in (
						select id from ". DB_TABLE_PREFIX ."stock_items
						where sku regexp '". database::input($code_regex) ."'
						or gtin regexp '". database::input($code_regex) ."'
						or mpn regexp '". database::input($code_regex) ."'
					)
				), 5, 0)"
			);

			$sql_select_relevance[] = (
				"if(json_value(p.name, '$.". database::input(language::$selected['code']) ."') like '%". addcslashes(database::input($_GET['query']), '%_') ."%', 3, 0)"
			);

			$sql_select_relevance[] = (
				"if(json_value(p.short_description, '$.". database::input(language::$selected['code']) ."') like '%". addcslashes(database::input($_GET['query']), '%_') ."%', 2, 0)"
			);

			$sql_select_relevance[] = (
				"if(json_value(p.description, '$.". database::input(language::$selected['code']) ."') like '%". addcslashes(database::input($_GET['query']), '%_') ."%', 1, 0)"
			);
		}

		if (!empty($filter['product_name'])) {
			$sql_select_relevance['product_name'] = (
				"if(json_value(p.name, '$.". database::input(language::$selected['code']) ."') like '%". addcslashes(database::input($filter['product_name']), '%_') ."%', 1, 0)"
			);
		}

		if (!empty($filter['products'])) {
			$sql_select_relevance['categories'] = (
				"if(p.id in ('". implode("', '", database::input($filter['products'])) ."'), 1, 0)"
			);
		}

		if (!empty($filter['categories'])) {
			$sql_select_relevance['categories'] = (
				"if(p.id in (
					select distinct product_id from ". DB_TABLE_PREFIX ."products_to_categories
					where category_id in ('". implode("', '", database::input($filter['categories'])) ."')
				), 1, 0)"
			);
		}

		if (!empty($filter['brands'])) {
			$sql_select_relevance['brands'] = (
				"if(p.brand_id in ('". implode("', '", database::input($filter['brands'])) ."'), 1, 0)"
			);
		}

		if (!empty($filter['attributes']) && is_array($filter['attributes'])) {
			$sql_where['attributes'] = (
				"if(p.id in (
					select distinct product_id
					from ". DB_TABLE_PREFIX ."products_attributes
					where concat(group_id, '-', value_id) in ('". implode("', '", database::input($filter['attributes'])) ."')
				), 1, 0)"
			);
		}

		if (!empty($filter['keywords'])) {
			$sql_select_relevance['keywords'] = (
				"if(find_in_set('". implode("', p.keywords), 1, 0) + if(find_in_set('", database::input($filter['keywords'])) ."', p.keywords), 1, 0)"
			);
		}

		if (!empty($filter['exclude_products'])) {
			$sql_inner_where['exclude_products'] = (
				"and p.id not in ('". implode("', '", database::input($filter['exclude_products'])) ."')"
			);
		}

		if (!empty($filter['campaigns'])) {
			$sql_where['campaigns'] = (
				"campaign_price > 0"
			);
		}

		if (!empty($filter['sort'])) {
			switch ($filter['sort']) {

				case 'name':
					$sql_order_by = "name asc";
					break;

				case 'price':
					$sql_order_by = "final_price asc";
					break;

				case 'date':
					$sql_order_by = "created_at desc";
					break;

				case 'rand':
					$sql_order_by = "rand()";
					break;

				case 'popularity':
					$sql_order_by = "(p.purchases / (datediff(now(), p.created_at)/7)) desc, (p.views / (datediff(now(), p.created_at)/7)) desc";
					break;
			}
		}

		$sql_column_price = "coalesce(". implode(", ", array_map(function($currency){
			return "if(json_value(price, '$.". database::input($currency['code']) ."') != 0, json_value(price, '$.". database::input($currency['code']) ."') * ". $currency['value'] .", null)";
		}, currency::$currencies)) . ")";

		$query = (
			"select	p.*, b.name as brand_name, pp.price, pc.campaign_price, if(pc.campaign_price, pc.campaign_price, pp.price) as final_price,
				ifnull(pso.num_stock_options, 0) as num_stock_options, pso.total_quantity, pso.quantity_available, pa.attributes

			from (
				select id, delivery_status_id, sold_out_status_id,code,	brand_id,	keywords,	image, recommended_price, tax_class_id,
					json_value(name, '$.". database::input(language::$selected['code']) ."') as name,
					json_value(short_description, '$.". database::input(language::$selected['code']) ."') as short_description,
					quantity_unit_id, views, purchases, created_at, (
						". implode(" + ", $sql_select_relevance) ."
					) as relevance
				from ". DB_TABLE_PREFIX ."products p
				where status
				". (!empty($sql_inner_where) ? implode(" and ", $sql_inner_where) : "")."
				". (!empty($filter['featured']) ? "and featured = 1" : "") ."
				and (valid_from is null or valid_from <= '". date('Y-m-d H:i:s') ."')
				and (valid_to is null or valid_to >= '". date('Y-m-d H:i:s') ."')
				having relevance > 0
			) p

			left join ". DB_TABLE_PREFIX ."brands b on (b.id = p.brand_id)

			left join (
				select product_id, group_concat(concat(group_id, '-', if(custom_value != '', custom_value, value_id)) separator ',') as attributes
				from ". DB_TABLE_PREFIX ."products_attributes
				group by product_id
				order by id
			) pa on (p.id = pa.product_id)

			left join (
				select product_id, $sql_column_price as price
				from ". DB_TABLE_PREFIX ."products_prices
				where customer_group_id is null
				and min_quantity = 1
			) pp on (pp.product_id = p.id)

			left join (
				select product_id, $sql_column_price as campaign_price
				from ". DB_TABLE_PREFIX ."campaigns_products
				where campaign_id in (
					select id from ". DB_TABLE_PREFIX ."campaigns
					where status
					and (valid_from is null or valid_from <= '". date('Y-m-d H:i:s') ."')
					and (valid_to is null or valid_to >= '". date('Y-m-d H:i:s') ."')
				)
				group by product_id
				order by $sql_column_price asc
				limit 1
			) pc on (pc.product_id = p.id)

			left join (
				select pso.product_id, pso.id as stock_option_id, count(pso.id) as num_stock_options, sum(si.quantity) as total_quantity, sum(si.quantity - oi.quantity_reserved) as quantity_available
				from ". DB_TABLE_PREFIX ."products_stock_options pso
				left join ". DB_TABLE_PREFIX ."stock_items si on (si.id = pso.stock_item_id)
				left join (
					select oi.stock_option_id, sum(oi.quantity) as quantity_reserved
					from ". DB_TABLE_PREFIX ."orders_items oi
					left join ". DB_TABLE_PREFIX ."orders o on (o.id = oi.order_id)
					where o.order_status_id in (
						select id from ". DB_TABLE_PREFIX ."order_statuses
						where stock_action = 'reserve'
					)
					group by oi.stock_option_id
				) oi on (oi.stock_option_id = pso.id)
				group by pso.product_id
			) pso on (pso.product_id = p.id)

			left join ". DB_TABLE_PREFIX ."sold_out_statuses ss on (p.sold_out_status_id = ss.id)

			where (p.id
				and (ifnull(pso.num_stock_options, 0) = 0 or pso.quantity_available > 0 or ss.hidden != 1)
				". (!empty($sql_where) ? implode(" and ", $sql_where) : "") ."
				". (!empty($filter['price_range']['min']) ? "and final_price >= ". (float)$filter['price_range']['min'] : "") ."
				". (!empty($filter['price_range']['max']) ? "and final_price <= ". (float)$filter['price_range']['max'] : "") ."
			)

			group by p.id
			having relevance > 0

			". (!empty($sql_order_by) ? "order by ". $sql_order_by : "") ."
			". (!empty($filter['limit']) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : "") . (int)$filter['limit'] : "") .";"
		);

		return database::query($query);
	}
