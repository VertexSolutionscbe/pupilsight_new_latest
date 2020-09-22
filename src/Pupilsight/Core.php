<?php
/*
Pupilsight, Flexible & Open School System
 */

namespace Pupilsight;

use Psr\Container\ContainerInterface;

/**
 * Pupilsight Core
 *
 * @version	v13
 * @since	v13
 */
class Core
{
    /**
     * Pupilsight system path and url, only available internally
     * @var  string
     */
    protected $basePath;

    /**
     * Core classes available to all Pupilsight scripts 
     * TODO: These need removed & replaced with DI
     * @var  object
     */
    public $session;
    public $locale;
    
    /**
     * Configuration variables
     * @var  array
     */
    protected $config = array();

    /**
     * Has pupilsight been initialized using a DB connection?
     * @var  bool
     */
    private $initialized;

    /**
     * Construct
     */
    public function __construct($directory)
    {
        $this->basePath = realpath($directory);
        
        // Load the configuration, if installed
        $this->loadConfigFromFile($this->basePath . '/config.php');

        // Set the current version
        $this->loadVersionFromFile($this->basePath . '/version.php');
    }

    /**
     * Setup the Pupilsight core: Runs once (enforced), if Pupilsight is installed & database connection exists
     *
     * @param   ContainerInterface  $container
     */
    public function initializeCore(ContainerInterface $container)
    {
        if ($this->initialized == true) return;

        $db = $container->get('db');

        $this->session->setDatabaseConnection($db);

        if (empty($this->session->get('systemSettingsSet'))) {
            $this->session->loadSystemSettings($db);
            $this->session->loadLanguageSettings($db);
        }

        $installType = $this->session->get('installType');
        if (empty($installType) || $installType == 'Production') {
            ini_set('display_errors', 0);
        }

        $this->locale->setLocale($this->session->get(array('i18n', 'code')));
        $this->locale->setTimezone($this->session->get('timezone', 'UTC'));
        $this->locale->setTextDomain($db);
        $this->locale->setStringReplacementList($db);

        $this->initialized = true;
    }

    /**
     * Is Pupilsight Installed? Based on existance of config.php file
     *
     * @return   bool
     */
    public function isInstalled()
    {
        return (file_exists($this->basePath . '/config.php') && filesize($this->basePath . '/config.php') > 0);
    }

    public function isInstalling()
    {
        return stripos($_SERVER['PHP_SELF'], 'installer/install.php') !== false;
    }

    /**
     * Gets the globally unique id, to allow multiple installs on the server
     *
     * @return   string|null
     */
    public function guid()
    {
        return isset($this->config['guid'])? $this->config['guid'] : 'undefined';
    }

    /**
     * Gets the current Pupilsight version
     *
     * @return   string
     */
    public function getVersion()
    {
        return $this->getConfig('version');
    }

    /**
     * Get a config value by name, othwerwise return the config array.
     * @param string $name
     * 
     * @return mixed|array
     */
    public function getConfig($name = null)
    {
        return !is_null($name) && isset($this->config[$name])
            ? $this->config[$name]
            : $this->config;
    }

    /**
     * Gets a System Requirement by array key.
     *
     * @return   string
     */
    public function getSystemRequirement($key)
    {
        return isset($this->config['systemRequirements'][$key]) 
            ? $this->config['systemRequirements'][$key] 
            : null;
    }

    /**
     * Load the current Pupilsight version number
     *
     * @param    string  $versionFilePath
     *
     * @throws   Exception If the version file is not found
     */
    protected function loadVersionFromFile($versionFilePath)
    {
        if (file_exists($versionFilePath) == false) {
            throw new Exception('Pupilsight version.php file missing: ' . $versionFilePath);
        }

        include $versionFilePath;

        $this->config['version'] = $version;
        $this->config['systemRequirements'] = $systemRequirements;
    }

    /**
     * Load the Pupilsight configuration file, contained in this scope to prevent unintended global access
     *
     * @param   string  $configFilePath
     */
    protected function loadConfigFromFile($configFilePath)
    {
        // Load the config values (from an array if possible)
        if (!$this->isInstalled()) return;
        
        $this->config = include $configFilePath;

        if (!isset($databasePort)) $databasePort = '';

        // Otherwise load the config values from global scope
        if (empty($this->config) || !is_array($this->config)) {
            $this->config = compact('databaseServer', 'databaseUsername', 'databasePassword', 'databaseName', 'databasePort', 'guid', 'caching');
        }
    }
}
