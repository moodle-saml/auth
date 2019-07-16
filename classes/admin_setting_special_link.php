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
 * Special setting for adding export link to mappings
 *
 * @package    auth_saml
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_saml_admin_setting_special_link extends admin_setting
{
    private $link;

    /**
     * not a setting, just text
     * @param string $name unique ascii name, either 'mysetting' for settings
     *                     that in config, or 'myplugin/mysetting' for ones
     *                     in config_plugins.
     * @param string $text text in box
     * @param string $link href of the link
     * @param string $newview  indicates if the link should open new windows
     */
    public function __construct($name, $text, $link, $newview = false) {
        $this->nosave = true;
        $this->link = $link;
        $this->newview = $newview;
        parent::__construct($name, '', $text, '');
    }

    /**
     * Always returns true
     * @return bool Always returns true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true
     * @return bool Always returns true
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Never write settings
     * @return string Always returns an empty string
     */
    public function write_setting($data) {
        // Do not write any setting.
        return '';
    }

    /**
     * Returns an HTML string
     * @return string Returns an HTML string
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;
        $context = new stdClass();
        $context->title = "$this->visiblename";
        $context->description = $this->description;
        $context->descriptionformatted = '<a href="'.$this->link.'" '.($this->newview ? 'target=_blank': '').'>' .
            highlight($query, markdown_to_html($this->description)) .
        '</a>';
        return $OUTPUT->render_from_template('core_admin/setting_heading', $context);
    }
}
