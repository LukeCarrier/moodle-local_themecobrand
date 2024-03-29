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

use core_plugin_manager;
use moodleform;

defined('MOODLE_INTERNAL') || die;

require_once "{$CFG->libdir}/formslib.php";

class rule_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $framework    = $this->_customdata['framework'];
        $organisation = $this->_customdata['organisation'];
        $rule         = $this->_customdata['rule'];

        $mform->addElement('hidden', 'organisationid');
        $mform->setType('organisationid', PARAM_INT);
        $mform->setConstant('organisationid', $organisation->id);

        $mform->addElement('static', 'frameworkname',
                           static::get_string('frameworkname'));
        $mform->setType('frameworkname', PARAM_ALPHA);
        $mform->setConstant('frameworkname', $framework->fullname);

        $mform->addElement('static', 'organisationname',
                           static::get_string('organisationname'));
        $mform->setType('organisationname', PARAM_ALPHA);
        $mform->setConstant('organisationname', $organisation->fullname);

        $themes = array_merge(array(
            '__default__' => static::get_string('none'),
        ), static::get_themes());
        $mform->addElement('select', 'applytheme',
                           static::get_string('applytheme'), $themes);
        $mform->setDefault('applytheme', $rule->get_theme());
        $mform->setType('applytheme', PARAM_ALPHA);

        $this->add_action_buttons();
    }

    protected static function get_themes() {
        $pluginmgr = core_plugin_manager::instance();
        $themes = $pluginmgr->get_plugins_of_type('theme');

        return array_map(function($theme) {
            return $theme->displayname;
        }, $themes);
    }

    protected static function get_string($string) {
        return get_string($string, 'local_themecobrand');
    }

    public function save($data) {
        if (!property_exists($data, 'applytheme')) {
            $data->applytheme = '';
        }

        $rule->update($data->organisationid, $data->applytheme);
        $rule->commit();
    }
}
