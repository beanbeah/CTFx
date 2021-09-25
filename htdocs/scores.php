<?php

require('../include/mellivora.inc.php');
enforce_authentication();

login_session_refresh();

send_cache_headers('scores', Config::get('MELLIVORA_CONFIG_CACHE_TIME_SCORES'));

head(lang_get('scoreboard'));

function show_score($selective_scoring=false){
    if (cache_start(CONST_CACHE_NAME_SCORES, Config::get('MELLIVORA_CONFIG_CACHE_TIME_SCORES'))) {

    $now = time();

    $user_types = db_select_all(
        'user_types',
        array(
            'id',
            'title'
        )
    );

    // no user types
    if (empty($user_types)) {
        $scores = db_query_fetch_all('
            SELECT
               u.id AS user_id,
               u.team_name,
               u.email,
               co.id AS country_id,
               co.country_name,
               co.country_code,
               COALESCE(SUM(c.points),0) AS score,
               MAX(s.added) AS tiebreaker
            FROM users AS u
            LEFT JOIN countries AS co ON co.id = u.country_id
            LEFT JOIN submissions AS s ON u.id = s.user_id AND s.correct = 1
            LEFT JOIN challenges AS c ON c.id = s.challenge
            WHERE u.competing = 1
            GROUP BY u.id
            ORDER BY score DESC, tiebreaker ASC'
        );

        scoreboard($scores);
    }
    
    // at least one ser type
    else {
        foreach ($user_types as $user_type) {
            if (user_is_staff() || get_user_type() == $user_type['id'] || $selective_scoring){
                if(user_is_staff()){section_head(htmlspecialchars($user_type['title']) . ' ' . lang_get('scoreboard'),
                    '<a href="/json?view=scoreboard&user_type='.$user_type['id'].'"></a>'
                );}
                else {section_head(lang_get('scoreboard'),'<a href="/json?view=scoreboard&user_type='.$user_type['id'].'"></a>');}

                $scores = db_query_fetch_all('
                SELECT
                   u.id AS user_id,
                   u.team_name,
                   co.id AS country_id,
                   co.country_name,
                   co.country_code,
                   COALESCE(SUM(c.points),0) AS score,
                   MAX(s.added) AS tiebreaker
                FROM users AS u
                LEFT JOIN countries AS co ON co.id = u.country_id
                LEFT JOIN submissions AS s ON u.id = s.user_id AND s.correct = 1
                LEFT JOIN challenges AS c ON c.id = s.challenge
                WHERE
                  u.competing = 1 AND
                  u.user_type = :user_type
                GROUP BY u.id
                ORDER BY score DESC, tiebreaker ASC',
                    array(
                        'user_type'=>$user_type['id']
                    )
                );

                scoreboard($scores);
            }
        }
    }

    echo '</div></div>';

    cache_end(CONST_CACHE_NAME_SCORES);
    }
}

if (Config::get('MELLIVORA_CONFIG_SHOW_SCOREBOARD')) {
    show_score();
}

else{
    if (user_is_staff()){
        message_inline('Scoreboard is currently frozen for all players');
        show_score();
    }
    else {
        message_inline('Scoreboard is frozen');
    }
}

foot();
