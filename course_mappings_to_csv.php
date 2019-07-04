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

// We load all moodle config and libs.
require_once(dirname(dirname(__DIR__)).'/config.php');
require_once($CFG->dirroot.'/auth/saml/locallib.php');

// Validate that the user has admin rights.
if (!is_siteadmin()) {
    die('Only admins can execute this action.');
}

$pluginconfig = get_config('auth_saml');

$coursemapping = get_course_mapping_for_sync($pluginconfig, true);

$filename = "course_saml_mappings_" . date('Y-m-d') . ".csv";
// Create a file pointer.
$f = fopen('php://memory', 'w');
$delimiter = ";";

// Set column headers.
$coursefields = ['Course at Moodle', 'Course at IdP'];

fputcsv($f, $coursefields, $delimiter);

foreach ($coursemapping as $key => $values) {
    foreach ($values as $value) {
        fputcsv($f, [$key, trim($value)], $delimiter);
    }
}

// Move back to beginning of file.
fseek($f, 0);

// Set headers to download file rather than displayed.
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '";');

// Output all remaining data on a file pointer.
fpassthru($f);
exit;
