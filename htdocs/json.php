<?php

require('../include/mellivora.inc.php');

if (Config::get('MELLIVORA_CONFIG_SHOW_SCOREBOARD') === false) {
	enforce_authentication(CONST_USER_CLASS_MODERATOR);
}

login_session_refresh();

header('Content-type: application/json');

if (!isset($_GET['view'])) {
	echo json_error(lang_get('please_request_view'));
	exit;
}
if ($_GET['view'] == 'scores') {
		json_scoreboard();
		// To make the scoreboard fully CTFTime compatible you can run this python3 one-liner to encode unicode chars to \uXXXX:
		// open("ctftime.json","wb").write(open("ctfx.json","r",encoding='utf-8').read().encode("ascii","backslashreplace").replace(b"\\x",b"\\u00"))
} else if ($_GET['view'] == 'graph') {
		json_score_graph();
} else {
	echo json_error(lang_get('please_request_view'));
	exit;
}
