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

$capabilities = array(
    'local/themecobrand:managerules' => array(
        'riskbitmask' => RISK_CONFIG | RISK_DATALOSS | RISK_XSS,

        'captype'      => 'write',
        'contextlevel' => CONTEXT_SYSTEM,

        'archetypes' => array(
            'manager' => CAP_ALLOW,
        ),
    ),
);
