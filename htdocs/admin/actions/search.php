<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	validate_xsrf_token($_POST[CONST_XSRF_TOKEN_KEY]);

	if ($_POST['search_in'] === 'users') {
		redirect('/admin/users?search_for=' . urlencode($_POST['search_for']));
	} else if ($_POST['search_in'] === 'ip_log') {
		redirect('/admin/ip_log?ip=' . urlencode($_POST['search_for']));
	}
}