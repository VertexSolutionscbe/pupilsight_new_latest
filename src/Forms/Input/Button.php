<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Input;

use Pupilsight\Forms\Traits\InputAttributesTrait;
use Pupilsight\Forms\Layout\Element;

/**
 * Button
 *
 * @version v14
 * @since   v14
 */
class Button extends Element
{
    use InputAttributesTrait;
    
    private $onclick;

    public function __construct($name, $onClick)
    {
        $this->setName($name);
        $this->onClick($onClick);
        $this->setValue($name);
        $this->setID($name);
        $this->addClass('btn_remove');
    }

    public function onClick($value)
    {
        $this->setAttribute('onClick', $value);
        return $this;
    }

    protected function getElement()
    {
        $output = '<button type="button" '.$this->getAttributeString().'>'.$this->getValue().'</button>';
        return $output;
    }
}
