<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

head('Submissions');
menu_management();

$where = array();
if (array_get($_GET, 'only_needing_marking')) {
	$only_needing_marking = true;
	$where['automark'] = 0;
	$where['marked'] = 0;
} else {
	$only_needing_marking = false;
}

if (is_valid_id(array_get($_GET, 'user_id'))) {
	$where['user_id'] = $_GET['user_id'];
}

$query = '
    FROM submissions AS s
    LEFT JOIN users AS u on s.user_id = u.id
    LEFT JOIN challenges AS c ON c.id = s.challenge
';

if (!empty($where)) {
	$query .= 'WHERE ' . implode('=? AND ', array_keys($where)) . '=? ';
}

if (array_get($_GET, 'user_id')) {
	section_title('User submissions', button_link('List all submissions', 'submissions?only_needing_marking=0'));
} else if ($only_needing_marking) {
	section_title('Submissions in need of marking', button_link('List all submissions', 'submissions?only_needing_marking=0'));
} else {
	section_title('All submissions', button_link('Show only in need of marking', 'submissions?only_needing_marking=1'));
}

$num_subs = db_query_fetch_one('
    SELECT
       COUNT(*) AS num
    ' . $query,
	array_values($where)
);

$from = get_pager_from($_GET);
$results_per_page = 70;

pager('/admin/submissions', $num_subs['num'], $results_per_page, $from);

echo '<table id="files" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Challenge</th>
          <th>Username</th>
          <th>Added</th>
          <th>Flag</th>
          <th>Delete</th>
        </tr>
      </thead>
      <tbody>';

$submissions = db_query_fetch_all('
    SELECT
       s.id,
       u.id AS user_id,
       u.username,
       s.added,
       s.correct,
       s.flag,
       c.id AS challenge_id,
       c.title AS challenge_title
    ' . $query . '
    ORDER BY s.added DESC
    LIMIT ' . $from . ', ' . $results_per_page,
	array_values($where)
);

foreach ($submissions as $submission) {
	echo '<tr>
      <td><a href="/challenge.php?id=', htmlspecialchars($submission['challenge_id']), '">', htmlspecialchars($submission['challenge_title']), '</a></td>
      <td><a href="/admin/user.php?id=', htmlspecialchars($submission['user_id']), '">', htmlspecialchars($submission['username']), '</a></td>
      <td>', time_elapsed($submission['added']), ' ago</td>
      <td>
      <form method="post" action="/admin/actions/submissions" class="discreet-inline">
        <input type="hidden" name="action" value="', ($submission['correct'] ? 'mark_incorrect' : 'mark_correct'), '" />
        <input type="hidden" name="id" value="', htmlspecialchars($submission['id']), '" />
        <input type="hidden" name="from" value="', htmlspecialchars($_GET['from']), '" />';
	form_xsrf_token();

	if ($submission['correct']) {
		echo '<button type="submit" style="color: #222222; font-size:18px" title="Click to mark incorrect"
        class="has-tooltip" data-toggle="tooltip" data-placement="top">
        ', htmlspecialchars($submission['flag']), ' <i class="bi bi-check" style="font-size:20px"></i>
        </button>';
	} else {
		echo '<button type="submit" style="color: #F2542D; font-size:18px" title="Click to mark correct"
        class="has-tooltip" data-toggle="tooltip" data-placement="top">
        ', htmlspecialchars($submission['flag']), ' <i class="bi bi-x" style="color:#FF4242;font-size:20px;"></i>
        </button>';
	}

	echo '</form></td>

    <td>
    <form method="post" action="/admin/actions/submissions">';
	form_xsrf_token();
	echo '
              <input type="hidden" name="action" value="delete" />
              <input type="hidden" name="id" value="', htmlspecialchars($submission['id']), '" />
              <input type="hidden" name="from" value="', htmlspecialchars($_GET['from']), '" />
              <button type="submit" class="btn btn-xs btn-3">Delete</button>
          </form>
      </td>
    </tr>';
}

echo '</tbody>
    </table>';

foot();
