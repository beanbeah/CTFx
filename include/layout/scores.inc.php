<?php

function scoreboard($scores, $show_podium = false, $show_scoreboard = true)
{
	if (empty ($scores)) {
		message_center("No teams");
	}

	//$scores = json_decode (file_get_contents ("/var/www/ctfx/include/layout/custom_scores.json"), true);

	if ($show_podium) podium($scores);
	if ($show_scoreboard)print_graph();
	echo '<table class="table team-table table-striped table-hover"><tbody>';

	$maxScore = $scores[0]['score'];
	if ($maxScore == 0) {
		$maxScore = 1;
	}

	$i = 1;
	foreach ($scores as $score) {
		echo '<tr>
          <td class="team-name">
            <div class="team-number">', number_format($i++), '. </div>
            <a href="user?id=', htmlspecialchars($score['user_id']), '" class="team_', htmlspecialchars($score['user_id']), '">'
		, htmlspecialchars($score['team_name']),
		'</a></td>',
		'<td class="team-flag">', country_flag_link($score['country_name'], $score['country_code']), '</td>
          <td class="team-progress-bar">', progress_bar(($score['score'] / $maxScore) * 100, false, false), '</td>
          <td class="team-score">', number_format($score['score']), ' Points</td>
        </tr>';
	}

	echo '</tbody>
    </table>';
}

function print_graph()
{
	echo '
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.27.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@0.1.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
    <script src="/js/chartjs-plugin-zoom.min.js"></script>
    <div class="score-graph">Top 10 Teams</div>
    <canvas></canvas>
    <script>';

	//plot graph function
	echo '
    function plot_graph(raw_data){
        const colors = ["#2c6378", "#88c390", "#f8f1ad", "#f1513d", "#ba2f66", "#4d685a", "#40caa5", "#f5c92f", "#faa62a", "#d9492d"];
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
                            color: "#222222"
                        }
                    },
                    y: {
                        ticks: {
                            color: "#222222"
                        }
                    }
                },
                plugins: {
                    zoom: zoomOptions,
                    legend: {
                        position: "bottom",
                        labels: {
                            color: "#222222"
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
	echo '
    fetch("' . Config::get("MELLIVORA_CONFIG_SITE_URL") . 'json?view=graph")
    .then((resp) => resp.json())
    .then(function(data) {
        plot_graph({"datasets": data});
    })
    .catch(function(error) {
        console.log(error);
    });
    </script>
    <button id="reset-zoom" class="btn btn-1 btn-zoom">Reset zoom</button>
    <div class="chartjs-note">Pan is activated by keeping ctrl pressed.</div>
    <br>';
}

function podium($scores)
{
	$top3 = [1, 0, 2];
	$widths = [128, 196, 96];

	echo '<div class="podium">';

	for ($i = 0; $i < 3; $i++) {
		$team = $scores[$top3[$i]];

		if (!isset ($team))
			continue;

		$avatar = "https://www.gravatar.com/avatar/" . md5($team["email"]) . "?s=256&d=mp";

		echo '<div class="podium-position" style="width:', $widths[$i], 'px">
      <a href="/user?id=', $team['user_id'], '">
        <img class="podium-icon" style="width:', $widths[$i], 'px" src="', htmlspecialchars($avatar), '">
      <div class="podium-name has-tooltip" data-toggle="tooltip" data-placement="top" title="', htmlspecialchars($team["team_name"]), '">
      ', $top3[$i] + 1, '. ', htmlspecialchars($team["team_name"]), '
      </a></div>
    </div>';
	}

	echo '</div>';
}

function challenges($categories)
{
	$now = time();
	$num_participating_users = get_num_participating_users();

	foreach ($categories as $category) {

		echo '
        <table class="team-table table table-striped table-hover">
          <thead>
            <tr>
              <th>', htmlspecialchars($category['title']), '</th>
              <th class="center">', lang_get('points'), '</th>
              <th class="center"><span class="has-tooltip" data-toggle="tooltip" data-placement="top" title="% of actively participating users">', lang_get('percentage_solvers'), '</span></th>
              <th>', lang_get('first_solvers'), '</th>
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
              available_from < ' . $now . ' AND
              category = :category AND
              exposed = 1
            ORDER BY points ASC',
			array(
				'category' => $category['id']
			)
		);

		foreach ($challenges as $challenge) {

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
                    <a href="challenge?id=', htmlspecialchars($challenge['id']), '">', htmlspecialchars($challenge['title']), '</a>
                </td>

                <td class="center">
                    ', number_format($challenge['points']), '
                </td>

                <td class="center">
                    ', number_format($num_participating_users ? ($num_solvers / $num_participating_users) * 100 : 0), '%
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
					'challenge' => $challenge['id']
				)
			);

			if (count($users)) {
				$pos = 1;
				foreach ($users as $user) {
					echo get_position_medal($pos++),
					'<a href="user?id=', htmlspecialchars($user['id']), '">', htmlspecialchars($user['team_name']), '</a><br />';
				}
			} else {
				echo '<i>', lang_get('unsolved'), '</i>';
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
