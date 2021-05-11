<?php
/*
Pupilsight, Flexible & Open School System
*/

// Setup the composer autoloader
$autoloader = require_once __DIR__ . "/vendor/autoload.php";

// Require the system-wide functions
require_once __DIR__ . "/functions.php";

// Core Services
$container = new League\Container\Container();
$container->delegate(new League\Container\ReflectionContainer());
$container->add("autoloader", $autoloader);

$container
    ->inflector(\League\Container\ContainerAwareInterface::class)
    ->invokeMethod("setContainer", [$container]);

$container
    ->inflector(\Pupilsight\Services\BackgroundProcess::class)
    ->invokeMethod("setProcessor", [
        \Pupilsight\Services\BackgroundProcessor::class,
    ]);

$container->addServiceProvider(
    new Pupilsight\Services\CoreServiceProvider(__DIR__)
);
$container->addServiceProvider(new Pupilsight\Services\ViewServiceProvider());
$container->addServiceProvider(new Pupilsight\Services\GoogleServiceProvider());

// Globals for backwards compatibility
$pupilsight = $container->get("config");
$pupilsight->session = $container->get("session");
$pupilsight->locale = $container->get("locale");
$guid = $pupilsight->getConfig("guid");
$caching = $pupilsight->getConfig("caching");
$version = $pupilsight->getConfig("version");

// Handle Pupilsight installation redirect
if (!$pupilsight->isInstalled() && !$pupilsight->isInstalling()) {
    header("Location: ./installer/install.php");
    exit();
}

// Autoload the current module namespace
if (!empty($pupilsight->session->get("module"))) {
    $moduleNamespace = preg_replace(
        "/[^a-zA-Z0-9]/",
        "",
        $pupilsight->session->get("module")
    );
    $autoloader->addPsr4(
        "Pupilsight\\Module\\" . $moduleNamespace . "\\",
        realpath(__DIR__) .
            "/modules/" .
            $pupilsight->session->get("module") .
            "/src"
    );

    // Temporary backwards-compatibility for external modules (Query Builder)
    $autoloader->addPsr4(
        "Pupilsight\\" . $moduleNamespace . "\\",
        realpath(__DIR__) . "/modules/" . $pupilsight->session->get("module")
    );
    $autoloader->register(true);
}

// Initialize using the database connection
if ($pupilsight->isInstalled() == true) {
    $mysqlConnector = new Pupilsight\Database\MySqlConnector();
    if ($pdo = $mysqlConnector->connect($pupilsight->getConfig())) {
        $container->add("db", $pdo);
        $container->share(
            Pupilsight\Contracts\Database\Connection::class,
            $pdo
        );
        $connection2 = $pdo->getConnection();

        $pupilsight->initializeCore($container);
    } else {
        // We need to handle failed database connections after install. Display an error if no connection
        // can be established. Needs a specific error page once header/footer is split out of index.
        if (!$pupilsight->isInstalling()) {
            include "./error.php";
            exit();
        }
    }
}
