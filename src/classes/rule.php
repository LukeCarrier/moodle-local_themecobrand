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

use theme_config;

defined('MOODLE_INTERNAL') || die;

/**
 * Co-branding theme rule.
 */
class rule {
    // DB tables (as per install.xml)
    const TABLE_RULES = 'local_themecobrand_rules';

    protected $id;
    protected $applytheme;
    protected $organisationid;

    public static function from_organisation_id($organisationid) {
        global $DB;

        $record = $DB->get_record(static::TABLE_RULES, array('organisationid' => $organisationid), '*', MUST_EXIST);

        $instance = new static();
        $instance->id             = $record->id;
        $instance->organisationid = $record->organisationid;

        return $instance;
    }

    public static function from_user_id($userid) {
        global $DB;

        // get organisation path, which includes IDs of all parents
        $sql = <<<SQL
SELECT o.path
FROM {pos_assignment} pa
LEFT JOIN {org} o
    ON o.id = pa.organisationid
WHERE pa.userid = ?
SQL;
        try {
            $organisationpath = $DB->get_field_sql($sql, array($userid), MUST_EXIST);
        } catch (dml_missing_record_exception $e) {
            return;
        }

        $parentids = array_reverse(explode('/', $organisationpath));
        array_pop($parentids);

        if (count($parentids) === 0) {
            return;
        }

        list($insql, $params) = $DB->get_in_or_equal($parentids);
        $sql = <<<SQL
SELECT organisationid, applytheme, id
FROM {local_themecobrand_rules}
WHERE organisationid {$insql}
SQL;
        $parentrules = $DB->get_records_sql($sql, $params);

        foreach ($parentids as $parentid) {
            if (array_key_exists($parentid, $parentrules)) {
                $record = $parentrules[$parentid];

                $instance = new static();
                $instance->update_id($record->id);
                $instance->update($record->organisationid, $record->applytheme);

                return $instance;
            }
        }
    }

    public function update($organisationid, $applytheme) {
        $this->organisationid = $organisationid;
        $this->applytheme     = $applytheme;
    }

    public function update_id($id) {
        $this->id = $id;
    }

    public function get_id() {
        return $this->id;
    }

    public function get_organisation_id() {
        return $this->organisationid;
    }

    public function get_theme() {
        return $this->applytheme;
    }

    public function record() {
        $record = (object) array(
            'organisationid' => $this->organisationid,
            'applytheme'     => $this->applytheme,
        );

        if ($this->id !== null) {
            $record->id = $this->id;
        }

        return $record;
    }

    public function commit() {
        global $DB;

        $record = $this->record();

        if ($this->id === null) {
            $this->id = $DB->insert_record(static::TABLE_RULES, $record);
        } else {
            $DB->update_record(static::TABLE_RULES, $record);
        }

        return $this->id;
    }

    /**
     * Call me from /lib/setup.php just after URL theme change.
     */
    public static function setup_theme() {
        global $SESSION, $USER;

        $cobrandrule = static::from_user_id($USER->id);
        if (!$cobrandrule) {
            return;
        }

        $cobrandtheme = $cobrandrule->get_theme();

        try {
            $themeconfig = theme_config::load($cobrandtheme);
            if ($themeconfig->name === $cobrandtheme) {
                $SESSION->theme = $cobrandtheme;
            } else {
                unset($SESSION->theme);
            }
        } catch (Exception $e) {
            debugging('Failed to set co-branding theme', DEBUG_DEVELOPER, $e->getTrace());
        }
    }
}
