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
 * Theme co-branding.
 *
 * Enables customised per-organisation theming for TotaraLMS.
 *
 * @author Luke Carrier <luke@tdm.co>
 * @copyright (c) The Development Manager Ltd
 * @license GPL v3
 */

namespace local_themecobrand;

use admin_root;
use admin_settingpage;
use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once "{$CFG->libdir}/adminlib.php";
require_once "{$CFG->libdir}/formslib.php";

/**
 * Theme settings form.
 */
class theme_form extends moodleform {
    /**
     * Theme settings page node.
     *
     * @var \admin_settingpage
     */
    protected $themesettings;

    /**
     * @override \moodleform
     */
    public function definition() {
        $mform        = $this->_form;
        $theme        = $this->_customdata['rule']->get_theme();
        $organisation = $this->_customdata['organisation'];

        $this->themesettings = static::theme_settings_load($theme);

        $mform->addElement('hidden', 'form', 'theme');
        $mform->setType('form', PARAM_ALPHA);

        $mform->addElement('hidden', 'organisationid', $organisation->id);
        $mform->setType('organisationid', PARAM_INT);

        $mform->addElement('html', $this->themesettings->output_html());

        $this->add_action_buttons();
    }

    /**
     * @override \moodleform
     */
    public function get_data() {
        if (($data = data_submitted()) && confirm_sesskey()) {
            return static::theme_settings_values($this->themesettings, $data);
        }
    }

    public function save($data) {
        /* loop through all the parameters, altering their properties as
         * appropriate, then store their values in our own table? */
    }

    /**
     * Source settings for the given theme.
     *
     * @param string $theme The name of the theme, e.g. "clean".
     *
     * @return \admin_settingpage The administration settings tree, from the
     *                            theme's settings page node.
     */
    protected static function theme_settings_load($theme) {
        global $CFG;

        /* Doing this inside a function will sandbox our environment a little
         * better than inlining it. */

        $name         = "themesetting{$theme}";
        $visiblename  = get_string('pluginname', "theme_{$theme}");
        $settingsfile = "{$CFG->dirroot}/theme/{$theme}/settings.php";

        $ADMIN    = new admin_root(true);
        $settings = new admin_settingpage($name, $visiblename);

        include $settingsfile;

        return $settings;
    }

    /**
     * Extract values for the settings tree from posted data.
     *
     * @param \admin_settingpage $node
     * @param mixed[]            $data
     *
     * @return
     */
    protected static function theme_settings_values($node, $data) {
        return admin_find_write_settings($node, $data);
    }
}
