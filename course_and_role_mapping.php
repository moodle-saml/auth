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

$rolemapping = get_role_mapping_for_sync($pluginconfig);
$coursemapping = get_course_mapping_for_sync($pluginconfig);

$mappedroles = [];
$mappedcourses = [];

foreach ($samlcourses as $key => $course) {
    $mappedrole = $mappedcourseid = null;
    if (function_exists('saml_hook_get_course_info')) {
        $regs = saml_hook_get_course_info($course);
        if ($regs) {
            list($match, $country, $domain, $courseid, $period, $role, $status) = $regs;
            $group = null;
            if (isset($regs[7])) {
                $group = $regs[7];
            }

            if (!empty($courseid)) {
                foreach ($rolemapping as $shortname => $values) {
                    if (in_array($role, $values)) {
                        $mappedrole = $shortname;
                        break;
                    }
                }

                $mappedcourseids = [];
                foreach ($coursemapping as $id => $values) {
                    if (in_array($courseid, $values)) {
                        $mappedcourseids[] = $id;
                    }
                }

                if (isset($status) && isset($mappedrole) && !empty($mappedcourseids)) {
                    if (!in_array($mappedrole, $mappedroles)) {
                        $mappedroles[] = $mappedrole;
                    }
                    foreach ($mappedcourseids as $mappedcourseid) {
                        $mappedcourses[$mappedrole][$status][$mappedcourseid] = [
                            'country' => $country,
                            'domain' => $domain,
                            'course_id' => $mappedcourseid,
                            'period' => $period,
                            'role' => $mappedrole,
                            'status' => $status,
                            'group' => $group
                        ];

                        if (!$anycourseactive && $status == 'active') {
                              $anycourseactive = true;
                        }
                    }
                } else if (!isset($status)) {
                    $err['course_enrollment'][] = get_string('auth_saml_status_not_found', 'auth_saml');
                } else if (!isset($role)) {
                    $err['course_enrollment'][] = get_string('auth_saml_role_not_found', 'auth_saml');
                } else {
                    $strobj = new stdClass();
                    $strobj->course = '('.$courseid.' -- '.$period.')';
                    $strobj->user = $username;
                    $err['course_enrollment'][] = get_string('auth_saml_course_not_found', 'auth_saml', $strobj);
                }
            }
        }
    } else {
        $err['course_enrollment'][] = get_string('auth_saml_hook_not_defined', 'auth_saml');
    }
}

unset($samlcourses);
