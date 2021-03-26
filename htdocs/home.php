<?php

require('../include/mellivora.inc.php');

login_session_refresh();

send_cache_headers('home', Config::get('MELLIVORA_CONFIG_CACHE_TIME_HOME'));

head(lang_get('home'));

if (cache_start(CONST_CACHE_NAME_HOME, Config::get('MELLIVORA_CONFIG_CACHE_TIME_HOME'))) {

    echo '<div id="home-logo"></div>
        <div class="home-intro-text">',
    (!ctfStarted ()) ? (title_decorator ("blue", "0deg", "asterisk.png") . 'CTF will start in <b>' . seconds_to_pretty_time(Config::get('MELLIVORA_CONFIG_CTF_START_TIME') - time ()) . '</b><br><br>') : '',
    '<b>ACSI CTF training set</b> is a <a href="https://ctftime.org/ctf-wtf/">Capture The Flag training set</a> organized by <a href="https://8059blank.weebly.com/">your lovely seniors</a>. We have prepared challenges from a diverse range of categories such as cryptography, web exploitation, forensics, reverse engineering, binary exploitation, hardware, algorithmics and more! We made sure that each category has challenges for every skill level, so that there is always something for everyone to learn. If you come across anything you don\'t understand (which most likely you will), don\'t be afraid to google or ask your seniors. 
    </div>';

    echo '<div class="row">
    <div class="col-md-6">';

    section_head ("Rules");
    
    echo '<ul>
        <li>Attacking the platform is strictly prohibited and will get you kicked from CCA.</li>
        <li>Bruteforcing the flag will not get you anywhere except on the naughty list.</li>
        <li>Any questions regarding challenges or the platform should be asked on our discord server. (Make sure you aren\'t sharing anything important related to a challenge!)</li>
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

    $bgchoice = rand(0, 2);

    echo '<style>
        .background-left {
            background-image:url("/img/theme/human' . $bgchoice . 'left.png");
        }
        .background-right {
            background-image:url("/img/theme/human' . $bgchoice . 'right.png");
        }
    </style>';

    cache_end (CONST_CACHE_NAME_HOME);
}

foot();
