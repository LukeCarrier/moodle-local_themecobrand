<?php

/**
 * Theme co-branding.
 *
 * Enables customised per-organisation theming for TotaraLMS.
 *
 * @author Luke Carrier <luke@tdm.co>
 * @copyright (c) The Development Manager Ltd
 * @license GPL v3
 */

/**
 * Co-branding theme rule.
 */
class local_tdmcobrand_rule {
    // DB tables (as per install.xml)
    const TABLE_RULES = 'local_tdmcobrand_rules';

    protected $id,
              $organisationid,
              $applycss,
              $applytheme,
              $applylogo;

    public static function from_organisation_id($organisationid) {
        global $DB;

        $record = $DB->get_record(static::TABLE_RULES, array('organisationid' => $organisationid), '*', MUST_EXIST);

        $instance = new static();
        $instance->id             = $record->id;
        $instance->organisationid = $record->organisationid;
        $instance->applycss       = $record->applycss;
        $instance->applytheme     = $record->applytheme;
        $instance->applylogo      = $record->applylogo;

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
SELECT organisationid, id, applycss, applylogo, applytheme
FROM {local_tdmcobrand_rules}
WHERE organisationid {$insql}
SQL;
        $parentrules = $DB->get_records_sql($sql, $params);

        foreach ($parentids as $parentid) {
            if (array_key_exists($parentid, $parentrules)) {
                $record = $parentrules[$parentid];

                $instance = new static();
                $instance->update_id($record->id);
                $instance->update($record->organisationid, $record->applycss, $record->applytheme, $record->applylogo);

                return $instance;
            }
        }
    }

    public function update($organisationid, $applycss, $applytheme, $applylogo) {
        $this->organisationid = $organisationid;
        $this->applycss       = $applycss;
        $this->applytheme     = $applytheme;
        $this->applylogo      = $applylogo;
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

    public function get_css() {
        return $this->applycss;
    }

    public function get_logo() {
        return $this->applylogo;
    }

    public function get_theme() {
        return $this->applytheme;
    }

    public function record() {
        $record = (object) array(
            'organisationid' => $this->organisationid,
            'applycss'       => $this->applycss,
            'applytheme'     => $this->applytheme,
            'applylogo'      => $this->applylogo,
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

    public static function setup_css() {
        global $USER;

        $cobrandrule = static::from_user_id($USER->id);
        if (!$cobrandrule) {
            return;
        }

        return new moodle_url('/local/tdmcobrand/css.php', array(
            'organisationid' => $cobrandrule->get_organisation_id(),
        ));
    }

    public static function setup_logo() {
        global $USER;

        $context = context_system::instance();

        $cobrandrule = static::from_user_id($USER->id);
        if (!$cobrandrule) {
            return;
        }

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_tdmcobrand', 'applylogo', $cobrandrule->get_id(),
                                     'filepath, filename', false);
        $file = array_shift($files);

        if (!($file instanceof stored_file)) {
            return;
        }

        return moodle_url::make_pluginfile_url($context->id, 'local_tdmcobrand', 'applylogo', $cobrandrule->get_id(),
                                               $file->get_filepath(), $file->get_filename());
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

/**
 * Plugin file request handler.
 */
function local_tdmcobrand_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload,
                                     array $options=array()) {
    if ($context->contextlevel !== CONTEXT_SYSTEM
            || $filearea !== 'applylogo') {
        return false;
    }

    require_login();

    $itemid = array_shift($args);
    $filename = array_pop($args);
    if (!$args) {
        $filepath = '/';
    } else {
        $filepath = '/' . implode('/', $args) . '/';
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_tdmcobrand', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
 
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
