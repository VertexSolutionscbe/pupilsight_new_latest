<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();
//include_once($_SERVER['DOCUMENT_ROOT'] . '/config.php');
$databaseServer = '127.0.0.1';
$databaseUsername = 'root';
$databasePassword = '';
$databaseName = 'pd_demo';

$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = $databaseServer;
$CFG->dbname    = $databaseName . '_lms';
$CFG->dbuser    = $databaseUsername;
$CFG->dbpass    = $databasePassword;
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array(
  'dbpersist' => 0,
  'dbport' => '',
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_general_ci',
);

$CFG->wwwroot   = 'http://localhost/lms';
$CFG->dataroot  = '/Users/dugu/laravel/pupilsight_lmsdata';
$CFG->admin     = 'admin';

$CFG->directorypermissions = 0777;

require_once(__DIR__ . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!
