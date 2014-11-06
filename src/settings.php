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

$ADMIN->add('organisations', new admin_externalpage('local_tdmcobrand_managerules',
                                                    get_string('managerules', 'local_tdmcobrand'),
                                                    new moodle_url('/local/tdmcobrand/managerules.php'),
                                                    array('local/tdmcobrand:managerules')));
