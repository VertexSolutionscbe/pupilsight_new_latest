<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\UI\Chart;

class ChartDataset
{
    protected $label = '';
    protected $properties = [];
    protected $data = [];

    /**
     * ChartDataset Constructor
     *
     * @param string $label
     */
    public function __construct($label = '')
    {
        $this->label = $label;
    }

    /**
     * Get the current dataset label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the current dataset label.
     *
     * @return string
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the array of dataset properties.
     *
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Set a key => value pair of properties.
     *
     * @param string $key
     * @param string $value
     * @return self
     */
    public function setProperty($key, $value)
    {
        if ($key == 'data' || $key == 'label') {
            throw new \InvalidArgumentException('Cannot assign data or label values with the setProperty method.');
        }

        $this->properties[$key] = $value;

        return $this;
    }

    /**
     * Set an array of key => value properties.
     *
     * @param array $properties
     * @return self
     */
    public function setProperties($properties)
    {
        if (!empty($properties) && array_values($properties) === $properties) {
            throw new \InvalidArgumentException('The argument passed to setProperties must be an associative array.');
        }

        if (isset($properties['data']) || isset($properties['label'])) {
            throw new \InvalidArgumentException('Cannot assign data or label values with the setProperty method.');
        }

        $this->properties = $properties;

        return $this;
    }

    /**
     * Returns the array of chart data.
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Set a single key => value pair of chart data, or optionally a whole array.
     *
     * @param number|array $index
     * @param mixed        $value
     * @return self
     */
    public function setData($key, $value = null)
    {
        if ($value === null && is_array($key)) {
            $this->data = array_values($key);
        } else {
            if (!isset($this->data[$key])) {
                throw new \InvalidArgumentException(sprintf('Cannot set data on an uninitialized data point %s.', $key));
            }

            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Append data to the current dataset.
     *
     * @param  mixed  $value
     * @return self
     */
    public function appendData($value)
    {
        $this->data[] = $value;

        return $this;
    }
}
