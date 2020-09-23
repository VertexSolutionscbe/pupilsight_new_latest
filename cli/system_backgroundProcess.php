<?php
/*
Pupilsight, Flexible & Open School System
*/

use Pupilsight\Services\BackgroundProcessor;
use Pupilsight\Services\Format;

$_POST['address'] = '/modules/'.($argv[3] ?? 'System Admin').'/index.php';

require __DIR__.'/../pupilsight.php';

// Cancel out now if we're not running via CLI
if (!isCommandLineInterface()) {
    die(__('This script cannot be run from a browser, only via CLI.'));
}

// Setup some of the globals
Format::setupFromSession($container->get('session'));

// Override the ini to keep this process alive
ini_set('memory_limit', '2048M');
ini_set('max_execution_time', 1800);
set_time_limit(1800);

// Incoming variables from command line
$processID = $argv[1] ?? '';
$processKey = $argv[2] ?? '';

// Run the process
$container->get(BackgroundProcessor::class)->runProcess($processID, $processKey);
