<?php

require('../include/mellivora.inc.php');

login_session_refresh();

if (strlen(array_get($_GET, 'code')) != 2) {
	message_error(lang_get('please_supply_country_code'));
}

$country = db_select_one(
	'countries',
	array(
		'id',
		'country_name',
		'country_code'
	),
	array(
		'country_code' => $_GET['code']
	)
);

if (!$country) {
	message_error(lang_get('please_supply_country_code'));
}

head($country['country_name']);

if (cache_start(CONST_CACHE_NAME_COUNTRY . $_GET['code'], Config::get('MELLIVORA_CONFIG_CACHE_TIME_COUNTRIES'))) {

	section_head(htmlspecialchars($country['country_name']), country_flag_link($country['country_name'], $country['country_code'], true));

	$scores = db_query_fetch_all('
            SELECT
               u.id AS user_id,
               u.username,
               u.competing,
               u.email,
               co.id AS country_id,
               co.country_name,
               co.country_code,
               x.score,
               x.tiebreaker
            FROM users AS u
            INNER JOIN (
               SELECT
                  u.id,
                  SUM(c.points) AS score,
                  MAX(s.added) AS tiebreaker
               FROM users AS u
               LEFT JOIN submissions AS s ON u.id = s.user_id AND s.correct = 1
               LEFT JOIN challenges AS c ON c.id = s.challenge
               GROUP BY u.id
            ) AS x USING (id)
            LEFT JOIN countries AS co ON co.id = u.country_id
            WHERE u.competing = 1 AND co.id = :country_id
            ORDER BY x.score DESC, x.tiebreaker ASC',
		array(
			'country_id' => $country['id']
		)
	);

	scoreboard($scores,true,false);

	cache_end(CONST_CACHE_NAME_COUNTRY . $_GET['code']);
}

foot();