<?php

require('../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

head('Site management');
menu_management();
section_title('Bulk Registration');
message_inline('Team names and Emails must be comma delimited.');
form_start('/admin/actions/bulk_register');
form_input_text('Email');
form_input_text('team_name');
$types = db_query_fetch_all('SELECT * FROM user_types ORDER BY title ASC');
form_select($types, 'user_type', 'id', $user['user_type'], 'title');
$opts = db_query_fetch_all('SELECT * FROM countries ORDER BY country_name ASC');
form_select($opts, 'Country', 'id', $user['country_id'], 'country_name');
form_hidden('action', 'new');
form_button_submit('Add Users');
form_end();
foot();
