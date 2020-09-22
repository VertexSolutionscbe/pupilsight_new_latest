<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Input;

use Pupilsight\Forms\Traits\MultipleOptionsTrait;

/**
 * Checkbox
 *
 * @version v14
 * @since   v14
 */
class Radio extends Input
{
    use MultipleOptionsTrait;

    protected $inline = false;

    public function __construct($name)
    {
        $this->setID(''); // Cannot share an ID across multiple Radio inputs
        $this->setName($name);
    }

    /**
     * Set the value of the radio element.
     * @param   mixed  $value
     * @return  self
     */
    public function checked($value)
    {
        $this->setValue(trim($value));
        return $this;
    }

    /**
     * Sets multiple radio elements to display horizontally.
     * @param   bool    $value
     * @return  self
     */
    public function inline($value = true)
    {
        $this->inline = $value;
        return $this;
    }

    /**
     * Dead-end stub for interface: LiveValidation does not support Radio elements.
     * @param  string  $type
     * @param  string  $params
     */
    public function addValidation($type, $params = '')
    {
        return $this;
    }

    /**
     * Return true if the passed value matches the current radio element value.
     * @param   mixed  $value
     * @return  bool
     */
    protected function getIsChecked($value)
    {
        return (!is_null($this->getValue()) && $value == $this->getValue());
    }

    /**
     * Gets the HTML output for this form element.
     * @return  string
     */
    protected function getElement()
    {
        $output = '';

        if (!empty($this->getOptions()) && is_array($this->getOptions())) {

            // Select the first option by default for required Radio elements with no checked value set
            if ($this->getRequired() && is_null($this->getValue())) {
                $firstOption = key($this->getOptions());
                $this->checked($firstOption);
            }

            foreach ($this->getOptions() as $value => $label) {

                $this->setAttribute('checked', $this->getIsChecked($value));

                if ($this->inline) {
                    $output .= '&nbsp;&nbsp;<input type="radio" value="'.$value.'" '.$this->getAttributeString().'>&nbsp;';
                    $output .= '<label title="'.$label.'">'.$label.'</label>';
                } else {
                    $output .= '<label title="'.$label.'">'.$label.'</label>&nbsp;';
                    $output .= '<input type="radio" value="'.$value.'" '.$this->getAttributeString().'><br/>';
                }
            }
        }

        return $output;
    }
}
