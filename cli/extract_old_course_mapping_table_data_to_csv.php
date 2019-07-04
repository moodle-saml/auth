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

define('CLI_SCRIPT', true);

// Global moodle config file.
require(dirname(dirname(dirname(__DIR__))).'/config.php');
global $CFG;
require_once("$CFG->libdir/clilib.php");

list($options, $unrecognized) = cli_get_params(
    ['tablename' => 'mdl_course_mapping', 'filename' => 'extacted_old_course_saml_mappings.csv', 'help' => false],
    ['t' => 'table', 'f' => 'file', 'h' => 'help']
);


if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

set_debugging(DEBUG_DEVELOPER, true);

if ($options['help']) {
    $help = "Extract course mapping data from the old SQL table of the SAML plugin.

Options:
-t, --table           Name of SQL table that contains the course mapping info
                      gonna be saved
-f, --filename        Name of the CSV file where the course data mapping
                      gonna be saved
-h, --help            Print out this help
";
    echo $help;
    die;
}

if (!empty($options['filename'])) {
    $filename = $options['filename'];
} else {
    $filename = 'extacted_old_course_saml_mappings.csv';
}

if (!empty($options['table'])) {
    $table = $options['table'];
} else {
    $table = 'mdl_course_mapping';
}

if (file_exists($filename)) {
    print("The file ".$filename.' already exists');
    die;
}

$f = fopen($filename, "w");
if ($f === false) {
    print("Error managing ".$filename);
    exit();
}

global $DB;
$query = "SELECT course_mapping_id, saml_course_id, saml_course_period, lms_course_id from ".$table;
$coursemapping = $DB->get_records_sql($query);
if (empty($coursemapping)) {
    print ("No course mapping data found");
    die;
}

$delimiter = ";";

// Set column headers.
$coursefields = ['Course at Moodle', 'Course at IdP', 'Period at IdP'];

fputcsv($f, $coursefields, $delimiter);

foreach ($coursemapping as $entry) {
    fputcsv($f, [$entry->lms_course_id, trim($entry->saml_course_id), trim($entry->saml_course_period)], $delimiter);
}

// Move back to beginning of file.
fseek($f, 0);
fpassthru($f);
