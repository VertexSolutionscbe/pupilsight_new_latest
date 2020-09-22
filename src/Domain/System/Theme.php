<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Domain\System;

use Pupilsight\View\AssetBundle;

/**
 * Pupilsight Theme Model.
 *
 * @version v17
 * @since   v17
 */
class Theme
{
    protected $pupilsightThemeID;
    protected $name;
    protected $version;

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
            'theme',
            'themes/'.$this->name.'/css/main.css',
            ['version' => $this->version]
        );
        $this->scripts->add(
            'theme',
            'themes/'.$this->name.'/js/common.js',
            ['version' => $this->version]
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
     * Get the pupilsightThemeID
     *
     * @return string
     */
    public function getID()
    {
        return $this->pupilsightThemeID;
    }

    /**
     * Get the theme name, used in the folder path and database record.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
