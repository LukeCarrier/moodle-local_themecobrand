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

use local_themecobrand\rule;

define('AJAX_SCRIPT', true);

error_reporting(E_ALL);
ini_set('display_errors', 'on');

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

$organisationid = required_param('organisationid', PARAM_INT);

$rule = rule::from_organisation_id($organisationid);

header('Content-Type: text/css');
echo $rule->get_css();
