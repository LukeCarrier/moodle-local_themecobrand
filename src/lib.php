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

/**
 * Plugin file request handler.
 */
function local_themecobrand_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload,
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
    $file = $fs->get_file($context->id, 'local_themecobrand', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }
 
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}
