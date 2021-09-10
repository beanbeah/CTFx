<?php

require('../../../include/mellivora.inc.php');

enforce_authentication(CONST_USER_CLASS_MODERATOR);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    validate_xsrf_token($_POST[CONST_XSRF_TOKEN_KEY]);

    if ($_POST['action'] === 'change_times') {

        require_fields(array('ctf_start_time'), $_POST);
        require_fields(array('ctf_end_time'), $_POST);

        //strtotime should "sanitise" our values
        $from = strtotime($_POST['ctf_start_time']);
        $end = strtotime($_POST['ctf_end_time']);

        if($_POST['write_to_config']){
            //write to config file, kinda hacky atm
            $concat_from = 's+^Config::set(\'"\'"\'MELLIVORA_CONFIG_CTF_START_TIME\'"\'"\', .*$+Config::set(\'"\'"\'MELLIVORA_CONFIG_CTF_START_TIME\'"\'"\', '.$from.');+g';

            shell_exec("sed -i '{$concat_from}' /var/www/ctfx/include/config/config.inc.php 2>/dev/null >/dev/null &");

            $concat_to = 's+^Config::set(\'"\'"\'MELLIVORA_CONFIG_CTF_END_TIME\'"\'"\', .*$+Config::set(\'"\'"\'MELLIVORA_CONFIG_CTF_END_TIME\'"\'"\', '.$end.');+g';

            shell_exec("sed -i '{$concat_to}' /var/www/ctfx/include/config/config.inc.php 2>/dev/null >/dev/null &");
        }

        db_update_all (
            'challenges',
            array(
                'available_from'=>$from,
                'available_until'=>$end
            )
        );

        redirect('/admin/edit_ctf.php?generic_success=1');
    }

    else if ($_POST['action'] === 'scoreboard_freeze') {
        //write to config file, quite hacky at the moment
        if ($_POST['freeze']){
            $concat_hide = 's+^Config::set(\'"\'"\'MELLIVORA_CONFIG_SHOW_SCOREBOARD\'"\'"\', .*$+Config::set(\'"\'"\'MELLIVORA_CONFIG_SHOW_SCOREBOARD\'"\'"\', false);+g';
            shell_exec("sed -i '{$concat_hide}' /var/www/ctfx/include/config/config.inc.php 2>/dev/null >/dev/null &");
        }
        else {
            $concat_show = 's+^Config::set(\'"\'"\'MELLIVORA_CONFIG_SHOW_SCOREBOARD\'"\'"\', .*$+Config::set(\'"\'"\'MELLIVORA_CONFIG_SHOW_SCOREBOARD\'"\'"\', true);+g';
            shell_exec("sed -i '{$concat_show}' /var/www/ctfx/include/config/config.inc.php 2>/dev/null >/dev/null &");

        }
        
        redirect('/admin/edit_ctf.php?generic_success=1');

    }
}
