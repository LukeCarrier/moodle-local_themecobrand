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

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once "{$CFG->libdir}/adminlib.php";
require_once dirname(__FILE__) . '/locallib.php';

admin_externalpage_setup('local_themecobrand_managerules');
// todo: $PAGE->navbar->add();

$organisationid = required_param('organisationid', PARAM_INT);

$prefix      = 'organisation';
$shortprefix = 'org';
$hierarchy   = hierarchy::load_hierarchy($prefix);
$context     = context_system::instance();

$fileopts = array(
    'subdirs'        => 0,
    'maxbytes'       => $CFG->maxbytes,
    'maxfiles'       => 1,
    'accepted_types' => array('image'),
);

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

$mform = new rule_form(null, array(
    'fileopts'     => $fileopts,
    'framework'    => $framework,
    'organisation' => $organisation,
));

if ($data = $mform->get_data()) {
    $draftareainfo = file_get_draft_area_info($data->applylogo);

    if (!property_exists($data, 'applytheme')) {
        $data->applytheme = '';
    }

    $rule->update($data->organisationid, $data->applycss, $data->applytheme, $draftareainfo['filecount']);
    $rule->commit();

    file_save_draft_area_files($data->applylogo, $context->id, 'local_themecobrand', 'applylogo', $rule->get_id());

    redirect(new moodle_url('managerules.php', array(
        'committedorganisationid' => $organisationid,
        'frameworkid'             => $organisation->frameworkid,
    )));
} elseif ($mform->is_cancelled()) {
    redirect(new moodle_url('managerules.php', array(
        'cancelled' => $organisation->id,
    )));
} else {
    $record = $rule->record();

    if (isset($record->id)) {
        $draftareaid = file_get_submitted_draft_itemid('applylogo');
        file_prepare_draft_area($draftareaid, $context->id, 'local_themecobrand', 'applylogo', $record->id, $fileopts);
        $record->applylogo = $draftareaid;
    }

    $mform->set_data($record);

    echo $OUTPUT->header(),
         $OUTPUT->heading(get_string('editingorgx', 'local_themecobrand', (object) array(
             'framework'    => $framework->fullname,
             'organisation' => $organisation->fullname,
         ))),
         $mform->display(),
         $OUTPUT->footer();
}

