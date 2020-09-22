<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\System;

use Pupilsight\View\AssetBundle;

/**
 * Pupilsight Module Model.
 *
 * @version v17
 * @since   v17
 */
class Module
{
    protected $pupilsightModuleID;
    protected $name;
    protected $version;
    protected $entryURL;

    protected $stylesheets;
    protected $scripts;

    public function __construct(array $params = [])
    {
        // Merge constructor params into class properties
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        $this->stylesheets = new AssetBundle();
        $this->scripts = new AssetBundle();

        $this->stylesheets->add(
            'module',
            'modules/'.$this->name.'/css/module.css',
            ['version' => $this->version, 'weight' => 0.5]
        );
        $this->scripts->add(
            'module',
            'modules/'.$this->name.'/js/module.js',
            ['version' => $this->version, 'context' => 'head']
        );
    }

    /**
     * Allow read-only access of model properties.
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    /**
     * Check if a model property exists.
     *
     * @param string $name
     * @return mixed
     */
    public function __isset(string $name)
    {
        return isset($this->$name);
    }

    /**
     * Get the pupilsightModuleID
     *
     * @return string
     */
    public function getID()
    {
        return $this->pupilsightModuleID;
    }

    /**
     * Get the module name, used in the folder path and database record.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
