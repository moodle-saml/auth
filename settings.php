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

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    if (!function_exists('save_in_saml_config_file')) {
        function save_in_saml_config_file() {
            global $CFG;
            $samlconfigfile = $CFG->dataroot.'/saml_config.php';
            if (file_exists($samlconfigfile)) {
                $contentfile = file_get_contents($samlconfigfile);
                $param = json_decode($contentfile, true);
            } else {
                $param = [];
            }

            $pluginconfig = get_config('auth_saml');
            $param['samllib'] = $pluginconfig->samllib;

            // Save saml settings in a file.
            $encodedparam = json_encode($param);
            file_put_contents($samlconfigfile, $encodedparam);
        }
    }

    global $CFG;
    /* Description */
    $settings->add(
        new admin_setting_heading(
            'auth_saml/plugindescription',
            '',
            new lang_string('auth_samldescription', 'auth_saml', $CFG->wwwroot)
        )
    );

    $name = 'auth_saml/samllib';
    $title = get_string('auth_saml_samllib', 'auth_saml');
    $description = get_string('auth_saml_samllib_description', 'auth_saml');
    $default = '/var/www/sp/simplesamlphp/lib';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);

    $setting->set_updatedcallback('save_in_saml_config_file');

    $settings->add($setting);

    $name = 'auth_saml/sp_source';
    $title = get_string('auth_saml_sp_source', 'auth_saml');
    $description = get_string('auth_saml_sp_source_description', 'auth_saml');
    $default = 'saml';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);

    $setting->set_updatedcallback('save_in_saml_config_file');

    $settings->add($setting);

    $name = 'auth_saml/supportcourses';
    $title = get_string('auth_saml_supportcourses', 'auth_saml');
    $description = get_string('auth_saml_supportcourses_description', 'auth_saml');
    $default = "nosupport";
    $choices = [
        "nosupport" => "nosupport",
        "internal" => "internal"
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $settings->add($setting);

    $name = 'auth_saml/username';
    $title = get_string('auth_saml_username', 'auth_saml');
    $description = get_string('auth_saml_username_description', 'auth_saml');
    $default = 'eduPersonPrincipalName';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);
    $settings->add($setting);

    $name = 'auth_saml/role';
    $title = get_string('auth_saml_role', 'auth_saml');
    $description = get_string('auth_saml_role_description', 'auth_saml');
    $default = null;
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);
    $settings->add($setting);

    $name = 'auth_saml/courses';
    $title = get_string('auth_saml_courses', 'auth_saml');
    $description = get_string('auth_saml_courses_description', 'auth_saml');
    $default = 'schacUserStatus';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);
    $settings->add($setting);

    $name = 'auth_saml/dosinglelogout';
    $title = get_string('auth_saml_dosinglelogout', 'auth_saml');
    $description = get_string('auth_saml_dosinglelogout_description', 'auth_saml');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);

    $setting->set_updatedcallback('save_in_saml_config_file');

    $settings->add($setting);

    $name = 'auth_saml/samllogoimage';
    $title = get_string('auth_saml_logo_path', 'auth_saml');
    $description = get_string('auth_saml_logo_path_description', 'auth_saml');
    $default = 'logo.gif';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);
    $settings->add($setting);

    $name = 'auth_saml/samllogoinfo';
    $title = get_string('auth_saml_logo_info', 'auth_saml');
    $description = get_string('auth_saml_logo_info_description', 'auth_saml');
    $default = 'SAML login';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);
    $settings->add($setting);

    $name = 'auth_saml/autologin';
    $title = get_string('auth_saml_autologin', 'auth_saml');
    $description = get_string('auth_saml_autologin_description', 'auth_saml');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'auth_saml/samlhookfile';
    $title = get_string('auth_saml_samlhookfile', 'auth_saml');
    $description = get_string('auth_saml_samlhookfile_description', 'auth_saml');
    $default = 'auth/saml/custom_hook.php';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);
    $settings->add($setting);

    $name = 'auth_saml/samllogfile';
    $title = get_string('auth_saml_logfile', 'auth_saml');
    $description = get_string('auth_saml_logfile_description', 'auth_saml');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);
    $settings->add($setting);

    $name = 'auth_saml/logextrainfo';
    $title = get_string('auth_saml_logextrainfo', 'auth_saml');
    $description = get_string('auth_saml_logextrainfo_description', 'auth_saml');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'auth_saml/dontdisplaytouser';
    $title = get_string('auth_saml_dontdisplaytouser', 'auth_saml');
    $description = get_string('auth_saml_dontdisplaytouser_description', 'auth_saml');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'auth_saml/disablejit';
    $title = get_string('auth_saml_disablejit', 'auth_saml');
    $description = get_string('auth_saml_disablejit_description', 'auth_saml');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    $name = 'auth_saml/syncusersfrom';
    $title = get_string('auth_saml_syncusersfrom', 'auth_saml');
    $description = get_string('auth_saml_syncusersfrom_description', 'auth_saml');
    $default = null;
    $choices = [];
    foreach (get_enabled_auth_plugins() as $name) {
        $plugin = get_auth_plugin($name);
        if (method_exists($plugin, 'sync_users')) {
            $choices[$name] = $name;
        }
    }
    if (!empty($choices)) {
        $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
        $settings->add($setting);
    }

    $name = 'auth_saml/ignoreinactivecourses';
    $title = get_string('auth_saml_ignoreinactivecourses', 'auth_saml');
    $description = get_string('auth_saml_ignoreinactivecourses_description', 'auth_saml');
    $default = true;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $settings->add($setting);

    // Display locking / mapping of profile fields.
    $authplugin = get_auth_plugin('saml');
    $help = get_string('auth_saml_attrmapping_head', 'auth_saml');
    $help .= get_string('auth_updatelocal_expl', 'auth');
    $help .= get_string('auth_fieldlock_expl', 'auth');
    $customuserfields = $authplugin->get_custom_user_profile_fields();
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields, $help, true, false, $customuserfields);

    require_once($CFG->dirroot.'/auth/saml/locallib.php');
    require_once($CFG->dirroot.'/auth/saml/classes/admin_setting_special_link.php');
    require_once($CFG->dirroot.'/auth/saml/classes/admin_setting_configtext_trim.php');
    require_once($CFG->dirroot.'/auth/saml/classes/admin_setting_configtextarea_trim.php');

    $settings->add(
        new admin_setting_heading(
            'auth_saml/rolemapping',
            new lang_string('auth_saml_rolemapping', 'auth_saml'),
            new lang_string('auth_saml_rolemapping_head', 'auth_saml')
        )
    );

    $roles = get_all_roles();
    if (!empty($roles)) {
        $settings->add(
            new auth_saml_admin_setting_special_link(
                'auth_saml/rolemapping_export',
                new lang_string('auth_saml_mapping_export', 'auth_saml'),
                $CFG->wwwroot.'/auth/saml/role_mappings_to_csv.php'
            )
        );

        foreach ($roles as $role) {
            $role = $role;
            
            $name = 'auth_saml/role_mapping_'.convert_to_valid_setting_name($role->shortname);
            $title = $role->shortname;
            $description = '';
            $default = null;
            $setting = new admin_setting_configtextarea_trim($name, $title, $description, $default, PARAM_RAW, '80', '1');
            $settings->add($setting);
        }
    }

    $settings->add(
        new admin_setting_heading(
            'auth_saml/coursemapping',
            new lang_string('auth_saml_coursemapping', 'auth_saml'),
            new lang_string('auth_saml_coursemapping_head', 'auth_saml')
        )
    );

    $courses = get_courses();

    if (!empty($courses)) {
        $settings->add(
            new auth_saml_admin_setting_special_link(
                'auth_saml/coursemapping_export',
                new lang_string('auth_saml_mapping_export', 'auth_saml'),
                $CFG->wwwroot.'/auth/saml/course_mappings_to_csv.php'
            )
        );

        foreach ($courses as $course) {
            if ($course->id == SITEID) {
                continue;
            }
            $name = 'auth_saml/course_mapping_'.convert_to_valid_setting_name($course->shortname);
            $title = $course->shortname;
            if (!empty($course->idnumber)) {
                $title .= ' - ' . $course->idnumber;
            }
            $description = '';
            $default = null;
            $setting = new admin_setting_configtextarea_trim($name, $title, $description, $default, PARAM_RAW, '80', '2');
            $settings->add($setting);
        }

        
        $settings->add(
            new auth_saml_admin_setting_special_link(
                'auth_saml/check_mapping_duplicates',
                new lang_string('auth_saml_check_mapping_duplicates', 'auth_saml'),
                $CFG->wwwroot.'/auth/saml/check_course_mappings_duplicates.php',
                true
            )
        );
    }

    require_once($CFG->dirroot.'/auth/saml/classes/admin_setting_special_javascript.php');
    $setting = new auth_saml_admin_setting_javascript();
    $settings->add($setting);
}
