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
    ['filename' => 'course_saml_mappings.csv', 'mode' => 'add', 'help' => false],
    ['f' => 'file', 'm'=> 'mode', 'h' => 'help']
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
-m, --mode            Mode. Replace or add the mapping values from the plugin
                      with the values on the csv.   
                      Options: add|replace   Default: add 
-h, --help            Print out this help
";
    echo $help;
    die;
}

if (!empty($options['filename'])) {
    $filename = $options['filename'];
} else {
    $filename = 'course_saml_mappings.csv';
}

if (!file_exists($filename)) {
    print($filename." file not found");
    die;
}

if (!empty($options['mode'])) {
    if (!in_array($options['mode'], ['add', 'replace'])) {
        print "Invalid mode, valid options are:  add | replace";
        die;
    }
    $mode = $options['mode'];
} else {
    $mode = 'add';
}

$f = fopen($filename, "r");
if ($f === false) {
    print("Error reading ".$filename);
    exit();
}

$mapping = [];
$header = true;
while (($data = fgetcsv($f, 1000, ";")) !== false) {
    if ($header) {
        $numfields = count($data);
        if ($numfields != 2) {
            print("Unexpexted number of fields: ".$numfields);
            exit();
        }
        $header = false;
        continue;
    }

    $moodlecourse = strtolower($data[0]);
    $idpcourses = explode(",", $data[1]);
    if (array_key_exists($moodlecourse, $mapping)) {
        $mapping[$moodlecourse] = array_merge($mapping[$moodlecourse], $idpcourses);
    } else {
        $mapping[$moodlecourse] = $idpcourses;
    }
}
fclose($f);

require_once($CFG->dirroot.'/auth/saml/locallib.php');

$pluginconfig = get_config('auth_saml');
$coursemapping = get_course_mapping_for_sync($pluginconfig);

foreach ($mapping as $moodlecourse => $idpcourses) {
    $values = clean_values($idpcourses);
    if ($mode == 'add') {
        if (isset($coursemapping[$moodlecourse])) {
            $values = array_unique(array_merge($values, clean_values($coursemapping[$moodlecourse])));
        }
    }

    set_config('course_mapping_'.$moodlecourse, implode(",", $values), 'auth_saml');
}
