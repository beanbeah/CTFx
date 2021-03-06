<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

head('Email Whitelist');
menu_management();
section_title('List of Emails', button_link('Add email', 'new_email_whitelist'));


echo '
    <table id="rules" class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Email</th>
          <th>Enabled</th>
          <th>Manage</th>
        </tr>
      </thead>
      <tbody>
    ';

$emails = db_query_fetch_all('
    SELECT
       re.id,
       re.email,
       re.enabled
    FROM email_list AS re');

foreach ($emails as $email) {
	echo '
    <tr>
        <td>', htmlspecialchars($email['email']), '</td>
        <td>', ($email['enabled'] ? 'Yes' : 'No'), '</td>
        <td>
            <a href="edit_email_whitelist.php?id=', htmlspecialchars($email['id']), '" class="btn btn-xs btn-primary">Edit</a>
        </td>
    </tr>
    ';
}

echo '
      </tbody>
    </table>
     ';

foot();
