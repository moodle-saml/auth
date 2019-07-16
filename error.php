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

if (!defined('MOODLE_INTERNAL')) {
    // It must be included from a Moodle page.
    die('Direct access to this script is forbidden.');
}

function auth_saml_error($err, $urltogo = false, $logfile = '', $showerror = false) {
    global $CFG, $PAGE, $OUTPUT;

    $debug = false;
    if ((isset($CFG->debugdisplay) && $CFG->debugdisplay) || $showerror) {
        $debug = true;
    }

    if ($urltogo != false) {
        $site = get_site();
        if ($site === false || !isset($site->fullname)) {
            $sitename = '';
        } else {
            $sitename = $site->fullname;
        }
        $PAGE->set_title($sitename .':Error SAML Login');

        echo $OUTPUT->header();

        echo '<div style="margin:20px;font-weight: bold; color: red;">';
    }
    if (is_array($err)) {
        foreach ($err as $key => $messages) {
            if (!is_array($messages)) {
                if ($urltogo != false && ($debug || $key == 'course_enrollment')) {
                    echo $messages;
                }
                $msg = 'Moodle SAML module: '.$key.': '.$messages;
                auth_saml_log_error($msg, $logfile);
            } else {
                foreach ($messages as $message) {
                    if ($urltogo != false && ($debug || $key == 'course_enrollment')) {
                        echo $message.'<br>';
                    }
                    $msg = 'Moodle SAML module: '.$key.': '.$message;
                    auth_saml_log_error($msg, $logfile);
                }
            }
            echo '<br>';
        }
    } else {
        if ($urltogo != false) {
            echo $err;
        }
        $msg = 'Moodle SAML module: login: '.$err;
        auth_saml_log_error($msg, $logfile);
    }
    if ($urltogo != false) {
        echo '</div>';
        echo $OUTPUT->continue_button($urltogo);
        if ($debug && !$showerror) {
            print_string("auth_saml_disable_debugdisplay", "auth_saml");
        }
        echo $OUTPUT->footer();
        exit();
    }
}

function auth_saml_log_error($msg, $logfile) {
    global $CFG;
    // 0 - message  is sent to PHP's system logger, using the Operating System's system logging mechanism or a file.
    // 3 - message  is appended to the file destination.
    $destination = '';
    $errorlogtype = 0;
    if (isset($logfile) && !empty($logfile)) {
        if (substr($logfile, 0) == '/') {
            $destination = $logfile;
        } else {
            $destination = $CFG->dataroot . '/' . $logfile;
        }
        $errorlogtype = 3;
        $msg = auth_saml_decorate_log($msg);
    }
    error_log($msg, $errorlogtype, $destination);
}


function auth_saml_log_info($msg, $logfile) {
    global $CFG;
    if (isset($logfile) && !empty($logfile)) {
        if (substr($logfile, 0) == '/') {
            $destination = $logfile;
        } else {
            $destination = $CFG->dataroot . '/' . $logfile;
        }
        $msg = auth_saml_decorate_log($msg, 'info');
        file_put_contents($destination, $msg, FILE_APPEND);
    } else {
        syslog(LOG_INFO, $msg);
    }
}

function auth_saml_decorate_log($msg, $level = "error") {
    return $msg = date('D M d H:i:s  Y').' [client '.$_SERVER['REMOTE_ADDR'].'] ['.$level.'] '.$msg."\r\n";
}
