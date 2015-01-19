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
use lang_string;

defined('MOODLE_INTERNAL') || die;

/**
 * Utility methods.
 */
class util {
    /**
     * Obtain a theme settings form.
     *
     * @param string $theme The name of the theme, e.g. "clean".
     *
     * @return string The form markup.
     */
    public static function theme_settings_form($theme) {
        global $CFG;

        $name         = "themesetting{$theme}";
        $visiblename  = new lang_string('pluginname', "theme_{$theme}");
        $settingsfile = "{$CFG->dirroot}/theme/{$theme}/settings.php";

        $ADMIN    = new admin_root(true);
        $settings = new admin_settingpage($name, $visiblename);

        include $settingsfile;

        return $settings->output_html();
    }
}
