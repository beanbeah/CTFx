<?php

require('../include/mellivora.inc.php');

if(Config::get('MELLIVORA_CONFIG_SHOW_SCOREBOARD') === false){
    enforce_authentication(CONST_USER_CLASS_MODERATOR);
}

login_session_refresh();

header('Content-type: application/json');

if (!isset($_GET['view'])) {
    echo json_error(lang_get('please_request_view'));
    exit;
}
/**
 * Gets all User Score
 * Input: None
 * Output: 
 * {
 *      "standings":[
 *                 {
 *                      "pos": "int",
 *                      "team": "USERNAME OR TEAMNAME",
 *                      "score": "int"
 *                 },   
 * 
 *       ]
 * }
 */
if ($_GET['view'] == 'scores') {
    if (cache_start(CONST_CACHE_NAME_SCORES_JSON, Config::get('MELLIVORA_CONFIG_CACHE_TIME_SCORES'))) {
        json_scoreboard(array_get($_GET, 'user_type'));
        // To make the scoreboard fully CTFTime compatible you can run this python3 one-liner to encode unicode chars to \uXXXX:
        // open("ctftime.json","wb").write(open("ctfx.json","r",encoding='utf-8').read().encode("ascii","backslashreplace").replace(b"\\x",b"\\u00"))
        cache_end(CONST_CACHE_NAME_SCORES_JSON);
    }
}
/**
 * Gets all Top 10 users scoreboard history (sorted chronologically). This is explicitely meant for Chart.js
 * Input: None
 * Output: 
 * {
 *      [
 *          {
 *              "label": "USERNAME OR TEAMNAME",
 *              "data":[
 *                          {
 *                              "x": "int" (this is the epoch time)
 *                              "y": "int" (this is the player's score at time x)
 *                          }
 *                      ]
 *          },   
 * 
 *       ]
 * }
 */
else if ($_GET['view'] == 'graph') {
    if (cache_start(CONST_CACHE_NAME_SCORES_JSON, Config::get('MELLIVORA_CONFIG_CACHE_TIME_SCORES'))) {
        json_score_dump();
        cache_end(CONST_CACHE_NAME_SCORES_JSON);
    }
}

else {
    echo json_error(lang_get('please_request_view'));
    exit;
}
