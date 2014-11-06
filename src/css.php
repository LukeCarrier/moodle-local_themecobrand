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

define('AJAX_SCRIPT', true);

error_reporting(E_ALL);
ini_set('display_errors', 'on');

require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

$organisationid = required_param('organisationid', PARAM_INT);

$rule = local_tdmcobrand_rule::from_organisation_id($organisationid);

header('Content-Type: text/css');
echo $rule->get_css();
