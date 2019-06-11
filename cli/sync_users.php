<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @author  Erlend Strømsvik - Ny Media AS
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package auth/saml
 *
 * Authentication Plugin: SAML based SSO Authentication
 *
 * Authentication using SAML2 with SimpleSAMLphp.
 *
 * Based on plugins made by Sergio Gómez (moodle_ssp) and Martin Dougiamas (Shibboleth).
 */

/**
 * SAML user sync script.
 *
 * This script is meant to be called from a cronjob to sync moodle with a
 * selected. Users cannot be synced from SAML, but can be synched from
 * for example an external database or from LDAP. By using this script,
 * user data will be fetched from the external source, but users will
 * be authenticated by SAML. Remember to enable the external module, and
 * enter all required settings in that module.
 *
 * Sample cron entry:
 * # 5 minutes past 4am
 * 5 4 * * * $sudo -u www-data /usr/bin/php /var/www/moodle/auth/saml/cli/sync_users.php
 *
 * Notes:
 *   - it is required to use the web server account when executing PHP CLI scripts
 *   - you need to change the "www-data" to match the apache user account
 *   - use "su" if "sudo" not available
 *   - If you have a large number of users, you may want to raise the memory limits
 *     by passing -d momory_limit=256M
 *   - For debugging & better logging, you are encouraged to use in the command line:
 *     -d log_errors=1 -d error_reporting=E_ALL -d display_errors=0 -d html_errors=0
 *   - If you have a large number of users, you may want to raise the memory limits
 *     by passing -d momory_limit=256M
 *   - For debugging & better logging, you are encouraged to use in the command line:
 *     -d log_errors=1 -d error_reporting=E_ALL -d display_errors=0 -d html_errors=0
 *
 * @package    auth_saml
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

// Global moodle config file.
require(dirname(dirname(dirname(__DIR__))).'/config.php');

require_once("$CFG->libdir/clilib.php");

if (!is_enabled_auth('saml')) {
    error_log('[AUTH SAML] '. get_string('pluginnotenabled', 'auth_saml'));
    die;
}

list($options, $unrecognized) = cli_get_params(
    ['noupdate' => false, 'verbose' => false, 'help' => false],
    ['n' => 'noupdate', 'v' => 'verbose', 'h' => 'help']
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

// Ensure errors are well explained.
set_debugging(DEBUG_DEVELOPER, true);


if ($options['help']) {
    $help = "Execute user account sync via another auth module. The users
             are fetched from an external source such as LDAP or an external
             DB, and authenticate via SAML.

Options:
-n, --noupdate        Skip update of existing users
-v, --verbose         Print verbose progress information
-h, --help            Print out this help

Example:
\$ sudo -u www-data /usr/bin/php auth/saml/cli/sync_users.php

Sample cron entry:
# 5 minutes past 4am
5 4 * * * sudo -u www-data /usr/bin/php /var/www/moodle/auth/saml/cli/sync_users.php
";

    echo $help;
    die;
}

$enabledupdate = empty($options['noupdate']);

$config = get_config('auth_saml');
if (!empty($config->syncusersfrom)) {
    $otherauth = get_auth_plugin($config->syncusersfrom);
    $otherauth->authtype = 'saml';

    $method = new ReflectionMethod($otherauth, 'sync_users');

    switch ($method->getNumberOfParameters()) {
        case 1:
            // LDAP takes one arg.
            $otherauth->sync_users($enabledupdate);
            break;
        case 2:
            // DB takes two.
            if (empty($options['verbose'])) {
                $trace = new null_progress_trace();
            } else {
                $trace = new text_progress_trace();
            }
            $otherauth->sync_users($trace, $enabledupdate);
            break;
        default:
            error_log('[AUTH SAML] '. get_string('unknownplugin', 'auth_saml') . $config->syncusersfrom);
    }
} else {
    error_log('[AUTH SAML] '. get_string('syncfromnotenabled', 'auth_saml'));
}
