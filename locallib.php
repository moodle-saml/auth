<?php

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
    $roles = array();

    if ($assignableroles = get_assignable_roles(context_system::instance(), ROLENAME_SHORT, false, $user)) {
        $systemroles = role_fix_names(get_all_roles(), context_system::instance(), ROLENAME_ORIGINAL);
        foreach ($assignableroles as $shortname) {
            foreach ($systemroles as $systemrole) {
                if (property_exists($systemrole, $shortname) && $systemrole->shortname == $shortname) {
                    $roles[] = array('id' => $systemrole->id,
                                     'shortname' => $shortname,
                                     'localname' => $systemrole->localname,
                                     'settingname' => $shortname . 'context');
                    break;
                }
            }
        }
    }

    return $roles;
}

function get_role_mapping_for_sync($pluginconfig) {
    $role_mapping = array();

    foreach (get_all_roles() as $role) {
        $field = 'role_mapping_'.strtolower($role->shortname);
        if (property_exists($pluginconfig, $field)) {
            $value = $pluginconfig->{"$field"};
            if (!empty($value)) {
                $role_mapping[$role->shortname] = explode(",", $value);
            }
        }
    }

    return $role_mapping;
}

function get_all_courses_available() {
    // return get_courses();

    global $DB;
    $query = "SELECT id, idnumber, shortname from {course}";
    $courses = $DB->get_records_sql($query);
    return $courses;
}

function get_course_mapping_for_sync($pluginconfig) {
    $course_mapping = array();

    $courses = get_all_courses_available();

    foreach($courses as $course) {
        $field = 'course_mapping_'.strtolower($course->shortname);
        if (property_exists($pluginconfig, $field)) {
            $value = $pluginconfig->{"$field"};
            if (!empty($value)) {
                $course_mapping[$course->shortname] = explode(",", $value);
            }
        }
    }
    return $course_mapping;
}
