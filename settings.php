<?php

defined('MOODLE_INTERNAL') || die;



if ($ADMIN->fulltree) {
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

    $setting->set_updatedcallback(function () {
        global $CFG;
        $samlConfigFile = $CFG->dataroot.'/saml_config.php';
        if (file_exists($samlConfigFile)) {
            $contentfile = file_get_contents($samlConfigFile);
            $saml_param = json_decode($contentfile, true);
        } else {
            $saml_param = array();
        }

        $pluginconfig = get_config('auth_saml');
        $saml_param['samllib'] = $pluginconfig->samllib;

        // Save saml settings in a file
        $saml_param_encoded = json_encode($saml_param);
        file_put_contents($samlConfigFile, $saml_param_encoded);
    });

    $settings->add($setting);

    $name = 'auth_saml/sp_source';
    $title = get_string('auth_saml_sp_source', 'auth_saml');
    $description = get_string('auth_saml_sp_source_description', 'auth_saml');
    $default = 'saml';
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);

    $setting->set_updatedcallback(function () {
        global $CFG;
        $samlConfigFile = $CFG->dataroot.'/saml_config.php';
        if (file_exists($samlConfigFile)) {
            $contentfile = file_get_contents($samlConfigFile);
            $saml_param = json_decode($contentfile, true);
        } else {
            $saml_param = array();
        }

        $pluginconfig = get_config('auth_saml');
        $saml_param['sp_source'] = $pluginconfig->sp_source;

        // Save saml settings in a file
        $saml_param_encoded = json_encode($saml_param);
        file_put_contents($samlConfigFile, $saml_param_encoded);
    });

    $settings->add($setting);

    $name = 'auth_saml/supportcourses';
    $title = get_string('auth_saml_supportcourses', 'auth_saml');
    $description = get_string('auth_saml_supportcourses_description', 'auth_saml');
    $default = "nosupport";
    $choices = array(
        "nosupport" => "nosupport",
        "internal" => "internal"
    );
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

    $setting->set_updatedcallback(function () {
        global $CFG;
        $samlConfigFile = $CFG->dataroot.'/saml_config.php';
        if (file_exists($samlConfigFile)) {
            $contentfile = file_get_contents($samlConfigFile);
            $saml_param = json_decode($contentfile, true);
        } else {
            $saml_param = array();
        }

        $pluginconfig = get_config('auth_saml');
        $saml_param['dosinglelogout'] = $pluginconfig->dosinglelogout;

        // Save saml settings in a file
        $saml_param_encoded = json_encode($saml_param);
        file_put_contents($samlConfigFile, $saml_param_encoded);
    });

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
    $choices = array();
    foreach (get_enabled_auth_plugins() as $pluginname) {
        $plugin = get_auth_plugin($pluginname);
        if (method_exists($plugin, 'sync_users')) {
            $choices[$pluginname] = $pluginname;
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
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
            $help, true, false, $authplugin->get_custom_user_profile_fields());

    $settings->add(
        new admin_setting_heading('auth_saml/rolemapping',
            new lang_string('auth_saml_rolemapping', 'auth_saml'),
            new lang_string('auth_saml_rolemapping_head', 'auth_saml')
        )
    );

    foreach (get_all_roles() as $role) {
        $role = $role;
        $name = 'auth_saml/role_mapping_'.strtolower($role->shortname);
        $title = $role->shortname;
        $description = '';
        $default = null;
        $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);
        $settings->add($setting);
    }

    $settings->add(
        new admin_setting_heading('auth_saml/coursemapping',
            new lang_string('auth_saml_coursemapping', 'auth_saml'),
            new lang_string('auth_saml_coursemapping_head', 'auth_saml')
        )
    );

    foreach (get_courses() as $course) {
        $name = 'auth_saml/course_mapping_'.strtolower($course->shortname);
        $title = $course->shortname;
        if (!empty($course->idnumber)) {
            $title .=' - '.$course->idnumber;
        }
        $description = '';
        $default = null;
        $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_RAW);
        $settings->add($setting);
    }

    require_once($CFG->dirroot.'/auth/saml/classes/admin_setting_special_javascript.php');
    $setting = new auth_saml_admin_setting_javascript();
    $settings->add($setting);
}
