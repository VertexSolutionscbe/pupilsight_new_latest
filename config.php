<?php
/*
Pupilsight, Flexible & Open School System

*/

/**
 * Sets the database connection information.
 * You can supply an optional $databasePort if your server requires one.
 */
$databaseServer = '127.0.0.1';
$databaseUsername = 'root';
//$databasePassword = '';
//$databaseName = 'pupilsight'; //pd_demo
//live
$databasePassword = 'xyz'; //xyz
$databaseName = 'pd_demo'; //pd_demo

// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }

$_SESSION["databaseName"] = $databaseName;
/**
 * Sets a globally unique id, to allow multiple installs on a single server.
 */
$guid = '4r8tmxybf-0du5-5ido-mwzw-medt3ucr55';

/**
 * Sets system-wide caching factor, used to balance performance and freshness.
 * Value represents number of page loads between cache refresh.
 * Must be positive integer. 1 means no caching.
 */
$caching = 10;
