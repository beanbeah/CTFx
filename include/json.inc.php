<?php

function json_error($message) {
    return json_encode(array('error'=>htmlspecialchars($message)));
}

function json_scoreboard ($user_type = null) {

    $values = array();

    if (is_valid_id($user_type)) {
        $values['user_type'] = $user_type;
    }

    $scores = db_query_fetch_all('
        SELECT
           u.id AS user_id,
           u.team_name,
           co.country_code,
           SUM(c.points) AS score,
           MAX(s.added) AS tiebreaker
        FROM users AS u
        LEFT JOIN countries AS co ON co.id = u.country_id
        LEFT JOIN submissions AS s ON u.id = s.user_id AND s.correct = 1
        LEFT JOIN challenges AS c ON c.id = s.challenge
        WHERE
          u.competing = 1
          '.(is_valid_id($user_type) ? 'AND u.user_type = :user_type' : '').'
        GROUP BY u.id
        ORDER BY score DESC, tiebreaker ASC',
        $values
    );

    $scoreboard = array();
    for ($i = 0; $i < count($scores); $i++) {
        $scoreboard['standings'][$i] = array(
            'pos'=>($i+1),
            'team'=>$scores[$i]['team_name'],
            'score'=>intval(array_get($scores[$i], 'score', 0))
        );
    }

    echo json_encode($scoreboard);
}

function json_score_dump() {
    $export = array();
    $dateTimeZone = new DateTimeZone(Config::get('MELLIVORA_CONFIG_CTF_TIMEZONE'));
    $dateTime = new DateTime("now", $dateTimeZone);
    $timeOffset = $dateTimeZone->getOffset($dateTime);

    //first retrieve top 10 so we dont actually die
    $consolidated_scores = db_query_fetch_all('
        SELECT
           u.id AS user_id,
           u.team_name,
           SUM(c.points) AS score,
           MAX(s.added) AS tiebreaker
        FROM users AS u
        LEFT JOIN submissions AS s ON u.id = s.user_id AND s.correct = 1
        LEFT JOIN challenges AS c ON c.id = s.challenge
        WHERE
          u.competing = 1
        GROUP BY u.id
        ORDER BY score DESC, tiebreaker ASC');

    for ($i =0; $i < 10; $i++){
        $challenges_solved = db_query_fetch_all('
            SELECT 
                submissions.added AS time_solve, 
                challenges.points
            FROM submissions INNER JOIN challenges 
            ON
                submissions.user_id = ' . $consolidated_scores[$i]['user_id'] .' AND
                submissions.correct = 1 AND
                submissions.challenge = challenges.id
            ORDER BY time_solve ASC');

        $export[$i]['label'] = $consolidated_scores[$i]['team_name'];

        //consolidate scores
        $sum = 0;
        for ($j = 0; $j<count($challenges_solved); $j++){
            $sum += $challenges_solved[$j]['points'];
            $time = $challenges_solved[$j]['time_solve'] + $timeOffset;
            $export[$i]['data'][$j] = array("x"=>$time,"y"=>$sum);
        }
    }
    echo json_encode($export);
}



