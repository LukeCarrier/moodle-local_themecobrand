--
-- Theme co-branding.
--
-- Enables customised per-organisation theming for TotaraLMS.
--
-- @author Luke Carrier <luke@tdm.co>
-- @copyright (c) The Development Manager Ltd
-- @license GPL v3
--

UPDATE mdl_config_plugins
SET value = '0'
WHERE plugin = 'local_themecobrand'
    AND name = 'version';

DROP TABLE IF EXISTS mdl_local_themecobrand_rules;
