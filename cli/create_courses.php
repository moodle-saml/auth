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
global $CFG, $DB;
require_once("$CFG->libdir/clilib.php");

list($options, $unrecognized) = cli_get_params(
    ['filename' => 'mdl_course_mapping.csv', 'help' => false],
    ['f' => 'file', 'h' => 'help']
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

set_debugging(DEBUG_DEVELOPER, true);

if ($options['help']) {
    $help = "Execute course mapping sync for the SAML plugin.

Options:
-f, --filename        Name of the CSV file that contains course mapping
-h, --help            Print out this help
";
    echo $help;
    die;
}

if (!empty($options['filename'])) {
    $filename = $options['filename'];
} else {
    $filename = 'mdl_course_mapping.csv';
}

if (!file_exists($filename)) {
    print($filename." file not found");
    die;
}

$f = fopen($filename, "r");
if ($f === false) {
    print("Error reading ".$filename);
    exit();
}

$moodlecourses = [];
while (($data = fgetcsv($f, 1000, ",")) !== false) {
    $moodlecourses[] = $data[3];
}
fclose($f);

$moodlecourses = array_unique($moodlecourses);

require_once("$CFG->dirroot/course/lib.php");

foreach ($moodlecourses as $moodlecourse) {
    $exists = $DB->record_exists('course', array('shortname' => $moodlecourse));
    if (!$exists) {
        $coursedata = new stdClass();
        $coursedata->shortname = $moodlecourse;
        $coursedata->fullname = $moodlecourse;
        $coursedata->category = 2;
        create_course($coursedata);
    }
}
