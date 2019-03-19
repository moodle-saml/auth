<?php
defined('MOODLE_INTERNAL') || die();
/**
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_auth_saml_upgrade($oldversion) {

    if($oldversion < 2018020601){
        upgrade_fix_config_auth_plugin_names('saml');
        upgrade_fix_config_auth_plugin_defaults('saml');
        upgrade_plugin_savepoint(true, 2018020601, 'auth', 'saml');
    }

    return true;

}
