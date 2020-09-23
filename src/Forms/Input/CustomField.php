<?php
/*
Pupilsight, Flexible & Open School System
*/

namespace Pupilsight\Forms\Input;

use Pupilsight\Forms\FormFactoryInterface;

/**
 * CustomField
 *
 * Turn an array of dynamic field information into a custom field
 *
 * @version v14
 * @since   v14
 */
class CustomField extends Input
{
    protected $factory;
    protected $fields;
    protected $type;

    protected $customField;

    /**
     * Creates a variable input type from a passed row of custom field settings (often from the database).
     * @param  FormFactoryInterface  $factory
     * @param  string                $name
     * @param  array                 $fields
     */
    public function __construct(FormFactoryInterface $factory, $name, $fields)
    {
        $this->factory = $factory;
        $this->fields = $fields;

        //From Enum: 'varchar','text','date','url','select', ('checkboxes' unimplemented?)
        $this->type = (isset($fields['type']))? $fields['type'] : 'varchar';
        $options = (isset($fields['options']))? $fields['options'] : '';

        switch($this->type) {

            case 'date':
                $this->customField = $this->factory->createDate($name);
                break;

            case 'url':
                $this->customField = $this->factory->createURL($name);
                break;

            case 'select':
                $this->customField = $this->factory->createSelect($name);
                if (!empty($options)) {
                    $this->customField->fromString($options)->placeholder();
                }
                break;

            case 'text':
                $this->customField = $this->factory->createTextArea($name);
                if (!empty($options) && intval($options) > 0) {
                    $this->customField->setRows($options);
                }
                break;

            default:
            case 'varchar':
                $this->customField = $this->factory->createTextField($name);
                if (!empty($options) && intval($options) > 0) {
                    $this->customField->maxLength($options);
                }
                break;
        }

        if ($fields['required'] == 'Y') {
            $this->customField->required();
            $this->required();
        }

        if (!empty($fields['default'])) {
            $this->customField->setValue($fields['default']);
        }

        $this->customField->setClass('w-full');

        parent::__construct($name);
    }

    /**
     * Sets the value of the custom field depending on it's internal type.
     * @param  mixed  $value
     */
    public function setValue($value = '')
    {
        switch($this->type) {

            case 'select':
                $this->customField->selected($value);
                break;

            case 'date':
                $this->customField->setDateFromValue($value);
                break;

            default:
            case 'url':
            case 'text':
            case 'varchar':
                $this->customField->setValue($value);
                break;
        }

        return $this;
    }

    /**
     * Gets the internal Input object
     * @return  object Input
     */
    protected function getElement()
    {
        return $this->customField->getElement();
    }

    /**
     * Get the validation output from the internal Input object.
     * @return  string
     */
    public function getValidationOutput()
    {
        return $this->customField->getValidationOutput();
    }
}
