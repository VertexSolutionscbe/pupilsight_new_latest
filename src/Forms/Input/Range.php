<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Input;

use Pupilsight\Forms\Element;

/**
 * Range Slider
 *
 * @version v17
 * @since   v17
 */
class Range extends Input
{
    /**
     * Create an HTML range slider.
     * @param  string  $name
     */
    public function __construct($name, $min, $max, $step = 1)
    {
        parent::__construct($name);

        $this->setAttribute('min', $min);
        $this->setAttribute('max', $max);
        $this->setAttribute('step', $step);
    }

    /**
     * Gets the HTML output for this form element.
     * @return  string
     */
    protected function getElement()
    {
        $output = '<input type="range" '.$this->getAttributeString().'>';

        return $output;
    }
}
