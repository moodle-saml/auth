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

defined('MOODLE_INTERNAL') || die();

/**
 * Get the absolute path to the SAML hook file.
 *
 * @global stdClass $CFG
 * @param string $path
 *   The path to the SAML hook file, either absolute or relative to the
 *   directory root.
 * @return string
 *   The absolute path to the hook file.
 */
function resolve_samlhookfile($path) {
    global $CFG;

    if (strpos($path, '/') !== 0 && strpos($path, '\\') !== 0) {
        // This is a relative path because it doesn't with an / or an \.
        $path = $CFG->dirroot . DIRECTORY_SEPARATOR . $path;
    }

    return $path;
}

/**
 * Get a list of system roles assignable by the current or a specified user, including their localised names.
 *
 * @param integer|object $user A user id or object. By default (null) checks the permissions of the current user.
 * @return array $roles, each role as an array with id, shortname, localname, and settingname for the config value.
 */
function get_saml_assignable_role_names($user = null) {
    $roles = [];

    if ($assignableroles = get_assignable_roles(context_system::instance(), ROLENAME_SHORT, false, $user)) {
        $systemroles = role_fix_names(get_all_roles(), context_system::instance(), ROLENAME_ORIGINAL);
        foreach ($assignableroles as $shortname) {
            foreach ($systemroles as $systemrole) {
                if (property_exists($systemrole, $shortname) && $systemrole->shortname == $shortname) {
                    $roles[] = ['id' => $systemrole->id,
                                     'shortname' => $shortname,
                                     'localname' => $systemrole->localname,
                                     'settingname' => $shortname . 'context'];
                    break;
                }
            }
        }
    }

    return $roles;
}

function get_role_mapping_for_sync($pluginconfig, $addempties = false) {
    $rolemapping = [];

    foreach (get_all_roles() as $role) {
        $field = 'role_mapping_'.trim(strtolower(str_replace("-", "_", $role->shortname)));
        if (property_exists($pluginconfig, $field)) {
            $value = $pluginconfig->{"$field"};
            if (!empty($value) || $addempties) {
                $rolemapping[$role->shortname] = explode(",", $value);
            }
        }
    }

    return $rolemapping;
}

function get_all_courses_available() {
    /* return get_courses(); */

    global $DB;
    $query = "SELECT id, idnumber, shortname from {course} WHERE id !=".SITEID;
    $courses = $DB->get_records_sql($query);
    return $courses;
}

function get_course_mapping_for_sync($pluginconfig, $addempties = false) {
    $coursemapping = [];

    $courses = get_all_courses_available();

    foreach ($courses as $course) {
        $field = 'course_mapping_'.convert_to_valid_setting_name($course->shortname);
        if (property_exists($pluginconfig, $field)) {
            $value = $pluginconfig->{"$field"};
            if (!empty($value) || $addempties) {
                $coursemapping[$course->shortname] = explode(",", $value);
            }
        }
    }
    return $coursemapping;
}

function clean_values($values) {
    foreach ($values as $key => $value) {
        $values[$key] = trim($value);
    }
    return $values;
}

function convert_to_valid_setting_name($value) {
    $value = strtolower($value);
    $value = preg_replace('/\s+/', '', $value);
    $value = str_replace("-", "_", $value);
    $value = str_replace(array("á","é", "í", "ó", "ú"), array("a","e", "i", "o", "u"), $value);
    $value = str_replace(array("ä","ë", "ï", "ö", "ü"), array("a","e", "i", "o", "u"), $value);
    $value = str_replace("ñ", "n", $value);
    $value = preg_replace('/[^a-z0-9_]/', '', $value);
    return $value;
}