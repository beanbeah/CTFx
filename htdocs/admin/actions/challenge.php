<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	validate_xsrf_token($_POST[CONST_XSRF_TOKEN_KEY]);

	if ($_POST['action'] === 'new') {
		if (!is_valid_id(array_get($_POST, 'category'))) {
			message_error('You must select a category to create a challenge!');
		}

		require_fields(array('title'), $_POST);

		$initialPts = get_db_config("MELLIVORA_CONFIG_CHALL_INITIAL_POINTS");
		$minPts = get_db_config("MELLIVORA_CONFIG_CHALL_MINIMUM_POINTS");

		$id = db_insert(
			'challenges',
			array(
				'added' => time(),
				'added_by' => $_SESSION['id'],
				'title' => $_POST['title'],
				'description' => $_POST['description'],
				'available_from' => get_db_config("MELLIVORA_CONFIG_CTF_START_TIME"),
				'available_until' => get_db_config("MELLIVORA_CONFIG_CTF_END_TIME"),
				'points' => dynamicScoringFormula($initialPts, $minPts, 0),
				'initial_points' => $initialPts,
				'minimum_points' => $minPts,
				'flag' => $_POST['flag'],
				'category' => $_POST['category'],
				'exposed' => $_POST['exposed']
			)
		);

		if ($id) {
			redirect('/admin/challenge.php?id=' . $id);
		} else {
			message_error('Could not insert new challenge.');
		}

	} else {

		validate_id($_POST['id']);

		if ($_POST['action'] === 'edit') {
			$challenge = db_select_one(
				'challenges',
				array(
					'solves'
				),
				array(
					'id' => $_POST['id']
				)
			);

			//date-time validation
			$expression = "/^(((0[1-9]|[12]\d|3[01])[\/\.-](0[13578]|1[02])[\/\.-]((19|[2-9]\d)\d{2})\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))|((0[1-9]|[12]\d|30)[\/\.-](0[13456789]|1[012])[\/\.-]((19|[2-9]\d)\d{2})\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))|((0[1-9]|1\d|2[0-8])[\/\.-](02)[\/\.-]((19|[2-9]\d)\d{2})\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))|((29)[\/\.-](02)[\/\.-]((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])))$/";

			$from_raw = $_POST['available_from'];
			$end_raw = $_POST['available_until'];

			if (preg_match($expression, $from_raw) && preg_match($expression, $end_raw)) {
				$from = new DateTime($from_raw, new DateTimeZone(get_db_config('MELLIVORA_CONFIG_CTF_TIMEZONE')));
				$end = new DateTime($end_raw, new DateTimeZone(get_db_config('MELLIVORA_CONFIG_CTF_TIMEZONE')));

				$from = $from->format('U');
				$end = $end->format('U');

				db_update(
					'challenges',
					array(
						'title' => $_POST['title'],
						'description' => $_POST['description'],
						'flag' => $_POST['flag'],
						'automark' => $_POST['automark'],
						'case_insensitive' => $_POST['case_insensitive'],
						'points' => dynamicScoringFormula($_POST['initial_points'], $_POST['minimum_points'], $challenge['solves']),
						'initial_points' => empty_to_zero($_POST['initial_points']),
						'minimum_points' => empty_to_zero($_POST['minimum_points']),
						'category' => $_POST['category'],
						'exposed' => $_POST['exposed'],
						'available_from' => $from,
						'available_until' => $end,
						'num_attempts_allowed' => $_POST['num_attempts_allowed'],
						'min_seconds_between_submissions' => $_POST['min_seconds_between_submissions'],
						'relies_on' => $_POST['relies_on']
					),
					array('id' => $_POST['id'])
				);

				redirect('/admin/challenge.php?id=' . $_POST['id'] . '&generic_success=1');
			} else {
				redirect('/admin/challenge.php?id=' . $_POST['id'] . '&generic_failure=1');
			}
		} else if ($_POST['action'] === 'delete') {

			if (!$_POST['delete_confirmation']) {
				message_error('Please confirm delete');
			}

			delete_challenge_cascading($_POST['id']);

			invalidate_cache(CONST_CACHE_NAME_FILES . $_POST['id']);
			invalidate_cache(CONST_CACHE_NAME_CHALLENGE_HINTS . $_POST['id']);

			redirect('/admin/index.php?generic_success=1');
		}
	}
}