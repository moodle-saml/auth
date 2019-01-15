<?php
/**
 * @author Erlend Strømsvik - Ny Media AS
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package auth/saml
 * @version 1.0
 *
 * Authentication Plugin: SAML based SSO Authentication
 *
 * Authentication using SAML2 with SimpleSAMLphp.
 *
 * Based on plugins made by Sergio Gómez (moodle_ssp) and Martin Dougiamas (Shibboleth).
 *
 * 2008-10  Created
 * 2009-07  added new configuration options.  Tightened up the session handling
**/

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); //  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');

/**
 * SimpleSAML authentication plugin.
**/
class auth_plugin_saml extends auth_plugin_base {

    /**
    * Constructor.
    */
    public function __construct() {
		$this->authtype = 'saml';
        $this->roleauth = 'auth_saml';
        $config = get_config('auth_saml');
        $legacyconfig = get_config('auth/saml');
        $this->config = (object)array_merge((array)$legacyconfig, (array)$config);
    }

    /**
    * Old syntax of class constructor. Deprecated in PHP7.
    *
    * @deprecated since Moodle 3.1
    */
    public function auth_plugin_saml() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    /**
    * Returns true if the username and password work and false if they are
    * wrong or don't exist.
    *
    * @param string $username The username (with system magic quotes)
    * @param string $password The password (with system magic quotes)
    * @return bool Authentication success or failure.
    */
    function user_login($username, $password) {
        global $SESSION;
	    // if true, user_login was initiated by saml/index.php
	    if(isset($SESSION->auth_saml_login) && $SESSION->auth_saml_login) {
	        unset($SESSION->auth_saml_login);
	        return TRUE;
	    }

	    return FALSE;
    }


    /**
    * Returns the user information for 'external' users. In this case the
    * attributes provided by Identity Provider
    *
    * @return array $result Associative array of user data
    */
    function get_userinfo($username) {
        global $SESSION;
	    if($login_attributes = $SESSION->auth_saml_login_attributes) {
	        $attributemap = $this->get_attributes();
	        $result = array();

	        foreach ($attributemap as $key => $value) {
		        if(isset($login_attributes[$value]) && $attribute = $login_attributes[$value][0]) {
		            $result[$key] = $attribute;
		        } else {
		            $result[$key] = '';
		        }
	        }

	        unset($SESSION->auth_saml_login_attributes);

	        $result["username"] = $username;
	        return $result;
	    }

	    return FALSE;
    }

    /*
    * Returns array containg attribute mappings between Moodle and Identity Provider.
    */
    function get_attributes() {
        $configarray = (array) $this->config;

        if(isset($this->userfields)) {
            $fields = $this->userfields;
        }
        else {
            $fields = array("firstname", "lastname", "email", "phone1", "phone2",
                "department", "address", "city", "country", "description",
                "idnumber", "lang", "guid", "web", "skype", "yahoo", "msn",
                            "aim", "icq");
        }

        $fields = array_merge($fields, $this->get_custom_user_profile_fields());

        $moodleattributes = array();
        foreach ($fields as $field) {
            if (isset($configarray["field_map_$field"])) {
                $moodleattributes[$field] = trim($configarray["field_map_$field"]);
            }
        }

        return $moodleattributes;
    }

    /**
    * Returns true if this authentication plugin is 'internal'.
    *
    * @return bool
    */
    function is_internal() {
	    return false;
    }

    /**
    * Returns true if this authentication plugin can change the user's
    * password.
    *
    * @return bool
    */
    function can_change_password() {
	    return false;
    }

    function pre_loginpage_hook() {
        // If Force Login is on then we can safely jump directly to the SAML IdP
        if (isset($this->config->autologin) && $this->config->autologin) {
            global $CFG, $SESSION;
            $samlurl = $CFG->wwwroot.'/auth/saml/index.php?wantsurl=' . urlencode($SESSION->wantsurl);
            redirect($samlurl);
        }
    }

    function loginpage_hook() {
	    global $CFG;

        if (empty($CFG->alternateloginurl) && !(isset($_GET['saml']) && $_GET['saml'] === 'false')) {
            $CFG->alternateloginurl = $CFG->wwwroot.'/auth/saml/login.php';
        }

	    // Prevent username from being shown on login page after logout
	    $CFG->nolastloggedin = true;
    }

    function logoutpage_hook() {
        global $CFG;

	    if(isset($this->config->dosinglelogout) && $this->config->dosinglelogout) {
	        set_moodle_cookie('nobody');
	        require_logout();
	        redirect($CFG->wwwroot.'/auth/saml/index.php?logout=1');
	    }
    }

    /**
    * Cleans and returns first of potential many values (multi-valued attributes)
    *
    * @param string $string Possibly multi-valued attribute from Identity Provider
    */
    function get_first_string($string) {
	    $list = split( ';', $string);
	    $clean_string = trim($list[0]);

	    return $clean_string;
    }
}
