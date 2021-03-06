<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Input;

use Pupilsight\Forms\Layout\Element;
use Pupilsight\Forms\RowDependancyInterface;
use Pupilsight\Forms\ValidatableInterface;
use Pupilsight\Forms\Traits\InputAttributesTrait;

/**
 * Abstract base class for form input elements.
 *
 * @version v14
 * @since   v14
 */
abstract class Input extends Element implements ValidatableInterface, RowDependancyInterface
{
    use InputAttributesTrait;

    protected $row;

    protected $validationOptions = array();
    protected $validation = array();

    /**
     * Create an HTML form input.
     * @param  string  $name
     */
    public function __construct($name)
    {
        $this->setID($name);
        $this->setName($name);
        $this->setClass('standardWidth');
    }

    /**
     * Method for RowDependancyInterface to automatically set a reference to the parent Row object.
     * @param  object  $row
     */
    public function setRow($row)
    {
        $this->row = $row;
    }

    /**
     * Add a LiveValidation option to the javascript object (eg: onlyOnSubmit: true, onlyOnBlur: true)
     * @param  string  $option
     */
    public function addValidationOption($option = '')
    {
        $this->validationOptions[] = $option;
        return $this;
    }

    /**
     * Add a LiveValidation setting to this element by type (eg: Validate.Presence)
     * @param  string  $type
     * @param  string  $params
     */
    public function addValidation($type, $params = '')
    {
        $this->validation[] = array('type' => $type, 'params' => $params);
        return $this;
    }

    /**
     * Can this input be validated? Prevent LiveValidation for elements with no ID, and readonly inputs.
     * @return bool
     */
    public function isValidatable() {
        return !empty($this->getID()) && !$this->getReadonly();
    }

    /**
     * An input has validation if it's validatable and either required or has defined validations.
     * @return bool
     */
    public function hasValidation()
    {
        return $this->isValidatable() && ($this->getRequired() == true || !empty($this->validation));
    }

    /**
     * Get a stringified json object of the current validations.
     * @return string
     */
    public function getValidationAsJSON()
    {
        return json_encode($this->buildValidations());
    }

    /**
     * Get the HTML output of the content element.
     * @return  string
     */
    public function getOutput()
    {
        $class = $this instanceof Checkbox ? 'inline flex-1 relative' : 'flex-1 relative';
        return $this->prepended."<div class='{$class}'>".$this->getElement()."</div>".$this->appended;
    }

    /**
     * Gets the HTML output for this form element.
     * @return  string
     */
    public function getValidationOutput()
    {
        $output = '';

        if ($this->hasValidation()) {
            $safeID = 'lv'.preg_replace('/[^a-zA-Z0-9_]/', '', $this->getID());
            
            $output .= 'var '.$safeID.'Validate=new LiveValidation(\''.$this->getID().'\', {'.implode(',', $this->validationOptions).' }); '."\r";

            foreach ($this->buildValidations() as $valid) {
                $output .= $safeID.'Validate.add('.$valid['type'].', {'.$valid['params'].' } ); '."\r";
            }
        }

        return $output;
    }

    /**
     * Get the array of current validations for this input.
     * @return array
     */
    protected function buildValidations()
    {
        if (!$this->isValidatable()) {
            return array();
        }

        if ($this->getRequired() == true) {
            if ($this instanceof Checkbox && $this->getOptionCount() == 1) {
                $this->addValidation('Validate.Acceptance');
            } else {
                $this->addValidation('Validate.Presence');
            }
        }

        return $this->validation;
    }
}
