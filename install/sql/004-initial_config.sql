USE mellivora;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_CTF_START_TIME', '1584699200', 'string');
INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_CTF_END_TIME', '1985699206', 'string');
INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_CTF_TIMEZONE', 'Asia/Singapore', 'string');

INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_SHOW_SCOREBOARD', 1, 'bool');

INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_ACCOUNTS_SIGNUP_ALLOWED', 1, 'bool');
INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_EMAIL_WHITELIST_CHECK', 0, 'bool');
INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_EMAIL_REGEX_CHECK', 0, 'bool');

INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_CHALL_INITIAL_POINTS', 1000, 'int');
INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_CHALL_MINIMUM_POINTS', 100, 'int');
INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_CHALL_LOWER_BOUND', 0.1, 'float');
INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_CHALL_UPPER_BOUND', 0.7, 'float');

INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_ACCOUNTS_EMAIL_PASSWORD_ON_SIGNUP', 0, 'bool');
INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_MIN_USERNAME_LENGTH', 2, 'int');
INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_MAX_USERNAME_LENGTH', 30, 'int');
INSERT INTO config (config_key, config_value, config_value_type) VALUES ('MELLIVORA_CONFIG_ACCOUNTS_DEFAULT_ENABLED', 1, 'bool');

