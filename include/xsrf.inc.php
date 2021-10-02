<?php

const CONST_XSRF_TOKEN_KEY = 'xsrf_token';

function form_xsrf_token() {
    echo '<input type="hidden" name="',CONST_XSRF_TOKEN_KEY,'" value="',htmlspecialchars($_SESSION[CONST_XSRF_TOKEN_KEY]),'" />';
}
//some people complained that is was CSRF and not XSRF....... D:
function validate_xsrf_token($token) {
    if ($_SESSION[CONST_XSRF_TOKEN_KEY] != $token) {
        log_exception(new Exception('Invalid XSRF/CSRF token. Was: "' . $token.'". Wanted: "' . $_SESSION[CONST_XSRF_TOKEN_KEY].'"'));
        message_error('XSRF/CSRF token mismatch');
        exit;
    }
}

function regenerate_xsrf_token() {
    $_SESSION[CONST_XSRF_TOKEN_KEY] = generate_random_string(64);
}