<?php

require('../include/mellivora.inc.php');
enforce_authentication();

login_session_refresh();

send_cache_headers('scores', Config::get('MELLIVORA_CONFIG_CACHE_TIME_SCORES'));

head(lang_get('scoreboard'));

function show_score()
{
	if (cache_start(CONST_CACHE_NAME_SCORES, Config::get('MELLIVORA_CONFIG_CACHE_TIME_SCORES'))) {

		$now = time();

		$scores = db_query_fetch_all('
         SELECT
            u.id AS user_id,
            u.team_name,
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
         WHERE u.competing = 1
			ORDER BY x.score DESC, x.tiebreaker ASC'
			);

			scoreboard($scores);

		echo '</div></div>';

		cache_end(CONST_CACHE_NAME_SCORES);
	}
}

if (get_db_config('MELLIVORA_CONFIG_SHOW_SCOREBOARD')) {
	show_score();
} else {
	if (user_is_staff()) {
		message_inline('Scoreboard is currently frozen for all players');
		show_score();
	} else {
		message_inline('Scoreboard is frozen');
	}
}

foot();
