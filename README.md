Theme co-branding for Moodle and Totara LMS
===========================================

A local plugin for Moodle, designed to enable co-branding of your e-learning
platform based upon the currently logged-in user.

Installation
------------

1. Extract the zip file distributed with the plugin, and copy the tdmcobrand
   directory into your Moodle instlallation's local directory.
2. Install the patch to /lib/setup.php, where the co-branding module will be
   summoned to change the theme.

Applying the patch
------------------

If you're not familiar with the command line, you'll probably want to ask a guru
for assistance with this. The plugin can't actually change the theme until you
make a modification one of Moodle's core files:

    $ cd /path/to/your/moodle/dirroot
    $ patch -p1 <../path/to/local_tdmcobrand/patches/lib-setup.patch
