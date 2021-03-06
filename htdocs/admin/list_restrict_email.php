<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

head('Email signup rules');
menu_management();
section_title('Email signup rules', button_link('New rule', 'new_restrict_email'));


message_inline('Rules in list below are applied top-down. Rules further down on the list override rules above.
                     List is ordered by "priority". A higher "priority" value puts a rule further down the list.
                     Rules are PCRE regex. Example: ^.+@.+$');

echo '
    <table id="rules" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Rule</th>
          <th>Added</th>
          <th>Added by</th>
          <th>Priority</th>
          <th>Enabled</th>
          <th>Manage</th>
        </tr>
      </thead>
      <tbody>
    ';

$rules = db_query_fetch_all('
    SELECT
       re.id,
       re.added,
       re.added_by,
       re.rule,
       re.enabled,
       re.priority,
       u.username
    FROM restrict_email AS re
    LEFT JOIN users AS u ON re.added_by = u.id
    ORDER BY re.priority ASC'
);

foreach ($rules as $rule) {
	echo '
    <tr>
        <td>', htmlspecialchars($rule['rule']), '</td>
        <td>', date_time($rule['added'], get_db_config('MELLIVORA_CONFIG_CTF_TIMEZONE')), '</td>
        <td>', htmlspecialchars($rule['username']), '</td>
        <td>', number_format($rule['priority']), '</td>
        <td>', ($rule['enabled'] ? 'Yes' : 'No'), '</td>
        <td>
            <a href="edit_restrict_email.php?id=', htmlspecialchars($rule['id']), '" class="btn btn-xs btn-primary">Edit</a>
        </td>
    </tr>
    ';
}

echo '
      </tbody>
    </table>
     ';

foot();
