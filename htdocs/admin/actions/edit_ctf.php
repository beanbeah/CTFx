<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	validate_xsrf_token($_POST[CONST_XSRF_TOKEN_KEY]);

	if ($_POST['action'] === 'change_times') {

		require_fields(array('ctf_start_time'), $_POST);
		require_fields(array('ctf_end_time'), $_POST);

		$from_raw = $_POST['ctf_start_time'];
		$end_raw = $_POST['ctf_end_time'];

		//shamelessly stolen from https://stackoverflow.com/questions/24375711/mm-dd-yyyy-hhmmss-am-pm-date-validation-regular-expression-in-javascript
		$expression = "/^(((0[1-9]|[12]\d|3[01])[\/\.-](0[13578]|1[02])[\/\.-]((19|[2-9]\d)\d{2})\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))|((0[1-9]|[12]\d|30)[\/\.-](0[13456789]|1[012])[\/\.-]((19|[2-9]\d)\d{2})\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))|((0[1-9]|1\d|2[0-8])[\/\.-](02)[\/\.-]((19|[2-9]\d)\d{2})\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))|((29)[\/\.-](02)[\/\.-]((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))\s(0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])))$/";

		//date-time validation
		if (preg_match($expression, $from_raw) && preg_match($expression, $end_raw)) {
			$from = new DateTime($from_raw, new DateTimeZone(Config::get('MELLIVORA_CONFIG_CTF_TIMEZONE')));
			$end = new DateTime($end_raw, new DateTimeZone(Config::get('MELLIVORA_CONFIG_CTF_TIMEZONE')));

			$from = $from->format('U');
			$end = $end->format('U');

			if ($_POST['write_to_config']) {
				//write to config file, kinda hacky atm
				$concat_from = 's+^Config::set(\'"\'"\'MELLIVORA_CONFIG_CTF_START_TIME\'"\'"\', .*$+Config::set(\'"\'"\'MELLIVORA_CONFIG_CTF_START_TIME\'"\'"\', ' . $from . ');+g';

				shell_exec("sed -i '{$concat_from}' /var/www/ctfx/include/config/config.inc.php 2>/dev/null >/dev/null &");

				$concat_to = 's+^Config::set(\'"\'"\'MELLIVORA_CONFIG_CTF_END_TIME\'"\'"\', .*$+Config::set(\'"\'"\'MELLIVORA_CONFIG_CTF_END_TIME\'"\'"\', ' . $end . ');+g';

				shell_exec("sed -i '{$concat_to}' /var/www/ctfx/include/config/config.inc.php 2>/dev/null >/dev/null &");
			}

			db_update_all(
				'challenges',
				array(
					'available_from' => $from,
					'available_until' => $end
				)
			);

			redirect('/admin/edit_ctf.php?generic_success=1');
		} else {
			//something went wrong with input
			redirect('/admin/edit_ctf.php?generic_failure=1');
		}

	} else if ($_POST['action'] === 'scoreboard_freeze') {
		//write to config file, quite hacky at the moment
		if ($_POST['freeze']) {
			$concat_hide = 's+^Config::set(\'"\'"\'MELLIVORA_CONFIG_SHOW_SCOREBOARD\'"\'"\', .*$+Config::set(\'"\'"\'MELLIVORA_CONFIG_SHOW_SCOREBOARD\'"\'"\', false);+g';
			shell_exec("sed -i '{$concat_hide}' /var/www/ctfx/include/config/config.inc.php 2>/dev/null >/dev/null &");
		} else {
			$concat_show = 's+^Config::set(\'"\'"\'MELLIVORA_CONFIG_SHOW_SCOREBOARD\'"\'"\', .*$+Config::set(\'"\'"\'MELLIVORA_CONFIG_SHOW_SCOREBOARD\'"\'"\', true);+g';
			shell_exec("sed -i '{$concat_show}' /var/www/ctfx/include/config/config.inc.php 2>/dev/null >/dev/null &");

		}

		redirect('/admin/edit_ctf.php?generic_success=1');

	}
}