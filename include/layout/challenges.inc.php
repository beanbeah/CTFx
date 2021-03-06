<?php

function print_submit_interval($challenge)
{
	echo($challenge['min_seconds_between_submissions'] ? '<span class="glyphicon glyphicon-calendar"></span> ' . lang_get(
			'minimum_time_between_submissions',
			array('time' => seconds_to_pretty_time($challenge['min_seconds_between_submissions']))
		) . ' ' : '');
}

function print_submissions_left($challenge)
{
	$remaining_submissions = get_num_remaining_submissions($challenge);
	echo($challenge['num_attempts_allowed'] ? tag($remaining_submissions . ' submissions remaining') : '');
}

function print_time_left($challenge)
{
	echo '<span class="time-left" data-countdown="', $challenge['available_until'], '">
    ', lang_get(
		'time_remaining',
		array('time' => time_remaining($challenge['available_until']))
	), '
    </span>';
}

function print_time_left_tooltip($challenge)
{
	print_time_left($challenge);
	echo ' <span class="time-left bi bi-clock"></span> ';
}

function print_submit_metadata($challenge)
{
	echo '<p>';
	print_submissions_left($challenge);
	echo '</p><p>';

	echo "<!--"; // Commenting this out to avoid some clutter
	print_submit_interval($challenge);
	echo '</p>';
	echo "-->";
}

function get_challenge_files($challenge)
{
	$files = cache_array_get(CONST_CACHE_NAME_FILES . $challenge['id'], Config::get('MELLIVORA_CONFIG_CACHE_TIME_FILES'));
	if (!is_array($files)) {
		$files = db_select_all(
			'files',
			array(
				'id',
				'title',
				'size',
				'url',
				'md5',
				'download_key'
			),
			array('challenge' => $challenge['id'])
		);

		cache_array_save(
			$files,
			CONST_CACHE_NAME_FILES . $challenge['id']
		);
	}

	return $files;
}

function
print_challenge_files($files)
{
	if (count($files)) {
		echo '<div>';
		foreach ($files as $file) {
			echo '<div class="challenge-file">';

			if (empty ($file['url'])) {
				$url = 'download?file_key=' . htmlspecialchars($file['download_key']) . '&user_key=' . get_user_download_key();
			} else {
				$url = htmlspecialchars($file['url']);
			}

			echo '<a target="_blank" href="', $url, '">', title_decorator("red", "0deg", "package.png"), '</a>';
			echo '<a target="_blank" class="challenge-filename" href="', $url, '">', htmlspecialchars($file['title']), '</a>';

			if (empty ($file['url'])) {
				if ($file['size']) {
					tag('Size: ' . bytes_to_pretty_size($file['size']));
				}

				if ($file['md5']) {
					tag('MD5: ' . $file['md5']);
				}
			}

			echo '</div>';
		}

		echo '</div> <!-- / challenge-files -->';
	}
}

function check_hint_exist($challenge)
{

	$hints = db_select_all(
		'hints',
		array('body'),
		array(
			'visible' => 1,
			'challenge' => $challenge['id']
		)
	);
	if (empty($hints)) {
		//no hints at all
		return false;
	} else {
		return true;
	}
}


function print_hints($challenge, $is_id)
{
	//idea is to accomodate both choices of calling by ID and calling by list
	if ($is_id) {
		if (cache_start(CONST_CACHE_NAME_CHALLENGE_HINTS . $challenge, Config::get('MELLIVORA_CONFIG_CACHE_TIME_HINTS'))) {
			$hints = db_select_all(
				'hints',
				array('body'),
				array(
					'visible' => 1,
					'challenge' => $challenge
				)
			);

			foreach ($hints as $hint) {
				message_inline('<strong>Hint!</strong> ' . parse_markdown($hint['body']), "blue", false);
			}

			cache_end(CONST_CACHE_NAME_CHALLENGE_HINTS . $challenge);
		}
	} else {

		if (cache_start(CONST_CACHE_NAME_CHALLENGE_HINTS . $challenge['id'], Config::get('MELLIVORA_CONFIG_CACHE_TIME_HINTS'))) {
			$hints = db_select_all(
				'hints',
				array('body'),
				array(
					'visible' => 1,
					'challenge' => $challenge['id']
				)
			);

			foreach ($hints as $hint) {
				message_inline('<strong>Hint!</strong> ' . parse_markdown($hint['body']), "blue", false);
			}

			cache_end(CONST_CACHE_NAME_CHALLENGE_HINTS . $challenge['id']);
		}
	}
}


function should_print_metadata($challenge)
{
	$remaining_submissions = get_num_remaining_submissions($challenge);
	return !$challenge['correct_submission_added'] && time() < $challenge['available_until'] && $remaining_submissions;
}

function get_num_remaining_submissions($challenge)
{
	// if we have defined a submission limit
	if ($challenge['num_attempts_allowed']) {
		return $challenge['num_attempts_allowed'] - $challenge['num_submissions'];
	}

	// no submission limit defined, always one submission remaining
	return 1;
}

function has_remaining_submissions($challenge)
{
	return get_num_remaining_submissions($challenge) > 0;
}

function get_submission_box_class($challenge, $remaining_submissions)
{
	// we have solved the challenge
	if ($challenge['correct_submission_added']) {
		return "challenge-solved";
	} // on an automark challenge, if we haven't solved a challenge, and we have no remaining submissions
	else if ($challenge['automark'] && !$challenge['correct_submission_added'] && !$remaining_submissions) {
		return "challenge-unavailable";
	} // if we have a manually marked challenge, and we have no submissions awaiting marking, we haven't solved it, and we have no remaining submissions
	else if (!$challenge['automark'] && !$challenge['unmarked'] && !$challenge['correct_submission_added'] && !$remaining_submissions) {
		return "challenge-unavailable";
	}

	return "";
}