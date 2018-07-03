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
 * Admin settings and defaults
 *
 * @package auth_saml
 * @copyright  2018 Enovation Solutions {@link http://enovation.ie}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $component_name = 'auth_saml';

    $yesno_options = array(
        '1' => new lang_string('yes'),
        '0' => new lang_string('no'),
    );
    $noyes_options = array(
        new lang_string('no'),
        new lang_string('yes'),
    );
    $no_options = array(
        new lang_string('no'),
    );

    $samlhookfile = $CFG->dirroot . '/auth/saml/custom_hook.php';

    $settings->add(
        new admin_setting_configtext(
            'auth_saml/samllib', 
            get_string("auth_saml_samllib", "auth_saml"), 
            get_string("auth_saml_samllib_description", "auth_saml"), 
            '', 
            PARAM_RAW_TRIMMED, 
            64
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'auth_saml/sp_source', 
            get_string("auth_saml_sp_source", "auth_saml"), 
            get_string("auth_saml_sp_source_description", "auth_saml"), 
            '', 
            PARAM_RAW_TRIMMED, 
            20
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'auth_saml/username', 
            get_string("auth_saml_username", "auth_saml"), 
            get_string("auth_saml_username_description", "auth_saml"), 
            '', 
            PARAM_RAW_TRIMMED, 
            20
        )
    );
    $settings->add(
        new admin_setting_configselect(
            'auth_saml/dosinglelogout', 
            get_string('auth_saml_dosinglelogout', 'auth_saml'), 
            get_string('auth_saml_dosinglelogout_description', 'auth_saml'), 
            '1', 
            $yesno_options
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'auth_saml/samllogoimage', 
            get_string("auth_saml_logo_path", "auth_saml"), 
            get_string("auth_saml_logo_path_description", "auth_saml"), 
            get_string("auth_saml_logo_path_default", "auth_saml"), 
            PARAM_RAW_TRIMMED, 
            64
        )
    );
    $settings->add(
        new admin_setting_configtextarea('auth_saml/samllogoinfo',
            get_string('auth_saml_logo_info', 'auth_saml'),
            get_string('auth_saml_logo_info_description', 'auth_saml'),
            get_string('auth_saml_logo_info_default', 'auth_saml'),
            PARAM_RAW_TRIMMED
        )
    );
    $settings->add(
        new admin_setting_configselect(
            'auth_saml/autologin', 
            get_string('auth_saml_autologin', 'auth_saml'), 
            get_string('auth_saml_autologin_description', 'auth_saml'), 
            0, 
            $noyes_options
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'auth_saml/samllogfile', 
            get_string('auth_saml_logfile', 'auth_saml'), 
            get_string('auth_saml_logfile_description', 'auth_saml'), 
            '', 
            PARAM_RAW_TRIMMED, 
            64
        )
    );
    $settings->add(
        new admin_setting_configtext(
            'auth_saml/samlhookfile', 
            get_string('auth_saml_samlhookfile', 'auth_saml'), 
            get_string('auth_saml_samlhookfile_description', 'auth_saml'), 
            $samlhookfile, 
            PARAM_RAW_TRIMMED, 
            64
        )
    );
    $settings->add(
        new admin_setting_configselect(
            'auth_saml/disablejit', 
            get_string('auth_saml_disablejit', 'auth_saml'), 
            get_string('auth_saml_disablejit_description', 'auth_saml'), 
            0, 
            $noyes_options
        )
    );
    $settings->add(
        new admin_setting_configselect(
            'auth_saml/supportcourses', 
            get_string('auth_saml_supportcourses', 'auth_saml'), 
            get_string('auth_saml_supportcourses_description', 'auth_saml'), 
            0, 
            $no_options
        )
    );
    $settings->add(
        new admin_setting_configselect(
            'auth_saml/syncusersfrom', 
            get_string('auth_saml_syncusersfrom', 'auth_saml'), 
            get_string('auth_saml_syncusersfrom_description', 'auth_saml'), 
            0, 
            $no_options
        )
    );
    $authplugin = get_auth_plugin('saml');
    display_auth_lock_options(
        $settings, 
        $authplugin->authtype, 
        $authplugin->userfields, 
        get_string('datamapping_description', 'auth_saml'), 
        true, 
        true, 
        $authplugin->get_custom_user_profile_fields()
    );
}
