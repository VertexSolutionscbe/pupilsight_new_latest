<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Traits;

/**
 * Basic HTML Input Attributes (name, type, value, required)
 *
 * @version v14
 * @since   v14
 */
trait InputAttributesTrait
{
    protected $required;

    /**
     * Set the input's name attribute.
     * @param  string  $name
     * @return self
     */
    public function setName($name = '')
    {
        $this->setAttribute('name', $name);
        return $this;
    }

    /**
     * Gets the input's name attribute.
     * @return  string
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * Set the input's value.
     * @param  string  $value
     * @return self
     */
    public function setValue($value = '')
    {
        $this->setAttribute('value', $value);
        return $this;
    }

    /**
     * Gets the input's value.
     * @return  mixed
     */
    public function getValue()
    {
        return $this->getAttribute('value');
    }

    /**
     * Sets the input's value if the name matches a key in the provided data set.
     * @param   array  &$data
     */
    public function loadFrom(&$data)
    {
        $name = str_replace('[]', '', $this->getName());

        if (isset($data[$name])) {
            $value = $data[$name];

            if (method_exists($this, 'selected')) {
                $this->selected($value);
            } else if (method_exists($this, 'checked')) {
                $this->checked($value);
            } else {
                $this->setAttribute('value', $value);
            }
        }

        return $this;
    }

    public function loadFromNew(&$data)
    {
        $name = str_replace('[]', '', $this->getName());

        if (isset($data[$name])) {
            $value = $data[$name];

            if (method_exists($this, 'selected')) {
                $this->selected($value);
            } else if (method_exists($this, 'checked')) {
                $this->checked($value);
            } else {
                $this->setAttribute('value', $value);
            }
        }

        return $this;
    }

    /**
     * Sets the input's array value from a CSV string if the name matches a key in the provided data set.
     * @param   array  &$data
     */
    public function loadFromCSV(&$data)
    {
        $name = str_replace('[]', '', $this->getName());

        if (isset($data[$name])) {
            $data[$name] = array_map(function($item) { return trim($item); }, explode(',', $data[$name]));
        }

        return $this->loadFrom($data);
    }

    public function loadFromCSVNew(&$data)
    {
        $name = str_replace('[]', '', $this->getName());

        if (isset($data[$name])) {
            $data[$name] = array_map(function($item) { return trim($item); }, explode(',', $data[$name]));
        }

        return $this->loadFromNew($data);
    }

    /**
     * Set the input's size attribute.
     * @param  string|int  $size
     * @return self
     */
    public function setSize($size = '')
    {
        $this->setAttribute('size', $size);
        return $this;
    }

    /**
     * Gets the input's size attribute.
     * @return  string|int
     */
    public function getSize()
    {
        return $this->getAttribute('size');
    }

    /**
     * @deprecated Remove setters that start with isXXX for code consistency.
     */
    public function isDisabled($disabled = true)
    {
        $this->setDisabled('disabled', $disabled);
        return $this;
    }

    /**
     * Set the input to disabled.
     * @param   bool    $value
     * @return  self
     */
    public function disabled($disabled = true)
    {
        return $this->setDisabled('disabled', $disabled);
    }

    /**
     * Set the input's disabled attribute.
     * @param  bool  $disabled
     * @return self
     */
    public function setDisabled($disabled)
    {
        $this->setAttribute('disabled', $disabled);
        return $this;
    }

    /**
     * Gets the input's disabled attribute.
     * @return  bool
     */
    public function getDisabled()
    {
        return $this->getAttribute('disabled');
    }

    /**
     * @deprecated Remove setters that start with isXXX for code consistency.
     */
    public function isRequired($required = true)
    {
        return $this->setRequired($required);
    }

    /**
     * Set the input to required.
     * @param   bool    $value
     * @return  self
     */
    public function required($required = true)
    {
        return $this->setRequired($required);
    }

    /**
     * Set if the input is required.
     * @param  bool  $required
     * @return self
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Gets the input's required attribute.
     * @return  bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set the input to readonly.
     * @param   bool    $value
     * @return  self
     */
    public function readonly($value = true)
    {
        return $this->setReadonly($value);
    }

    /**
     * Set the input's readonly attribute.
     * @param  string  $value
     * @return self
     */
    public function setReadonly($value)
    {
        $this->setAttribute('readonly', $value);

        return $this;
    }

    /**
     * Gets the input's readonly attribute.
     * @return  bool
     */
    public function getReadonly()
    {
        return $this->getAttribute('readonly');
    }

    /**
     * Set the input's tabindex attribute.
     * @param  string  $value
     * @return self
     */
    public function setTabIndex($value)
    {
        $this->setAttribute('tabindex', $value);

        return $this;
    }

    /**
     * Gets the input's tabindex attribute.
     * @return  int
     */
    public function getTabIndex()
    {
        return $this->getAttribute('tabindex');
    }
}
