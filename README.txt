SAML Authentication for Moodle
-------------------------------------------------------------------------------
license: http://www.gnu.org/copyleft/gpl.html GNU Public License

Changes:
- 2008-10    : Created by Ny Media AS
- 2008-11-03 : Updated by Ny Media AS
- 2009-07-29 : added configuration options for sslib path and config path
               tightened up the session switching between ss and moodle
               Piers Harding <piers@catalyst.net.nz>
- 2010-11    : Rewrited by Yaco Sistemas.
- 2011-19    : Maintained by Sixto Martin Garcia <sixto.martin.garcia@gmail.com>
  

Requirements:
- simpleSAMLphp (http://rnd.feide.no/simplesamlphp).

Optional:
- SAML Enrollment for Moodle module (if want to support enrolment on course based on SAML attributes)


Notes:
- This plugin add SAML support to Moodle enabling a Service Provider using simpleSAMLphp endpoints.
- Uses IdP attribute "eduPersonPrincipalName" as username by default

Install instructions:

Check moodle_auth_saml.txt


Important for enrollment!!
==========================

This plugin suppose that the IdP send the courses data of the user in a attribute that can be configured but the pattern of the expected data is defined per the [RFC](https://tools.ietf.org/html/rfc6338)
urn:schac:userStatus:<country>:<domain>:<course_id>:<period>:<role>:<status>
You can change this pattern editing the saml_hook_get_course_info method from the custom_hook.php file.
