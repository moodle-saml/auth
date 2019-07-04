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

/*
SAML Authentication Plugin Custom Hook


This file acts as a hook for the SAML Authentication plugin. The plugin will
call the functions defined in this file in certain points in the plugin
lifecycle.

Use this sample file as a template. You should copy it and not modify it
in place since you may lost your changes in future updates.

To use this hook you have to go to the config form in the admin interface of
Moodle and set the full path to this file. Please note that the default value
for such a field is this custom_hook.php file itself.

You should not change the name of the funcions since that's the API the plugin
expect to exist and to use.

Read the comments of each function to discover when they are called and what
are they for.
*/

if (!defined('MOODLE_INTERNAL')) {
    // It must be included from a Moodle page.
    die('Direct access to this script is forbidden.');
}

/*
 name: saml_hook_attribute_filter
 arguments:
   - $saml_attributes: array of SAML attributes
 return value:
   - nothing
 purpose: this function allows you to modify the array of SAML attributes.
          You can change the values of them (e.g. removing the non desired
          urn parts) or you can even remove or add attributes on the fly.
*/
function saml_hook_attribute_filter(&$saml_attributes) {
    // DNI in schacPersonalUniqueID
    /*
    if(isset($saml_attributes['schacPersonalUniqueID'])) {
        foreach($saml_attributes['schacPersonalUniqueID'] as $key => $value) {
            $data = [];
            if(preg_match('/urn:mace:terena.org:schac:personalUniqueID:es:(.*):(.*)/', $value, $data)) {
                $saml_attributes['schacPersonalUniqueID'][$key] = $data[2];
                //DNI sin letra
                //$saml_attributes['schacPersonalUniqueID'][$key] = substr($value[2], 0, 8);
            }
            else {
                unset($saml_attributes['schacPersonalUniqueID'][$key]);
            }
        }
    }
    */

    // Set irisMailMainAddress as mail if it does not exists.
    if (!isset($saml_attributes['mail'])) {
        if (isset($saml_attributes['irisMailMainAddress'])) {
            $saml_attributes['mail'] = $saml_attributes['irisMailMainAddress'];
        }
    }

    // Set uid / eduPersonTargetedID / mail as eduPersonPrincipalName.
    if (!isset($saml_attributes['eduPersonPrincipalName'])) {
        if (isset($saml_attributes['uid'])) {
            $saml_attributes['eduPersonPrincipalName'] = $saml_attributes['uid'];
        } else if (isset($saml_attributes['eduPersonTargetedID'])) {
            $saml_attributes['eduPersonPrincipalName'] = $saml_attributes['eduPersonTargetedID'];
        } else if (isset($saml_attributes['mail'])) {
            $saml_attributes['eduPersonPrincipalName'] = $saml_attributes['mail'];
        }
    }
}

/*
 name: saml_hook_user_exists
 arguments:
   - $username: candidate name of the current user
   - $saml_attributes: array of SAML attributes
   - $user_exists: true if the $username exists in Moodle database
 return value:
   - true if you consider that this username should exist, false otherwise.
 purpose: this function let you change the logic by which the plugin thinks
          the user exists in Moodle. You can even change the username if
          the user exists but you want to recreate with another name.
*/
function saml_hook_user_exists(&$username, $saml_attributes, $user_exists) {
    return true;
}

/*
 name: saml_hook_authorize_user
 arguments:
    - $username: name of the current user
    - $saml_attributes: array of SAML attributes
    - $authorize_user: true if the plugin thinks this user should be allowed
 return value:
    - true if the user should be authorized or an error string explaining
      why the user access should be denied.
 purpose: use this function to deny the access to the current user based on
          the value of its attributes or any other reason you want. It is
	  very important that this function return either true or an error
	  message.
*/
function saml_hook_authorize_user($username, $saml_attributes, $authorize_user) {
    return true;
}

/*
 name: saml_hook_post_user_created
 arguments:
   - $user: object containing the Moodle user
   - $saml_attributes: array of SAML attributes
 return value:
   - nothing
 purpose: use this function if you want to make changes to the user object
          or update any external system for statistics or something similar.
*/
function saml_hook_post_user_created($user, $saml_attributes = []) {

}

/*
 name: saml_hook_get_course_info
 arguments:
   - $course: string that contains info about the course

 return array with the following indexes:
        0 - match      matched string
        1 - country    country info
        2 - domain     domain info
        3 - course_id  the course id to be mapped with moodle course
        4 - period     period of the course
        5 - role       role to be mappend with moodle role
        6 - status     'active' | 'inactive'
        7 - group      the group inside the course

  The auth/saml plugin save those data that will be available
  for the enrol/saml plugin.

  Right now only course_id, period, role and status are
  required, so if your Identity Provider don't retrieve country or domain info, return
  empty values for them Ex. alternative pattern
  Info: 'courseData:math1:2016-17:student:active'

  $regex = '/courseData:(.+):(.+):(.+):(.+):(.+):(.+)/';
  if (preg_match($regex, $course, $matches) {
    $regs = [];
    $regs[0] = $matches[0];
    $regs[1] = null;          // country
    $regs[2] = null;          // domain
    $regs[3] = $matches[1];   // course_id
    $regs[4] = $matches[2];   // period
    $regs[5] = $matches[3];   // role
    $regs[6] = $matches[4];   // status
    $regs[7] = null;          // group
  }
*/
function saml_hook_get_course_info($course) {
    $regs = null;

    $regex = '/urn:mace:terena.org:schac:userStatus:(.+):(.+):(.+):(.+):(.+):(.+)/';

    if (preg_match($regex, $course, $matches)) {
        $regs = $matches;
    }

    // Example retreving course from course_id
    // because course_id is like:  mat1-t1, mat1-t2 and t1 and t2 are
    // groups of course mat1
    // $course_id = $regs[3];
    // $data = explode("-", $course_id);
    // if (isset($data[1])) {
    //    $regs[7] = $data[1];
    // }

    return $regs;
}
