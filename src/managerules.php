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

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once "{$CFG->libdir}/adminlib.php";
require_once "{$CFG->libdir}/totaratablelib.php";

define('DEFAULT_PAGE_SIZE', 50);
define('SHOW_ALL_PAGE_SIZE', 5000);

admin_externalpage_setup('local_themecobrand_managerules');

$prefix      = 'organisation';
$shortprefix = 'org';
$hierarchy   = hierarchy::load_hierarchy($prefix);
$context     = context_system::instance();

$frameworkid = optional_param('frameworkid', null,              PARAM_INT);
$perpage     = optional_param('perpage',     DEFAULT_PAGE_SIZE, PARAM_INT);
$page        = optional_param('page',        0,                 PARAM_INT);

$frameworks = array_map(function($framework) {
    return $framework->fullname;
}, $hierarchy->get_frameworks());

$body = html_writer::tag('p', get_string('selectaframework', 'local_themecobrand'))
      . $OUTPUT->single_select(new moodle_url('managerules.php'), 'frameworkid', $frameworks);

if ($frameworkid !== null) {
    $ruleicon  = new pix_icon('t/edit',     get_string('edit'));
    $themeicon = new pix_icon('i/settings', get_string('settings'));

    $editurl  = new moodle_url('editrule.php');
    $tickicon = $OUTPUT->pix_icon('t/check', get_string('yes'));

    $urlparams = array(
        'prefix'      => $prefix,
        'frameworkid' => $frameworkid,
    );

    $framework = $hierarchy->get_framework($frameworkid);

    // Build query now as we need the count for flexible tables.
    $select = 'SELECT hierarchy.*, cobrandrule.applytheme';
    $count  = 'SELECT COUNT(hierarchy.id)';
    $from   = " FROM {{$shortprefix}} hierarchy";
    $join   = ' LEFT JOIN {local_themecobrand_rules} cobrandrule ON cobrandrule.organisationid = hierarchy.id';
    $where  = ' WHERE frameworkid = ?';
    $params = array($frameworkid);
    $order  = ' ORDER BY sortthread';

    $matchcount = $DB->count_records_sql($count . $from . $where, $params);

    $filteredcount = $DB->count_records_sql($count . $from . $where, $params);

    $table = new totara_table("{$prefix}-framework-index-{$frameworkid}");
    $table->define_baseurl(new moodle_url('index.php', $urlparams));

    $headercolumns = array('organisationname', 'appliestheme', 'actions');
    $headerdata    = array();
    foreach ($headercolumns as $columnname) {
        $headerdata[] = (object) array (
            'type'  => $columnname,
            'value' => (object) array(
                'fullname' => get_string($columnname, 'local_themecobrand'),
            ),
        );
    }

    $columns = array();
    $headers = array();

    foreach ($headerdata as $key => $head) {
        $columns[] = $head->type . $key;
        $headers[] = $head->value->fullname;
    }
    $table->define_headers($headers);
    $table->define_columns($columns);

    $baseurl = new moodle_url('/totara/hierarchy/index.php', $urlparams);
    $table->define_baseurl($baseurl);
    $table->set_attribute('class', 'hierarchy-index fullwidth');
    $table->setup();
    $table->pagesize($perpage, $filteredcount);

    $records = $DB->get_recordset_sql($select . $from . $join . $where . $order, $params, $table->get_page_start(), $table->get_page_size());

    $framework->description = file_rewrite_pluginfile_urls($framework->description, 'pluginfile.php', $context->id,
            'totara_hierarchy', $shortprefix.'_framework', $frameworkid);
    $body .= $OUTPUT->container($framework->description);

    $table->add_toolbar_pagination('right', 'top', 1);
    $table->add_toolbar_pagination('left', 'bottom');
    $table->set_no_records_message(get_string('no'.$prefix, 'totara_hierarchy'));

    $body .= html_writer::tag('div', '', array('class' => 'clearfix'));

    $num_on_page = 0;
    ob_start();
    if ($matchcount > 0 && $records) {
        $params = array();
        if ($page) {
            $params[] = 'page='.$page;
        }
        $extraparams = (count($params)) ? implode($params, '&amp;') : '';

        $types = $hierarchy->get_types();

        // Figure out which custom fields are used by which types.
        $cfields = $DB->get_records($shortprefix.'_type_info_field');
        foreach ($records as $record) {
            $ruleurl = $editurl->out(false, array(
                'organisationid' => $record->id,
                'form'           => 'rule',
            ));
            $themeurl = $editurl->out(false, array(
                'organisationid' => $record->id,
                'form'           => 'theme',
            ));

            $actionicons = $OUTPUT->action_icon($ruleurl,  $ruleicon)
                         . $OUTPUT->action_icon($themeurl, $themeicon);

            $row = array(
                $hierarchy->display_hierarchy_item($record, false, true, $cfields, $types),
                ($record->applytheme) ? $tickicon : '',
                $actionicons,
            );
            $table->add_data($row);
            ++$num_on_page;
        }
    }
    $table->finish_html();
    $body .= ob_get_contents();
    ob_end_clean();

    $records->close();
}

echo $OUTPUT->header(),
     $OUTPUT->heading(get_string('managerules', 'local_themecobrand')),
     $body,
     $OUTPUT->footer();
