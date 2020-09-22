<?php
/*
Pupilsight, Flexible & Open School System
*/

/**
 * Sets version information.
 */
$version = '18.0.01';

/**
 * System Requirements
 */
$systemRequirements = array(
    'php'        => '7.0.0',
    'mysql'      => '5.6',
    'apache'     => array('mod_rewrite'),
    'extensions' => array('gettext', 'mbstring', 'curl', 'zip', 'xml', 'gd'),
    'settings'   => array(
                        array('max_input_vars', '>=', 5000),
                        array('max_file_uploads', '>=', 20),
                        array('allow_url_fopen', '==', 1),
                        array('register_globals', '==', 0),
                    ),
);
