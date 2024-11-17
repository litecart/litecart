<?php

	function catalog_categories_query($parent_id=null) {

		$query = database::query(
			"select c.id, c.parent_id, c.image, ci.name, ci.short_description, c.priority, c.date_updated from ". DB_TABLE_PREFIX ."categories c

			left join ". DB_TABLE_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')

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
			and ". ($parent_id ? "c.parent_id = ". (int)$parent_id : "c.parent_id is null") ."
			and (ptc.num_products > 0 or c2.num_subcategories > 0)

			order by c.priority asc, ci.name asc;"
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
				"if(c.id in (
					select distinct category_id from ". DB_TABLE_PREFIX ."categories_info
					where match(name) against ('". database::input($query_fulltext) ."' in boolean mode)
				), 10, 0)"
			);

			$sql_select_relevance[] = (
				"if(c.id in (
					select distinct category_id from ". DB_TABLE_PREFIX ."categories_info
					where match(synonyms) against ('". database::input($query_fulltext) ."' in boolean mode)
				), 10, 0)"
			);

			$sql_select_relevance[] = (
				"if(c.id in (
					select distinct category_id from ". DB_TABLE_PREFIX ."categories_info
					where match(short_description) against ('". database::input($query_fulltext) ."' in boolean mode)
				), 5, 0)"
			);

			$sql_select_relevance[] = (
				"if(c.id in (
					select distinct category_id from ". DB_TABLE_PREFIX ."categories_info
					where match(description) against ('". database::input($query_fulltext) ."' in boolean mode)
				), 5, 0)"
			);

			$sql_select_relevance[] = (
				"if(match(synonyms) against ('". database::input($query_fulltext) ."' in boolean mode), 10, 0)"
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

		$categories_query = database::query(
			"select c.id, c.parent_id, c.image, ci.name, ci.short_description, c.priority, c.date_updated,
			(". implode(" + ", $sql_select_relevance) .") as relevance

			from ". DB_TABLE_PREFIX ."categories c

			left join ". DB_TABLE_PREFIX ."categories_info ci on (ci.category_id = c.id and ci.language_code = '". database::input(language::$selected['code']) ."')

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

		return $categories_query;
	}

	// Filter function using AND syntax
	function catalog_products_query($filter=[]) {

		if (!is_array($filter)) {
			trigger_error('Invalid array filter for products query', E_USER_ERROR);
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
				$sql_inner_sort[] = "p.date_created desc";
				$sql_outer_sort[] = "p.date_created desc";
				break;

			case 'popularity':
				$sql_inner_sort[] = "(p.purchases / ceil(datediff(now(), p.date_created)/7)) desc, (p.views / ceil(datediff(now(), p.date_created)/7)) desc";
				$sql_outer_sort[] = "(p.purchases / ceil(datediff(now(), p.date_created)/7)) desc, (p.views / ceil(datediff(now(), p.date_created)/7)) desc";
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

		$sql_where_prices = [];
		if (!empty($filter['price_ranges']) && is_array($filter['price_ranges'])) {
			foreach ($filter['price_ranges'] as $price_range) {
				list($min,$max) = explode('-', $price_range);
				$sql_where_prices[] = "(if(pc.campaign_price, pc.campaign_price, pp.price) >= ". (float)$min ." and if(pc.campaign_price, pc.campaign_price, pp.price) <= ". (float)$max .")";
			}
			$sql_where_prices = "and (". implode(" or ", $sql_where_prices) .")";
		}

		$query = (
			"select
				p.*,
				pi.name,
				pi.short_description,
				b.id as brand_id,
				b.name as brand_name,
				pp.price,
				pc.campaign_price,
				if(pc.campaign_price,
				pc.campaign_price, pp.price) as final_price,
				ifnull(pso.num_stock_options, 0) as num_stock_options,
				pso.quantity,
				pso.quantity_available,
				pa.attributes,
				ss.hidden

			from (
				select
					p.id,
					p.delivery_status_id,
					p.sold_out_status_id,
					p.code,
					p.brand_id,
					p.keywords,
					p.image,
					p.recommended_price,
					p.tax_class_id,
					p.quantity_unit_id,
					p.views,
					p.purchases,
					p.date_created

				from ". DB_TABLE_PREFIX ."products p

				where p.status
				". (!empty($filter['products']) ? "and p.id in ('". implode("', '", database::input($filter['products'])) ."')" : null) ."
				". fallback($sql_where_categories) ."
				". fallback($sql_where_attributes) ."
				". (!empty($filter['brands']) ? "and p.brand_id in ('". implode("', '", database::input($filter['brands'])) ."')" : null) ."
				". (!empty($filter['keywords']) ? "and (". implode(" or ", array_map(function($s){ return "find_in_set('$s', p.keywords)"; }, database::input($filter['keywords']))) .")" : null) ."
				and (p.date_valid_from is null or p.date_valid_from <= '". date('Y-m-d H:i:s') ."')
				and (p.date_valid_to is null or p.date_valid_to >= '". date('Y-m-d H:i:s') ."')
				". (!empty($filter['purchased']) ? "and p.purchases" : "") ."
				". (!empty($filter['exclude_products']) ? "and p.id not in ('". implode("', '", $filter['exclude_products']) ."')" : "") ."

				". ((!empty($sql_inner_sort) && !empty($filter['limit'])) ? "order by " . implode(",", $sql_inner_sort) : "") ."
				". ((!empty($filter['limit']) && empty($filter['sql_where']) && empty($filter['product_name']) && empty($filter['product_name']) && empty($filter['campaign']) && empty($sql_where_prices)) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : "") . (int)$filter['limit'] : "") ."
			) p

			left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')

			left join ". DB_TABLE_PREFIX ."brands b on (b.id = p.brand_id)

			left join (
				select
					product_id,
					group_concat(concat(group_id, '-', if(custom_value != '',
					custom_value, value_id)) separator ',') as attributes
				from ". DB_TABLE_PREFIX ."products_attributes
				group by product_id
				order by id
			) pa on (p.id = pa.product_id)

			left join (
				select
					product_id,
					coalesce(
						". implode(", ", array_map(function($currency){
							return "if(`". database::input($currency['code']) ."` != 0, `". database::input($currency['code']) ."` * ". $currency['value'] .", null)";
						}, currency::$currencies)) ."
					) as price
				from ". DB_TABLE_PREFIX ."products_prices
			) pp on (pp.product_id = p.id)

			left join (
				select product_id, min(coalesce(
					". implode(", ", array_map(function($currency){
						return "if(`". database::input($currency['code']) ."` != 0, `". database::input($currency['code']) ."` * ". $currency['value'] .", null)";
					}, currency::$currencies)) ."
				)) as campaign_price
				from ". DB_TABLE_PREFIX ."campaigns_products
				where campaign_id in (
					select id from ". DB_TABLE_PREFIX ."campaigns
					where status
					and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
					and (date_valid_to is null or date_valid_to >= '". date('Y-m-d H:i:s') ."')
				)
				group by product_id
				limit 1
			) pc on (pc.product_id = p.id)

			left join (
				select
					pso.product_id,
					pso.id as stock_option_id,
					count(pso.id) as num_stock_options,
					sum(si.quantity) as quantity,
					(si.quantity - oi.quantity_reserved) as quantity_available
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
				". (!empty($filter['sql_where']) ? "and (". $filter['sql_where'] .")" : null) ."
				". (!empty($filter['product_name']) ? "and pi.name like '%". database::input($filter['product_name']) ."%'" : null) ."
				". (!empty($filter['campaign']) ? "and campaign_price > 0" : null) ."
				". fallback($sql_where_prices) ."
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
			trigger_error('Invalid array filter for products query', E_USER_ERROR);
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
			$sql_select_relevance[] = "if(p.sku regexp '". database::input($code_regex) ."', 5, 0)";
			$sql_select_relevance[] = "if(p.mpn regexp '". database::input($code_regex) ."', 5, 0)";
			$sql_select_relevance[] = "if(p.gtin regexp '". database::input($code_regex) ."', 5, 0)";

			$sql_select_relevance[] = (
				"if(p.id in (
					select distinct product_id from ". DB_TABLE_PREFIX ."products_stock_options
					where stock_item_id in (
						select id from ". DB_TABLE_PREFIX ."stock_items
						where sku regexp '". database::input($code_regex) ."'
					)
				), 5, 0)"
			);

			$sql_select_relevance[] = (
				"if(p.id in (
					select distinct product_id from ". DB_TABLE_PREFIX ."products_info
					where match(name) against ('". database::input($query_fulltext) ."' in boolean mode)
				), 10, 0)"
			);

			$sql_select_relevance[] = (
				"if(p.id in (
					select distinct product_id from ". DB_TABLE_PREFIX ."products_info
					where match(short_description) against ('". database::input($query_fulltext) ."' in boolean mode)
				), 10, 0)"
			);

			$sql_select_relevance[] = (
				"if(p.id in (
				select distinct product_id from ". DB_TABLE_PREFIX ."products_info
				where match(description) against ('". database::input($query_fulltext) ."' in boolean mode)
				), 10, 0)"
			);

			$sql_select_relevance[] = (
				"if(p.id in (
					select distinct product_id
					from ". DB_TABLE_PREFIX ."products_info
					where name like '%". addcslashes(database::input($_GET['query']), '%_') ."%'
				), 3, 0)"
			);

			$sql_select_relevance[] = (
				"if(p.id in (
					select distinct product_id
					from ". DB_TABLE_PREFIX ."products_info
					where short_description like '%". addcslashes(database::input($_GET['query']), '%_') ."%'
				), 2, 0)"
			);

			$sql_select_relevance[] = (
				"if(p.id in (
					select distinct product_id
					from ". DB_TABLE_PREFIX ."products_info
					where description like '%". addcslashes(database::input($_GET['query']), '%_') ."%'
				), 1, 0)"
			);
		}

		if (!empty($filter['product_name'])) {
			$sql_select_relevance['product_name'] = (
				"if(p.id in (
					select distinct product_id
					from ". DB_TABLE_PREFIX ."products_info
					where name like '%". addcslashes(database::input($filter['product_name']), '%_') ."%'
				), 1, 0)"
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
					$sql_order_by = "date_created desc";
					break;

				case 'rand':
					$sql_order_by = "rand()";
					break;

				case 'popularity':
					$sql_order_by = "(p.purchases / (datediff(now(), p.date_created)/7)) desc, (p.views / (datediff(now(), p.date_created)/7)) desc";
					break;
			}
		}

		$query = (
			"select
				p.*,
				pi.name,
				pi.short_description,
				b.id as brand_id,
				b.name as brand_name,
				pp.price,
				pc.campaign_price,
				if(pc.campaign_price,
				pc.campaign_price, pp.price) as final_price,
				ifnull(pso.num_stock_options, 0) as num_stock_options,
				pso.quantity,
				pso.quantity_available,
				pa.attributes

			from (
				select
					id,
					delivery_status_id,
					sold_out_status_id,
					code,
					brand_id,
					keywords,
					image,
					recommended_price,
					tax_class_id,
					quantity_unit_id,
					views,
					purchases,
					date_created,
					(". implode(" + ", $sql_select_relevance) .") as relevance
				from ". DB_TABLE_PREFIX ."products p
				where status
				". (!empty($sql_inner_where) ? implode(" and ", $sql_inner_where) : "")."
				and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
				and (date_valid_to is null or date_valid_to >= '". date('Y-m-d H:i:s') ."')
				having relevance > 0
			) p

			left join ". DB_TABLE_PREFIX ."products_info pi on (pi.product_id = p.id and pi.language_code = '". database::input(language::$selected['code']) ."')

			left join ". DB_TABLE_PREFIX ."brands b on (b.id = p.brand_id)

			left join (
				select product_id, group_concat(concat(group_id, '-', if(custom_value != '', custom_value, value_id)) separator ',') as attributes
				from ". DB_TABLE_PREFIX ."products_attributes
				group by product_id
				order by id
			) pa on (p.id = pa.product_id)

			left join (
				select
					product_id,
					coalesce(
						". implode(", ", array_map(function($currency){
							return "if(`". database::input($currency['code']) ."` != 0, `". database::input($currency['code']) ."` * ". $currency['value'] .", null)";
						}, currency::$currencies)) ."
					) as price
				from ". DB_TABLE_PREFIX ."products_prices
			) pp on (pp.product_id = p.id)

			left join (
				select product_id, min(coalesce(
					". implode(", ", array_map(function($currency){
						return "if(`". database::input($currency['code']) ."` != 0, `". database::input($currency['code']) ."` * ". $currency['value'] .", null)";
					}, currency::$currencies)) ."
				)) as campaign_price
				from ". DB_TABLE_PREFIX ."campaigns_products
				where campaign_id in (
					select id from ". DB_TABLE_PREFIX ."campaigns
					where status
					and (date_valid_from is null or date_valid_from <= '". date('Y-m-d H:i:s') ."')
					and (date_valid_to is null or date_valid_to >= '". date('Y-m-d H:i:s') ."')
				)
				group by product_id
				limit 1
			) pc on (pc.product_id = p.id)

			left join (
				select
					pso.product_id,
					pso.id as stock_option_id,
					count(pso.id) as num_stock_options,
					sum(si.quantity) as quantity,
					(si.quantity - oi.quantity_reserved) as quantity_available
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
			)

			group by p.id
			having relevance > 0

			". (!empty($sql_order_by) ? "order by ". $sql_order_by : "") ."
			". (!empty($filter['limit']) ? "limit ". (!empty($filter['offset']) ? (int)$filter['offset'] . ", " : "") . (int)$filter['limit'] : "") .";"
		);

		return database::query($query);
	}
