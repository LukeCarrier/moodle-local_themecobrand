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
use local_themecobrand\rule_form;
use local_themecobrand\theme_form;

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once "{$CFG->libdir}/adminlib.php";

admin_externalpage_setup('local_themecobrand_managerules');
// todo: $PAGE->navbar->add();

$form           = optional_param('form',           'rule', PARAM_ALPHA);
$organisationid = required_param('organisationid',         PARAM_INT);

$prefix      = 'organisation';
$shortprefix = 'org';
$hierarchy   = hierarchy::load_hierarchy($prefix);
$context     = context_system::instance();

$organisation = $hierarchy->get_item($organisationid);
if ($organisation === false) {
    print_error('invalidorg', 'local_themecobrand', new moodle_url('managerules.php'));
}
$framework = $hierarchy->get_framework($organisation->frameworkid);

try {
    $rule = rule::from_organisation_id($organisationid);
} catch (dml_missing_record_exception $e) {
    $rule = new rule();
}

$formcustomdata = array(
    'framework'    => $framework,
    'organisation' => $organisation,
    'rule'         => $rule,
);

switch ($form) {
    case 'rule':
        $mform = new rule_form(null, $formcustomdata);
        break;

    case 'theme':
        $mform = new theme_form(null, $formcustomdata);
        break;
    default:
        print_error('invalidform', 'local_themecobrand');
}

if ($data = $mform->get_data()) {
    $mform->save($data);

    redirect(new moodle_url('managerules.php', array(
        'committedorganisationid' => $organisationid,
        'frameworkid'             => $organisation->frameworkid,
    )));
} elseif ($mform->is_cancelled()) {
    redirect(new moodle_url('managerules.php', array(
        'cancelled' => $organisation->id,
    )));
} else {
    echo $OUTPUT->header(),
         $OUTPUT->heading(get_string('editingorgx', 'local_themecobrand', (object) array(
             'framework'    => $framework->fullname,
             'organisation' => $organisation->fullname,
         ))),
         $mform->display(),
         $OUTPUT->footer();
}

