<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

head('Users');
menu_management();
section_title('Users');

echo '<table id="files" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>user</th>
          <th>E-Mail</th>
          <th>Last active</th>
          <th class="center">Class</th>
          <th class="center">Num IPs</th>
        </tr>
      </thead>
      <tbody>';

$values = array();
$search_for = array_get($_GET, 'search_for');
if ($search_for) {
	$values['search_for_username'] = '%' . $search_for . '%';
	$values['search_for_email'] = '%' . $search_for . '%';

	$res = db_query('
        SELECT COUNT(*) AS num
        FROM users AS u
        WHERE u.username LIKE :search_for_username OR u.email LIKE :search_for_email
    ', $values, false);

	$total_results = $res['num'];
} // no search
else {
	$total_results = db_count_num('users');
}

$from = get_pager_from($_GET);
$results_per_page = 100;

$users = db_query_fetch_all('
    SELECT
       u.id,
       u.email,
       u.username,
       u.added,
       u.last_active,
       u.class,
       u.enabled,
       u.competing,
       co.country_name,
       co.country_code,
       x.num_ips
    FROM users AS u
    INNER JOIN (
       SELECT u.id, COUNT(ipl.id) AS num_ips
       FROM users AS u
       LEFT JOIN ip_log AS ipl ON ipl.user_id = u.id
       GROUP BY u.id
    ) AS x USING (id)
    LEFT JOIN countries AS co ON co.id = u.country_id
    ' . ($search_for ? 'WHERE u.username LIKE :search_for_username OR u.email LIKE :search_for_email' : '') . '
    ORDER BY u.username ASC
    LIMIT ' . $from . ', ' . $results_per_page,
	$values
);

$total_results = isset($total_results) ? $total_results : count($users);

$base_url = '/admin/users';

pager($base_url, $total_results, $results_per_page, $from);

foreach ($users as $user) {
	echo '
    <tr>
        <td>
            <a href="/admin/user.php?id=', htmlspecialchars($user['id']), '">✎ ', htmlspecialchars($user['username']),
	country_flag_link($user['country_name'], $user['country_code']);

	if (!$user['enabled']) {
		echo '<span class="glyphicon glyphicon-remove has-tooltip" title="User Disabled" data-toggle="tooltip"></span>';
	} else if (!$user['competing']) {
		echo '<span class="glyphicon glyphicon-asterisk has-tooltip" title="Non-Competitor" data-toggle="tooltip"></span>';
	}

	echo '</a>
        </td>
        <td><a href="/admin/new_email.php?to=', htmlspecialchars($user['email']), '">', htmlspecialchars($user['email']), '</a></td>
        <td>', ($user['last_active'] ? date_time($user['last_active'], get_db_config('MELLIVORA_CONFIG_CTF_TIMEZONE')) : '<i>Never</i>'), '</td>
        <td class="center">', user_class_name($user['class']), '</td>
        <td class="center"><a href="/admin/ip_log.php?user_id=', htmlspecialchars($user['id']), '">', number_format($user['num_ips']), '</a></td>
    </tr>
    ';
}

echo '
      </tbody>
    </table>
     ';

foot();