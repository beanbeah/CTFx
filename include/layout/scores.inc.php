<?php

function scoreboard ($scores,$show_podium = false) {
  if (empty ($scores)) {
    message_center ("No teams");
  }

  //$scores = json_decode (file_get_contents ("/var/www/ctfx/include/layout/custom_scores.json"), true);

  if($show_podium)podium($scores);
  print_graph();
  echo '<table class="table team-table table-striped table-hover"><tbody>';

    $maxScore = $scores[0]['score'];
    if ($maxScore == 0) {
        $maxScore = 1;
    }

    $i = 1;
    foreach ($scores as $score) {
        echo '<tr>
          <td class="team-name">
            <div class="team-number">',number_format($i++),'.</div>
            <a href="user?id=',htmlspecialchars($score['user_id']),'" class="team_',htmlspecialchars($score['user_id']),'">'
                , htmlspecialchars($score['team_name']),
            '</a></td>',
          '<td class="team-flag">',country_flag_link($score['country_name'], $score['country_code']),'</td>
          <td class="team-progress-bar">',progress_bar(($score['score'] / $maxScore) * 100, false, false),'</td>
          <td class="team-score">',number_format($score['score']),' Points</td>
        </tr>';
    }

    echo '</tbody>
    </table>';
}

function print_graph(){
    echo'
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.27.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@0.1.1"></script>
    <script src="/js/chartjs-plugin-zoom.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
    <div class="score-graph">Top 10 Teams</div>
    <canvas></canvas>
    <script>';

    //plot graph function
    echo'
    function plot_graph(raw_data){
        //Colors from https://bl.ocks.org/emeeks/8cdec64ed6daf955830fa723252a4ab3
        const colors = ["#a6cee3","#1f78b4","#b2df8a","#33a02c","#fb9a99","#e31a1c","#fdbf6f","#ff7f00","#cab2d6","#6a3d9a","#ffff99","#b15928"];
        const zoomOptions = {
          pan: {
            enabled: true,
            mode: "xy",
            modifierKey: "ctrl",
          },
          zoom: {
            mode: "xy",
            drag: {
              enabled: true,
              borderColor: "rgb(54, 162, 235)",
              borderWidth: 1,
              backgroundColor: "rgba(54, 162, 235, 0.3)"
            }
          }
        };
        var edited_data = [];
        edited_data["datasets"] = [];
        for (let [key, value] of Object.entries(raw_data["datasets"])) {
            value["backgroundColor"] = colors[key];
            value["borderColor"] = colors[key];
            edited_data["datasets"].push(value);
        }
        let ctx = document.querySelector("canvas").getContext("2d");
        let chart = new Chart(ctx, {
            type: "line",
            data: edited_data,
            options: {
                scales: {
                    x: {
                        type: "time",
                        ticks: {
                            color: "#ffffff"
                        }
                    },
                    y: {
                        ticks: {
                            color: "#ffffff"
                        }
                    }
                },
                plugins: {
                    zoom: zoomOptions,
                    legend: {
                        position: "bottom",
                        labels: {
                            color: "#ffffff"
                        }
                    },

                }
            },
        });
        $("#reset-zoom").click(function(){
            chart.resetZoom();
        });
    }';

    //fetch data + plot
    echo'
    fetch("https://ctf.8059blank.tk/json?view=graph")
    .then((resp) => resp.json())
    .then(function(data) {
        plot_graph({"datasets": data});
    })
    .catch(function(error) {
        console.log(error);
    });
    </script>
    <button id="reset-zoom" class="reset-zoom">Reset zoom</button>
    <div class="chartjs-note">Pan is activated by keeping ctrl pressed.</div>
    <br>';
}

function podium ($scores) {
  $top3 = [1, 0, 2];
  $widths = [128, 196, 96];

  echo '<div class="podium">';

  for ($i = 0; $i < 3; $i++) {
    $team = $scores[$top3[$i]];

    if (!isset ($team))
      continue;

    $avatar = "https://www.gravatar.com/avatar/" . md5 ($team["email"]) . "?s=256&d=mp";

    echo '<div class="podium-position" style="width:', $widths[$i], 'px">
      <a href="/user?id=',$team['user_id'],'">
        <img class="podium-icon" style="width:', $widths[$i], 'px" src="', htmlspecialchars($avatar), '">
      <div class="podium-name has-tooltip" data-toggle="tooltip" data-placement="top" title="', htmlspecialchars($team["team_name"]),'">
      ', $top3[$i] + 1, '. ', htmlspecialchars($team["team_name"]), '
      </a></div>
    </div>';
  }

  echo '</div>';
}

function challenges($categories) {
    $now = time();
    $num_participating_users = get_num_participating_users();

    foreach($categories as $category) {

        echo '
        <table class="team-table table table-striped table-hover">
          <thead>
            <tr>
              <th>',htmlspecialchars($category['title']),'</th>
              <th class="center">',lang_get('points'),'</th>
              <th class="center"><span class="has-tooltip" data-toggle="tooltip" data-placement="top" title="% of actively participating users">',lang_get('percentage_solvers'),'</span></th>
              <th>',lang_get('first_solvers'),'</th>
            </tr>
          </thead>
          <tbody>
         ';

        $challenges = db_query_fetch_all('
            SELECT
               id,
               title,
               points,
               available_from
            FROM challenges
            WHERE
              available_from < '.$now.' AND
              category = :category AND
              exposed = 1
            ORDER BY points ASC',
            array(
                'category'=>$category['id']
            )
        );

        foreach($challenges as $challenge) {

            $num_solvers = db_count_num(
                'submissions',
                array(
                    'correct' => 1,
                    'challenge' => $challenge['id']
                )
            );

            echo '
            <tr>
                <td>
                    <a href="challenge?id=',htmlspecialchars($challenge['id']),'">',htmlspecialchars($challenge['title']),'</a>
                </td>

                <td class="center">
                    ',number_format($challenge['points']),'
                </td>

                <td class="center">
                    ',number_format($num_participating_users ? ($num_solvers / $num_participating_users) * 100 : 0),'%
                </td>

                <td class="team-name">';

            $users = db_query_fetch_all('
                SELECT
                   u.id,
                   u.team_name
                FROM users AS u
                JOIN submissions AS s ON s.user_id = u.id
                WHERE
                   u.competing = 1 AND
                   s.correct = 1 AND
                   s.challenge = :challenge
                ORDER BY s.added ASC
                LIMIT 3',
                array(
                    'challenge'=>$challenge['id']
                )
            );

            if (count($users)) {
                $pos = 1;
                foreach($users as $user) {
                    echo get_position_medal($pos++),
                    '<a href="user?id=',htmlspecialchars($user['id']),'">',htmlspecialchars($user['team_name']), '</a><br />';
                }
            }

            else {
                echo '<i>',lang_get('unsolved'),'</i>';
            }

            echo '
                </td>
            </tr>';
        }
        echo '
        </tbody>
        </table>';
    }
}

function get_position_medal ($position, $return_pos = false) {
    switch ($position) {
        case 1:
            return '<img src="'.Config::get('MELLIVORA_CONFIG_SITE_URL_STATIC_RESOURCES').'img/award_xenon.png" class="has-tooltip challenge-solved-badge" data-html="true" data-toggle="tooltip" data-placement="right" title="<b>Xenon Award: </b>First to solve this challenge."/>';
        case 2:
            return '<img src="'.Config::get('MELLIVORA_CONFIG_SITE_URL_STATIC_RESOURCES').'img/award_osmium.png" class="has-tooltip challenge-solved-badge" data-html="true" data-toggle="tooltip" data-placement="right" title="<b>Osmium Award: </b>Second to solve this challenge."/>';
        case 3:
            return '<img src="'.Config::get('MELLIVORA_CONFIG_SITE_URL_STATIC_RESOURCES').'img/award_hydrogen.png" class="has-tooltip challenge-solved-badge" data-html="true" data-toggle="tooltip" data-placement="right" title="<b>Hydrogen Award: </b>Third to solve this challenge."/>';
    }

    if ($return_pos) {
        return '#'.$position.', ';
    }

    return '';
}
