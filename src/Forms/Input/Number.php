<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Input;

/**
 * TextField
 *
 * @version v14
 * @since   v14
 */
class Number extends TextField
{
    protected $min;
    protected $max;
    protected $decimalPlaces = 0;
    protected $onlyInteger = true;

    /**
     * Define a minimum for this numeric value.
     * @param   int|float  $value
     * @return  self
     */
    public function minimum($value)
    {
        $this->min = $value;
        return $this;
    }

    /**
     * Define a maximum for this numeric value.
     * @param   int|float  $value
     * @return  self
     */
    public function maximum($value)
    {
        $this->max = $value;
        return $this;
    }

    /**
     * Define a required number of decimal places (max) for this numeric value.
     * @param   int  $value
     * @return  self
     */
    public function decimalPlaces($value)
    {
        $this->decimalPlaces = intval($value);
        $this->onlyInteger = !($this->decimalPlaces > 0);
        return $this;
    }

    public function onlyInteger($value)
    {
        $this->onlyInteger = $value;
        return $this;
    }

    /**
     * Gets the HTML output for this form element.
     * @return  string
     */
    protected function getElement()
    {

        $validateParams = array();
        if (isset($this->min)) {
            $validateParams[] = 'minimum: '.$this->min;
        }
        if (!empty($this->max)) {
            $validateParams[] = 'maximum: '.$this->max;
        }

        if ($this->onlyInteger) {
            $validateParams[] = 'onlyInteger: true';
        }

        $this->addValidation('Validate.Numericality', implode(', ', $validateParams));

        if (!empty($this->decimalPlaces) && $this->decimalPlaces > 0) {
            $this->addValidation('Validate.Format', 'pattern: /^[0-9\-]+(\.[0-9]{1,'.$this->decimalPlaces.'})?$/, failureMessage: "'.sprintf(__('Must be in format %1$s'), str_pad('0.', $this->decimalPlaces+2, '0')).'"');
        }

        $output = '<input type="text" '.$this->getAttributeString().'>';

        return $output;
    }
}
