<?php

require('../include/mellivora.inc.php');

login_session_refresh();

send_cache_headers('home', Config::get('MELLIVORA_CONFIG_CACHE_TIME_HOME'));

head(lang_get('home'));

if (cache_start(CONST_CACHE_NAME_HOME, Config::get('MELLIVORA_CONFIG_CACHE_TIME_HOME'))) {

    echo '<div id="home-logo"></div>
	<div class="home-intro-text">',
    (!ctfStarted ()) ? (title_decorator ("blue", "0deg", "asterisk.png") . 'CTF will start in <b>' . seconds_to_pretty_time(Config::get('MELLIVORA_CONFIG_CTF_START_TIME') - time ()) . '</b><br><br>') : '',
    'CTFx is a blazing fast CTF platform. This is a fork of <a href="https://github.com/Nakiami/mellivora">mellivora</a> that adds extra functionality and various other neat stuff. It should be noted this is still in development and there may be occasional bugs</div>';

    echo '<div class="row" style="text-align:center; font-size: 20px; margin-bottom:-5px">
    
    <div style="display:block ruby; transform: translate(-8%, 0%);">
    <br></div></div>';

    echo '<div class="row">
    <div class="col-md-6">';

    section_head ("Rules");
    
    echo '<ul>
        <li>Attacking the platform is strictly prohibited and will get you disqualified.</li>
        <li>The flag format is specified in the challenge description</li>
        <li>Bruteforcing the flag will not get you anywhere except on the naughty list.</li>
        <li>Any questions regarding challenges or the platform should be directed to the admins.</li>
    </ul>';

    echo '</div>
    <div class="col-md-6">';

    section_head ("Latest News");

    $news = db_query_fetch_all('SELECT * FROM news ORDER BY added DESC');

    if (count ($news) == 0) {
        message_inline ("No news");
    }

    foreach ($news as $item) {
        echo '<div class="ctfx-card">
            <div class="ctfx-card-head"><h4>',
                htmlspecialchars($item['title']),
                '</h4> <small>',
                date_time ($item['added']),
                '</small></div>
            <div class="ctfx-card-body">
                ',get_bbcode()->parse($item['body']),'
            </div>
        </div>';
    }

    echo '</div></div>';


    cache_end (CONST_CACHE_NAME_HOME);
}

foot();
